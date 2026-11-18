@extends('layouts.guru.main')
@section('title', 'Beranda')

@section('header_extra')
<div class="mt-3 pt-2 border-top border-white border-opacity-10 d-flex justify-content-between align-items-center">
    <div class="text-white small d-flex align-items-center gap-1">
        <i class="bi bi-geo-alt-fill text-warning"></i>
        <span>{{ $setting->nama_sekolah ?? 'Sekolah' }}</span>
    </div>
    <div class="badge bg-white bg-opacity-20 text-white font-monospace py-1 px-2" style="font-size: 0.75rem;">
        <i class="bi bi-clock me-1"></i>{{ substr($currentTime, 0, 5) }} WIB
    </div>
</div>
@endsection

@section('content')

<!-- NOTIFIKASI SUKSES / ERROR -->
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show p-3 mb-3 border-0 shadow-sm" role="alert" style="border-radius: 14px;">
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
    <div class="alert alert-info alert-dismissible fade show p-3 mb-3 border-0 shadow-sm" role="alert" style="border-radius: 14px;">
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
    <div class="alert alert-danger alert-dismissible fade show p-3 mb-3 border-0 shadow-sm" role="alert" style="border-radius: 14px;">
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
            <div class="avatar bg-warning text-dark rounded-circle">
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
            <div class="avatar bg-warning text-white rounded-circle">
                <i class="bi bi-envelope-heart-fill fs-4"></i>
            </div>
            <div>
                <div class="fw-bold text-dark">Status: {{ strtoupper($izinAktif->jenis) }}</div>
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
            <div class="text-secondary" style="font-size: 0.7rem;">Total Jadwal</div>
            <div class="h3 mb-0 fw-bold text-primary">{{ $totalJadwalHariIni }}</div>
        </div>
    </div>
    <div class="col-4">
        <div class="card-mobile p-2 text-center h-100">
            <div class="text-secondary" style="font-size: 0.7rem;">Sudah Absen</div>
            <div class="h3 mb-0 fw-bold text-success">{{ $totalSudahAbsen }}</div>
        </div>
    </div>
    <div class="col-4">
        <div class="card-mobile p-2 text-center h-100">
            <div class="text-secondary" style="font-size: 0.7rem;">Jadwal Aktif</div>
            <div class="h3 mb-0 fw-bold text-azure">
                {{ $jadwalAktifSekarang ? '1' : '0' }}
            </div>
        </div>
    </div>
</div>

