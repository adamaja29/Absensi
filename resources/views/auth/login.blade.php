<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Absensi Guru</title>

    <!-- TABLER UI & BOOTSTRAP ICONS -->
    <link rel="stylesheet" href="{{ asset('tabler/css/tabler.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">

    <style>
        body {
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--space-md);
            background-color: var(--color-paper-3);
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background-color: var(--color-paper-2);
            border: 1px solid var(--color-rule);
            border-radius: var(--radius-card);
            box-shadow: var(--shadow-card);
        }

        .brand-icon {
            width: 44px;
            height: 44px;
            background-color: var(--color-accent);
            color: var(--color-accent-ink);
            border-radius: var(--radius-control);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .login-title {
            font-size: var(--text-xl);
            font-weight: 600;
            color: var(--color-ink);
        }

        .login-sub {
            font-size: var(--text-sm);
            color: var(--color-muted);
        }

        .field-label {
            display: block;
            margin-bottom: var(--space-2xs);
            font-size: var(--text-sm);
            font-weight: 600;
            color: var(--color-ink-2);
        }

        .form-control {
            min-height: 42px;
            border: 1px solid var(--color-rule-2);
            border-radius: var(--radius-control);
            background-color: var(--color-paper-2);
            color: var(--color-ink);
            font-size: var(--text-base);
        }

        .form-control::placeholder {
            color: var(--color-muted);
        }

        .form-control:focus {
            border-color: var(--color-accent);
            outline: 2px solid var(--color-accent-soft);
            outline-offset: 0;
            box-shadow: none;
            color: var(--color-ink);
        }

        .input-group-text {
            border: 1px solid var(--color-rule-2);
            background-color: var(--color-paper-3);
            color: var(--color-muted);
        }

        .btn-toggle {
            min-height: 42px;
            border: 1px solid var(--color-rule-2);
            border-radius: 0 var(--radius-control) var(--radius-control) 0;
            background-color: var(--color-paper-3);
            color: var(--color-ink-2);
            transition: background-color var(--dur-micro) var(--ease-out),
                color var(--dur-micro) var(--ease-out);
        }

        .btn-toggle:hover {
            background-color: var(--color-paper-2);
            color: var(--color-ink);
        }

        .btn-toggle:focus-visible {
            outline: 2px solid var(--color-focus);
            outline-offset: 2px;
        }

        .btn-login {
            min-height: 44px;
            width: 100%;
            border: 1px solid var(--color-accent);
            border-radius: var(--radius-control);
            background-color: var(--color-accent);
            color: var(--color-accent-ink);
            font-size: var(--text-sm);
            font-weight: 600;
            transition: background-color var(--dur-micro) var(--ease-out),
                border-color var(--dur-micro) var(--ease-out),
                color var(--dur-micro) var(--ease-out);
        }

        .btn-login:hover {
            background-color: var(--color-accent-hover);
            border-color: var(--color-accent-hover);
            color: var(--color-accent-ink);
        }

        .btn-login:focus-visible {
            outline: 2px solid var(--color-focus);
            outline-offset: 2px;
        }

        .alert-login {
            border: 1px solid var(--color-error);
            border-radius: var(--radius-control);
            background-color: var(--color-error-soft);
            color: var(--color-error);
            font-size: var(--text-sm);
        }

        .demo-note {
            font-size: var(--text-sm);
            color: var(--color-muted);
        }

        .demo-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: var(--space-xs);
        }

        .demo-chip {
            display: block;
            width: 100%;
            padding: var(--space-xs) var(--space-sm);
            border: 1px solid var(--color-rule);
            border-radius: var(--radius-control);
            background-color: var(--color-paper-2);
            text-align: left;
            cursor: pointer;
            transition: border-color var(--dur-micro) var(--ease-out),
                background-color var(--dur-micro) var(--ease-out);
        }

        .demo-chip:hover {
            border-color: var(--color-accent);
            background-color: var(--color-accent-soft);
        }

        .demo-chip:focus-visible {
            outline: 2px solid var(--color-focus);
            outline-offset: 2px;
        }

        .demo-role {
            display: block;
            font-size: var(--text-xs);
            font-weight: 600;
            color: var(--color-ink);
        }

        .demo-mail {
            display: block;
            font-family: var(--font-mono);
            font-size: var(--text-2xs);
            color: var(--color-muted);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .login-foot {
            font-size: var(--text-xs);
            color: var(--color-muted);
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
                <h1 class="login-title mb-0">Absensi Guru</h1>
                <p class="login-sub mb-0">Sistem Presensi Mengajar &amp; GPS</p>
            </div>
        </div>

        <!-- ALERT ERROR -->
        @if ($errors->any())
            <div class="alert-login px-3 py-2 mb-3" role="alert">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-circle-fill fs-5"></i>
                    <div class="fw-semibold">
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
                <label for="email" class="field-label">Alamat email</label>
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
                <label for="password" class="field-label">Kata sandi</label>
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
                    <button class="btn btn-toggle" type="button" id="btnTogglePassword" title="Tampilkan sandi">
                        <i class="bi bi-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <!-- REMEMBER ME -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <label class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <span class="form-check-label">Ingat saya</span>
                </label>
            </div>

            <!-- SUBMIT BUTTON -->
            <button type="submit" class="btn btn-login mb-4">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk sekarang
            </button>
        </form>

        <!-- QUICK LOGIN DEMO PILLS -->
        <div class="pt-3 border-top">
            <div class="demo-note mb-2">Akun demo</div>
            <div class="demo-grid">
                <button type="button" class="demo-chip" onclick="fillLogin('admin@gmail.com', '12345678')">
                    <span class="demo-role">Admin</span>
                    <span class="demo-mail">admin@...</span>
                </button>
                <button type="button" class="demo-chip" onclick="fillLogin('waka@gmail.com', '12345678')">
                    <span class="demo-role">Waka</span>
                    <span class="demo-mail">waka@...</span>
                </button>
                <button type="button" class="demo-chip" onclick="fillLogin('andi@gmail.com', '12345678')">
                    <span class="demo-role">Guru</span>
                    <span class="demo-mail">guru@...</span>
                </button>
            </div>
        </div>

        <div class="text-center mt-4 login-foot">
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
