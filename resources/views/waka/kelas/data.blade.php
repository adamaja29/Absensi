@extends('layouts.waka.main')
@section('title', 'Data Kelas')
@section('content')
<div class="container-xl admin-page">
    <div class="card admin-content-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="card-title">Data Kelas</h3>
                <div class="text-secondary mt-1" style="font-size: 0.875rem;">Kelola data kelas dan rombongan belajar.</div>
            </div>
            <a href="{{ route('waka.kelas.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                Tambah Kelas
            </a>
        </div>

        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif

            <form action="{{ route('waka.kelas.data') }}" method="GET" class="row g-2 mb-3">
                <div class="col-md-9">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama kelas, tingkat, atau jurusan" value="{{ old('search', $search ?? '') }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Cari</button>
                    <a href="{{ route('waka.kelas.data') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kelas</th>
                            <th>Tingkat</th>
                            <th>Jurusan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kelas as $index => $item)
                            <tr>
                                <td>{{ $kelas->firstItem() + $index }}</td>
                                <td class="fw-bold">{{ $item->nama_kelas }}</td>
                                <td>{{ $item->tingkat ?? '-' }}</td>
                                <td>{{ $item->jurusan ?? '-' }}</td>
                                <td>
                                    <div class="btn-list flex-nowrap">
                                        <a href="{{ route('waka.kelas.edit', $item) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil-square me-1"></i>Edit
                                        </a>
                                        <form action="{{ route('waka.kelas.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus kelas ini?')">
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
                                <td colspan="5" class="text-center text-secondary py-5">Belum ada data kelas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $kelas->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
