<?php

namespace App\Http\Controllers;

use App\Models\AbsensiGuru;
use App\Models\guru;
use App\Models\IzinGuru;
use App\Models\JadwalPelajaran;
use App\Models\JamPelajaran;
use App\Models\KegiatanSekolah;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\SekolahSetting;
use App\Models\tahunAjaran;
use App\Models\User;
use App\Support\AbsenceBlockResolver;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class wakaController extends Controller
{
    private function attachMeetingAttendance(Collection $schedules, string $date): Collection
    {
        $attendance = AbsensiGuru::whereIn('jadwal_pelajaran_id', $schedules->pluck('id'))
            ->whereDate('tanggal', $date)
            ->get();

        return AbsenceBlockResolver::attachAttendance($schedules, $attendance);
    }

    /** Validate selected teaching sessions and return their range in the legacy schema. */
    private function resolveJadwalSessionRange(array $validated, Request $request): array
    {
        if ($request->has('jam_pelajaran_ids')) {
            $ids = array_values(array_unique(array_map('intval', (array) $request->input('jam_pelajaran_ids', []))));
        } else {
            // Keep compatibility with existing clients that submit start/end IDs.
            $legacyStart = JamPelajaran::where('jenis', 'pelajaran')->find((int) $validated['jam_pelajaran_mulai_id']);
            $legacyEnd = JamPelajaran::where('jenis', 'pelajaran')->find((int) $validated['jam_pelajaran_selesai_id']);
            if (!$legacyStart || !$legacyEnd || $legacyEnd->urutan < $legacyStart->urutan) {
                throw ValidationException::withMessages(['jam_pelajaran_ids' => 'Rentang sesi yang dipilih tidak valid.']);
            }
            $ids = JamPelajaran::where('jenis', 'pelajaran')
                ->whereBetween('urutan', [$legacyStart->urutan, $legacyEnd->urutan])
                ->orderBy('urutan')
                ->orderBy('jam_mulai')
                ->pluck('id')
                ->all();
        }

        if (!$ids) {
            throw ValidationException::withMessages(['jam_pelajaran_ids' => 'Pilih minimal satu sesi jam pelajaran.']);
        }

        $selected = JamPelajaran::whereIn('id', $ids)
            ->where('jenis', 'pelajaran')
            ->orderBy('urutan')
            ->orderBy('jam_mulai')
            ->get();
        if ($selected->count() !== count($ids)) {
            throw ValidationException::withMessages(['jam_pelajaran_ids' => 'Sesi yang dipilih tidak tersedia sebagai jam pelajaran. Kegiatan dan istirahat tidak dapat dipilih.']);
        }

        $first = $selected->first();
        $last = $selected->last();
        $required = JamPelajaran::where('jenis', 'pelajaran')
            ->where('urutan', '>=', $first->urutan)
            ->where('urutan', '<=', $last->urutan)
            ->orderBy('urutan')
            ->orderBy('jam_mulai')
            ->get();

        if ($required->pluck('id')->all() !== $selected->pluck('id')->all()) {
            throw ValidationException::withMessages(['jam_pelajaran_ids' => 'Sesi dalam satu pertemuan harus berurutan dan tidak boleh melewati sesi lain.']);
        }

        return [
            'jam_pelajaran_mulai_id' => $first->id,
            'jam_pelajaran_selesai_id' => $last->id,
            'jam_mulai' => $first->jam_mulai,
            'jam_selesai' => $last->jam_selesai,
            'jam_ids' => $selected->pluck('id')->all(),
        ];
    }

    private function persistJadwalSessions(JadwalPelajaran $jadwal, array $data, array $range): void
    {
        $now = now();
        $rows = collect($range['jam_ids'])->map(fn ($jamId) => [
            'jadwal_pelajaran_id' => $jadwal->id,
            'jam_pelajaran_id' => $jamId,
            'tahun_ajaran_id' => $data['tahun_ajaran_id'],
            'guru_id' => $data['guru_id'],
            'kelas_id' => $data['kelas_id'],
            'hari' => $data['hari'],
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        DB::table('jadwal_pelajaran_sesi')->insert($rows);
    }

    private function assertJadwalHasNoConflict(array $data, array $range, ?int $ignoreId = null): void
    {
        $selectedSlots = JamPelajaran::whereIn('id', $range['jam_ids'])->get();
        $activity = KegiatanSekolah::where('tahun_ajaran_id', $data['tahun_ajaran_id'])
            ->where('hari', $data['hari'])
            ->where(function ($query) use ($selectedSlots) {
                foreach ($selectedSlots as $slot) {
                    $query->orWhere(function ($overlap) use ($slot) {
                        $overlap->where('jam_mulai', '<', $slot->jam_selesai)
                            ->where('jam_selesai', '>', $slot->jam_mulai);
                    });
                }
            })
            ->first();
        if ($activity) {
            throw ValidationException::withMessages([
                'jam_pelajaran_ids' => "Jadwal tidak dapat disimpan karena bertabrakan dengan kegiatan sekolah '{$activity->nama_kegiatan}'.",
            ]);
        }

        $overlapQuery = function (string $field) use ($data, $range, $ignoreId) {
            return JadwalPelajaran::query()
                ->with(['kelas', 'guru.user', 'jamMulai', 'jamSelesai'])
                ->where('tahun_ajaran_id', $data['tahun_ajaran_id'])
                ->where('hari', $data['hari'])
                ->where($field, $data[$field])
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->whereHas('jamMulai', fn ($q) => $q->where('jam_mulai', '<', $range['jam_selesai']))
                ->whereHas('jamSelesai', fn ($q) => $q->where('jam_selesai', '>', $range['jam_mulai']));
        };

        $guruConflict = $overlapQuery('guru_id')->first();
        if ($guruConflict) {
            $guruName = $guruConflict->guru->user->name ?? 'Guru tersebut';
            $session = ($guruConflict->jamMulai->nama_sesi ?? 'sesi awal').(isset($guruConflict->jamSelesai->nama_sesi) ? ' sampai '.$guruConflict->jamSelesai->nama_sesi : '');
            throw ValidationException::withMessages([
                'guru_id' => "Jadwal tidak dapat disimpan. {$guruName} sudah memiliki jadwal di kelas {$guruConflict->kelas->nama_kelas} pada hari {$data['hari']} ({$session}).",
            ]);
        }

        $classConflict = $overlapQuery('kelas_id')->first();
        if ($classConflict) {
            $session = ($classConflict->jamMulai->nama_sesi ?? 'sesi awal').(isset($classConflict->jamSelesai->nama_sesi) ? ' sampai '.$classConflict->jamSelesai->nama_sesi : '');
            throw ValidationException::withMessages([
                'kelas_id' => "Jadwal tidak dapat disimpan. Kelas {$classConflict->kelas->nama_kelas} sudah memiliki pertemuan pada hari {$data['hari']} ({$session}).",
            ]);
        }
    }

    // ==========================================
    // DASHBOARD
    // ==========================================
    public function dashboard(): View
    {
        $hariInggris = now()->format('l');
        $mapHari = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];
        $hariIni = $mapHari[$hariInggris] ?? 'Senin';
        $today = now()->toDateString();

        $tahunAktif = tahunAjaran::where('status', true)->first();

        $totalGuru = guru::count();
        $totalKelas = Kelas::count();
        $totalMapel = MataPelajaran::count();

        $jadwalHariIniQuery = JadwalPelajaran::query()
            ->whereIn('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'])
            ->where('hari', $hariIni);
        if ($tahunAktif) {
            $jadwalHariIniQuery->where('tahun_ajaran_id', $tahunAktif->id);
        }
        $izinMenunggu = IzinGuru::where('status', 'menunggu')->count();
        $settingSekolah = SekolahSetting::getSetting();

        $jadwalHariIni = (clone $jadwalHariIniQuery)
            ->with(['guru.user', 'kelas', 'mataPelajaran', 'jamMulai', 'jamSelesai'])
            ->orderBy(JamPelajaran::select('jam_mulai')
                ->whereColumn('jam_pelajaran.id', 'jadwal_pelajaran.jam_pelajaran_mulai_id'))
            ->get();
        $this->attachMeetingAttendance($jadwalHariIni, $today);

        $totalJadwalHariIni = $jadwalHariIni->count();
        $sudahHadir = $jadwalHariIni->filter(fn ($jadwal) => in_array($jadwal->absensi->first()?->status, ['hadir', 'terlambat'], true))->count();
        $terlambat = $jadwalHariIni->filter(fn ($jadwal) => $jadwal->absensi->first()?->status === 'terlambat')->count();
        $izinSakit = $jadwalHariIni->filter(fn ($jadwal) => in_array($jadwal->absensi->first()?->status, ['izin', 'sakit'], true))->count();
        $belumCheckIn = $jadwalHariIni->filter(fn ($jadwal) => !$jadwal->absensi->first())->count();
        $jadwalHariIni = $jadwalHariIni->take(6);

        return view('waka.dashboard', compact(
            'totalGuru',
            'totalKelas',
            'totalMapel',
            'totalJadwalHariIni',
            'sudahHadir',
            'terlambat',
            'izinSakit',
            'belumCheckIn',
            'izinMenunggu',
            'hariIni',
            'today',
            'tahunAktif',
            'settingSekolah',
            'jadwalHariIni'
        ));
    }

    // ==========================================
    // KELOLA GURU
    // ==========================================
    public function dataGuru(Request $request): View
    {
        $guru = guru::with('user')
            ->whereHas('user', function ($query) {
                $query->where('role', 'guru');
            })
            ->when($request->string('search')->trim()->toString(), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('nip', 'like', "%{$search}%")
                        ->orWhere('no_hp', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('waka.guru.data', [
            'guru' => $guru,
            'search' => $request->input('search'),
        ]);
    }

    public function createGuru(): View
    {
        return view('waka.guru.tambah');
    }

    public function storeGuru(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'nip' => ['nullable', 'string', 'max:255', 'unique:guru,nip'],
            'no_hp' => ['nullable', 'string', 'max:20'],
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => 'guru',
            ]);

            guru::create([
                'user_id' => $user->id,
                'nip' => $validated['nip'] ?: null,
                'no_hp' => $validated['no_hp'] ?: null,
            ]);
        });

        return redirect()->route('waka.guru.data')
            ->with('success', 'Data guru berhasil ditambahkan.');
    }

    public function editGuru(Guru $guru): View
    {
        $guru->load('user');

        return view('waka.guru.edit', compact('guru'));
    }

    public function updateGuru(Request $request, Guru $guru): RedirectResponse
    {
        $guru->load('user');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($guru->user->id),
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'nip' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('guru', 'nip')->ignore($guru->id),
            ],
            'no_hp' => ['nullable', 'string', 'max:20'],
        ]);

        DB::transaction(function () use ($validated, $guru) {
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => 'guru',
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = $validated['password'];
            }

            $guru->user->update($userData);
            $guru->update([
                'nip' => $validated['nip'] ?: null,
                'no_hp' => $validated['no_hp'] ?: null,
            ]);
        });

        return redirect()->route('waka.guru.data')
            ->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroyGuru(Guru $guru): RedirectResponse
    {
        DB::transaction(function () use ($guru) {
            $guru->delete();

            if ($guru->user) {
                $guru->user->delete();
            }
        });

        return redirect()->route('waka.guru.data')
            ->with('success', 'Data guru berhasil dihapus.');
    }

    // ==========================================
    // TAHUN AJARAN
    // ==========================================
    public function dataTahunAjaran(Request $request): View
    {
        $tahunAjaran = tahunAjaran::query()
            ->when($request->string('search')->trim()->toString(), function ($query, $search) {
                $query->where('nama', 'like', "%{$search}%")
                    ->orWhere('semester', 'like', "%{$search}%");
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('waka.tahunAjaran.data', [
            'tahunAjaran' => $tahunAjaran,
            'search' => $request->input('search'),
        ]);
    }

    public function createTahunAjaran(): View
    {
        return view('waka.tahunAjaran.tambah');
    }

    public function storeTahunAjaran(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:9', Rule::unique('tahun_ajaran')->where(fn ($query) => $query->where('semester', $request->semester))],
            'semester' => ['required', Rule::in(['ganjil', 'genap'])],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'status' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($validated) {
            if ($validated['status'] ?? false) {
                tahunAjaran::where('status', true)->update(['status' => false]);
            }

            tahunAjaran::create([
                ...$validated,
                'status' => $validated['status'] ?? false,
            ]);
        });

        return redirect()->route('waka.tahun-ajaran.data')
            ->with('success', 'Data tahun ajaran berhasil ditambahkan.');
    }

    public function editTahunAjaran(tahunAjaran $tahunAjaran): View
    {
        return view('waka.tahunAjaran.edit', compact('tahunAjaran'));
    }

    public function updateTahunAjaran(Request $request, tahunAjaran $tahunAjaran): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:9',
                Rule::unique('tahun_ajaran')->where(fn ($query) => $query->where('semester', $request->semester))->ignore($tahunAjaran->id),
            ],
            'semester' => ['required', Rule::in(['ganjil', 'genap'])],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'status' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($validated, $tahunAjaran) {
            if ($validated['status'] ?? false) {
                tahunAjaran::where('id', '!=', $tahunAjaran->id)->where('status', true)->update(['status' => false]);
            }

            $tahunAjaran->update([
                ...$validated,
                'status' => $validated['status'] ?? false,
            ]);
        });

        return redirect()->route('waka.tahun-ajaran.data')
            ->with('success', 'Data tahun ajaran berhasil diperbarui.');
    }

    public function destroyTahunAjaran(tahunAjaran $tahunAjaran): RedirectResponse
    {
        $tahunAjaran->delete();

        return redirect()->route('waka.tahun-ajaran.data')
            ->with('success', 'Data tahun ajaran berhasil dihapus.');
    }

    // ==========================================
    // KELAS
    // ==========================================
    public function dataKelas(Request $request): View
    {
        $kelas = Kelas::query()
            ->when($request->string('search')->trim()->toString(), function ($query, $search) {
                $query->where('nama_kelas', 'like', "%{$search}%")
                    ->orWhere('tingkat', 'like', "%{$search}%")
                    ->orWhere('jurusan', 'like', "%{$search}%");
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('waka.kelas.data', [
            'kelas' => $kelas,
            'search' => $request->input('search'),
        ]);
    }

    public function createKelas(): View
    {
        return view('waka.kelas.tambah');
    }

    public function storeKelas(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kelas' => ['required', 'string', 'max:50', 'unique:kelas,nama_kelas'],
            'tingkat' => ['nullable', 'string', 'max:20'],
            'jurusan' => ['nullable', 'string', 'max:50'],
        ]);

        Kelas::create($validated);

        return redirect()->route('waka.kelas.data')
            ->with('success', 'Data kelas berhasil ditambahkan.');
    }

    public function editKelas(Kelas $kelas): View
    {
        return view('waka.kelas.edit', compact('kelas'));
    }

    public function updateKelas(Request $request, Kelas $kelas): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kelas' => ['required', 'string', 'max:50', Rule::unique('kelas', 'nama_kelas')->ignore($kelas->id)],
            'tingkat' => ['nullable', 'string', 'max:20'],
            'jurusan' => ['nullable', 'string', 'max:50'],
        ]);

        $kelas->update($validated);

        return redirect()->route('waka.kelas.data')
            ->with('success', 'Data kelas berhasil diperbarui.');
    }

    public function destroyKelas(Kelas $kelas): RedirectResponse
    {
        $kelas->delete();

        return redirect()->route('waka.kelas.data')
            ->with('success', 'Data kelas berhasil dihapus.');
    }

    // ==========================================
    // MATA PELAJARAN
    // ==========================================
    public function dataMapel(Request $request): View
    {
        $mapel = MataPelajaran::query()
            ->when($request->string('search')->trim()->toString(), function ($query, $search) {
                $query->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode', 'like', "%{$search}%");
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('waka.mapel.data', [
            'mapel' => $mapel,
            'search' => $request->input('search'),
        ]);
    }

    public function createMapel(): View
    {
        return view('waka.mapel.tambah');
    }

    public function storeMapel(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode' => ['nullable', 'string', 'max:20'],
            'nama' => ['required', 'string', 'max:100'],
        ]);

        MataPelajaran::create($validated);

        return redirect()->route('waka.mapel.data')
            ->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function editMapel(MataPelajaran $mapel): View
    {
        return view('waka.mapel.edit', compact('mapel'));
    }

    public function updateMapel(Request $request, MataPelajaran $mapel): RedirectResponse
    {
        $validated = $request->validate([
            'kode' => ['nullable', 'string', 'max:20'],
            'nama' => ['required', 'string', 'max:100'],
        ]);

        $mapel->update($validated);

        return redirect()->route('waka.mapel.data')
            ->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroyMapel(MataPelajaran $mapel): RedirectResponse
    {
        $mapel->delete();

        return redirect()->route('waka.mapel.data')
            ->with('success', 'Mata pelajaran berhasil dihapus.');
    }

    // ==========================================
    // JAM PELAJARAN (SESI)
    // ==========================================
    public function dataJamPelajaran(Request $request): View
    {
        $jamPelajaran = JamPelajaran::query()
            ->when($request->string('search')->trim()->toString(), function ($query, $search) {
                $query->where('nama_sesi', 'like', "%{$search}%");
            })
            ->orderBy('urutan')
            ->orderBy('jam_mulai')
            ->paginate(10)
            ->withQueryString();

        return view('waka.jamPelajaran.data', [
            'jamPelajaran' => $jamPelajaran,
            'search' => $request->input('search'),
        ]);
    }

    public function createJamPelajaran(): View
    {
        $nextUrutan = (JamPelajaran::max('urutan') ?? 0) + 1;
        return view('waka.jamPelajaran.tambah', compact('nextUrutan'));
    }

    public function storeJamPelajaran(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_sesi' => ['required', 'string', 'max:50'],
            'jenis' => ['required', Rule::in(['pelajaran', 'upacara', 'istirahat', 'kegiatan_lain'])],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'urutan' => ['required', 'integer', 'min:1'],
        ]);

        JamPelajaran::create($validated);

        return redirect()->route('waka.jam-pelajaran.data')
            ->with('success', 'Jam pelajaran berhasil ditambahkan.');
    }

    public function editJamPelajaran(JamPelajaran $jamPelajaran): View
    {
        return view('waka.jamPelajaran.edit', compact('jamPelajaran'));
    }

    public function updateJamPelajaran(Request $request, JamPelajaran $jamPelajaran): RedirectResponse
    {
        $validated = $request->validate([
            'nama_sesi' => ['required', 'string', 'max:50'],
            'jenis' => ['required', Rule::in(['pelajaran', 'upacara', 'istirahat', 'kegiatan_lain'])],
            'jam_mulai' => ['required'],
            'jam_selesai' => ['required', 'after:jam_mulai'],
            'urutan' => ['required', 'integer', 'min:1'],
        ]);

        $jamPelajaran->update($validated);

        return redirect()->route('waka.jam-pelajaran.data')
            ->with('success', 'Jam pelajaran berhasil diperbarui.');
    }

    public function destroyJamPelajaran(JamPelajaran $jamPelajaran): RedirectResponse
    {
        $jamPelajaran->delete();

        return redirect()->route('waka.jam-pelajaran.data')
            ->with('success', 'Jam pelajaran berhasil dihapus.');
    }

    // ==========================================
    // JADWAL MENGAJAR & KEGIATAN SEKOLAH
    // ==========================================
    public function dataJadwal(Request $request): View
    {
        $tahunAktif = tahunAjaran::where('status', true)->first();
        $daftarTahunAjaran = tahunAjaran::all();

        $tahunAjaranId = $request->input('tahun_ajaran_id', $tahunAktif?->id);
        $selectedTahun = $tahunAjaranId ? tahunAjaran::find($tahunAjaranId) : $tahunAktif;

        $mapHari = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];
        $todayName = $mapHari[now()->format('l')] ?? 'Senin';
        $daftarHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $defaultHari = in_array($todayName, $daftarHari, true) ? $todayName : 'Senin';

        $selectedHari = $request->input('hari', $defaultHari);
        if (!in_array($selectedHari, $daftarHari, true)) {
            $selectedHari = $defaultHari;
        }
        $activeTab = $request->input('tab', 'timeline'); // 'timeline', 'matriks', 'kegiatan', 'tabel'

        $filterTabelHari = $request->input('filter_tabel_hari');
        if (!in_array($filterTabelHari, $daftarHari, true)) {
            $filterTabelHari = null;
        }
        $daftarGuru = guru::with('user')->get();
        $daftarKelas = Kelas::orderBy('nama_kelas')->get();
        $daftarMapel = MataPelajaran::orderBy('nama')->get();
        $daftarJamSemua = JamPelajaran::orderBy('jam_mulai')->orderBy('urutan')->get();
        $daftarJamPelajaran = JamPelajaran::where('jenis', 'pelajaran')->orderBy('jam_mulai')->orderBy('urutan')->get();

        // 1. DATA TIMELINE & MATRIKS (Hari Terpilih)
        $kegiatanHariIni = KegiatanSekolah::query()
            ->when($selectedTahun, fn ($q) => $q->where('tahun_ajaran_id', $selectedTahun->id))
            ->where('hari', $selectedHari)
            ->orderBy('jam_mulai')
            ->get();

        $jadwalHariIni = JadwalPelajaran::with(['guru.user', 'kelas', 'mataPelajaran', 'jamMulai', 'jamSelesai'])
            ->when($selectedTahun, fn ($q) => $q->where('tahun_ajaran_id', $selectedTahun->id))
            ->whereIn('hari', $daftarHari)
            ->where('hari', $selectedHari)
            ->get();

        // Gabungkan timeline terpadu hari ini (kegiatan sekolah + sesi pelajaran)
        $timelineHariIni = collect();

        foreach ($kegiatanHariIni as $keg) {
            $timelineHariIni->push([
                'type' => 'kegiatan',
                'jam_mulai' => $keg->jam_mulai,
                'jam_selesai' => $keg->jam_selesai,
                'data' => $keg,
            ]);
        }

        foreach ($jadwalHariIni as $jadwalItem) {
            $timelineHariIni->push([
                'type' => 'jadwal',
                'jam_mulai' => $jadwalItem->jamMulai->jam_mulai,
                'jam_selesai' => $jadwalItem->jamSelesai->jam_selesai,
                'data' => $jadwalItem,
            ]);
        }

        $timelineHariIni = $timelineHariIni->sortBy([
            ['jam_mulai', 'asc'],
            ['jam_selesai', 'asc'],
        ])->values();

        // 2. DATA KEGIATAN SEKOLAH (Tab Kegiatan)
        $semuaKegiatan = KegiatanSekolah::query()
            ->when($selectedTahun, fn ($q) => $q->where('tahun_ajaran_id', $selectedTahun->id))
            ->when($request->filled('filter_kegiatan_hari'), fn ($q) => $q->where('hari', $request->filter_kegiatan_hari))
            ->orderByRaw("CASE hari WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 ELSE 6 END")
            ->orderBy('jam_mulai')
            ->get();

        // 3. DATA TABEL JADWAL CRUD LENGKAP (Tab Tabel)
        $jadwal = JadwalPelajaran::with(['tahunAjaran', 'guru.user', 'kelas', 'mataPelajaran', 'jamMulai', 'jamSelesai'])
            ->when($selectedTahun, fn ($q) => $q->where('tahun_ajaran_id', $selectedTahun->id))
            ->whereIn('hari', $daftarHari)
            ->when($filterTabelHari, fn ($q) => $q->where('hari', $filterTabelHari))
            ->when($request->filled('guru_id'), fn ($q) => $q->where('guru_id', $request->guru_id))
            ->when($request->filled('kelas_id'), fn ($q) => $q->where('kelas_id', $request->kelas_id))
            ->when($request->filled('mata_pelajaran_id'), fn ($q) => $q->where('mata_pelajaran_id', $request->mata_pelajaran_id))
            ->orderByRaw("CASE hari WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 ELSE 6 END")
            ->orderBy(JamPelajaran::select('jam_mulai')
                ->whereColumn('jam_pelajaran.id', 'jadwal_pelajaran.jam_pelajaran_mulai_id'))
            ->paginate(15)
            ->withQueryString();

        return view('waka.jadwal.data', compact(
            'tahunAktif',
            'selectedTahun',
            'daftarTahunAjaran',
            'selectedHari',
            'activeTab',
            'daftarHari',
            'daftarGuru',
            'daftarKelas',
            'daftarMapel',
            'daftarJamSemua',
            'daftarJamPelajaran',
            'kegiatanHariIni',
            'jadwalHariIni',
            'timelineHariIni',
            'semuaKegiatan',
            'jadwal'
        ));
    }

    public function togglePublikasiJadwal(Request $request): RedirectResponse
    {
        $tahunAjaranId = $request->input('tahun_ajaran_id');
        $tahunAjaran = $tahunAjaranId
            ? tahunAjaran::findOrFail($tahunAjaranId)
            : tahunAjaran::where('status', true)->first();

        if (!$tahunAjaran) {
            return back()->withErrors(['publikasi' => 'Tidak ada tahun ajaran aktif yang dapat dipublikasikan.']);
        }

        $tahunAjaran->is_published = !$tahunAjaran->is_published;
        $tahunAjaran->save();

        $statusStr = $tahunAjaran->is_published
            ? 'DIPUBLIKASIKAN. Seluruh guru sekarang dapat melihat jadwal mengajar mereka di portal guru.'
            : 'DIKEMBALIKAN KE DRAFT. Jadwal disembunyikan sementara dari portal guru selama proses penyesuaian.';

        return back()->with('success', "Status jadwal Tahun Ajaran {$tahunAjaran->nama} ({$tahunAjaran->semester}) berhasil diubah menjadi: {$statusStr}");
    }

    public function createKegiatan(): View
    {
        $tahunAktif = tahunAjaran::where('status', true)->first();
        $daftarTahunAjaran = tahunAjaran::all();
        return view('waka.kegiatan.tambah', compact('tahunAktif', 'daftarTahunAjaran'));
    }

    public function storeKegiatan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tahun_ajaran_id' => ['required', 'exists:tahun_ajaran,id'],
            'nama_kegiatan' => ['required', 'string', 'max:100'],
            'jenis' => ['required', Rule::in(['upacara', 'istirahat', 'ibadah', 'senam', 'literasi', 'kegiatan_lain'])],
            'hari' => ['required', Rule::in(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'])],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'lokasi' => ['nullable', 'string', 'max:100'],
            'peserta' => ['nullable', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ]);

        // Cek bentrok dengan kegiatan sekolah lain
        $bentrokKegiatan = KegiatanSekolah::where('tahun_ajaran_id', $validated['tahun_ajaran_id'])
            ->where('hari', $validated['hari'])
            ->where('jam_mulai', '<', $validated['jam_selesai'])
            ->where('jam_selesai', '>', $validated['jam_mulai'])
            ->first();

        if ($bentrokKegiatan) {
            return back()->withInput()->withErrors([
                'jam_mulai' => "Jam kegiatan bertabrakan dengan agenda kegiatan sekolah: '{$bentrokKegiatan->nama_kegiatan}' ({$bentrokKegiatan->jam_mulai} - {$bentrokKegiatan->jam_selesai}).",
            ]);
        }

        // Cek bentrok dengan jadwal pelajaran KBM
        $bentrokJadwal = JadwalPelajaran::where('tahun_ajaran_id', $validated['tahun_ajaran_id'])
            ->where('hari', $validated['hari'])
            ->whereHas('jamMulai', function ($q) use ($validated) {
                $q->where('jam_mulai', '<', $validated['jam_selesai']);
            })
            ->whereHas('jamSelesai', function ($q) use ($validated) {
                $q->where('jam_selesai', '>', $validated['jam_mulai']);
            })
            ->count();

        if ($bentrokJadwal > 0) {
            return back()->withInput()->withErrors([
                'jam_mulai' => "Terdapat {$bentrokJadwal} jadwal pelajaran KBM pada hari {$validated['hari']} yang jamnya bertabrakan dengan rentang kegiatan ini. Harap sesuaikan jam kegiatan atau sesuaikan jadwal KBM terlebih dahulu.",
            ]);
        }

        KegiatanSekolah::create($validated);

        return redirect()->route('waka.jadwal.data', ['tab' => 'kegiatan', 'hari' => $validated['hari']])
            ->with('success', "Kegiatan sekolah '{$validated['nama_kegiatan']}' berhasil ditambahkan.");
    }

    public function editKegiatan(KegiatanSekolah $kegiatan): View
    {
        $tahunAktif = tahunAjaran::where('status', true)->first();
        $daftarTahunAjaran = tahunAjaran::all();
        return view('waka.kegiatan.edit', compact('kegiatan', 'tahunAktif', 'daftarTahunAjaran'));
    }

    public function updateKegiatan(Request $request, KegiatanSekolah $kegiatan): RedirectResponse
    {
        $validated = $request->validate([
            'tahun_ajaran_id' => ['required', 'exists:tahun_ajaran,id'],
            'nama_kegiatan' => ['required', 'string', 'max:100'],
            'jenis' => ['required', Rule::in(['upacara', 'istirahat', 'ibadah', 'senam', 'literasi', 'kegiatan_lain'])],
            'hari' => ['required', Rule::in(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'])],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'lokasi' => ['nullable', 'string', 'max:100'],
            'peserta' => ['nullable', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ]);

        // Cek bentrok dengan kegiatan sekolah lain
        $bentrokKegiatan = KegiatanSekolah::where('id', '!=', $kegiatan->id)
            ->where('tahun_ajaran_id', $validated['tahun_ajaran_id'])
            ->where('hari', $validated['hari'])
            ->where('jam_mulai', '<', $validated['jam_selesai'])
            ->where('jam_selesai', '>', $validated['jam_mulai'])
            ->first();

        if ($bentrokKegiatan) {
            return back()->withInput()->withErrors([
                'jam_mulai' => "Jam kegiatan bertabrakan dengan agenda kegiatan sekolah: '{$bentrokKegiatan->nama_kegiatan}' ({$bentrokKegiatan->jam_mulai} - {$bentrokKegiatan->jam_selesai}).",
            ]);
        }

        // Cek bentrok dengan jadwal pelajaran KBM
        $bentrokJadwal = JadwalPelajaran::where('tahun_ajaran_id', $validated['tahun_ajaran_id'])
            ->where('hari', $validated['hari'])
            ->whereHas('jamMulai', function ($q) use ($validated) {
                $q->where('jam_mulai', '<', $validated['jam_selesai']);
            })
            ->whereHas('jamSelesai', function ($q) use ($validated) {
                $q->where('jam_selesai', '>', $validated['jam_mulai']);
            })
            ->count();

        if ($bentrokJadwal > 0) {
            return back()->withInput()->withErrors([
                'jam_mulai' => "Terdapat {$bentrokJadwal} jadwal pelajaran KBM pada hari {$validated['hari']} yang bertabrakan dengan rentang kegiatan ini.",
            ]);
        }

        $kegiatan->update($validated);

        return redirect()->route('waka.jadwal.data', ['tab' => 'kegiatan', 'hari' => $validated['hari']])
            ->with('success', "Kegiatan sekolah '{$kegiatan->nama_kegiatan}' berhasil diperbarui.");
    }

    public function destroyKegiatan(KegiatanSekolah $kegiatan): RedirectResponse
    {
        $nama = $kegiatan->nama_kegiatan;
        $hari = $kegiatan->hari;
        $kegiatan->delete();

        return redirect()->route('waka.jadwal.data', ['tab' => 'kegiatan', 'hari' => $hari])
            ->with('success', "Kegiatan sekolah '{$nama}' berhasil dihapus.");
    }

    public function createJadwal(): View
    {
        $tahunAktif = tahunAjaran::where('status', true)->first();
        $daftarTahunAjaran = tahunAjaran::orderByDesc('tanggal_mulai')->get();
        $daftarGuru = guru::with('user')->get();
        $daftarKelas = Kelas::all();
        $daftarMapel = MataPelajaran::all();
        $daftarJam = JamPelajaran::orderBy('jam_mulai')->orderBy('urutan')->get();

        return view('waka.jadwal.tambah', compact(
            'tahunAktif',
            'daftarTahunAjaran',
            'daftarGuru',
            'daftarKelas',
            'daftarMapel',
            'daftarJam'
        ));
    }

    public function previewJadwal(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tahun_ajaran_id' => ['required', 'exists:tahun_ajaran,id'],
            'kelas_id' => ['nullable', 'exists:kelas,id'],
            'guru_id' => ['nullable', 'exists:guru,id'],
        ]);

        if (empty($data['kelas_id']) && empty($data['guru_id'])) {
            return response()->json(['message' => 'Pilih kelas atau guru untuk melihat jadwal.'], 422);
        }

        $hariSekolah = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $sesi = JamPelajaran::where('jenis', 'pelajaran')
            ->orderBy('urutan')
            ->orderBy('jam_mulai')
            ->get(['id', 'nama_sesi', 'jam_mulai', 'jam_selesai']);
        $barisJadwal = JamPelajaran::whereIn('jenis', ['pelajaran', 'istirahat'])
            ->orderBy('urutan')
            ->orderBy('jam_mulai')
            ->get(['id', 'nama_sesi', 'jenis', 'jam_mulai', 'jam_selesai']);
        $jadwalKelas = [];
        $jadwalGuru = [];

        $buatPeta = function ($jadwals) use ($sesi) {
            $peta = [];
            foreach ($jadwals as $jadwal) {
                $sesiIds = $jadwal->sesi->pluck('jam_pelajaran_id');
                if ($sesiIds->isEmpty()) {
                    $sesiIds = $sesi->filter(fn ($slot) =>
                        $slot->jam_mulai >= $jadwal->jamMulai->jam_mulai &&
                        $slot->jam_selesai <= $jadwal->jamSelesai->jam_selesai
                    )->pluck('id');
                }

                foreach ($sesiIds as $sesiId) {
                    $peta[$jadwal->hari][$sesiId] = [
                        'mapel' => $jadwal->mataPelajaran->nama,
                        'guru' => $jadwal->guru->user->name ?? 'Guru',
                        'kelas' => $jadwal->kelas->nama_kelas,
                    ];
                }
            }

            return $peta;
        };

        if (!empty($data['kelas_id'])) {
            $jadwalKelas = $buatPeta(JadwalPelajaran::with([
                'guru.user', 'kelas', 'mataPelajaran', 'jamMulai', 'jamSelesai', 'sesi',
            ])
                ->where('tahun_ajaran_id', $data['tahun_ajaran_id'])
                ->where('kelas_id', $data['kelas_id'])
                ->whereIn('hari', $hariSekolah)
                ->get());
        }

        if (!empty($data['guru_id'])) {
            $jadwalGuru = $buatPeta(JadwalPelajaran::with([
                'guru.user', 'kelas', 'mataPelajaran', 'jamMulai', 'jamSelesai', 'sesi',
            ])
                ->where('tahun_ajaran_id', $data['tahun_ajaran_id'])
                ->where('guru_id', $data['guru_id'])
                ->whereIn('hari', $hariSekolah)
                ->get());
        }

        return response()->json([
            'sesi' => $barisJadwal->map(fn ($slot) => [
                'id' => $slot->id,
                'nama' => $slot->nama_sesi,
                'jenis' => $slot->jenis,
                'mulai' => substr($slot->jam_mulai, 0, 5),
                'selesai' => substr($slot->jam_selesai, 0, 5),
            ])->values(),
            'hari' => $hariSekolah,
            'jadwal_kelas' => $jadwalKelas,
            'jadwal_guru' => $jadwalGuru,
        ]);
    }

    public function checkJadwalConflict(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tahun_ajaran_id' => ['required', 'exists:tahun_ajaran,id'],
            'guru_id' => ['required', 'exists:guru,id'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'mata_pelajaran_id' => ['required', 'exists:mata_pelajaran,id'],
            'hari' => ['required', Rule::in(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'])],
            'jam_pelajaran_ids' => ['required', 'array', 'min:1'],
            'jam_pelajaran_ids.*' => ['required', 'integer', 'distinct', 'exists:jam_pelajaran,id'],
            'jadwal_id' => ['nullable', 'integer', 'exists:jadwal_pelajaran,id'],
        ]);

        try {
            $range = $this->resolveJadwalSessionRange($data, $request);
            $this->assertJadwalHasNoConflict($data, $range, $data['jadwal_id'] ?? null);
        } catch (ValidationException $exception) {
            $messages = collect($exception->errors())->flatten()->values();
            return response()->json([
                'available' => false,
                'message' => $messages->first() ?? 'Jadwal bertabrakan dengan jadwal lain.',
            ], 422);
        }

        return response()->json([
            'available' => true,
            'message' => 'Tidak ditemukan bentrok. Jadwal dapat disimpan.',
        ]);
    }

    public function storeJadwal(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tahun_ajaran_id' => ['required', 'exists:tahun_ajaran,id'],
            'guru_id' => ['required', 'exists:guru,id'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'mata_pelajaran_id' => ['required', 'exists:mata_pelajaran,id'],
            'hari' => ['required', Rule::in(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'])],
            'jam_pelajaran_ids' => ['nullable', 'array', 'min:1'],
            'jam_pelajaran_ids.*' => ['required', 'integer', 'distinct', 'exists:jam_pelajaran,id'],
            'jam_pelajaran_mulai_id' => ['required_without:jam_pelajaran_ids', 'nullable', 'exists:jam_pelajaran,id'],
            'jam_pelajaran_selesai_id' => ['required_without:jam_pelajaran_ids', 'nullable', 'exists:jam_pelajaran,id'],
        ]);

        $range = $this->resolveJadwalSessionRange($validated, $request);
        $validated['jam_pelajaran_mulai_id'] = $range['jam_pelajaran_mulai_id'];
        $validated['jam_pelajaran_selesai_id'] = $range['jam_pelajaran_selesai_id'];
        unset($validated['jam_pelajaran_ids']);

        $jamMulai = JamPelajaran::findOrFail($validated['jam_pelajaran_mulai_id']);
        $jamSelesai = JamPelajaran::findOrFail($validated['jam_pelajaran_selesai_id']);

        if ($jamMulai->jenis !== 'pelajaran' || $jamSelesai->jenis !== 'pelajaran') {
            return back()->withInput()->withErrors([
                'jam_pelajaran_mulai_id' => 'Sesi yang dipilih bukan jam pelajaran (KBM). Sesi upacara, istirahat, atau kegiatan lain tidak boleh dimasukkan sebagai jadwal mengajar.',
            ]);
        }

        if ($jamSelesai->jam_selesai <= $jamMulai->jam_mulai) {
            return back()->withInput()->withErrors([
                'jam_pelajaran_selesai_id' => 'Waktu selesai harus setelah waktu mulai.',
            ]);
        }

        // Cek bentrok dengan kegiatan sekolah umum (Upacara / Istirahat)
        $bentrokKegiatan = KegiatanSekolah::where('tahun_ajaran_id', $validated['tahun_ajaran_id'])
            ->where('hari', $validated['hari'])
            ->where('jam_mulai', '<', $jamSelesai->jam_selesai)
            ->where('jam_selesai', '>', $jamMulai->jam_mulai)
            ->first();

        if ($bentrokKegiatan) {
            return back()->withInput()->withErrors([
                'jam_pelajaran_mulai_id' => "Jam mengajar bertabrakan dengan agenda kegiatan sekolah '{$bentrokKegiatan->nama_kegiatan}' ({$bentrokKegiatan->jam_mulai} - {$bentrokKegiatan->jam_selesai}).",
            ]);
        }

        // Cek bentrok guru di hari & jam yang sama
        $bentrokGuru = JadwalPelajaran::with(['kelas', 'jamMulai', 'jamSelesai', 'guru.user'])->where('tahun_ajaran_id', $validated['tahun_ajaran_id'])
            ->where('hari', $validated['hari'])
            ->where('guru_id', $validated['guru_id'])
            ->whereHas('jamMulai', function ($q) use ($jamSelesai) {
                $q->where('jam_mulai', '<', $jamSelesai->jam_selesai);
            })
            ->whereHas('jamSelesai', function ($q) use ($jamMulai) {
                $q->where('jam_selesai', '>', $jamMulai->jam_mulai);
            })
            ->first();

        if ($bentrokGuru) {
            return back()->withInput()->withErrors([
                'guru_id' => "Jadwal tidak dapat disimpan. ".($bentrokGuru->guru->user->name ?? 'Guru tersebut')." sudah memiliki jadwal mengajar di {$bentrokGuru->kelas->nama_kelas} pada {$validated['hari']} sesi {$bentrokGuru->jamMulai->nama_sesi} sampai {$bentrokGuru->jamSelesai->nama_sesi}.",
            ]);
        }

        // Cek bentrok kelas di hari & jam yang sama
        $bentrokKelas = JadwalPelajaran::with(['kelas', 'jamMulai', 'jamSelesai'])->where('tahun_ajaran_id', $validated['tahun_ajaran_id'])
            ->where('hari', $validated['hari'])
            ->where('kelas_id', $validated['kelas_id'])
            ->whereHas('jamMulai', function ($q) use ($jamSelesai) {
                $q->where('jam_mulai', '<', $jamSelesai->jam_selesai);
            })
            ->whereHas('jamSelesai', function ($q) use ($jamMulai) {
                $q->where('jam_selesai', '>', $jamMulai->jam_mulai);
            })
            ->first();

        if ($bentrokKelas) {
            return back()->withInput()->withErrors([
                'kelas_id' => "Jadwal tidak dapat disimpan. Kelas {$bentrokKelas->kelas->nama_kelas} sudah memiliki jadwal pada {$validated['hari']} sesi {$bentrokKelas->jamMulai->nama_sesi} sampai {$bentrokKelas->jamSelesai->nama_sesi}.",
            ]);
        }

        try {
            DB::transaction(function () use ($validated, $range) {
                tahunAjaran::whereKey($validated['tahun_ajaran_id'])->lockForUpdate()->firstOrFail();
                $this->assertJadwalHasNoConflict($validated, $range);
                $jadwal = JadwalPelajaran::create($validated);
                $this->persistJadwalSessions($jadwal, $validated, $range);
            });
        } catch (QueryException $exception) {
            if (in_array((string) $exception->getCode(), ['23000', '23505'], true)) {
                return back()->withInput()->withErrors([
                    'jam_pelajaran_ids' => 'Jadwal tidak dapat disimpan karena sesi yang dipilih baru saja digunakan oleh jadwal lain. Muat ulang jadwal lalu pilih sesi yang tersedia.',
                ]);
            }

            throw $exception;
        }

        return redirect()->route('waka.jadwal.data')
            ->with('success', 'Jadwal pelajaran berhasil disusun.');
    }

    public function editJadwal(JadwalPelajaran $jadwal): View
    {
        $tahunAktif = tahunAjaran::where('status', true)->first();
        $daftarGuru = guru::with('user')->get();
        $daftarKelas = Kelas::all();
        $daftarMapel = MataPelajaran::all();
        $daftarJam = JamPelajaran::orderBy('jam_mulai')->orderBy('urutan')->get();
        $jamTerpilih = JamPelajaran::where('jenis', 'pelajaran')
            ->whereBetween('urutan', [$jadwal->jamMulai->urutan, $jadwal->jamSelesai->urutan])
            ->orderBy('urutan')
            ->pluck('id')
            ->all();

        return view('waka.jadwal.edit', compact(
            'jadwal',
            'tahunAktif',
            'daftarGuru',
            'daftarKelas',
            'daftarMapel',
            'daftarJam',
            'jamTerpilih'
        ));
    }

    public function updateJadwal(Request $request, JadwalPelajaran $jadwal): RedirectResponse
    {
        $validated = $request->validate([
            'tahun_ajaran_id' => ['required', 'exists:tahun_ajaran,id'],
            'guru_id' => ['required', 'exists:guru,id'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'mata_pelajaran_id' => ['required', 'exists:mata_pelajaran,id'],
            'hari' => ['required', Rule::in(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'])],
            'jam_pelajaran_ids' => ['nullable', 'array', 'min:1'],
            'jam_pelajaran_ids.*' => ['required', 'integer', 'distinct', 'exists:jam_pelajaran,id'],
            'jam_pelajaran_mulai_id' => ['required_without:jam_pelajaran_ids', 'nullable', 'exists:jam_pelajaran,id'],
            'jam_pelajaran_selesai_id' => ['required_without:jam_pelajaran_ids', 'nullable', 'exists:jam_pelajaran,id'],
        ]);

        $range = $this->resolveJadwalSessionRange($validated, $request);
        $validated['jam_pelajaran_mulai_id'] = $range['jam_pelajaran_mulai_id'];
        $validated['jam_pelajaran_selesai_id'] = $range['jam_pelajaran_selesai_id'];
        unset($validated['jam_pelajaran_ids']);

        $jamMulai = JamPelajaran::findOrFail($validated['jam_pelajaran_mulai_id']);
        $jamSelesai = JamPelajaran::findOrFail($validated['jam_pelajaran_selesai_id']);

        if ($jamMulai->jenis !== 'pelajaran' || $jamSelesai->jenis !== 'pelajaran') {
            return back()->withInput()->withErrors([
                'jam_pelajaran_mulai_id' => 'Sesi yang dipilih bukan jam pelajaran (KBM). Sesi upacara, istirahat, atau kegiatan lain tidak boleh dimasukkan sebagai jadwal mengajar.',
            ]);
        }

        if ($jamSelesai->jam_selesai <= $jamMulai->jam_mulai) {
            return back()->withInput()->withErrors([
                'jam_pelajaran_selesai_id' => 'Waktu selesai harus setelah waktu mulai.',
            ]);
        }

        // Cek bentrok dengan kegiatan sekolah umum (Upacara / Istirahat)
        $bentrokKegiatan = KegiatanSekolah::where('tahun_ajaran_id', $validated['tahun_ajaran_id'])
            ->where('hari', $validated['hari'])
            ->where('jam_mulai', '<', $jamSelesai->jam_selesai)
            ->where('jam_selesai', '>', $jamMulai->jam_mulai)
            ->first();

        if ($bentrokKegiatan) {
            return back()->withInput()->withErrors([
                'jam_pelajaran_mulai_id' => "Jam mengajar bertabrakan dengan agenda kegiatan sekolah '{$bentrokKegiatan->nama_kegiatan}' ({$bentrokKegiatan->jam_mulai} - {$bentrokKegiatan->jam_selesai}).",
            ]);
        }

        // Cek bentrok guru kecuali jadwal ini sendiri
        $bentrokGuru = JadwalPelajaran::with(['kelas', 'jamMulai', 'jamSelesai', 'guru.user'])->where('id', '!=', $jadwal->id)
            ->where('tahun_ajaran_id', $validated['tahun_ajaran_id'])
            ->where('hari', $validated['hari'])
            ->where('guru_id', $validated['guru_id'])
            ->whereHas('jamMulai', function ($q) use ($jamSelesai) {
                $q->where('jam_mulai', '<', $jamSelesai->jam_selesai);
            })
            ->whereHas('jamSelesai', function ($q) use ($jamMulai) {
                $q->where('jam_selesai', '>', $jamMulai->jam_mulai);
            })
            ->first();

        if ($bentrokGuru) {
            return back()->withInput()->withErrors([
                'guru_id' => "Jadwal tidak dapat disimpan. ".($bentrokGuru->guru->user->name ?? 'Guru tersebut')." sudah memiliki jadwal mengajar di {$bentrokGuru->kelas->nama_kelas} pada {$validated['hari']} sesi {$bentrokGuru->jamMulai->nama_sesi} sampai {$bentrokGuru->jamSelesai->nama_sesi}.",
            ]);
        }

        // Cek bentrok kelas kecuali jadwal ini sendiri
        $bentrokKelas = JadwalPelajaran::with(['kelas', 'jamMulai', 'jamSelesai'])->where('id', '!=', $jadwal->id)
            ->where('tahun_ajaran_id', $validated['tahun_ajaran_id'])
            ->where('hari', $validated['hari'])
            ->where('kelas_id', $validated['kelas_id'])
            ->whereHas('jamMulai', function ($q) use ($jamSelesai) {
                $q->where('jam_mulai', '<', $jamSelesai->jam_selesai);
            })
            ->whereHas('jamSelesai', function ($q) use ($jamMulai) {
                $q->where('jam_selesai', '>', $jamMulai->jam_mulai);
            })
            ->first();

        if ($bentrokKelas) {
            return back()->withInput()->withErrors([
                'kelas_id' => "Jadwal tidak dapat disimpan. Kelas {$bentrokKelas->kelas->nama_kelas} sudah memiliki jadwal pada {$validated['hari']} sesi {$bentrokKelas->jamMulai->nama_sesi} sampai {$bentrokKelas->jamSelesai->nama_sesi}.",
            ]);
        }

        try {
            DB::transaction(function () use ($validated, $range, $jadwal) {
                tahunAjaran::whereKey($validated['tahun_ajaran_id'])->lockForUpdate()->firstOrFail();
                $this->assertJadwalHasNoConflict($validated, $range, $jadwal->id);
                $jadwal->update($validated);
                DB::table('jadwal_pelajaran_sesi')->where('jadwal_pelajaran_id', $jadwal->id)->delete();
                $this->persistJadwalSessions($jadwal, $validated, $range);
            });
        } catch (QueryException $exception) {
            if (in_array((string) $exception->getCode(), ['23000', '23505'], true)) {
                return back()->withInput()->withErrors([
                    'jam_pelajaran_ids' => 'Jadwal tidak dapat diperbarui karena sesi yang dipilih baru saja digunakan oleh jadwal lain. Muat ulang jadwal lalu pilih sesi yang tersedia.',
                ]);
            }

            throw $exception;
        }

        return redirect()->route('waka.jadwal.data')
            ->with('success', 'Jadwal pelajaran berhasil diperbarui.');
    }

    public function destroyJadwal(JadwalPelajaran $jadwal): RedirectResponse
    {
        $jadwal->delete();

        return redirect()->route('waka.jadwal.data')
            ->with('success', 'Jadwal pelajaran berhasil dihapus.');
    }

    // ==========================================
    // MONITORING ABSENSI
    // ==========================================
    public function monitoringAbsensi(Request $request): View
    {
        $tanggal = $request->input('tanggal', now()->toDateString());
        $carbonDate = Carbon::parse($tanggal);

        $mapHari = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];
        $hariPilihan = $mapHari[$carbonDate->format('l')] ?? 'Senin';

        $tahunAktif = tahunAjaran::where('status', true)->first();

        $query = JadwalPelajaran::with([
            'guru.user',
            'kelas',
            'mataPelajaran',
            'jamMulai',
            'jamSelesai',
            'absensi' => function ($q) use ($tanggal) {
                $q->whereDate('tanggal', $tanggal);
            }
        ])
        ->where('hari', $hariPilihan);

        if ($tahunAktif) {
            $query->where('tahun_ajaran_id', $tahunAktif->id);
        }

        if ($request->filled('guru_id')) {
            $query->where('guru_id', $request->guru_id);
        }
        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }
        if ($request->filled('mata_pelajaran_id')) {
            $query->where('mata_pelajaran_id', $request->mata_pelajaran_id);
        }

        $semuaJadwalHari = $query
            ->orderBy(JamPelajaran::select('jam_mulai')
                ->whereColumn('jam_pelajaran.id', 'jadwal_pelajaran.jam_pelajaran_mulai_id'))
            ->get();
        $this->attachMeetingAttendance($semuaJadwalHari, $tanggal);

        // Filter status (OR): Waka dapat memilih satu atau beberapa status sekaligus.
        $statusValid = ['hadir', 'terlambat', 'izin_sakit', 'belum'];
        $statusFilter = array_values(array_intersect(
            (array) $request->input('status', []),
            $statusValid
        ));
        if ($statusFilter) {
            $semuaJadwalHari = $semuaJadwalHari->filter(function ($item) use ($statusFilter) {
                $absen = $item->absensi->first();
                $statusItem = !$absen
                    ? 'belum'
                    : (in_array($absen->status, ['izin', 'sakit'], true) ? 'izin_sakit' : $absen->status);

                return in_array($statusItem, $statusFilter, true);
            })->values();
        }

        // Hitung statistik dari data yang sudah difilter
        $totalJadwal = $semuaJadwalHari->count();
        $sudahCheckIn = 0;
        $terlambatCount = 0;
        $izinSakitCount = 0;
        $belumCheckInCount = 0;

        foreach ($semuaJadwalHari as $j) {
            $absen = $j->absensi->first();
            if ($absen) {
                if ($absen->status === 'hadir') {
                    $sudahCheckIn++;
                } elseif ($absen->status === 'terlambat') {
                    $sudahCheckIn++;
                    $terlambatCount++;
                } elseif (in_array($absen->status, ['izin', 'sakit'])) {
                    $izinSakitCount++;
                }
            } else {
                $belumCheckInCount++;
            }
        }

        $daftarGuru = guru::with('user')->get();
        $daftarKelas = Kelas::all();
        $daftarMapel = MataPelajaran::all();

        return view('waka.absensi.monitoring', compact(
            'semuaJadwalHari',
            'tanggal',
            'hariPilihan',
            'totalJadwal',
            'sudahCheckIn',
            'terlambatCount',
            'izinSakitCount',
            'belumCheckInCount',
            'daftarGuru',
            'daftarKelas',
            'daftarMapel',
            'statusFilter'
        ));
    }

    public function cetakLaporan(Request $request): View
    {
        $tanggal = $request->input('tanggal', now()->toDateString());
        $carbonDate = Carbon::parse($tanggal);

        $mapHari = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];
        $hariPilihan = $mapHari[$carbonDate->format('l')] ?? 'Senin';

        $tahunAktif = tahunAjaran::where('status', true)->first();
        $settingSekolah = SekolahSetting::getSetting();

        $query = JadwalPelajaran::with([
            'guru.user',
            'kelas',
            'mataPelajaran',
            'jamMulai',
            'jamSelesai',
            'absensi' => function ($q) use ($tanggal) {
                $q->whereDate('tanggal', $tanggal);
            }
        ])
        ->where('hari', $hariPilihan);

        if ($tahunAktif) {
            $query->where('tahun_ajaran_id', $tahunAktif->id);
        }

        $filterGuruNama = 'Semua Guru';
        if ($request->filled('guru_id')) {
            $query->where('guru_id', $request->guru_id);
            $selectedGuru = guru::with('user')->find($request->guru_id);
            if ($selectedGuru && $selectedGuru->user) {
                $filterGuruNama = $selectedGuru->user->name;
            }
        }

        $filterKelasNama = 'Semua Kelas';
        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
            $selectedKelas = Kelas::find($request->kelas_id);
            if ($selectedKelas) {
                $filterKelasNama = $selectedKelas->nama_kelas;
            }
        }

        $filterMapelNama = 'Semua Mata Pelajaran';
        if ($request->filled('mata_pelajaran_id')) {
            $query->where('mata_pelajaran_id', $request->mata_pelajaran_id);
            $selectedMapel = MataPelajaran::find($request->mata_pelajaran_id);
            if ($selectedMapel) {
                $filterMapelNama = $selectedMapel->nama;
            }
        }

        $semuaJadwalHari = $query
            ->orderBy(JamPelajaran::select('jam_mulai')
                ->whereColumn('jam_pelajaran.id', 'jadwal_pelajaran.jam_pelajaran_mulai_id'))
            ->get();
        $this->attachMeetingAttendance($semuaJadwalHari, $tanggal);

        // Filter status jika dipilih
        $statusValid = ['hadir', 'terlambat', 'izin_sakit', 'belum'];
        $rawStatus = $request->input('status', []);
        $statusFilter = array_values(array_intersect(
            (array) $rawStatus,
            $statusValid
        ));

        $filterStatusNama = 'Semua Status';
        if (!empty($statusFilter)) {
            $statusLabels = [
                'hadir' => 'Hadir (Tepat Waktu)',
                'terlambat' => 'Terlambat',
                'izin_sakit' => 'Izin / Sakit',
                'belum' => 'Belum Check-In'
            ];
            $filterStatusNama = implode(', ', array_map(fn ($s) => $statusLabels[$s] ?? $s, $statusFilter));

            $semuaJadwalHari = $semuaJadwalHari->filter(function ($item) use ($statusFilter) {
                $absen = $item->absensi->first();
                $statusItem = !$absen
                    ? 'belum'
                    : (in_array($absen->status, ['izin', 'sakit'], true) ? 'izin_sakit' : $absen->status);

                return in_array($statusItem, $statusFilter, true);
            })->values();
        }

        // Hitung statistik dari data yang sudah difilter
        $totalJadwal = $semuaJadwalHari->count();
        $sudahCheckIn = 0;
        $terlambatCount = 0;
        $izinSakitCount = 0;
        $belumCheckInCount = 0;

        foreach ($semuaJadwalHari as $j) {
            $absen = $j->absensi->first();
            if ($absen) {
                if ($absen->status === 'hadir') {
                    $sudahCheckIn++;
                } elseif ($absen->status === 'terlambat') {
                    $sudahCheckIn++;
                    $terlambatCount++;
                } elseif (in_array($absen->status, ['izin', 'sakit'])) {
                    $izinSakitCount++;
                }
            } else {
                $belumCheckInCount++;
            }
        }

        $isFiltered = $request->filled('guru_id') ||
                      $request->filled('kelas_id') ||
                      $request->filled('mata_pelajaran_id') ||
                      !empty($statusFilter);

        $daftarGuru = guru::with('user')->get();
        $daftarKelas = Kelas::orderBy('nama_kelas')->get();
        $daftarMapel = MataPelajaran::orderBy('nama')->get();
        $wakaUser = Auth::user();

        return view('waka.absensi.cetak', compact(
            'semuaJadwalHari',
            'tanggal',
            'hariPilihan',
            'totalJadwal',
            'sudahCheckIn',
            'terlambatCount',
            'izinSakitCount',
            'belumCheckInCount',
            'settingSekolah',
            'tahunAktif',
            'filterGuruNama',
            'filterKelasNama',
            'filterMapelNama',
            'filterStatusNama',
            'statusFilter',
            'isFiltered',
            'daftarGuru',
            'daftarKelas',
            'daftarMapel',
            'wakaUser'
        ));
    }

    // ==========================================
    // PERSETUJUAN IZIN / SAKIT
    // ==========================================
    public function dataIzin(Request $request): View
    {
        $izin = IzinGuru::with(['guru.user', 'disetujuiOleh'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('waka.izin.data', compact('izin'));
    }

    public function buktiIzin(IzinGuru $izin): Response
    {
        return $this->serveBuktiIzin($izin);
    }

    public function updateStatusIzin(Request $request, IzinGuru $izin): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['disetujui', 'ditolak'])],
            'catatan_waka' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated, $izin) {
            $izin->update([
                'status' => $validated['status'],
                'catatan_waka' => $validated['catatan_waka'],
                'disetujui_oleh' => Auth::id(),
            ]);

            // Jika disetujui, buat absensi otomatis untuk rentang tanggal tersebut
            if ($validated['status'] === 'disetujui') {
                $mulai = Carbon::parse($izin->tanggal_mulai);
                $selesai = Carbon::parse($izin->tanggal_selesai);

                $mapHari = [
                    'Sunday' => 'Minggu',
                    'Monday' => 'Senin',
                    'Tuesday' => 'Selasa',
                    'Wednesday' => 'Rabu',
                    'Thursday' => 'Kamis',
                    'Friday' => 'Jumat',
                    'Saturday' => 'Sabtu',
                ];

                for ($date = $mulai->copy(); $date->lte($selesai); $date->addDay()) {
                    $hari = $mapHari[$date->format('l')] ?? null;
                    if (!$hari) continue;

                    $jadwals = JadwalPelajaran::where('guru_id', $izin->guru_id)
                        ->where('hari', $hari)
                        ->get();

                    foreach ($jadwals as $jadwal) {
                        AbsensiGuru::updateOrCreate(
                            [
                                'jadwal_pelajaran_id' => $jadwal->id,
                                'tanggal' => $date->toDateString(),
                            ],
                            [
                                'guru_id' => $izin->guru_id,
                                'waktu_absen' => '00:00:00',
                                'status' => $izin->jenis, // 'izin' atau 'sakit'
                                'keterangan' => 'Pengajuan ' . ucfirst($izin->jenis) . ': ' . $izin->alasan,
                            ]
                        );
                    }
                }
            }
        });

        return redirect()->route('waka.izin.data')
            ->with('success', 'Status pengajuan izin/sakit berhasil diperbarui.');
    }

    // ==========================================
    // PROFIL PENGGUNA (WAKA)
    // ==========================================
    public function profile(): View
    {
        $user = Auth::user();
        return view('waka.profile', compact('user'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.min' => 'Password baru minimal 8 karakter.',
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $userData['password'] = $validated['password'];
        }

        $user->update($userData);

        return redirect()->route('waka.profile')
            ->with('success', 'Profil Waka Kurikulum berhasil diperbarui.');
    }
}
