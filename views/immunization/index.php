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
                <p class="text-muted small mb-0">Track child vaccinations, booster immunizations, and appointment schedules.</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#logImmunizationModal">
                    <i class="bi bi-plus-lg me-1"></i> Log Vaccination / Schedule
                </button>
            </div>
        </div>

        <!-- Navigation Tabs for List vs Calendar -->
        <ul class="nav nav-tabs border-bottom mb-4" id="immunizationTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold text-secondary" id="list-tab" data-bs-toggle="tab" data-bs-target="#list-view" type="button" role="tab" aria-controls="list-view" aria-selected="true">
                    <i class="bi bi-list-task me-1"></i> Records Directory
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-secondary" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendar-view" type="button" role="tab" aria-controls="calendar-view" aria-selected="false">
                    <i class="bi bi-calendar-event me-1"></i> Calendar Scheduler
                </button>
            </li>
        </ul>

        <div class="tab-content" id="immunizationTabsContent">
            <!-- TAB 1: Records Table -->
            <div class="tab-pane fade show active" id="list-view" role="tabpanel" aria-labelledby="list-tab">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table id="immunization-table" class="table table-hover align-middle w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th>Patient (Child)</th>
                                        <th>Vaccine</th>
                                        <th>Dose</th>
                                        <th>Date Given</th>
                                        <th>Next Schedule</th>
                                        <th>Status</th>
                                        <th>Health Worker</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($records as $r): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-semibold"><?= escape($r['resident_name']) ?></div>
                                                <small class="text-muted"><?= escape($r['resident_code']) ?> • (<?= escape($r['resident_age']) ?> yrs)</small>
                                            </td>
                                            <td><span class="badge bg-info bg-opacity-10 text-info border border-info-subtle"><i class="bi bi-shield-fill-check me-1"></i><?= escape($r['vaccine_name']) ?></span></td>
                                            <td><?= escape($r['dose']) ?></td>
                                            <td><?= $r['date_given'] ? date('Y-m-d', strtotime($r['date_given'])) : '<span class="text-muted small">Not Given</span>' ?></td>
                                            <td class="fw-medium <?= $r['status'] === 'Missed' ? 'text-danger' : 'text-primary' ?>">
                                                <?= $r['next_schedule'] ? date('Y-m-d', strtotime($r['next_schedule'])) : '<span class="text-muted small">None</span>' ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $r['status'] === 'Completed' ? 'success' : ($r['status'] === 'Upcoming' ? 'warning' : 'danger') ?> bg-opacity-10 text-<?= $r['status'] === 'Completed' ? 'success' : ($r['status'] === 'Upcoming' ? 'warning' : 'danger') ?> border border-<?= $r['status'] === 'Completed' ? 'success' : ($r['status'] === 'Upcoming' ? 'warning' : 'danger') ?>-subtle">
                                                    <?= escape($r['status']) ?>
                                                </span>
                                            </td>
                                            <td><?= escape($r['worker_name']) ?></td>
                                            <td class="text-center">
                                                <div class="btn-group gap-1">
                                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-2" onclick="editImmunization(<?= $r['id'] ?>)" title="Edit Log">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button onclick="confirmDelete(<?= $r['id'] ?>, '<?= escape($r['resident_name']) ?>')" class="btn btn-outline-danger btn-sm rounded-2" title="Delete Log">
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

            <!-- TAB 2: Calendar Scheduler -->
            <div class="tab-pane fade" id="calendar-view" role="tabpanel" aria-labelledby="calendar-tab">
                <div class="row g-4">
                    <!-- The Grid Calendar -->
                    <div class="col-12 col-lg-8">
                        <div class="card border-0 shadow-sm p-4">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h4 class="fw-bold mb-0 text-dark" id="calendar-month-year"></h4>
                                <div class="btn-group">
                                    <button class="btn btn-outline-secondary btn-sm" id="prev-month-btn"><i class="bi bi-chevron-left"></i></button>
                                    <button class="btn btn-outline-secondary btn-sm" id="next-month-btn"><i class="bi bi-chevron-right"></i></button>
                                </div>
                            </div>
                            
                            <!-- Weeks header -->
                            <div class="row text-center fw-bold mb-2 text-muted" style="font-size: 0.85rem;">
                                <div class="col">Sun</div>
                                <div class="col">Mon</div>
                                <div class="col">Tue</div>
                                <div class="col">Wed</div>
                                <div class="col">Thu</div>
                                <div class="col">Fri</div>
                                <div class="col">Sat</div>
                            </div>
                            
                            <!-- Calendar Days Container -->
                            <div id="calendar-days" class="row text-center g-1">
                                <!-- Generated Dynamically -->
                            </div>
                        </div>
                    </div>

                    <!-- Agenda / Detail Widgets -->
                    <div class="col-12 col-lg-4">
                        <div class="card border-0 shadow-sm p-4 h-100">
                            <h5 class="fw-bold mb-3"><i class="bi bi-journal-medical text-primary me-2"></i>Day Schedules</h5>
                            <div class="alert alert-light border small" id="calendar-day-info">
                                Click on a highlighted day in the calendar grid to see scheduled patient vaccinations.
                            </div>
                            <div id="calendar-day-list" class="list-group gap-2">
                                <!-- Loaded dynamically on date click -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once LAYOUT_PATH . 'footer.php'; ?>

