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
            <p class="text-muted small mb-0">Manage medicine and family planning inventory dynamically.</p>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs border-bottom mb-4" id="medicineTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold text-secondary" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory-pane" type="button" role="tab" aria-controls="inventory-pane" aria-selected="true">
                    <i class="bi bi-boxes me-1"></i> Stocks Inventory
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-secondary" id="distributions-tab" data-bs-toggle="tab" data-bs-target="#distributions-pane" type="button" role="tab" aria-controls="distributions-pane" aria-selected="false">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Distribution Logs
                </button>
            </li>
        </ul>

        <div class="tab-content" id="medicineTabsContent">
            <!-- Tab 1: Stocks Inventory -->
            <div class="tab-pane fade show active" id="inventory-pane" role="tabpanel" aria-labelledby="inventory-tab">
                <!-- Real-time Search Box and Action Buttons -->
                <div class="row align-items-center mb-4 g-3">
                    <div class="col-12 col-md-8 col-lg-5">
                        <div class="input-group shadow-sm border rounded bg-white">
                            <span class="input-group-text bg-white border-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" id="medicineSearch" class="form-control border-0" placeholder="Type to search medicines...">
                            <button class="btn btn-light border-start dropdown-toggle text-secondary fw-semibold px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                                <i class="bi bi-funnel"></i> Filters
                            </button>
                            <div class="dropdown-menu dropdown-menu-end p-3 shadow border-0 rounded-3 mt-1" style="width: 260px;">
                                <h6 class="dropdown-header px-0 text-dark fw-bold mb-2">Filter Inventory</h6>
                                <div class="mb-3">
                                    <label class="form-label small text-muted fw-semibold">Category</label>
                                    <select id="medicineCategoryFilter" class="form-select form-select-sm shadow-none">
                                        <option value="">All Categories</option>
                                        <option value="Medicine">Medicine</option>
                                        <option value="Vaccine">Vaccine</option>
                                        <option value="Family Planning">Family Planning</option>
                                        <option value="Supply">Supply</option>
                                    </select>
                                </div>
                                <div class="mb-1">
                                    <label class="form-label small text-muted fw-semibold">Status</label>
                                    <select id="medicineStatusFilter" class="form-select form-select-sm shadow-none">
                                        <option value="">All Statuses</option>
                                        <option value="Good Stock">Good Stock</option>
                                        <option value="Low Stock">Low Stock</option>
                                        <option value="Out of Stock">Out of Stock</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 col-lg-7 d-flex justify-content-md-end gap-2">
                        <button type="button" class="btn btn-primary" onclick="newMedicine()">
                            <i class="bi bi-plus-lg me-1"></i> Add Inventory Item
                        </button>
                    </div>
                </div>

                <!-- Dynamic Inventory Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item Code</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Stock Qty</th>
                                        <th class="text-center">Reorder Level</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="medicineTable">
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <div class="text-muted mt-2 small">Fetching inventory from API...</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Distribution Logs -->
            <div class="tab-pane fade" id="distributions-pane" role="tabpanel" aria-labelledby="distributions-tab">
                <!-- Real-time Search Box and Log Distribution Button -->
                <div class="row align-items-center mb-4 g-3">
                    <div class="col-12 col-md-8 col-lg-5">
                        <div class="input-group shadow-sm border rounded bg-white">
                            <span class="input-group-text bg-white border-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" id="distributionSearch" class="form-control border-0" placeholder="Type to search distributions...">
                            <button class="btn btn-light border-start dropdown-toggle text-secondary fw-semibold px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                                <i class="bi bi-funnel"></i> Filters
                            </button>
                            <div class="dropdown-menu dropdown-menu-end p-3 shadow border-0 rounded-3 mt-1" style="width: 260px;">
                                <h6 class="dropdown-header px-0 text-dark fw-bold mb-2">Filter Logs</h6>
                                <div class="mb-1">
                                    <label class="form-label small text-muted fw-semibold">Category</label>
                                    <select id="distributionCategoryFilter" class="form-select form-select-sm shadow-none">
                                        <option value="">All Categories</option>
                                        <option value="Medicine">Medicine</option>
                                        <option value="Vaccine">Vaccine</option>
                                        <option value="Family Planning">Family Planning</option>
                                        <option value="Supply">Supply</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 col-lg-7 d-flex justify-content-md-end gap-2">
                        <button type="button" class="btn btn-success" onclick="newDistribution()">
                            <i class="bi bi-plus-lg me-1"></i> Log Distribution
                        </button>
                    </div>
                </div>

                <!-- Distributions Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Item Name</th>
                                        <th>Category</th>
                                        <th>Recipient Resident</th>
                                        <th>Quantity</th>
                                        <th>Health Worker</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="distributionTable">
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="spinner-border text-success" role="status" style="width: 2.5rem; height: 2.5rem;">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <div class="text-muted mt-2 small">Fetching distributions...</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Add/Edit Medicine Modal -->
<div class="modal fade" id="medicineModal" tabindex="-1" aria-labelledby="medicineModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary bg-gradient text-white">
                <h5 class="modal-title fw-bold" id="medicineModalLabel">
                    <i class="bi bi-box-seam-fill me-2"></i><span id="modalTitleText">Add Inventory Item</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="medicineForm" class="needs-validation" novalidate>
                <input type="hidden" id="medicineId" name="id">
                
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Item Name -->
                        <div class="col-12 col-md-6">
                            <label for="medName" class="form-label fw-semibold small">Item Name (Generic/Brand) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="medName" name="name" required placeholder="e.g. Paracetamol (Biogesic)">
                            <div class="invalid-feedback">Item name is required.</div>
                        </div>

                        <!-- Category -->
                        <div class="col-12 col-md-6">
                            <label for="medCategory" class="form-label fw-semibold small">Inventory Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="medCategory" name="category" required>
                                <option value="" selected disabled>Select Category</option>
                                <option value="Medicine">Medicine (Tablets/Syrup)</option>
                                <option value="Vaccine">Vaccine (Immunization)</option>
                                <option value="Family Planning">Family Planning (Pills/Condoms/Implants)</option>
                                <option value="Supply">Supply (Bandage/Syringes)</option>
                            </select>
                            <div class="invalid-feedback">Please select a category.</div>
                        </div>

                        <!-- Initial Stock -->
                        <div class="col-12 col-md-6" id="stockQtyContainer">
                            <label for="medStockQty" class="form-label fw-semibold small">Initial Stock Qty <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="medStockQty" name="stock_qty" min="0" value="0" required>
                            <div class="invalid-feedback">Stock quantity is required and must be 0 or greater.</div>
                        </div>

                        <!-- Reorder Level -->
                        <div class="col-12 col-md-6">
                            <label for="medReorderLevel" class="form-label fw-semibold small">Reorder Threshold Limit <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="medReorderLevel" name="reorder_level" min="1" value="10" required>
                            <div class="invalid-feedback">Reorder level is required and must be 1 or greater.</div>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label for="medDescription" class="form-label fw-semibold small">Description</label>
                            <textarea class="form-control" id="medDescription" name="description" rows="3" placeholder="e.g. Dosages, location details..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4" id="saveButton">Save Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Restock Item Modal -->
