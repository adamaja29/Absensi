    <aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
        <div class="container-fluid">
            <!-- BEGIN NAVBAR TOGGLER -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- END NAVBAR TOGGLER -->
            <!-- BEGIN NAVBAR LOGO -->
            <div class="navbar-brand navbar-brand-autodark">
                <a href="{{ route('waka.dashboard') }}" class="d-flex align-items-center text-decoration-none text-white fw-bold">
                    <span class="avatar avatar-sm bg-primary text-white me-2">
                        <i class="bi bi-clock-history"></i>
                    </span>
                    <span>Absensi Guru</span>
                </a>
            </div>
            <!-- END NAVBAR LOGO -->
            <div class="navbar-nav flex-row d-lg-none">
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
                        <span class="avatar avatar-sm d-flex align-items-center justify-content-center bg-transparent text-secondary">
                            <i class="bi bi-person-circle fs-3"></i>
                        </span>
                        <div class="d-none d-xl-block ps-2">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="mt-1 small text-secondary">Waka Kurikulum</div>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                        <a href="{{ route('waka.profile') }}" class="dropdown-item w-100 text-start">
                            <i class="bi bi-person-gear me-2 text-primary"></i>Profil Saya
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item w-100 text-start text-danger">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="collapse navbar-collapse" id="sidebar-menu">
                <!-- BEGIN NAVBAR MENU -->
                <ul class="navbar-nav pt-lg-3">
                    <li class="nav-item m-2">
                        <a class="nav-link {{ request()->routeIs('waka.dashboard') ? 'active' : '' }}" href="{{ route('waka.dashboard') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="bi bi-speedometer2 fs-4"></i>
                            </span>
                            <span class="nav-link-title"> Dashboard </span>
                        </a>
                    </li>
                    <li class="nav-item m-2">
                        <a class="nav-link {{ request()->routeIs('waka.guru.*') ? 'active' : '' }}" href="{{ route('waka.guru.data') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="bi bi-person-badge fs-4"></i>
                            </span>
                            <span class="nav-link-title"> Data Guru </span>
                        </a>
                    </li>
                    <li class="nav-item m-2">
                        <a class="nav-link {{ request()->routeIs('waka.tahun-ajaran.*') ? 'active' : '' }}" href="{{ route('waka.tahun-ajaran.data') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="bi bi-calendar3 fs-4"></i>
                            </span>
                            <span class="nav-link-title"> Tahun Ajaran </span>
                        </a>
                    </li>
                    <li class="nav-item m-2">
                        <a class="nav-link {{ request()->routeIs('waka.kelas.*') ? 'active' : '' }}" href="{{ route('waka.kelas.data') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="bi bi-door-open fs-4"></i>
                            </span>
                            <span class="nav-link-title"> Data Kelas </span>
                        </a>
                    </li>
                    <li class="nav-item m-2">
                        <a class="nav-link {{ request()->routeIs('waka.mapel.*') ? 'active' : '' }}" href="{{ route('waka.mapel.data') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="bi bi-book fs-4"></i>
                            </span>
                            <span class="nav-link-title"> Mata Pelajaran </span>
                        </a>
                    </li>
                    <li class="nav-item m-2">
                        <a class="nav-link {{ request()->routeIs('waka.jam-pelajaran.*') ? 'active' : '' }}" href="{{ route('waka.jam-pelajaran.data') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="bi bi-alarm fs-4"></i>
                            </span>
                            <span class="nav-link-title"> Jam Pelajaran </span>
                        </a>
                    </li>
                    <li class="nav-item m-2">
                        <a class="nav-link {{ request()->routeIs('waka.jadwal.*') ? 'active' : '' }}" href="{{ route('waka.jadwal.data') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="bi bi-calendar2-range fs-4"></i>
                            </span>
                            <span class="nav-link-title"> Jadwal Mengajar </span>
                        </a>
                    </li>
                    <li class="nav-item m-2">
                        <a class="nav-link {{ request()->routeIs('waka.absensi.*') ? 'active' : '' }}" href="{{ route('waka.absensi.monitoring') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="bi bi-clipboard-check fs-4"></i>
                            </span>
                            <span class="nav-link-title"> Monitoring Absensi </span>
                        </a>
                    </li>
                    <li class="nav-item m-2">
                        <a class="nav-link {{ request()->routeIs('waka.izin.*') ? 'active' : '' }}" href="{{ route('waka.izin.data') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="bi bi-envelope-paper fs-4"></i>
                            </span>
                            <span class="nav-link-title"> Izin & Sakit Guru </span>
                            @if(($izinMenungguNotification ?? 0) > 0)
                                <span class="badge bg-danger ms-auto">{{ $izinMenungguNotification }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item m-2">
                        <a class="nav-link {{ request()->routeIs('waka.profile') ? 'active' : '' }}" href="{{ route('waka.profile') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="bi bi-person-gear fs-4"></i>
                            </span>
                            <span class="nav-link-title"> Profil Saya </span>
                        </a>
                    </li>
                </ul>
                <!-- END NAVBAR MENU -->
            </div>
        </div>
    </aside>
