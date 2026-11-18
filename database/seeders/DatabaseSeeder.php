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
        ];

        $guruList = [];
        foreach ($users as $index => $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [...$userData, 'password' => '12345678']
            );

            if ($userData['role'] !== 'guru') {
                continue;
            }

            $guruList[] = guru::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nip' => sprintf('1988%02d%02d2010%02d%04d', $index + 1, $index + 1, $index + 1, $index + 1),
                    'no_hp' => sprintf('0812%08d', $index + 1),
                ]
            );
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

        $mapelUmumList = [];
        $mapelList = [];
        foreach ($mapelUmum as [$kode, $nama]) {
            $mapelList[$kode] = MataPelajaran::updateOrCreate(
                ['nama' => $nama],
                ['kode' => $kode]
            );
            $mapelUmumList[$kode] = $mapelList[$kode];
        }
        foreach (array_merge(...array_values($jurusanMapel)) as [$kode, $nama]) {
            $mapelList[$kode] = MataPelajaran::updateOrCreate(
                ['nama' => $nama],
                ['kode' => $kode]
            );
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

        KegiatanSekolah::query()
            ->where('tahun_ajaran_id', $tahunAjaran->id)
            ->where('hari', 'Senin')
            ->where('nama_kegiatan', 'Upacara Bendera Rutin')
            ->delete();

        $hariSekolah = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $kelasLama = [
            ['guru' => $guruList[0], 'kelas' => 'X RPL 1', 'mapel' => 'MTK'],
            ['guru' => $guruList[0], 'kelas' => 'XI RPL 1', 'mapel' => 'PWEB'],
            ['guru' => $guruList[1], 'kelas' => 'X RPL 2', 'mapel' => 'BIN'],
        ];
        foreach ($kelasLama as $jadwalLama) {
            JadwalPelajaran::query()
                ->where('tahun_ajaran_id', $tahunAjaran->id)
                ->where('guru_id', $jadwalLama['guru']->id)
                ->where('kelas_id', Kelas::where('nama_kelas', $jadwalLama['kelas'])->value('id'))
                ->where('mata_pelajaran_id', $mapelList[$jadwalLama['mapel']]->id)
                ->whereNotIn('hari', $hariSekolah)
                ->delete();
        }

        $guruCount = count($guruList);
        foreach ($kelasList as $kelasIndex => $kelasData) {
            $mapelJurusan = array_map(
                fn (array $mapel) => $mapelList[$mapel[0]],
                $jurusanMapel[$kelasData['jurusan']]
            );
            $mapelKelas = array_merge(array_values($mapelUmumList), $mapelJurusan);

            foreach ($hariSekolah as $hariIndex => $hari) {
                $jumlahSesi = $hari === 'Jumat' ? 4 : 9;
                for ($sesiIndex = 0; $sesiIndex < $jumlahSesi; $sesiIndex++) {
                    $jam = $jamList['Sesi '.($sesiIndex + 1)];
                    $guru = $guruList[($kelasIndex + $sesiIndex + $hariIndex) % $guruCount];
                    $mapel = $mapelKelas[($kelasIndex + $sesiIndex + $hariIndex) % count($mapelKelas)];

                    JadwalPelajaran::updateOrCreate(
                        [
                            'tahun_ajaran_id' => $tahunAjaran->id,
                            'kelas_id' => $kelasData['model']->id,
                            'hari' => $hari,
                            'jam_pelajaran_mulai_id' => $jam->id,
                        ],
                        [
                            'guru_id' => $guru->id,
                            'mata_pelajaran_id' => $mapel->id,
                            'jam_pelajaran_selesai_id' => $jam->id,
                        ]
                    );
                }
            }
        }

        foreach (JadwalPelajaran::with(['jamMulai', 'jamSelesai'])
            ->where('tahun_ajaran_id', $tahunAjaran->id)
            ->get() as $jadwal) {
            $sesiIds = JamPelajaran::where('jenis', 'pelajaran')
                ->where('jam_mulai', '>=', $jadwal->jamMulai->jam_mulai)
                ->where('jam_selesai', '<=', $jadwal->jamSelesai->jam_selesai)
                ->orderBy('urutan')
                ->pluck('id');

            foreach ($sesiIds as $sesiId) {
                DB::table('jadwal_pelajaran_sesi')->updateOrInsert(
                    ['jadwal_pelajaran_id' => $jadwal->id, 'jam_pelajaran_id' => $sesiId],
                    [
                        'tahun_ajaran_id' => $jadwal->tahun_ajaran_id,
                        'guru_id' => $jadwal->guru_id,
                        'kelas_id' => $jadwal->kelas_id,
                        'hari' => $jadwal->hari,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }
}
