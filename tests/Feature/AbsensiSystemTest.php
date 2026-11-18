<?php

namespace Tests\Feature;

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
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbsensiSystemTest extends TestCase
{
    use RefreshDatabase;

    private function setupData()
    {
        $setting = SekolahSetting::firstOrCreate([], [
            'nama_sekolah' => 'SMK Negeri 1',
            'latitude' => -6.20000000,
            'longitude' => 106.81666600,
            'radius_meter' => 100,
            'toleransi_terlambat_menit' => 15,
        ]);

        $tahun = tahunAjaran::firstOrCreate(
            ['nama' => '2026/2027', 'semester' => 'ganjil'],
            ['tanggal_mulai' => '2026-07-01', 'tanggal_selesai' => '2026-12-31', 'status' => true]
        );

        $wakaUser = User::firstOrCreate(
            ['email' => 'waka@test.com'],
            ['name' => 'Waka Test', 'password' => bcrypt('password'), 'role' => 'waka']
        );

        $guruUser = User::firstOrCreate(
            ['email' => 'guru@test.com'],
            ['name' => 'Guru Test', 'password' => bcrypt('password'), 'role' => 'guru']
        );

        $guruProfile = guru::firstOrCreate(
            ['user_id' => $guruUser->id],
            ['nip' => '9999999999', 'no_hp' => '0811111111']
        );

        $kelas = Kelas::firstOrCreate(['nama_kelas' => 'X RPL 99']);
        $mapel = MataPelajaran::firstOrCreate(['nama' => 'Matematika Test']);

        $jam1 = JamPelajaran::firstOrCreate(
            ['nama_sesi' => 'Sesi Test 1'],
            ['jam_mulai' => '00:00:00', 'jam_selesai' => '23:59:59', 'urutan' => 1]
        );

        $mapHari = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];
        $hariIni = $mapHari[now()->format('l')];

        $jadwal = JadwalPelajaran::firstOrCreate(
            [
                'tahun_ajaran_id' => $tahun->id,
                'guru_id' => $guruProfile->id,
                'kelas_id' => $kelas->id,
                'mata_pelajaran_id' => $mapel->id,
                'hari' => $hariIni,
            ],
            [
                'jam_pelajaran_mulai_id' => $jam1->id,
                'jam_pelajaran_selesai_id' => $jam1->id,
            ]
        );

        return compact('wakaUser', 'guruUser', 'guruProfile', 'jadwal', 'setting');
    }

    public function test_guru_can_access_dashboard()
    {
        $data = $this->setupData();

        $response = $this->actingAs($data['guruUser'])->get('/guru/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Matematika Test');
    }

    public function test_guru_can_checkin_within_gps_radius()
    {
        $data = $this->setupData();

        // Lokasi tepat di sekolah
        $response = $this->actingAs($data['guruUser'])->post('/guru/absen', [
            'jadwal_pelajaran_id' => $data['jadwal']->id,
            'latitude' => -6.20000000,
            'longitude' => 106.81666600,
            'materi_diajarkan' => 'Belajar Aljabar Linier Dasar',
            'catatan_siswa_tidak_masuk' => 'Nihil',
        ]);

        $response->assertRedirect('/guru/dashboard');
        $this->assertDatabaseHas('absensi_guru', [
            'jadwal_pelajaran_id' => $data['jadwal']->id,
            'guru_id' => $data['guruProfile']->id,
            'materi_diajarkan' => 'Belajar Aljabar Linier Dasar',
        ]);
    }

    public function test_guru_cannot_checkin_outside_gps_radius()
    {
        $data = $this->setupData();

        // Lokasi sangat jauh dari sekolah (-7.0, 110.0)
        $response = $this->actingAs($data['guruUser'])->post('/guru/absen', [
            'jadwal_pelajaran_id' => $data['jadwal']->id,
            'latitude' => -7.00000000,
            'longitude' => 110.00000000,
            'materi_diajarkan' => 'Materi Jarak Jauh',
        ]);

        $response->assertSessionHasErrors('absensi');
    }

    public function test_guru_can_checkin_during_local_jadwal_time_only()
    {
        $this->travelTo(Carbon::parse('2026-11-09 07:30:00', 'Asia/Jakarta'));
        $data = $this->setupData();

        $jam = JamPelajaran::findOrFail($data['jadwal']->jam_pelajaran_mulai_id);
        $jam->update([
            'jam_mulai' => '07:45:00',
            'jam_selesai' => '08:30:00',
        ]);

        $payload = [
            'jadwal_pelajaran_id' => $data['jadwal']->id,
            'latitude' => -6.20000000,
            'longitude' => 106.81666600,
            'materi_diajarkan' => 'Materi pagi',
        ];

        $this->actingAs($data['guruUser'])
            ->post('/guru/absen', $payload)
            ->assertSessionHasErrors('absensi');

        $this->travelTo(Carbon::parse('2026-11-09 08:00:00', 'Asia/Jakarta'));
        $this->actingAs($data['guruUser'])
            ->get('/guru/dashboard')
            ->assertOk()
            ->assertSee('Check-In Sekarang');

        $this->actingAs($data['guruUser'])
            ->post('/guru/absen', $payload)
            ->assertRedirect('/guru/dashboard')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('absensi_guru', [
            'jadwal_pelajaran_id' => $data['jadwal']->id,
            'tanggal' => '2026-11-09 00:00:00',
            'waktu_absen' => '08:00:00',
        ]);
    }

    public function test_guru_weekly_agenda_is_sorted_by_time_across_activities_and_lessons()
    {
        $this->travelTo(Carbon::parse('2026-11-09 08:15:00', 'Asia/Jakarta'));
        $data = $this->setupData();

        $lateSlot = JamPelajaran::findOrFail($data['jadwal']->jam_pelajaran_mulai_id);
        $lateSlot->update([
            'jam_mulai' => '10:00:00',
            'jam_selesai' => '10:45:00',
        ]);

        $earlySlot = JamPelajaran::create([
            'nama_sesi' => 'Sesi Lebih Awal',
            'jenis' => 'pelajaran',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '08:45:00',
            'urutan' => 2,
        ]);
        $earlyMapel = MataPelajaran::create(['nama' => 'Pelajaran Lebih Awal']);
        $earlyClass = Kelas::create(['nama_kelas' => 'X RPL Awal']);
        JadwalPelajaran::create([
            'tahun_ajaran_id' => tahunAjaran::where('status', true)->value('id'),
            'guru_id' => $data['guruProfile']->id,
            'kelas_id' => $earlyClass->id,
            'mata_pelajaran_id' => $earlyMapel->id,
            'hari' => 'Senin',
            'jam_pelajaran_mulai_id' => $earlySlot->id,
            'jam_pelajaran_selesai_id' => $earlySlot->id,
        ]);
        KegiatanSekolah::create([
            'tahun_ajaran_id' => tahunAjaran::where('status', true)->value('id'),
            'nama_kegiatan' => 'Kegiatan Sebelum Jadwal',
            'jenis' => 'lainnya',
            'hari' => 'Senin',
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '07:15:00',
        ]);
        KegiatanSekolah::create([
            'tahun_ajaran_id' => tahunAjaran::where('status', true)->value('id'),
            'nama_kegiatan' => 'Kegiatan Tengah Pagi',
            'jenis' => 'lainnya',
            'hari' => 'Senin',
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '09:15:00',
        ]);

        $response = $this->actingAs($data['guruUser'])->get('/guru/jadwal');

        $response->assertOk();
        $earlyPosition = strpos($response->getContent(), 'Pelajaran Lebih Awal');
        $activityPosition = strpos($response->getContent(), 'Kegiatan Tengah Pagi');
        $latePosition = strpos($response->getContent(), 'Matematika Test');

        $this->assertNotFalse($earlyPosition);
        $this->assertNotFalse($activityPosition);
        $this->assertNotFalse($latePosition);
        $this->assertLessThan($activityPosition, $earlyPosition);
        $this->assertLessThan($latePosition, $activityPosition);
        $response->assertDontSee('Sesi Lebih Awal');

        $dashboard = $this->actingAs($data['guruUser'])->get('/guru/dashboard');
        $dashboard->assertOk();
        $dashboardEarlyPosition = strpos($dashboard->getContent(), 'Pelajaran Lebih Awal');
        $dashboardActivityPosition = strpos($dashboard->getContent(), 'Kegiatan Sebelum Jadwal');

        $this->assertNotFalse($dashboardEarlyPosition);
        $this->assertNotFalse($dashboardActivityPosition);
        $this->assertLessThan($dashboardActivityPosition, $dashboardEarlyPosition);
        $dashboard->assertSee('Jam Mengajar Berlangsung');
    }

    public function test_waka_can_access_monitoring()
    {
        $data = $this->setupData();

        $response = $this->actingAs($data['wakaUser'])->get('/waka/absensi/monitoring');
        $response->assertStatus(200);
        $response->assertSee('Monitoring Absensi');
    }

    public function test_waka_can_access_dashboard()
    {
        $data = $this->setupData();

        $response = $this->actingAs($data['wakaUser'])->get('/waka/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard Waka Kurikulum');
    }

    public function test_waka_can_access_cetak_laporan()
    {
        $data = $this->setupData();

        $response = $this->actingAs($data['wakaUser'])->get('/waka/absensi/cetak');
        $response->assertStatus(200);
        $response->assertSee('LAPORAN REKAPITULASI PRESENSI');
    }

    public function test_login_page_renders_cleanly()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Sistem Presensi Mengajar');
    }

    public function test_admin_can_access_dashboard()
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin_test@test.com'],
            ['name' => 'Admin Test', 'password' => bcrypt('password'), 'role' => 'admin']
        );

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard Administrator');
    }

    public function test_admin_guru_and_waka_lists_keep_older_records_accessible()
    {
        $admin = User::create([
            'name' => 'Admin Pagination',
            'email' => 'admin-pagination@test.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        for ($index = 1; $index <= 11; $index++) {
            $guruUser = User::create([
                'name' => "Guru Pagination {$index}",
                'email' => "guru-pagination-{$index}@test.com",
                'password' => 'password',
                'role' => 'guru',
            ]);

            guru::create([
                'user_id' => $guruUser->id,
                'nip' => "NIP-PAGINATION-{$index}",
            ]);

            User::create([
                'name' => "Waka Pagination {$index}",
                'email' => "waka-pagination-{$index}@test.com",
                'password' => 'password',
                'role' => 'waka',
            ]);
        }

        $guruPage = $this->actingAs($admin)->get(route('admin.guru.data'));
        $guruPage->assertOk();
        $guruPage->assertSee('pagination');
        $this->actingAs($admin)->get(route('admin.guru.data', ['page' => 2]))
            ->assertOk()
            ->assertSee('Guru Pagination 1');

        $wakaPage = $this->actingAs($admin)->get(route('admin.waka.data'));
        $wakaPage->assertOk();
        $wakaPage->assertSee('pagination');
        $this->actingAs($admin)->get(route('admin.waka.data', ['page' => 2]))
            ->assertOk()
            ->assertSee('Waka Pagination 1');
    }

    public function test_waka_table_pagination_uses_bootstrap_markup()
    {
        $data = $this->setupData();

        for ($index = 1; $index <= 11; $index++) {
            Kelas::create([
                'nama_kelas' => "Pagination {$index}",
                'tingkat' => 'X',
                'jurusan' => 'RPL',
            ]);
        }

        $response = $this->actingAs($data['wakaUser'])->get('/waka/kelas/data');

        $response->assertOk();
        $response->assertSee('pagination');
        $this->actingAs($data['wakaUser'])->get('/waka/kelas/data?page=2')
            ->assertOk()
            ->assertSee('Pagination 1');
    }

    public function test_guru_can_submit_izin_and_waka_can_approve()
    {
        $data = $this->setupData();

        // Guru ajukan izin
        $response = $this->actingAs($data['guruUser'])->post('/guru/izin', [
            'jenis' => 'izin',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->toDateString(),
            'alasan' => 'Urusan keluarga mendesak',
        ]);

        $response->assertRedirect('/guru/izin');
        $this->assertDatabaseHas('izin_guru', [
            'guru_id' => $data['guruProfile']->id,
            'jenis' => 'izin',
            'status' => 'menunggu',
        ]);

        $izin = IzinGuru::where('guru_id', $data['guruProfile']->id)->first();

        // Waka menyetujui izin
        $approveResponse = $this->actingAs($data['wakaUser'])->put("/waka/izin/{$izin->id}", [
            'status' => 'disetujui',
            'catatan_waka' => 'Disetujui silakan tinggalkan tugas',
        ]);

        $approveResponse->assertRedirect('/waka/izin/data');
        $this->assertDatabaseHas('izin_guru', [
            'id' => $izin->id,
            'status' => 'disetujui',
        ]);

        // Pastikan absensi otomatis tercatat sebagai 'izin'
        $this->assertDatabaseHas('absensi_guru', [
            'jadwal_pelajaran_id' => $data['jadwal']->id,
            'guru_id' => $data['guruProfile']->id,
            'status' => 'izin',
        ]);
    }

    public function test_waka_can_access_jadwal_timeline()
    {
        $data = $this->setupData();

        $response = $this->actingAs($data['wakaUser'])->get('/waka/jadwal/data');
        $response->assertStatus(200);
        $response->assertSee('Timeline Harian Terpadu');
        $response->assertSee('Matriks Kelas vs Jam');
        $response->assertSee('Agenda Kegiatan Sekolah');
        $response->assertSee('00:00 - 23:59 WIB');
        $response->assertDontSee('Sesi Test 1');
    }

    public function test_waka_can_create_and_manage_kegiatan_sekolah()
    {
        $data = $this->setupData();
        $tahun = tahunAjaran::where('status', true)->first();

        // Waka membuat kegiatan sekolah (Upacara)
        $response = $this->actingAs($data['wakaUser'])->post('/waka/kegiatan', [
            'tahun_ajaran_id' => $tahun->id,
            'nama_kegiatan' => 'Upacara Bendera Senin',
            'jenis' => 'upacara',
            'hari' => 'Senin',
            'jam_mulai' => '07:00',
            'jam_selesai' => '07:45',
            'lokasi' => 'Lapangan Utama',
            'peserta' => 'Seluruh Guru & Siswa',
            'keterangan' => 'Seragam resmi PGRI/KORPRI',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('kegiatan_sekolah', [
            'nama_kegiatan' => 'Upacara Bendera Senin',
            'jenis' => 'upacara',
            'hari' => 'Senin',
        ]);

        // Pastikan Upacara TIDAK masuk sebagai mata_pelajaran atau jadwal_pelajaran
        $this->assertDatabaseMissing('mata_pelajaran', [
            'nama' => 'Upacara Bendera Senin',
        ]);
    }

    public function test_system_prevents_conflict_between_kbm_and_kegiatan_sekolah()
    {
        $data = $this->setupData();
        $tahun = tahunAjaran::where('status', true)->first();

        // Buat kegiatan sekolah Upacara 07:00 - 07:45
        KegiatanSekolah::create([
            'tahun_ajaran_id' => $tahun->id,
            'nama_kegiatan' => 'Upacara Bendera Rutin',
            'jenis' => 'upacara',
            'hari' => 'Senin',
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '07:45:00',
        ]);

        // Buat Sesi KBM yang bertabrakan (07:15 - 08:00)
        $jamBentrok = JamPelajaran::create([
            'nama_sesi' => 'Jam Bentrok',
            'jenis' => 'pelajaran',
            'jam_mulai' => '07:15:00',
            'jam_selesai' => '08:00:00',
            'urutan' => 99,
        ]);

        $kelas = Kelas::first();
        $mapel = MataPelajaran::first();

        // Coba jadwalkan KBM di jam bentrok tersebut
        $response = $this->actingAs($data['wakaUser'])->post('/waka/jadwal', [
            'tahun_ajaran_id' => $tahun->id,
            'guru_id' => $data['guruProfile']->id,
            'kelas_id' => $kelas->id,
            'mata_pelajaran_id' => $mapel->id,
            'hari' => 'Senin',
            'jam_pelajaran_mulai_id' => $jamBentrok->id,
            'jam_pelajaran_selesai_id' => $jamBentrok->id,
        ]);

        // Harus gagal dengan pesan bentrok
        $response->assertSessionHasErrors('jam_pelajaran_mulai_id');
    }

    public function test_waka_prevents_teacher_conflicts_by_clock_time_not_session_order()
    {
        $data = $this->setupData();
        $existingSlot = JamPelajaran::findOrFail($data['jadwal']->jam_pelajaran_mulai_id);
        $existingSlot->update([
            'jam_mulai' => '12:00:00',
            'jam_selesai' => '13:00:00',
            'urutan' => 1,
        ]);

        $overlappingSlot = JamPelajaran::create([
            'nama_sesi' => 'Sesi Urutan Berbeda',
            'jenis' => 'pelajaran',
            'jam_mulai' => '12:15:00',
            'jam_selesai' => '12:45:00',
            'urutan' => 99,
        ]);
        $kelasLain = Kelas::create(['nama_kelas' => 'X RPL Bentrok']);
        $mapelLain = MataPelajaran::create(['nama' => 'Mapel Bentrok']);

        $response = $this->actingAs($data['wakaUser'])->post('/waka/jadwal', [
            'tahun_ajaran_id' => tahunAjaran::where('status', true)->value('id'),
            'guru_id' => $data['guruProfile']->id,
            'kelas_id' => $kelasLain->id,
            'mata_pelajaran_id' => $mapelLain->id,
            'hari' => $data['jadwal']->hari,
            'jam_pelajaran_mulai_id' => $overlappingSlot->id,
            'jam_pelajaran_selesai_id' => $overlappingSlot->id,
        ]);

        $response->assertSessionHasErrors('guru_id');
    }

    public function test_waka_can_toggle_publikasi_jadwal_and_guru_sees_draft()
    {
        $data = $this->setupData();
        $tahun = tahunAjaran::where('status', true)->first();

        // Waka ubah status jadwal ke Draft (is_published = false)
        $publishResponse = $this->actingAs($data['wakaUser'])->post('/waka/jadwal/publish', [
            'tahun_ajaran_id' => $tahun->id,
        ]);
        $publishResponse->assertSessionHas('success');

        $tahun->refresh();
        $this->assertFalse((bool) $tahun->is_published);

        // Guru buka dashboard dan melihat peringatan Draft
        $guruResponse = $this->actingAs($data['guruUser'])->get('/guru/dashboard');
        $guruResponse->assertStatus(200);
        $guruResponse->assertSee('Jadwal Masih Dalam Tahap Draft');

        // Pastikan guru tidak bisa check-in saat jadwal masih Draft
        $checkinResponse = $this->actingAs($data['guruUser'])->post('/guru/absen', [
            'jadwal_pelajaran_id' => $data['jadwal']->id,
            'latitude' => $data['setting']->latitude,
            'longitude' => $data['setting']->longitude,
            'materi_diajarkan' => 'Materi saat draft',
        ]);

        $checkinResponse->assertSessionHasErrors('absensi');
    }
}
