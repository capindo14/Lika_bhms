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
                <p class="text-muted small mb-0">Record clinical checkups, patient symptoms, diagnoses, treatments, and prescriptions.</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#logConsultationModal">
                    <i class="bi bi-plus-lg me-1"></i> Log Consultation
                </button>
            </div>
        </div>

        <!-- DataTable Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table id="consultations-table" class="table table-hover align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Consultation No.</th>
                                <th>Date</th>
                                <th>Resident</th>
                                <th>Diagnosis</th>
                                <th>Medicine Given</th>
                                <th>Health Worker</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($consultations as $c): ?>
                                <tr>
                                    <td class="fw-bold text-primary"><?= escape($c['consultation_no']) ?></td>
                                    <td><?= date('Y-m-d', strtotime($c['consultation_date'])) ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= escape($c['resident_name']) ?></div>
                                        <small class="text-muted"><?= escape($c['resident_code']) ?></small>
                                    </td>
                                    <td><span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle"><?= escape($c['diagnosis']) ?></span></td>
                                    <td>
                                        <?php if (!empty($c['medicine_name'])): ?>
                                            <span class="fw-medium text-success"><?= escape($c['medicine_name']) ?></span>
                                            <small class="text-muted d-block">Qty: <?= $c['medicine_qty'] ?> pcs</small>
                                        <?php else: ?>
                                            <span class="text-muted small">None</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= escape($c['worker_name']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $c['status'] === 'Completed' ? 'success' : ($c['status'] === 'Pending' ? 'warning' : 'danger') ?> bg-opacity-10 text-<?= $c['status'] === 'Completed' ? 'success' : ($c['status'] === 'Pending' ? 'warning' : 'danger') ?> border border-<?= $c['status'] === 'Completed' ? 'success' : ($c['status'] === 'Pending' ? 'warning' : 'danger') ?>-subtle">
                                            <?= escape($c['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group gap-1">
                                            <button onclick="viewConsultation(<?= $c['id'] ?>)" class="btn btn-outline-info btn-sm rounded-2" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-primary btn-sm rounded-2" onclick="editConsultation(<?= $c['id'] ?>)" title="Edit Consultation">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button onclick="confirmDelete(<?= $c['id'] ?>, '<?= escape($c['consultation_no']) ?>')" class="btn btn-outline-danger btn-sm rounded-2" title="Delete Log">
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
</div>

<!-- Modal for Consultation Details & Print -->
<div class="modal fade" id="consultationModal" tabindex="-1" aria-labelledby="consultationModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-primary bg-gradient text-white">
                <h5 class="modal-title fw-bold" id="consultationModalTitle"><i class="bi bi-clipboard2-pulse me-2"></i>Consultation Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4" id="consultationPrintArea">
                <!-- Print Header (Visible in print only) -->
                <div class="d-none d-print-block text-center border-bottom pb-3 mb-4">
                    <h5 class="fw-bold mb-0">Barangay Health Center</h5>
                    <p class="text-muted small mb-0">Patient Consultation Record</p>
                    <p class="text-muted small">Date Generated: <?= date('Y-m-d') ?></p>
                </div>

                <div class="row g-3">
                    <div class="col-6">
                        <small class="text-muted d-block">Consultation No.</small>
                        <span class="fw-bold text-primary" id="modal-con-no"></span>
                    </div>
                    <div class="col-6 text-end">
                        <small class="text-muted d-block">Date of Visit</small>
                        <span class="fw-semibold" id="modal-con-date"></span>
                    </div>
                    
                    <hr class="my-2">
                    
                    <div class="col-12">
                        <small class="text-muted d-block">Patient Name</small>
                        <span class="fw-bold" id="modal-patient-name"></span>
                        <span class="text-muted small ms-1" id="modal-patient-details"></span>
                    </div>

                    <div class="col-12">
                        <small class="text-muted d-block">Symptoms / Complaints</small>
                        <div class="p-2 border rounded bg-light" id="modal-symptoms"></div>
                    </div>

                    <div class="col-12">
                        <small class="text-muted d-block">Diagnosis</small>
                        <div class="p-2 border rounded bg-light fw-medium text-dark" id="modal-diagnosis"></div>
                    </div>

                    <div class="col-12">
                        <small class="text-muted d-block">Treatment Plan</small>
                        <div class="p-2 border rounded bg-light" id="modal-treatment"></div>
                    </div>

                    <div class="col-12" id="modal-medicine-section">
                        <small class="text-muted d-block">Medicine Dispensed</small>
                        <div class="p-2 border rounded bg-success bg-opacity-10 border-success-subtle text-success fw-medium">
                            <i class="bi bi-capsule me-1"></i>
                            <span id="modal-medicine-name"></span>
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="col-12 d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">Health Worker</small>
                            <span class="fw-semibold small" id="modal-worker"></span>
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block">Status</small>
                            <span class="badge" id="modal-status"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Close</button>
                <button type="button" onclick="printConsultation()" class="btn btn-primary px-4">
                    <i class="bi bi-printer me-1"></i> Print prescription
                </button>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    #consultationPrintArea, #consultationPrintArea * {
        visibility: visible;
    }
    #consultationPrintArea {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        border: none !important;
        background: white !important;
    }
}
</style>

