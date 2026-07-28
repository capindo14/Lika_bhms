<?php
require_once LAYOUT_PATH . 'header.php';
require_once LAYOUT_PATH . 'sidebar.php';
?>

<div class="main-content-wrapper">
    <?php require_once LAYOUT_PATH . 'navbar.php'; ?>
    
    <div class="container-fluid p-3 p-md-4">
        
        <!-- Header -->
        <div class="mb-4">
            <a href="<?= url('index.php?route=immunization') ?>" class="text-decoration-none small">
                <i class="bi bi-arrow-left"></i> Back to Scheduler
            </a>
            <h3 class="fw-bold mt-2 mb-1"><?= escape($pageTitle) ?></h3>
            <p class="text-muted small">Log child/resident vaccination details, target dose levels, or upcoming appointment schedules.</p>
        </div>

        <!-- Form Card -->
        <div class="card border-0 shadow-sm" style="max-width: 800px;">
            <div class="card-body p-4 p-md-5">
                <form action="<?= url('index.php?route=immunization/store') ?>" method="POST" id="immunization-form" class="needs-validation" novalidate>
                    <?= csrf_field() ?>

                    <div class="row g-4 mb-4">
                        <!-- Patient Resident Select -->
                        <div class="col-12 col-md-6">
                            <label for="resident_id" class="form-label fw-semibold small">Resident (Child/Patient) <span class="text-danger">*</span></label>
                            <select class="form-select" id="resident_id" name="resident_id" required>
                                <option value="" selected disabled>Select Patient</option>
                                <?php foreach ($residents as $res): ?>
                                    <option value="<?= $res['id'] ?>">
                                        <?= escape($res['last_name'] . ', ' . $res['first_name'] . ' (' . $res['resident_id'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select the vaccine recipient.</div>
                        </div>

                        <!-- Vaccine Selection -->
                        <div class="col-12 col-md-6">
                            <label for="vaccine_id" class="form-label fw-semibold small">Vaccine Item <span class="text-danger">*</span></label>
                            <select class="form-select" id="vaccine_id" name="vaccine_id" required>
                                <option value="" selected disabled>Select Vaccine</option>
                                <?php foreach ($vaccines as $vac): ?>
                                    <option value="<?= $vac['id'] ?>" data-stock="<?= $vac['stock_qty'] ?>">
                                        <?= escape($vac['name'] . ' - Code: ' . $vac['code'] . ' (' . $vac['stock_qty'] . ' in stock)') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select the vaccine.</div>
                        </div>

                        <!-- Dose level -->
                        <div class="col-12 col-md-6">
                            <label for="dose" class="form-label fw-semibold small">Dose Level <span class="text-danger">*</span></label>
                            <select class="form-select" id="dose" name="dose" required>
                                <option value="" selected disabled>Select Dose</option>
                                <option value="1st Dose">1st Dose</option>
                                <option value="2nd Dose">2nd Dose</option>
                                <option value="3rd Dose">3rd Dose</option>
                                <option value="Booster">Booster</option>
                                <option value="Booster 2">Booster 2</option>
                            </select>
                            <div class="invalid-feedback">Please select a dose level.</div>
                        </div>

                        <!-- Status Selection -->
                        <div class="col-12 col-md-6">
                            <label for="status" class="form-label fw-semibold small">Appointment Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Upcoming" selected>Upcoming Schedule</option>
                                <option value="Completed">Completed (Given)</option>
                                <option value="Missed">Missed Schedule</option>
                            </select>
                        </div>

                        <!-- Date Given -->
                        <div class="col-12 col-md-6" id="date-given-container">
                            <label for="date_given" class="form-label fw-semibold small" id="date-given-label">Date Given</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-calendar-check"></i></span>
                                <input type="text" class="form-control datepicker" id="date_given" name="date_given" placeholder="YYYY-MM-DD">
                            </div>
                            <div class="invalid-feedback" id="date-given-feedback">Please select date given.</div>
                        </div>

                        <!-- Next Schedule -->
                        <div class="col-12 col-md-6" id="next-schedule-container">
                            <label for="next_schedule" class="form-label fw-semibold small" id="next-schedule-label">Next Schedule Date</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-calendar-plus"></i></span>
                                <input type="text" class="form-control future-datepicker" id="next_schedule" name="next_schedule" placeholder="YYYY-MM-DD">
                            </div>
                            <div class="invalid-feedback" id="next-schedule-feedback">Please select future schedule date.</div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="<?= url('index.php?route=immunization') ?>" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4" id="submit-btn">Save Schedule</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<?php require_once LAYOUT_PATH . 'footer.php'; ?>

<script>
$(document).ready(function() {
    
    function toggleFields() {
        const status = $('#status').val();
        
        if (status === 'Completed') {
            // Require Date Given, Next Schedule is optional
            $('#date_given').attr('required', true);
            $('#date-given-label').html('Date Given <span class="text-danger">*</span>');
            
            $('#next_schedule').removeAttr('required');
            $('#next-schedule-label').html('Next Schedule Date');
        } else if (status === 'Upcoming') {
            // Require Next Schedule, Date Given is optional/hidden
            $('#next_schedule').attr('required', true);
            $('#next-schedule-label').html('Next Schedule Date <span class="text-danger">*</span>');
            
            $('#date_given').removeAttr('required');
            $('#date-given-label').html('Date Given');
        } else {
            // Missed schedule
            $('#next_schedule').attr('required', true);
            $('#next-schedule-label').html('Missed Schedule Date <span class="text-danger">*</span>');
            $('#date_given').removeAttr('required');
        }
    }

    $('#status').on('change', function() {
        toggleFields();
    });

    // Run on initial load
    toggleFields();

    // Verify stock availability
    $('#vaccine_id').on('change', function() {
        const stock = parseInt($(this).find(':selected').data('stock')) || 0;
        if (stock === 0 && $('#status').val() === 'Completed') {
            alert("Warning: This vaccine is out of stock. If marked completed, this transaction may fail.");
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
