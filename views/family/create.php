<?php
require_once LAYOUT_PATH . 'header.php';
require_once LAYOUT_PATH . 'sidebar.php';
?>

<div class="main-content-wrapper">
    <?php require_once LAYOUT_PATH . 'navbar.php'; ?>
    
    <div class="container-fluid p-3 p-md-4">
        
        <!-- Header -->
        <div class="mb-4">
            <a href="<?= url('index.php?route=family') ?>" class="text-decoration-none small">
                <i class="bi bi-arrow-left"></i> Back to Profiles
            </a>
            <h3 class="fw-bold mt-2 mb-1"><?= escape($pageTitle) ?></h3>
            <p class="text-muted small">Establish a new household unit and link active resident members.</p>
        </div>

        <!-- Form Card -->
        <div class="card border-0 shadow-sm" style="max-width: 900px;">
            <div class="card-body p-4 p-md-5">
                <form action="<?= url('index.php?route=family/store') ?>" method="POST" id="family-form" class="needs-validation" novalidate>
                    <?= csrf_field() ?>

                    <!-- Section 1: Household Head & Info -->
                    <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-house-fill me-2"></i>Household Metadata</h5>
                    
                    <div class="row g-4 mb-4 pb-4 border-bottom">
                        <!-- Family Head Select -->
                        <div class="col-12 col-md-6">
                            <label for="head_resident_id" class="form-label fw-semibold small">Designated Family Head <span class="text-danger">*</span></label>
                            <select class="form-select select2-enable" id="head_resident_id" name="head_resident_id" required>
                                <option value="" selected disabled>Select Resident Head</option>
                                <?php foreach ($residents as $res): ?>
                                    <option value="<?= $res['id'] ?>" data-address="<?= escape($res['address']) ?>">
                                        <?= escape($res['last_name'] . ', ' . $res['first_name'] . ' (' . $res['resident_id'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Only residents registered in the system are listed.</small>
                            <div class="invalid-feedback">Please select a family head.</div>
                        </div>

                        <!-- Family Address -->
                        <div class="col-12 col-md-6">
                            <label for="address" class="form-label fw-semibold small">Family Address <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="address" name="address" rows="2" required placeholder="Will auto-fill from selected family head..."></textarea>
                            <div class="invalid-feedback">Family address is required.</div>
                        </div>
                    </div>

                    <!-- Section 1.5: Socio-Economic & Health Indicators -->
                    <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-heart-pulse-fill me-2"></i>Public Health & Socio-Economic Indicators</h5>
                    <div class="row g-3 mb-4 pb-4 border-bottom">
                        <!-- Occupation -->
                        <div class="col-12 col-md-4">
                            <label for="occupation" class="form-label fw-semibold small">Occupation (Head)</label>
                            <input type="text" class="form-control" id="occupation" name="occupation" placeholder="e.g. Farmer, Housewife">
                        </div>
                        <!-- Educational Attainment -->
                        <div class="col-12 col-md-4">
                            <label for="educational_attainment" class="form-label fw-semibold small">Educational Attainment</label>
                            <input type="text" class="form-control" id="educational_attainment" name="educational_attainment" placeholder="e.g. High School Graduate">
                        </div>
                        <!-- Pregnancy Status -->
                        <div class="col-12 col-md-4">
                            <label for="pregnancy_status" class="form-label fw-semibold small">Pregnancy Status</label>
                            <select class="form-select" id="pregnancy_status" name="pregnancy_status">
                                <option value="N/A" selected>N/A (Not Applicable)</option>
                                <option value="Pregnant">Pregnant</option>
                                <option value="Not Pregnant">Not Pregnant</option>
                            </select>
                        </div>
                        <!-- Family Planning Status -->
                        <div class="col-12 col-md-4">
                            <label for="family_planning_status" class="form-label fw-semibold small">Family Planning Method</label>
                            <input type="text" class="form-control" id="family_planning_status" name="family_planning_status" placeholder="e.g. Pill, Condom, None">
                        </div>
                        <!-- Child Feeding Type -->
                        <div class="col-12 col-md-4">
                            <label for="child_feeding_type" class="form-label fw-semibold small">Child Feeding Type</label>
                            <input type="text" class="form-control" id="child_feeding_type" name="child_feeding_type" placeholder="e.g. Breastfeeding, N/A">
                        </div>
                        <!-- Toilet Type -->
                        <div class="col-12 col-md-4">
                            <label for="toilet_type" class="form-label fw-semibold small">Toilet Facility Type</label>
                            <input type="text" class="form-control" id="toilet_type" name="toilet_type" placeholder="e.g. Water-sealed, Flush">
                        </div>
                        <!-- Water Source -->
                        <div class="col-12 col-md-6">
                            <label for="water_source" class="form-label fw-semibold small">Water Source</label>
                            <input type="text" class="form-control" id="water_source" name="water_source" placeholder="e.g. Piped water, Shared well">
                        </div>
                        <!-- Food Production Activity -->
                        <div class="col-12 col-md-6">
                            <label for="food_production_activity" class="form-label fw-semibold small">Food Production Activity</label>
                            <input type="text" class="form-control" id="food_production_activity" name="food_production_activity" placeholder="e.g. Backyard Gardening, Poultry">
                        </div>
                    </div>

                    <!-- Section 2: Household Members -->
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-people-fill me-2"></i>Household Members</h5>
                        <button type="button" id="add-member-btn" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-plus-lg me-1"></i> Add Member
                        </button>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table table-hover align-middle border rounded-3 overflow-hidden">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 55%;">Resident Member</th>
                                    <th style="width: 35%;">Relationship to Head</th>
                                    <th style="width: 10%;" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="members-tbody">
                                <!-- Dynamic rows go here -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="<?= url('index.php?route=family') ?>" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">Save Profile</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<!-- Template Row (Hidden) -->
<table class="d-none">
    <tbody id="member-row-template">
        <tr>
            <td>
                <select class="form-select member-select" name="member_resident_id[]" required disabled>
                    <option value="" selected disabled>Select Household Member</option>
                    <?php foreach ($residents as $res): ?>
                        <option value="<?= $res['id'] ?>">
                            <?= escape($res['last_name'] . ', ' . $res['first_name'] . ' (' . $res['resident_id'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">Please select a resident.</div>
            </td>
            <td>
                <input type="text" class="form-control" name="member_relationship[]" placeholder="e.g. Spouse, Child, Parent" required disabled>
                <div class="invalid-feedback">Please input relationship status.</div>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm remove-row-btn" title="Remove Member">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    </tbody>
</table>

<?php require_once LAYOUT_PATH . 'footer.php'; ?>

<script>
$(document).ready(function() {
    
    // Auto-fill address on head selection
    $('#head_resident_id').on('change', function() {
        const address = $(this).find(':selected').data('address');
        if (address) {
            $('#address').val(address);
        }
    });

    // Add Member Row Click
    $('#add-member-btn').on('click', function() {
        const clone = $('#member-row-template tr').clone();
        clone.find('select, input').removeAttr('disabled');
        $('#members-tbody').append(clone);
    });

    // Remove Member Row Click
    $(document).on('click', '.remove-row-btn', function() {
        $(this).closest('tr').remove();
    });

    // Handle initial template loading
    // Start with 1 empty row for convenience
    $('#add-member-btn').trigger('click');
});

// Bootstrap Form Validations
(function () {
  'use strict'
  var forms = document.querySelectorAll('.needs-validation')
  Array.prototype.slice.call(forms)
    .forEach(function (form) {
      form.addEventListener('submit', function (event) {
        // Enforce member selector constraints
        const headVal = $('#head_resident_id').val();
        let duplicates = false;
        
        const selectedMembers = [];
        $('.member-select').each(function() {
            const val = $(this).val();
            if (val) {
                if (val === headVal) {
                    duplicates = true;
                    $(this).addClass('is-invalid');
                    alert("A household head cannot be added as a member in the grid.");
                } else if (selectedMembers.includes(val)) {
                    duplicates = true;
                    $(this).addClass('is-invalid');
                    alert("Duplicate household members selected.");
                } else {
                    $(this).removeClass('is-invalid');
                    selectedMembers.push(val);
                }
            }
        });

        if (!form.checkValidity() || duplicates) {
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
