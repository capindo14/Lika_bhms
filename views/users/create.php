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
                <p class="text-muted small">Register a new healthcare worker, administrative officer, or staff member.</p>
            </div>

            <!-- Form Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <form action="<?= url('index.php?route=users/store') ?>" method="POST" class="needs-validation" novalidate id="user-create-form">
                        <?= csrf_field() ?>

                        <div class="row g-3 mb-4">
                            <!-- Full Name -->
                            <div class="col-12">
                                <label for="fullname" class="form-label fw-semibold small">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="fullname" name="fullname" required placeholder="Enter full name">
                                <div class="invalid-feedback">Full name is required.</div>
                            </div>

                            <!-- Username -->
                            <div class="col-12 col-md-6">
                                <label for="username" class="form-label fw-semibold small">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" required placeholder="Enter username" autocomplete="username">
                                <div class="invalid-feedback">Username is required (min 4 characters).</div>
                            </div>

                            <!-- Role -->
                            <div class="col-12 col-md-6">
                                <label for="role" class="form-label fw-semibold small">System Role <span class="text-danger">*</span></label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="" selected disabled>Select Role</option>
                                    <option value="Admin">Administrator</option>
                                    <option value="Health Worker">Barangay Health Worker (BHW)</option>
                                    <option value="Staff">Regular Staff</option>
                                </select>
                                <div class="invalid-feedback">Please select a role.</div>
                            </div>

                            <!-- Password -->
                            <div class="col-12 col-md-6">
                                <label for="password" class="form-label fw-semibold small">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" required placeholder="Enter password" minlength="6" autocomplete="new-password">
                                <div class="invalid-feedback">Password is required (min 6 characters).</div>
                            </div>

                            <!-- Confirm Password -->
                            <div class="col-12 col-md-6">
                                <label for="confirm_password" class="form-label fw-semibold small">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="Confirm password" autocomplete="new-password">
                                <div class="invalid-feedback" id="confirm-feedback">Confirm password is required.</div>
                            </div>

                            <!-- Status -->
                            <div class="col-12">
                                <label for="status" class="form-label fw-semibold small">Account Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="Active" selected>Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="<?= url('index.php?route=users') ?>" class="btn btn-outline-secondary px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">Create Account</button>
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
    $('#user-create-form').on('submit', function(e) {
        const pass = $('#password').val();
        const confirm = $('#confirm_password').val();
        
        if (pass !== confirm) {
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
