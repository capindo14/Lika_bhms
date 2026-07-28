<?php
require_once LAYOUT_PATH . 'header.php';
require_once LAYOUT_PATH . 'sidebar.php';
?>

<div class="main-content-wrapper">
    <?php require_once LAYOUT_PATH . 'navbar.php'; ?>
    
    <div class="container-fluid p-3 p-md-4">
        
        <!-- Header -->
        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between mb-4 gap-2">
            <div>
                <h3 class="fw-bold mb-1"><?= escape($pageTitle) ?></h3>
                <p class="text-muted small mb-0">Manage profile data, health card identifiers, and household classifications.</p>
            </div>
            <div>
                <?php if ($status === 'Active'): ?>
                    <a href="<?= url('index.php?route=residents&status=Archived') ?>" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-archive-fill me-1"></i> Archived Profiles
                    </a>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registerResidentModal">
                        <i class="bi bi-plus-lg me-1"></i> Register Resident
                    </button>
                <?php else: ?>
                    <a href="<?= url('index.php?route=residents') ?>" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Directory
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- DataTable Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table id="residents-table" class="table table-hover align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Resident ID</th>
                                <th>Name</th>
                                <th>Gender</th>
                                <th>Age</th>
                                <th>Civil Status</th>
                                <th>Contact</th>
                                <th>Household Role</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($residents as $res): ?>
                                <tr>
                                    <td class="fw-bold text-primary"><?= escape($res['resident_id']) ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= escape($res['last_name'] . ', ' . $res['first_name'] . ' ' . $res['middle_name']) ?></div>
                                        <small class="text-muted text-truncate d-block" style="max-width: 200px;"><?= escape($res['address']) ?></small>
                                    </td>
                                    <td>
                                        <?= gender_badge($res['gender']) ?>
                                    </td>
                                    <td><?= escape($res['age']) ?> yrs</td>
                                    <td><?= escape($res['civil_status']) ?></td>
                                    <td><?= escape($res['contact_number'] ?: 'N/A') ?></td>
                                    <td>
                                        <?php if ($res['is_family_head']): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle">Family Head</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">Member</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group gap-1">
                                            <button type="button" class="btn btn-outline-info btn-sm rounded-2" onclick="viewResidentProfile(<?= $res['id'] ?>)" title="View Profile">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-primary btn-sm rounded-2" onclick="editResidentProfile(<?= $res['id'] ?>)" title="Edit Profile">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <?php if ($status === 'Active'): ?>
                                                <button onclick="confirmArchive(<?= $res['id'] ?>, '<?= escape($res['first_name'] . ' ' . $res['last_name']) ?>')" class="btn btn-outline-warning btn-sm rounded-2" title="Archive Profile">
                                                    <i class="bi bi-archive"></i>
                                                </button>
                                            <?php else: ?>
                                                <button onclick="confirmRestore(<?= $res['id'] ?>, '<?= escape($res['first_name'] . ' ' . $res['last_name']) ?>')" class="btn btn-outline-success btn-sm rounded-2" title="Restore Profile">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </button>
                                            <?php endif; ?>
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
</div>

