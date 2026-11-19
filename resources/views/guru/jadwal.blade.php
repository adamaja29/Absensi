@extends('layouts.guru.main')
@section('title', 'Jadwal Mingguan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <div class="text-secondary small">Jadwal Mengajar</div>
        <div class="fw-bold fs-3 text-dark">Jadwal Mingguan</div>
    </div>
    @if($tahunAktif)
        <span class="badge bg-primary-lt px-2 py-1" style="font-size: var(--text-xs);">{{ $tahunAktif->nama }}</span>
    @endif
</div>

@php
    $daftarHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
    $mapHariIni = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
    ];
    $hariSekarang = $mapHariIni[now()->format('l')] ?? 'Senin';
@endphp

@if(isset($isPublished) && !$isPublished)
    <div class="card-mobile p-3 mb-3 bg-warning-lt border-warning">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-lock-fill fs-3 text-warning"></i>
            <div>
                <div class="fw-bold text-dark">Jadwal Masih Dalam Tahap Draft</div>
                <div class="small text-secondary">
                    Jadwal ini belum dipublikasikan secara resmi oleh Waka Kurikulum.
                </div>
            </div>
        </div>
    </div>
@endif

<!-- DAY SELECTOR TABS -->
<div class="d-flex gap-2 overflow-auto pb-2 mb-3" style="scrollbar-width: none; -ms-overflow-style: none;">
    @foreach($daftarHari as $index => $h)
        @php
            $countAgenda = $agendaMingguan->get($h, collect())->count();
            $isToday = ($h === $hariSekarang);
        @endphp
        <button type="button"
            class="btn btn-sm text-nowrap rounded-pill px-3 py-2 tab-day-btn {{ $loop->first ? 'btn-primary' : 'btn-outline-secondary' }}"
            data-target="day-section-{{ $h }}"
            onclick="switchDayTab(this, 'day-section-{{ $h }}')">
            {{ $h }}
            @if($isToday)
                <span class="badge bg-white text-primary ms-1" style="font-size: var(--text-2xs);">Hari Ini</span>
            @elseif($countAgenda > 0)
                <span class="badge bg-secondary-lt ms-1" style="font-size: var(--text-2xs);">{{ $countAgenda }}</span>
            @endif
        </button>
    @endforeach
</div>

<!-- CONTAINER JADWAL PER HARI -->
@foreach($daftarHari as $h)
    @php
        $agendaHari = $agendaMingguan->get($h, collect());
    @endphp
    <div id="day-section-{{ $h }}" class="day-section {{ $loop->first ? '' : 'd-none' }}">
        <div class="d-flex justify-content-between align-items-center mb-2 px-1">
            <span class="fw-bold text-dark fs-5">{{ $h }}</span>
            <span class="text-secondary small">{{ $agendaHari->count() }} agenda</span>
        </div>

        @forelse($agendaHari as $agenda)
            @if($agenda['type'] === 'kegiatan')
                @php $item = $agenda['data']; @endphp
                <div class="card-mobile p-3 mb-2 {{ $item->jenis === 'upacara' ? 'bg-danger-lt border-danger' : ($item->jenis === 'istirahat' ? 'bg-warning-lt border-warning' : 'bg-teal-lt border-teal') }}">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <div class="fw-bold text-dark">{{ $item->nama_kegiatan }}</div>
                            <div class="small text-secondary">
                                @if($item->lokasi)<i class="bi bi-geo-alt me-1"></i>{{ $item->lokasi }}@endif
                            </div>
                        </div>
                        <span class="badge bg-secondary-lt font-monospace text-nowrap">
                            <i class="bi bi-clock me-1"></i>{{ substr($agenda['jam_mulai'], 0, 5) }} - {{ substr($agenda['jam_selesai'], 0, 5) }}
                        </span>
                    </div>
                </div>
            @else
                @php $item = $agenda['data']; @endphp
                <div class="card-mobile p-3 mb-2">
                    <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                        <span class="badge bg-indigo-lt fw-bold px-2 py-1" >
                            <i class="bi bi-door-open me-1"></i>{{ $item->kelas->nama_kelas }}
                        </span>
                        <span class="badge bg-secondary-lt font-monospace text-nowrap" style="font-size: var(--text-xs);">
                            <i class="bi bi-clock me-1"></i>{{ substr($agenda['jam_mulai'], 0, 5) }} - {{ substr($agenda['jam_selesai'], 0, 5) }}
                        </span>
                    </div>
                    <div class="fw-bold fs-5 text-dark">{{ $item->mataPelajaran->nama }}</div>
                </div>
            @endif
        @empty
            <div class="card-mobile p-4 text-center">
                <i class="bi bi-cup-hot text-muted fs-1 mb-2 d-block"></i>
                <div class="fw-semibold text-secondary small">Tidak ada jadwal atau kegiatan pada hari {{ $h }}.</div>
            </div>
        @endforelse
    </div>
@endforeach

<script>
function switchDayTab(btn, targetId) {
    document.querySelectorAll('.tab-day-btn').forEach(b => {
        b.classList.remove('btn-primary');
        b.classList.add('btn-outline-secondary');
    });
    btn.classList.remove('btn-outline-secondary');
    btn.classList.add('btn-primary');

    document.querySelectorAll('.day-section').forEach(sec => sec.classList.add('d-none'));
    const target = document.getElementById(targetId);
    if (target) target.classList.remove('d-none');
}
</script>
@endsection
