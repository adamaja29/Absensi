@extends ('layouts.admin.main')
@section('title','Data Guru')
@section('content')
<div class="container-xl admin-page">
    <div class="card admin-content-card">
        <!-- Header di dalam Card -->
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="card-title">Data Guru</h3>
                <div class="text-secondary mt-1" style="font-size: 0.875rem;">Kelola informasi data guru dan akun pengguna.</div>
            </div>
            <a href="{{ route('admin.guru.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                Tambah Guru
            </a>
        </div>

        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success" role="alert">{{ session('success') }}</div>
            @endif

            <!-- Form Pencarian -->
            <form action="{{ route('admin.guru.data') }}" method="GET" class="row g-2 mb-3">
                <div class="col-md-9">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama, NIP, atau email" value="{{ old('search', $search ?? '') }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Cari</button>
                    <a href="{{ route('admin.guru.data') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>NIP</th>
                            <th>Email</th>
                            <th>No. HP</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($guru as $index => $guruItem)
                            <tr>
                                <td>{{ $guru->firstItem() ? $guru->firstItem() + $index : $index + 1 }}</td>
                                <td>{{ $guruItem->user->name ?? '-' }}</td>
                                <td>{{ $guruItem->nip ?? '-' }}</td>
                                <td>{{ $guruItem->user->email ?? '-' }}</td>
                                <td>{{ $guruItem->no_hp ?? '-' }}</td>
                                <td>
                                    <div class="btn-list flex-nowrap">
                                        <a href="{{ route('admin.guru.edit', $guruItem) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil-square me-1"></i>
                                            Edit
                                        </a>
                                        <form action="{{ route('admin.guru.destroy', $guruItem) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data guru ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash me-1"></i>
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-5">Belum ada data guru.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            @if ($guru->hasPages())
                <div class="d-flex justify-content-center justify-content-md-end mt-3">
                    {{ $guru->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection