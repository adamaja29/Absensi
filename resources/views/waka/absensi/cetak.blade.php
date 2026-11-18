<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Absensi Guru - {{ $tanggal }}</title>
    <link rel="stylesheet" href="{{ asset('tabler/css/tabler.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f1f5f9;
            color: #0f172a;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }

        .paper-container {
            width: 100%;
            max-width: 1000px;
            margin: 20px auto;
            background: #ffffff;
            padding: 35px 45px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-radius: 8px;
        }

        /* KOP SURAT */
        .kop-surat {
            border-bottom: 3px double #000;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .kop-logo {
            width: 75px;
            height: 75px;
            object-fit: contain;
        }

        .kop-header h2 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 2px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kop-header h4 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .kop-header p {
            font-size: 11px;
            margin-bottom: 0;
            color: #334155;
        }

        /* PRINT CONTROL BAR */
        .print-control-bar {
            max-width: 1000px;
            margin: 15px auto 0 auto;
            padding: 10px 15px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* TABLE PRINT STYLES */
        .table-print {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 11px;
        }

        .table-print th, .table-print td {
            border: 1px solid #334155;
            padding: 6px 8px;
            vertical-align: top;
        }

        .table-print th {
            background-color: #f1f5f9 !important;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
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
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            .print-control-bar {
                display: none !important;
            }
            @page {
                size: A4 portrait;
                margin: 1.5cm;
            }
        }
    </style>
</head>
<body>

<!-- CONTROL BAR (TIDAK TAMPIL SAAT DI-PRINT) -->
<div class="print-control-bar">
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-primary-lt fs-6">Mode Cetak Laporan</span>
        <span class="text-secondary small">Pastikan pengaturan printer menggunakan kertas A4.</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('waka.absensi.monitoring') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Monitoring
        </a>
        <button type="button" class="btn btn-primary btn-sm fw-bold" onclick="window.print()">
            <i class="bi bi-printer-fill me-1"></i> Cetak / Simpan PDF
        </button>
    </div>
</div>

<div class="paper-container">
    <!-- KOP SURAT SEKOLAH -->
    <div class="kop-surat d-flex align-items-center gap-4">
        <div class="avatar avatar-xl bg-primary text-white rounded-circle shadow-sm" style="width: 70px; height: 70px; font-size: 2rem;">
            <i class="bi bi-mortarboard-fill"></i>
        </div>
        <div class="kop-header flex-fill text-center">
            <h2>PEMERINTAH PROVINSI / DAERAH</h2>
            <h4>DINAS PENDIDIKAN DAN KEBUDAYAAN</h4>
            <h2 class="text-primary">{{ $settingSekolah->nama_sekolah ?? 'SEKOLAH MENENGAH KEJURUAN' }}</h2>
            <p>{{ $settingSekolah->alamat ?? 'Jl. Pendidikan No. 1' }} &bull; Sistem Presensi & Jurnal Mengajar Digital</p>
        </div>
    </div>

    <!-- JUDUL LAPORAN -->
    <div class="text-center my-3">
        <h3 class="fw-bold mb-1" style="text-decoration: underline; text-transform: uppercase;">
            LAPORAN REKAPITULASI PRESENSI & JURNAL MENGAJAR GURU
        </h3>
        <div class="text-secondary small">
            Tanggal: <strong>{{ $hariPilihan }}, {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}</strong>
            @if($tahunAktif)
                &bull; Tahun Ajaran: <strong>{{ $tahunAktif->nama }} (Semester {{ ucfirst($tahunAktif->semester) }})</strong>
            @endif
        </div>
    </div>

    <!-- METADATA FILTER -->
    <div class="row g-2 mb-3 small border rounded p-2 bg-light">
        <div class="col-4">
            <span class="text-secondary">Guru:</span> <strong>{{ $filterGuruNama }}</strong>
        </div>
        <div class="col-4">
            <span class="text-secondary">Kelas:</span> <strong>{{ $filterKelasNama }}</strong>
        </div>
        <div class="col-4">
            <span class="text-secondary">Mata Pelajaran:</span> <strong>{{ $filterMapelNama }}</strong>
        </div>
    </div>

    <!-- STATISTIK SINGKAT REKAP -->
    <div class="row g-2 text-center mb-3">
        <div class="col-3">
            <div class="border rounded p-1">
                <div class="text-secondary" style="font-size: 10px;">TOTAL JADWAL</div>
                <div class="fw-bold fs-5">{{ $totalJadwal }}</div>
            </div>
        </div>
        <div class="col-3">
            <div class="border rounded p-1">
                <div class="text-secondary" style="font-size: 10px;">HADIR (TEPAT WAKTU)</div>
                <div class="fw-bold fs-5 text-success">{{ $sudahCheckIn - $terlambatCount }}</div>
            </div>
        </div>
        <div class="col-3">
            <div class="border rounded p-1">
                <div class="text-secondary" style="font-size: 10px;">TERLAMBAT</div>
                <div class="fw-bold fs-5 text-warning">{{ $terlambatCount }}</div>
            </div>
        </div>
        <div class="col-3">
            <div class="border rounded p-1">
                <div class="text-secondary" style="font-size: 10px;">BELUM CHECK-IN</div>
                <div class="fw-bold fs-5 text-danger">{{ $belumCheckInCount }}</div>
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
                <th style="width: 16%;">Mata Pelajaran</th>
                <th style="width: 12%;">Status Kehadiran</th>
                <th style="width: 12%;">Jam Masuk</th>
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
                                <strong>Hadir (Tepat Waktu)</strong>
                            @elseif($absen->status === 'terlambat')
                                <strong>Terlambat</strong>
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
                    <tr style="background-color: #fafafa;">
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
                    <td colspan="8" class="text-center py-3 text-secondary">Tidak ada data jadwal pada tanggal ini.</td>
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
// Otomatis tawarkan dialog print saat halaman dibuka jika ada parameter autoprint
if (window.location.search.includes('autoprint=1')) {
    window.onload = function() {
        window.print();
    }
}
</script>
</body>
</html>
