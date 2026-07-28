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
                <p class="text-muted small">Generate reports for medical checkups, symptoms, diagnoses, and treatments.</p>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-outline-secondary">
                    <i class="bi bi-printer-fill me-1"></i> Print Report
                </button>
                <a href="<?= url('index.php?route=reports/consultations&' . http_build_query(array_merge($_GET, ['export' => 'csv']))) ?>" class="btn btn-success">
                    <i class="bi bi-file-earmark-excel-fill me-1"></i> Export Excel/CSV
                </a>
            </div>
        </div>

        <!-- Print header -->
        <div class="d-none d-print-block text-center border-bottom pb-3 mb-4">
            <h4 class="fw-bold mb-0">Barangay Health Center</h4>
            <h5>Medical Consultations Report</h5>
            <p class="text-muted small">Generated on: <?= date('Y-m-d H:i:s') ?> • Date-filtered Logs</p>
        </div>

        <!-- Filter Card -->
        <div class="card border-0 shadow-sm mb-4 no-print">
            <div class="card-body p-4">
                <form method="GET" action="index.php">
                    <input type="hidden" name="route" value="reports/consultations">
                    
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

                        <!-- Status -->
                        <div class="col-12 col-md-4">
                            <label for="status" class="form-label fw-semibold small">Consultation Status</label>
                            <select class="form-select select-sm" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="Completed" <?= ($filters['status'] ?? '') === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="Pending" <?= ($filters['status'] ?? '') === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Cancelled" <?= ($filters['status'] ?? '') === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
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
                                <th>Consultation No.</th>
                                <th>Date</th>
                                <th>Patient Name</th>
                                <th>Symptoms</th>
                                <th>Diagnosis</th>
                                <th>Treatment Plan</th>
                                <th>Medicine Dispensed</th>
                                <th>Health Worker</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">No consultation records match the specified search parameters.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($data as $c): ?>
                                    <tr>
                                        <td class="fw-bold text-primary"><?= escape($c['consultation_no']) ?></td>
                                        <td><?= escape($c['consultation_date']) ?></td>
                                        <td>
                                            <div class="fw-semibold"><?= escape($c['resident_name']) ?></div>
                                            <small class="text-muted"><?= escape($c['resident_code']) ?></small>
                                        </td>
                                        <td class="small text-truncate" style="max-width: 150px;" title="<?= escape($c['symptoms']) ?>"><?= escape($c['symptoms']) ?></td>
                                        <td><span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle"><?= escape($c['diagnosis']) ?></span></td>
                                        <td class="small text-truncate" style="max-width: 150px;" title="<?= escape($c['treatment']) ?>"><?= escape($c['treatment']) ?></td>
                                        <td class="small">
                                            <?php if (!empty($c['medicine_name'])): ?>
                                                <?= escape($c['medicine_name']) ?> <span class="badge bg-secondary"><?= $c['medicine_qty'] ?> pcs</span>
                                            <?php else: ?>
                                                <span class="text-muted">None</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= escape($c['worker_name']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $c['status'] === 'Completed' ? 'success' : ($c['status'] === 'Pending' ? 'warning' : 'danger') ?> bg-opacity-10 text-<?= $c['status'] === 'Completed' ? 'success' : ($c['status'] === 'Pending' ? 'warning' : 'danger') ?> border border-<?= $c['status'] === 'Completed' ? 'success' : ($c['status'] === 'Pending' ? 'warning' : 'danger') ?>-subtle">
                                                <?= escape($c['status']) ?>
                                            </span>
                                        </td>
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
