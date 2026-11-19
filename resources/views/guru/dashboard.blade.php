@extends('layouts.guru.main')
@section('title', 'Beranda')

@section('header_extra')
<div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
    <div class="text-white small d-flex align-items-center gap-1">
        <i class="bi bi-geo-alt-fill text-warning"></i>
        <span>{{ $setting->nama_sekolah ?? 'Sekolah' }}</span>
    </div>
    <div class="badge font-monospace py-1 px-2" style="font-size: var(--text-xs);">
        <i class="bi bi-clock me-1"></i>{{ substr($currentTime, 0, 5) }} WIB
    </div>
</div>
@endsection

@section('content')

<!-- NOTIFIKASI SUKSES / ERROR -->
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

@if (session('info'))
    <div class="alert alert-info alert-dismissible fade show p-3 mb-3" role="alert" >
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-info-circle-fill fs-3 text-info"></i>
            <div>
                <div class="small">{{ session('info') }}</div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show p-3 mb-3" role="alert" >
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-octagon-fill fs-3 text-danger"></i>
            <div>
                <div class="fw-bold">Absensi Gagal!</div>
                <ul class="mb-0 ps-3 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(isset($isPublished) && !$isPublished)
    <div class="card-mobile p-3 mb-3 bg-warning-lt border-warning">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar bg-warning-lt text-warning rounded-circle">
                <i class="bi bi-file-earmark-lock-fill fs-4"></i>
            </div>
            <div>
                <div class="fw-bold text-dark">Jadwal Masih Dalam Tahap Draft</div>
                <div class="small text-secondary">
                    Jadwal semester ini masih dalam proses penyusunan oleh Waka Kurikulum dan belum dipublikasikan resmi. Anda belum dapat melakukan absensi.
                </div>
            </div>
        </div>
    </div>
@endif

@if($izinAktif)
    <div class="card-mobile p-3 mb-3 bg-warning-lt border-warning">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar bg-warning-lt text-warning rounded-circle">
                <i class="bi bi-envelope-heart-fill fs-4"></i>
            </div>
            <div>
                <div class="fw-bold text-dark">Status: {{ ucfirst($izinAktif->jenis) }}</div>
                <div class="small text-secondary">
                    Periode: {{ $izinAktif->tanggal_mulai->format('d/m') }} s.d. {{ $izinAktif->tanggal_selesai->format('d/m/Y') }} (Disetujui)
                </div>
            </div>
        </div>
    </div>
@endif

<!-- INFO HARI INI & KPI RINGKAS -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <div class="text-secondary small">Jadwal Mengajar</div>
        <div class="fw-bold fs-3 text-dark">{{ $hariIni }}, {{ \Carbon\Carbon::parse($today)->translatedFormat('d M Y') }}</div>
    </div>
    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="window.location.reload();">
        <i class="bi bi-arrow-clockwise me-1"></i>Refresh
    </button>
</div>

<!-- KPI CHIPS -->
<div class="row g-2 mb-3">
    <div class="col-4">
        <div class="card-mobile p-2 text-center h-100">
            <div class="text-secondary" style="font-size: var(--text-xs);">Total Jadwal</div>
            <div class="h3 mb-0 fw-bold text-primary">{{ $totalJadwalHariIni }}</div>
        </div>
    </div>
    <div class="col-4">
        <div class="card-mobile p-2 text-center h-100">
            <div class="text-secondary" style="font-size: var(--text-xs);">Sudah Absen</div>
            <div class="h3 mb-0 fw-bold text-success">{{ $totalSudahAbsen }}</div>
        </div>
    </div>
    <div class="col-4">
        <div class="card-mobile p-2 text-center h-100">
            <div class="text-secondary" style="font-size: var(--text-xs);">Jadwal Aktif</div>
            <div class="h3 mb-0 fw-bold text-azure">
                {{ $jadwalAktifSekarang ? '1' : '0' }}
            </div>
        </div>
    </div>
</div>