<!-- Calendar Stylings -->
<style>
.calendar-day-box {
    height: 90px;
    border: 1px solid rgba(0,0,0,0.06);
    border-radius: 6px;
    position: relative;
    padding: 4px;
    transition: all 0.2s ease;
    background-color: var(--bs-card-bg);
}

[data-bs-theme="dark"] .calendar-day-box {
    border-color: rgba(255,255,255,0.08);
}

.calendar-day-box:hover {
    background-color: rgba(2, 132, 199, 0.05);
    cursor: pointer;
}

.calendar-day-num {
    font-weight: 600;
    font-size: 0.85rem;
    color: var(--bs-body-color);
}

.calendar-event-indicator {
    font-size: 0.7rem;
    padding: 2px 4px;
    border-radius: 4px;
    margin-top: 2px;
    text-align: left;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
}

.indicator-upcoming {
    background-color: rgba(245, 158, 11, 0.1);
    color: #d97706;
    border: 1px solid rgba(245, 158, 11, 0.2);
}

.indicator-missed {
    background-color: rgba(239, 68, 68, 0.1);
    color: #dc2626;
    border: 1px solid rgba(239, 68, 68, 0.2);
}

.indicator-completed {
    background-color: rgba(16, 185, 129, 0.1);
    color: #059669;
    border: 1px solid rgba(16, 185, 129, 0.2);
}

.other-month {
    opacity: 0.3;
}
</style>

