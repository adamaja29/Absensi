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
            $mapelList = MataPelajaran::orderBy('id')->get()->keyBy('kode');
            $jamList = JamPelajaran::where('jenis', 'pelajaran')->orderBy('urutan')->get()->keyBy('nama_sesi');

            if (!$tahunAjaran || $guruList->count() < $kelasList->count() || $mapelList->isEmpty() || $jamList->isEmpty()) {
                throw new RuntimeException('Data master belum cukup untuk membuat jadwal dummy. Pastikan ada tahun ajaran aktif, guru, kelas, mata pelajaran, dan jam pelajaran.');
            }

            // Pemetaan guru pengampu (1 guru hanya mengampu 1 maksimal 2 mapel)
            $guruMapelMap = [
                'guru@gmail.com'    => ['MTK'],
                'andi@gmail.com'    => ['INF'],
                'citra@gmail.com'   => ['BIN'],
                'dedi@gmail.com'    => ['BIG'],
                'eka@gmail.com'     => ['PPKN', 'SEJ'],
                'fajar@gmail.com'   => ['PAI'],
                'gina@gmail.com'    => ['PJOK'],
                'hendra@gmail.com'  => ['SBD'],
                'intan@gmail.com'   => ['BK', 'IPAS'],
                'joko@gmail.com'    => ['PPL', 'PWEB'],
                'kartika@gmail.com' => ['PBO', 'BD'],
                'lukman@gmail.com'  => ['UIUX'],
                'maria@gmail.com'   => ['DJK', 'AJJ'],
                'nadia@gmail.com'   => ['KJS', 'SRV'],
                'oki@gmail.com'     => ['LJK'],
                'putri@gmail.com'   => ['AKD', 'KOM'],
                'qori@gmail.com'    => ['AKP', 'AKDAG'],
                'rian@gmail.com'    => ['PAJAK'],
                'siti@gmail.com'    => ['DKV', 'GRAF'],
                'taufik@gmail.com'  => ['FOTO', 'VID'],
                'utami@gmail.com'   => ['ANIM'],
                'wahyu@gmail.com'   => ['MTK'],
                'yuni@gmail.com'    => ['BIG'],
                'zainal@gmail.com'  => ['BIN'],
            ];

            $guruByEmail = [];
            foreach ($guruList as $g) {
                if ($g->user?->email) {
                    $guruByEmail[$g->user->email] = $g;
                }
            }

            $mapelToGuru = [];
            foreach ($guruMapelMap as $email => $kodes) {
                foreach ($kodes as $kode) {
                    if (isset($guruByEmail[$email])) {
                        $mapelToGuru[$kode][] = $guruByEmail[$email];
                    }
                }
            }

            $jurusanMapel = [
                'RPL' => ['PPL', 'PWEB', 'PBO', 'BD', 'UIUX'],
                'TKJ' => ['DJK', 'AJJ', 'LJK', 'KJS', 'SRV'],
                'AKL' => ['AKD', 'AKP', 'AKDAG', 'KOM', 'PAJAK'],
                'DKV' => ['DKV', 'GRAF', 'FOTO', 'VID', 'ANIM'],
            ];

            $mapelUmumKodes = ['PAI', 'PPKN', 'BIN', 'BIG', 'MTK', 'SEJ', 'PJOK', 'SBD', 'INF', 'IPAS', 'BK'];
            $hariSekolah = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

            $busyTeachers = [];
            $teacherHours = [];
            foreach ($guruList as $g) {
                $teacherHours[$g->id] = 0;
            }

            $jumlahPertemuan = 0;
            $jumlahDetailSesi = 0;

            foreach ($kelasList as $kelasIndex => $kelas) {
                $jurusan = $kelas->jurusan ?? 'RPL';
                $jurusanKodes = $jurusanMapel[$jurusan] ?? $jurusanMapel['RPL'];

                $curriculum = [];
                foreach ($jurusanKodes as $jk) {
                    for ($i = 0; $i < 3; $i++) {
                        $curriculum[] = $jk;
                    }
                }
                foreach ($mapelUmumKodes as $uk) {
                    $curriculum[] = $uk;
                    $curriculum[] = $uk;
                }
                $curriculum[] = $jurusanKodes[0];
                $curriculum[] = $jurusanKodes[1];
                $curriculum[] = 'MTK';

                $shift = ($kelasIndex * 7) % count($curriculum);
                $curriculum = array_merge(array_slice($curriculum, $shift), array_slice($curriculum, 0, $shift));

                $currIndex = 0;
                foreach ($hariSekolah as $hari) {
                    $jumlahSesi = $hari === 'Jumat' ? 4 : 9;
                    for ($sesiIndex = 0; $sesiIndex < $jumlahSesi; $sesiIndex++) {
                        $jam = $jamList->get('Sesi '.($sesiIndex + 1));
                        if (!$jam) {
                            continue;
                        }

                        $assignedMapelKode = null;
                        $selectedGuru = null;

                        $attempts = 0;
                        $maxAttempts = count($curriculum);

                        while ($attempts < $maxAttempts && !empty($curriculum)) {
                            $candidateKode = $curriculum[$currIndex % count($curriculum)];
                            $candidates = $mapelToGuru[$candidateKode] ?? [];

                            usort($candidates, fn ($a, $b) => ($teacherHours[$a->id] ?? 0) <=> ($teacherHours[$b->id] ?? 0));

                            foreach ($candidates as $candidateGuru) {
                                if (empty($busyTeachers[$hari][$sesiIndex][$candidateGuru->id])) {
                                    $selectedGuru = $candidateGuru;
                                    $assignedMapelKode = $candidateKode;
                                    break;
                                }
                            }

                            if ($selectedGuru !== null) {
                                array_splice($curriculum, $currIndex % count($curriculum), 1);
                                break;
                            }

                            $currIndex++;
                            $attempts++;
                        }

                        if ($selectedGuru === null) {
                            $fallbackKodes = array_merge($jurusanKodes, $mapelUmumKodes);
                            shuffle($fallbackKodes);
                            foreach ($fallbackKodes as $fbKode) {
                                $candidates = $mapelToGuru[$fbKode] ?? [];
                                foreach ($candidates as $candidateGuru) {
                                    if (empty($busyTeachers[$hari][$sesiIndex][$candidateGuru->id])) {
                                        $selectedGuru = $candidateGuru;
                                        $assignedMapelKode = $fbKode;
                                        break 2;
                                    }
                                }
                            }
                        }

                        if ($selectedGuru !== null && $assignedMapelKode !== null && isset($mapelList[$assignedMapelKode])) {
                            $busyTeachers[$hari][$sesiIndex][$selectedGuru->id] = true;
                            $teacherHours[$selectedGuru->id] = ($teacherHours[$selectedGuru->id] ?? 0) + 1;

                            $jadwal = JadwalPelajaran::create([
                                'tahun_ajaran_id' => $tahunAjaran->id,
                                'kelas_id' => $kelas->id,
                                'guru_id' => $selectedGuru->id,
                                'mata_pelajaran_id' => $mapelList[$assignedMapelKode]->id,
                                'hari' => $hari,
                                'jam_pelajaran_mulai_id' => $jam->id,
                                'jam_pelajaran_selesai_id' => $jam->id,
                            ]);

                            DB::table('jadwal_pelajaran_sesi')->insert([
                                'jadwal_pelajaran_id' => $jadwal->id,
                                'jam_pelajaran_id' => $jam->id,
                                'tahun_ajaran_id' => $tahunAjaran->id,
                                'guru_id' => $selectedGuru->id,
                                'kelas_id' => $kelas->id,
                                'hari' => $hari,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);

                            $jumlahPertemuan++;
                            $jumlahDetailSesi++;
                        }
                    }
                }
            }

            $this->command?->info("Jadwal dummy berhasil dibuat: {$jumlahPertemuan} pertemuan, {$jumlahDetailSesi} detail sesi, Senin–Jumat.");
        });
    }
}