<!-- AGENDA HARI INI BERDASARKAN JAM -->
<div class="mb-2">
    <div class="text-secondary fw-semibold small text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">
        Agenda Hari Ini (Urut Jam)
    </div>

    @forelse ($agendaHariIni as $agenda)
        @if($agenda['type'] === 'kegiatan')
            @php $keg = $agenda['data']; @endphp
            <div class="card-mobile p-3 mb-2 {{ $keg->jenis === 'upacara' ? 'bg-danger-lt border-danger' : ($keg->jenis === 'istirahat' ? 'bg-warning-lt border-warning' : 'bg-teal-lt border-teal') }}">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fs-4">
                            @if($keg->jenis === 'upacara') 🇮🇩 @elseif($keg->jenis === 'istirahat') ☕ @elseif($keg->jenis === 'ibadah') 🕌 @elseif($keg->jenis === 'senam') 🏃 @elseif($keg->jenis === 'literasi') 📖 @else 📌 @endif
                        </span>
                        <div>
                            <div class="fw-bold text-dark">{{ $keg->nama_kegiatan }}</div>
                            @if($keg->lokasi)
                                <div class="small text-secondary"><i class="bi bi-geo-alt me-1"></i>{{ $keg->lokasi }}</div>
                            @endif
                        </div>
                    </div>
                    <span class="badge bg-secondary-lt font-monospace text-nowrap">
                        <i class="bi bi-clock me-1"></i>{{ substr($agenda['jam_mulai'], 0, 5) }} - {{ substr($agenda['jam_selesai'], 0, 5) }}
                    </span>
                </div>
            </div>
        @else
            @php $j = $agenda['data']; @endphp
        <div class="card-mobile p-3 mb-3 {{ $j->live_status === 'berlangsung' ? 'border-success pulse-badge' : '' }}">
            <!-- TOP CARD: CLASS & TIME -->
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="badge bg-primary-lt fw-bold px-2 py-1" style="border-radius: 8px;">
                    <i class="bi bi-door-open me-1"></i>{{ $j->kelas->nama_kelas }}
                </span>
                <span class="badge bg-secondary-lt font-monospace" style="font-size: 0.75rem;">
                    <i class="bi bi-clock me-1"></i>{{ substr($agenda['jam_mulai'], 0, 5) }} - {{ substr($agenda['jam_selesai'], 0, 5) }}
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
                        <span class="badge bg-success text-white px-2 py-1">
                            <i class="bi bi-broadcast me-1"></i> Jam Mengajar Berlangsung
                        </span>
                        @if($j->is_late)
                            <span class="text-warning small fw-bold">
                                <i class="bi bi-alarm me-1"></i>Lewat toleransi
                            </span>
                        @endif
                    </div>
                @elseif($j->live_status === 'belum_mulai')
                    <span class="badge bg-secondary-lt px-2 py-1">
                        <i class="bi bi-hourglass-top me-1"></i> Belum Waktunya (Mulai {{ substr($j->jamMulai->jam_mulai, 0, 5) }})
                    </span>
                @elseif($j->live_status === 'habis')
                    <span class="badge bg-danger-lt px-2 py-1">
                        <i class="bi bi-x-circle me-1"></i> Waktu Mengajar Berakhir (Tidak Hadir)
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
                    <button type="button" class="btn btn-outline-secondary w-100 py-2 disabled" style="border-radius: 12px; opacity: 0.6;">
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
                    <button type="button" class="btn btn-secondary w-100 py-2 disabled" style="border-radius: 12px; opacity: 0.6;">
                        <i class="bi bi-lock-fill me-1"></i> Belum Waktu Mengajar
                    </button>
                @else
                    <button type="button" class="btn btn-outline-secondary w-100 py-2 disabled" style="border-radius: 12px; opacity: 0.6;">
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
                                            <div class="text-secondary small">{{ $j->mataPelajaran->nama }} • {{ $j->kelas->nama_kelas }}</div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <div class="modal-body px-4 pt-1 pb-3">
                                    <!-- STATUS RADAR GPS -->
                                    <div class="p-3 mb-3 rounded-3 border bg-body-tertiary">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <div class="spinner-border spinner-border-sm text-primary" id="spinner{{ $j->id }}" role="status"></div>
                                            <div class="fw-bold small" id="gpsTitle{{ $j->id }}">Mendeteksi Lokasi GPS...</div>
                                        </div>
                                        <div id="gpsAlert{{ $j->id }}" class="alert alert-info py-2 px-3 mb-2 small" style="border-radius: 10px;">
                                            Pastikan GPS aktif & izinkan akses lokasi browser Anda.
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center small text-secondary" style="font-size: 0.72rem;">
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
                                        <textarea name="materi_diajarkan" class="form-control" rows="3" placeholder="Tuliskan pokok bahasan / bab materi yang diajarkan..." required style="border-radius: 12px;">{{ old('materi_diajarkan') }}</textarea>
                                    </div>

                                    <!-- CATATAN SISWA -->
                                    <div class="mb-2">
                                        <label class="form-label fw-bold small">Catatan Murid Tidak Masuk (Opsional)</label>
                                        <textarea name="catatan_siswa_tidak_masuk" class="form-control" rows="2" placeholder="Contoh: Budi (Sakit), Ani (Izin), atau kosongkan jika hadir semua." style="border-radius: 12px;">{{ old('catatan_siswa_tidak_masuk') }}</textarea>
                                    </div>
                                </div>

                                <div class="modal-footer px-4 pb-4 pt-0 border-0">
                                    <button type="submit" class="btn-mobile-success" id="btnSubmit{{ $j->id }}" disabled>
                                        <i class="bi bi-send-check-fill"></i> Kirim Presensi Sekarang
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary w-100 py-2 mt-2" data-bs-dismiss="modal" style="border-radius: 12px;">
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
                                    <div class="text-secondary small">{{ $j->mataPelajaran->nama }} • {{ $j->kelas->nama_kelas }}</div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body px-4 pt-0">
                                <div class="p-3 bg-body-tertiary rounded-3 mb-3 border">
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
                                    <div class="p-3 bg-white rounded-3 border small">
                                        {{ $j->absen_record->materi_diajarkan }}
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="text-secondary small fw-bold mb-1">Catatan Murid Tidak Hadir</div>
                                    <div class="p-3 bg-white rounded-3 border small">
                                        {{ $j->absen_record->catatan_siswa_tidak_masuk ?? 'Semua siswa hadir lengkap.' }}
                                    </div>
                                </div>

                                @if($j->absen_record->latitude && $j->absen_record->longitude)
                                    <div class="mb-2">
                                        <a href="https://www.google.com/maps?q={{ $j->absen_record->latitude }},{{ $j->absen_record->longitude }}" target="_blank" class="btn btn-outline-primary btn-sm w-100 py-2" style="border-radius: 12px;">
                                            <i class="bi bi-geo-alt-fill me-1"></i> Buka Peta Lokasi Presensi
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer px-4 pb-4 pt-0 border-0">
                                <button type="button" class="btn btn-secondary w-100 py-2" data-bs-dismiss="modal" style="border-radius: 12px;">
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        @endif
    @empty
        <div class="card-mobile p-4 text-center">
            <div class="avatar bg-primary-lt text-primary rounded-circle mb-3 mx-auto" style="width: 60px; height: 60px;">
                <i class="bi bi-calendar-x fs-2"></i>
            </div>
            <h4 class="fw-bold">Tidak Ada Agenda Hari Ini</h4>
            <p class="text-secondary small mb-3">Hari ini ({{ $hariIni }}) Anda tidak memiliki kegiatan atau jadwal mengajar.</p>
            <a href="{{ route('guru.jadwal') }}" class="btn-mobile-outline">
                <i class="bi bi-calendar2-week"></i> Cek Jadwal Hari Lain
            </a>
        </div>
    @endforelse
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
