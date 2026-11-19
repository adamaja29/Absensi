@extends('layouts.guru.main')
@section('title', 'Riwayat Absensi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <div class="text-secondary small">Rekap Presensi</div>
        <div class="fw-bold fs-3 text-dark">Riwayat Absensi</div>
    </div>
</div>

<!-- FILTER CARD COLLAPSIBLE -->
<div class="card-mobile p-3 mb-3">
    <form action="{{ route('guru.riwayat') }}" method="GET" class="row g-2">
        <div class="col-6">
            <label class="form-label small mb-1">Tanggal</label>
            <input type="date" name="tanggal" class="form-control form-control-sm" value="{{ request('tanggal') }}" >
        </div>
        <div class="col-6">
            <label class="form-label small mb-1">Status</label>
            <select name="status" class="form-select form-select-sm" >
                <option value="">Semua</option>
                <option value="hadir" {{ request('status') == 'hadir' ? 'selected' : '' }}>Tepat Waktu</option>
                <option value="terlambat" {{ request('status') == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                <option value="izin" {{ request('status') == 'izin' ? 'selected' : '' }}>Izin</option>
                <option value="sakit" {{ request('status') == 'sakit' ? 'selected' : '' }}>Sakit</option>
            </select>
        </div>
        <div class="col-12 d-flex gap-2 mt-2">
            <button type="submit" class="btn btn-sm btn-primary w-100" >
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            @if(request('tanggal') || request('status'))
                <a href="{{ route('guru.riwayat') }}" class="btn btn-sm btn-outline-secondary" >Reset</a>
            @endif
        </div>
    </form>
</div>

<!-- LIST RIWAYAT CARDS -->
<div>
    @forelse ($riwayat as $item)
        <div class="card-mobile p-3 mb-2">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="badge bg-indigo-lt fw-bold px-2 py-1" >
                    <i class="bi bi-door-open me-1"></i>{{ $item->jadwalPelajaran->kelas->nama_kelas ?? '-' }}
                </span>
                <div>
                    @if($item->status === 'hadir')
                        <span class="badge bg-success-lt"><i class="bi bi-check-circle me-1"></i>Tepat Waktu</span>
                    @elseif($item->status === 'terlambat')
                        <span class="badge bg-warning-lt"><i class="bi bi-clock-history me-1"></i>Terlambat</span>
                    @elseif($item->status === 'izin')
                        <span class="badge bg-info-lt"><i class="bi bi-envelope me-1"></i>Izin</span>
                    @elseif($item->status === 'sakit')
                        <span class="badge bg-purple-lt"><i class="bi bi-hospital me-1"></i>Sakit</span>
                    @endif
                </div>
            </div>

            <div class="fw-bold fs-5 text-dark mb-1">{{ $item->jadwalPelajaran->mataPelajaran->nama ?? '-' }}</div>

            <div class="d-flex justify-content-between align-items-center text-secondary small mb-2" style="font-size: var(--text-xs);">
                <span><i class="bi bi-calendar3 me-1"></i>{{ $item->tanggal->translatedFormat('d M Y') }}</span>
                <span><i class="bi bi-clock me-1"></i>{{ substr($item->waktu_absen, 0, 5) }} WIB</span>
                @if($item->jarak_meter !== null)
                    <span class="badge bg-teal-lt">{{ $item->jarak_meter }} m</span>
                @endif
            </div>

            @if($item->materi_diajarkan)
                <div class="well p-2 small mt-2">
                    <div class="text-secondary fw-semibold" style="font-size: var(--text-xs);">Materi:</div>
                    <div class="text-dark">{{ $item->materi_diajarkan }}</div>
                    @if($item->catatan_siswa_tidak_masuk)
                        <div class="text-muted mt-1" style="font-size: var(--text-xs);">
                            <em>Catatan siswa: {{ $item->catatan_siswa_tidak_masuk }}</em>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @empty
        <div class="card-mobile p-4 text-center">
            <i class="bi bi-clock-history text-muted fs-1 mb-2 d-block"></i>
            <div class="fw-semibold text-secondary small">Belum ada riwayat absensi.</div>
        </div>
    @endforelse

    <div class="d-flex justify-content-center mt-3">
        {{ $riwayat->links() }}
    </div>
</div>
@endsection
