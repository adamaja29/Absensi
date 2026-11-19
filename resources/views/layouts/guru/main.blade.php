<!doctype html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#15202d">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>@yield('title') - Portal Guru Mobile</title>

    <!-- TABLER UI & BOOTSTRAP ICONS -->
    <link rel="stylesheet" href="{{ asset('tabler/css/tabler.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- MOBILE-SPECIFIC STYLES -->
    <link rel="stylesheet" href="{{ asset('css/guru-mobile.css') }}">

    @yield('styles')
</head>

<body>

<div class="mobile-app-container">
    <!-- TOP APP BAR -->
    <header class="mobile-header">
        <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('guru.profile') }}" class="d-flex align-items-center gap-3 text-decoration-none">
                <div class="user-avatar text-white">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div>
                    <div class="text-white-50 small" style="font-size: var(--text-xs);">Portal Guru</div>
                    <div class="fw-bold text-white fs-4 lh-sm">{{ Auth::user()->name }}</div>
                    @if(Auth::user()->guru && Auth::user()->guru->nip)
                        <div class="small text-white-50" style="font-size: var(--text-xs);">NIP: {{ Auth::user()->guru->nip }}</div>
                    @endif
                </div>
            </a>

            <div class="d-flex align-items-center gap-1">
                <!-- EDIT PROFIL BUTTON -->
                <a href="{{ route('guru.profile') }}" class="btn btn-sm text-white-50" title="Profil Saya">
                    <i class="bi bi-person-gear fs-5"></i>
                </a>

                <!-- LOGOUT BUTTON -->
                <form method="POST" action="{{ route('logout') }}" onsubmit="return confirm('Yakin ingin logout?')">
                    @csrf
                    <button type="submit" class="btn btn-sm text-white-50" title="Keluar">
                        <i class="bi bi-box-arrow-right fs-5"></i>
                    </button>
                </form>
            </div>
        </div>

        @yield('header_extra')
    </header>

    <!-- CONTENT BODY -->
    <main class="mobile-content">
        @yield('content')
    </main>

    <!-- BOTTOM NAVIGATION BAR -->
    <nav class="mobile-bottom-nav">
        <a href="{{ route('guru.dashboard') }}" class="nav-item-mobile {{ request()->routeIs('guru.dashboard') ? 'active' : '' }}">
            <i class="bi bi-house-door{{ request()->routeIs('guru.dashboard') ? '-fill' : '' }}"></i>
            <span>Beranda</span>
        </a>

        <a href="{{ route('guru.jadwal') }}" class="nav-item-mobile {{ request()->routeIs('guru.jadwal') ? 'active' : '' }}">
            <i class="bi bi-calendar2-week{{ request()->routeIs('guru.jadwal') ? '-fill' : '' }}"></i>
            <span>Jadwal</span>
        </a>

        <a href="{{ route('guru.riwayat') }}" class="nav-item-mobile {{ request()->routeIs('guru.riwayat') ? 'active' : '' }}">
            <i class="bi bi-clock-history"></i>
            <span>Riwayat</span>
        </a>

        <a href="{{ route('guru.izin') }}" class="nav-item-mobile {{ request()->routeIs('guru.izin') ? 'active' : '' }}">
            <i class="bi bi-envelope-paper{{ request()->routeIs('guru.izin') ? '-fill' : '' }}"></i>
            <span>Izin</span>
        </a>

        <a href="{{ route('guru.profile') }}" class="nav-item-mobile {{ request()->routeIs('guru.profile') ? 'active' : '' }}">
            <i class="bi bi-person{{ request()->routeIs('guru.profile') ? '-fill' : '' }}"></i>
            <span>Profil</span>
        </a>
    </nav>
</div>

<!-- SCRIPTS -->
<script src="{{ asset('tabler/js/tabler.min.js') }}"></script>
@yield('scripts')
</body>
</html>
