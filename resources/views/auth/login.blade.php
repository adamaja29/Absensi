<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Absensi Guru</title>

    <!-- TABLER UI & BOOTSTRAP ICONS -->
    <link rel="stylesheet" href="{{ asset('tabler/css/tabler.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            padding: 1rem;
            margin: 0;
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            border: none;
        }

        .brand-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: #ffffff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .form-control {
            border-color: #e2e8f0;
            padding: 0.65rem 0.85rem;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .input-group-text {
            background-color: #f8fafc;
            border-color: #e2e8f0;
            color: #64748b;
        }

        .btn-login {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
            color: #ffffff;
            border: none;
            padding: 0.75rem;
            font-weight: 600;
            font-size: 0.95rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            transition: all 0.2s ease;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #1e40af 0%, #1d4ed8 100%);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
            transform: translateY(-1px);
            color: #ffffff;
        }

        .demo-chip {
            cursor: pointer;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            border-radius: 8px;
            padding: 6px 8px;
            font-size: 0.75rem;
            transition: all 0.15s ease;
            text-align: left;
        }

        .demo-chip:hover {
            border-color: #3b82f6;
            background: #eff6ff;
            color: #1d4ed8;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="p-4 p-md-5">
        <!-- HEADER & LOGO -->
        <div class="d-flex align-items-center gap-3 mb-4">
            <div class="brand-icon flex-shrink-0">
                <i class="bi bi-clock-history"></i>
            </div>
            <div>
                <h4 class="fw-bold text-dark mb-0">Absensi Guru</h4>
                <p class="text-secondary small mb-0">Sistem Presensi Mengajar & GPS</p>
            </div>
        </div>

        <!-- ALERT ERROR -->
        @if ($errors->any())
            <div class="alert alert-danger py-2 px-3 mb-3 border-0 shadow-sm" style="border-radius: 8px;">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-circle-fill text-danger fs-5"></i>
                    <div class="small fw-semibold">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('login.process') }}" method="POST">
            @csrf

            <!-- EMAIL -->
            <div class="mb-3">
                <label for="email" class="form-label small fw-bold text-secondary">Alamat Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        placeholder="nama@sekolah.sch.id"
                        value="{{ old('email') }}"
                        required
                        autofocus
                    >
                </div>
            </div>

            <!-- PASSWORD -->
            <div class="mb-3">
                <label for="password" class="form-label small fw-bold text-secondary">Kata Sandi</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="••••••••"
                        required
                    >
                    <button class="btn btn-outline-secondary" type="button" id="btnTogglePassword" title="Tampilkan sandi">
                        <i class="bi bi-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <!-- REMEMBER ME -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <label class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <span class="form-check-label small text-secondary">Ingat saya</span>
                </label>
            </div>

            <!-- SUBMIT BUTTON -->
            <button type="submit" class="btn btn-login w-100 mb-4">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang
            </button>
        </form>

        <!-- QUICK LOGIN DEMO PILLS -->
        <div class="pt-3 border-top">
            <div class="text-secondary small fw-bold mb-2" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">
                <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Akun Demo Cepat:
            </div>
            <div class="row g-2">
                <div class="col-4">
                    <div class="demo-chip" onclick="fillLogin('admin@gmail.com', '12345678')">
                        <div class="fw-bold text-dark">Admin</div>
                        <div class="text-secondary font-monospace" style="font-size: 0.6rem; overflow: hidden; text-overflow: ellipsis;">admin@...</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="demo-chip" onclick="fillLogin('waka@gmail.com', '12345678')">
                        <div class="fw-bold text-indigo">Waka</div>
                        <div class="text-secondary font-monospace" style="font-size: 0.6rem; overflow: hidden; text-overflow: ellipsis;">waka@...</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="demo-chip" onclick="fillLogin('andi@gmail.com', '12345678')">
                        <div class="fw-bold text-success">Guru</div>
                        <div class="text-secondary font-monospace" style="font-size: 0.6rem; overflow: hidden; text-overflow: ellipsis;">guru@...</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4 text-secondary" style="font-size: 0.75rem;">
            &copy; {{ date('Y') }} Sistem Presensi Guru. All rights reserved.
        </div>
    </div>
</div>

<script>
// SHOW / HIDE PASSWORD TOGGLE
const btnToggle = document.getElementById('btnTogglePassword');
const inputPassword = document.getElementById('password');
const iconToggle = document.getElementById('toggleIcon');

btnToggle.addEventListener('click', function() {
    if (inputPassword.type === 'password') {
        inputPassword.type = 'text';
        iconToggle.className = 'bi bi-eye-slash';
    } else {
        inputPassword.type = 'password';
        iconToggle.className = 'bi bi-eye';
    }
});

// AUTO FILL HELPER UNTUK DEMO
function fillLogin(email, password) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
}
</script>
</body>
</html>