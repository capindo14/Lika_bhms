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
    <link href="<?= url('public/css/style.css') ?>" rel="stylesheet">
    <style>
        body.login-body {
            background: linear-gradient(135deg, #e0f2fe 0%, #d1fae5 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        [data-bs-theme="dark"] body.login-body {
            background: linear-gradient(135deg, #0f172a 0%, #064e3b 100%);
        }
        .login-card {
            border-radius: 1.25rem;
            max-width: 450px;
            width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
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
