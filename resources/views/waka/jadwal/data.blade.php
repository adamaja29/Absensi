@extends('layouts.waka.main')
@section('title', 'Jadwal & Agenda Sekolah')
@section('content')
<div class="container-xl admin-page">
    <!-- HEADER & STATUS PUBLIKASI -->
    <div class="card admin-content-card mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h2 class="card-title m-0 fs-2 fw-bold">Jadwal & Agenda Sekolah</h2>
                        @if($selectedTahun)
                            <span class="badge bg-primary-lt fs-5 px-3 py-1">
                                <i class="bi bi-calendar-check me-1"></i>{{ $selectedTahun->nama }} - Semester {{ ucfirst($selectedTahun->semester) }}
                            </span>
                        @endif

                        @if($selectedTahun?->is_published)
                            <span class="badge bg-success text-white fs-5 px-3 py-1">
                                <i class="bi bi-broadcast me-1"></i> Dipublikasikan ke Guru
                            </span>
                        @else
                            <span class="badge bg-warning text-dark fs-5 px-3 py-1">
                                <i class="bi bi-file-earmark-lock me-1"></i> Mode Draft (Belum Dipublikasikan)
                            </span>
                        @endif
                    </div>
                    <div class="text-secondary mt-1 small">
                        Kelola kalender timeline harian terpadu, kegiatan sekolah (upacara & istirahat), serta jadwal KBM mengajar guru.
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <!-- Toggle Publikasi Form -->
                    <form action="{{ route('waka.jadwal.publish') }}" method="POST" class="d-inline">
                        @csrf
                        @if($selectedTahun)
                            <input type="hidden" name="tahun_ajaran_id" value="{{ $selectedTahun->id }}">
                        @endif
                        @if($selectedTahun?->is_published)
                            <button type="submit" class="btn btn-outline-warning" onclick="return confirm('Kembalikan status jadwal ke DRAFT? Guru tidak akan melihat jadwal hingga dipublikasikan kembali.')">
                                <i class="bi bi-eye-slash me-1"></i>Kembalikan ke Draft
                            </button>
                        @else
                            <button type="submit" class="btn btn-success" onclick="return confirm('Publikasikan jadwal ini ke seluruh guru? Guru akan dapat melihat jadwal mengajar mereka di portal guru.')">
                                <i class="bi bi-broadcast me-1"></i>Publikasikan Jadwal ke Guru
                            </button>
                        @endif
                    </form>

                    <!-- Tombol Tambah Kegiatan & KBM -->
                    <a href="{{ route('waka.kegiatan.create', ['hari' => $selectedHari]) }}" class="btn btn-teal">
                        <i class="bi bi-calendar-plus me-1"></i> Kegiatan Sekolah (Upacara/Istirahat)
                    </a>
                    <a href="{{ route('waka.jadwal.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Jadwal Mengajar (KBM)
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mt-3 mb-0" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mt-3 mb-0" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>
    </div>

    <!-- TABS NAVIGASI UTAMA -->
    <div class="card admin-content-card">
        <div class="card-header border-bottom">
            <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs">
                <li class="nav-item">
                    <a href="#tab-timeline" class="nav-link {{ $activeTab === 'timeline' ? 'active' : '' }}" data-bs-toggle="tab">
                        <i class="bi bi-clock-history me-1 text-primary"></i>
                        <strong>Timeline Harian Terpadu</strong>
                        <span class="badge bg-primary-lt ms-2">{{ $selectedHari }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#tab-matriks" class="nav-link {{ $activeTab === 'matriks' ? 'active' : '' }}" data-bs-toggle="tab">
                        <i class="bi bi-grid-3x3-gap me-1 text-teal"></i>
                        Matriks Kelas vs Jam
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#tab-kegiatan" class="nav-link {{ $activeTab === 'kegiatan' ? 'active' : '' }}" data-bs-toggle="tab">
                        <i class="bi bi-calendar-event me-1 text-warning"></i>
                        Agenda Kegiatan Sekolah (Upacara/Istirahat)
                        <span class="badge bg-warning-lt ms-1">{{ $semuaKegiatan->count() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#tab-tabel" class="nav-link {{ $activeTab === 'tabel' ? 'active' : '' }}" data-bs-toggle="tab">
                        <i class="bi bi-table me-1 text-azure"></i>
                        Tabel Jadwal Mengajar (KBM)
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content">
                <!-- ======================================================== -->
                <!-- TAB 1: TIMELINE HARIAN TERPADU                           -->
                <!-- ======================================================== -->
                <div class="tab-pane {{ $activeTab === 'timeline' ? 'active show' : '' }}" id="tab-timeline">
                    <!-- Day Switcher Pills -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 p-2 bg-body-tertiary rounded">
                        <div class="d-flex flex-wrap gap-1">
                            <span class="align-self-center px-2 text-secondary fw-semibold small">Pilih Hari:</span>
                            @foreach($daftarHari as $h)
                                <a href="{{ route('waka.jadwal.data', ['tab' => 'timeline', 'hari' => $h]) }}"
                                   class="btn btn-sm {{ $selectedHari === $h ? 'btn-primary' : 'btn-outline-secondary' }}">
                                    {{ $h }}
                                </a>
                            @endforeach
                        </div>
                        <div class="text-secondary small d-flex align-items-center gap-3">
                            <span><span class="badge bg-primary me-1">&nbsp;</span> KBM Mengajar (Tagihan Absen)</span>
                            <span><span class="badge bg-warning me-1">&nbsp;</span> Kegiatan Sekolah (Tanpa Absen)</span>
                        </div>
                    </div>

                    <!-- Timeline Content -->
                    <div class="timeline-container position-relative ps-4" style="border-left: 2px solid var(--tblr-border-color); margin-left: 20px;">
                        @forelse($timelineHariIni as $item)
                            @if($item['type'] === 'kegiatan')
                                @php $keg = $item['data']; @endphp
                                <!-- BLOK KEGIATAN SEKOLAH (Upacara / Istirahat / Senam / Ibadah) -->
                                <div class="timeline-block mb-4 position-relative">
                                    <div class="position-absolute" style="left: -33px; top: 0;">
                                        @if($keg->jenis === 'upacara')
                                            <span class="avatar avatar-sm rounded-circle bg-danger text-white shadow-sm">🇮🇩</span>
                                        @elseif($keg->jenis === 'istirahat')
                                            <span class="avatar avatar-sm rounded-circle bg-warning text-white shadow-sm">☕</span>
                                        @elseif($keg->jenis === 'ibadah')
                                            <span class="avatar avatar-sm rounded-circle bg-teal text-white shadow-sm">🕌</span>
                                        @elseif($keg->jenis === 'senam')
                                            <span class="avatar avatar-sm rounded-circle bg-azure text-white shadow-sm">🏃</span>
                                        @elseif($keg->jenis === 'literasi')
                                            <span class="avatar avatar-sm rounded-circle bg-purple text-white shadow-sm">📖</span>
                                        @else
                                            <span class="avatar avatar-sm rounded-circle bg-secondary text-white shadow-sm">📌</span>
                                        @endif
                                    </div>

                                    <div class="card border-2 shadow-sm {{ $keg->jenis === 'upacara' ? 'border-danger-subtle bg-danger-lt' : ($keg->jenis === 'istirahat' ? 'border-warning-subtle bg-warning-lt' : 'border-teal-subtle bg-teal-lt') }}">
                                        <div class="card-body p-3">
                                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-1">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-dark text-white font-monospace">
                                                        <i class="bi bi-clock me-1"></i>{{ substr($keg->jam_mulai, 0, 5) }} - {{ substr($keg->jam_selesai, 0, 5) }} WIB
                                                    </span>
                                                    <h4 class="card-title m-0 fw-bold">{{ $keg->nama_kegiatan }}</h4>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-secondary-lt fw-normal text-secondary">
                                                        <i class="bi bi-shield-x me-1"></i>Kegiatan Sekolah &bull; Bukan KBM (Tanpa Absen)
                                                    </span>
                                                    <div class="btn-list">
                                                        <a href="{{ route('waka.kegiatan.edit', $keg) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Edit Kegiatan">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <form action="{{ route('waka.kegiatan.destroy', $keg) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus kegiatan sekolah ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-wrap gap-3 small text-secondary mt-2">
                                                @if($keg->lokasi)
                                                    <span><i class="bi bi-geo-alt me-1 text-danger"></i><strong>Lokasi:</strong> {{ $keg->lokasi }}</span>
                                                @endif
                                                @if($keg->peserta)
                                                    <span><i class="bi bi-people me-1 text-primary"></i><strong>Peserta:</strong> {{ $keg->peserta }}</span>
                                                @endif
                                                @if($keg->keterangan)
                                                    <span><i class="bi bi-info-circle me-1 text-muted"></i>{{ $keg->keterangan }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @elseif($item['type'] === 'jadwal')
                                @php $j = $item['data']; @endphp
                                <div class="timeline-block mb-4 position-relative">
                                    <div class="position-absolute" style="left: -33px; top: 0;">
                                        <span class="avatar avatar-sm rounded-circle bg-primary text-white shadow-sm">
                                            <i class="bi bi-book"></i>
                                        </span>
                                    </div>

                                    <div class="card shadow-sm border">
                                        <div class="card-header py-2 px-3 bg-body-tertiary d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-primary text-white font-monospace">
                                                    <i class="bi bi-clock me-1"></i>{{ substr($item['jam_mulai'], 0, 5) }} - {{ substr($item['jam_selesai'], 0, 5) }} WIB
                                                </span>
                                                <strong class="fs-4">{{ $j->mataPelajaran->nama }}</strong>
                                            </div>
                                            <span class="badge bg-indigo-lt fw-bold">
                                                {{ $j->kelas->nama_kelas }}
                                            </span>
                                        </div>

                                        <div class="card-body p-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                                            <div class="small text-secondary">
                                                <i class="bi bi-person me-1"></i>{{ $j->guru->user->name ?? 'Guru' }}
                                            </div>
                                            <div class="btn-list flex-nowrap">
                                                <a href="{{ route('waka.jadwal.edit', $j) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil me-1"></i>Edit
                                                </a>
                                                <form action="{{ route('waka.jadwal.destroy', $j) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus jadwal ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <div class="text-center py-5">
                                <i class="bi bi-calendar-x fs-1 text-muted d-block mb-2"></i>
                                <h4 class="text-secondary">Belum ada agenda kegiatan maupun jadwal pelajaran pada hari {{ $selectedHari }}.</h4>
                                <div class="mt-3 d-flex justify-content-center gap-2">
                                    <a href="{{ route('waka.kegiatan.create', ['hari' => $selectedHari]) }}" class="btn btn-sm btn-teal">
                                        <i class="bi bi-calendar-plus me-1"></i>+ Tambah Kegiatan (Upacara/Istirahat)
                                    </a>
                                    <a href="{{ route('waka.jadwal.create') }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-plus-lg me-1"></i>+ Susun Jadwal KBM
                                    </a>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- ======================================================== -->
                <!-- TAB 2: MATRIKS KELAS VS JAM                              -->
                <!-- ======================================================== -->
                <div class="tab-pane {{ $activeTab === 'matriks' ? 'active show' : '' }}" id="tab-matriks">
                    <!-- Day Switcher for Matrix -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 p-2 bg-body-tertiary rounded">
                        <div class="d-flex flex-wrap gap-1">
                            <span class="align-self-center px-2 text-secondary fw-semibold small">Pilih Hari:</span>
                            @foreach($daftarHari as $h)
                                <a href="{{ route('waka.jadwal.data', ['tab' => 'matriks', 'hari' => $h]) }}"
                                   class="btn btn-sm {{ $selectedHari === $h ? 'btn-teal text-white' : 'btn-outline-secondary' }}">
                                    {{ $h }}
                                </a>
                            @endforeach
                        </div>
                        <div class="small text-secondary">
                            Melihat persebaran mata pelajaran seluruh kelas pada hari <strong>{{ $selectedHari }}</strong>.
                        </div>
                    </div>

                    <!-- Highlight Kegiatan Sekolah Hari Ini -->
                    @if($kegiatanHariIni->count() > 0)
                        <div class="mb-3 d-flex flex-wrap gap-2">
                            <span class="small text-secondary align-self-center">Kegiatan Terjadwal Hari Ini:</span>
                            @foreach($kegiatanHariIni as $keg)
                                <span class="badge {{ $keg->jenis_badge_class }} py-1 px-2">
                                    {{ $keg->nama_kegiatan }} ({{ substr($keg->jam_mulai, 0, 5) }} - {{ substr($keg->jam_selesai, 0, 5) }})
                                </span>
                            @endforeach
                        </div>
                    @endif

                    <div class="table-responsive border rounded">
                        <table class="table table-bordered table-vcenter text-center table-sm m-0">
                            <thead class="bg-body-tertiary">
                                <tr>
                                    <th style="min-width: 120px;" class="text-start">Kelas</th>
                                    @foreach($daftarJamPelajaran as $jam)
                                        <th style="min-width: 140px;">
                                            <span class="small text-secondary font-monospace" style="font-size: 0.75rem;">
                                                {{ substr($jam->jam_mulai, 0, 5) }}-{{ substr($jam->jam_selesai, 0, 5) }}
                                            </span>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($daftarKelas as $kelas)
                                    <tr>
                                        <td class="text-start fw-bold bg-body-tertiary">
                                            <span class="badge bg-indigo-lt fs-6">{{ $kelas->nama_kelas }}</span>
                                        </td>
                                        @foreach($daftarJamPelajaran as $jam)
                                            @php
                                                $match = $jadwalHariIni->first(function ($j) use ($kelas, $jam) {
                                                    return $j->kelas_id === $kelas->id &&
                                                           $jam->jam_mulai >= $j->jamMulai->jam_mulai &&
                                                           $jam->jam_mulai < $j->jamSelesai->jam_selesai;
                                                });
                                            @endphp
                                            <td class="p-2 {{ $match ? 'bg-primary-lt' : '' }}">
                                                @if($match)
                                                    <div class="fw-bold text-primary small">{{ $match->mataPelajaran->nama }}</div>
                                                    <div class="text-secondary small" style="font-size: 0.75rem;">
                                                        <i class="bi bi-person me-1"></i>{{ Str::limit($match->guru->user->name ?? '-', 15) }}
                                                    </div>
                                                @else
                                                    <span class="text-muted" style="font-size: 0.75rem;">-</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $daftarJamPelajaran->count() + 1 }}" class="py-4 text-center text-muted">
                                            Belum ada data kelas yang terdaftar.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ======================================================== -->
                <!-- TAB 3: AGENDA KEGIATAN SEKOLAH                           -->
                <!-- ======================================================== -->
                <div class="tab-pane {{ $activeTab === 'kegiatan' ? 'active show' : '' }}" id="tab-kegiatan">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div>
                            <h4 class="m-0 fw-bold">Daftar Kegiatan Umum Sekolah</h4>
                            <div class="small text-secondary">
                                Kegiatan seperti Upacara, Istirahat, Apel Pagi, dan Senam berlaku untuk seluruh warga sekolah dan tidak menimbulkan tagihan absensi KBM.
                            </div>
                        </div>
                        <a href="{{ route('waka.kegiatan.create') }}" class="btn btn-teal btn-sm">
                            <i class="bi bi-plus-lg me-1"></i>Tambah Kegiatan Sekolah
                        </a>
                    </div>

                    <div class="table-responsive border rounded">
                        <table class="table table-vcenter card-table table-hover">
                            <thead>
                                <tr>
                                    <th>Hari</th>
                                    <th>Waktu (WIB)</th>
                                    <th>Jenis Kegiatan</th>
                                    <th>Nama Kegiatan</th>
                                    <th>Lokasi</th>
                                    <th>Peserta</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($semuaKegiatan as $keg)
                                    <tr>
                                        <td>
                                            <span class="badge bg-azure-lt fw-bold fs-6">{{ $keg->hari }}</span>
                                        </td>
                                        <td>
                                            <span class="font-monospace fw-bold">
                                                {{ substr($keg->jam_mulai, 0, 5) }} - {{ substr($keg->jam_selesai, 0, 5) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $keg->jenis_badge_class }}">
                                                {{ ucfirst($keg->jenis) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold">{{ $keg->nama_kegiatan }}</div>
                                            @if($keg->keterangan)
                                                <div class="small text-secondary">{{ $keg->keterangan }}</div>
                                            @endif
                                        </td>
                                        <td>{{ $keg->lokasi ?? '-' }}</td>
                                        <td>{{ $keg->peserta ?? 'Semua' }}</td>
                                        <td>
                                            <div class="btn-list flex-nowrap">
                                                <a href="{{ route('waka.kegiatan.edit', $keg) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil me-1"></i>Edit
                                                </a>
                                                <form action="{{ route('waka.kegiatan.destroy', $keg) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus kegiatan {{ $keg->nama_kegiatan }}?')">
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
                                        <td colspan="7" class="text-center py-5 text-secondary">
                                            <i class="bi bi-calendar-x fs-1 d-block mb-2 text-muted"></i>
                                            Belum ada kegiatan umum sekolah yang terdaftar.
                                            <div class="mt-2">
                                                <a href="{{ route('waka.kegiatan.create') }}" class="btn btn-sm btn-teal">
                                                    <i class="bi bi-plus-lg me-1"></i>Tambah Kegiatan Sekarang
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ======================================================== -->
                <!-- TAB 4: TABEL JADWAL MENGAJAR (KBM)                       -->
                <!-- ======================================================== -->
                <div class="tab-pane {{ $activeTab === 'tabel' ? 'active show' : '' }}" id="tab-tabel">
                    <!-- FILTER FORM -->
                    <form action="{{ route('waka.jadwal.data') }}" method="GET" class="row g-2 mb-3 p-3 bg-body-tertiary rounded">
                        <input type="hidden" name="tab" value="tabel">
                        <div class="col-md-2">
                            <select name="filter_tabel_hari" class="form-select">
                                <option value="">Semua Hari</option>
                                @foreach($daftarHari as $h)
                                    <option value="{{ $h }}" {{ request('filter_tabel_hari') == $h ? 'selected' : '' }}>{{ $h }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <select name="guru_id" class="form-select">
                                <option value="">Semua Guru</option>
                                @foreach($daftarGuru as $g)
                                    <option value="{{ $g->id }}" {{ request('guru_id') == $g->id ? 'selected' : '' }}>{{ $g->user->name ?? 'Guru #'.$g->id }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <select name="kelas_id" class="form-select">
                                <option value="">Semua Kelas</option>
                                @foreach($daftarKelas as $k)
                                    <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <select name="mata_pelajaran_id" class="form-select">
                                <option value="">Semua Mapel</option>
                                @foreach($daftarMapel as $m)
                                    <option value="{{ $m->id }}" {{ request('mata_pelajaran_id') == $m->id ? 'selected' : '' }}>{{ $m->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                            <a href="{{ route('waka.jadwal.data', ['tab' => 'tabel']) }}" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>

                    <div class="table-responsive border rounded">
                        <table class="table table-vcenter card-table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Hari</th>
                                    <th>Waktu Mengajar</th>
                                    <th>Guru Pengampu</th>
                                    <th>Kelas</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($jadwal as $index => $item)
                                    <tr>
                                        <td>{{ $jadwal->firstItem() + $index }}</td>
                                        <td>
                                            <span class="badge bg-azure-lt fw-bold fs-6">{{ $item->hari }}</span>
                                        </td>
                                        <td>
                                            <span class="font-monospace fw-bold text-nowrap">
                                                {{ substr($item->jamMulai->jam_mulai, 0, 5) }} - {{ substr($item->jamSelesai->jam_selesai, 0, 5) }} WIB
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="avatar avatar-xs bg-primary-lt me-2">
                                                    <i class="bi bi-person"></i>
                                                </span>
                                                <div>
                                                    <div class="fw-bold">{{ $item->guru->user->name ?? '-' }}</div>
                                                    <div class="small text-secondary">NIP: {{ $item->guru->nip ?? '-' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-indigo-lt fw-bold">{{ $item->kelas->nama_kelas }}</span>
                                        </td>
                                        <td class="fw-bold text-primary">{{ $item->mataPelajaran->nama }}</td>
                                        <td>
                                            <div class="btn-list flex-nowrap">
                                                <a href="{{ route('waka.jadwal.edit', $item) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil-square me-1"></i>Edit
                                                </a>
                                                <form action="{{ route('waka.jadwal.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus jadwal ini?')">
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
                                        <td colspan="7" class="text-center text-secondary py-5">
                                            <i class="bi bi-calendar-x fs-1 d-block mb-2 text-muted"></i>
                                            Belum ada data jadwal mengajar KBM yang sesuai filter.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $jadwal->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