<!-- Log Consultation Modal -->
<div class="modal fade" id="logConsultationModal" tabindex="-1" aria-labelledby="logConsultationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form action="<?= url('index.php?route=consultation/store') ?>" method="POST" class="modal-content border-0 shadow needs-validation" novalidate id="modal-consultation-form">
            <div class="modal-header bg-primary bg-gradient text-white">
                <h5 class="modal-title fw-bold" id="logConsultationModalLabel">
                    <i class="bi bi-clipboard2-pulse-fill me-2"></i>Log New Consultation
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <?= csrf_field() ?>

                <div class="row g-3">
                    <!-- Patient Select -->
                    <div class="col-12 col-md-6">
                        <label for="modal_con_resident_id" class="form-label fw-semibold small">Patient (Resident) <span class="text-danger">*</span></label>
                        <select class="form-select" id="modal_con_resident_id" name="resident_id" required>
                            <option value="" selected disabled>Select Patient</option>
                            <?php if (!empty($residents)): ?>
                                <?php foreach ($residents as $res): ?>
                                    <option value="<?= $res['id'] ?>">
                                        <?= escape($res['last_name'] . ', ' . $res['first_name'] . ' (' . $res['resident_id'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="invalid-feedback">Please select a resident patient.</div>
                    </div>

                    <!-- Consultation Date -->
                    <div class="col-12 col-md-6">
                        <label for="modal_consultation_date" class="form-label fw-semibold small">Consultation Date <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-calendar-check"></i></span>
                            <input type="text" class="form-control datepicker" id="modal_consultation_date" name="consultation_date" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="invalid-feedback">Date of visit is required.</div>
                    </div>

                    <!-- Symptoms -->
                    <div class="col-12">
                        <label for="modal_symptoms" class="form-label fw-semibold small">Symptoms / Patient Complaints <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="modal_symptoms" name="symptoms" rows="2" required placeholder="Describe symptoms, duration, temperature, vitals, etc."></textarea>
                        <div class="invalid-feedback">Patient symptoms are required.</div>
                    </div>

                    <!-- Diagnosis -->
                    <div class="col-12 col-md-6">
                        <label for="modal_diagnosis" class="form-label fw-semibold small">Diagnosis <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="modal_diagnosis" name="diagnosis" required placeholder="e.g. Acute Bronchitis, Hypertension">
                        <div class="invalid-feedback">Diagnosis is required.</div>
                    </div>

                    <!-- Status -->
                    <div class="col-12 col-md-6">
                        <label for="modal_con_status" class="form-label fw-semibold small">Consultation Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="modal_con_status" name="status" required>
                            <option value="Completed" selected>Completed</option>
                            <option value="Pending">Pending</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>

                    <!-- Treatment -->
                    <div class="col-12">
                        <label for="modal_treatment" class="form-label fw-semibold small">Treatment Plan / Instructions <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="modal_treatment" name="treatment" rows="2" required placeholder="e.g. Rest for 3 days, drink plenty of water, dosage timeline..."></textarea>
                        <div class="invalid-feedback">Treatment details are required.</div>
                    </div>

                    <!-- Divider for Dispensation -->
                    <div class="col-12 my-1"><hr></div>
                    <h6 class="fw-bold text-success mb-0"><i class="bi bi-capsule-prescription me-2"></i>Dispense Medicine (Optional)</h6>

                    <!-- Medicine Selection -->
                    <div class="col-12 col-md-8">
                        <label for="modal_medicine_id" class="form-label fw-semibold small">Medicine / Supply</label>
                        <select class="form-select" id="modal_medicine_id" name="medicine_id">
                            <option value="" selected>No prescription allocation</option>
                            <?php if (!empty($medicines)): ?>
                                <?php foreach ($medicines as $med): ?>
                                    <option value="<?= $med['id'] ?>" data-stock="<?= $med['stock_qty'] ?>">
                                        <?= escape($med['name'] . ' [' . $med['category'] . '] - Code: ' . $med['code'] . ' (' . $med['stock_qty'] . ' available)') ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Quantity -->
                    <div class="col-12 col-md-4">
                        <label for="modal_medicine_qty" class="form-label fw-semibold small">Quantity to Dispense</label>
                        <input type="number" class="form-control" id="modal_medicine_qty" name="medicine_qty" min="1" placeholder="e.g. 10">
                        <small class="text-muted" id="modal-stock-hint">Select a medicine to view stock limit.</small>
                        <div class="invalid-feedback" id="modal-qty-feedback">Please enter valid quantity.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-3">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4">Log Consultation</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Consultation Modal -->
<div class="modal fade" id="editConsultationModal" tabindex="-1" aria-labelledby="editConsultationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form action="<?= url('index.php?route=consultation/update') ?>" method="POST" class="modal-content border-0 shadow needs-validation" novalidate id="edit-consultation-form">
            <div class="modal-header bg-primary bg-gradient text-white">
                <h5 class="modal-title fw-bold" id="editConsultationModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>Edit Consultation Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <?= csrf_field() ?>
                <input type="hidden" id="edit_con_id" name="id">

                <div class="row g-3">
                    <!-- Patient Select -->
                    <div class="col-12 col-md-6">
                        <label for="edit_con_resident_id" class="form-label fw-semibold small">Patient (Resident) <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_con_resident_id" name="resident_id" required>
                            <option value="" disabled>Select Patient</option>
                            <?php if (!empty($residents)): ?>
                                <?php foreach ($residents as $res): ?>
                                    <option value="<?= $res['id'] ?>">
                                        <?= escape($res['last_name'] . ', ' . $res['first_name'] . ' (' . $res['resident_id'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="invalid-feedback">Please select a resident patient.</div>
                    </div>

                    <!-- Consultation Date -->
                    <div class="col-12 col-md-6">
                        <label for="edit_consultation_date" class="form-label fw-semibold small">Consultation Date <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-calendar-check"></i></span>
                            <input type="text" class="form-control datepicker" id="edit_consultation_date" name="consultation_date" required>
                        </div>
                        <div class="invalid-feedback">Date of visit is required.</div>
                    </div>

                    <!-- Symptoms -->
                    <div class="col-12">
                        <label for="edit_symptoms" class="form-label fw-semibold small">Symptoms / Patient Complaints <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="edit_symptoms" name="symptoms" rows="2" required placeholder="Describe symptoms..."></textarea>
                        <div class="invalid-feedback">Patient symptoms are required.</div>
                    </div>

                    <!-- Diagnosis -->
                    <div class="col-12 col-md-6">
                        <label for="edit_diagnosis" class="form-label fw-semibold small">Diagnosis <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_diagnosis" name="diagnosis" required placeholder="e.g. Acute Bronchitis">
                        <div class="invalid-feedback">Diagnosis is required.</div>
                    </div>

                    <!-- Status -->
                    <div class="col-12 col-md-6">
                        <label for="edit_con_status" class="form-label fw-semibold small">Consultation Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_con_status" name="status" required>
                            <option value="Completed">Completed</option>
                            <option value="Pending">Pending</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>

                    <!-- Treatment -->
                    <div class="col-12">
                        <label for="edit_treatment" class="form-label fw-semibold small">Treatment Plan / Instructions <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="edit_treatment" name="treatment" rows="2" required placeholder="Describe treatment..."></textarea>
                        <div class="invalid-feedback">Treatment details are required.</div>
                    </div>

                    <!-- Divider for Dispensation -->
                    <div class="col-12 my-1"><hr></div>
                    <h6 class="fw-bold text-success mb-0"><i class="bi bi-capsule-prescription me-2"></i>Dispense Medicine (Optional)</h6>

                    <!-- Medicine Selection -->
                    <div class="col-12 col-md-8">
                        <label for="edit_medicine_id" class="form-label fw-semibold small">Medicine / Supply</label>
                        <select class="form-select" id="edit_medicine_id" name="medicine_id">
                            <option value="">No prescription allocation</option>
                            <?php if (!empty($medicines)): ?>
                                <?php foreach ($medicines as $med): ?>
                                    <option value="<?= $med['id'] ?>" data-stock="<?= $med['stock_qty'] ?>">
                                        <?= escape($med['name'] . ' [' . $med['category'] . '] - Code: ' . $med['code'] . ' (' . $med['stock_qty'] . ' available)') ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Quantity -->
                    <div class="col-12 col-md-4">
                        <label for="edit_medicine_qty" class="form-label fw-semibold small">Quantity to Dispense</label>
                        <input type="number" class="form-control" id="edit_medicine_qty" name="medicine_qty" min="1" placeholder="e.g. 10">
                        <small class="text-muted" id="edit-stock-hint">Select a medicine to view stock limit.</small>
                        <div class="invalid-feedback" id="edit-qty-feedback">Please enter valid quantity.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-3">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4">Save Edit</button>
            </div>
        </form>
    </div>
</div>

<?php require_once LAYOUT_PATH . 'footer.php'; ?>

<script>
$(document).ready(function() {
    $('#consultations-table').DataTable({
        responsive: true,
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: false, targets: 7 }
        ]
    });

    $('#logConsultationModal').on('shown.bs.modal', function () {
        if (typeof flatpickr !== 'undefined') {
            flatpickr('#modal_consultation_date', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                maxDate: 'today'
            });
        }
    });

    $('#editConsultationModal').on('shown.bs.modal', function () {
        if (typeof flatpickr !== 'undefined') {
            const currentVal = $('#edit_consultation_date').val();
            flatpickr('#edit_consultation_date', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                defaultDate: currentVal || 'today',
                maxDate: 'today'
            });
        }
    });

    $('#modal_medicine_id').on('change', function() {
        const stock = $(this).find(':selected').data('stock');
        if (stock !== undefined && stock !== '') {
            $('#modal_medicine_qty').attr('max', stock);
            $('#modal-stock-hint').text(`Available stock: ${stock} pcs`).addClass('text-success').removeClass('text-muted');
        } else {
            $('#modal_medicine_qty').removeAttr('max').val('');
            $('#modal-stock-hint').text('Select a medicine to view stock limit.').addClass('text-muted').removeClass('text-success');
        }
    });

    $('#edit_medicine_id').on('change', function() {
        const stock = $(this).find(':selected').data('stock');
        if (stock !== undefined && stock !== '') {
            $('#edit_medicine_qty').attr('max', stock);
            $('#edit-stock-hint').text(`Available stock: ${stock} pcs`).addClass('text-success').removeClass('text-muted');
        } else {
            $('#edit_medicine_qty').removeAttr('max').val('');
            $('#edit-stock-hint').text('Select a medicine to view stock limit.').addClass('text-muted').removeClass('text-success');
        }
    });
});