<!-- JADWAL MENGAJAR HARI INI -->
<div class="mb-2">
    <div class="text-secondary fw-semibold small mb-2" style="font-size: var(--text-xs);">
        Jam Mengajar Hari Ini
    </div>

    @if($jadwalUtama)
        @php $j = $jadwalUtama; @endphp
        <div class="card-mobile p-3 mb-3 {{ $j->live_status === 'berlangsung' ? ($j->is_late ? 'border-warning' : 'border-success') : '' }}">
            <!-- TOP CARD: CLASS & TIME -->
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="badge bg-primary-lt fw-bold px-2 py-1" >
                    <i class="bi bi-door-open me-1"></i>{{ $j->kelas->nama_kelas }}
                </span>
                <span class="badge bg-secondary-lt font-monospace" style="font-size: var(--text-xs);">
                    <i class="bi bi-clock me-1"></i>{{ substr($j->meeting_start, 0, 5) }} - {{ substr($j->meeting_end, 0, 5) }}
                </span>
            </div>

            <!-- SUBJECT NAME -->
            <div class="mb-2">
                <h4 class="fw-bold mb-0 text-dark">{{ $j->mataPelajaran->nama }}</h4>
            </div>

            <!-- STATUS BADGE -->
            <div class="mb-3">
                @if($j->live_status === 'draft')
                    <span class="badge bg-warning-lt px-2 py-1">
                        <i class="bi bi-file-earmark-lock me-1"></i> Status: Draft (Belum Dipublikasikan Waka)
                    </span>
                @elseif($j->live_status === 'sudah_absen')
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success-lt px-2 py-1">
                            <i class="bi bi-check-circle-fill me-1"></i>
                            {{ $j->absen_record->status === 'terlambat' ? 'Hadir (Terlambat)' : 'Hadir Tepat Waktu' }}
                        </span>
                        <span class="text-secondary small font-monospace">Pukul {{ substr($j->absen_record->waktu_absen, 0, 5) }} WIB</span>
                    </div>
                @elseif($j->live_status === 'berlangsung')
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="badge {{ $j->is_late ? 'bg-warning text-dark' : 'bg-success text-white' }} px-2 py-1">
                            <i class="bi {{ $j->is_late ? 'bi-alarm' : 'bi-broadcast' }} me-1"></i>
                            {{ $j->is_late ? 'Terlambat' : 'Sedang Berlangsung' }}
                        </span>
                    </div>
                @elseif($j->live_status === 'belum_mulai')
                    <span class="badge bg-secondary-lt px-2 py-1">
                        <i class="bi bi-hourglass-top me-1"></i> Akan Berlangsung
                    </span>
                @elseif($j->live_status === 'habis')
                    <span class="badge bg-danger-lt px-2 py-1">
                        <i class="bi bi-x-circle me-1"></i> Terlewat (Tidak Hadir)
                    </span>
                @elseif($j->live_status === 'izin_sakit')
                    <span class="badge bg-info-lt px-2 py-1">
                        <i class="bi bi-envelope-fill me-1"></i> {{ ucfirst($izinAktif->jenis) }}
                    </span>
                @endif
            </div>

            <!-- BUTTON ACTION -->
            <div>
                @if($j->live_status === 'draft')
                    <button type="button" class="btn btn-outline-secondary w-100 py-2 disabled" >
                        <i class="bi bi-lock-fill me-1"></i> Jadwal Belum Dipublikasikan
                    </button>
                @elseif($j->live_status === 'berlangsung')
                    <button type="button" class="btn-mobile-success"
                        data-bs-toggle="modal"
                        data-bs-target="#modalCheckIn{{ $j->id }}"
                        onclick="prepareGps({{ $j->id }})">
                        <i class="bi bi-geo-alt-fill"></i> Check-In Sekarang
                    </button>
                @elseif($j->live_status === 'sudah_absen')
                    <button type="button" class="btn-mobile-outline w-100"
                        data-bs-toggle="modal"
                        data-bs-target="#modalLihat{{ $j->absen_record->id }}">
                        <i class="bi bi-journal-check"></i> Lihat Jurnal Mengajar
                    </button>
                @elseif($j->live_status === 'belum_mulai')
                    <button type="button" class="btn btn-secondary w-100 py-2 disabled" >
                        <i class="bi bi-lock-fill me-1"></i> Belum Waktu Mengajar
                    </button>
                @else
                    <button type="button" class="btn btn-outline-secondary w-100 py-2 disabled" >
                        <i class="bi bi-slash-circle me-1"></i> Waktu Absen Telah Habis
                    </button>
                @endif
            </div>

            <!-- ================================================== -->
            <!-- MODAL BOTTOM SHEET: CHECK-IN DENGAN GPS & JURNAL  -->
            <!-- ================================================== -->
            @if($j->live_status === 'berlangsung')
                <div class="modal fade" id="modalCheckIn{{ $j->id }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-bottom">
                        <div class="modal-content">
                            <form action="{{ route('guru.absen.store') }}" method="POST" id="formAbsen{{ $j->id }}">
                                @csrf
                                <input type="hidden" name="jadwal_pelajaran_id" value="{{ $j->id }}">
                                <input type="hidden" name="latitude" id="lat{{ $j->id }}" value="">
                                <input type="hidden" name="longitude" id="lng{{ $j->id }}" value="">

                                <div class="modal-header py-3 px-4 border-0 d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar avatar-sm bg-success text-white rounded-circle">
                                            <i class="bi bi-check2"></i>
                                        </div>
                                        <div>
                                            <h5 class="modal-title fw-bold mb-0">Presensi Mengajar</h5>
                                            <div class="text-secondary small">{{ $j->mataPelajaran->nama }}, {{ $j->kelas->nama_kelas }}</div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <div class="modal-body px-4 pt-1 pb-3">
                                    <!-- STATUS RADAR GPS -->
                                    <div class="well p-3 mb-3">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <div class="spinner-border spinner-border-sm text-primary" id="spinner{{ $j->id }}" role="status"></div>
                                            <div class="fw-bold small" id="gpsTitle{{ $j->id }}">Mendeteksi Lokasi GPS...</div>
                                        </div>
                                        <div id="gpsAlert{{ $j->id }}" class="alert alert-info py-2 px-3 mb-2 small" >
                                            Pastikan GPS aktif & izinkan akses lokasi browser Anda.
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center small text-secondary" style="font-size: var(--text-xs);">
                                            <span>Radius maks: {{ $setting->radius_meter }} m</span>
                                            <span id="gpsCoordText{{ $j->id }}">Mencari satelit...</span>
                                        </div>
                                            <button type="button" class="btn btn-outline-primary btn-sm w-100 mt-2 d-none"
                                                id="retryGps{{ $j->id }}"
                                                onclick="prepareGps({{ $j->id }})">
                                                <i class="bi bi-arrow-clockwise me-1"></i> Coba GPS Lagi
                                            </button>
                                        </div>

                                    <!-- MATERI PELAJARAN -->
                                    <div class="mb-3">
                                        <label class="form-label required fw-bold small">Materi yang Diajarkan Hari Ini</label>
                                        <textarea name="materi_diajarkan" class="form-control" rows="3" placeholder="Tuliskan pokok bahasan / bab materi yang diajarkan..." required >{{ old('materi_diajarkan') }}</textarea>
                                    </div>

                                    <!-- CATATAN SISWA -->
                                    <div class="mb-2">
                                        <label class="form-label fw-bold small">Catatan Murid Tidak Masuk (Opsional)</label>
                                        <textarea name="catatan_siswa_tidak_masuk" class="form-control" rows="2" placeholder="Contoh: Budi (Sakit), Ani (Izin), atau kosongkan jika hadir semua." >{{ old('catatan_siswa_tidak_masuk') }}</textarea>
                                    </div>
                                </div>

                                <div class="modal-footer px-4 pb-4 pt-0 border-0">
                                    <button type="submit" class="btn-mobile-success" id="btnSubmit{{ $j->id }}" disabled>
                                        <i class="bi bi-send-check-fill"></i> Kirim Presensi Sekarang
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary w-100 py-2 mt-2" data-bs-dismiss="modal" >
                                        Batal
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <!-- ================================================== -->
            <!-- MODAL BOTTOM SHEET: LIHAT JURNAL MENGAJAR          -->
            <!-- ================================================== -->
            @if($j->live_status === 'sudah_absen' && $j->absen_record)
                <div class="modal fade" id="modalLihat{{ $j->absen_record->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-bottom">
                        <div class="modal-content">
                            <div class="modal-header py-3 px-4 border-0">
                                <div>
                                    <h5 class="modal-title fw-bold">Detail Jurnal Mengajar</h5>
                                    <div class="text-secondary small">{{ $j->mataPelajaran->nama }}, {{ $j->kelas->nama_kelas }}</div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body px-4 pt-0">
                                <div class="well p-3 mb-3">
                                    <div class="row g-2 small">
                                        <div class="col-6">
                                            <div class="text-secondary">Waktu Check-In</div>
                                            <div class="fw-bold">{{ $j->absen_record->waktu_absen }} WIB</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-secondary">Jarak Lokasi</div>
                                            <div class="fw-bold text-success">{{ $j->absen_record->jarak_meter ?? 0 }} m dari sekolah</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="text-secondary small fw-bold mb-1">Materi Pembelajaran</div>
                                    <div class="well p-3 small">
                                        {{ $j->absen_record->materi_diajarkan }}
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="text-secondary small fw-bold mb-1">Catatan Murid Tidak Hadir</div>
                                    <div class="well p-3 small">
                                        {{ $j->absen_record->catatan_siswa_tidak_masuk ?? 'Semua siswa hadir lengkap.' }}
                                    </div>
                                </div>

                                @if($j->absen_record->latitude && $j->absen_record->longitude)
                                    <div class="mb-2">
                                        <a href="https://www.google.com/maps?q={{ $j->absen_record->latitude }},{{ $j->absen_record->longitude }}" target="_blank" class="btn btn-outline-primary btn-sm w-100 py-2" >
                                            <i class="bi bi-geo-alt-fill me-1"></i> Buka Peta Lokasi Presensi
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer px-4 pb-4 pt-0 border-0">
                                <button type="button" class="btn btn-secondary w-100 py-2" data-bs-dismiss="modal" >
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="card-mobile p-4 text-center">
            <div class="avatar bg-primary-lt text-primary rounded-circle mb-3 mx-auto" style="width: 60px; height: 60px;">
                <i class="bi bi-calendar-x fs-2"></i>
            </div>
            <h4 class="fw-bold">Tidak Ada Jam Mengajar Hari Ini</h4>
            <p class="text-secondary small mb-3">Hari ini ({{ $hariIni }}) Anda tidak memiliki jadwal mengajar.</p>
            <a href="{{ route('guru.jadwal') }}" class="btn-mobile-outline">
                <i class="bi bi-calendar2-week"></i> Cek Jadwal Hari Lain
            </a>
        </div>
    @endif
