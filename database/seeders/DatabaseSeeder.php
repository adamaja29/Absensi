<?php

namespace Database\Seeders;

use App\Models\guru;
use App\Models\JadwalPelajaran;
use App\Models\JamPelajaran;
use App\Models\KegiatanSekolah;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\SekolahSetting;
use App\Models\tahunAjaran;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Admin Sekolah', 'email' => 'admin@gmail.com', 'role' => 'admin'],
            ['name' => 'Budi Santoso', 'email' => 'guru@gmail.com', 'role' => 'guru'],
            ['name' => 'Andi Saputra', 'email' => 'andi@gmail.com', 'role' => 'guru'],
            ['name' => 'Waka Kurikulum', 'email' => 'waka@gmail.com', 'role' => 'waka'],
            ['name' => 'Citra Lestari', 'email' => 'citra@gmail.com', 'role' => 'guru'],
            ['name' => 'Dedi Kurniawan', 'email' => 'dedi@gmail.com', 'role' => 'guru'],
            ['name' => 'Eka Pratiwi', 'email' => 'eka@gmail.com', 'role' => 'guru'],
            ['name' => 'Fajar Nugroho', 'email' => 'fajar@gmail.com', 'role' => 'guru'],
            ['name' => 'Gina Maharani', 'email' => 'gina@gmail.com', 'role' => 'guru'],
            ['name' => 'Hendra Wijaya', 'email' => 'hendra@gmail.com', 'role' => 'guru'],
            ['name' => 'Intan Permata', 'email' => 'intan@gmail.com', 'role' => 'guru'],
            ['name' => 'Joko Susilo', 'email' => 'joko@gmail.com', 'role' => 'guru'],
            ['name' => 'Kartika Dewi', 'email' => 'kartika@gmail.com', 'role' => 'guru'],
            ['name' => 'Lukman Hakim', 'email' => 'lukman@gmail.com', 'role' => 'guru'],
            ['name' => 'Maria Fitri', 'email' => 'maria@gmail.com', 'role' => 'guru'],
            ['name' => 'Nadia Putri', 'email' => 'nadia@gmail.com', 'role' => 'guru'],
            ['name' => 'Oki Pratama', 'email' => 'oki@gmail.com', 'role' => 'guru'],
            ['name' => 'Putri Rahayu', 'email' => 'putri@gmail.com', 'role' => 'guru'],
            ['name' => 'Qori Anggraini', 'email' => 'qori@gmail.com', 'role' => 'guru'],
            ['name' => 'Rian Hidayat', 'email' => 'rian@gmail.com', 'role' => 'guru'],
            ['name' => 'Siti Aminah', 'email' => 'siti@gmail.com', 'role' => 'guru'],
            ['name' => 'Taufik Ismail', 'email' => 'taufik@gmail.com', 'role' => 'guru'],
            ['name' => 'Utami Dewi', 'email' => 'utami@gmail.com', 'role' => 'guru'],
            ['name' => 'Wahyu Setiawan', 'email' => 'wahyu@gmail.com', 'role' => 'guru'],
            ['name' => 'Yuni Astuti', 'email' => 'yuni@gmail.com', 'role' => 'guru'],
            ['name' => 'Zainal Abidin', 'email' => 'zainal@gmail.com', 'role' => 'guru'],
        ];

        $guruList = [];
        $guruByEmail = [];
        foreach ($users as $index => $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [...$userData, 'password' => '12345678']
            );

            if ($userData['role'] !== 'guru') {
                continue;
            }

            $guruModel = guru::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nip' => sprintf('1988%02d%02d2010%02d%04d', $index + 1, $index + 1, $index + 1, $index + 1),
                    'no_hp' => sprintf('0812%08d', $index + 1),
                ]
            );
            $guruList[] = $guruModel;
            $guruByEmail[$userData['email']] = $guruModel;
        }

        SekolahSetting::firstOrCreate([], [
            'nama_sekolah' => 'SMK Negeri 1 Contoh',
            'alamat' => 'Jl. Pendidikan No. 123',
            'latitude' => -6.20000000,
            'longitude' => 106.81666600,
            'radius_meter' => 100,
            'toleransi_terlambat_menit' => 15,
        ]);

        $tahunAjaran = tahunAjaran::updateOrCreate(
            ['nama' => '2026/2027', 'semester' => 'ganjil'],
            [
                'tanggal_mulai' => '2026-07-01',
                'tanggal_selesai' => '2026-12-31',
                'status' => true,
                'is_published' => true,
            ]
        );

        $jurusanMapel = [
            'RPL' => [
                ['PPL', 'Pemrograman Perangkat Lunak'],
                ['PWEB', 'Pemrograman Web'],
                ['PBO', 'Pemrograman Berorientasi Objek'],
                ['BD', 'Basis Data'],
                ['UIUX', 'Desain Antarmuka dan Pengalaman Pengguna'],
            ],
            'TKJ' => [
                ['DJK', 'Dasar-Dasar Jaringan Komputer'],
                ['AJJ', 'Administrasi Infrastruktur Jaringan'],
                ['LJK', 'Teknologi Layanan Jaringan'],
                ['KJS', 'Keamanan Jaringan'],
                ['SRV', 'Administrasi Sistem Jaringan'],
            ],
            'AKL' => [
                ['AKD', 'Akuntansi Dasar'],
                ['AKP', 'Praktikum Akuntansi Perusahaan Jasa'],
                ['AKDAG', 'Akuntansi Perusahaan Dagang'],
                ['KOM', 'Komputer Akuntansi'],
                ['PAJAK', 'Administrasi Pajak'],
            ],
            'DKV' => [
                ['DKV', 'Dasar-Dasar Desain Komunikasi Visual'],
                ['GRAF', 'Desain Grafis'],
                ['FOTO', 'Fotografi'],
                ['VID', 'Produksi Video'],
                ['ANIM', 'Animasi'],
            ],
        ];

        $mapelUmum = [
            ['PAI', 'Pendidikan Agama dan Budi Pekerti'],
            ['PPKN', 'Pendidikan Pancasila'],
            ['BIN', 'Bahasa Indonesia'],
            ['BIG', 'Bahasa Inggris'],
            ['MTK', 'Matematika'],
            ['SEJ', 'Sejarah Indonesia'],
            ['PJOK', 'Pendidikan Jasmani, Olahraga, dan Kesehatan'],
            ['SBD', 'Seni Budaya'],
            ['INF', 'Informatika'],
            ['IPAS', 'Ilmu Pengetahuan Alam dan Sosial'],
            ['BK', 'Bimbingan dan Konseling'],
        ];

        $mapelList = [];
        foreach ($mapelUmum as [$kode, $nama]) {
            $mapelList[$kode] = MataPelajaran::updateOrCreate(
                ['nama' => $nama],
                ['kode' => $kode]
            );
        }
        foreach (array_merge(...array_values($jurusanMapel)) as [$kode, $nama]) {
            $mapelList[$kode] = MataPelajaran::updateOrCreate(
                ['nama' => $nama],
                ['kode' => $kode]
            );
        }

        // Pemetaan guru pengampu (1 guru hanya mengampu 1 maksimal 2 mapel)
        $guruMapelMap = [
            'guru@gmail.com'    => ['MTK'],          // Budi Santoso (1 mapel)
            'andi@gmail.com'    => ['INF'],          // Andi Saputra (1 mapel)
            'citra@gmail.com'   => ['BIN'],          // Citra Lestari (1 mapel)
            'dedi@gmail.com'    => ['BIG'],          // Dedi Kurniawan (1 mapel)
            'eka@gmail.com'     => ['PPKN', 'SEJ'],  // Eka Pratiwi (2 mapel)
            'fajar@gmail.com'   => ['PAI'],          // Fajar Nugroho (1 mapel)
            'gina@gmail.com'    => ['PJOK'],         // Gina Maharani (1 mapel)
            'hendra@gmail.com'  => ['SBD'],          // Hendra Wijaya (1 mapel)
            'intan@gmail.com'   => ['BK', 'IPAS'],   // Intan Permata (2 mapel)
            'joko@gmail.com'    => ['PPL', 'PWEB'],  // Joko Susilo (2 mapel - RPL)
            'kartika@gmail.com' => ['PBO', 'BD'],    // Kartika Dewi (2 mapel - RPL)
            'lukman@gmail.com'  => ['UIUX'],         // Lukman Hakim (1 mapel - RPL)
            'maria@gmail.com'   => ['DJK', 'AJJ'],   // Maria Fitri (2 mapel - TKJ)
            'nadia@gmail.com'   => ['KJS', 'SRV'],   // Nadia Putri (2 mapel - TKJ)
            'oki@gmail.com'     => ['LJK'],          // Oki Pratama (1 mapel - TKJ)
            'putri@gmail.com'   => ['AKD', 'KOM'],   // Putri Rahayu (2 mapel - AKL)
            'qori@gmail.com'    => ['AKP', 'AKDAG'], // Qori Anggraini (2 mapel - AKL)
            'rian@gmail.com'    => ['PAJAK'],        // Rian Hidayat (1 mapel - AKL)
            'siti@gmail.com'    => ['DKV', 'GRAF'],  // Siti Aminah (2 mapel - DKV)
            'taufik@gmail.com'  => ['FOTO', 'VID'],  // Taufik Ismail (2 mapel - DKV)
            'utami@gmail.com'   => ['ANIM'],         // Utami Dewi (1 mapel - DKV)
            'wahyu@gmail.com'   => ['MTK'],          // Wahyu Setiawan (1 mapel)
            'yuni@gmail.com'    => ['BIG'],          // Yuni Astuti (1 mapel)
            'zainal@gmail.com'  => ['BIN'],          // Zainal Abidin (1 mapel)
        ];

        $mapelToGuru = [];
        foreach ($guruMapelMap as $email => $kodes) {
            foreach ($kodes as $kode) {
                if (isset($guruByEmail[$email])) {
                    $mapelToGuru[$kode][] = $guruByEmail[$email];
                }
            }
        }

        $kelasList = [];
        foreach (['X', 'XI', 'XII'] as $tingkat) {
            foreach (array_keys($jurusanMapel) as $jurusan) {
                $namaKelas = "{$tingkat} {$jurusan} 1";
                $kelasList[] = [
                    'model' => Kelas::updateOrCreate(
                        ['nama_kelas' => $namaKelas],
                        ['tingkat' => $tingkat, 'jurusan' => $jurusan]
                    ),
                    'jurusan' => $jurusan,
                ];
            }
        }
        $kelasList[] = [
            'model' => Kelas::updateOrCreate(
                ['nama_kelas' => 'X RPL 2'],
                ['tingkat' => 'X', 'jurusan' => 'RPL']
            ),
            'jurusan' => 'RPL',
        ];

        $slotData = [
            ['Sesi 1', '07:45', '08:30', 1],
            ['Sesi 2', '08:30', '09:15', 2],
            ['Istirahat Pagi', '09:15', '09:30', 3, 'istirahat'],
            ['Sesi 3', '09:30', '10:15', 4],
            ['Sesi 4', '10:15', '11:00', 5],
            ['Istirahat Siang', '11:00', '11:30', 6, 'istirahat'],
            ['Sesi 5', '11:30', '12:15', 7],
            ['Sesi 6', '12:15', '13:00', 8],
            ['Sesi 7', '13:00', '13:45', 9],
            ['Istirahat Siang 2', '13:45', '14:00', 10, 'istirahat'],
            ['Sesi 8', '14:00', '14:45', 11],
            ['Sesi 9', '14:45', '15:30', 12],
        ];

        $jamList = [];
        foreach ($slotData as $slot) {
            [$namaSesi, $mulai, $selesai, $urutan] = $slot;
            $jamList[$namaSesi] = JamPelajaran::updateOrCreate(
                ['nama_sesi' => $namaSesi],
                [
                    'jenis' => $slot[4] ?? 'pelajaran',
                    'jam_mulai' => "{$mulai}:00",
                    'jam_selesai' => "{$selesai}:00",
                    'urutan' => $urutan,
                ]
            );
        }

        JamPelajaran::updateOrCreate(
            ['nama_sesi' => 'Kegiatan Bebas Pagi'],
            [
                'jenis' => 'kegiatan_lain',
                'jam_mulai' => '07:00:00',
                'jam_selesai' => '07:45:00',
                'urutan' => 0,
            ]
        );

        $kegiatanPerHari = [
            'Senin' => [
                ['Kegiatan Bebas Pagi', 'lainnya', '07:00', '07:45', 'Area sekolah', 'Kegiatan bebas sebelum pembelajaran dimulai.'],
                ['Istirahat Pagi', 'istirahat', '09:15', '09:30', 'Kantin dan area sekolah', 'Istirahat pagi.'],
                ['Istirahat dan Makan Siang', 'istirahat', '11:00', '11:30', 'Kantin dan area sekolah', 'Istirahat dan makan siang.'],
                ['Istirahat Siang', 'istirahat', '13:45', '14:00', 'Kantin dan area sekolah', 'Istirahat siang.'],
            ],
            'Selasa' => [
                ['Kegiatan Bebas Pagi', 'lainnya', '07:00', '07:45', 'Area sekolah', 'Kegiatan bebas sebelum pembelajaran dimulai.'],
                ['Istirahat Pagi', 'istirahat', '09:15', '09:30', 'Kantin dan area sekolah', 'Istirahat pagi.'],
                ['Istirahat dan Makan Siang', 'istirahat', '11:00', '11:30', 'Kantin dan area sekolah', 'Istirahat dan makan siang.'],
                ['Istirahat Siang', 'istirahat', '13:45', '14:00', 'Kantin dan area sekolah', 'Istirahat siang.'],
            ],
            'Rabu' => [
                ['Kegiatan Bebas Pagi', 'lainnya', '07:00', '07:45', 'Area sekolah', 'Kegiatan bebas sebelum pembelajaran dimulai.'],
                ['Istirahat Pagi', 'istirahat', '09:15', '09:30', 'Kantin dan area sekolah', 'Istirahat pagi.'],
                ['Istirahat dan Makan Siang', 'istirahat', '11:00', '11:30', 'Kantin dan area sekolah', 'Istirahat dan makan siang.'],
                ['Istirahat Siang', 'istirahat', '13:45', '14:00', 'Kantin dan area sekolah', 'Istirahat siang.'],
            ],
            'Kamis' => [
                ['Kegiatan Bebas Pagi', 'lainnya', '07:00', '07:45', 'Area sekolah', 'Kegiatan bebas sebelum pembelajaran dimulai.'],
                ['Istirahat Pagi', 'istirahat', '09:15', '09:30', 'Kantin dan area sekolah', 'Istirahat pagi.'],
                ['Istirahat dan Makan Siang', 'istirahat', '11:00', '11:30', 'Kantin dan area sekolah', 'Istirahat dan makan siang.'],
                ['Istirahat Siang', 'istirahat', '13:45', '14:00', 'Kantin dan area sekolah', 'Istirahat siang.'],
            ],
            'Jumat' => [
                ['Kegiatan Bebas Pagi', 'lainnya', '07:00', '07:45', 'Area sekolah', 'Kegiatan bebas sebelum pembelajaran dimulai.'],
                ['Istirahat Pagi', 'istirahat', '09:15', '09:30', 'Kantin dan area sekolah', 'Istirahat pagi.'],
                ['Istirahat Jumat', 'istirahat', '11:00', '11:30', 'Kantin dan area sekolah', 'Istirahat dan penutup kegiatan sekolah hari Jumat.'],
            ],
        ];

        foreach ($kegiatanPerHari as $hari => $daftarKegiatan) {
            foreach ($daftarKegiatan as [$nama, $jenis, $mulai, $selesai, $lokasi, $keterangan]) {
                KegiatanSekolah::updateOrCreate(
                    [
                        'tahun_ajaran_id' => $tahunAjaran->id,
                        'hari' => $hari,
                        'nama_kegiatan' => $nama,
                    ],
                    [
                        'jenis' => $jenis,
                        'jam_mulai' => "{$mulai}:00",
                        'jam_selesai' => "{$selesai}:00",
                        'lokasi' => $lokasi,
                        'peserta' => 'Seluruh guru dan siswa',
                        'keterangan' => $keterangan,
                    ]
                );
            }
        }

        $hariSekolah = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        // Bersihkan data jadwal lama untuk tahun ajaran aktif
        DB::table('jadwal_pelajaran_sesi')->where('tahun_ajaran_id', $tahunAjaran->id)->delete();
        JadwalPelajaran::where('tahun_ajaran_id', $tahunAjaran->id)->delete();

        // Generate Jadwal Pelajaran (Bebas bentrok dan setiap guru hanya mengajar mapel yang diampunya)
        $busyTeachers = [];
        $teacherHours = [];
        foreach ($guruList as $g) {
            $teacherHours[$g->id] = 0;
        }

        $mapelUmumKodes = array_column($mapelUmum, 0);

        foreach ($kelasList as $kelasIndex => $kelasData) {
            $jurusanKodes = array_column($jurusanMapel[$kelasData['jurusan']], 0);

            // Susun kurikulum mingguan 40 sesi (Senin-Kamis 9 sesi, Jumat 4 sesi)
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

            // Rotasi agar antar-kelas tidak menuntut mapel yang sama persis di jam yang sama
            $shift = ($kelasIndex * 7) % count($curriculum);
            $curriculum = array_merge(array_slice($curriculum, $shift), array_slice($curriculum, 0, $shift));

            $currIndex = 0;
            foreach ($hariSekolah as $hari) {
                $jumlahSesi = $hari === 'Jumat' ? 4 : 9;
                for ($sesiIndex = 0; $sesiIndex < $jumlahSesi; $sesiIndex++) {
                    $jam = $jamList['Sesi '.($sesiIndex + 1)];
                    $assignedMapelKode = null;
                    $selectedGuru = null;

                    $attempts = 0;
                    $maxAttempts = count($curriculum);

                    while ($attempts < $maxAttempts && !empty($curriculum)) {
                        $candidateKode = $curriculum[$currIndex % count($curriculum)];
                        $candidates = $mapelToGuru[$candidateKode] ?? [];

                        // Utamakan guru dengan jam mengajar terendah agar beban merata
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

                    // Fallback jika slot dari antrean kurikulum sedang bentrok
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

                    if ($selectedGuru !== null && $assignedMapelKode !== null) {
                        $busyTeachers[$hari][$sesiIndex][$selectedGuru->id] = true;
                        $teacherHours[$selectedGuru->id] = ($teacherHours[$selectedGuru->id] ?? 0) + 1;

                        $jadwal = JadwalPelajaran::create([
                            'tahun_ajaran_id' => $tahunAjaran->id,
                            'kelas_id' => $kelasData['model']->id,
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
                            'kelas_id' => $kelasData['model']->id,
                            'hari' => $hari,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }
}
