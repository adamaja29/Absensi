@extends('layouts.waka.main')
@section('title', 'Monitoring Absensi Guru')
@section('content')
<div class="container-xl admin-page">
    <div class="page-header admin-page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2 class="page-title">Monitoring Absensi Mengajar Guru</h2>
            <div class="text-secondary mt-1">
                Pantau kehadiran guru sesuai jadwal mengajar secara real-time.
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary-lt fs-6 px-3 py-2">
                <i class="bi bi-calendar-event me-1"></i>
                {{ $hariPilihan }}, {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}
            </span>
        </div>
    </div>

    <!-- INFO BOX CATATAN PENTING -->
    <div class="alert alert-info d-flex align-items-center mb-3">
        <i class="bi bi-info-circle-fill fs-3 me-3 text-info"></i>
        <div>
            <strong>Catatan Penting:</strong> Status <em>"Belum Check-In"</em> tidak otomatis berarti guru tidak hadir di sekolah. Sistem mencatat bahwa belum ada pengiriman absensi mandiri dari akun guru untuk jadwal sesi tersebut.
        </div>
    </div>

    <!-- STATISTIK KARTU HARI INI -->
    <div class="row row-cards mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-primary text-white avatar">
                                <i class="bi bi-calendar3 fs-2"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="text-secondary small">Total Jadwal</div>
                            <div class="h2 mb-0">{{ $totalJadwal }} Sesi</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-success text-white avatar">
                                <i class="bi bi-check-circle fs-2"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="text-secondary small">Sudah Check-In</div>
                            <div class="h2 mb-0">{{ $sudahCheckIn }} Guru</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-warning text-white avatar">
                                <i class="bi bi-clock-history fs-2"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="text-secondary small">Terlambat</div>
                            <div class="h2 mb-0">{{ $terlambatCount }} Sesi</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-secondary text-white avatar">
                                <i class="bi bi-hourglass-split fs-2"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="text-secondary small">Belum Check-In</div>
                            <div class="h2 mb-0">{{ $belumCheckInCount }} Sesi</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FILTER FORM -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('waka.absensi.monitoring') }}" method="GET" class="row g-2">
                <div class="col-md-2">
                    <label class="form-label small">Tanggal Monitoring</label>
                    <input type="date" name="tanggal" class="form-control" value="{{ $tanggal }}" onchange="this.form.submit()">
                </div>

                <div class="col-md-2">
                    <label class="form-label small">Filter Guru</label>
                    <select name="guru_id" class="form-select">
                        <option value="">Semua Guru</option>
                        @foreach($daftarGuru as $g)
                            <option value="{{ $g->id }}" {{ request('guru_id') == $g->id ? 'selected' : '' }}>{{ $g->user->name ?? 'Guru #'.$g->id }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small">Filter Kelas</label>
                    <select name="kelas_id" class="form-select">
                        <option value="">Semua Kelas</option>
                        @foreach($daftarKelas as $k)
                            <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small">Filter Mata Pelajaran</label>
                    <select name="mata_pelajaran_id" class="form-select">
                        <option value="">Semua Mata Pelajaran</option>
                        @foreach($daftarMapel as $mapel)
                            <option value="{{ $mapel->id }}" {{ request('mata_pelajaran_id') == $mapel->id ? 'selected' : '' }}>{{ $mapel->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small d-block">Filter Status <span class="text-secondary">(bisa pilih beberapa)</span></label>
                    <div class="d-flex flex-wrap gap-x-3 gap-y-1">
                        @foreach(['hadir' => 'Hadir', 'terlambat' => 'Terlambat', 'izin_sakit' => 'Izin / Sakit', 'belum' => 'Belum Check-In'] as $statusValue => $statusLabel)
                            <label class="form-check form-check-inline mb-1">
                                <input class="form-check-input" type="checkbox" name="status[]" value="{{ $statusValue }}" {{ in_array($statusValue, $statusFilter, true) ? 'checked' : '' }}>
                                <span class="form-check-label small">{{ $statusLabel }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="col-md-1 d-flex flex-column justify-content-end gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                    <a href="{{ route('waka.absensi.monitoring') }}" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- TABEL MONITORING JADWAL & ABSENSI -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Daftar Jadwal & Status Kehadiran</h3>
            <a href="{{ route('waka.absensi.cetak', request()->all()) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-printer me-1"></i> Cetak Laporan (PDF)
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Jam Pelajaran (Sesi)</th>
                        <th>Guru Pengampu</th>
                        <th>Kelas</th>
                        <th>Mata Pelajaran</th>
                        <th>Status Check-In</th>
                        <th>Waktu Check-In</th>
                        <th>Aksi / Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($semuaJadwalHari as $index => $item)
                        @php
                            $absen = $item->absensi->first();
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="fw-bold">
                                    {{ $item->jamMulai->nama_sesi }}
                                    @if($item->jam_pelajaran_mulai_id != $item->jam_pelajaran_selesai_id)
                                        - {{ $item->jamSelesai->nama_sesi }}
                                    @endif
                                </div>
                                <div class="small text-secondary">
                                    {{ substr($item->jamMulai->jam_mulai, 0, 5) }} - {{ substr($item->jamSelesai->jam_selesai, 0, 5) }} WIB
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold">{{ $item->guru->user->name ?? '-' }}</div>
                                <div class="small text-secondary">NIP: {{ $item->guru->nip ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-indigo-lt fw-bold">{{ $item->kelas->nama_kelas }}</span>
                            </td>
                            <td class="fw-bold text-primary">{{ $item->mataPelajaran->nama }}</td>
                            <td>
                                @if($absen)
                                    @if($absen->status === 'hadir')
                                        <span class="badge bg-success-lt fs-6">
                                            <i class="bi bi-check2-circle me-1"></i>Hadir Tepat Waktu
                                        </span>
                                    @elseif($absen->status === 'terlambat')
                                        <span class="badge bg-warning-lt fs-6">
                                            <i class="bi bi-clock-history me-1"></i>Terlambat
                                        </span>
                                    @elseif($absen->status === 'izin')
                                        <span class="badge bg-info-lt fs-6">
                                            <i class="bi bi-envelope me-1"></i>Izin
                                        </span>
                                    @elseif($absen->status === 'sakit')
                                        <span class="badge bg-purple-lt fs-6">
                                            <i class="bi bi-hospital me-1"></i>Sakit
                                        </span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary-lt fs-6">
                                        <i class="bi bi-dash-circle me-1"></i>Belum Check-In
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($absen && in_array($absen->status, ['hadir', 'terlambat']))
                                    <span class="fw-bold text-dark">{{ substr($absen->waktu_absen, 0, 5) }} WIB</span>
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
                            </td>
                            <td>
                                @if($absen)
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalDetail{{ $absen->id }}">
                                        <i class="bi bi-eye me-1"></i>Detail
                                    </button>

                                    <!-- MODAL DETAIL ABSENSI -->
                                    <div class="modal fade" id="modalDetail{{ $absen->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Detail Presensi & Jurnal Mengajar</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3 border-bottom pb-2">
                                                        <div class="text-secondary small">Guru & Kelas</div>
                                                        <div class="fw-bold fs-5">{{ $item->guru->user->name ?? '-' }}</div>
                                                        <div class="text-muted">{{ $item->mataPelajaran->nama }} | {{ $item->kelas->nama_kelas }}</div>
                                                    </div>

                                                    <div class="row g-2 mb-3">
                                                        <div class="col-6">
                                                            <div class="text-secondary small">Waktu Check-In</div>
                                                            <div class="fw-bold">{{ $absen->waktu_absen }} WIB</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="text-secondary small">Jarak dari Sekolah</div>
                                                            <div class="fw-bold">
                                                                @if($absen->jarak_meter !== null)
                                                                    <span class="badge bg-teal-lt">{{ $absen->jarak_meter }} Meter</span>
                                                                @else
                                                                    -
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="text-secondary small">Koordinat GPS Check-In</div>
                                                            <div class="d-flex justify-content-between align-items-center mt-1">
                                                                <span class="small font-monospace">{{ $absen->latitude ?? '-' }}, {{ $absen->longitude ?? '-' }}</span>
                                                                @if($absen->latitude && $absen->longitude)
                                                                    <a href="https://www.google.com/maps?q={{ $absen->latitude }},{{ $absen->longitude }}" target="_blank" class="btn btn-xs btn-outline-primary" style="font-size: 0.75rem; padding: 2px 8px;">
                                                                        <i class="bi bi-geo-alt-fill me-1"></i> Buka Peta Lokasi
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label small text-secondary">Materi yang Diajarkan</label>
                                                        <div class="p-2 bg-body-tertiary rounded border">
                                                            {{ $absen->materi_diajarkan ?? 'Tidak ada catatan materi.' }}
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label small text-secondary">Catatan Murid Tidak Masuk</label>
                                                        <div class="p-2 bg-body-tertiary rounded border">
                                                            {{ $absen->catatan_siswa_tidak_masuk ?? 'Semua murid hadir / tidak ada catatan.' }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted small">Tidak ada data</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-secondary py-5">
                                <i class="bi bi-calendar-x fs-1 d-block mb-2 text-muted"></i>
                                Tidak ada jadwal mengajar pada hari {{ $hariPilihan }} ({{ $tanggal }}).
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
