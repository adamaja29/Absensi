@extends('layouts.waka.main')
@section('title', 'Persetujuan Izin Guru')
@section('content')
<div class="container-xl admin-page">
    <div class="card admin-content-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="card-title">Permohonan Izin & Sakit Guru</h3>
                <div class="text-secondary mt-1" style="font-size: var(--text-sm);">
                    Verifikasi dan setujui surat keterangan izin atau sakit dari guru yang berhalangan hadir.
                </div>
            </div>
        </div>

        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif

            <!-- FILTER STATUS -->
            <form action="{{ route('waka.izin.data') }}" method="GET" class="row g-2 mb-3">
                <div class="col-md-9">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Status Pengajuan</option>
                        <option value="menunggu" {{ request('status') == 'menunggu' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                        <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('waka.izin.data') }}" class="btn btn-outline-secondary w-100">Reset Filter</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-vcenter card-table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Guru</th>
                            <th>Jenis</th>
                            <th>Rentang Tanggal</th>
                            <th>Alasan</th>
                            <th>Bukti Surat</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($izin as $index => $item)
                            <tr>
                                <td>{{ $izin->firstItem() + $index }}</td>
                                <td>
                                    <div class="fw-bold">{{ $item->guru->user->name ?? '-' }}</div>
                                    <div class="small text-secondary">NIP: {{ $item->guru->nip ?? '-' }}</div>
                                </td>
                                <td>
                                    @if($item->jenis === 'sakit')
                                        <span class="badge bg-purple-lt text-capitalize"><i class="bi bi-hospital me-1"></i>Sakit</span>
                                    @else
                                        <span class="badge bg-info-lt text-capitalize"><i class="bi bi-envelope me-1"></i>Izin</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $item->tanggal_mulai->format('d/m/Y') }}</div>
                                    <div class="small text-secondary">s.d. {{ $item->tanggal_selesai->format('d/m/Y') }}</div>
                                </td>
                                <td style="max-width: 250px;">{{ $item->alasan }}</td>
                                <td>
                                    @if($item->bukti_file)
                                        <a href="{{ route('waka.izin.bukti', $item) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-paperclip me-1"></i>Lihat Bukti
                                        </a>
                                    @else
                                        <span class="text-secondary small">Tidak ada lampiran</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->status === 'menunggu')
                                        <span class="badge bg-warning-lt">Menunggu ACC</span>
                                    @elseif($item->status === 'disetujui')
                                        <span class="badge bg-success-lt">Disetujui</span>
                                    @else
                                        <span class="badge bg-danger-lt">Ditolak</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->status === 'menunggu')
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalProses{{ $item->id }}">
                                            Proses ACC
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalProses{{ $item->id }}">
                                            Ubah Status
                                        </button>
                                    @endif

                                    <!-- MODAL PROSES ACC -->
                                    <div class="modal fade" id="modalProses{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <form action="{{ route('waka.izin.update', $item) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Konfirmasi Pengajuan Izin/Sakit</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <div class="text-secondary small">Guru</div>
                                                            <div class="fw-bold fs-5">{{ $item->guru->user->name ?? '-' }}</div>
                                                            <div class="text-muted">{{ ucfirst($item->jenis) }}: {{ $item->tanggal_mulai->format('d M Y') }} s.d. {{ $item->tanggal_selesai->format('d M Y') }}</div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label required">Tindakan</label>
                                                            <select name="status" class="form-select" required>
                                                                <option value="disetujui" {{ $item->status === 'disetujui' ? 'selected' : '' }}>Setujui (Disetujui)</option>
                                                                <option value="ditolak" {{ $item->status === 'ditolak' ? 'selected' : '' }}>Tolak Permohonan</option>
                                                            </select>
                                                            <small class="text-secondary">Jika disetujui, jadwal mengajar guru pada rentang tanggal tersebut otomatis diberi status Izin/Sakit.</small>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">Catatan Waka (Opsional)</label>
                                                            <textarea name="catatan_waka" class="form-control" rows="3" placeholder="Tulis catatan atau alasan penolakan jika ada...">{{ old('catatan_waka', $item->catatan_waka) }}</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary">Simpan Keputusan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-secondary py-5">
                                    Belum ada data permohonan izin atau sakit.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $izin->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
