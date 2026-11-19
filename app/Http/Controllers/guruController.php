<?php

namespace App\Http\Controllers;

use App\Models\AbsensiGuru;
use App\Models\guru;
use App\Models\IzinGuru;
use App\Models\JadwalPelajaran;
use App\Models\JamPelajaran;
use App\Models\KegiatanSekolah;
use App\Models\SekolahSetting;
use App\Models\tahunAjaran;
use App\Support\AbsenceBlockResolver;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class guruController extends Controller
{
    private function getGuruProfile(): guru
    {
        $user = Auth::user();
        return guru::firstOrCreate(
            ['user_id' => $user->id],
            ['nip' => null, 'no_hp' => null]
        );
    }

    // ==========================================
    // DASHBOARD & ABSENSI HARI INI
    // ==========================================
    public function dashboard(): View
    {
        $guru = $this->getGuruProfile();
        $today = now()->toDateString();
        $currentTime = now()->format('H:i:s');

        $mapHari = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];
        $hariIni = $mapHari[now()->format('l')] ?? 'Senin';

        $tahunAktif = tahunAjaran::where('status', true)->first();
        $isPublished = $tahunAktif ? (bool) $tahunAktif->is_published : true;
        $setting = SekolahSetting::getSetting();

        // Cek izin aktif hari ini
        $izinAktif = IzinGuru::where('guru_id', $guru->id)
            ->where('status', 'disetujui')
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today)
            ->first();

        $query = JadwalPelajaran::with(['kelas', 'mataPelajaran', 'jamMulai', 'jamSelesai', 'absensi' => function ($q) use ($today) {
            $q->whereDate('tanggal', $today);
        }])
        ->where('guru_id', $guru->id)
        ->whereIn('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'])
        ->where('hari', $hariIni);

        if ($tahunAktif) {
            $query->where('tahun_ajaran_id', $tahunAktif->id);
        }

        $jadwalHariIni = $query
            ->orderBy(JamPelajaran::select('jam_mulai')
                ->whereColumn('jam_pelajaran.id', 'jadwal_pelajaran.jam_pelajaran_mulai_id'))
            ->get();

        $absenceBlocks = AbsenceBlockResolver::resolve($jadwalHariIni);
        $schedulesById = $jadwalHariIni->keyBy('id');
        $absensiHariIni = AbsensiGuru::where('guru_id', $guru->id)
            ->whereDate('tanggal', $today)
            ->get()
            ->keyBy('jadwal_pelajaran_id');

        // Tambahkan metadata status realtime ke setiap item jadwal
        foreach ($jadwalHariIni as $j) {
            $blockIds = $absenceBlocks[$j->id] ?? [$j->id];
            $blockSchedules = collect($blockIds)
                ->map(fn ($id) => $schedulesById->get($id))
                ->filter()
                ->sortBy(fn ($schedule) => $schedule->jamMulai->urutan)
                ->values();
            $meetingStart = $blockSchedules->first()->jamMulai->jam_mulai;
            $meetingEnd = $blockSchedules->last()->jamSelesai->jam_selesai;
            $j->meeting_start = $meetingStart;
            $j->meeting_end = $meetingEnd;
            $absen = $absensiHariIni->first(fn ($record) => in_array($record->jadwal_pelajaran_id, $blockIds, true))
                ?? $j->absensi->first();
            $j->absen_record = $absen;

            if (!$isPublished) {
                $j->live_status = 'draft';
                $j->can_absen = false;
            } elseif ($absen) {
                $j->live_status = 'sudah_absen';
                $j->can_absen = false;
            } elseif ($izinAktif) {
                $j->live_status = 'izin_sakit';
                $j->can_absen = false;
            } else {
                if ($currentTime < $meetingStart) {
                    $j->live_status = 'belum_mulai';
                    $j->can_absen = false;
                } elseif ($currentTime <= $meetingEnd) {
                    $j->live_status = 'berlangsung';
                    $j->can_absen = true;

                    // Cek apakah sudah lewat toleransi keterlambatan
                    $toleransiMenit = $setting->toleransi_terlambat_menit ?? 15;
                    $batasToleransi = Carbon::parse($meetingStart)->addMinutes($toleransiMenit)->format('H:i:s');
                    $j->is_late = ($currentTime > $batasToleransi);
                } else {
                    // Sesi pelajaran sudah lewat/habis
                    $j->live_status = 'habis';
                    $j->can_absen = false;
                }
            }
        }

        $jadwalBerlangsung = $jadwalHariIni->filter(fn ($j) => $j->live_status === 'berlangsung');
        $jadwalHariIni = $jadwalBerlangsung
            ->concat($jadwalHariIni->reject(fn ($j) => $j->live_status === 'berlangsung'))
            ->values();

        $jadwalUtama = $isPublished
            ? ($jadwalHariIni->firstWhere('live_status', 'berlangsung')
                ?? $jadwalHariIni->first(fn ($j) => $j->jamMulai->jam_mulai > $currentTime)
                ?? $jadwalHariIni->last())
            : $jadwalHariIni->first();

        // Statistik singkat guru
        $totalJadwalHariIni = $jadwalHariIni->count();
        $totalSudahAbsen = $jadwalHariIni->filter(fn ($j) => $j->absen_record !== null)->count();
        $jadwalAktifSekarang = $jadwalHariIni->firstWhere('live_status', 'berlangsung');

        return view('guru.dashboard', compact(
            'guru',
            'hariIni',
            'today',
            'currentTime',
            'tahunAktif',
            'isPublished',
            'setting',
            'jadwalHariIni',
            'jadwalUtama',
            'izinAktif',
            'totalJadwalHariIni',
            'totalSudahAbsen',
            'jadwalAktifSekarang'
        ));
    }

    // ==========================================
    // PROSES SIMPAN ABSENSI DENGAN GPS
    // ==========================================
    public function storeAbsen(Request $request): RedirectResponse
    {
        $guru = $this->getGuruProfile();
        $setting = SekolahSetting::getSetting();
        $today = now()->toDateString();
        $currentTime = now()->format('H:i:s');

        $validated = $request->validate([
            'jadwal_pelajaran_id' => ['required', 'exists:jadwal_pelajaran,id'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'materi_diajarkan' => ['required', 'string', 'max:1000'],
            'catatan_siswa_tidak_masuk' => ['nullable', 'string', 'max:1000'],
        ]);

        $jadwal = JadwalPelajaran::with(['jamMulai', 'jamSelesai', 'tahunAjaran'])->findOrFail($validated['jadwal_pelajaran_id']);

        // Validasi publikasi jadwal
        if ($jadwal->tahunAjaran && !$jadwal->tahunAjaran->is_published) {
            return back()->withErrors(['absensi' => 'Jadwal pelajaran saat ini masih berstatus draft dan belum dipublikasikan oleh Waka Kurikulum. Absensi belum dapat dilakukan.']);
        }

        // Validasi kepemilikan jadwal
        if ($jadwal->guru_id !== $guru->id) {
            return back()->withErrors(['absensi' => 'Jadwal pelajaran ini bukan milik akun Anda.']);
        }

        // Validasi hari
        $mapHari = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];
        $hariIni = $mapHari[now()->format('l')] ?? 'Senin';
        if ($jadwal->hari !== $hariIni) {
            return back()->withErrors(['absensi' => "Jadwal ini untuk hari {$jadwal->hari}, bukan hari {$hariIni}."]);
        }

        // Absensi berlaku untuk sesi mapel yang sama, termasuk jeda istirahat di antaranya.
        $jadwalDalamHari = JadwalPelajaran::query()
            ->where('tahun_ajaran_id', $jadwal->tahun_ajaran_id)
            ->where('guru_id', $jadwal->guru_id)
            ->where('kelas_id', $jadwal->kelas_id)
            ->where('mata_pelajaran_id', $jadwal->mata_pelajaran_id)
            ->where('hari', $jadwal->hari)
            ->get();
        $blockIds = AbsenceBlockResolver::resolve($jadwalDalamHari)[$jadwal->id] ?? [$jadwal->id];
        $blockSchedules = $jadwalDalamHari
            ->whereIn('id', $blockIds)
            ->sortBy(fn ($schedule) => $schedule->jamMulai->urutan)
            ->values();
        $meetingStart = $blockSchedules->first()->jamMulai->jam_mulai;
        $meetingEnd = $blockSchedules->last()->jamSelesai->jam_selesai;

        if ($currentTime < $meetingStart) {
            return back()->withErrors(['absensi' => 'Sesi pelajaran belum dimulai. Absensi dapat dilakukan setelah jam mulai.']);
        }

        if ($currentTime > $meetingEnd) {
            return back()->withErrors(['absensi' => 'Rangkaian sesi pelajaran telah selesai. Tombol absensi sudah tidak dapat digunakan.']);
        }

        $sudahAda = AbsensiGuru::whereIn('jadwal_pelajaran_id', $blockIds)
            ->whereDate('tanggal', $today)
            ->exists();

        if ($sudahAda) {
            return back()->with('info', 'Anda sudah melakukan absensi untuk rangkaian sesi mata pelajaran ini.');
        }

        // Hitung jarak GPS menggunakan formula Haversine
        $jarakMeter = $this->calculateDistance(
            $validated['latitude'],
            $validated['longitude'],
            $setting->latitude,
            $setting->longitude
        );

        $radiusAllowed = $setting->radius_meter ?? 100;
        if ($jarakMeter > $radiusAllowed) {
            return back()->withErrors([
                'absensi' => "Lokasi Anda berada di luar radius sekolah ({$jarakMeter} meter dari sekolah, batas maksimal: {$radiusAllowed} meter). Pastikan Anda berada di area sekolah dan GPS aktif.",
            ]);
        }

        // Tentukan status kehadiran (tepat waktu atau terlambat)
        $toleransiMenit = $setting->toleransi_terlambat_menit ?? 15;
        $batasToleransi = Carbon::parse($meetingStart)->addMinutes($toleransiMenit)->format('H:i:s');
        $status = ($currentTime > $batasToleransi) ? 'terlambat' : 'hadir';

        AbsensiGuru::create([
            'jadwal_pelajaran_id' => $jadwal->id,
            'guru_id' => $guru->id,
            'tanggal' => $today,
            'waktu_absen' => $currentTime,
            'status' => $status,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'jarak_meter' => $jarakMeter,
            'materi_diajarkan' => $validated['materi_diajarkan'],
            'catatan_siswa_tidak_masuk' => $validated['catatan_siswa_tidak_masuk'] ?? null,
            'keterangan' => $status === 'terlambat' ? 'Check-in lewat batas toleransi' : 'Check-in tepat waktu',
        ]);

        $pesanStatus = ($status === 'terlambat')
            ? 'Absensi berhasil dicatat (Status: Terlambat).'
            : 'Absensi berhasil dicatat tepat waktu. Selamat mengajar!';

        return redirect()->route('guru.dashboard')->with('success', $pesanStatus);
    }

    // ==========================================
    // JADWAL MENGAJAR MINGGUAN GURU
    // ==========================================
    public function jadwalMingguan(): View
    {
        $guru = $this->getGuruProfile();
        $tahunAktif = tahunAjaran::where('status', true)->first();
        $isPublished = $tahunAktif ? (bool) $tahunAktif->is_published : true;

        $query = JadwalPelajaran::with(['kelas', 'mataPelajaran', 'jamMulai', 'jamSelesai'])
            ->where('guru_id', $guru->id)
            ->whereIn('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']);

        if ($tahunAktif) {
            $query->where('tahun_ajaran_id', $tahunAktif->id);
        }

        $semuaJadwal = $query->orderByRaw("CASE hari WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 ELSE 6 END")
            ->orderBy(JamPelajaran::select('jam_mulai')
                ->whereColumn('jam_pelajaran.id', 'jadwal_pelajaran.jam_pelajaran_mulai_id'))
            ->get()
            ->groupBy('hari');

        $kegiatanMingguan = KegiatanSekolah::query()
            ->when($tahunAktif, fn ($q) => $q->where('tahun_ajaran_id', $tahunAktif->id))
            ->orderByRaw("CASE hari WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 ELSE 6 END")
            ->orderBy('jam_mulai')
            ->get()
            ->groupBy('hari');

        $agendaMingguan = collect();
        foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $hari) {
            $agendaHari = $kegiatanMingguan->get($hari, collect())->map(fn ($kegiatan) => [
                'type' => 'kegiatan',
                'jam_mulai' => $kegiatan->jam_mulai,
                'jam_selesai' => $kegiatan->jam_selesai,
                'data' => $kegiatan,
            ])->concat($semuaJadwal->get($hari, collect())->map(fn ($jadwal) => [
                'type' => 'jadwal',
                'jam_mulai' => $jadwal->jamMulai->jam_mulai,
                'jam_selesai' => $jadwal->jamSelesai->jam_selesai,
                'data' => $jadwal,
            ]));

            $agendaMingguan->put($hari, $agendaHari->sortBy('jam_mulai')->values());
        }

        return view('guru.jadwal', compact('guru', 'tahunAktif', 'isPublished', 'semuaJadwal', 'kegiatanMingguan', 'agendaMingguan'));
    }

    // ==========================================
    // RIWAYAT ABSENSI
    // ==========================================
    public function riwayatAbsensi(Request $request): View
    {
        $guru = $this->getGuruProfile();

        $riwayat = AbsensiGuru::with(['jadwalPelajaran.kelas', 'jadwalPelajaran.mataPelajaran', 'jadwalPelajaran.jamMulai', 'jadwalPelajaran.jamSelesai'])
            ->where('guru_id', $guru->id)
            ->when($request->filled('tanggal'), fn ($q) => $q->whereDate('tanggal', $request->tanggal))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('tanggal')
            ->latest('waktu_absen')
            ->paginate(15)
            ->withQueryString();

        return view('guru.riwayat', compact('riwayat'));
    }

    // ==========================================
    // PENGAJUAN IZIN / SAKIT
    // ==========================================
    public function izin(): View
    {
        $guru = $this->getGuruProfile();

        $daftarIzin = IzinGuru::where('guru_id', $guru->id)
            ->latest('id')
            ->paginate(10);

        return view('guru.izin', compact('guru', 'daftarIzin'));
    }

    public function storeIzin(Request $request): RedirectResponse
    {
        $guru = $this->getGuruProfile();

        $validated = $request->validate([
            'jenis' => ['required', Rule::in(['izin', 'sakit'])],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'alasan' => ['required', 'string', 'max:1000'],
            'bukti_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:3072'],
        ]);

        $filePath = null;
        if ($request->hasFile('bukti_file')) {
            $filePath = $request->file('bukti_file')->store('bukti_izin', 'public');
        }

        IzinGuru::create([
            'guru_id' => $guru->id,
            'jenis' => $validated['jenis'],
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'alasan' => $validated['alasan'],
            'bukti_file' => $filePath,
            'status' => 'menunggu',
        ]);

        return redirect()->route('guru.izin')
            ->with('success', 'Pengajuan izin/sakit berhasil dikirim dan sedang menunggu persetujuan Waka.');
    }

    public function buktiIzin(IzinGuru $izin): Response
    {
        $guru = $this->getGuruProfile();
        abort_if($izin->guru_id !== $guru->id, 403, 'Anda tidak memiliki akses ke lampiran ini.');

        return $this->serveBuktiIzin($izin);
    }

    // ==========================================
    // FORMULA HAVERSINE (JARAK DALAM METER)
    // ==========================================
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): int
    {
        $earthRadius = 6371000; // Radius bumi dalam meter

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return (int) round($earthRadius * $c);
    }

    // ==========================================
    // PROFIL PENGGUNA (GURU)
    // ==========================================
    public function profile(): View
    {
        $user = Auth::user();
        $guru = $user->guru;

        $mapelDiampu = collect();
        $totalJamMengajar = 0;

        if ($guru) {
            $jadwalList = JadwalPelajaran::where('guru_id', $guru->id)
                ->with(['mataPelajaran', 'kelas'])
                ->get();

            $mapelDiampu = $jadwalList->pluck('mataPelajaran.nama')->filter()->unique()->values();
            $totalJamMengajar = $jadwalList->count();
        }

        return view('guru.profile', compact('user', 'guru', 'mapelDiampu', 'totalJamMengajar'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $guru = $user->guru;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'nip' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('guru', 'nip')->ignore($guru?->id),
            ],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'nip.unique' => 'NIP sudah terdaftar untuk guru lain.',
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $userData['password'] = $validated['password'];
        }

        $user->update($userData);

        if ($guru) {
            $guru->update([
                'nip' => $validated['nip'] ?? $guru->nip,
                'no_hp' => $validated['no_hp'],
            ]);
        }

        return redirect()->route('guru.profile')
            ->with('success', 'Profil Anda berhasil diperbarui.');
    }
}
