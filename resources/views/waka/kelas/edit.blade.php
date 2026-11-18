@extends('layouts.waka.main')
@section('title', 'Edit Kelas')
@section('content')
<div class="container-xl admin-page">
    <div class="card admin-content-card">
        <div class="card-header">
            <h3 class="card-title">Edit Data Kelas</h3>
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

            <form action="{{ route('waka.kelas.update', $kelas) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Nama Kelas</label>
                        <input type="text" name="nama_kelas" class="form-control" value="{{ old('nama_kelas', $kelas->nama_kelas) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Tingkat</label>
                        <select name="tingkat" class="form-select">
                            <option value="">Pilih Tingkat</option>
                            <option value="X" {{ old('tingkat', $kelas->tingkat) == 'X' ? 'selected' : '' }}>X (Sepuluh)</option>
                            <option value="XI" {{ old('tingkat', $kelas->tingkat) == 'XI' ? 'selected' : '' }}>XI (Sebelas)</option>
                            <option value="XII" {{ old('tingkat', $kelas->tingkat) == 'XII' ? 'selected' : '' }}>XII (Dua Belas)</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Jurusan</label>
                        <input type="text" name="jurusan" class="form-control" value="{{ old('jurusan', $kelas->jurusan) }}">
                    </div>

                    <div class="col-12 mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <a href="{{ route('waka.kelas.data') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Perbarui
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