</div>

<script>
// KOORDINAT SEKOLAH DARI DATABASE
const SEKOLAH_LAT = {{ (float) $setting->latitude }};
const SEKOLAH_LNG = {{ (float) $setting->longitude }};
const RADIUS_MAKS = {{ (int) $setting->radius_meter }};

function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371000; // meter
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return Math.round(R * c);
}

function prepareGps(jadwalId) {
    const spinner = document.getElementById('spinner' + jadwalId);
    const title = document.getElementById('gpsTitle' + jadwalId);
    const alertBox = document.getElementById('gpsAlert' + jadwalId);
    const coordText = document.getElementById('gpsCoordText' + jadwalId);
    const btnSubmit = document.getElementById('btnSubmit' + jadwalId);
    const inputLat = document.getElementById('lat' + jadwalId);
    const inputLng = document.getElementById('lng' + jadwalId);
    const retryGps = document.getElementById('retryGps' + jadwalId);

    title.className = "fw-bold small";
    btnSubmit.disabled = true;
    if (retryGps) {
        retryGps.classList.add('d-none');
    }

    if (!window.isSecureContext) {
        spinner.classList.add('d-none');
        title.innerText = "Koneksi Tidak Aman";
        alertBox.className = "alert alert-danger py-2 px-3 mb-2 small";
        alertBox.innerText = "Akses GPS hanya tersedia melalui HTTPS atau localhost.";
        if (retryGps) {
            retryGps.classList.remove('d-none');
        }
        return;
    }

    if (!navigator.geolocation) {
        spinner.classList.add('d-none');
        title.innerText = "GPS Tidak Didukung";
        alertBox.className = "alert alert-danger py-2 px-3 mb-2 small";
        alertBox.innerText = "Browser tidak mendukung Geolocation.";
        if (retryGps) {
            retryGps.classList.remove('d-none');
        }
        return;
    }

    spinner.classList.remove('d-none');
    title.innerText = "Mendeteksi Posisi Anda...";
    alertBox.className = "alert alert-info py-2 px-3 mb-2 small";
    alertBox.innerText = "Sedang mengambil sinyal GPS satelit HP Anda...";

    navigator.geolocation.getCurrentPosition(
        function(position) {
            spinner.classList.add('d-none');
            const userLat = position.coords.latitude;
            const userLng = position.coords.longitude;
            inputLat.value = userLat;
            inputLng.value = userLng;

            coordText.innerText = `${userLat.toFixed(5)}, ${userLng.toFixed(5)}`;

            const jarak = calculateDistance(userLat, userLng, SEKOLAH_LAT, SEKOLAH_LNG);

            if (jarak <= RADIUS_MAKS) {
                title.innerText = "Lokasi Valid (Di Lingkungan Sekolah)";
                title.className = "fw-bold small text-success";
                alertBox.className = "alert alert-success py-2 px-3 mb-2 small";
                alertBox.innerHTML = `<i class="bi bi-geo-fill me-1"></i> Jarak ke sekolah: <strong>${jarak} m</strong> (Diizinkan, batas: ${RADIUS_MAKS} m).`;
                btnSubmit.disabled = false;
            } else {
                title.innerText = "Di Luar Radius Sekolah";
                title.className = "fw-bold small text-danger";
                alertBox.className = "alert alert-danger py-2 px-3 mb-2 small";
                alertBox.innerHTML = `<i class="bi bi-exclamation-octagon-fill me-1"></i> Anda berada <strong>${jarak} m</strong> dari sekolah (Maksimal: ${RADIUS_MAKS} m). Absen harus di sekolah.`;
                btnSubmit.disabled = true;
                if (retryGps) {
                    retryGps.classList.remove('d-none');
                }
            }
        },
        function(error) {
            spinner.classList.add('d-none');
            title.innerText = "Gagal Mengambil Lokasi GPS";
            title.className = "fw-bold small text-danger";
            alertBox.className = "alert alert-danger py-2 px-3 mb-2 small";
            let msg = error.message;
            if (error.code === 1) msg = "Izin GPS ditolak. Silakan aktifkan izin lokasi di browser HP Anda.";
            else if (error.code === 2) msg = "Posisi GPS tidak ditemukan. Pastikan GPS HP aktif.";
            else if (error.code === 3) msg = "Waktu pencarian sinyal habis. Coba klik tombol lagi.";
            alertBox.innerText = msg;
            btnSubmit.disabled = true;
            if (retryGps) {
                retryGps.classList.remove('d-none');
            }
        },
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
    );
}

document.addEventListener('shown.bs.modal', function(event) {
    const modal = event.target;
    const jadwalId = modal.id?.replace('modalCheckIn', '');

    if (jadwalId && document.getElementById('lat' + jadwalId)?.value === '') {
        prepareGps(jadwalId);
    }
});

document.querySelectorAll('form[id^="formAbsen"]').forEach(function(form) {
    form.addEventListener('submit', function() {
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton && !submitButton.disabled) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengirim Presensi...';
        }
    });
});
</script>
@endsection
