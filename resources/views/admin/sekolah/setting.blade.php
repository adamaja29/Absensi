@extends('layouts.admin.main')
@section('title', 'Pengaturan GPS & Lokasi Sekolah')

@section('styles')
<!-- LEAFLET CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    #map, #modal-map {
        height: 420px;
        width: 100%;
        border-radius: 12px;
        z-index: 1;
    }
    .leaflet-popup-content {
        font-family: inherit;
        font-size: 13px;
    }
</style>
@endsection

@section('content')
<!-- Fallback CDN in case layout head doesn't render -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

<div class="container-xl admin-page">
    <div class="card admin-content-card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h3 class="card-title">Pengaturan Titik GPS & Radius Sekolah</h3>
                <div class="text-secondary mt-1" style="font-size: 0.875rem;">
                    Pilih titik pusat sekolah di peta interaktif Leaflet dan atur radius toleransi absensi guru.
                </div>
            </div>
            <div class="btn-list">
                <!-- TOMBOL UTAMA UNTUK NAMPILIN PETA -->
                <!-- <button type="button" class="btn btn-primary" onclick="scrollKePeta()">
                    <i class="bi bi-map-fill me-1"></i> Tampilkan Peta Leaflet
                </button>
                <button type="button" class="btn btn-teal" data-bs-toggle="modal" data-bs-target="#modalPetaBesar">
                    <i class="bi bi-arrows-fullscreen me-1"></i> Buka Modal Peta
                </button> -->
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Dashboard
                </a>
            </div>
        </div>

        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.sekolah.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <!-- NAMA & ALAMAT SEKOLAH -->
                    <div class="col-md-6">
                        <label class="form-label required">Nama Sekolah</label>
                        <input type="text" name="nama_sekolah" class="form-control" value="{{ old('nama_sekolah', $setting->nama_sekolah) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Alamat Sekolah</label>
                        <input type="text" name="alamat" class="form-control" value="{{ old('alamat', $setting->alamat) }}" placeholder="Contoh: Jl. Pendidikan No. 123">
                    </div>

                    <!-- PETA LEAFLET INTERAKTIF -->
                    <div class="col-12 mt-4" id="map-section">
                        <div class="card border border-primary-subtle shadow-sm">
                            <div class="card-header bg-primary-lt d-flex justify-content-between align-items-center flex-wrap gap-2 py-2 px-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar avatar-sm bg-primary text-white rounded">
                                        <i class="bi bi-geo-alt-fill"></i>
                                    </span>
                                    <div>
                                        <h4 class="m-0 fw-bold text-primary">Peta Lokasi & Radius Sekolah</h4>
                                        <span class="text-secondary small">Klik pada peta atau geser pin merah untuk mengubah koordinat</span>
                                    </div>
                                </div>

                                <!-- TOMBOL GPS & PRESET TESTING -->
                                <div class="btn-list">
                                    <button type="button" class="btn btn-sm btn-primary" id="btn-detect-loc">
                                        <i class="bi bi-crosshair me-1"></i> Posisi GPS Saya
                                    </button>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-compass me-1"></i> Preset Kota
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <button class="dropdown-item" type="button" onclick="setPreset(-6.175392, 106.827153, 'Monas Jakarta')">Jakarta (Monas)</button>
                                            <button class="dropdown-item" type="button" onclick="setPreset(-6.902484, 107.618776, 'Gedung Sate Bandung')">Bandung (Gedung Sate)</button>
                                            <button class="dropdown-item" type="button" onclick="setPreset(-7.795580, 110.369490, 'Malioboro Yogyakarta')">Yogyakarta (Malioboro)</button>
                                            <button class="dropdown-item" type="button" onclick="setPreset(-7.257472, 112.752088, 'Surabaya')">Surabaya (Pusat)</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body p-2">
                                <!-- MAP CONTAINER -->
                                <div id="map" style="height: 420px; width: 100%; border-radius: 8px;"></div>
                                <div class="d-flex justify-content-between align-items-center mt-2 px-2">
                                    <span id="loc-status" class="text-secondary small">Status: Peta siap digunakan. Geser pin merah untuk mengganti koordinat.</span>
                                    <span class="badge bg-primary-lt font-monospace" id="coord-display">
                                        Lat: {{ number_format($setting->latitude, 6) }}, Lng: {{ number_format($setting->longitude, 6) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- INPUT LATITUDE & LONGITUDE -->
                    <div class="col-md-6 mt-3">
                        <label class="form-label required">Latitude Sekolah</label>
                        <div class="input-group">
                            <input type="number" step="any" id="latitude" name="latitude" class="form-control font-monospace" value="{{ old('latitude', $setting->latitude) }}" required>
                            <button type="button" class="btn btn-outline-primary" onclick="scrollKePeta()">
                                <i class="bi bi-geo-alt-fill me-1"></i> Pilih di Peta
                            </button>
                        </div>
                        <small class="text-secondary">Terisi otomatis saat pin peta digeser.</small>
                    </div>

                    <div class="col-md-6 mt-3">
                        <label class="form-label required">Longitude Sekolah</label>
                        <div class="input-group">
                            <input type="number" step="any" id="longitude" name="longitude" class="form-control font-monospace" value="{{ old('longitude', $setting->longitude) }}" required>
                            <button type="button" class="btn btn-outline-primary" onclick="scrollKePeta()">
                                <i class="bi bi-geo-alt-fill me-1"></i> Pilih di Peta
                            </button>
                        </div>
                        <small class="text-secondary">Terisi otomatis saat pin peta digeser.</small>
                    </div>

                    <!-- RADIUS TOLERANSI -->
                    <div class="col-md-6">
                        <label class="form-label required">Radius Toleransi Absensi (Meter)</label>
                        <div class="input-group">
                            <input type="number" id="radius_meter" name="radius_meter" class="form-control" value="{{ old('radius_meter', $setting->radius_meter) }}" min="10" max="5000" required>
                            <span class="input-group-text">Meter</span>
                        </div>
                        <small class="text-secondary">Lingkaran biru pada peta secara realtime menunjukkan radius kehadiran guru.</small>
                    </div>

                    <!-- TOLERANSI WAKTU -->
                    <div class="col-md-6">
                        <label class="form-label required">Toleransi Waktu Keterlambatan</label>
                        <div class="input-group">
                            <input type="number" name="toleransi_terlambat_menit" class="form-control" value="{{ old('toleransi_terlambat_menit', $setting->toleransi_terlambat_menit) }}" min="0" max="120" required>
                            <span class="input-group-text">Menit setelah jam mulai sesi</span>
                        </div>
                        <small class="text-secondary">Jika check-in lewat dari menit ini, guru tercatat sebagai "Terlambat".</small>
                    </div>

                    <div class="col-12 mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary fw-bold px-4">
                            <i class="bi bi-save me-1"></i>Simpan Pengaturan Lokasi
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL PETA BESAR (LAYAR PENUH)                          -->
<!-- ======================================================== -->
<!-- <div class="modal fade" id="modalPetaBesar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center gap-2">
                    <i class="bi bi-map text-primary"></i> Peta Lokasi Sekolah
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2">
                <div class="d-flex justify-content-between align-items-center mb-2 px-2">
                    <span class="text-secondary small">Klik atau geser pin merah ke titik lokasi yang diinginkan:</span>
                    <button type="button" class="btn btn-xs btn-outline-primary" id="btn-modal-detect">
                        <i class="bi bi-crosshair me-1"></i> GPS Saya
                    </button>
                </div>
                <div id="modal-map" style="height: 480px; width: 100%; border-radius: 8px;"></div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <span class="small font-monospace text-secondary" id="modal-coord-text">
                    Koordinat saat ini: {{ $setting->latitude }}, {{ $setting->longitude }}
                </span>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    <i class="bi bi-check-lg me-1"></i> Gunakan Titik Ini
                </button>
            </div>
        </div>
    </div>
</div> -->

<!-- Fallback Leaflet JS direct load -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
function scrollKePeta() {
    const el = document.getElementById('map-section');
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        el.classList.add('border-primary');
        setTimeout(() => el.classList.remove('border-primary'), 2000);
    }
}

document.addEventListener("DOMContentLoaded", function () {
    let initialLat = parseFloat(document.getElementById('latitude').value) || -6.200000;
    let initialLng = parseFloat(document.getElementById('longitude').value) || 106.816666;
    let initialRadius = parseInt(document.getElementById('radius_meter').value) || 100;

    // 1. Inisialisasi Peta Inline
    const map = L.map('map').setView([initialLat, initialLng], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    const marker = L.marker([initialLat, initialLng], {
        draggable: true,
        title: "Titik Pusat Sekolah"
    }).addTo(map);

    marker.bindPopup("<b>Lokasi Sekolah</b><br>Geser pin untuk mengubah koordinat.").openPopup();

    const radiusCircle = L.circle([initialLat, initialLng], {
        color: '#2563eb',
        fillColor: '#3b82f6',
        fillOpacity: 0.2,
        radius: initialRadius
    }).addTo(map);

    function updateCoordinates(lat, lng) {
        document.getElementById('latitude').value = lat.toFixed(7);
        document.getElementById('longitude').value = lng.toFixed(7);

        marker.setLatLng([lat, lng]);
        radiusCircle.setLatLng([lat, lng]);
        marker.getPopup().setContent(`<b>Titik Sekolah</b><br>Lat: ${lat.toFixed(6)}<br>Lng: ${lng.toFixed(6)}`).openPopup();

        const coordDisplay = document.getElementById('coord-display');
        if (coordDisplay) coordDisplay.innerText = `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;

        const modalCoordText = document.getElementById('modal-coord-text');
        if (modalCoordText) modalCoordText.innerText = `Koordinat: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;

        if (modalMarker && modalRadiusCircle) {
            modalMarker.setLatLng([lat, lng]);
            modalRadiusCircle.setLatLng([lat, lng]);
        }
    }

    marker.on('dragend', function (e) {
        const pos = marker.getLatLng();
        updateCoordinates(pos.lat, pos.lng);
    });

    map.on('click', function (e) {
        updateCoordinates(e.latlng.lat, e.latlng.lng);
    });

    document.getElementById('radius_meter').addEventListener('input', function () {
        const newRadius = parseInt(this.value) || 0;
        radiusCircle.setRadius(newRadius);
        if (modalRadiusCircle) modalRadiusCircle.setRadius(newRadius);
    });

    function onManualCoordChange() {
        const lat = parseFloat(document.getElementById('latitude').value);
        const lng = parseFloat(document.getElementById('longitude').value);
        if (!isNaN(lat) && !isNaN(lng)) {
            marker.setLatLng([lat, lng]);
            radiusCircle.setLatLng([lat, lng]);
            map.panTo([lat, lng]);
        }
    }
    document.getElementById('latitude').addEventListener('change', onManualCoordChange);
    document.getElementById('longitude').addEventListener('change', onManualCoordChange);

    window.setPreset = function(lat, lng, label) {
        updateCoordinates(lat, lng);
        map.setView([lat, lng], 16);
        const statusText = document.getElementById('loc-status');
        if (statusText) {
            statusText.innerText = `Peta dipindahkan ke: ${label}`;
            statusText.className = "text-primary small fw-semibold";
        }
    };

    function detectUserLocation(targetMap) {
        const statusText = document.getElementById('loc-status');
        if (!navigator.geolocation) {
            if (statusText) statusText.innerText = "Browser tidak mendukung Geolocation.";
            return;
        }

        if (statusText) statusText.innerText = "Mendeteksi posisi GPS perangkat...";

        navigator.geolocation.getCurrentPosition(
            function (position) {
                const userLat = position.coords.latitude;
                const userLng = position.coords.longitude;
                updateCoordinates(userLat, userLng);
                targetMap.setView([userLat, userLng], 17);
                if (statusText) {
                    statusText.innerText = `Berhasil! Lokasi GPS disinkronkan (${userLat.toFixed(6)}, ${userLng.toFixed(6)}).`;
                    statusText.className = "text-success small fw-bold";
                }
            },
            function (error) {
                if (statusText) {
                    statusText.innerText = "Gagal mendapatkan lokasi GPS: " + error.message;
                    statusText.className = "text-danger small";
                }
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    }

    document.getElementById('btn-detect-loc').addEventListener('click', function () {
        detectUserLocation(map);
    });

    // 2. Inisialisasi Peta Modal
    let modalMap = null;
    let modalMarker = null;
    let modalRadiusCircle = null;

    const modalEl = document.getElementById('modalPetaBesar');
    modalEl.addEventListener('shown.bs.modal', function () {
        const curLat = parseFloat(document.getElementById('latitude').value) || initialLat;
        const curLng = parseFloat(document.getElementById('longitude').value) || initialLng;
        const curRadius = parseInt(document.getElementById('radius_meter').value) || initialRadius;

        if (!modalMap) {
            modalMap = L.map('modal-map').setView([curLat, curLng], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(modalMap);

            modalMarker = L.marker([curLat, curLng], { draggable: true }).addTo(modalMap);
            modalRadiusCircle = L.circle([curLat, curLng], {
                color: '#2563eb',
                fillColor: '#3b82f6',
                fillOpacity: 0.2,
                radius: curRadius
            }).addTo(modalMap);

            modalMarker.on('dragend', function () {
                const pos = modalMarker.getLatLng();
                updateCoordinates(pos.lat, pos.lng);
            });

            modalMap.on('click', function (e) {
                updateCoordinates(e.latlng.lat, e.latlng.lng);
            });

            document.getElementById('btn-modal-detect').addEventListener('click', function () {
                detectUserLocation(modalMap);
            });
        } else {
            modalMap.setView([curLat, curLng], 16);
            modalMarker.setLatLng([curLat, curLng]);
            modalRadiusCircle.setLatLng([curLat, curLng]);
            modalRadiusCircle.setRadius(curRadius);
        }

        setTimeout(() => modalMap.invalidateSize(), 200);
    });

    setTimeout(() => map.invalidateSize(), 300);
});
</script>
@endsection
