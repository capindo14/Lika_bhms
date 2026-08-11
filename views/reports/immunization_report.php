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
                <a href="<?= url('index.php?route=reports') ?>" class="btn btn-sm btn-outline-primary px-3 py-1 mb-2 rounded-pill d-inline-flex align-items-center gap-1">
                    <i class="bi bi-arrow-left"></i> Reports Center
                </a>
                <h3 class="fw-bold mt-2 mb-1"><?= escape($pageTitle) ?></h3>
                <p class="text-muted small">Generate immunization logs, appointment schedules, and child vaccination targets.</p>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-outline-secondary">
                    <i class="bi bi-printer-fill me-1"></i> Print Report
                </button>
            </div>
        </div>

        <!-- Print header -->
        <div class="d-none d-print-block text-center border-bottom pb-3 mb-4">
            <h4 class="fw-bold mb-0">Barangay Health Center</h4>
            <h5>Immunization Schedules Report</h5>
            <p class="text-muted small">Generated on: <?= date('Y-m-d H:i:s') ?> • Date-filtered Logs</p>
        </div>

        <!-- Filter Card -->
        <div class="card border-0 shadow-sm mb-4 no-print">
            <div class="card-body p-4">
                <form method="GET" action="index.php">
                    <input type="hidden" name="route" value="reports/immunizations">
                    
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

                        <!-- Vaccine Dropdown -->
                        <div class="col-12 col-md-3">
                            <label for="vaccine_id" class="form-label fw-semibold small">Select Vaccine</label>
                            <select class="form-select select-sm" id="vaccine_id" name="vaccine_id">
                                <option value="">All Vaccines</option>
                                <?php foreach ($vaccines as $vac): ?>
                                    <option value="<?= $vac['id'] ?>" <?= ($filters['vaccine_id'] ?? '') == $vac['id'] ? 'selected' : '' ?>><?= escape($vac['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="col-12 col-md-2">
                            <label for="status" class="form-label fw-semibold small">Status</label>
                            <select class="form-select select-sm" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="Completed" <?= ($filters['status'] ?? '') === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="Upcoming" <?= ($filters['status'] ?? '') === 'Upcoming' ? 'selected' : '' ?>>Upcoming</option>
                                <option value="Missed" <?= ($filters['status'] ?? '') === 'Missed' ? 'selected' : '' ?>>Missed</option>
                            </select>
                        </div>

                        <!-- Filter Button -->
                        <div class="col-12 col-md-1">
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i></button>
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
                                <th>Patient/Child</th>
                                <th>Patient ID</th>
                                <th>Vaccine</th>
                                <th>Dose Level</th>
                                <th>Date Given</th>
                                <th>Next Appointment</th>
                                <th>Status</th>
                                <th>Health Worker</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No immunization schedules match the specified search parameters.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($data as $i): ?>
                                    <tr>
                                        <td class="fw-bold"><?= escape($i['resident_name']) ?></td>
                                        <td class="fw-semibold text-primary"><?= escape($i['resident_code']) ?></td>
                                        <td><span class="badge bg-info bg-opacity-10 text-info border border-info-subtle"><i class="bi bi-shield-fill-check me-1"></i><?= escape($i['vaccine_name']) ?></span></td>
                                        <td><?= escape($i['dose']) ?></td>
                                        <td><?= $i['date_given'] ? date('Y-m-d', strtotime($i['date_given'])) : '<span class="text-muted small">Not Given</span>' ?></td>
                                        <td class="fw-medium text-danger"><?= $i['next_schedule'] ? date('Y-m-d', strtotime($i['next_schedule'])) : '<span class="text-muted small">None</span>' ?></td>
                                        <td>
                                            <span class="badge bg-<?= $i['status'] === 'Completed' ? 'success' : ($i['status'] === 'Upcoming' ? 'warning' : 'danger') ?> bg-opacity-10 text-<?= $i['status'] === 'Completed' ? 'success' : ($i['status'] === 'Upcoming' ? 'warning' : 'danger') ?> border border-<?= $i['status'] === 'Completed' ? 'success' : ($i['status'] === 'Upcoming' ? 'warning' : 'danger') ?>-subtle">
                                                <?= escape($i['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= escape($i['worker_name']) ?></td>
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
