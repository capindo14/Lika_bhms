<?php
require_once LAYOUT_PATH . 'header.php';
require_once LAYOUT_PATH . 'sidebar.php';
?>

<div class="main-content-wrapper">
    <?php require_once LAYOUT_PATH . 'navbar.php'; ?>
    
    <div class="container-fluid p-3 p-md-4">
        
        <!-- Header -->
        <div class="mb-4">
            <a href="<?= url("index.php?route=residents/view&id={$resident['id']}") ?>" class="text-decoration-none small">
                <i class="bi bi-arrow-left"></i> Back to Profile
            </a>
            <h3 class="fw-bold mt-2 mb-1"><?= escape($pageTitle) ?></h3>
            <p class="text-muted small">Update profile settings for resident <strong><?= escape($resident['first_name'] . ' ' . $resident['last_name']) ?></strong>.</p>
        </div>

        <!-- Form Card -->
        <div class="card border-0 shadow-sm" style="max-width: 850px;">
            <div class="card-body p-4 p-md-5">
                <form action="<?= url('index.php?route=residents/update') ?>" method="POST" class="needs-validation" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $resident['id'] ?>">

                    <div class="row g-4 mb-4">
                        <!-- First Name -->
                        <div class="col-12 col-md-4">
                            <label for="first_name" class="form-label fw-semibold small">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?= escape($resident['first_name']) ?>" required>
                            <div class="invalid-feedback">First name is required.</div>
                        </div>

                        <!-- Middle Name -->
                        <div class="col-12 col-md-4">
                            <label for="middle_name" class="form-label fw-semibold small">Middle Name</label>
                            <input type="text" class="form-control" id="middle_name" name="middle_name" value="<?= escape($resident['middle_name']) ?>">
                        </div>

                        <!-- Last Name -->
                        <div class="col-12 col-md-4">
                            <label for="last_name" class="form-label fw-semibold small">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?= escape($resident['last_name']) ?>" required>
                            <div class="invalid-feedback">Last name is required.</div>
                        </div>

                        <!-- Gender -->
                        <div class="col-12 col-md-4">
                            <label for="gender" class="form-label fw-semibold small">Gender <span class="text-danger">*</span></label>
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="Male" <?= $resident['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= $resident['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                                <option value="Other" <?= $resident['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
                            </select>
                            <div class="invalid-feedback">Please select a gender.</div>
                        </div>

                        <!-- Birthdate -->
                        <div class="col-12 col-md-4">
                            <label for="birthdate" class="form-label fw-semibold small">Birthdate <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-calendar-event"></i></span>
                                <input type="text" class="form-control birthdate-picker" id="birthdate" name="birthdate" value="<?= escape($resident['birthdate']) ?>" required>
                            </div>
                            <div class="invalid-feedback">Please enter a valid birthdate.</div>
                        </div>

                        <!-- Civil Status -->
                        <div class="col-12 col-md-4">
                            <label for="civil_status" class="form-label fw-semibold small">Civil Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="civil_status" name="civil_status" required>
                                <option value="Single" <?= $resident['civil_status'] === 'Single' ? 'selected' : '' ?>>Single</option>
                                <option value="Married" <?= $resident['civil_status'] === 'Married' ? 'selected' : '' ?>>Married</option>
                                <option value="Widowed" <?= $resident['civil_status'] === 'Widowed' ? 'selected' : '' ?>>Widowed</option>
                                <option value="Divorced" <?= $resident['civil_status'] === 'Divorced' ? 'selected' : '' ?>>Divorced</option>
                            </select>
                            <div class="invalid-feedback">Please select civil status.</div>
                        </div>

                        <!-- Contact Number -->
                        <div class="col-12 col-md-6">
                            <label for="contact_number" class="form-label fw-semibold small">Contact Number</label>
                            <input type="text" class="form-control" id="contact_number" name="contact_number" value="<?= escape($resident['contact_number']) ?>" placeholder="e.g. 09171234567" pattern="^(09|\+639)\d{9}$">
                            <div class="invalid-feedback">Please enter a valid Philippine mobile number.</div>
                        </div>

                        <!-- Barangay -->
                        <div class="col-12 col-md-6">
                            <label for="barangay" class="form-label fw-semibold small">Barangay <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="barangay" name="barangay" value="<?= escape($resident['barangay']) ?>" required>
                            <div class="invalid-feedback">Please input Barangay.</div>
                        </div>

                        <!-- Complete Address -->
                        <div class="col-12">
                            <label for="address" class="form-label fw-semibold small">Complete Address <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="address" name="address" rows="3" required><?= escape($resident['address']) ?></textarea>
                            <div class="invalid-feedback">Complete address is required.</div>
                        </div>

                        <!-- Family Head Designation -->
                        <div class="col-12">
                            <div class="form-check form-switch p-3 border rounded-3 bg-light bg-opacity-50">
                                <input class="form-check-input ms-0 me-2" type="checkbox" role="switch" id="is_family_head" name="is_family_head" value="1" <?= $resident['is_family_head'] ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold small" for="is_family_head">Designate as Family Head</label>
                                <span class="d-block text-muted small mt-1">If active, this resident can be referenced as the family head when building Family Profiles.</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="<?= url("index.php?route=residents/view&id={$resident['id']}") ?>" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<?php require_once LAYOUT_PATH . 'footer.php'; ?>

<script>
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
