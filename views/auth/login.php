<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= APP_NAME ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= url('css/style.css') ?>" rel="stylesheet">
    <style>
        body.login-body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(rgba(15, 23, 42, 0.39), rgba(15, 23, 42, 0.55)), url('<?= url('images/login-bg.jpg') ?>');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
        }
        [data-bs-theme="dark"] body.login-body {
            background: linear-gradient(rgba(15, 23, 42, 0.75), rgba(15, 23, 42, 0.75)), url('<?= url('images/login-bg.jpg') ?>');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .login-card {
            background: rgba(15, 23, 42, 0.45) !important;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 1.5rem !important;
            max-width: 450px;
            width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
            color: #ffffff;
            z-index: 2;
        }
        .login-card h4 {
            color: #ffffff !important;
            font-weight: 700 !important;
            letter-spacing: -0.025em;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        .login-card p.text-muted {
            color: rgba(255, 255, 255, 0.85) !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }
        .login-card .rounded-circle {
            width: 75px !important;
            height: 75px !important;
            font-size: 2.2rem !important;
            background: linear-gradient(135deg, #0f52ba 0%, #0284c7 100%) !important;
            border: 3px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 0 20px rgba(15, 82, 186, 0.4) !important;
            transition: all 0.3s ease;
        }
        .login-card .rounded-circle:hover {
            transform: scale(1.05) rotate(5deg);
            box-shadow: 0 0 25px rgba(15, 82, 186, 0.6) !important;
        }
        .login-card .form-label {
            color: #ffffff !important;
            font-weight: 600 !important;
            letter-spacing: 0.01em;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }
        .login-card .input-group {
            border-radius: 50px;
            background-color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }
        .login-card .input-group:focus-within {
            box-shadow: 0 0 0 3px rgba(15, 82, 186, 0.4);
            border-color: #0f52ba;
        }
        .login-card .input-group-text {
            background-color: transparent !important;
            border: none !important;
            padding-left: 1.25rem;
            color: #0f52ba !important;
        }
        .login-card .input-group-text i {
            color: #0f52ba !important;
        }
        .login-card .form-control {
            background-color: transparent !important;
            border: none !important;
            padding: 0.75rem 1.25rem 0.75rem 0.5rem !important;
            font-size: 0.95rem !important;
            color: #0f172a !important;
            border-radius: 0 !important;
            font-weight: 500 !important;
        }
        .login-card .form-control::placeholder {
            color: #94a3b8 !important;
        }
        .login-card #toggle-password {
            padding-right: 1.25rem;
            padding-left: 0.5rem;
        }
        .login-card .form-check-label {
            color: #ffffff !important;
            font-weight: 500 !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }
        .login-card .form-check-input {
            background-color: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.3);
        }
        .login-card .form-check-input:checked {
            background-color: #0f52ba;
            border-color: #0f52ba;
        }
        .login-card a.text-primary {
            color: #38bdf8 !important;
            font-weight: 500 !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
            transition: all 0.2s ease;
        }
        .login-card a.text-primary:hover {
            color: #7dd3fc !important;
            text-decoration: underline !important;
        }
        .login-card .btn-primary {
            border-radius: 50px !important;
            background: rgba(15, 82, 186, 0.8) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            backdrop-filter: blur(4px);
            padding: 0.75rem 1.5rem !important;
            font-weight: 600 !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 4px 15px rgba(15, 82, 186, 0.3) !important;
        }
        .login-card .btn-primary:hover {
            background: rgba(15, 82, 186, 0.95) !important;
            box-shadow: 0 6px 20px rgba(15, 82, 186, 0.5) !important;
            transform: translateY(-1px);
        }
        .login-card .btn-primary:active {
            transform: translateY(1px);
        }
        .login-card .text-center.mt-3 span {
            color: rgba(255, 255, 255, 0.85) !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }
        .login-card .text-center.mt-3 strong {
            color: #ffffff !important;
        }
    </style>
</head>
<body class="login-body">

    <!-- Loading Spinner -->
    <div id="loading-spinner" class="spinner-wrapper d-none">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <div class="card glass-panel login-card p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-gradient text-white rounded-circle mb-3" style="width: 60px; height: 60px; font-size: 1.8rem; box-shadow: 0 10px 15px -3px rgba(2, 132, 199, 0.3);">
                <i class="bi bi-heart-pulse-fill"></i>
            </div>
            <h4 class="fw-bold mb-1">Lika BHMS</h4>
            <p class="text-muted small">Access the Health Monitoring System</p>
        </div>

        <!-- System-wide alerts -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <div><?= escape($error) ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <div><?= escape($success) ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form id="login-form" action="<?= url('index.php?route=auth/authenticate') ?>" method="POST">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="username" class="form-label fw-semibold small">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-person text-muted"></i></span>
                    <input type="text" class="form-control border-start-0" id="username" name="username" placeholder="Enter username" required autocomplete="username">
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label fw-semibold small">Password</label>
                <div class="input-group password-toggle-wrapper">
                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" class="form-control border-start-0 border-end-0" id="password" name="password" placeholder="Enter password" required autocomplete="current-password">
                    <span class="input-group-text bg-transparent border-start-0" style="cursor: pointer;" id="toggle-password">
                        <i class="bi bi-eye text-muted" id="password-eye-icon"></i>
                    </span>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me">
                    <label class="form-check-label text-muted small" for="remember_me">
                        Remember Me
                    </label>
                </div>
                <!-- Optional Forgot Password link -->
                <a href="#" onclick="alert('Please contact your administrator to reset your password.')" class="text-primary text-decoration-none small fw-medium">Forgot Password?</a>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2.5 rounded-3 mb-2 shadow-sm d-flex align-items-center justify-content-center">
                <span class="me-2">Sign In</span>
                <i class="bi bi-arrow-right"></i>
            </button>
        </form>
        
        <div class="text-center mt-3">
            <span class="text-muted small"> <strong>PUROK 1 LIKA</strong>, <strong>MLANG</strong></span>
        </div>
    </div>

    <!-- Bootstrap & jQuery JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password Visibility Toggle
        const togglePassword = document.getElementById('toggle-password');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('password-eye-icon');

        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle eye icon class
            if (type === 'text') {
                eyeIcon.classList.remove('bi-eye');
                eyeIcon.classList.add('bi-eye-slash');
            } else {
                eyeIcon.classList.remove('bi-eye-slash');
                eyeIcon.classList.add('bi-eye');
            }
        });

        // Loading Spinner on Form Submit
        const loginForm = document.getElementById('login-form');
        const spinner = document.getElementById('loading-spinner');

        loginForm.addEventListener('submit', function() {
            spinner.classList.remove('d-none');
        });
    </script>
</body>
</html>