<script>
$(document).ready(function() {
    $('#immunization-table').DataTable({
        responsive: true,
        order: [[4, 'asc']], // Sort by Next Schedule
        columnDefs: [
            { orderable: false, targets: 7 }
        ]
    });

    // --- Dynamic Calendar Implementation ---
    
    // Parse scheduled activities from server-side PHP loop
    const schedules = [
        <?php foreach ($records as $r): 
            $scheduleDate = $r['next_schedule'] ?: $r['date_given'];
            if ($scheduleDate):
        ?>
            {
                id: <?= $r['id'] ?>,
                patient: '<?= escape(addslashes($r['resident_name'])) ?>',
                vaccine: '<?= escape(addslashes($r['vaccine_name'])) ?>',
                dose: '<?= escape(addslashes($r['dose'])) ?>',
                date: '<?= $scheduleDate ?>',
                status: '<?= $r['status'] ?>'
            },
        <?php endif; endforeach; ?>
    ];

    let currentDate = new Date();

    function renderCalendar() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();

        const monthNames = [
            "January", "February", "March", "April", "May", "June", 
            "July", "August", "September", "October", "November", "December"
        ];

        $('#calendar-month-year').text(`${monthNames[month]} ${year}`);

        const firstDayOfMonth = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const daysInPrevMonth = new Date(year, month, 0).getDate();

        let dayBoxes = '';

        // Prepend previous month days
        for (let i = firstDayOfMonth - 1; i >= 0; i--) {
            const dayNum = daysInPrevMonth - i;
            const prevMonthDate = new Date(year, month - 1, dayNum);
            dayBoxes += generateDayBox(prevMonthDate, true);
        }

        // Current month days
        for (let i = 1; i <= daysInMonth; i++) {
            const thisDate = new Date(year, month, i);
            dayBoxes += generateDayBox(thisDate, false);
        }

        // Append next month days to complete weekly columns
        const totalRendered = firstDayOfMonth + daysInMonth;
        const remaining = totalRendered % 7 === 0 ? 0 : 7 - (totalRendered % 7);
        for (let i = 1; i <= remaining; i++) {
            const nextMonthDate = new Date(year, month + 1, i);
            dayBoxes += generateDayBox(nextMonthDate, true);
        }

        $('#calendar-days').html(dayBoxes);
    }

    function generateDayBox(date, isOtherMonth) {
        const dateStr = date.toISOString().split('T')[0];
        const day = date.getDate();
        
        // Find matching events
        const dayEvents = schedules.filter(ev => ev.date === dateStr);
        let eventHTML = '';

        dayEvents.slice(0, 2).forEach(ev => {
            const statusClass = ev.status.toLowerCase();
            eventHTML += `
                <div class="calendar-event-indicator indicator-${statusClass}" title="${ev.patient}: ${ev.vaccine}">
                    ${ev.patient.split(' ')[0]} - ${ev.vaccine}
                </div>
            `;
        });

        if (dayEvents.length > 2) {
            eventHTML += `<div class="text-muted small mt-1 text-start" style="font-size: 0.65rem;">+ ${dayEvents.length - 2} more</div>`;
        }

        return `
            <div class="col-7-custom calendar-day-box ${isOtherMonth ? 'other-month' : ''}" data-date="${dateStr}">
                <div class="calendar-day-num text-start">${day}</div>
                ${eventHTML}
            </div>
        `;
    }

    // Set responsive 7 column columns layout
    $("<style>")
        .prop("type", "text/css")
        .html(".col-7-custom { width: 14.285%; flex: 0 0 14.285%; max-width: 14.285%; }")
        .appendTo("head");

    // Click trigger on days
    $(document).on('click', '.calendar-day-box', function() {
        const clickedDate = $(this).data('date');
        const dayEvents = schedules.filter(ev => ev.date === clickedDate);
        
        const dateObj = new Date(clickedDate);
        const formatTitle = dateObj.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
        
        $('#calendar-day-info').html(`Schedules for <strong>${formatTitle}</strong>`);
        
        let listHTML = '';
        if (dayEvents.length === 0) {
            listHTML = `<div class="text-muted text-center py-4 small">No immunizations scheduled on this day.</div>`;
        } else {
            dayEvents.forEach(ev => {
                const statusClass = ev.status === 'Completed' ? 'success' : (ev.status === 'Upcoming' ? 'warning' : 'danger');
                listHTML += `
                    <div class="list-group-item border rounded-3 p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark text-truncate" style="max-width: 70%;">${ev.patient}</span>
                            <span class="badge bg-${statusClass} bg-opacity-10 text-${statusClass} border border-${statusClass}-subtle">${ev.status}</span>
                        </div>
                        <div class="small text-muted"><i class="bi bi-shield-plus me-1 text-info"></i>Vaccine: ${ev.vaccine}</div>
                        <div class="small text-muted"><i class="bi bi-layers me-1"></i>Dose: ${ev.dose}</div>
                        <div class="d-flex justify-content-end mt-2">
                            <button type="button" onclick="editImmunization(${ev.id})" class="btn btn-sm btn-light border py-1 px-2.5 small"><i class="bi bi-pencil-square me-1"></i> Update record</button>
                        </div>
                    </div>
                `;
            });
        }
        $('#calendar-day-list').html(listHTML);
    });

    $('#prev-month-btn').on('click', function() {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
    });

    $('#next-month-btn').on('click', function() {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
    });

    // Bind initial calendar build on tab click
    $('button[data-bs-target="#calendar-view"]').on('shown.bs.tab', function () {
        renderCalendar();
    });

});
</script>

