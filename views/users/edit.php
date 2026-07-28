<?php
require_once LAYOUT_PATH . 'header.php';
require_once LAYOUT_PATH . 'sidebar.php';
?>

<div class="main-content-wrapper">
    <?php require_once LAYOUT_PATH . 'navbar.php'; ?>
    
    <div class="container-fluid p-3 p-md-4">
        
        <div class="mx-auto" style="max-width: 600px;">
            <!-- Header -->
            <div class="mb-4">
                <a href="<?= url('index.php?route=users') ?>" class="text-decoration-none small">
                    <i class="bi bi-arrow-left"></i> Back to Accounts
                </a>
                <h3 class="fw-bold mt-2 mb-1"><?= escape($pageTitle) ?></h3>
                <p class="text-muted small">Update profile settings or reset passwords for operator <strong><?= escape($user['username']) ?></strong>.</p>
            </div>

            <!-- Form Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <form action="<?= url('index.php?route=users/update') ?>" method="POST" class="needs-validation" novalidate id="user-edit-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= $user['id'] ?>">

                        <div class="row g-3 mb-4">
                            <!-- Full Name -->
                            <div class="col-12">
                                <label for="fullname" class="form-label fw-semibold small">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="fullname" name="fullname" value="<?= escape($user['fullname']) ?>" required>
                                <div class="invalid-feedback">Full name is required.</div>
                            </div>

                            <!-- Username -->
                            <div class="col-12 col-md-6">
                                <label for="username" class="form-label fw-semibold small">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" value="<?= escape($user['username']) ?>" required autocomplete="username">
                                <div class="invalid-feedback">Username is required (min 4 characters).</div>
                            </div>

                            <!-- Role -->
                            <div class="col-12 col-md-6">
                                <label for="role" class="form-label fw-semibold small">System Role <span class="text-danger">*</span></label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="Admin" <?= $user['role'] === 'Admin' ? 'selected' : '' ?>>Administrator</option>
                                    <option value="Health Worker" <?= $user['role'] === 'Health Worker' ? 'selected' : '' ?>>Barangay Health Worker (BHW)</option>
                                    <option value="Staff" <?= $user['role'] === 'Staff' ? 'selected' : '' ?>>Regular Staff</option>
                                </select>
                                <div class="invalid-feedback">Please select a role.</div>
                            </div>

                            <!-- Status -->
                            <div class="col-12">
                                <label for="status" class="form-label fw-semibold small">Account Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="Active" <?= $user['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                                    <option value="Inactive" <?= $user['status'] === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>

                            <!-- Optional Password Reset -->
                            <div class="col-12 mt-2"><hr></div>
                            <h6 class="fw-bold text-danger mb-1"><i class="bi bi-shield-slash me-2"></i>Reset Password (Optional)</h6>

                            <div class="col-12 col-md-6">
                                <label for="password" class="form-label fw-semibold small">New Password</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current" minlength="6" autocomplete="new-password">
                                <div class="invalid-feedback">Password must be at least 6 characters.</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="confirm_password" class="form-label fw-semibold small">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password" autocomplete="new-password">
                                <div class="invalid-feedback" id="confirm-feedback">Confirm password does not match.</div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="<?= url('index.php?route=users') ?>" class="btn btn-outline-secondary px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">Update Account</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once LAYOUT_PATH . 'footer.php'; ?>

<script>
$(document).ready(function() {
    $('#user-edit-form').on('submit', function(e) {
        const pass = $('#password').val();
        const confirm = $('#confirm_password').val();
        
        if (pass && pass !== confirm) {
            e.preventDefault();
            e.stopPropagation();
            $('#confirm_password').addClass('is-invalid');
            $('#confirm-feedback').text('Passwords do not match!').show();
        } else {
            $('#confirm_password').removeClass('is-invalid');
            $('#confirm-feedback').hide();
        }
    });
});

// Bootstrap validations
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
            App.showLoader();
        }
        form.classList.add('was-validated')
      }, false)
    })
})()
</script>
