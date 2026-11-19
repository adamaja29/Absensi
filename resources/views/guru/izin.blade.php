@extends('layouts.guru.main')
@section('title', 'Izin & Sakit')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <div class="text-secondary small">Dispensasi Kehadiran</div>
        <div class="fw-bold fs-3 text-dark">Izin & Sakit</div>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show p-3 mb-3" role="alert" >
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill fs-3 text-success"></i>
            <div>
                <div class="fw-bold">Berhasil!</div>
                <div class="small">{{ session('success') }}</div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show p-3 mb-3" role="alert" >
        <div class="fw-bold small mb-1">Pengajuan Gagal:</div>
        <ul class="mb-0 ps-3 small">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- NAV TABS -->
<div class="d-flex gap-2 mb-3">
    <button type="button" id="btn-tab-form" class="btn btn-sm btn-primary flex-fill rounded-pill py-2" onclick="showIzinTab('form')">
        <i class="bi bi-pencil-square me-1"></i> Ajukan Baru
    </button>
    <button type="button" id="btn-tab-riwayat" class="btn btn-sm btn-outline-secondary flex-fill rounded-pill py-2" onclick="showIzinTab('riwayat')">
        <i class="bi bi-clock-history me-1"></i> Riwayat ({{ $daftarIzin->total() }})
    </button>
</div>

<!-- SECTION 1: FORM PENGAJUAN -->
<div id="section-form" class="card-mobile p-3 mb-3">
    <h4 class="fw-bold text-dark mb-3">Formulir Permohonan Izin / Sakit</h4>
    <form action="{{ route('guru.izin.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label class="form-label required small fw-bold">Jenis Pengajuan</label>
            <select name="jenis" class="form-select"  required>
                <option value="izin" {{ old('jenis') == 'izin' ? 'selected' : '' }}>Izin (Keperluan Pribadi / Tugas Luar)</option>
                <option value="sakit" {{ old('jenis') == 'sakit' ? 'selected' : '' }}>Sakit</option>
            </select>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-6">
                <label class="form-label required small fw-bold">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" class="form-control" value="{{ old('tanggal_mulai', now()->toDateString()) }}"  required>
            </div>
            <div class="col-6">
                <label class="form-label required small fw-bold">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" class="form-control" value="{{ old('tanggal_selesai', now()->toDateString()) }}"  required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label required small fw-bold">Alasan Berhalangan Hadir</label>
            <textarea name="alasan" class="form-control" rows="3" placeholder="Tuliskan keterangan / alasan ketidakhadiran..."  required>{{ old('alasan') }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Foto / Dokumen Surat Keterangan (Opsional)</label>
            <input type="file" name="bukti_file" class="form-control" accept="image/*,.pdf" >
            <div class="text-secondary" style="font-size: var(--text-xs); margin-top: var(--space-2xs);">Foto surat dokter atau surat izin (JPG, PNG, PDF maks 3MB).</div>
        </div>

        <button type="submit" class="btn-mobile-primary mt-2">
            <i class="bi bi-send-fill"></i> Kirim Permohonan Izin
        </button>
    </form>
</div>

<!-- SECTION 2: RIWAYAT PENGAJUAN -->
<div id="section-riwayat" class="d-none">
    @forelse ($daftarIzin as $item)
        <div class="card-mobile p-3 mb-2">
            <div class="d-flex justify-content-between align-items-start mb-2">
                @if($item->jenis === 'sakit')
                    <span class="badge bg-purple-lt px-2 py-1"><i class="bi bi-hospital me-1"></i>Sakit</span>
                @else
                    <span class="badge bg-info-lt px-2 py-1"><i class="bi bi-envelope me-1"></i>Izin</span>
                @endif

                @if($item->status === 'menunggu')
                    <span class="badge bg-warning-lt">Menunggu persetujuan</span>
                @elseif($item->status === 'disetujui')
                    <span class="badge bg-success-lt"><i class="bi bi-check2 me-1"></i>Disetujui</span>
                @else
                    <span class="badge bg-danger-lt"><i class="bi bi-x me-1"></i>Ditolak</span>
                @endif
            </div>

            <div class="fw-bold text-dark mb-1">
                {{ $item->tanggal_mulai->format('d/m/Y') }} s.d. {{ $item->tanggal_selesai->format('d/m/Y') }}
            </div>

            <div class="text-secondary small mb-2">{{ $item->alasan }}</div>

            @if($item->catatan_waka)
                <div class="well p-2 small mb-2">
                    <span class="text-secondary fw-semibold">Catatan Waka:</span> {{ $item->catatan_waka }}
                </div>
            @endif

            @if($item->bukti_file)
                <div class="mt-2 pt-2 border-top">
                    <a href="{{ route('guru.izin.bukti', $item) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        <i class="bi bi-paperclip me-1"></i>Lihat Lampiran Surat
                    </a>
                </div>
            @endif
        </div>
    @empty
        <div class="card-mobile p-4 text-center">
            <i class="bi bi-envelope-paper text-muted fs-1 mb-2 d-block"></i>
            <div class="fw-semibold text-secondary small">Belum pernah mengajukan izin atau sakit.</div>
        </div>
    @endforelse

    <div class="d-flex justify-content-center mt-3">
        {{ $daftarIzin->links() }}
    </div>
</div>

<script>
function showIzinTab(tab) {
    const secForm = document.getElementById('section-form');
    const secRiwayat = document.getElementById('section-riwayat');
    const btnForm = document.getElementById('btn-tab-form');
    const btnRiwayat = document.getElementById('btn-tab-riwayat');

    if (tab === 'form') {
        secForm.classList.remove('d-none');
        secRiwayat.classList.add('d-none');
        btnForm.className = 'btn btn-sm btn-primary flex-fill rounded-pill py-2';
        btnRiwayat.className = 'btn btn-sm btn-outline-secondary flex-fill rounded-pill py-2';
    } else {
        secForm.classList.add('d-none');
        secRiwayat.classList.remove('d-none');
        btnForm.className = 'btn btn-sm btn-outline-secondary flex-fill rounded-pill py-2';
        btnRiwayat.className = 'btn btn-sm btn-primary flex-fill rounded-pill py-2';
    }
}
</script>
@endsection
