@extends('layouts.waka.main')
@section('title', 'Tambah Mata Pelajaran')
@section('content')
<div class="container-xl admin-page">
    <div class="card admin-content-card">
        <div class="card-header">
            <h3 class="card-title">Tambah Mata Pelajaran Baru</h3>
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

            <form action="{{ route('waka.mapel.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Kode Mata Pelajaran</label>
                        <input type="text" name="kode" class="form-control" placeholder="Contoh: MTK, BIN, PWEB" value="{{ old('kode') }}">
                    </div>

                    <div class="col-md-8">
                        <label class="form-label required">Nama Mata Pelajaran</label>
                        <input type="text" name="nama" class="form-control" placeholder="Contoh: Matematika" value="{{ old('nama') }}" required>
                    </div>

                    <div class="col-12 mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <a href="{{ route('waka.mapel.data') }}" class="btn btn-outline-secondary">Batal</a>
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
