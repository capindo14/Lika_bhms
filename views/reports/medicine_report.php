<?php
require_once LAYOUT_PATH . 'header.php';
require_once LAYOUT_PATH . 'sidebar.php';
?>

<style>
@media print {
    body { background: white; color: black; }
    .sidebar-wrapper, .navbar-top, .no-print, .btn, form, footer { display: none !important; }
    .main-content-wrapper { margin-left: 0 !important; padding-top: 0 !important; }
    .card { border: none !important; box-shadow: none !important; }
}
</style>

<div class="main-content-wrapper">
    <?php require_once LAYOUT_PATH . 'navbar.php'; ?>
    
    <div class="container-fluid p-3 p-md-4">
        
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-4 no-print">
            <div>
                <a href="<?= url('index.php?route=reports') ?>" class="text-decoration-none small">
                    <i class="bi bi-arrow-left"></i> Reports Center
                </a>
                <h3 class="fw-bold mt-2 mb-1"><?= escape($pageTitle) ?></h3>
                <p class="text-muted small">Generate reports for distributed medicine allocations and center supplies.</p>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-outline-secondary">
                    <i class="bi bi-printer-fill me-1"></i> Print Report
                </button>
                <a href="<?= url('index.php?route=reports/medicine&' . http_build_query(array_merge($_GET, ['export' => 'csv']))) ?>" class="btn btn-success">
                    <i class="bi bi-file-earmark-excel-fill me-1"></i> Export Excel/CSV
                </a>
            </div>
        </div>

        <!-- Print header -->
        <div class="d-none d-print-block text-center border-bottom pb-3 mb-4">
            <h4 class="fw-bold mb-0">Barangay Health Center</h4>
            <h5>Medicine Distribution Report</h5>
            <p class="text-muted small">Generated on: <?= date('Y-m-d H:i:s') ?> • Date-filtered Logs</p>
        </div>

        <!-- Filter Card -->
        <div class="card border-0 shadow-sm mb-4 no-print">
            <div class="card-body p-4">
                <form method="GET" action="index.php">
                    <input type="hidden" name="route" value="reports/medicine">
                    
                    <div class="row g-3 align-items-end">
                        <!-- Start Date -->
                        <div class="col-12 col-md-3">
                            <label for="start_date" class="form-label fw-semibold small">Start Date</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-calendar-event"></i></span>
                                <input type="text" class="form-control datepicker" id="start_date" name="start_date" value="<?= escape($filters['start_date'] ?? '') ?>" placeholder="YYYY-MM-DD">
                            </div>
                        </div>

                        <!-- End Date -->
                        <div class="col-12 col-md-3">
                            <label for="end_date" class="form-label fw-semibold small">End Date</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-calendar-event"></i></span>
                                <input type="text" class="form-control datepicker" id="end_date" name="end_date" value="<?= escape($filters['end_date'] ?? '') ?>" placeholder="YYYY-MM-DD">
                            </div>
                        </div>

                        <!-- Medicine Dropdown -->
                        <div class="col-12 col-md-4">
                            <label for="medicine_id" class="form-label fw-semibold small">Select Medicine / Supply</label>
                            <select class="form-select select-sm" id="medicine_id" name="medicine_id">
                                <option value="">All Medicines / Supplies</option>
                                <?php foreach ($medicinesList as $med): ?>
                                    <option value="<?= $med['id'] ?>" <?= ($filters['medicine_id'] ?? '') == $med['id'] ? 'selected' : '' ?>><?= escape($med['name'] . ' (' . $med['code'] . ')') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Filter Button -->
                        <div class="col-12 col-md-2">
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Apply Filter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Allocation Date</th>
                                <th>Item Name</th>
                                <th>Code</th>
                                <th>Category</th>
                                <th>Patient Recipient</th>
                                <th>Patient ID</th>
                                <th>Quantity Dispensed</th>
                                <th>Health Worker</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No distribution records match the specified search parameters.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($data as $md): ?>
                                    <tr>
                                        <td class="small fw-semibold"><?= date('Y-m-d', strtotime($md['distribution_date'])) ?></td>
                                        <td class="fw-bold"><?= escape($md['medicine_name']) ?></td>
                                        <td class="fw-semibold text-primary"><?= escape($md['medicine_code']) ?></td>
                                        <td>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle">
                                                <?= escape($md['medicine_category']) ?>
                                            </span>
                                        </td>
                                        <td><?= escape($md['resident_name']) ?></td>
                                        <td class="small"><?= escape($md['resident_code']) ?></td>
                                        <td class="fw-bold text-success"><?= $md['quantity'] ?> pcs</td>
                                        <td><?= escape($md['worker_name']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once LAYOUT_PATH . 'footer.php'; ?>
