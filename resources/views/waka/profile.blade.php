@extends('layouts.waka.main')
@section('title', 'Profil Saya')

@section('content')
<div class="container-xl admin-page">
    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title fs-2 fw-bold text-dark">
                    <i class="bi bi-person-gear me-2 text-primary"></i>Pengaturan Profil
                </h2>
                <div class="text-secondary mt-1">
                    Kelola informasi akun Waka Kurikulum dan perbarui kata sandi Anda.
                </div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="{{ route('waka.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-3 me-3 text-success"></i>
                <div>
                    <h4 class="alert-title mb-1 fw-bold">Berhasil!</h4>
                    <div class="text-secondary">{{ session('success') }}</div>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-3 me-3 text-danger"></i>
                <div>
                    <h4 class="alert-title mb-1 fw-bold">Terjadi Kesalahan!</h4>
                    <ul class="mb-0 ps-3 small text-secondary">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- RINGKASAN PROFIL (KOLOM KIRI) -->
        <div class="col-lg-4">
            <div class="card admin-content-card text-center p-4">
                <div class="d-flex justify-content-center mb-3">
                    <span class="avatar avatar-xl rounded-circle bg-primary-lt shadow-sm" style="width: 88px; height: 88px;">
                        <i class="bi bi-person-circle fs-1 text-primary"></i>
                    </span>
                </div>
                <h3 class="mb-1 fw-bold text-dark">{{ $user->name }}</h3>
                <div class="text-secondary small mb-3">{{ $user->email }}</div>

                <div class="mb-3">
                    <span class="badge bg-primary-lt px-3 py-2 fs-6">
                        <i class="bi bi-shield-check me-1"></i> Waka Kurikulum
                    </span>
                </div>

                <div class="border-top pt-3 text-start small text-secondary">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Role Pengguna</span>
                        <span class="fw-bold text-dark text-capitalize">{{ $user->role }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Terdaftar Sejak</span>
                        <span class="fw-bold text-dark">{{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Pembaruan Terakhir</span>
                        <span class="fw-bold text-dark">{{ $user->updated_at ? $user->updated_at->diffForHumans() : '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- FORM EDIT PROFIL (KOLOM KANAN) -->
        <div class="col-lg-8">
            <div class="card admin-content-card">
                <div class="card-header border-bottom">
                    <h3 class="card-title fw-bold">
                        <i class="bi bi-pencil-square me-2 text-primary"></i>Edit Data Profil & Kata Sandi
                    </h3>
                </div>

                <form action="{{ route('waka.profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        <!-- BAGIAN 1: INFORMASI AKUN -->
                        <div class="mb-4">
                            <h4 class="text-uppercase text-secondary fw-bold fs-6 mb-3 tracking-wider">
                                1. Informasi Akun
                            </h4>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label required">Nama Lengkap</label>
                                    <input type="text" 
                                           name="name" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $user->name) }}" 
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label required">Alamat Email</label>
                                    <input type="email" 
                                           name="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           value="{{ old('email', $user->email) }}" 
                                           required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- BAGIAN 2: UBAH KATA SANDI -->
                        <div class="mb-2">
                            <h4 class="text-uppercase text-secondary fw-bold fs-6 mb-1 tracking-wider">
                                2. Ubah Kata Sandi
                            </h4>
                            <div class="small text-muted mb-3">
                                Kosongkan bidang di bawah jika Anda tidak ingin mengubah kata sandi akun saat ini.
                            </div>

                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Password Saat Ini</label>
                                    <input type="password" 
                                           name="current_password" 
                                           class="form-control @error('current_password') is-invalid @enderror" 
                                           placeholder="Masukkan password lama untuk konfirmasi pergantian">
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Password Baru</label>
                                    <input type="password" 
                                           name="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           placeholder="Minimal 8 karakter">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Konfirmasi Password Baru</label>
                                    <input type="password" 
                                           name="password_confirmation" 
                                           class="form-control" 
                                           placeholder="Ulangi password baru">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-transparent d-flex justify-content-end gap-2 border-top py-3">
                        <a href="{{ route('waka.dashboard') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