<!-- Register Resident Modal -->
<div class="modal fade" id="registerResidentModal" tabindex="-1" aria-labelledby="registerResidentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form action="<?= url('index.php?route=residents/store') ?>" method="POST" class="modal-content border-0 shadow needs-validation" novalidate id="register-resident-form">
            <div class="modal-header bg-primary bg-gradient text-white">
                <h5 class="modal-title fw-bold" id="registerResidentModalLabel">
                    <i class="bi bi-person-plus-fill me-2"></i>Register New Resident
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <?= csrf_field() ?>

                <div class="row g-3">
                    <!-- First Name -->
                    <div class="col-12 col-md-4">
                        <label for="modal_first_name" class="form-label fw-semibold small">First Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="modal_first_name" name="first_name" required placeholder="Enter first name">
                        <div class="invalid-feedback">First name is required.</div>
                    </div>

                    <!-- Middle Name -->
                    <div class="col-12 col-md-4">
                        <label for="modal_middle_name" class="form-label fw-semibold small">Middle Name</label>
                        <input type="text" class="form-control" id="modal_middle_name" name="middle_name" placeholder="Enter middle name">
                    </div>

                    <!-- Last Name -->
                    <div class="col-12 col-md-4">
                        <label for="modal_last_name" class="form-label fw-semibold small">Last Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="modal_last_name" name="last_name" required placeholder="Enter last name">
                        <div class="invalid-feedback">Last name is required.</div>
                    </div>

                    <!-- Gender -->
                    <div class="col-12 col-md-4">
                        <label for="modal_gender" class="form-label fw-semibold small">Gender <span class="text-danger">*</span></label>
                        <select class="form-select" id="modal_gender" name="gender" required>
                            <option value="" selected disabled>Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                        <div class="invalid-feedback">Please select a gender.</div>
                    </div>

                    <!-- Birthdate -->
                    <div class="col-12 col-md-4">
                        <label for="modal_birthdate" class="form-label fw-semibold small">Birthdate <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-calendar-event"></i></span>
                            <input type="text" class="form-control datepicker" id="modal_birthdate" name="birthdate" required placeholder="YYYY-MM-DD">
                        </div>
                        <div class="invalid-feedback">Please enter a valid birthdate.</div>
                    </div>

                    <!-- Civil Status -->
                    <div class="col-12 col-md-4">
                        <label for="modal_civil_status" class="form-label fw-semibold small">Civil Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="modal_civil_status" name="civil_status" required>
                            <option value="" selected disabled>Select Civil Status</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Widowed">Widowed</option>
                            <option value="Divorced">Divorced</option>
                        </select>
                        <div class="invalid-feedback">Please select civil status.</div>
                    </div>

                    <!-- Contact Number -->
                    <div class="col-12 col-md-6">
                        <label for="modal_contact_number" class="form-label fw-semibold small">Contact Number</label>
                        <input type="text" class="form-control" id="modal_contact_number" name="contact_number" placeholder="e.g. 09171234567" pattern="^(09|\+639)\d{9}$">
                        <small class="text-muted d-block mt-1">Format: 11 digit mobile number starting with 09.</small>
                        <div class="invalid-feedback">Please enter a valid Philippine mobile number.</div>
                    </div>

                    <!-- Barangay -->
                    <div class="col-12 col-md-6">
                        <label for="modal_barangay" class="form-label fw-semibold small">Barangay Center Jurisdiction <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="modal_barangay" name="barangay" value="Barangay Health Center" required>
                        <div class="invalid-feedback">Please input Barangay.</div>
                    </div>

                    <!-- Complete Address -->
                    <div class="col-12">
                        <label for="modal_address" class="form-label fw-semibold small">Complete Address <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="modal_address" name="address" rows="2" required placeholder="Street name, block, lot, zone/sitio..."></textarea>
                        <div class="invalid-feedback">Complete address is required.</div>
                    </div>

                    <!-- Family Head Designation -->
                    <div class="col-12">
                        <div class="form-check form-switch p-3 border rounded-3 bg-light bg-opacity-50">
                            <input class="form-check-input ms-0 me-2" type="checkbox" role="switch" id="modal_is_family_head" name="is_family_head" value="1">
                            <label class="form-check-label fw-semibold small" for="modal_is_family_head">Designate as Family Head</label>
                            <span class="d-block text-muted small mt-1">If active, this resident can be referenced as the family head when building Family Profiles.</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-3">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4">Register Resident</button>
            </div>
        </form>
    </div>
</div>

