<?php
require_once LAYOUT_PATH . 'header.php';
require_once LAYOUT_PATH . 'sidebar.php';

// Fetch active residents and vaccines from database directly in the view
$residents = \App\Models\Resident::getAll('Active');
$vaccines = \App\Models\Medicine::getByCategory('Vaccine');
?>

<div class="main-content-wrapper">
    <?php require_once LAYOUT_PATH . 'navbar.php'; ?>
    
    <div class="container-fluid p-3 p-md-4">
        
        <!-- Header -->
        <div class="mb-4">
            <h3 class="fw-bold mb-1"><?= escape($pageTitle) ?></h3>
            <p class="text-muted small mb-0">Track child vaccinations, booster immunizations, and appointment schedules.</p>
        </div>

        <!-- Navigation Tabs -->
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
                <!-- Real-time Search Box and Action Buttons -->
                <div class="row align-items-center mb-4 g-3">
                    <div class="col-12 col-md-8 col-lg-5">
                        <div class="input-group shadow-sm border rounded bg-white">
                            <span class="input-group-text bg-white border-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" id="immunizationSearch" class="form-control border-0" placeholder="Search immunizations...">
                            <button class="btn btn-light border-start dropdown-toggle text-secondary fw-semibold px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                                <i class="bi bi-funnel"></i> Filters
                            </button>
                            <div class="dropdown-menu dropdown-menu-end p-3 shadow border-0 rounded-3 mt-1" style="width: 260px;">
                                <h6 class="dropdown-header px-0 text-dark fw-bold mb-2">Filter Records</h6>
                                <div class="mb-3">
                                    <label class="form-label small text-muted fw-semibold">Vaccine</label>
                                    <select id="immunizationVaccineFilter" class="form-select form-select-sm shadow-none">
                                        <option value="">All Vaccines</option>
                                        <?php if(isset($vaccines)): foreach($vaccines as $v): ?>
                                            <option value="<?= htmlspecialchars($v['name']) ?>"><?= htmlspecialchars($v['name']) ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small text-muted fw-semibold">Dose</label>
                                    <select id="immunizationDoseFilter" class="form-select form-select-sm shadow-none">
                                        <option value="">All Doses</option>
                                        <option value="1st Dose">1st Dose</option>
                                        <option value="2nd Dose">2nd Dose</option>
                                        <option value="3rd Dose">3rd Dose</option>
                                        <option value="Booster">Booster</option>
                                    </select>
                                </div>
                                <div class="mb-1">
                                    <label class="form-label small text-muted fw-semibold">Status</label>
                                    <select id="immunizationStatusFilter" class="form-select form-select-sm shadow-none">
                                        <option value="">All Statuses</option>
                                        <option value="Scheduled">Scheduled</option>
                                        <option value="Completed">Completed</option>
                                        <option value="Missed">Missed</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 col-lg-7 d-flex justify-content-md-end gap-2">
                        <button type="button" class="btn btn-primary" onclick="newImmunization()">
                            <i class="bi bi-plus-lg me-1"></i> Log Vaccination / Schedule
                        </button>
                    </div>
                </div>

                <!-- Dynamic Immunizations Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th>Patient Name</th>
                                        <th>Vaccine</th>
                                        <th>Dose</th>
                                        <th>Date Administered</th>
                                        <th>Next Schedule</th>
                                        <th>Health Worker</th>
                                        <th>Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="immunizationTable">
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <div class="text-muted mt-2 small">Loading immunization schedule...</div>
                                        </td>
                                    </tr>
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
        .col-7-custom {
            width: 14.285%;
            flex: 0 0 14.285%;
            max-width: 14.285%;
        }
        </style>

    </div>
</div>