<div class="modal fade" id="restockModal" tabindex="-1" aria-labelledby="restockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success bg-gradient text-white">
                <h5 class="modal-title fw-bold" id="restockModalLabel">
                    <i class="bi bi-plus-circle-fill me-2"></i>Restock Inventory Item
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="restockForm" class="needs-validation" novalidate>
                <input type="hidden" id="restockMedicineId" name="medicine_id">
                
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <span class="d-block text-muted small">Restocking Item:</span>
                        <strong class="d-block text-dark fs-5" id="restockItemName"></strong>
                        <span class="badge bg-light text-muted border mt-1" id="restockItemCode"></span>
                    </div>

                    <div class="mb-3">
                        <label for="restockQty" class="form-label fw-semibold small">Quantity to Add <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="restockQty" name="restock_qty" min="1" required placeholder="Enter amount to add">
                        <div class="invalid-feedback">Please enter valid quantity (minimum 1).</div>
                    </div>
                </div>

                <div class="modal-footer border-0 p-3 bg-light">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm px-4" id="restockSaveButton">Add Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Log Distribution Modal -->
<div class="modal fade" id="distributionModal" tabindex="-1" aria-labelledby="distributionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success bg-gradient text-white">
                <h5 class="modal-title fw-bold" id="distributionModalLabel">
                    <i class="bi bi-box-arrow-up-right me-2"></i><span id="distModalTitleText">Log Distribution / Dispensing</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="distributionForm" class="needs-validation" novalidate>
                <input type="hidden" id="distributionId" name="id">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Select Resident -->
                        <div class="col-12">
                            <label for="distResidentId" class="form-label fw-semibold small">Select Resident (Recipient) <span class="text-danger">*</span></label>
                            <select class="form-select searchable-select" id="distResidentId" name="resident_id" required>
                                <option value="" selected disabled>Choose Resident...</option>
                                <!-- Will be loaded dynamically via JS based on category -->
                            </select>
                            <div class="invalid-feedback">Please select a resident.</div>
                        </div>

                        <!-- Select Item -->
                        <div class="col-12">
                            <label for="distMedicineId" class="form-label fw-semibold small">Select Item to Dispense <span class="text-danger">*</span></label>
                            <select class="form-select" id="distMedicineId" name="medicine_id" required>
                                <option value="" selected disabled>Choose Item...</option>
                                <!-- Will be loaded dynamically via JS -->
                            </select>
                            <div class="invalid-feedback">Please select an item.</div>
                        </div>

                        <!-- Quantity -->
                        <div class="col-12 col-md-6">
                            <label for="distQuantity" class="form-label fw-semibold small">Quantity to Dispense <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="distQuantity" name="quantity" min="1" required placeholder="e.g. 10">
                            <small class="text-muted" id="distStockHint">Select an item to see stock.</small>
                            <div class="invalid-feedback" id="distQtyFeedback">Please enter a valid quantity.</div>
                        </div>

                        <!-- Date -->
                        <div class="col-12 col-md-6">
                            <label for="distDate" class="form-label fw-semibold small">Distribution Date <span class="text-danger">*</span></label>
                            <input type="text" class="form-control datepicker" id="distDate" name="distribution_date" value="<?= date('Y-m-d') ?>" required>
                            <div class="invalid-feedback">Date is required.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm px-4" id="saveDistButton">Log Dispensing</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- View Distribution Details Modal -->
<div class="modal fade" id="viewDistributionModal" tabindex="-1" aria-labelledby="viewDistributionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-success bg-gradient text-white">
                <h5 class="modal-title fw-bold" id="viewDistributionModalLabel">
                    <i class="bi bi-eye-fill me-2"></i>Distribution Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="viewDistributionBody">
                <!-- Dynamically loaded by JavaScript -->
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary px-4 btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Pass residents data to JS for dynamic filtering
    const allResidents = <?= json_encode(array_map(function($r) {
        return [
            'id' => $r['id'],
            'name' => $r['last_name'] . ', ' . $r['first_name'] . ' ' . ($r['middle_name'] ? substr($r['middle_name'], 0, 1) . '.' : ''),
            'gender' => $r['gender'],
            'age' => $r['age'],
            'resident_id' => $r['resident_id']
        ];
    }, $residents)) ?>;
</script>

<?php 
// Pass custom script URL to footer layout script loader
$pageScript = url('js/medicine_js.js');
require_once LAYOUT_PATH . 'footer.php'; 
?>
