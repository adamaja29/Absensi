<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Absensi Guru - {{ $tanggal }}</title>
    <link rel="stylesheet" href="{{ asset('tabler/css/tabler.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: var(--color-paper-3);
            color: var(--color-ink);
            font-size: var(--text-sm);
        }

        .paper-container {
            width: 100%;
            max-width: 1050px;
            margin: 20px auto;
            background: var(--color-paper);
            padding: 35px 45px;
            border: 1px solid var(--color-rule);
            border-radius: var(--radius-card);
        }

        /* KOP SURAT */
        .kop-surat {
            border-bottom: 3px double var(--color-ink);
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .kop-header h2 {
            font-size: var(--text-lg);
            font-weight: bold;
            margin-bottom: 2px;
        }

        .kop-header h4 {
            font-size: var(--text-base);
            font-weight: bold;
            margin-bottom: 4px;
        }

        .kop-header p {
            font-size: var(--text-2xs);
            margin-bottom: 0;
            color: var(--color-ink-2);
        }

        /* PRINT CONTROL BAR */
        .print-control-bar {
            max-width: 1050px;
            margin: 15px auto 0 auto;
            padding: 16px 20px;
            background: #ffffff;
            border: 1px solid var(--color-rule);
            border-radius: var(--radius-card);
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        /* TABLE PRINT STYLES */
        .table-print {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: var(--text-2xs);
        }

        .table-print th, .table-print td {
            border: 1px solid var(--color-rule-2);
            padding: 6px 8px;
            vertical-align: top;
        }

        .table-print th {
            background-color: var(--color-paper-3) !important;
            text-align: center;
            font-weight: bold;
            font-size: var(--text-2xs);
        }

        .signature-box {
            margin-top: 40px;
            page-break-inside: avoid;
        }

        /* CSS KHUSUS SAAT DICETAK ATAU SAVE PDF */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .paper-container {
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
                box-shadow: none !important;
            }
            .print-control-bar {
                display: none !important;
            }
            @page {
                size: A4 portrait;
                margin: 1.2cm;
            }
        }
    </style>
</head>
<body>

<!-- CONTROL BAR CETAK (TIDAK TAMPIL SAAT DI-PRINT) -->
<div class="print-control-bar">
    <div class="d-flex flex-wrap justify-content-end align-items-center gap-2">
        <div class="d-flex gap-2">
            <a href="{{ route('waka.absensi.monitoring', request()->all()) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Monitoring
            </a>
            <button type="button" class="btn btn-primary btn-sm fw-bold px-3" onclick="window.print()">
                <i class="bi bi-printer-fill me-1"></i> Cetak / Simpan PDF
            </button>
        </div>
    </div>
</div>

<div class="paper-container shadow-sm">
    <!-- KOP SURAT SEKOLAH -->
    <div class="kop-surat d-flex align-items-center gap-4">
        <div class="avatar avatar-xl bg-primary text-white rounded-circle shadow-sm" style="width: 70px; height: 70px; font-size: var(--text-2xl);">
            <i class="bi bi-mortarboard-fill"></i>
        </div>
        <div class="kop-header flex-fill text-center">
            <h2>PEMERINTAH PROVINSI / DAERAH</h2>
            <h4>DINAS PENDIDIKAN DAN KEBUDAYAAN</h4>
            <h2 class="text-primary">{{ $settingSekolah->nama_sekolah ?? 'SMK NEGERI 1 CONTOH' }}</h2>
            <p>{{ $settingSekolah->alamat ?? 'Jl. Pendidikan No. 123' }}</p>
            <p>Sistem Presensi & Jurnal Mengajar Digital</p>
        </div>
    </div>

    <!-- JUDUL LAPORAN -->
    <div class="text-center my-3">
        <h3 class="fw-bold mb-1" style="text-decoration: underline;">
            LAPORAN REKAPITULASI PRESENSI & JURNAL MENGAJAR GURU
        </h3>
        <div class="text-secondary small">
            Hari / Tanggal: <strong>{{ $hariPilihan }}, {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}</strong>
        </div>
        @if($tahunAktif)
            <div class="text-secondary small">
                Tahun Ajaran: <strong>{{ $tahunAktif->nama }}</strong> (Semester {{ ucfirst($tahunAktif->semester) }})
            </div>
        @endif
    </div>

    <!-- STATISTIK SINGKAT REKAPITULASI -->
    <div class="row g-2 text-center mb-3">
        <div class="col-3">
            <div class="border rounded p-2 bg-white">
                <div class="text-secondary" style="font-size: var(--text-2xs);">Total Jadwal Dicetak</div>
                <div class="fw-bold fs-4 text-dark">{{ $semuaJadwalHari->count() }}</div>
            </div>
        </div>
        <div class="col-3">
            <div class="border rounded p-2 bg-white">
                <div class="text-secondary" style="font-size: var(--text-2xs);">Hadir (Tepat Waktu)</div>
                <div class="fw-bold fs-4 text-success">{{ $sudahCheckIn - $terlambatCount }}</div>
            </div>
        </div>
        <div class="col-3">
            <div class="border rounded p-2 bg-white">
                <div class="text-secondary" style="font-size: var(--text-2xs);">Terlambat</div>
                <div class="fw-bold fs-4 text-warning">{{ $terlambatCount }}</div>
            </div>
        </div>
        <div class="col-3">
            <div class="border rounded p-2 bg-white">
                <div class="text-secondary" style="font-size: var(--text-2xs);">Belum Check-In / Izin</div>
                <div class="fw-bold fs-4 text-danger">{{ $belumCheckInCount + $izinSakitCount }}</div>
            </div>
        </div>
    </div>

    <!-- TABEL REKAPITULASI DETAIL -->
    <table class="table-print">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 14%;">Jam / Sesi</th>
                <th style="width: 20%;">Guru Pengampu & NIP</th>
                <th style="width: 10%;">Kelas</th>
                <th style="width: 18%;">Mata Pelajaran</th>
                <th style="width: 12%;">Status Kehadiran</th>
                <th style="width: 10%;">Jam Masuk</th>
                <th style="width: 12%;">Jarak GPS</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($semuaJadwalHari as $index => $item)
                @php
                    $absen = $item->absensi->first();
                @endphp
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>
                        <div class="fw-bold">{{ $item->jamMulai->nama_sesi }}</div>
                        <div class="small text-secondary">{{ substr($item->jamMulai->jam_mulai, 0, 5) }} - {{ substr($item->jamSelesai->jam_selesai, 0, 5) }} WIB</div>
                    </td>
                    <td>
                        <div class="fw-bold">{{ $item->guru->user->name ?? '-' }}</div>
                        <div class="small text-secondary">NIP: {{ $item->guru->nip ?? '-' }}</div>
                    </td>
                    <td class="text-center fw-bold">{{ $item->kelas->nama_kelas }}</td>
                    <td>{{ $item->mataPelajaran->nama }}</td>
                    <td class="text-center">
                        @if($absen)
                            @if($absen->status === 'hadir')
                                <strong class="text-success">Hadir (Tepat Waktu)</strong>
                            @elseif($absen->status === 'terlambat')
                                <strong class="text-warning">Terlambat</strong>
                            @elseif($absen->status === 'izin')
                                <strong>Izin</strong>
                            @elseif($absen->status === 'sakit')
                                <strong>Sakit</strong>
                            @endif
                        @else
                            <span class="text-secondary">Belum Absen</span>
                        @endif
                    </td>
                    <td class="text-center">
                        {{ $absen ? substr($absen->waktu_absen, 0, 5) . ' WIB' : '-' }}
                    </td>
                    <td class="text-center">
                        {{ $absen && $absen->jarak_meter !== null ? $absen->jarak_meter . ' m' : '-' }}
                    </td>
                </tr>
                @if($absen && ($absen->materi_diajarkan || $absen->catatan_siswa_tidak_masuk))
                    <tr style="background-color: var(--color-paper-3);">
                        <td></td>
                        <td colspan="7" class="small">
                            @if($absen->materi_diajarkan)
                                <div><strong>Materi:</strong> {{ $absen->materi_diajarkan }}</div>
                            @endif
                            @if($absen->catatan_siswa_tidak_masuk)
                                <div class="text-secondary"><strong>Catatan Siswa:</strong> {{ $absen->catatan_siswa_tidak_masuk }}</div>
                            @endif
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-secondary">
                        <i class="bi bi-info-circle me-1"></i> Tidak ada data jadwal pada tanggal ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- LEMBAR PENGESAHAN / TANDA TANGAN -->
    <div class="row signature-box">
        <div class="col-6 text-center">
            <p class="mb-1">Mengetahui,</p>
            <p class="fw-bold mb-5">Kepala Sekolah</p>
            <br><br>
            <p class="fw-bold mb-0" style="text-decoration: underline;">( ................................................... )</p>
            <p class="small text-secondary mb-0">NIP. .................................................</p>
        </div>
        <div class="col-6 text-center">
            <p class="mb-1">Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }} WIB</p>
            <p class="fw-bold mb-5">Waka Bidang Kurikulum</p>
            <br><br>
            <p class="fw-bold mb-0" style="text-decoration: underline;">{{ $wakaUser->name ?? 'Waka Kurikulum' }}</p>
            <p class="small text-secondary mb-0">NIP. {{ $wakaUser->guru->nip ?? '.................................................' }}</p>
        </div>
    </div>
</div>

<script>
// Otomatis buka dialog print jika terdapat parameter autoprint=1
if (window.location.search.includes('autoprint=1')) {
    window.onload = function() {
        window.print();
    }
}
</script>
</body>
</html>
