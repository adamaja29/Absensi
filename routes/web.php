<?php

use App\Http\Controllers\authController;
use App\Http\Controllers\adminController;
use App\Http\Controllers\guruController;
use App\Http\Controllers\wakaController;
use Illuminate\Support\Facades\Route;

// Halaman Login & Autentikasi
Route::get('/', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [authController::class, 'login'])->name('login.process');
Route::post('/logout', [authController::class, 'logout'])->name('logout');

// ========================================================
// ROUTE ADMIN
// ========================================================
Route::middleware(['role:admin'])->group(function () {
    Route::get('/admin/dashboard', [adminController::class, 'dashboard'])->name('admin.dashboard');

    // Kelola Guru
    Route::get('/admin/guru/data', [adminController::class, 'dataGuru'])->name('admin.guru.data');
    Route::get('/admin/guru/tambah', [adminController::class, 'createGuru'])->name('admin.guru.create');
    Route::post('/admin/guru', [adminController::class, 'storeGuru'])->name('admin.guru.store');
    Route::get('/admin/guru/{guru}/edit', [adminController::class, 'editGuru'])->name('admin.guru.edit');
    Route::put('/admin/guru/{guru}', [adminController::class, 'updateGuru'])->name('admin.guru.update');
    Route::delete('/admin/guru/{guru}', [adminController::class, 'destroyGuru'])->name('admin.guru.destroy');

    // Kelola Waka
    Route::get('/admin/waka/data', [adminController::class, 'dataWaka'])->name('admin.waka.data');
    Route::get('/admin/waka/tambah', [adminController::class, 'createWaka'])->name('admin.waka.create');
    Route::post('/admin/waka', [adminController::class, 'storeWaka'])->name('admin.waka.store');
    Route::get('/admin/waka/{waka}/edit', [adminController::class, 'editWaka'])->name('admin.waka.edit');
    Route::put('/admin/waka/{waka}', [adminController::class, 'updateWaka'])->name('admin.waka.update');
    Route::delete('/admin/waka/{waka}', [adminController::class, 'destroyWaka'])->name('admin.waka.destroy');

    // Pengaturan Lokasi GPS Sekolah
    Route::get('/admin/sekolah/pengaturan', [adminController::class, 'pengaturanSekolah'])->name('admin.sekolah.setting');
    Route::put('/admin/sekolah/pengaturan', [adminController::class, 'updatePengaturanSekolah'])->name('admin.sekolah.update');
});

// ========================================================
// ROUTE GURU
// ========================================================
Route::middleware(['role:guru'])->group(function () {
    Route::get('/guru/dashboard', [guruController::class, 'dashboard'])->name('guru.dashboard');
    Route::post('/guru/absen', [guruController::class, 'storeAbsen'])->name('guru.absen.store');
    Route::get('/guru/jadwal', [guruController::class, 'jadwalMingguan'])->name('guru.jadwal');
    Route::get('/guru/riwayat', [guruController::class, 'riwayatAbsensi'])->name('guru.riwayat');
    Route::get('/guru/izin', [guruController::class, 'izin'])->name('guru.izin');
    Route::post('/guru/izin', [guruController::class, 'storeIzin'])->name('guru.izin.store');
});

