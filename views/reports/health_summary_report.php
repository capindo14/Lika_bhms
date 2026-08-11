<?php
require_once LAYOUT_PATH . 'header.php';
require_once LAYOUT_PATH . 'sidebar.php';
?>

<div class="main-content-wrapper">
    <?php require_once LAYOUT_PATH . 'navbar.php'; ?>
    
    <div class="container-fluid p-3 p-md-4">
        
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <a href="<?= url('index.php?route=reports') ?>" class="btn btn-sm btn-outline-primary px-3 py-1 mb-2 rounded-pill d-inline-flex align-items-center gap-1">
                    <i class="bi bi-arrow-left"></i> Reports Center
                </a>
                <h3 class="fw-bold mb-1 text-dark"><?= escape($pageTitle) ?></h3>
                <p class="text-muted small mb-0">Overview of key healthcare indicators, consultations, immunizations, and medicine allocations.</p>
            </div>
            
            <div class="d-flex gap-2 d-print-none">
                <button onclick="window.print()" class="btn btn-outline-secondary">
                    <i class="bi bi-printer-fill me-1"></i> Print Report
                </button>
            </div>
        </div>

        <!-- Filter Card / Generate Report Form -->
        <div class="card border-0 shadow-sm mb-4 d-print-none">
            <div class="card-body p-3 p-md-4">
                <form action="<?= url('index.php?route=reports/health_summary') ?>" method="GET" class="row g-3 align-items-end">
                    <input type="hidden" name="route" value="reports/health_summary">

                    <!-- Start Date -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <label for="start_date" class="form-label fw-semibold small">Start Date</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="bi bi-calendar-event"></i></span>
                            <input type="text" class="form-control datepicker" id="start_date" name="start_date" value="<?= escape($filters['start_date']) ?>" placeholder="YYYY-MM-DD">
                        </div>
                    </div>

                    <!-- End Date -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <label for="end_date" class="form-label fw-semibold small">End Date</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="bi bi-calendar-event"></i></span>
                            <input type="text" class="form-control datepicker" id="end_date" name="end_date" value="<?= escape($filters['end_date']) ?>" placeholder="YYYY-MM-DD">
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="col-12 col-md-4 col-lg-6 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-4 rounded-2">
                            <i class="bi bi-funnel-fill me-1"></i> Generate Report
                        </button>
                        <a href="<?= url('index.php?route=reports/health_summary') ?>" class="btn btn-outline-secondary btn-sm px-3 rounded-2">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Filter Status Notice if Filter Active -->
        <?php if (!empty($filters['start_date']) || !empty($filters['end_date'])): ?>
            <div class="alert alert-info border-0 shadow-sm mb-4 d-flex align-items-center justify-content-between">
                <div>
                    <i class="bi bi-filter-circle me-2"></i>
                    Filtered Report Range: <strong><?= escape($filters['start_date'] ?: 'Beginning') ?></strong> to <strong><?= escape($filters['end_date'] ?: 'Today') ?></strong>
                </div>
                <a href="<?= url('index.php?route=reports/health_summary') ?>" class="btn btn-sm btn-link text-info text-decoration-none p-0 fw-semibold">Clear Filter</a>
            </div>
        <?php endif; ?>

        <!-- 4 Core Health Indicators Cards -->
        <div class="row g-3 mb-4">
            <!-- Total Residents Served -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary bg-gradient text-white rounded-3 p-3 fs-3">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Total Residents Served</span>
                            <strong class="text-dark fs-3"><?= number_format($reportData['total_residents_served']) ?></strong>
                            <span class="d-block text-muted small mt-1">Unique Patients</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Consultations -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-info bg-gradient text-white rounded-3 p-3 fs-3">
                            <i class="bi bi-clipboard2-pulse-fill"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Total Consultations</span>
                            <strong class="text-dark fs-3"><?= number_format($reportData['total_consultations']) ?></strong>
                            <span class="d-block text-muted small mt-1">Medical Checkups</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Immunizations Conducted -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-warning bg-gradient text-white rounded-3 p-3 fs-3">
                            <i class="bi bi-shield-fill-check"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Total Immunizations Conducted</span>
                            <strong class="text-dark fs-3"><?= number_format($reportData['total_immunizations']) ?></strong>
                            <span class="d-block text-muted small mt-1">Vaccine Doses Administered</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Medicines Distributed -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-success bg-gradient text-white rounded-3 p-3 fs-3">
                            <i class="bi bi-capsule-prescription"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Medicines Distributed</span>
                            <strong class="text-dark fs-3"><?= number_format($reportData['medicines_distributed']) ?></strong>
                            <span class="d-block text-muted small mt-1">Units / Tablets Dispensed</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Breakdown Tabs -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom pt-3 px-4">
                <ul class="nav nav-tabs card-header-tabs" id="summaryTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-semibold" id="served-tab" data-bs-toggle="tab" data-bs-target="#served-pane" type="button" role="tab">
                            <i class="bi bi-person-check me-1"></i> Residents Served (<?= count($reportData['residents_served_list']) ?>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold" id="consultations-tab" data-bs-toggle="tab" data-bs-target="#consultations-pane" type="button" role="tab">
                            <i class="bi bi-clipboard2-pulse me-1"></i> Consultations (<?= count($reportData['consultations_list']) ?>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold" id="immunizations-tab" data-bs-toggle="tab" data-bs-target="#immunizations-pane" type="button" role="tab">
                            <i class="bi bi-shield-plus me-1"></i> Immunizations (<?= count($reportData['immunizations_list']) ?>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold" id="medicines-tab" data-bs-toggle="tab" data-bs-target="#medicines-pane" type="button" role="tab">
                            <i class="bi bi-capsule me-1"></i> Medicine Distributions (<?= count($reportData['medicines_list']) ?>)
                        </button>
                    </li>
                </ul>
            </div>
            
            <div class="card-body p-4">
                <div class="tab-content" id="summaryTabContent">
                    
                    <!-- Tab 1: Residents Served -->
                    <div class="tab-pane fade show active" id="served-pane" role="tabpanel" aria-labelledby="served-tab">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle border rounded-3 overflow-hidden summary-table" id="table-served">
                                <thead class="table-light">
                                    <tr>
                                        <th>Resident ID</th>
                                        <th>Resident Name</th>
                                        <th>Gender</th>
                                        <th>Age</th>
                                        <th>Contact Number</th>
                                        <th>Barangay Location</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($reportData['residents_served_list'])): ?>
                                        <?php foreach ($reportData['residents_served_list'] as $r): ?>
                                            <tr>
                                                <td class="fw-semibold text-primary"><?= escape($r['resident_code']) ?></td>
                                                <td class="fw-medium"><?= escape($r['resident_name']) ?></td>
                                                <td><?= gender_badge($r['gender']) ?></td>
                                                <td><?= escape($r['age']) ?> yrs</td>
                                                <td><?= escape($r['contact_number'] ?: 'N/A') ?></td>
                                                <td><?= escape($r['barangay']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No resident service records found for the selected period.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 2: Consultations -->
                    <div class="tab-pane fade" id="consultations-pane" role="tabpanel" aria-labelledby="consultations-tab">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle border rounded-3 overflow-hidden summary-table" id="table-consultations">
                                <thead class="table-light">
                                    <tr>
                                        <th>Consultation No</th>
                                        <th>Date</th>
                                        <th>Patient Name</th>
                                        <th>Diagnosis</th>
                                        <th>Treatment</th>
                                        <th>Status</th>
                                        <th>Health Worker</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($reportData['consultations_list'])): ?>
                                        <?php foreach ($reportData['consultations_list'] as $c): ?>
                                            <tr>
                                                <td class="fw-semibold text-primary"><?= escape($c['consultation_no']) ?></td>
                                                <td><?= escape($c['consultation_date']) ?></td>
                                                <td><?= escape($c['resident_name']) ?> <small class="text-muted">(<?= escape($c['resident_code']) ?>)</small></td>
                                                <td><span class="fw-medium"><?= escape($c['diagnosis']) ?></span></td>
                                                <td class="small text-muted" style="max-width: 200px;"><?= escape($c['treatment']) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $c['status'] === 'Completed' ? 'success' : 'warning' ?> bg-opacity-10 text-<?= $c['status'] === 'Completed' ? 'success' : 'warning' ?> border">
                                                        <?= escape($c['status']) ?>
                                                    </span>
                                                </td>
                                                <td><?= escape($c['worker_name']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">No consultation records found for the selected period.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 3: Immunizations -->
                    <div class="tab-pane fade" id="immunizations-pane" role="tabpanel" aria-labelledby="immunizations-tab">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle border rounded-3 overflow-hidden summary-table" id="table-immunizations">
                                <thead class="table-light">
                                    <tr>
                                        <th>Patient Name</th>
                                        <th>Vaccine Item</th>
                                        <th>Dose Level</th>
                                        <th>Date Given</th>
                                        <th>Status</th>
                                        <th>Health Worker</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($reportData['immunizations_list'])): ?>
                                        <?php foreach ($reportData['immunizations_list'] as $i): ?>
                                            <tr>
                                                <td><?= escape($i['resident_name']) ?> <small class="text-muted">(<?= escape($i['resident_code']) ?>)</small></td>
                                                <td><span class="badge bg-info bg-opacity-10 text-info border"><?= escape($i['vaccine_name']) ?></span></td>
                                                <td><?= escape($i['dose']) ?></td>
                                                <td><?= $i['date_given'] ? escape($i['date_given']) : '<span class="text-muted small">Not Given</span>' ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $i['status'] === 'Completed' ? 'success' : 'warning' ?> bg-opacity-10 text-<?= $i['status'] === 'Completed' ? 'success' : 'warning' ?> border">
                                                        <?= escape($i['status']) ?>
                                                    </span>
                                                </td>
                                                <td><?= escape($i['worker_name']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No immunization records found for the selected period.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 4: Medicine Distributions -->
                    <div class="tab-pane fade" id="medicines-pane" role="tabpanel" aria-labelledby="medicines-tab">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle border rounded-3 overflow-hidden summary-table" id="table-medicines">
                                <thead class="table-light">
                                    <tr>
                                        <th>Distribution Date</th>
                                        <th>Item Name</th>
                                        <th>Category</th>
                                        <th>Recipient Patient</th>
                                        <th>Quantity</th>
                                        <th>Health Worker</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($reportData['medicines_list'])): ?>
                                        <?php foreach ($reportData['medicines_list'] as $m): ?>
                                            <tr>
                                                <td><?= escape($m['distribution_date']) ?></td>
                                                <td class="fw-semibold text-primary"><?= escape($m['medicine_name']) ?> <small class="text-muted">(<?= escape($m['medicine_code']) ?>)</small></td>
                                                <td><span class="badge bg-secondary bg-opacity-10 text-secondary border"><?= escape($m['medicine_category']) ?></span></td>
                                                <td><?= escape($m['resident_name']) ?> <small class="text-muted">(<?= escape($m['resident_code']) ?>)</small></td>
                                                <td class="fw-bold text-success"><?= number_format($m['quantity']) ?> pcs</td>
                                                <td><?= escape($m['worker_name']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No medicine distribution records found for the selected period.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once LAYOUT_PATH . 'footer.php'; ?>

<script>
$(document).ready(function() {
    if (typeof flatpickr !== 'undefined') {
        flatpickr('.datepicker', {
            dateFormat: 'Y-m-d',
            allowInput: true
        });
    }

    $('.summary-table').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[0, 'desc']]
    });
});
</script>

<style>
@media print {
    .sidebar-wrapper, .navbar, .d-print-none, .nav-tabs, .dataTables_length, .dataTables_filter, .dataTables_info, .dataTables_paginate {
        display: none !important;
    }
    .main-content-wrapper {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
    .tab-pane {
        display: block !important;
        opacity: 1 !important;
        visibility: visible !important;
        margin-bottom: 2rem;
    }
}
</style>