<!-- View Resident Profile Modal -->
<div class="modal fade" id="viewResidentModal" tabindex="-1" aria-labelledby="viewResidentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info bg-gradient text-white">
                <h5 class="modal-title fw-bold" id="viewResidentModalLabel">
                    <i class="bi bi-person-bounding-box me-2"></i>Resident Profile Overview
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Resident Details Summary Card -->
                <div class="d-flex align-items-center justify-content-between p-3 border rounded bg-light mb-4">
                    <div>
                        <h4 class="fw-bold mb-1 text-dark" id="view-res-name">Loading...</h4>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-primary px-2.5 py-1.5" id="view-res-code">---</span>
                            <span id="view-res-gender-badge">---</span>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2.5 py-1.5" id="view-res-age">-- yrs</span>
                            <span id="view-res-head-badge"></span>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <small class="text-muted d-block">Birthdate</small>
                        <div class="fw-semibold text-dark" id="view-res-birthdate">---</div>
                    </div>
                    <div class="col-12 col-md-4">
                        <small class="text-muted d-block">Civil Status</small>
                        <div class="fw-semibold text-dark" id="view-res-civil">---</div>
                    </div>
                    <div class="col-12 col-md-4">
                        <small class="text-muted d-block">Contact Number</small>
                        <div class="fw-semibold text-dark" id="view-res-contact">---</div>
                    </div>
                    <div class="col-12">
                        <small class="text-muted d-block">Full Residential Address</small>
                        <div class="p-2 border rounded bg-light fw-medium text-dark" id="view-res-address">---</div>
                    </div>
                </div>

                <!-- History Nav Tabs -->
                <ul class="nav nav-tabs border-bottom mb-3" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-bold small py-2" id="res-con-tab" data-bs-toggle="tab" data-bs-target="#res-con-pane" type="button" role="tab">
                            <i class="bi bi-clipboard2-pulse me-1"></i> Consultations (<span id="view-res-con-count">0</span>)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-bold small py-2" id="res-imm-tab" data-bs-toggle="tab" data-bs-target="#res-imm-pane" type="button" role="tab">
                            <i class="bi bi-shield-plus me-1"></i> Immunizations (<span id="view-res-imm-count">0</span>)
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="res-con-pane" role="tabpanel">
                        <div id="view-res-con-list">
                            <!-- Populated dynamically -->
                        </div>
                    </div>
                    <div class="tab-pane fade" id="res-imm-pane" role="tabpanel">
                        <div id="view-res-imm-list">
                            <!-- Populated dynamically -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-3">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Resident Profile Modal -->
<div class="modal fade" id="editResidentModal" tabindex="-1" aria-labelledby="editResidentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form action="<?= url('index.php?route=residents/update') ?>" method="POST" class="modal-content border-0 shadow needs-validation" novalidate id="edit-resident-form">
            <div class="modal-header bg-primary bg-gradient text-white">
                <h5 class="modal-title fw-bold" id="editResidentModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>Edit Resident Profile
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <?= csrf_field() ?>
                <input type="hidden" id="edit_resident_id" name="id">

                <div class="row g-3">
                    <!-- First Name -->
                    <div class="col-12 col-md-4">
                        <label for="edit_first_name" class="form-label fw-semibold small">First Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_first_name" name="first_name" required placeholder="Enter first name">
                        <div class="invalid-feedback">First name is required.</div>
                    </div>

                    <!-- Middle Name -->
                    <div class="col-12 col-md-4">
                        <label for="edit_middle_name" class="form-label fw-semibold small">Middle Name</label>
                        <input type="text" class="form-control" id="edit_middle_name" name="middle_name" placeholder="Enter middle name">
                    </div>

                    <!-- Last Name -->
                    <div class="col-12 col-md-4">
                        <label for="edit_last_name" class="form-label fw-semibold small">Last Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_last_name" name="last_name" required placeholder="Enter last name">
                        <div class="invalid-feedback">Last name is required.</div>
                    </div>

                    <!-- Gender -->
                    <div class="col-12 col-md-4">
                        <label for="edit_gender" class="form-label fw-semibold small">Gender <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_gender" name="gender" required>
                            <option value="" disabled>Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                        <div class="invalid-feedback">Please select gender.</div>
                    </div>

                    <!-- Birthdate -->
                    <div class="col-12 col-md-4">
                        <label for="edit_birthdate" class="form-label fw-semibold small">Birthdate <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-calendar"></i></span>
                            <input type="text" class="form-control datepicker" id="edit_birthdate" name="birthdate" required placeholder="YYYY-MM-DD">
                        </div>
                        <div class="invalid-feedback">Birthdate is required.</div>
                    </div>

                    <!-- Civil Status -->
                    <div class="col-12 col-md-4">
                        <label for="edit_civil_status" class="form-label fw-semibold small">Civil Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_civil_status" name="civil_status" required>
                            <option value="" disabled>Select Civil Status</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Widowed">Widowed</option>
                            <option value="Separated">Separated</option>
                        </select>
                        <div class="invalid-feedback">Please select civil status.</div>
                    </div>

                    <!-- Contact Number -->
                    <div class="col-12 col-md-6">
                        <label for="edit_contact_number" class="form-label fw-semibold small">Contact Number</label>
                        <input type="text" class="form-control" id="edit_contact_number" name="contact_number" placeholder="e.g. 09123456789">
                    </div>

                    <!-- Family Head Checkbox -->
                    <div class="col-12 col-md-6 d-flex align-items-end">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="edit_is_family_head" name="is_family_head" value="1">
                            <label class="form-check-label fw-semibold small" for="edit_is_family_head">Designate as Household Head</label>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="col-12">
                        <label for="edit_address" class="form-label fw-semibold small">House Address <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="edit_address" name="address" rows="2" required placeholder="Purok / Street / Block & Lot, Barangay Name"></textarea>
                        <div class="invalid-feedback">House address is required.</div>
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

