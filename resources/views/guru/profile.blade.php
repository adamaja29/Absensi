@extends('layouts.guru.main')
@section('title', 'Profil Guru')

@section('content')
<div class="mb-3">
    <div class="text-secondary small">Akun Pengguna</div>
    <div class="fw-bold fs-3 text-dark">Profil & Keamanan</div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show p-3 mb-3" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill fs-3 text-success"></i>
            <div>
                <div class="fw-bold">Berhasil!</div>
                <div class="small">{{ session('success') }}</div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show p-3 mb-3" role="alert">
        <div class="fw-bold small mb-1">Pembaruan Gagal:</div>
        <ul class="mb-0 ps-3 small">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- HEADER RINGKASAN PROFIL GURU -->
<div class="card-mobile p-3 mb-3 text-center">
    <div class="d-flex justify-content-center mb-2">
        <div class="user-avatar text-white shadow-sm" style="width: 68px; height: 68px; font-size: 2rem;">
            <i class="bi bi-person-fill"></i>
        </div>
    </div>
    <h3 class="fw-bold text-dark mb-1">{{ $user->name }}</h3>
    <div class="text-secondary small mb-2">{{ $user->email }}</div>

    <div class="d-flex justify-content-center gap-2 flex-wrap mb-3">
        <span class="badge bg-primary-lt px-2 py-1">
            <i class="bi bi-mortarboard me-1"></i> Guru Pengampu
        </span>
        @if($guru && $guru->nip)
            <span class="badge bg-secondary-lt px-2 py-1">
                NIP: {{ $guru->nip }}
            </span>
        @endif
    </div>

    <!-- STATS MENGAJAR -->
    <div class="row g-2 border-top pt-3 text-start">
        <div class="col-6">
            <div class="bg-light p-2 rounded">
                <div class="text-secondary small" style="font-size: 0.75rem;">Mata Pelajaran</div>
                <div class="fw-bold text-dark text-truncate small" title="{{ $mapelDiampu->implode(', ') }}">
                    {{ $mapelDiampu->isNotEmpty() ? $mapelDiampu->implode(', ') : 'Belum Ada' }}
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="bg-light p-2 rounded">
                <div class="text-secondary small" style="font-size: 0.75rem;">Total Mengajar</div>
                <div class="fw-bold text-dark small">
                    {{ $totalJamMengajar }} Jam Pelajaran
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FORMULIR EDIT PROFIL -->
<div class="card-mobile p-3 mb-4">
    <form action="{{ route('guru.profile.update') }}" method="POST">
        @csrf
        @method('PUT')

        <!-- SECTION 1: DATA GURU -->
        <div class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                <i class="bi bi-person-lines-fill text-primary fs-5"></i>
                <h5 class="fw-bold text-dark m-0">Informasi Pribadi</h5>
            </div>

            <div class="mb-3">
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

            <div class="mb-3">
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

            <div class="mb-3">
                <label class="form-label">Nomor Induk Pegawai (NIP)</label>
                <input type="text" 
                       name="nip" 
                       class="form-control @error('nip') is-invalid @enderror" 
                       value="{{ old('nip', $guru?->nip) }}" 
                       placeholder="Contoh: 198801012010011001">
                @error('nip')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Nomor WhatsApp / HP</label>
                <input type="text" 
                       name="no_hp" 
                       class="form-control @error('no_hp') is-invalid @enderror" 
                       value="{{ old('no_hp', $guru?->no_hp) }}" 
                       placeholder="Contoh: 081234567890">
                @error('no_hp')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- SECTION 2: GANTI PASSWORD -->
        <div class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-2 pb-2 border-bottom">
                <i class="bi bi-shield-lock-fill text-primary fs-5"></i>
                <h5 class="fw-bold text-dark m-0">Ubah Kata Sandi</h5>
            </div>
            <div class="small text-muted mb-3">
                Biarkan kosong jika tidak ingin mengganti password akun.
            </div>

            <div class="mb-3">
                <label class="form-label">Password Saat Ini</label>
                <input type="password" 
                       name="current_password" 
                       class="form-control @error('current_password') is-invalid @enderror" 
                       placeholder="Password lama Anda">
                @error('current_password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Password Baru</label>
                <input type="password" 
                       name="password" 
                       class="form-control @error('password') is-invalid @enderror" 
                       placeholder="Minimal 8 karakter">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Konfirmasi Password Baru</label>
                <input type="password" 
                       name="password_confirmation" 
                       class="form-control" 
                       placeholder="Ulangi password baru">
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
            <i class="bi bi-check2-circle me-1"></i> Simpan Pembaruan Profil
        </button>
    </form>
</div>
@endsection