<!-- Log Vaccination / Schedule Modal -->
<div class="modal fade" id="logImmunizationModal" tabindex="-1" aria-labelledby="logImmunizationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form action="<?= url('index.php?route=immunization/store') ?>" method="POST" class="modal-content border-0 shadow needs-validation" novalidate id="modal-immunization-form">
            <div class="modal-header bg-primary bg-gradient text-white">
                <h5 class="modal-title fw-bold" id="logImmunizationModalLabel">
                    <i class="bi bi-shield-plus me-2"></i>Log Vaccination / Schedule
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <?= csrf_field() ?>

                <div class="row g-3">
                    <!-- Patient Resident Select -->
                    <div class="col-12 col-md-6">
                        <label for="modal_imm_resident_id" class="form-label fw-semibold small">Resident (Child/Patient) <span class="text-danger">*</span></label>
                        <select class="form-select searchable-select" id="modal_imm_resident_id" name="resident_id" required>
                            <option value="" selected disabled>Select Patient</option>
                            <?php if (!empty($residents)): ?>
                                <?php foreach ($residents as $res): ?>
                                    <option value="<?= $res['id'] ?>">
                                        <?= escape($res['last_name'] . ', ' . $res['first_name'] . ' (' . $res['resident_id'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="invalid-feedback">Please select the vaccine recipient.</div>
                    </div>

                    <!-- Vaccine Selection -->
                    <div class="col-12 col-md-6">
                        <label for="modal_vaccine_id" class="form-label fw-semibold small">Vaccine Item <span class="text-danger">*</span></label>
                        <select class="form-select" id="modal_vaccine_id" name="vaccine_id" required>
                            <option value="" selected disabled>Select Vaccine</option>
                            <?php if (!empty($vaccines)): ?>
                                <?php foreach ($vaccines as $vac): ?>
                                    <option value="<?= $vac['id'] ?>" data-stock="<?= $vac['stock_qty'] ?>">
                                        <?= escape($vac['name'] . ' - Code: ' . $vac['code'] . ' (' . $vac['stock_qty'] . ' in stock)') ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="invalid-feedback">Please select the vaccine.</div>
                    </div>

                    <!-- Dose level -->
                    <div class="col-12 col-md-6">
                        <label for="modal_dose" class="form-label fw-semibold small">Dose Level <span class="text-danger">*</span></label>
                        <select class="form-select" id="modal_dose" name="dose" required>
                            <option value="" selected disabled>Select Dose</option>
                            <option value="1st Dose">1st Dose</option>
                            <option value="2nd Dose">2nd Dose</option>
                            <option value="3rd Dose">3rd Dose</option>
                            <option value="Booster">Booster</option>
                            <option value="Booster 2">Booster 2</option>
                        </select>
                        <div class="invalid-feedback">Please select a dose level.</div>
                    </div>

                    <!-- Status Selection -->
                    <div class="col-12 col-md-6">
                        <label for="modal_imm_status" class="form-label fw-semibold small">Appointment Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="modal_imm_status" name="status" required>
                            <option value="Upcoming" selected>Upcoming Schedule</option>
                            <option value="Completed">Completed (Given)</option>
                            <option value="Missed">Missed Schedule</option>
                        </select>
                    </div>

                    <!-- Date Given -->
                    <div class="col-12 col-md-6">
                        <label for="modal_date_given" class="form-label fw-semibold small">Date Given</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-calendar-check"></i></span>
                            <input type="text" class="form-control datepicker" id="modal_date_given" name="date_given" placeholder="YYYY-MM-DD">
                        </div>
                    </div>

                    <!-- Next Schedule -->
                    <div class="col-12 col-md-6">
                        <label for="modal_next_schedule" class="form-label fw-semibold small">Next Schedule Date</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-calendar-plus"></i></span>
                            <input type="text" class="form-control future-datepicker" id="modal_next_schedule" name="next_schedule" placeholder="YYYY-MM-DD">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-3">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4">Log Immunization</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Immunization Modal -->
<div class="modal fade" id="editImmunizationModal" tabindex="-1" aria-labelledby="editImmunizationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form action="<?= url('index.php?route=immunization/update') ?>" method="POST" class="modal-content border-0 shadow needs-validation" novalidate id="edit-immunization-form">
            <div class="modal-header bg-primary bg-gradient text-white">
                <h5 class="modal-title fw-bold" id="editImmunizationModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>Edit Immunization Record
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <?= csrf_field() ?>
                <input type="hidden" id="edit_imm_id" name="id">

                <div class="row g-3">
                    <!-- Patient Resident Select -->
                    <div class="col-12 col-md-6">
                        <label for="edit_imm_resident_id" class="form-label fw-semibold small">Resident (Child/Patient) <span class="text-danger">*</span></label>
                        <select class="form-select searchable-select" id="edit_imm_resident_id" name="resident_id" required>
                            <option value="" disabled>Select Patient</option>
                            <?php if (!empty($residents)): ?>
                                <?php foreach ($residents as $res): ?>
                                    <option value="<?= $res['id'] ?>">
                                        <?= escape($res['last_name'] . ', ' . $res['first_name'] . ' (' . $res['resident_id'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="invalid-feedback">Please select the vaccine recipient.</div>
                    </div>

                    <!-- Vaccine Selection -->
                    <div class="col-12 col-md-6">
                        <label for="edit_vaccine_id" class="form-label fw-semibold small">Vaccine Item <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_vaccine_id" name="vaccine_id" required>
                            <option value="" disabled>Select Vaccine</option>
                            <?php if (!empty($vaccines)): ?>
                                <?php foreach ($vaccines as $vac): ?>
                                    <option value="<?= $vac['id'] ?>" data-stock="<?= $vac['stock_qty'] ?>">
                                        <?= escape($vac['name'] . ' - Code: ' . $vac['code'] . ' (' . $vac['stock_qty'] . ' in stock)') ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="invalid-feedback">Please select the vaccine.</div>
                    </div>

                    <!-- Dose level -->
                    <div class="col-12 col-md-6">
                        <label for="edit_dose" class="form-label fw-semibold small">Dose Level <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_dose" name="dose" required>
                            <option value="" disabled>Select Dose</option>
                            <option value="1st Dose">1st Dose</option>
                            <option value="2nd Dose">2nd Dose</option>
                            <option value="3rd Dose">3rd Dose</option>
                            <option value="Booster">Booster</option>
                            <option value="Booster 2">Booster 2</option>
                        </select>
                        <div class="invalid-feedback">Please select a dose level.</div>
                    </div>

                    <!-- Status Selection -->
                    <div class="col-12 col-md-6">
                        <label for="edit_imm_status" class="form-label fw-semibold small">Appointment Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_imm_status" name="status" required>
                            <option value="Upcoming">Upcoming Schedule</option>
                            <option value="Completed">Completed (Given)</option>
                            <option value="Missed">Missed Schedule</option>
                        </select>
                    </div>

                    <!-- Date Given -->
                    <div class="col-12 col-md-6">
                        <label for="edit_date_given" class="form-label fw-semibold small">Date Given</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-calendar-check"></i></span>
                            <input type="text" class="form-control datepicker" id="edit_date_given" name="date_given" placeholder="YYYY-MM-DD">
                        </div>
                    </div>

                    <!-- Next Schedule -->
                    <div class="col-12 col-md-6">
                        <label for="edit_next_schedule" class="form-label fw-semibold small">Next Schedule Date</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-calendar-plus"></i></span>
                            <input type="text" class="form-control future-datepicker" id="edit_next_schedule" name="next_schedule" placeholder="YYYY-MM-DD">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-3">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4">Update Profile</button>
            </div>
        </form>
    </div>
</div>
    </div>
</div>

<?php require_once LAYOUT_PATH . 'footer.php'; ?>

<script>
$(document).ready(function() {
    $('#logImmunizationModal').on('shown.bs.modal', function () {
        if (typeof flatpickr !== 'undefined') {
            flatpickr('#modal_date_given', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                maxDate: 'today'
            });
            flatpickr('#modal_next_schedule', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                minDate: 'today'
            });
        }
    });

    $('#editImmunizationModal').on('shown.bs.modal', function () {
        if (typeof flatpickr !== 'undefined') {
            flatpickr('#edit_date_given', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                maxDate: 'today'
            });
            flatpickr('#edit_next_schedule', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                minDate: 'today'
            });
        }
    });
});

