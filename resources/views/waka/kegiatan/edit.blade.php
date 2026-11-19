@extends('layouts.waka.main')
@section('title', 'Edit Kegiatan Sekolah')
@section('content')
<div class="container-xl admin-page">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card admin-content-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="card-title">Edit Kegiatan Sekolah</h3>
                        <div class="text-secondary small mt-1">
                            Perbarui agenda umum: {{ $kegiatan->nama_kegiatan }}
                        </div>
                    </div>
                    <a href="{{ route('waka.jadwal.data', ['tab' => 'kegiatan', 'hari' => $kegiatan->hari]) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Kembali
                    </a>
                </div>

                <form action="{{ route('waka.kegiatan.update', $kegiatan) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="alert alert-info d-flex align-items-start mb-4" role="alert">
                            <i class="bi bi-info-circle fs-3 me-3 text-info"></i>
                            <div>
                                <div class="fw-bold">Pemisahan Kegiatan Sekolah vs KBM Mengajar</div>
                                <div class="small text-secondary mt-1">
                                    Kegiatan umum seperti <strong>Upacara</strong> dan <strong>Istirahat</strong> bukan merupakan mata pelajaran. Sistem akan menampilkannya pada timeline harian seluruh guru & siswa, namun <strong>tidak mewajibkan absensi guru</strong>.
                                </div>
                            </div>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">
                                <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle me-2"></i>Gagal Memperbarui Kegiatan:</div>
                                <ul class="mb-0 ps-3 small">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required">Tahun Ajaran</label>
                                <select name="tahun_ajaran_id" class="form-select @error('tahun_ajaran_id') is-invalid @enderror" required>
                                    @foreach($daftarTahunAjaran as $ta)
                                        <option value="{{ $ta->id }}" {{ (old('tahun_ajaran_id', $kegiatan->tahun_ajaran_id) == $ta->id) ? 'selected' : '' }}>
                                            {{ $ta->nama }} - Semester {{ ucfirst($ta->semester) }} {{ $ta->status ? '(Aktif)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('tahun_ajaran_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required">Hari Pelaksanaan</label>
                                <select name="hari" class="form-select @error('hari') is-invalid @enderror" required>
                                    <option value="">-- Pilih Hari --</option>
                                    @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $hari)
                                        <option value="{{ $hari }}" {{ old('hari', $kegiatan->hari) == $hari ? 'selected' : '' }}>
                                            {{ $hari }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('hari')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required">Jenis Kegiatan</label>
                                <select name="jenis" class="form-select @error('jenis') is-invalid @enderror" required>
                                    <option value="">-- Pilih Jenis Kegiatan --</option>
                                    <option value="upacara" {{ old('jenis', $kegiatan->jenis) == 'upacara' ? 'selected' : '' }}>Upacara Bendera</option>
                                    <option value="istirahat" {{ old('jenis', $kegiatan->jenis) == 'istirahat' ? 'selected' : '' }}>Istirahat / Rehat</option>
                                    <option value="ibadah" {{ old('jenis', $kegiatan->jenis) == 'ibadah' ? 'selected' : '' }}>🕌 Ibadah / Shalat Berjamaah</option>
                                    <option value="senam" {{ old('jenis', $kegiatan->jenis) == 'senam' ? 'selected' : '' }}>🏃 Senam / Olahraga Bersama</option>
                                    <option value="literasi" {{ old('jenis', $kegiatan->jenis) == 'literasi' ? 'selected' : '' }}>📖 Gerakan Literasi Sekolah</option>
                                    <option value="kegiatan_lain" {{ old('jenis', $kegiatan->jenis) == 'kegiatan_lain' ? 'selected' : '' }}>📌 Kegiatan Sekolah Lainnya</option>
                                </select>
                                @error('jenis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required">Nama Kegiatan</label>
                                <input type="text" name="nama_kegiatan" class="form-control @error('nama_kegiatan') is-invalid @enderror"
                                       value="{{ old('nama_kegiatan', $kegiatan->nama_kegiatan) }}" required>
                                @error('nama_kegiatan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required">Jam Mulai (WIB)</label>
                                <input type="time" name="jam_mulai" class="form-control @error('jam_mulai') is-invalid @enderror"
                                       value="{{ old('jam_mulai', substr($kegiatan->jam_mulai, 0, 5)) }}" required>
                                @error('jam_mulai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required">Jam Selesai (WIB)</label>
                                <input type="time" name="jam_selesai" class="form-control @error('jam_selesai') is-invalid @enderror"
                                       value="{{ old('jam_selesai', substr($kegiatan->jam_selesai, 0, 5)) }}" required>
                                @error('jam_selesai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Lokasi Kegiatan</label>
                                <input type="text" name="lokasi" class="form-control @error('lokasi') is-invalid @enderror"
                                       value="{{ old('lokasi', $kegiatan->lokasi) }}">
                                @error('lokasi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Peserta</label>
                                <input type="text" name="peserta" class="form-control @error('peserta') is-invalid @enderror"
                                       value="{{ old('peserta', $kegiatan->peserta) }}">
                                @error('peserta')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Keterangan / Catatan Tambahan</label>
                                <textarea name="keterangan" rows="2" class="form-control @error('keterangan') is-invalid @enderror">{{ old('keterangan', $kegiatan->keterangan) }}</textarea>
                                @error('keterangan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <a href="{{ route('waka.jadwal.data', ['tab' => 'kegiatan', 'hari' => $kegiatan->hari]) }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Perbarui Kegiatan Sekolah
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
