<?php
require_once LAYOUT_PATH . 'header.php';
require_once LAYOUT_PATH . 'sidebar.php';
?>

<div class="main-content-wrapper">
    <?php require_once LAYOUT_PATH . 'navbar.php'; ?>
    
    <div class="container-fluid p-3 p-md-4">
        
        <!-- Header -->
        <div class="mb-4">
            <a href="<?= url('index.php?route=residents') ?>" class="text-decoration-none small">
                <i class="bi bi-arrow-left"></i> Back to Directory
            </a>
            <h3 class="fw-bold mt-2 mb-1"><?= escape($pageTitle) ?></h3>
            <p class="text-muted small">Register a new resident in the health center directory.</p>
        </div>

        <!-- Form Card -->
        <div class="card border-0 shadow-sm" style="max-width: 850px;">
            <div class="card-body p-4 p-md-5">
                <form action="<?= url('index.php?route=residents/store') ?>" method="POST" class="needs-validation" novalidate>
                    <?= csrf_field() ?>

                    <div class="row g-4 mb-4">
                        <!-- First Name -->
                        <div class="col-12 col-md-4">
                            <label for="first_name" class="form-label fw-semibold small">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required placeholder="Enter first name">
                            <div class="invalid-feedback">First name is required.</div>
                        </div>

                        <!-- Middle Name -->
                        <div class="col-12 col-md-4">
                            <label for="middle_name" class="form-label fw-semibold small">Middle Name</label>
                            <input type="text" class="form-control" id="middle_name" name="middle_name" placeholder="Enter middle name">
                        </div>

                        <!-- Last Name -->
                        <div class="col-12 col-md-4">
                            <label for="last_name" class="form-label fw-semibold small">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required placeholder="Enter last name">
                            <div class="invalid-feedback">Last name is required.</div>
                        </div>

                        <!-- Gender -->
                        <div class="col-12 col-md-4">
                            <label for="gender" class="form-label fw-semibold small">Gender <span class="text-danger">*</span></label>
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="" selected disabled>Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                            <div class="invalid-feedback">Please select a gender.</div>
                        </div>

                        <!-- Birthdate -->
                        <div class="col-12 col-md-4">
                            <label for="birthdate" class="form-label fw-semibold small">Birthdate <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-calendar-event"></i></span>
                                <input type="text" class="form-control birthdate-picker" id="birthdate" name="birthdate" required placeholder="YYYY-MM-DD">
                            </div>
                            <div class="invalid-feedback">Please enter a valid birthdate.</div>
                        </div>

                        <!-- Civil Status -->
                        <div class="col-12 col-md-4">
                            <label for="civil_status" class="form-label fw-semibold small">Civil Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="civil_status" name="civil_status" required>
                                <option value="" selected disabled>Select Civil Status</option>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Widowed">Widowed</option>
                                <option value="Divorced">Divorced</option>
                            </select>
                            <div class="invalid-feedback">Please select civil status.</div>
                        </div>

                        <!-- Contact Number -->
                        <div class="col-12 col-md-6">
                            <label for="contact_number" class="form-label fw-semibold small">Contact Number</label>
                            <input type="text" class="form-control" id="contact_number" name="contact_number" placeholder="e.g. 09171234567" pattern="^(09|\+639)\d{9}$">
                            <small class="text-muted">Format: 11 digit mobile number starting with 09.</small>
                            <div class="invalid-feedback">Please enter a valid Philippine mobile number.</div>
                        </div>

                        <!-- Barangay -->
                        <div class="col-12 col-md-6">
                            <label for="barangay" class="form-label fw-semibold small">Barangay Health Center Jurisdiction <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="barangay" name="barangay" value="Barangay Health Center" required>
                            <div class="invalid-feedback">Please input Barangay.</div>
                        </div>

                        <!-- Complete Address -->
                        <div class="col-12">
                            <label for="address" class="form-label fw-semibold small">Complete Address <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="address" name="address" rows="3" required placeholder="Street name, block, lot, zone/sitio..."></textarea>
                            <div class="invalid-feedback">Complete address is required.</div>
                        </div>

                        <!-- Family Head Designation -->
                        <div class="col-12">
                            <div class="form-check form-switch p-3 border rounded-3 bg-light bg-opacity-50">
                                <input class="form-check-input ms-0 me-2" type="checkbox" role="switch" id="is_family_head" name="is_family_head" value="1">
                                <label class="form-check-label fw-semibold small" for="is_family_head">Designate as Family Head</label>
                                <span class="d-block text-muted small mt-1">If active, this resident can be referenced as the family head when building Family Profiles.</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="<?= url('index.php?route=residents') ?>" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">Save Profile</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<?php require_once LAYOUT_PATH . 'footer.php'; ?>

<script>
// Bootstrap 5 client-side validations
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
            App.showLoader(); // Show loading spinner on successful client validation
        }
        form.classList.add('was-validated')
      }, false)
    })
})()
</script>