// ========================================================
// ROUTE WAKA
// ========================================================
Route::middleware(['role:waka'])->group(function () {
    Route::get('/waka/dashboard', [wakaController::class, 'dashboard'])->name('waka.dashboard');

    // Kelola Guru
    Route::get('/waka/guru/data', [wakaController::class, 'dataGuru'])->name('waka.guru.data');
    Route::get('/waka/guru/tambah', [wakaController::class, 'createGuru'])->name('waka.guru.create');
    Route::post('/waka/guru', [wakaController::class, 'storeGuru'])->name('waka.guru.store');
    Route::get('/waka/guru/{guru}/edit', [wakaController::class, 'editGuru'])->name('waka.guru.edit');
    Route::put('/waka/guru/{guru}', [wakaController::class, 'updateGuru'])->name('waka.guru.update');
    Route::delete('/waka/guru/{guru}', [wakaController::class, 'destroyGuru'])->name('waka.guru.destroy');

    // Tahun Ajaran
    Route::get('/waka/tahun-ajaran/data', [wakaController::class, 'dataTahunAjaran'])->name('waka.tahun-ajaran.data');
    Route::get('/waka/tahun-ajaran/tambah', [wakaController::class, 'createTahunAjaran'])->name('waka.tahun-ajaran.create');
    Route::post('/waka/tahun-ajaran', [wakaController::class, 'storeTahunAjaran'])->name('waka.tahun-ajaran.store');
    Route::get('/waka/tahun-ajaran/{tahunAjaran}/edit', [wakaController::class, 'editTahunAjaran'])->name('waka.tahun-ajaran.edit');
    Route::put('/waka/tahun-ajaran/{tahunAjaran}', [wakaController::class, 'updateTahunAjaran'])->name('waka.tahun-ajaran.update');
    Route::delete('/waka/tahun-ajaran/{tahunAjaran}', [wakaController::class, 'destroyTahunAjaran'])->name('waka.tahun-ajaran.destroy');

    // Kelas
    Route::get('/waka/kelas/data', [wakaController::class, 'dataKelas'])->name('waka.kelas.data');
    Route::get('/waka/kelas/tambah', [wakaController::class, 'createKelas'])->name('waka.kelas.create');
    Route::post('/waka/kelas', [wakaController::class, 'storeKelas'])->name('waka.kelas.store');
    Route::get('/waka/kelas/{kelas}/edit', [wakaController::class, 'editKelas'])->name('waka.kelas.edit');
    Route::put('/waka/kelas/{kelas}', [wakaController::class, 'updateKelas'])->name('waka.kelas.update');
    Route::delete('/waka/kelas/{kelas}', [wakaController::class, 'destroyKelas'])->name('waka.kelas.destroy');

    // Mata Pelajaran
    Route::get('/waka/mapel/data', [wakaController::class, 'dataMapel'])->name('waka.mapel.data');
    Route::get('/waka/mapel/tambah', [wakaController::class, 'createMapel'])->name('waka.mapel.create');
    Route::post('/waka/mapel', [wakaController::class, 'storeMapel'])->name('waka.mapel.store');
    Route::get('/waka/mapel/{mapel}/edit', [wakaController::class, 'editMapel'])->name('waka.mapel.edit');
    Route::put('/waka/mapel/{mapel}', [wakaController::class, 'updateMapel'])->name('waka.mapel.update');
    Route::delete('/waka/mapel/{mapel}', [wakaController::class, 'destroyMapel'])->name('waka.mapel.destroy');

    // Jam Pelajaran
    Route::get('/waka/jam-pelajaran/data', [wakaController::class, 'dataJamPelajaran'])->name('waka.jam-pelajaran.data');
    Route::get('/waka/jam-pelajaran/tambah', [wakaController::class, 'createJamPelajaran'])->name('waka.jam-pelajaran.create');
    Route::post('/waka/jam-pelajaran', [wakaController::class, 'storeJamPelajaran'])->name('waka.jam-pelajaran.store');
    Route::get('/waka/jam-pelajaran/{jamPelajaran}/edit', [wakaController::class, 'editJamPelajaran'])->name('waka.jam-pelajaran.edit');
    Route::put('/waka/jam-pelajaran/{jamPelajaran}', [wakaController::class, 'updateJamPelajaran'])->name('waka.jam-pelajaran.update');
    Route::delete('/waka/jam-pelajaran/{jamPelajaran}', [wakaController::class, 'destroyJamPelajaran'])->name('waka.jam-pelajaran.destroy');

    // Jadwal Mengajar & Kegiatan Sekolah
    Route::get('/waka/jadwal/data', [wakaController::class, 'dataJadwal'])->name('waka.jadwal.data');
    Route::post('/waka/jadwal/publish', [wakaController::class, 'togglePublikasiJadwal'])->name('waka.jadwal.publish');
    Route::get('/waka/jadwal/tambah', [wakaController::class, 'createJadwal'])->name('waka.jadwal.create');
    Route::get('/waka/jadwal/preview', [wakaController::class, 'previewJadwal'])->name('waka.jadwal.preview');
    Route::post('/waka/jadwal/cek-bentrok', [wakaController::class, 'checkJadwalConflict'])->name('waka.jadwal.check');
    Route::post('/waka/jadwal', [wakaController::class, 'storeJadwal'])->name('waka.jadwal.store');
    Route::get('/waka/jadwal/{jadwal}/edit', [wakaController::class, 'editJadwal'])->name('waka.jadwal.edit');
    Route::put('/waka/jadwal/{jadwal}', [wakaController::class, 'updateJadwal'])->name('waka.jadwal.update');
    Route::delete('/waka/jadwal/{jadwal}', [wakaController::class, 'destroyJadwal'])->name('waka.jadwal.destroy');

    // Kegiatan Sekolah (Upacara, Istirahat, Apel, dsb)
    Route::get('/waka/kegiatan/tambah', [wakaController::class, 'createKegiatan'])->name('waka.kegiatan.create');
    Route::post('/waka/kegiatan', [wakaController::class, 'storeKegiatan'])->name('waka.kegiatan.store');
    Route::get('/waka/kegiatan/{kegiatan}/edit', [wakaController::class, 'editKegiatan'])->name('waka.kegiatan.edit');
    Route::put('/waka/kegiatan/{kegiatan}', [wakaController::class, 'updateKegiatan'])->name('waka.kegiatan.update');
    Route::delete('/waka/kegiatan/{kegiatan}', [wakaController::class, 'destroyKegiatan'])->name('waka.kegiatan.destroy');

    // Monitoring Absensi
    Route::get('/waka/absensi/monitoring', [wakaController::class, 'monitoringAbsensi'])->name('waka.absensi.monitoring');
    Route::get('/waka/absensi/cetak', [wakaController::class, 'cetakLaporan'])->name('waka.absensi.cetak');

    // Pengelolaan Izin / Sakit
    Route::get('/waka/izin/data', [wakaController::class, 'dataIzin'])->name('waka.izin.data');
    Route::put('/waka/izin/{izin}', [wakaController::class, 'updateStatusIzin'])->name('waka.izin.update');
});
