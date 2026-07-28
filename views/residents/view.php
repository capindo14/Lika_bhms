<?php
require_once LAYOUT_PATH . 'header.php';
require_once LAYOUT_PATH . 'sidebar.php';
?>

<!-- Print Stylesheet Overrides -->
<style>
@media print {
    body {
        background: #ffffff !important;
        color: #000000 !important;
    }
    .sidebar-wrapper, .navbar-top, .no-print, .btn, footer {
        display: none !important;
    }
    .main-content-wrapper {
        margin-left: 0 !important;
        padding-top: 0 !important;
    }
    .card {
        box-shadow: none !important;
        border: 1px solid #ccc !important;
        margin-bottom: 20px !important;
    }
}
</style>

<div class="main-content-wrapper">
    <?php require_once LAYOUT_PATH . 'navbar.php'; ?>
    
    <div class="container-fluid p-3 p-md-4">
        
        <!-- Header Actions -->
        <div class="d-flex align-items-center justify-content-between mb-4 no-print">
            <div>
                <a href="<?= url('index.php?route=residents') ?>" class="text-decoration-none small">
                    <i class="bi bi-arrow-left"></i> Back to Directory
                </a>
                <h3 class="fw-bold mt-2 mb-0">Resident Profile</h3>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-outline-secondary">
                    <i class="bi bi-printer-fill me-1"></i> Print Chart
                </button>
                <a href="<?= url("index.php?route=residents/edit&id={$resident['id']}") ?>" class="btn btn-primary">
                    <i class="bi bi-pencil-fill me-1"></i> Edit Profile
                </a>
            </div>
        </div>

        <!-- Print-only Header -->
        <div class="d-none d-print-block text-center border-bottom pb-3 mb-4">
            <h4 class="fw-bold mb-0">Barangay Health Monitoring System</h4>
            <p class="text-muted small mb-0">Barangay Health Center Record Sheet</p>
            <p class="text-muted small">Generated on: <?= date('F d, Y H:i:s') ?></p>
        </div>

        <div class="row g-4">
            <!-- Left: Profile Summary -->
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm text-center p-4 h-100">
                    <div class="mx-auto bg-primary bg-gradient text-white rounded-circle d-flex align-items-center justify-content-center fw-bold mb-3" style="width: 90px; height: 90px; font-size: 2.2rem; box-shadow: 0 10px 15px -3px rgba(2, 132, 199, 0.2);">
                        <?= strtoupper(substr($resident['first_name'], 0, 1) . substr($resident['last_name'], 0, 1)) ?>
                    </div>
                    <h4 class="fw-bold mb-1"><?= escape($resident['last_name'] . ', ' . $resident['first_name'] . ' ' . $resident['middle_name']) ?></h4>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-2 rounded-pill mx-auto mb-3"><?= escape($resident['resident_id']) ?></span>

                    <table class="table table-borderless text-start mt-3 mb-0 small">
                        <tbody>
                            <tr class="border-bottom">
                                <td class="text-muted py-2">Gender</td>
                                <td class="fw-semibold text-end py-2"><?= gender_badge($resident['gender']) ?></td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-muted py-2">Age</td>
                                <td class="fw-semibold text-end py-2"><?= escape($resident['age']) ?> Years Old</td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-muted py-2">Birthdate</td>
                                <td class="fw-semibold text-end py-2"><?= date('F d, Y', strtotime($resident['birthdate'])) ?></td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-muted py-2">Civil Status</td>
                                <td class="fw-semibold text-end py-2"><?= escape($resident['civil_status']) ?></td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-muted py-2">Contact</td>
                                <td class="fw-semibold text-end py-2"><?= escape($resident['contact_number'] ?: 'None') ?></td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-muted py-2">Center Area</td>
                                <td class="fw-semibold text-end py-2"><?= escape($resident['barangay']) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted py-2">Address</td>
                                <td class="fw-semibold text-end py-2"><?= escape($resident['address']) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right: Health History Charts -->
            <div class="col-12 col-lg-8">
                <!-- Consultations Timeline -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0"><i class="bi bi-clipboard2-pulse me-2 text-primary"></i>Consultation Records</h5>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <?php if (empty($consultations)): ?>
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-folder2-open fs-2 d-block mb-2"></i>
                                No consultation medical records found for this resident.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Symptoms</th>
                                            <th>Diagnosis</th>
                                            <th>Treatment</th>
                                            <th>Medicine Given</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($consultations as $con): ?>
                                            <tr>
                                                <td class="small fw-semibold"><?= date('M d, Y', strtotime($con['consultation_date'])) ?></td>
                                                <td class="small text-truncate" style="max-width: 150px;" title="<?= escape($con['symptoms']) ?>"><?= escape($con['symptoms']) ?></td>
                                                <td><span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle"><?= escape($con['diagnosis']) ?></span></td>
                                                <td class="small text-truncate" style="max-width: 150px;"><?= escape($con['treatment']) ?></td>
                                                <td class="small">
                                                    <?php if (!empty($con['medicine_name'])): ?>
                                                        <?= escape($con['medicine_name']) ?> <span class="badge bg-secondary"><?= $con['medicine_qty'] ?> pcs</span>
                                                    <?php else: ?>
                                                        <span class="text-muted">None</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Immunizations History -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0"><i class="bi bi-shield-plus me-2 text-info"></i>Immunization History</h5>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <?php if (empty($immunizations)): ?>
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-shield-slash fs-2 d-block mb-2"></i>
                                No immunization records tracked.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Vaccine</th>
                                            <th>Dose</th>
                                            <th>Date Given</th>
                                            <th>Next Schedule</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($immunizations as $imm): ?>
                                            <tr>
                                                <td class="fw-semibold small"><?= escape($imm['vaccine_name']) ?></td>
                                                <td class="small"><?= escape($imm['dose']) ?></td>
                                                <td class="small"><?= $imm['date_given'] ? date('M d, Y', strtotime($imm['date_given'])) : '<span class="text-muted">N/A</span>' ?></td>
                                                <td class="small fw-semibold text-danger"><?= $imm['next_schedule'] ? date('M d, Y', strtotime($imm['next_schedule'])) : '<span class="text-muted">None</span>' ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $imm['status'] === 'Completed' ? 'success' : ($imm['status'] === 'Upcoming' ? 'warning' : 'danger') ?> bg-opacity-10 text-<?= $imm['status'] === 'Completed' ? 'success' : ($imm['status'] === 'Upcoming' ? 'warning' : 'danger') ?> border border-<?= $imm['status'] === 'Completed' ? 'success' : ($imm['status'] === 'Upcoming' ? 'warning' : 'danger') ?>-subtle">
                                                        <?= escape($imm['status']) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once LAYOUT_PATH . 'footer.php'; ?>