<?php require_once LAYOUT_PATH . 'footer.php'; ?>

<script>
$(document).ready(function() {
    $('#residents-table').DataTable({
        responsive: true,
        order: [[1, 'asc']], // Sort by Name
        columnDefs: [
            { orderable: false, targets: 7 } // Disable ordering on Actions column
        ]
    });

    $('#editResidentModal').on('shown.bs.modal', function () {
        if (typeof flatpickr !== 'undefined') {
            flatpickr('#edit_birthdate', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                maxDate: 'today'
            });
        }
    });
});

function viewResidentProfile(id) {
    App.showLoader();
    $.getJSON(`index.php?route=residents/detail_json&id=${id}`)
        .done(function(res) {
            App.hideLoader();
            if (res.success) {
                const r = res.data.resident;
                const cons = res.data.consultations || [];
                const imms = res.data.immunizations || [];

                $('#view-res-name').text(`${r.last_name}, ${r.first_name} ${r.middle_name || ''}`);
                $('#view-res-code').text(r.resident_id);
                $('#view-res-age').text(`${r.age} yrs`);
                $('#view-res-birthdate').text(r.birthdate);
                $('#view-res-civil').text(r.civil_status);
                $('#view-res-contact').text(r.contact_number || 'N/A');
                $('#view-res-address').text(r.address);

                // Badges
                const gBadge = r.gender === 'Male' 
                    ? '<span class="badge badge-gender-male"><i class="bi bi-gender-male me-1"></i> Male</span>'
                    : (r.gender === 'Female' ? '<span class="badge badge-gender-female"><i class="bi bi-gender-female me-1"></i> Female</span>' : '<span class="badge badge-gender-other"><i class="bi bi-gender-ambiguous me-1"></i> Other</span>');
                $('#view-res-gender-badge').html(gBadge);

                if (parseInt(r.is_family_head) === 1) {
                    $('#view-res-head-badge').html('<span class="badge bg-success bg-opacity-10 text-success border border-success-subtle">Family Head</span>');
                } else {
                    $('#view-res-head-badge').html('<span class="badge bg-light text-muted border">Member</span>');
                }

                // Consultations count & list
                $('#view-res-con-count').text(cons.length);
                if (cons.length === 0) {
                    $('#view-res-con-list').html('<div class="text-muted text-center py-3 small">No consultation history recorded for this resident.</div>');
                } else {
                    let conHtml = '<div class="list-group list-group-flush">';
                    cons.forEach(c => {
                        conHtml += `
                            <div class="list-group-item px-0 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong class="text-dark small">${c.diagnosis}</strong>
                                    <span class="small text-muted">${c.consultation_date}</span>
                                </div>
                                <div class="small text-muted">Symptoms: ${c.symptoms}</div>
                                <div class="small text-secondary">Worker: ${c.worker_name} ${c.medicine_name ? '• Medicine: ' + c.medicine_name : ''}</div>
                            </div>
                        `;
                    });
                    conHtml += '</div>';
                    $('#view-res-con-list').html(conHtml);
                }

                // Immunizations count & list
                $('#view-res-imm-count').text(imms.length);
                if (imms.length === 0) {
                    $('#view-res-imm-list').html('<div class="text-muted text-center py-3 small">No immunization records recorded for this resident.</div>');
                } else {
                    let immHtml = '<div class="list-group list-group-flush">';
                    imms.forEach(i => {
                        immHtml += `
                            <div class="list-group-item px-0 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong class="text-dark small">${i.vaccine_name} (${i.dose})</strong>
                                    <span class="badge bg-${i.status === 'Completed' ? 'success' : 'warning'} bg-opacity-10 text-${i.status === 'Completed' ? 'success' : 'warning'}">${i.status}</span>
                                </div>
                                <div class="small text-muted">Date: ${i.date_given || 'Scheduled: ' + i.next_schedule}</div>
                            </div>
                        `;
                    });
                    immHtml += '</div>';
                    $('#view-res-imm-list').html(immHtml);
                }

                const myModal = new bootstrap.Modal(document.getElementById('viewResidentModal'));
                myModal.show();
            } else {
                Swal.fire('Error', res.message || 'Unable to fetch resident details.', 'error');
            }
        })
        .fail(function() {
            App.hideLoader();
            Swal.fire('Error', 'Communication error happened.', 'error');
        });
}