function viewConsultation(id) {
    App.showLoader();
    
    $.getJSON(`index.php?route=consultation/detail_json&id=${id}`)
        .done(function(res) {
            App.hideLoader();
            if (res.success) {
                const c = res.data;
                $('#modal-con-no').text(c.consultation_no);
                $('#modal-con-date').text(c.consultation_date);
                $('#modal-patient-name').text(c.resident_name);
                $('#modal-patient-details').text(`(${c.resident_gender}, ${c.resident_age} yrs, ${c.resident_code})`);
                $('#modal-symptoms').text(c.symptoms);
                $('#modal-diagnosis').text(c.diagnosis);
                $('#modal-treatment').text(c.treatment);
                
                if (c.medicine_name) {
                    $('#modal-medicine-name').text(`${c.medicine_name} (${c.medicine_qty} pcs)`);
                    $('#modal-medicine-section').show();
                } else {
                    $('#modal-medicine-section').hide();
                }
                
                $('#modal-worker').text(c.worker_name);
                $('#modal-status').text(c.status)
                    .removeClass('bg-success bg-warning bg-danger bg-opacity-10 text-success text-warning text-danger')
                    .addClass(`bg-${c.status === 'Completed' ? 'success' : (c.status === 'Pending' ? 'warning' : 'danger')} bg-opacity-10 text-${c.status === 'Completed' ? 'success' : (c.status === 'Pending' ? 'warning' : 'danger')}`);
                
                const myModal = new bootstrap.Modal(document.getElementById('consultationModal'));
                myModal.show();
            } else {
                Swal.fire('Error', 'Failed to retrieve consultation details.', 'error');
            }
        })
        .fail(function() {
            App.hideLoader();
            Swal.fire('Error', 'Communication error happened.', 'error');
        });
}

