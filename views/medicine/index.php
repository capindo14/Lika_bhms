<?php
require_once LAYOUT_PATH . 'header.php';
require_once LAYOUT_PATH . 'sidebar.php';
?>

<div class="main-content-wrapper">
    <?php require_once LAYOUT_PATH . 'navbar.php'; ?>
    
    <div class="container-fluid p-3 p-md-4">
        
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold mb-1"><?= escape($pageTitle) ?></h3>
                <p class="text-muted small mb-0">Monitor active pharmaceutical inventory, vaccines, medical supplies, and distribution logs.</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMedicineModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Inventory Item
                </button>
            </div>
        </div>

        <!-- Low Stock Alerts Panel -->
        <?php if (!empty($lowStockAlerts)): ?>
            <div class="card border-0 shadow-sm mb-4 border-start border-4 border-warning">
                <div class="card-header bg-transparent border-0 pt-3 px-4">
                    <h6 class="fw-bold text-warning mb-0"><i class="bi bi-exclamation-triangle-fill me-2"></i>Reorder Alerts (Low Stock)</h6>
                </div>
                <div class="card-body px-4 pb-3 pt-1">
                    <div class="row g-2">
                        <?php foreach ($lowStockAlerts as $item): ?>
                            <div class="col-12 col-md-4">
                                <div class="p-2 border rounded bg-light d-flex align-items-center justify-content-between">
                                    <div>
                                        <span class="fw-bold small d-block"><?= escape($item['name']) ?></span>
                                        <small class="text-muted">Code: <?= escape($item['code']) ?> • Reorder Limit: <?= $item['reorder_level'] ?></small>
                                    </div>
                                    <span class="badge bg-danger px-2.5 py-1.5 rounded-pill"><?= $item['stock_qty'] ?> left</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Inventory List and Distributions tabs -->
        <ul class="nav nav-tabs border-bottom mb-4" id="inventoryTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold text-secondary" id="stock-tab" data-bs-toggle="tab" data-bs-target="#stock-view" type="button" role="tab" aria-controls="stock-view" aria-selected="true">
                    <i class="bi bi-box-seam me-1"></i> Current Stock Inventory
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-secondary" id="dist-tab" data-bs-toggle="tab" data-bs-target="#dist-view" type="button" role="tab" aria-controls="dist-view" aria-selected="false">
                    <i class="bi bi-clock-history me-1"></i> Allocation History
                </button>
            </li>
        </ul>

        <div class="tab-content" id="inventoryTabsContent">
            <!-- TAB 1: Stock Inventory -->
            <div class="tab-pane fade show active" id="stock-view" role="tabpanel" aria-labelledby="stock-tab">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table id="medicines-table" class="table table-hover align-middle w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item Code</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Stock Qty</th>
                                        <th>Reorder Level</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($medicines as $med): 
                                        $isLow = $med['stock_qty'] <= $med['reorder_level'];
                                    ?>
                                        <tr>
                                            <td class="fw-bold text-primary"><?= escape($med['code']) ?></td>
                                            <td>
                                                <div class="fw-semibold"><?= escape($med['name']) ?></div>
                                                <small class="text-muted text-truncate d-block" style="max-width: 250px;"><?= escape($med['description'] ?: 'No description.') ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle">
                                                    <?php if ($med['category'] === 'Medicine'): ?>
                                                        <i class="bi bi-capsule me-1 text-primary"></i>
                                                    <?php elseif ($med['category'] === 'Vaccine'): ?>
                                                        <i class="bi bi-shield-plus me-1 text-success"></i>
                                                    <?php else: ?>
                                                        <i class="bi bi-box me-1 text-secondary"></i>
                                                    <?php endif; ?>
                                                    <?= escape($med['category']) ?>
                                                </span>
                                            </td>
                                            <td class="fw-semibold <?= $isLow ? 'text-danger' : '' ?>">
                                                <?= number_format($med['stock_qty']) ?>
                                            </td>
                                            <td><?= number_format($med['reorder_level']) ?></td>
                                            <td class="text-center">
                                                <?php if ($med['stock_qty'] === 0): ?>
                                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle">Out of Stock</span>
                                                <?php elseif ($isLow): ?>
                                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle">Low Stock</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle">Good Stock</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group gap-1">
                                                    <button onclick="triggerRestock(<?= $med['id'] ?>, '<?= escape($med['name']) ?>', '<?= escape($med['code']) ?>')" class="btn btn-outline-success btn-sm rounded-2" title="Restock Item">
                                                        <i class="bi bi-plus-circle"></i> Restock
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-2" onclick="editMedicineItem(<?= $med['id'] ?>)" title="Edit Item">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button onclick="confirmDelete(<?= $med['id'] ?>, '<?= escape($med['name']) ?>')" class="btn btn-outline-danger btn-sm rounded-2" title="Delete Item">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: Allocation History -->
            <div class="tab-pane fade" id="dist-view" role="tabpanel" aria-labelledby="dist-tab">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table id="distributions-table" class="table table-hover align-middle w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Item Allocated</th>
                                        <th>Recipient</th>
                                        <th>Quantity</th>
                                        <th>Health Worker</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($distributions as $d): ?>
                                        <tr>
                                            <td class="small fw-semibold"><?= date('Y-m-d', strtotime($d['distribution_date'])) ?></td>
                                            <td>
                                                <span class="fw-semibold"><?= escape($d['medicine_name']) ?></span>
                                                <small class="text-muted d-block"><?= escape($d['medicine_code']) ?></small>
                                            </td>
                                            <td><?= escape($d['resident_name']) ?></td>
                                            <td class="fw-bold text-success"><?= $d['quantity'] ?> pcs</td>
                                            <td><?= escape($d['worker_name']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Add Inventory Item Modal -->
<div class="modal fade" id="addMedicineModal" tabindex="-1" aria-labelledby="addMedicineModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary bg-gradient text-white">
                <h5 class="modal-title fw-bold" id="addMedicineModalLabel">
                    <i class="bi bi-box-seam-fill me-2"></i>Add Inventory Item
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('index.php?route=medicine/store') ?>" method="POST" class="needs-validation" novalidate id="modal-medicine-form">
                <div class="modal-body p-4">
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <!-- Item Name -->
                        <div class="col-12 col-md-6">
                            <label for="modal_med_name" class="form-label fw-semibold small">Item Name (Generic/Brand) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modal_med_name" name="name" required placeholder="e.g. Paracetamol (Biogesic), BCG Vaccine">
                            <div class="invalid-feedback">Item name is required.</div>
                        </div>

                        <!-- Category -->
                        <div class="col-12 col-md-6">
                            <label for="modal_med_category" class="form-label fw-semibold small">Inventory Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="modal_med_category" name="category" required>
                                <option value="" selected disabled>Select Category</option>
                                <option value="Medicine">Medicine (Tablets/Syrup)</option>
                                <option value="Vaccine">Vaccine (Immunization)</option>
                                <option value="Supply">Supply (Bandage/Syringes/PPEs)</option>
                            </select>
                            <div class="invalid-feedback">Please select a category.</div>
                        </div>

                        <!-- Initial Stock -->
                        <div class="col-12 col-md-6">
                            <label for="modal_med_stock_qty" class="form-label fw-semibold small">Initial Stock Qty <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="modal_med_stock_qty" name="stock_qty" min="0" value="0" required>
                            <div class="invalid-feedback">Initial stock quantity is required.</div>
                        </div>

                        <!-- Reorder Level -->
                        <div class="col-12 col-md-6">
                            <label for="modal_med_reorder_level" class="form-label fw-semibold small">Reorder Threshold Limit <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="modal_med_reorder_level" name="reorder_level" min="1" value="10" required>
                            <small class="text-muted d-block mt-1">A warning is shown when stock falls below this level.</small>
                            <div class="invalid-feedback">Reorder level is required.</div>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label for="modal_med_description" class="form-label fw-semibold small">Description</label>
                            <textarea class="form-control" id="modal_med_description" name="description" rows="2" placeholder="e.g. Dosages, chemical composition, shelf location..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Create Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Restock Modal Form -->
<div class="modal fade" id="restockModal" tabindex="-1" aria-labelledby="restockModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 400px;">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-success bg-gradient text-white">
                <h5 class="modal-title fw-bold" id="restockModalTitle"><i class="bi bi-box-seam me-2"></i>Restock Item</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('index.php?route=medicine/restock') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" id="restock-medicine-id" name="medicine_id">
                
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <span class="d-block text-muted small">Restocking Item:</span>
                        <strong class="d-block text-dark fs-5" id="restock-item-name"></strong>
                        <span class="badge bg-light text-muted border mt-1" id="restock-item-code"></span>
                    </div>

                    <div class="mb-3">
                        <label for="restock_qty" class="form-label fw-semibold small">Quantity to Add <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="restock_qty" name="restock_qty" min="1" required placeholder="Enter amount to add">
                        <div class="invalid-feedback">Please enter valid quantity.</div>
                    </div>
                </div>

                <div class="modal-footer border-0 p-3 bg-light">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">Add Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Inventory Item Modal -->
<div class="modal fade" id="editMedicineModal" tabindex="-1" aria-labelledby="editMedicineModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary bg-gradient text-white">
                <h5 class="modal-title fw-bold" id="editMedicineModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>Edit Inventory Item Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('index.php?route=medicine/update') ?>" method="POST" class="needs-validation" novalidate id="edit-medicine-form">
                <div class="modal-body p-4">
                    <?= csrf_field() ?>
                    <input type="hidden" id="edit_med_id" name="id">

                    <div class="row g-3">
                        <!-- Item Name -->
                        <div class="col-12 col-md-6">
                            <label for="edit_med_name" class="form-label fw-semibold small">Item Name (Generic/Brand) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_med_name" name="name" required placeholder="e.g. Paracetamol (Biogesic), BCG Vaccine">
                            <div class="invalid-feedback">Item name is required.</div>
                        </div>

                        <!-- Category -->
                        <div class="col-12 col-md-6">
                            <label for="edit_med_category" class="form-label fw-semibold small">Inventory Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_med_category" name="category" required>
                                <option value="" disabled>Select Category</option>
                                <option value="Medicine">Medicine (Tablets/Syrup)</option>
                                <option value="Vaccine">Vaccine (Immunization)</option>
                                <option value="Supply">Supply (Bandage/Syringes/PPEs)</option>
                            </select>
                            <div class="invalid-feedback">Please select a category.</div>
                        </div>

                        <!-- Stock Qty -->
                        <div class="col-12 col-md-6">
                            <label for="edit_med_stock_qty" class="form-label fw-semibold small">Current Stock Qty <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit_med_stock_qty" name="stock_qty" min="0" required>
                            <div class="invalid-feedback">Stock quantity is required.</div>
                        </div>

                        <!-- Reorder Level -->
                        <div class="col-12 col-md-6">
                            <label for="edit_med_reorder_level" class="form-label fw-semibold small">Reorder Threshold Limit <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit_med_reorder_level" name="reorder_level" min="1" required>
                            <small class="text-muted d-block mt-1">A warning is shown when stock falls below this level.</small>
                            <div class="invalid-feedback">Reorder level is required.</div>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label for="edit_med_description" class="form-label fw-semibold small">Description</label>
                            <textarea class="form-control" id="edit_med_description" name="description" rows="2" placeholder="e.g. Dosages, chemical composition, shelf location..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Update Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once LAYOUT_PATH . 'footer.php'; ?>

<script>
$(document).ready(function() {
    $('#medicines-table').DataTable({
        responsive: true,
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: 6 }
        ]
    });

    $('#distributions-table').DataTable({
        responsive: true,
        order: [[0, 'desc']]
    });
});

