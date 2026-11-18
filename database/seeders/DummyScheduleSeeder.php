<?php

namespace Database\Seeders;

use App\Models\guru;
use App\Models\JadwalPelajaran;
use App\Models\JamPelajaran;
use App\Models\KegiatanSekolah;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\tahunAjaran;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DummyScheduleSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            if (JadwalPelajaran::query()->exists()) {
                throw new RuntimeException('Jadwal belum kosong. Seeder dummy berhenti agar jadwal yang sudah ada tidak tertimpa.');
            }

            $tahunAjaran = tahunAjaran::where('status', true)->first();
            $guruList = guru::with('user')->orderBy('id')->get();
            $kelasList = Kelas::orderBy('nama_kelas')->get();
            $mapelList = MataPelajaran::orderBy('id')->get();
            $semuaSesi = JamPelajaran::where('jenis', 'pelajaran')
                ->orderBy('urutan')
                ->orderBy('jam_mulai')
                ->get();

            if (!$tahunAjaran || $guruList->count() < $kelasList->count() || $mapelList->isEmpty() || $semuaSesi->isEmpty()) {
                throw new RuntimeException('Data master belum cukup untuk membuat jadwal dummy. Pastikan ada tahun ajaran aktif, guru, kelas, mata pelajaran, dan jam pelajaran.');
            }

            $daftarHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
            $aktivitas = KegiatanSekolah::where('tahun_ajaran_id', $tahunAjaran->id)
                ->whereIn('hari', $daftarHari)
                ->get()
                ->groupBy('hari');

            $jumlahPertemuan = 0;
            $jumlahDetailSesi = 0;
            $now = now();

            foreach ($kelasList as $kelasIndex => $kelas) {
                foreach ($daftarHari as $hariIndex => $hari) {
                    $sesiHari = $hari === 'Jumat' ? $semuaSesi->take(4)->values() : $semuaSesi->values();
                    $slotBebas = $sesiHari->filter(function ($sesi) use ($aktivitas, $hari) {
                        return !$aktivitas->get($hari, collect())->contains(fn ($kegiatan) =>
                            $kegiatan->jam_mulai < $sesi->jam_selesai &&
                            $kegiatan->jam_selesai > $sesi->jam_mulai
                        );
                    })->values();

                    $groupedSlots = collect();
                    if ($hari === 'Senin' && $slotBebas->count() >= 3) {
                        $awal = $slotBebas->take(3);
                        // Contoh satu pertemuan multi-sesi, termasuk ketika melintasi istirahat.
                        $this->createMeeting(
                            $tahunAjaran->id,
                            $kelas->id,
                            $guruList[($kelasIndex + $hariIndex) % $guruList->count()],
                            $mapelList[($kelasIndex + $hariIndex) % $mapelList->count()],
                            $hari,
                            $awal,
                            $now
                        );
                        $jumlahPertemuan++;
                        $jumlahDetailSesi += $awal->count();
                        $groupedSlots = $awal->pluck('id');
                    }

                    foreach ($slotBebas as $slotIndex => $sesi) {
                        if ($groupedSlots->contains($sesi->id)) {
                            continue;
                        }

                        // Leave a rotating set of openings so Waka can test adding schedules.
                        if (($kelasIndex + $hariIndex + $slotIndex) % 5 === 0) {
                            continue;
                        }

                        $guru = $guruList[($kelasIndex + $hariIndex + $slotIndex) % $guruList->count()];
                        $mapel = $mapelList[($kelasIndex * 3 + $hariIndex + $slotIndex) % $mapelList->count()];
                        $this->createMeeting(
                            $tahunAjaran->id,
                            $kelas->id,
                            $guru,
                            $mapel,
                            $hari,
                            collect([$sesi]),
                            $now
                        );
                        $jumlahPertemuan++;
                        $jumlahDetailSesi++;
                    }
                }
            }

            $this->command?->info("Jadwal dummy berhasil dibuat: {$jumlahPertemuan} pertemuan, {$jumlahDetailSesi} detail sesi, Senin–Jumat.");
        });
    }

    private function createMeeting(int $tahunAjaranId, int $kelasId, guru $guru, MataPelajaran $mapel, string $hari, $sesi, $now): void
    {
        $awal = $sesi->first();
        $akhir = $sesi->last();
        $jadwal = JadwalPelajaran::create([
            'tahun_ajaran_id' => $tahunAjaranId,
            'guru_id' => $guru->id,
            'kelas_id' => $kelasId,
            'mata_pelajaran_id' => $mapel->id,
            'hari' => $hari,
            'jam_pelajaran_mulai_id' => $awal->id,
            'jam_pelajaran_selesai_id' => $akhir->id,
        ]);

        DB::table('jadwal_pelajaran_sesi')->insert($sesi->map(fn ($slot) => [
            'jadwal_pelajaran_id' => $jadwal->id,
            'jam_pelajaran_id' => $slot->id,
            'tahun_ajaran_id' => $tahunAjaranId,
            'guru_id' => $guru->id,
            'kelas_id' => $kelasId,
            'hari' => $hari,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all());
    }
}