<!-- Add/Edit Immunization Modal -->
<div class="modal fade" id="immunizationModal" tabindex="-1" aria-labelledby="immunizationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary bg-gradient text-white">
                <h5 class="modal-title fw-bold" id="immunizationModalLabel">
                    <i class="bi bi-shield-plus-fill me-2"></i><span id="modalTitleText">Log Vaccination / Schedule</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="immunizationForm" class="needs-validation" novalidate>
                <input type="hidden" id="immunizationId" name="id">
                
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Patient Select -->
                        <div class="col-12 col-md-6">
                            <label for="modalResidentId" class="form-label fw-semibold small">Patient (Resident) <span class="text-danger">*</span></label>
                            <select class="form-select searchable-select" id="modalResidentId" name="resident_id" required>
                                <option value="" selected disabled>Select Patient</option>
                                <?php foreach ($residents as $res): ?>
                                    <option value="<?= $res['id'] ?>">
                                        <?= escape($res['last_name'] . ', ' . $res['first_name'] . ' (' . $res['resident_id'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select a patient.</div>
                        </div>

                        <!-- Vaccine Select -->
                        <div class="col-12 col-md-6">
                            <label for="modalVaccineId" class="form-label fw-semibold small">Vaccine (Category) <span class="text-danger">*</span></label>
                            <select class="form-select" id="modalVaccineId" name="vaccine_id" required>
                                <option value="" selected disabled>Select Vaccine</option>
                                <?php foreach ($vaccines as $v): ?>
                                    <option value="<?= $v['id'] ?>" data-stock="<?= $v['stock_qty'] ?>">
                                        <?= escape($v['name'] . ' (Code: ' . $v['code'] . ' - ' . $v['stock_qty'] . ' left)') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted d-block mt-1" id="modalStockHint">Select a vaccine to verify current center stock level.</small>
                            <div class="invalid-feedback">Please select a vaccine.</div>
                        </div>

                        <!-- Dose Number -->
                        <div class="col-12 col-md-6">
                            <label for="modalDose" class="form-label fw-semibold small">Dose Sequence <span class="text-danger">*</span></label>
                            <select class="form-select" id="modalDose" name="dose" required>
                                <option value="" selected disabled>Select Dose Sequence</option>
                                <option value="At Birth">At Birth</option>
                                <option value="1st Dose">1st Dose (6 Weeks)</option>
                                <option value="2nd Dose">2nd Dose (10 Weeks)</option>
                                <option value="3rd Dose">3rd Dose (14 Weeks)</option>
                                <option value="Booster 1">Booster 1 (12-18 Months)</option>
                                <option value="Booster 2">Booster 2 (4-6 Years)</option>
                            </select>
                            <div class="invalid-feedback">Dose sequence is required.</div>
                        </div>

                        <!-- Case Status -->
                        <div class="col-12 col-md-6">
                            <label for="modalStatus" class="form-label fw-semibold small">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="modalStatus" name="status" required>
                                <option value="Upcoming" selected>Upcoming (Appointment Scheduled)</option>
                                <option value="Completed">Completed (Administered)</option>
                                <option value="Missed">Missed (Passed Schedule)</option>
                            </select>
                        </div>

                        <!-- Date Given -->
                        <div class="col-12 col-md-6" id="dateGivenContainer">
                            <label for="modalDateGiven" class="form-label fw-semibold small">Date Administered</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-calendar-check-fill"></i></span>
                                <input type="text" class="form-control datepicker" id="modalDateGiven" name="date_given" placeholder="YYYY-MM-DD">
                            </div>
                            <div class="invalid-feedback">Date administered is required for Completed status.</div>
                        </div>

                        <!-- Next Schedule -->
                        <div class="col-12 col-md-6" id="nextScheduleContainer">
                            <label for="modalNextSchedule" class="form-label fw-semibold small">Next Appointment Schedule</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-calendar-event"></i></span>
                                <input type="text" class="form-control datepicker" id="modalNextSchedule" name="next_schedule" placeholder="YYYY-MM-DD">
                            </div>
                            <div class="d-flex gap-1 mt-1 align-items-center">
                                <small class="text-muted me-1" style="font-size: 0.75rem;">Quick add:</small>
                                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-1.5" style="font-size: 0.7rem;" onclick="addWeeksToSchedule(4)">+4 Wks</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-1.5" style="font-size: 0.7rem;" onclick="addWeeksToSchedule(8)">+8 Wks</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-1.5" style="font-size: 0.7rem;" onclick="addMonthsToSchedule(1)">+1 Mo</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-1.5" style="font-size: 0.7rem;" onclick="addMonthsToSchedule(6)">+6 Mos</button>
                            </div>
                            <div class="invalid-feedback">Next schedule is required for Upcoming status.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4" id="saveButton">Save Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
$pageScript = url('js/immunization_js.js');
require_once LAYOUT_PATH . 'footer.php'; 
?>
