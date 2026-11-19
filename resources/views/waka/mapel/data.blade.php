@extends('layouts.waka.main')
@section('title', 'Mata Pelajaran')
@section('content')
<div class="container-xl admin-page">
    <div class="card admin-content-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="card-title">Data Mata Pelajaran</h3>
                <div class="text-secondary mt-1" style="font-size: var(--text-sm);">Kelola daftar mata pelajaran yang diajarkan di sekolah.</div>
            </div>
            <a href="{{ route('waka.mapel.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                Tambah Mata Pelajaran
            </a>
        </div>

        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif

            <form action="{{ route('waka.mapel.data') }}" method="GET" class="row g-2 mb-3">
                <div class="col-md-9">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama mata pelajaran atau kode" value="{{ old('search', $search ?? '') }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Cari</button>
                    <a href="{{ route('waka.mapel.data') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Mapel</th>
                            <th>Nama Mata Pelajaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($mapel as $index => $item)
                            <tr>
                                <td>{{ $mapel->firstItem() + $index }}</td>
                                <td>
                                    @if($item->kode)
                                        <span class="badge bg-blue-lt">{{ $item->kode }}</span>
                                    @else
                                        <span class="text-secondary">-</span>
                                    @endif
                                </td>
                                <td class="fw-bold">{{ $item->nama }}</td>
                                <td>
                                    <div class="btn-list flex-nowrap">
                                        <a href="{{ route('waka.mapel.edit', $item) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil-square me-1"></i>Edit
                                        </a>
                                        <form action="{{ route('waka.mapel.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus mata pelajaran ini?')">
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
                                <td colspan="4" class="text-center text-secondary py-5">Belum ada data mata pelajaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $mapel->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
