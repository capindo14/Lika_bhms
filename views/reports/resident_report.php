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
                <p class="text-muted small">Generate lists filtered by demographic classifications.</p>
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
            <h5>Residents Demographics Report</h5>
            <p class="text-muted small">Generated on: <?= date('Y-m-d H:i:s') ?> • Filtered List</p>
        </div>

        <!-- Filter Card -->
        <div class="card border-0 shadow-sm mb-4 no-print">
            <div class="card-body p-4">
                <form method="GET" action="index.php">
                    <input type="hidden" name="route" value="reports/residents">
                    
                    <div class="row g-3 align-items-end">
                        <!-- Gender -->
                        <div class="col-12 col-md-3">
                            <label for="gender" class="form-label fw-semibold small">Gender</label>
                            <select class="form-select select-sm" id="gender" name="gender">
                                <option value="">All Genders</option>
                                <option value="Male" <?= ($filters['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= ($filters['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                                <option value="Other" <?= ($filters['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>

                        <!-- Civil Status -->
                        <div class="col-12 col-md-3">
                            <label for="civil_status" class="form-label fw-semibold small">Civil Status</label>
                            <select class="form-select select-sm" id="civil_status" name="civil_status">
                                <option value="">All Statuses</option>
                                <option value="Single" <?= ($filters['civil_status'] ?? '') === 'Single' ? 'selected' : '' ?>>Single</option>
                                <option value="Married" <?= ($filters['civil_status'] ?? '') === 'Married' ? 'selected' : '' ?>>Married</option>
                                <option value="Widowed" <?= ($filters['civil_status'] ?? '') === 'Widowed' ? 'selected' : '' ?>>Widowed</option>
                                <option value="Divorced" <?= ($filters['civil_status'] ?? '') === 'Divorced' ? 'selected' : '' ?>>Divorced</option>
                            </select>
                        </div>

                        <!-- Age Group -->
                        <div class="col-12 col-md-3">
                            <label for="age_group" class="form-label fw-semibold small">Age Bracket</label>
                            <select class="form-select select-sm" id="age_group" name="age_group">
                                <option value="">All Ages</option>
                                <option value="infant" <?= ($filters['age_group'] ?? '') === 'infant' ? 'selected' : '' ?>>Infant (<1 yr)</option>
                                <option value="child" <?= ($filters['age_group'] ?? '') === 'child' ? 'selected' : '' ?>>Child (1-12 yrs)</option>
                                <option value="teen" <?= ($filters['age_group'] ?? '') === 'teen' ? 'selected' : '' ?>>Teen (13-19 yrs)</option>
                                <option value="adult" <?= ($filters['age_group'] ?? '') === 'adult' ? 'selected' : '' ?>>Adult (20-59 yrs)</option>
                                <option value="senior" <?= ($filters['age_group'] ?? '') === 'senior' ? 'selected' : '' ?>>Senior (60+ yrs)</option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="col-12 col-md-2">
                            <label for="status" class="form-label fw-semibold small">Profile State</label>
                            <select class="form-select select-sm" id="status" name="status">
                                <option value="Active" <?= ($filters['status'] ?? '') === 'Active' ? 'selected' : '' ?>>Active Directory</option>
                                <option value="Archived" <?= ($filters['status'] ?? '') === 'Archived' ? 'selected' : '' ?>>Archived</option>
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
                                <th>Resident ID</th>
                                <th>Full Name</th>
                                <th>Gender</th>
                                <th>Age</th>
                                <th>Birthdate</th>
                                <th>Civil Status</th>
                                <th>Address</th>
                                <th>Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No resident profiles matching current criteria.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($data as $r): ?>
                                    <tr>
                                        <td class="fw-semibold text-primary"><?= escape($r['resident_id']) ?></td>
                                        <td class="fw-bold"><?= escape($r['last_name'] . ', ' . $r['first_name'] . ' ' . $r['middle_name']) ?></td>
                                        <td><?= gender_badge($r['gender']) ?></td>
                                        <td><?= escape($r['age']) ?> yrs</td>
                                        <td><?= date('Y-m-d', strtotime($r['birthdate'])) ?></td>
                                        <td><?= escape($r['civil_status']) ?></td>
                                        <td><small><?= escape($r['address']) ?></small></td>
                                        <td>
                                            <?php if ($r['is_family_head']): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle">Family Head</span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted border">Member</span>
                                            <?php endif; ?>
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