function editMedicineItem(id) {
    App.showLoader();
    $.getJSON(`index.php?route=medicine/detail_json&id=${id}`)
        .done(function(res) {
            App.hideLoader();
            if (res.success) {
                const med = res.data;
                $('#edit_med_id').val(med.id);
                $('#edit_med_name').val(med.name);
                $('#edit_med_category').val(med.category);
                $('#edit_med_stock_qty').val(med.stock_qty);
                $('#edit_med_reorder_level').val(med.reorder_level);
                $('#edit_med_description').val(med.description || '');

                const modal = new bootstrap.Modal(document.getElementById('editMedicineModal'));
                modal.show();
            } else {
                Swal.fire('Error', res.message || 'Unable to fetch inventory item details.', 'error');
            }
        })
        .fail(function() {
            App.hideLoader();
            Swal.fire('Error', 'Communication error happened.', 'error');
        });
}

function triggerRestock(id, name, code) {
    $('#restock-medicine-id').val(id);
    $('#restock-item-name').text(name);
    $('#restock-item-code').text(code);
    $('#restock_qty').val('');
    
    const modal = new bootstrap.Modal(document.getElementById('restockModal'));
    modal.show();
}

function confirmDelete(id, name) {
    Swal.fire({
        title: 'Delete Inventory Item?',
        text: `Are you sure you want to delete ${name}? This will remove it from search options.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Delete'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `index.php?route=medicine/delete&id=${id}`;
        }
    });
}

// Bootstrap Form Validations for Medicine Modals
(function () {
  'use strict'
  var forms = document.querySelectorAll('#modal-medicine-form, #edit-medicine-form')
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
