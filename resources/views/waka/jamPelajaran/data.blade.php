@extends('layouts.waka.main')
@section('title', 'Jam Pelajaran')
@section('content')
<div class="container-xl admin-page">
    <div class="card admin-content-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="card-title">Data Jam Pelajaran (Sesi)</h3>
                <div class="text-secondary mt-1" style="font-size: var(--text-sm);">Atur urutan sesi dan rentang waktu mengajar di sekolah.</div>
            </div>
            <a href="{{ route('waka.jam-pelajaran.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                Tambah Jam Pelajaran
            </a>
        </div>

        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif

            <form action="{{ route('waka.jam-pelajaran.data') }}" method="GET" class="row g-2 mb-3">
                <div class="col-md-9">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama sesi" value="{{ old('search', $search ?? '') }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Cari</button>
                    <a href="{{ route('waka.jam-pelajaran.data') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Urutan</th>
                            <th>Nama Sesi</th>
                            <th>Jenis Sesi</th>
                            <th>Jam Mulai</th>
                            <th>Jam Selesai</th>
                            <th>Durasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($jamPelajaran as $item)
                            <tr>
                                <td>
                                    <span class="badge bg-secondary-lt fw-bold">Ke-{{ $item->urutan }}</span>
                                </td>
                                <td class="fw-bold">{{ $item->nama_sesi }}</td>
                                <td>
                                    @if($item->jenis === 'pelajaran')
                                        <span class="badge bg-primary-lt fw-bold"><i class="bi bi-book me-1"></i>Pelajaran (KBM)</span>
                                    @elseif($item->jenis === 'upacara')
                                        <span class="badge bg-indigo-lt fw-bold"><i class="bi bi-flag me-1"></i>Upacara / Apel</span>
                                    @elseif($item->jenis === 'istirahat')
                                        <span class="badge bg-warning-lt fw-bold"><i class="bi bi-cup-hot me-1"></i>Istirahat</span>
                                    @else
                                        <span class="badge bg-secondary-lt fw-bold"><i class="bi bi-calendar-event me-1"></i>Kegiatan Lain</span>
                                    @endif
                                </td>
                                <td>{{ substr($item->jam_mulai, 0, 5) }} WIB</td>
                                <td>{{ substr($item->jam_selesai, 0, 5) }} WIB</td>
                                <td>
                                    @php
                                        $mulai = \Carbon\Carbon::parse($item->jam_mulai);
                                        $selesai = \Carbon\Carbon::parse($item->jam_selesai);
                                        $diff = $mulai->diffInMinutes($selesai);
                                    @endphp
                                    <span class="badge bg-teal-lt">{{ $diff }} Menit</span>
                                </td>
                                <td>
                                    <div class="btn-list flex-nowrap">
                                        <a href="{{ route('waka.jam-pelajaran.edit', $item) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil-square me-1"></i>Edit
                                        </a>
                                        <form action="{{ route('waka.jam-pelajaran.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus sesi pelajaran ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash me-1"></i>Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-5">Belum ada data sesi jam pelajaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $jamPelajaran->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
