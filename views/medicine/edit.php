<?php
require_once LAYOUT_PATH . 'header.php';
require_once LAYOUT_PATH . 'sidebar.php';
?>

<div class="main-content-wrapper">
    <?php require_once LAYOUT_PATH . 'navbar.php'; ?>
    
    <div class="container-fluid p-3 p-md-4">
        
        <!-- Header -->
        <div class="mb-4">
            <a href="<?= url('index.php?route=medicine') ?>" class="text-decoration-none small">
                <i class="bi bi-arrow-left"></i> Back to Inventory
            </a>
            <h3 class="fw-bold mt-2 mb-1"><?= escape($pageTitle) ?></h3>
            <p class="text-muted small">Update profile details and reorder thresholds for inventory item <strong><?= escape($medicine['name']) ?></strong>.</p>
        </div>

        <!-- Form Card -->
        <div class="card border-0 shadow-sm" style="max-width: 600px;">
            <div class="card-body p-4 p-md-5">
                <form action="<?= url('index.php?route=medicine/update') ?>" method="POST" class="needs-validation" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $medicine['id'] ?>">

                    <div class="row g-3 mb-4">
                        <!-- Item Name -->
                        <div class="col-12">
                            <label for="name" class="form-label fw-semibold small">Item Name (Generic/Brand) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="<?= escape($medicine['name']) ?>" required>
                            <div class="invalid-feedback">Item name is required.</div>
                        </div>

                        <!-- Category -->
                        <div class="col-12">
                            <label for="category" class="form-label fw-semibold small">Inventory Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="Medicine" <?= $medicine['category'] === 'Medicine' ? 'selected' : '' ?>>Medicine (Tablets/Syrup)</option>
                                <option value="Vaccine" <?= $medicine['category'] === 'Vaccine' ? 'selected' : '' ?>>Vaccine (Immunization)</option>
                                <option value="Supply" <?= $medicine['category'] === 'Supply' ? 'selected' : '' ?>>Supply (Bandage/Syringes/PPEs)</option>
                            </select>
                            <div class="invalid-feedback">Please select a category.</div>
                        </div>

                        <!-- Current Stock -->
                        <div class="col-12 col-md-6">
                            <label for="stock_qty" class="form-label fw-semibold small">Current Stock Qty <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="stock_qty" name="stock_qty" min="0" value="<?= $medicine['stock_qty'] ?>" required>
                            <div class="invalid-feedback">Stock quantity is required.</div>
                        </div>

                        <!-- Reorder Level -->
                        <div class="col-12 col-md-6">
                            <label for="reorder_level" class="form-label fw-semibold small">Reorder Threshold Limit <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="reorder_level" name="reorder_level" min="1" value="<?= $medicine['reorder_level'] ?>" required>
                            <div class="invalid-feedback">Reorder level is required.</div>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label for="description" class="form-label fw-semibold small">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?= escape($medicine['description']) ?></textarea>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="<?= url('index.php?route=medicine') ?>" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">Update Item</button>
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