function editResidentProfile(id) {
    App.showLoader();
    $.getJSON(`index.php?route=residents/detail_json&id=${id}`)
        .done(function(res) {
            App.hideLoader();
            if (res.success) {
                const r = res.data.resident;
                $('#edit_resident_id').val(r.id);
                $('#edit_first_name').val(r.first_name);
                $('#edit_middle_name').val(r.middle_name);
                $('#edit_last_name').val(r.last_name);
                $('#edit_gender').val(r.gender);
                $('#edit_birthdate').val(r.birthdate);
                $('#edit_civil_status').val(r.civil_status);
                $('#edit_contact_number').val(r.contact_number);
                $('#edit_address').val(r.address);
                $('#edit_is_family_head').prop('checked', parseInt(r.is_family_head) === 1);

                const myModal = new bootstrap.Modal(document.getElementById('editResidentModal'));
                myModal.show();
            } else {
                Swal.fire('Error', res.message || 'Unable to fetch resident details.', 'error');
            }
        })
        .fail(function() {
            App.hideLoader();
            Swal.fire('Error', 'Communication error happened.', 'error');
        });
}

function confirmArchive(id, name) {
    Swal.fire({
        title: 'Archive Resident?',
        text: `Are you sure you want to archive the profile of ${name}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#10b981', // green
        cancelButtonColor: '#ef4444', // red
        confirmButtonText: 'Yes, Archive'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `index.php?route=residents/archive&id=${id}`;
        }
    });
}

function confirmRestore(id, name) {
    Swal.fire({
        title: 'Restore Resident?',
        text: `Are you sure you want to restore the profile of ${name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981', // emerald-500
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Restore'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `index.php?route=residents/restore&id=${id}`;
        }
    });
}

// Re-initialize Flatpickr inside Register Modal when opened
$('#registerResidentModal').on('shown.bs.modal', function () {
    if (typeof flatpickr !== 'undefined') {
        flatpickr('#modal_birthdate', {
            dateFormat: 'Y-m-d',
            allowInput: true,
            maxDate: 'today'
        });
    }
});

// Bootstrap client-side validation for modal forms
(function () {
  'use strict'
  var forms = document.querySelectorAll('#register-resident-form, #edit-resident-form')
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