function editConsultation(id) {
    App.showLoader();
    $.getJSON(`index.php?route=consultation/detail_json&id=${id}`)
        .done(function(res) {
            App.hideLoader();
            if (res.success) {
                const c = res.data;
                $('#edit_con_id').val(c.id);
                $('#edit_con_resident_id').val(c.resident_id);
                $('#edit_consultation_date').val(c.consultation_date);
                $('#edit_symptoms').val(c.symptoms);
                $('#edit_diagnosis').val(c.diagnosis);
                $('#edit_con_status').val(c.status);
                $('#edit_treatment').val(c.treatment);
                $('#edit_medicine_id').val(c.medicine_id || '').trigger('change');
                $('#edit_medicine_qty').val(c.medicine_qty || '');

                const myModal = new bootstrap.Modal(document.getElementById('editConsultationModal'));
                myModal.show();

                if (typeof flatpickr !== 'undefined') {
                    flatpickr('#edit_consultation_date', {
                        dateFormat: 'Y-m-d',
                        allowInput: true,
                        defaultDate: c.consultation_date,
                        maxDate: 'today'
                    });
                }
            } else {
                Swal.fire('Error', res.message || 'Unable to fetch consultation details.', 'error');
            }
        })
        .fail(function() {
            App.hideLoader();
            Swal.fire('Error', 'Communication error happened.', 'error');
        });
}

function printConsultation() {
    window.print();
}

function confirmDelete(id, code) {
    Swal.fire({
        title: 'Delete Consultation Record?',
        text: `Are you sure you want to permanently delete consultation ${code}? This will reverse any medicine stock distributions linked to it.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Delete'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `index.php?route=consultation/delete&id=${id}`;
        }
    });
}

// Bootstrap Form Validations for Consultation Modals
(function () {
  'use strict'
  var forms = document.querySelectorAll('#modal-consultation-form, #edit-consultation-form')
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
