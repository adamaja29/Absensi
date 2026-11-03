<?php

use App\Http\Controllers\authController;
use App\Http\Controllers\adminController;
use App\Http\Controllers\wakaController;
use Illuminate\Support\Facades\Route;

// Halaman Login
Route::get('/', function () {
    return view('auth.login');
})->name('login');

// Proses Login dan Logout
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['role:admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');})->name('admin.dashboard');

    Route::get('/admin/guru/data', [adminController::class, 'dataGuru'])->name('admin.guru.data');
    Route::get('/admin/guru/tambah', [adminController::class, 'createGuru'])->name('admin.guru.create');
    Route::post('/admin/guru', [adminController::class, 'storeGuru'])->name('admin.guru.store');
    Route::get('/admin/guru/{guru}/edit', [adminController::class, 'editGuru'])->name('admin.guru.edit');
    Route::put('/admin/guru/{guru}', [adminController::class, 'updateGuru'])->name('admin.guru.update');
    Route::delete('/admin/guru/{guru}', [adminController::class, 'destroyGuru'])->name('admin.guru.destroy');


    Route::get('/admin/waka/data', [adminController::class, 'dataWaka'])->name('admin.waka.data');
    Route::get('/admin/waka/tambah', [adminController::class, 'createWaka'])->name('admin.waka.create');
    Route::post('/admin/waka', [adminController::class, 'storeWaka'])->name('admin.waka.store');
    Route::get('/admin/waka/{waka}/edit', [adminController::class, 'editWaka'])->name('admin.waka.edit');
    Route::put('/admin/waka/{waka}', [adminController::class, 'updateWaka'])->name('admin.waka.update');
    Route::delete('/admin/waka/{waka}', [adminController::class, 'destroyWaka'])->name('admin.waka.destroy');
});

Route::middleware(['role:guru'])->group(function () {
    Route::get('/guru/dashboard', function () {
        return view('guru.dashboard');})->name('guru.dashboard');
});

Route::middleware(['role:waka'])->group(function () {
    Route::get('/waka/dashboard', function () {
        return view('waka.dashboard');})->name('waka.dashboard');

    Route::get('/waka/tahun-ajaran/data', [wakaController::class, 'dataTahunAjaran'])->name('waka.tahun-ajaran.data');
    Route::get('/waka/tahun-ajaran/tambah', [wakaController::class, 'createTahunAjaran'])->name('waka.tahun-ajaran.create');
    Route::post('/waka/tahun-ajaran', [wakaController::class, 'storeTahunAjaran'])->name('waka.tahun-ajaran.store');
    Route::get('/waka/tahun-ajaran/{tahunAjaran}/edit', [wakaController::class, 'editTahunAjaran'])->name('waka.tahun-ajaran.edit');
    Route::put('/waka/tahun-ajaran/{tahunAjaran}', [wakaController::class, 'updateTahunAjaran'])->name('waka.tahun-ajaran.update');
    Route::delete('/waka/tahun-ajaran/{tahunAjaran}', [wakaController::class, 'destroyTahunAjaran'])->name('waka.tahun-ajaran.destroy');
});


