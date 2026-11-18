@extends('layouts.waka.main')
@section('title', 'Tambah Kelas')
@section('content')
<div class="container-xl admin-page">
    <div class="card admin-content-card">
        <div class="card-header">
            <h3 class="card-title">Tambah Kelas Baru</h3>
        </div>

        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('waka.kelas.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Nama Kelas</label>
                        <input type="text" name="nama_kelas" class="form-control" placeholder="Contoh: X RPL 1" value="{{ old('nama_kelas') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Tingkat</label>
                        <select name="tingkat" class="form-select">
                            <option value="">Pilih Tingkat</option>
                            <option value="X" {{ old('tingkat') == 'X' ? 'selected' : '' }}>X (Sepuluh)</option>
                            <option value="XI" {{ old('tingkat') == 'XI' ? 'selected' : '' }}>XI (Sebelas)</option>
                            <option value="XII" {{ old('tingkat') == 'XII' ? 'selected' : '' }}>XII (Dua Belas)</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Jurusan</label>
                        <input type="text" name="jurusan" class="form-control" placeholder="Contoh: RPL, TKJ, AKL" value="{{ old('jurusan') }}">
                    </div>

                    <div class="col-12 mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <a href="{{ route('waka.kelas.data') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
