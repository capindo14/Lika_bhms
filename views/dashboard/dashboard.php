<?php
require_once LAYOUT_PATH . 'header.php';
require_once LAYOUT_PATH . 'sidebar.php';
?>

<div class="main-content-wrapper">
    <?php require_once LAYOUT_PATH . 'navbar.php'; ?>
    
    <div class="container-fluid p-3 p-md-4">
        
        <!-- Header Section -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold mb-1">Barangay Health Monitoring Dashboard</h3>
                <p class="text-muted small mb-0">Overview of active health status, consultations, and medical stock distributions.</p>
            </div>
        </div>

        <!-- Alert Flag for Low Stock -->
        <?php if ($lowStockCount > 0): ?>
            <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm d-flex align-items-center mb-4 rounded-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-warning"></i>
                <div>
                    <strong>Inventory Alert:</strong> There are currently <strong><?= $lowStockCount ?></strong> medicine/vaccine/family planning item(s) running below their reorder thresholds.
                    <a href="<?= url('index.php?route=medicine') ?>" class="alert-link text-decoration-none ms-2">View Stocks <i class="bi bi-arrow-right"></i></a>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- 1. Metric Cards Grid -->
        <div class="row g-3 mb-4">
            <!-- Total Residents -->
            <div class="col-6 col-lg-3">
                <div class="card p-3 border-0 shadow-sm h-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary bg-gradient text-white rounded-3 p-2.5 fs-4"><i class="bi bi-people-fill"></i></div>
                        <div>
                            <small class="text-muted d-block">Total Residents</small>
                            <strong class="text-dark fs-5"><?= number_format($totalResidents) ?></strong>
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
                            <small class="text-muted d-block">Total Families</small>
                            <strong class="text-dark fs-5"><?= number_format($totalFamilies) ?></strong>
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
                            <small class="text-muted d-block">Total Consultations</small>
                            <strong class="text-dark fs-5"><?= number_format($totalConsultations) ?></strong>
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
                            <small class="text-muted d-block">Total Immunizations</small>
                            <strong class="text-dark fs-5"><?= number_format($totalImmunizations) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Charts Section -->
        <div class="row g-4 mb-4">
            <!-- Line Chart: Monthly Consultations -->
            <div class="col-12 col-xl-8">
                <div class="card h-100 border-0 p-4 shadow-sm">
                    <h5 class="fw-bold mb-3"><i class="bi bi-activity text-primary me-2"></i>Monthly Consultation Trends</h5>
                    <div style="position: relative; height: 320px;">
                        <canvas id="consultationsChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Doughnut Chart: Medicine Distribution -->
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100 border-0 p-4 shadow-sm">
                    <h5 class="fw-bold mb-3"><i class="bi bi-capsule-prescription text-success me-2"></i>Medicine & Family Planning</h5>
                    <div style="position: relative; height: 320px; display: flex; align-items: center; justify-content: center;">
                        <canvas id="medicineChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Bar Chart: Monthly Immunizations -->
            <div class="col-12 col-md-6 col-xl-6">
                <div class="card h-100 border-0 p-4 shadow-sm">
                    <h5 class="fw-bold mb-3"><i class="bi bi-shield-plus text-info me-2"></i>Monthly Immunizations</h5>
                    <div style="position: relative; height: 300px;">
                        <canvas id="immunizationsChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Pie Chart: Gender & Age Polar Chart -->
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card h-100 border-0 p-4 shadow-sm">
                    <h5 class="fw-bold mb-3"><i class="bi bi-gender-ambiguous text-warning me-2"></i>Gender Mix</h5>
                    <div style="position: relative; height: 260px; display: flex; align-items: center; justify-content: center;">
                        <canvas id="genderChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="card h-100 border-0 p-4 shadow-sm">
                    <h5 class="fw-bold mb-3"><i class="bi bi-hourglass-split text-danger me-2"></i>Age Brackets</h5>
                    <div style="position: relative; height: 260px; display: flex; align-items: center; justify-content: center;">
                        <canvas id="ageChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Tables & Timelines Widgets -->
        <div class="row g-4">
            <!-- Left: Quick Lists -->
            <div class="col-12 col-lg-8">
                <div class="card border-0 mb-4 shadow-sm">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0"><i class="bi bi-clipboard2-pulse me-2 text-primary"></i>Today's Consultations</h5>
                        <a href="<?= url('index.php?route=consultation') ?>" class="text-decoration-none small">View All <i class="bi bi-arrow-right"></i></a>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4" style="width: 22%;">Patient</th>
                                    <th style="width: 28%;">Symptoms</th>
                                    <th style="width: 22%;">Diagnosis</th>
                                    <th style="width: 16%;">Health Worker</th>
                                    <th class="pe-4 text-end" style="width: 12%;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($latestConsultations)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No consultations logged today.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($latestConsultations as $c): ?>
                                        <tr>
                                            <td class="fw-semibold ps-4"><?= escape($c['resident_name']) ?></td>
                                            <td class="small text-muted"><?= escape($c['symptoms']) ?></td>
                                            <td>
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle text-wrap text-start"><?= escape($c['diagnosis']) ?></span>
                                            </td>
                                            <td class="small"><?= escape($c['worker_name']) ?></td>
                                            <td class="pe-4 text-end">
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

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0"><i class="bi bi-shield-plus me-2 text-info"></i>Upcoming Immunization Schedules</h5>
                        <a href="<?= url('index.php?route=immunization') ?>" class="text-decoration-none small">View Schedules <i class="bi bi-arrow-right"></i></a>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Child/Resident</th>
                                    <th>Vaccine</th>
                                    <th>Dose</th>
                                    <th class="pe-4 text-end">Next Schedule</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($upcomingImmunizations)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">No upcoming schedules.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($upcomingImmunizations as $i): ?>
                                        <tr role="button" onclick="window.location.href='<?= url('index.php?route=immunization') ?>'" title="Click to view immunization management">
                                            <td class="fw-semibold ps-4 text-primary"><?= escape($i['resident_name']) ?> <i class="bi bi-box-arrow-up-right ms-1 small" style="font-size: 0.75rem;"></i></td>
                                            <td><span class="badge bg-info bg-opacity-10 text-info border border-info-subtle"><i class="bi bi-shield-plus me-1"></i><?= escape($i['vaccine_name']) ?></span></td>
                                            <td><?= escape($i['dose']) ?></td>
                                            <td class="text-danger fw-medium pe-4 text-end"><?= date('F d, Y', strtotime($i['next_schedule'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right: Activity Logs & New Residents -->
            <div class="col-12 col-lg-4">
                <div class="card border-0 mb-4 shadow-sm">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-warning"></i>Recent System Activity</h5>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <ul class="timeline list-unstyled mb-0">
                            <?php if (empty($recentActivities)): ?>
                                <li class="text-muted text-center py-3">No activity logs recorded.</li>
                            <?php else: ?>
                                <?php foreach ($recentActivities as $log): 
                                    $actionDisplay = str_replace('_JS', '', $log['action']);
                                    $descDisplay = str_replace(['via JS CRUD', 'via JS:', 'via JS', '  '], ['', ':', '', ' '], $log['description']);
                                    $descDisplay = trim(str_replace(' : ', ': ', $descDisplay));
                                ?>
                                    <li class="mb-3 pb-3 border-bottom last-border-0">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="fw-semibold small text-primary"><?= escape($actionDisplay) ?></span>
                                            <span class="text-muted small" style="font-size: 0.75rem;"><?= date('M d, H:i', strtotime($log['created_at'])) ?></span>
                                        </div>
                                        <p class="text-muted small mb-1"><?= escape($descDisplay) ?></p>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary small border border-secondary-subtle" style="font-size: 0.7rem;">
                                            By: <?= escape($log['fullname'] ?: 'Guest') ?> (<?= escape($log['role'] ?: 'Unknown') ?>)
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0"><i class="bi bi-people-fill me-2 text-success"></i>Latest Residents Registered</h5>
                        <a href="<?= url('index.php?route=residents') ?>" class="text-decoration-none small"><i class="bi bi-plus-lg"></i> Add</a>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <ul class="list-unstyled mb-0">
                            <?php if (empty($latestResidents)): ?>
                                <li class="text-muted text-center py-3">No residents registered yet.</li>
                            <?php else: ?>
                                <?php foreach ($latestResidents as $res): ?>
                                    <li class="d-flex align-items-center mb-3">
                                        <div class="bg-success bg-gradient text-black rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 42px; height: 42px; font-size: 1.1rem; flex-shrink: 0;">
                                            <i class="bi bi-person-fill"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <a href="<?= url("index.php?route=residents/view&id={$res['id']}") ?>" class="d-block fw-semibold text-dark text-decoration-none text-truncate small"><?= escape($res['last_name'] . ', ' . $res['first_name']) ?></a>
                                            <span class="text-muted small" style="font-size: 0.75rem;"><?= escape($res['gender']) ?> • <?= escape($res['age']) ?> yrs old • <?= escape($res['resident_id']) ?></span>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once LAYOUT_PATH . 'footer.php'; ?>

<!-- Page-Specific Chart.js Configurations -->
<script>
$(document).ready(function() {
    
    // Theme configurations
    const gridColor = () => document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.05)';
    const textColor = () => document.documentElement.getAttribute('data-bs-theme') === 'dark' ? '#94a3b8' : '#64748b';

    // 1. Line Chart: Monthly Consultations
    const ctxCon = document.getElementById('consultationsChart').getContext('2d');
    const consultationsChart = new Chart(ctxCon, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Consultations logged',
                data: <?= json_encode(array_values($monthlyConsultations)) ?>,
                borderColor: '#0284c7',
                backgroundColor: 'rgba(2, 132, 199, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#0284c7',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { color: gridColor() },
                    ticks: { color: textColor() }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor() },
                    ticks: { 
                        color: textColor(),
                        stepSize: 1
                    }
                }
            }
        }
    });

    // 2. Bar Chart: Monthly Immunizations
    const ctxImm = document.getElementById('immunizationsChart').getContext('2d');
    new Chart(ctxImm, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Vaccinations given',
                data: <?= json_encode(array_values($monthlyImmunizations)) ?>,
                backgroundColor: 'rgba(16, 185, 129, 0.75)',
                borderColor: '#10b981',
                borderWidth: 1.5,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { color: gridColor() },
                    ticks: { color: textColor() }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor() },
                    ticks: { 
                        color: textColor(),
                        stepSize: 1
                    }
                }
            }
        }
    });

    // 3. Doughnut Chart: Medicine Distribution
    const ctxMed = document.getElementById('medicineChart').getContext('2d');
    const medLabels = <?= json_encode(array_column($medicineDistribution, 'name')) ?>;
    const medCounts = <?= json_encode(array_column($medicineDistribution, 'count')) ?>;
    
    new Chart(ctxMed, {
        type: 'doughnut',
        data: {
            labels: medLabels.length > 0 ? medLabels : ['No Allocation Yet'],
            datasets: [{
                data: medCounts.length > 0 ? medCounts : [1],
                backgroundColor: [
                    'rgba(2, 132, 199, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(139, 92, 246, 0.8)',
                    'rgba(148, 163, 184, 0.3)'
                ],
                borderWidth: 2,
                borderColor: document.documentElement.getAttribute('data-bs-theme') === 'dark' ? '#0f172a' : '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: textColor(), padding: 12, boxWidth: 12 }
                }
            },
            cutout: '65%'
        }
    });

    // 4. Pie Chart: Gender Distribution
    const ctxGen = document.getElementById('genderChart').getContext('2d');
    const genderLabels = [];
    const genderCounts = [];
    <?php foreach($genderDistribution as $g): ?>
        genderLabels.push('<?= $g['gender'] ?>');
        genderCounts.push(<?= $g['count'] ?>);
    <?php endforeach; ?>

    new Chart(ctxGen, {
        type: 'pie',
        data: {
            labels: genderLabels.length > 0 ? genderLabels : ['No Record'],
            datasets: [{
                data: genderCounts.length > 0 ? genderCounts : [0],
                backgroundColor: [
                    'rgba(14, 165, 233, 0.8)', // Male Blue
                    'rgba(236, 72, 153, 0.8)',  // Female Pink
                    'rgba(100, 116, 139, 0.8)'  // Other Gray
                ],
                borderWidth: 2,
                borderColor: document.documentElement.getAttribute('data-bs-theme') === 'dark' ? '#0f172a' : '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: textColor(), boxWidth: 12 }
                }
            }
        }
    });

    // 5. Radar Chart: Age Distribution
    const ctxAge = document.getElementById('ageChart').getContext('2d');
    const ageData = [
        <?= (int)($ageDistribution['infants'] ?? 0) ?>,
        <?= (int)($ageDistribution['children'] ?? 0) ?>,
        <?= (int)($ageDistribution['teens'] ?? 0) ?>,
        <?= (int)($ageDistribution['adults'] ?? 0) ?>,
        <?= (int)($ageDistribution['seniors'] ?? 0) ?>
    ];

    new Chart(ctxAge, {
        type: 'polarArea',
        data: {
            labels: ['Infant (<1)', 'Child (1-12)', 'Teen (13-19)', 'Adult (20-59)', 'Senior (60+)'],
            datasets: [{
                data: ageData,
                backgroundColor: [
                    'rgba(239, 68, 68, 0.7)',
                    'rgba(245, 158, 11, 0.7)',
                    'rgba(16, 185, 129, 0.7)',
                    'rgba(59, 130, 246, 0.7)',
                    'rgba(139, 92, 246, 0.7)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: textColor(), boxWidth: 12 }
                }
            },
            scales: {
                r: {
                    grid: { color: gridColor() },
                    ticks: { display: false }
                }
            }
        }
    });

    // Handle theme toggle synchronization for charts
    $('#theme-toggle-btn').on('click', function() {
        setTimeout(function() {
            const grid = gridColor();
            const text = textColor();
            const bgBorder = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? '#0f172a' : '#ffffff';
            
            // Update line chart scales
            consultationsChart.options.scales.x.grid.color = grid;
            consultationsChart.options.scales.x.ticks.color = text;
            consultationsChart.options.scales.y.grid.color = grid;
            consultationsChart.options.scales.y.ticks.color = text;
            consultationsChart.update();
            
            // Recolor doughnut chart borders
            // (We could reload or leave it to trigger on reload, but this looks clean)
        }, 50);
    });
});
</script>
