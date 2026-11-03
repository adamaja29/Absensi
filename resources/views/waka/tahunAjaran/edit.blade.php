@extends('layouts.waka.main')
@section('title', 'Edit Tahun Ajaran')
@section('content')
<div class="container-xl admin-page">
    <div class="card admin-content-card">
        <div class="card-header">
            <h3 class="card-title">Edit Data Tahun Ajaran</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('waka.tahun-ajaran.update', $tahunAjaran) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Tahun Ajaran</label>
                        <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $tahunAjaran->nama) }}" maxlength="9" required>
                        @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">Semester</label>
                        <select name="semester" class="form-select @error('semester') is-invalid @enderror" required>
                            <option value="">Pilih Semester</option>
                            <option value="ganjil" @selected(old('semester', $tahunAjaran->semester) === 'ganjil')>Ganjil</option>
                            <option value="genap" @selected(old('semester', $tahunAjaran->semester) === 'genap')>Genap</option>
                        </select>
                        @error('semester')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror" value="{{ old('tanggal_mulai', $tahunAjaran->tanggal_mulai->format('Y-m-d')) }}" required>
                        @error('tanggal_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control @error('tanggal_selesai') is-invalid @enderror" value="{{ old('tanggal_selesai', $tahunAjaran->tanggal_selesai->format('Y-m-d')) }}" required>
                        @error('tanggal_selesai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-check">
                            <input type="checkbox" name="status" value="1" class="form-check-input" @checked(old('status', $tahunAjaran->status))>
                            <span class="form-check-label">Jadikan tahun ajaran aktif</span>
                        </label>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('waka.tahun-ajaran.data') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection