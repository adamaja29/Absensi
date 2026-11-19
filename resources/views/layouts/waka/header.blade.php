<header class="navbar navbar-expand-md d-none d-lg-flex d-print-none">
    <div class="container-xl">
        <div class="navbar-nav flex-row order-md-last ms-auto">
            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Buka menu pengguna">
                    <span class="avatar avatar-sm bg-primary-lt">
                        <i class="bi bi-person-fill fs-4"></i>
                    </span>
                    <div class="d-none d-xl-block ps-2">
                        <div class="fw-bold">{{ Auth::user()->name ?? 'Pengguna' }}</div>
                        <div class="mt-1 small text-secondary">Waka Kurikulum</div>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <a href="{{ route('waka.profile') }}" class="dropdown-item">
                        <i class="bi bi-person-gear me-2 text-primary"></i>Profil Saya
                    </a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
