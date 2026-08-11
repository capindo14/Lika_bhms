<?php
require_once LAYOUT_PATH . 'header.php';
require_once LAYOUT_PATH . 'sidebar.php';
?>

<div class="main-content-wrapper">
    <?php require_once LAYOUT_PATH . 'navbar.php'; ?>
    
    <div class="container-fluid p-3 p-md-4">
        
        <!-- Header -->
        <div class="mb-4">
            <h3 class="fw-bold mb-1"><?= escape($pageTitle) ?></h3>
            <p class="text-muted small">Update your personal profile information, system name, and credentials.</p>
        </div>

        <div class="row g-4">
            <!-- Left: Profile Widget -->
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm text-center p-4">
                    <div class="mx-auto bg-primary bg-gradient text-white rounded-circle d-flex align-items-center justify-content-center fw-bold mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                        <?= strtoupper(substr($user['fullname'], 0, 2)) ?>
                    </div>
                    <h5 class="fw-bold mb-1"><?= escape($user['fullname']) ?></h5>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-1.5 rounded-pill mx-auto mb-3"><?= escape($user['role']) ?></span>
                    
                    <div class="text-muted small text-start border-top pt-3">
                        <span class="d-block mb-1"><strong>Username:</strong> <?= escape($user['username']) ?></span>
                        <span class="d-block mb-1"><strong>Status:</strong> <?= escape($user['status']) ?></span>
                        <span class="d-block"><strong>Member Since:</strong> <?= date('Y-m-d', strtotime($user['created_at'])) ?></span>
                    </div>
                </div>
            </div>

            <!-- Right: Settings Form -->
            <div class="col-12 col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <form action="<?= url('index.php?route=users/update_settings') ?>" method="POST" class="needs-validation" novalidate id="settings-form">
                            <?= csrf_field() ?>

                            <h5 class="fw-bold text-primary mb-3"><i class="bi bi-person-fill me-2"></i>Personal Profile</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-12 col-md-6">
                                    <label for="fullname" class="form-label fw-semibold small">Display Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="fullname" name="fullname" value="<?= escape($user['fullname']) ?>" required>
                                    <div class="invalid-feedback">Display name is required.</div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="username_display" class="form-label fw-semibold small">System Username</label>
                                    <input type="text" class="form-control bg-light" id="username_display" value="<?= escape($user['username']) ?>" readonly disabled>
                                    <small class="text-muted">Username is set by the system administrator.</small>
                                </div>
                            </div>

                            <hr class="my-4">

                            <?php if ($user['role'] === 'Admin'): ?>
                                <h5 class="fw-bold text-danger mb-3"><i class="bi bi-shield-lock-fill me-2"></i>Update Password</h5>
                                <div class="row g-3 mb-4">
                                    <div class="col-12">
                                        <label for="current_password" class="form-label fw-semibold small">Current Password</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control border-end-0" id="current_password" name="current_password" placeholder="Enter current password to verify identity" autocomplete="current-password">
                                            <span class="input-group-text bg-transparent border-start-0" style="cursor: pointer;" id="toggle-current-password">
                                                <i class="bi bi-eye text-muted" id="current-password-eye-icon"></i>
                                            </span>
                                            <div class="invalid-feedback">Current password is required to save changes.</div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="new_password" class="form-label fw-semibold small">New Password</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control border-end-0" id="new_password" name="new_password" placeholder="Enter new password" minlength="6" autocomplete="new-password">
                                            <span class="input-group-text bg-transparent border-start-0" style="cursor: pointer;" id="toggle-new-password">
                                                <i class="bi bi-eye text-muted" id="new-password-eye-icon"></i>
                                            </span>
                                            <div class="invalid-feedback">New password must be at least 6 characters.</div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="confirm_password" class="form-label fw-semibold small">Confirm New Password</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control border-end-0" id="confirm_password" name="confirm_password" placeholder="Re-enter new password" autocomplete="new-password">
                                            <span class="input-group-text bg-transparent border-start-0" style="cursor: pointer;" id="toggle-confirm-password">
                                                <i class="bi bi-eye text-muted" id="confirm-password-eye-icon"></i>
                                            </span>
                                            <div class="invalid-feedback" id="confirm-feedback">Confirm password does not match.</div>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info border-0 shadow-sm d-flex align-items-center gap-3 p-3 mb-4 rounded-3">
                                    <i class="bi bi-shield-lock fs-3 text-info"></i>
                                    <div>
                                        <h6 class="fw-bold mb-1">Password Management Restricted</h6>
                                        <p class="small text-muted mb-0">Only System Administrators can modify account passwords. Please contact an Admin if you need to reset or update your password.</p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                                <button type="submit" class="btn btn-primary px-4">Save Settings</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once LAYOUT_PATH . 'footer.php'; ?>

<script>
$(document).ready(function() {
    // Password Visibility Toggles
    $('#toggle-current-password').on('click', function() {
        const input = $('#current_password');
        const eyeIcon = $('#current-password-eye-icon');
        const type = input.attr('type') === 'password' ? 'text' : 'password';
        input.attr('type', type);
        
        if (type === 'text') {
            eyeIcon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            eyeIcon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
    });

    $('#toggle-new-password').on('click', function() {
        const input = $('#new_password');
        const eyeIcon = $('#new-password-eye-icon');
        const type = input.attr('type') === 'password' ? 'text' : 'password';
        input.attr('type', type);
        
        if (type === 'text') {
            eyeIcon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            eyeIcon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
    });

    $('#toggle-confirm-password').on('click', function() {
        const input = $('#confirm_password');
        const eyeIcon = $('#confirm-password-eye-icon');
        const type = input.attr('type') === 'password' ? 'text' : 'password';
        input.attr('type', type);
        
        if (type === 'text') {
            eyeIcon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            eyeIcon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
    });

    $('#settings-form').on('submit', function(e) {
        const currentPass = $('#current_password').val();
        const newPass = $('#new_password').val();
        const confirm = $('#confirm_password').val();
        
        let valid = true;

        if (newPass) {
            // Require current password
            if (!currentPass) {
                // Since input is wrapped in input-group, we want is-invalid class on the input element
                $('#current_password').attr('required', true).addClass('is-invalid');
                valid = false;
            } else {
                $('#current_password').removeAttr('required').removeClass('is-invalid');
            }

            // Confirm password matching check
            if (newPass !== confirm) {
                $('#confirm_password').addClass('is-invalid');
                $('#confirm-feedback').text('Passwords do not match!').show();
                valid = false;
            } else {
                $('#confirm_password').removeClass('is-invalid');
                $('#confirm-feedback').hide();
            }
        } else {
            $('#current_password').removeAttr('required').removeClass('is-invalid');
            $('#confirm_password').removeClass('is-invalid');
            $('#confirm-feedback').hide();
        }

        if (!valid) {
            e.preventDefault();
            e.stopPropagation();
        }
    });
});

// Bootstrap Form Validations
(function () {
  'use strict'
  var forms = document.querySelectorAll('.needs-validation')
  Array.prototype.slice.call(forms)
    .forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        } else {
            // Only loader on valid submit
            if ($('#new_password').val() && !$('#current_password').val()) {
                // Skip loader if password check fails
            } else {
                App.showLoader();
            }
        }
        form.classList.add('was-validated')
      }, false)
    })
})()
</script>
