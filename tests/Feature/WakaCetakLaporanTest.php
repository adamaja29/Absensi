<?php

namespace Tests\Feature;

use App\Models\AbsensiGuru;
use App\Models\guru;
use App\Models\JadwalPelajaran;
use App\Models\JamPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\tahunAjaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WakaCetakLaporanTest extends TestCase
{
    use RefreshDatabase;

    private const TANGGAL = '2026-11-16'; // Senin

    private function seedData(): array
    {
        $waka = User::create([
            'name' => 'Waka Test',
            'email' => 'waka-cetak@test.com',
            'password' => 'password',
            'role' => 'waka',
        ]);

        $tahun = tahunAjaran::create([
            'nama' => '2026/2027',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'status' => true,
        ]);

        $kelas = Kelas::create(['nama_kelas' => 'X RPL 1']);
        $mapel = MataPelajaran::create(['nama' => 'Matematika']);
        $jam = JamPelajaran::create([
            'nama_sesi' => 'Sesi 1',
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '08:00:00',
            'urutan' => 1,
        ]);

        $guruList = [];
        foreach ([['Guru Satu', '111'], ['Guru Dua', '222'], ['Guru Tiga', '333']] as [$nama, $nip]) {
            $user = User::create([
                'name' => $nama,
                'email' => strtolower(str_replace(' ', '.', $nama)).'@test.com',
                'password' => 'password',
                'role' => 'guru',
            ]);
            $guruList[] = guru::create(['user_id' => $user->id, 'nip' => $nip]);
        }

        $jadwal = [];
        foreach ($guruList as $item) {
            $jadwal[] = JadwalPelajaran::create([
                'tahun_ajaran_id' => $tahun->id,
                'guru_id' => $item->id,
                'kelas_id' => $kelas->id,
                'mata_pelajaran_id' => $mapel->id,
                'hari' => 'Senin',
                'jam_pelajaran_mulai_id' => $jam->id,
                'jam_pelajaran_selesai_id' => $jam->id,
            ]);
        }

        AbsensiGuru::create([
            'jadwal_pelajaran_id' => $jadwal[0]->id,
            'guru_id' => $guruList[0]->id,
            'tanggal' => self::TANGGAL,
            'waktu_absen' => '07:00:00',
            'status' => 'hadir',
        ]);

        AbsensiGuru::create([
            'jadwal_pelajaran_id' => $jadwal[1]->id,
            'guru_id' => $guruList[1]->id,
            'tanggal' => self::TANGGAL,
            'waktu_absen' => '07:20:00',
            'status' => 'terlambat',
        ]);

        return compact('waka', 'guruList', 'jadwal');
    }

    public function test_cetak_menampilkan_data_sesuai_filter_status(): void
    {
        $data = $this->seedData();

        $response = $this->actingAs($data['waka'])->get(route('waka.absensi.cetak', [
            'tanggal' => self::TANGGAL,
            'status' => ['terlambat'],
        ]));

        $response->assertOk();

        $jadwal = $response->viewData('semuaJadwalHari');
        $this->assertCount(1, $jadwal);
        $this->assertSame($data['jadwal'][1]->id, $jadwal->first()->id);

        $response->assertViewHas('totalJadwal', 1);
        $response->assertViewHas('sudahCheckIn', 1);
        $response->assertViewHas('terlambatCount', 1);
        $response->assertViewHas('belumCheckInCount', 0);
        $response->assertViewHas('izinSakitCount', 0);
    }

    public function test_cetak_menampilkan_data_sesuai_filter_guru_kelas_mapel(): void
    {
        $data = $this->seedData();

        $response = $this->actingAs($data['waka'])->get(route('waka.absensi.cetak', [
            'tanggal' => self::TANGGAL,
            'guru_id' => $data['guruList'][1]->id,
            'kelas_id' => $data['jadwal'][1]->kelas_id,
            'mata_pelajaran_id' => $data['jadwal'][1]->mata_pelajaran_id,
        ]));

        $response->assertOk();

        $jadwal = $response->viewData('semuaJadwalHari');
        $this->assertCount(1, $jadwal);
        $this->assertSame($data['jadwal'][1]->id, $jadwal->first()->id);
        $response->assertViewHas('filterGuruNama', 'Guru Dua');
        $response->assertViewHas('filterKelasNama', 'X RPL 1');
        $response->assertViewHas('filterMapelNama', 'Matematika');
    }

    public function test_monitoring_dan_cetak_konsisten_untuk_filter_belum_check_in(): void
    {
        $data = $this->seedData();

        $monitoring = $this->actingAs($data['waka'])->get(route('waka.absensi.monitoring', [
            'tanggal' => self::TANGGAL,
            'status' => ['belum'],
        ]));

        $monitoring->assertOk();
        $monitoring->assertViewHas('totalJadwal', 1);
        $monitoring->assertViewHas('sudahCheckIn', 0);
        $monitoring->assertViewHas('belumCheckInCount', 1);
        $this->assertCount(1, $monitoring->viewData('semuaJadwalHari'));

        $cetak = $this->actingAs($data['waka'])->get(route('waka.absensi.cetak', [
            'tanggal' => self::TANGGAL,
            'status' => ['belum'],
        ]));

        $cetak->assertOk();
        $cetak->assertViewHas('totalJadwal', 1);
        $cetak->assertViewHas('sudahCheckIn', 0);
        $cetak->assertViewHas('belumCheckInCount', 1);
        $this->assertCount(1, $cetak->viewData('semuaJadwalHari'));
    }
}
