    <aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
        <div class="container-fluid">
            <!-- BEGIN NAVBAR TOGGLER -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- END NAVBAR TOGGLER -->
            <!-- BEGIN NAVBAR LOGO -->
            <div class="navbar-brand navbar-brand-autodark">
                <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center text-decoration-none text-white fw-bold">
                    <span class="avatar avatar-sm bg-primary text-white me-2">
                        <i class="bi bi-shield-check"></i>
                    </span>
                    <span>Admin Absensi</span>
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
                            <div class="mt-1 small text-secondary">Administrator</div>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item" style="width: 100%; text-align: left;">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="collapse navbar-collapse" id="sidebar-menu">
                <!-- BEGIN NAVBAR MENU -->
                <ul class="navbar-nav pt-lg-3">
                    <li class="nav-item m-2">
                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="bi bi-speedometer2 fs-4"></i>
                            </span>
                            <span class="nav-link-title"> Home </span>
                        </a>
                    </li>
                    <li class="nav-item m-2 dropdown {{ request()->routeIs('admin.guru.*') || request()->routeIs('admin.waka.*') ? 'active' : '' }}">
                        <a class="nav-link dropdown-toggle" href="#navbar-master" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="true">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="bi bi-people fs-4"></i>
                            </span>
                            <span class="nav-link-title"> Data Pengguna </span>
                        </a>
                        <div class="dropdown-menu show">
                            <a class="dropdown-item {{ request()->routeIs('admin.waka.*') ? 'active' : '' }}" href="{{ route('admin.waka.data') }}"> Data Waka </a>
                            <a class="dropdown-item {{ request()->routeIs('admin.guru.*') ? 'active' : '' }}" href="{{ route('admin.guru.data') }}"> Data Guru </a>
                        </div>
                    </li>
                    <li class="nav-item m-2">
                        <a class="nav-link {{ request()->routeIs('admin.sekolah.*') ? 'active' : '' }}" href="{{ route('admin.sekolah.setting') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="bi bi-geo-alt-fill fs-4 text-danger"></i>
                            </span>
                            <span class="nav-link-title"> Peta & Lokasi GPS Sekolah </span>
                        </a>
                    </li>
                </ul>
                <!-- END NAVBAR MENU -->
            </div>
        </div>
    </aside>
