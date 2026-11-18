@extends('layouts.admin.main')

@section('title', 'Dashboard Admin')

@section('content')
<div class="container-xl admin-page">
    <div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h2 class="page-title mb-1">Dashboard Administrator</h2>
            <div class="text-secondary">
                {{ $hariIni }}, {{ \Carbon\Carbon::parse($today)->translatedFormat('d F Y') }}
            </div>
        </div>

    </div>

    <div class="row row-cards mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto"><span class="avatar bg-primary-lt text-primary"><i class="bi bi-people"></i></span></div>
                        <div class="col">
                            <div class="text-secondary small">Total Guru</div>
                            <div class="h1 mb-0">{{ $totalGuru }}</div>
                        </div>
                    </div>
                    <a href="{{ route('admin.guru.data') }}" class="stretched-link"></a>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto"><span class="avatar bg-purple-lt text-purple"><i class="bi bi-person-badge"></i></span></div>
                        <div class="col">
                            <div class="text-secondary small">Waka Kurikulum</div>
                            <div class="h1 mb-0">{{ $totalWaka }}</div>
                        </div>
                    </div>
                    <a href="{{ route('admin.waka.data') }}" class="stretched-link"></a>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto"><span class="avatar bg-teal-lt text-teal"><i class="bi bi-door-open"></i></span></div>
                        <div class="col">
                            <div class="text-secondary small">Total Kelas</div>
                            <div class="h1 mb-0">{{ $totalKelas }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto"><span class="avatar bg-azure-lt text-azure"><i class="bi bi-book"></i></span></div>
                        <div class="col">
                            <div class="text-secondary small">Mata Pelajaran</div>
                            <div class="h1 mb-0">{{ $totalMapel }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Kehadiran Guru Hari Ini</h3>
                        <div class="text-secondary small">{{ $totalJadwalHariIni }} sesi jadwal mengajar</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-3">
                            <div class="border rounded p-3">
                                <div class="text-secondary small">Hadir</div>
                                <div class="h2 mb-0 text-success">{{ $sudahHadir }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="border rounded p-3">
                                <div class="text-secondary small">Terlambat</div>
                                <div class="h2 mb-0 text-warning">{{ $terlambat }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="border rounded p-3">
                                <div class="text-secondary small">Izin / Sakit</div>
                                <div class="h2 mb-0 text-purple">{{ $izinSakit }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="border rounded p-3">
                                <div class="text-secondary small">Belum Absen</div>
                                <div class="h2 mb-0 text-secondary">{{ $belumCheckIn }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Guru</th>
                                    <th>Kelas / Mata Pelajaran</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($jadwalHariIni as $jadwal)
                                    @php $absen = $jadwal->absensi->first(); @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $jadwal->guru->user->name ?? '-' }}</div>
                                            <div class="text-secondary small">{{ $jadwal->jamMulai->nama_sesi }}</div>
                                        </td>
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
                                    <tr>
                                        <td colspan="3" class="text-center text-secondary py-4">Belum ada jadwal hari ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Informasi Sekolah</h3>
                </div>
                <div class="card-body">
                    <div class="fw-bold">{{ $settingSekolah->nama_sekolah ?? 'Sekolah belum diberi nama' }}</div>
                    <div class="text-secondary small mt-1">{{ $settingSekolah->alamat ?? 'Alamat belum diatur' }}</div>
                    @if($tahunAktif)
                        <div class="mt-3 small">Tahun Ajaran: <strong>{{ $tahunAktif->nama }}</strong></div>
                        <div class="small text-secondary">Semester {{ ucfirst($tahunAktif->semester) }}</div>
                    @endif
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span class="avatar bg-danger-lt text-danger me-3"><i class="bi bi-geo-alt"></i></span>
                        <div>
                            <div class="text-secondary small">Radius Absensi</div>
                            <div class="h3 mb-0">{{ $settingSekolah->radius_meter ?? 0 }} meter</div>
                        </div>
                    </div>
                    <a href="{{ route('admin.sekolah.setting') }}" class="btn btn-primary w-100">
                        <i class="bi bi-map-fill me-1"></i> Buka / Tampilkan Peta Sekolah
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($guruTerbaru->isNotEmpty())
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Guru Terbaru</h3>
                <a href="{{ route('admin.guru.data') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter mb-0">
                    <thead><tr><th>Nama</th><th>NIP</th><th>Email</th></tr></thead>
                    <tbody>
                        @foreach($guruTerbaru->take(5) as $guru)
                            <tr>
                                <td class="fw-semibold">{{ $guru->user->name ?? '-' }}</td>
                                <td>{{ $guru->nip ?? '-' }}</td>
                                <td>{{ $guru->user->email ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