function editImmunization(id) {
    App.showLoader();
    $.getJSON(`index.php?route=immunization/detail_json&id=${id}`)
        .done(function(res) {
            App.hideLoader();
            if (res.success) {
                const rec = res.data;
                $('#edit_imm_id').val(rec.id);
                $('#edit_imm_resident_id').val(rec.resident_id);
                $('#edit_vaccine_id').val(rec.vaccine_id);
                $('#edit_dose').val(rec.dose);
                $('#edit_imm_status').val(rec.status);
                $('#edit_date_given').val(rec.date_given || '');
                $('#edit_next_schedule').val(rec.next_schedule || '');

                const myModal = new bootstrap.Modal(document.getElementById('editImmunizationModal'));
                myModal.show();
            } else {
                Swal.fire('Error', res.message || 'Unable to fetch immunization record.', 'error');
            }
        })
        .fail(function() {
            App.hideLoader();
            Swal.fire('Error', 'Communication error happened.', 'error');
        });
}

function confirmDelete(id, name) {
    Swal.fire({
        title: 'Delete Immunization Log?',
        text: `Are you sure you want to permanently delete the vaccination record of ${name}? If marked completed, this will reverse the allocated vaccine stock.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Delete'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `index.php?route=immunization/delete&id=${id}`;
        }
    });
}

// Bootstrap Form Validations for Immunization Modals
(function () {
  'use strict'
  var forms = document.querySelectorAll('#modal-immunization-form, #edit-immunization-form')
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
