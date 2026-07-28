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
            <p class="text-muted small">Access clinical reports, demographic lists, stock distribution statistics, and print summaries.</p>
        </div>

        <!-- 1. Statistics Cards -->
        <div class="row g-3 mb-4">
            <!-- Total Residents -->
            <div class="col-6 col-lg-3">
                <div class="card p-3 border-0 shadow-sm h-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary bg-gradient text-white rounded-3 p-2.5 fs-4"><i class="bi bi-people-fill"></i></div>
                        <div>
                            <small class="text-muted d-block">Active Directory</small>
                            <strong class="text-dark fs-5"><?= number_format($summary['total_residents']) ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Families -->
            <div class="col-6 col-lg-3">
                <div class="card p-3 border-0 shadow-sm h-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-success bg-gradient text-white rounded-3 p-2.5 fs-4"><i class="bi bi-house-heart-fill"></i></div>
                        <div>
                            <small class="text-muted d-block">Household Profiles</small>
                            <strong class="text-dark fs-5"><?= number_format($summary['total_families']) ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Consultations -->
            <div class="col-6 col-lg-3">
                <div class="card p-3 border-0 shadow-sm h-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-info bg-gradient text-white rounded-3 p-2.5 fs-4"><i class="bi bi-clipboard2-pulse-fill"></i></div>
                        <div>
                            <small class="text-muted d-block">Checkup Logs</small>
                            <strong class="text-dark fs-5"><?= number_format($summary['total_consultations']) ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Immunizations -->
            <div class="col-6 col-lg-3">
                <div class="card p-3 border-0 shadow-sm h-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-warning bg-gradient text-white rounded-3 p-2.5 fs-4"><i class="bi bi-shield-fill-plus"></i></div>
                        <div>
                            <small class="text-muted d-block">Immunizations Logs</small>
                            <strong class="text-dark fs-5"><?= number_format($summary['total_immunizations']) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Modules Navigation Links -->
        <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-folder-fill me-2 text-primary"></i>Available Report Modules</h5>
        
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-3 mb-4">
            <!-- 1. Resident Demographics -->
            <div class="col">
                <div class="card h-100 border-0 shadow-sm p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="text-primary fs-3 mb-2"><i class="bi bi-people"></i></div>
                        <h6 class="fw-bold text-dark mb-1 small">Resident Demographics</h6>
                        <p class="text-muted mb-3" style="font-size: 0.78rem; line-height: 1.3;">Filter by gender, civil status, age brackets, and active directory status.</p>
                    </div>
                    <a href="<?= url('index.php?route=reports/residents') ?>" class="btn btn-outline-primary w-100 btn-sm rounded-2 py-1.5 fw-medium">Generate Report</a>
                </div>
            </div>

            <!-- 2. Consultation Logs -->
            <div class="col">
                <div class="card h-100 border-0 shadow-sm p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="text-info fs-3 mb-2"><i class="bi bi-clipboard2-pulse"></i></div>
                        <h6 class="fw-bold text-dark mb-1 small">Consultation Records</h6>
                        <p class="text-muted mb-3" style="font-size: 0.78rem; line-height: 1.3;">Analyze diagnostic records, treatment trends, and checkups over specified date ranges.</p>
                    </div>
                    <a href="<?= url('index.php?route=reports/consultations') ?>" class="btn btn-outline-info w-100 btn-sm rounded-2 py-1.5 fw-medium">Generate Report</a>
                </div>
            </div>

            <!-- 3. Immunization Logs -->
            <div class="col">
                <div class="card h-100 border-0 shadow-sm p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="text-warning fs-3 mb-2"><i class="bi bi-shield-plus"></i></div>
                        <h6 class="fw-bold text-dark mb-1 small">Immunization Logs</h6>
                        <p class="text-muted mb-3" style="font-size: 0.78rem; line-height: 1.3;">Track pediatric schedules, completed vaccines, and missed appointments.</p>
                    </div>
                    <a href="<?= url('index.php?route=reports/immunizations') ?>" class="btn btn-outline-warning w-100 btn-sm rounded-2 py-1.5 fw-medium">Generate Report</a>
                </div>
            </div>

            <!-- 4. Medicine Allocations -->
            <div class="col">
                <div class="card h-100 border-0 shadow-sm p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="text-success fs-3 mb-2"><i class="bi bi-capsule"></i></div>
                        <h6 class="fw-bold text-dark mb-1 small">Medicine Allocations</h6>
                        <p class="text-muted mb-3" style="font-size: 0.78rem; line-height: 1.3;">Monitor distributed stock items, tracking quantities given to individual patients.</p>
                    </div>
                    <a href="<?= url('index.php?route=reports/medicine') ?>" class="btn btn-outline-success w-100 btn-sm rounded-2 py-1.5 fw-medium">Generate Report</a>
                </div>
            </div>

            <!-- 5. Health Summary Report (Right of Medicine Allocations) -->
            <div class="col">
                <div class="card h-100 border-0 shadow-sm p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="text-primary fs-3 mb-2"><i class="bi bi-file-earmark-medical"></i></div>
                        <h6 class="fw-bold text-dark mb-1 small">Health Summary Report</h6>
                        <p class="text-muted mb-3" style="font-size: 0.78rem; line-height: 1.3;">Overall health summary detailing Residents Served, Checkups, Vaccines, and Medicines.</p>
                    </div>
                    <a href="<?= url('index.php?route=reports/health_summary') ?>" class="btn btn-outline-primary w-100 btn-sm rounded-2 py-1.5 fw-medium">Generate Report</a>
                </div>
            </div>
        </div>

        <!-- 3. Stock levels low stock warnings grid -->
        <?php if (!empty($lowStocks)): ?>
            <div class="card border-0 shadow-sm border-start border-4 border-danger">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="fw-bold text-danger mb-0"><i class="bi bi-exclamation-octagon-fill me-2"></i>Critical Stock Shortage Items</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="table-responsive">
                        <table class="table align-middle table-sm small table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Code</th>
                                    <th>Item</th>
                                    <th>Category</th>
                                    <th>Available Qty</th>
                                    <th>Reorder Limit</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lowStocks as $m): ?>
                                    <tr>
                                        <td class="fw-bold"><?= escape($m['code']) ?></td>
                                        <td><?= escape($m['name']) ?></td>
                                        <td><?= escape($m['category']) ?></td>
                                        <td class="text-danger fw-bold"><?= $m['stock_qty'] ?> pcs</td>
                                        <td><?= $m['reorder_level'] ?> pcs</td>
                                        <td><a href="<?= url('index.php?route=medicine') ?>" class="btn btn-sm btn-outline-secondary py-0 px-2 small">Restock</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once LAYOUT_PATH . 'footer.php'; ?>
