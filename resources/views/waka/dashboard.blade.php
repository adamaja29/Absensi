@extends('layouts.waka.main')

@section('title', 'Dashboard Waka Kurikulum')

@section('content')
<div class="container-xl admin-page">
    <div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h2 class="page-title mb-1">Dashboard Waka Kurikulum</h2>
            <div class="text-secondary">
                {{ $hariIni }}, {{ \Carbon\Carbon::parse($today)->translatedFormat('d F Y') }}
                @if($tahunAktif)
                    &bull; TA {{ $tahunAktif->nama }} &bull; Semester {{ ucfirst($tahunAktif->semester) }}
                @endif
            </div>
        </div>
        <a href="{{ route('waka.absensi.monitoring') }}" class="btn btn-primary">
            <i class="bi bi-clipboard-check me-1"></i> Monitoring Absensi
        </a>
    </div>

    <div class="row row-cards mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto"><span class="avatar bg-primary-lt text-primary"><i class="bi bi-calendar-check"></i></span></div>
                        <div class="col"><div class="text-secondary small">Jadwal Hari Ini</div><div class="h1 mb-0">{{ $totalJadwalHariIni }}</div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto"><span class="avatar bg-success-lt text-success"><i class="bi bi-person-check"></i></span></div>
                        <div class="col"><div class="text-secondary small">Sudah Absen</div><div class="h1 mb-0">{{ $sudahHadir }}</div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto"><span class="avatar bg-warning-lt text-warning"><i class="bi bi-clock-history"></i></span></div>
                        <div class="col"><div class="text-secondary small">Terlambat</div><div class="h1 mb-0">{{ $terlambat }}</div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto"><span class="avatar bg-danger-lt text-danger"><i class="bi bi-hourglass-split"></i></span></div>
                        <div class="col"><div class="text-secondary small">Belum Absen</div><div class="h1 mb-0">{{ $belumCheckIn }}</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="card-title">Jadwal Mengajar Hari Ini</h3>
                        <div class="text-secondary small">Status presensi 6 sesi terdekat</div>
                    </div>
                    <a href="{{ route('waka.absensi.monitoring') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter">
                        <thead>
                            <tr><th>Jam</th><th>Guru</th><th>Kelas / Mapel</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @forelse($jadwalHariIni as $jadwal)
                                @php $absen = $jadwal->absensi->first(); @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ substr($jadwal->jamMulai->jam_mulai, 0, 5) }}</div>
                                        <div class="text-secondary small">{{ $jadwal->jamMulai->nama_sesi }}</div>
                                    </td>
                                    <td>{{ $jadwal->guru->user->name ?? '-' }}</td>
                                    <td>
                                        <div>{{ $jadwal->kelas->nama_kelas }}</div>
                                        <div class="text-secondary small">{{ $jadwal->mataPelajaran->nama }}</div>
                                    </td>
                                    <td>
                                        @if($absen?->status === 'hadir')
                                            <span class="badge bg-success-lt">Hadir</span>
                                        @elseif($absen?->status === 'terlambat')
                                            <span class="badge bg-warning-lt">Terlambat</span>
                                        @elseif($absen && in_array($absen->status, ['izin', 'sakit']))
                                            <span class="badge bg-purple-lt">{{ ucfirst($absen->status) }}</span>
                                        @else
                                            <span class="badge bg-secondary-lt">Belum Absen</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-secondary py-4">Belum ada jadwal hari ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <span class="avatar bg-purple-lt text-purple me-3"><i class="bi bi-envelope-paper"></i></span>
                        <div>
                            <div class="text-secondary small">Pengajuan Izin Menunggu</div>
                            <div class="h1 mb-0">{{ $izinMenunggu }}</div>
                        </div>
                    </div>
                    <a href="{{ route('waka.izin.data') }}" class="btn btn-primary w-100 mt-3">Periksa Pengajuan</a>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h3 class="card-title">Kelola Kurikulum</h3></div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('waka.jadwal.data') }}" class="list-group-item list-group-item-action d-flex justify-content-between">
                        Jadwal Pelajaran <i class="bi bi-chevron-right"></i>
                    </a>
                    <a href="{{ route('waka.kelas.data') }}" class="list-group-item list-group-item-action d-flex justify-content-between">
                        Data Kelas ({{ $totalKelas }}) <i class="bi bi-chevron-right"></i>
                    </a>
                    <a href="{{ route('waka.mapel.data') }}" class="list-group-item list-group-item-action d-flex justify-content-between">
                        Mata Pelajaran ({{ $totalMapel }}) <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
