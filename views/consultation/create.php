<?php
require_once LAYOUT_PATH . 'header.php';
require_once LAYOUT_PATH . 'sidebar.php';
?>

<div class="main-content-wrapper">
    <?php require_once LAYOUT_PATH . 'navbar.php'; ?>
    
    <div class="container-fluid p-3 p-md-4">
        
        <!-- Header -->
        <div class="mb-4">
            <a href="<?= url('index.php?route=consultation') ?>" class="text-decoration-none small">
                <i class="bi bi-arrow-left"></i> Back to Directory
            </a>
            <h3 class="fw-bold mt-2 mb-1"><?= escape($pageTitle) ?></h3>
            <p class="text-muted small">Log symptoms, diagnosis, treatment plan, and distribute matching medicines from active inventory.</p>
        </div>

        <!-- Form Card -->
        <div class="card border-0 shadow-sm" style="max-width: 850px;">
            <div class="card-body p-4 p-md-5">
                <form action="<?= url('index.php?route=consultation/store') ?>" method="POST" id="consultation-form" class="needs-validation" novalidate>
                    <?= csrf_field() ?>

                    <div class="row g-4 mb-4">
                        <!-- Patient Select -->
                        <div class="col-12 col-md-6">
                            <label for="resident_id" class="form-label fw-semibold small">Patient (Resident) <span class="text-danger">*</span></label>
                            <select class="form-select searchable-select" id="resident_id" name="resident_id" required>
                                <option value="" selected disabled>Select Patient</option>
                                <?php foreach ($residents as $res): ?>
                                    <option value="<?= $res['id'] ?>">
                                        <?= escape($res['last_name'] . ', ' . $res['first_name'] . ' (' . $res['resident_id'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select a resident patient.</div>
                        </div>

                        <!-- Consultation Date -->
                        <div class="col-12 col-md-6">
                            <label for="consultation_date" class="form-label fw-semibold small">Consultation Date <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-calendar-check"></i></span>
                                <input type="text" class="form-control datepicker" id="consultation_date" name="consultation_date" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="invalid-feedback">Date of visit is required.</div>
                        </div>

                        <!-- Symptoms -->
                        <div class="col-12">
                            <label for="symptoms" class="form-label fw-semibold small">Symptoms / Patient Complaints <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="symptoms" name="symptoms" rows="3" required placeholder="Describe symptoms, duration, temperature, vitals, etc."></textarea>
                            <div class="invalid-feedback">Patient symptoms are required.</div>
                        </div>

                        <!-- Diagnosis -->
                        <div class="col-12 col-md-6">
                            <label for="diagnosis" class="form-label fw-semibold small">Diagnosis <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="diagnosis" name="diagnosis" required placeholder="e.g. Acute Bronchitis, Hypertension">
                            <div class="invalid-feedback">Diagnosis is required.</div>
                        </div>

                        <!-- Status -->
                        <div class="col-12 col-md-6">
                            <label for="status" class="form-label fw-semibold small">Consultation Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Completed" selected>Completed</option>
                                <option value="Pending">Pending</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>

                        <!-- Treatment -->
                        <div class="col-12">
                            <label for="treatment" class="form-label fw-semibold small">Treatment Plan / Instructions <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="treatment" name="treatment" rows="2" required placeholder="e.g. Rest for 3 days, drink plenty of water, dosage timeline..."></textarea>
                            <div class="invalid-feedback">Treatment details are required.</div>
                        </div>

                        <!-- Divider for Optional Dispensation -->
                        <div class="col-12 my-2"><hr></div>
                        <h6 class="fw-bold text-success mb-1"><i class="bi bi-capsule-prescription me-2"></i>Dispense Medicine (Optional)</h6>

                        <!-- Medicine Selection -->
                        <div class="col-12 col-md-6">
                            <label for="medicine_id" class="form-label fw-semibold small">Medicine / Supply</label>
                            <select class="form-select" id="medicine_id" name="medicine_id">
                                <option value="" selected>No prescription allocation</option>
                                <?php foreach ($medicines as $med): ?>
                                    <option value="<?= $med['id'] ?>" data-stock="<?= $med['stock_qty'] ?>">
                                        <?= escape($med['name'] . ' [' . $med['category'] . '] - Code: ' . $med['code'] . ' (' . $med['stock_qty'] . ' available)') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Quantity -->
                        <div class="col-12 col-md-6">
                            <label for="medicine_qty" class="form-label fw-semibold small">Quantity</label>
                            <input type="number" class="form-control" id="medicine_qty" name="medicine_qty" min="1" placeholder="Enter quantity to dispense">
                            <div class="form-text text-danger d-none" id="stock-error-msg">Cannot dispense more than available stock!</div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="<?= url('index.php?route=consultation') ?>" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4" id="submit-btn">Save Record</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<?php require_once LAYOUT_PATH . 'footer.php'; ?>

<script>
$(document).ready(function() {
    
    // Real-time stock validation check
    function validateStock() {
        const selectedOpt = $('#medicine_id').find(':selected');
        const availableStock = parseInt(selectedOpt.data('stock')) || 0;
        const requestedQty = parseInt($('#medicine_qty').val()) || 0;
        const medicineSelected = $('#medicine_id').val() !== '';

        if (medicineSelected) {
            // Require quantity if medicine is selected
            $('#medicine_qty').attr('required', true);
            
            if (requestedQty > availableStock) {
                $('#medicine_qty').addClass('is-invalid');
                $('#stock-error-msg').removeClass('d-none');
                $('#submit-btn').attr('disabled', true);
                return false;
            }
        } else {
            $('#medicine_qty').removeAttr('required').removeClass('is-invalid');
            $('#stock-error-msg').addClass('d-none');
            $('#submit-btn').attr('disabled', false);
        }
        
        $('#medicine_qty').removeClass('is-invalid');
        $('#stock-error-msg').addClass('d-none');
        $('#submit-btn').attr('disabled', false);
        return true;
    }

    $('#medicine_id, #medicine_qty').on('change keyup', function() {
        validateStock();
    });

    $('#consultation-form').on('submit', function(e) {
        if (!validateStock()) {
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
            App.showLoader();
        }
        form.classList.add('was-validated')
      }, false)
    })
})()
</script>
