@extends('layouts.waka.main')
@section('title', 'Edit Jam Pelajaran')
@section('content')
<div class="container-xl admin-page">
    <div class="card admin-content-card">
        <div class="card-header">
            <h3 class="card-title">Edit Jam Pelajaran (Sesi)</h3>
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

            <form action="{{ route('waka.jam-pelajaran.update', $jamPelajaran) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label required">Urutan Sesi</label>
                        <input type="number" name="urutan" class="form-control" value="{{ old('urutan', $jamPelajaran->urutan) }}" min="1" required>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label required">Nama Sesi</label>
                        <input type="text" name="nama_sesi" class="form-control" value="{{ old('nama_sesi', $jamPelajaran->nama_sesi) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Jenis Kegiatan Sesi</label>
                        <select name="jenis" class="form-select" required>
                            <option value="pelajaran" {{ old('jenis', $jamPelajaran->jenis) == 'pelajaran' ? 'selected' : '' }}>Jam Pelajaran (KBM - Wajib Absen)</option>
                            <option value="upacara" {{ old('jenis', $jamPelajaran->jenis) == 'upacara' ? 'selected' : '' }}>Upacara / Apel Bendera (Non-KBM)</option>
                            <option value="istirahat" {{ old('jenis', $jamPelajaran->jenis) == 'istirahat' ? 'selected' : '' }}>Istirahat (Non-KBM)</option>
                            <option value="kegiatan_lain" {{ old('jenis', $jamPelajaran->jenis) == 'kegiatan_lain' ? 'selected' : '' }}>Kegiatan Lain (Non-KBM)</option>
                        </select>
                        <small class="text-secondary">Pilih "Jam Pelajaran" jika sesi digunakan mengajar kelas.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required">Jam Mulai</label>
                        <input type="time" name="jam_mulai" class="form-control" value="{{ old('jam_mulai', substr($jamPelajaran->jam_mulai, 0, 5)) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required">Jam Selesai</label>
                        <input type="time" name="jam_selesai" class="form-control" value="{{ old('jam_selesai', substr($jamPelajaran->jam_selesai, 0, 5)) }}" required>
                    </div>

                    <div class="col-12 mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <a href="{{ route('waka.jam-pelajaran.data') }}" class="btn btn-outline-secondary">Batal</a>
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
