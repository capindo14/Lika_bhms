<?php
require_once LAYOUT_PATH . 'header.php';
require_once LAYOUT_PATH . 'sidebar.php';

// Fetch active residents and medicines directly in the view
$residents = \App\Models\Resident::getAll('Active');
$medicines = \App\Models\Medicine::getAll();
?>

<div class="main-content-wrapper">
    <?php require_once LAYOUT_PATH . 'navbar.php'; ?>
    
    <div class="container-fluid p-3 p-md-4">
        
        <!-- Header -->
        <div class="mb-4">
            <h3 class="fw-bold mb-1"><?= escape($pageTitle) ?></h3>
            <p class="text-muted small mb-0">Record clinical checkups, patient symptoms, diagnoses, treatments, and prescriptions.</p>
        </div>

        <!-- Real-time Search Box and Action Buttons -->
        <div class="row align-items-center mb-4 g-3">
            <div class="col-12 col-md-6 col-lg-4">
                <div class="input-group shadow-sm border rounded">
                    <span class="input-group-text bg-white border-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="consultationSearch" class="form-control border-0" placeholder="Search consultations...">
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-8 d-flex justify-content-md-end gap-2">
                <button type="button" class="btn btn-primary" onclick="newConsultation()">
                    <i class="bi bi-plus-lg me-1"></i> Log Consultation
                </button>
            </div>
        </div>

        <!-- Dynamic Consultations Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th>CON No.</th>
                                <th>Date</th>
                                <th>Patient Name</th>
                                <th>Diagnosis</th>
                                <th>Dispensed Medicine</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="consultationTable">
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <div class="text-muted mt-2 small">Loading consultation list...</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Log/Edit Consultation Modal -->
<div class="modal fade" id="consultationModal" tabindex="-1" aria-labelledby="consultationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary bg-gradient text-white">
                <h5 class="modal-title fw-bold" id="consultationModalLabel">
                    <i class="bi bi-clipboard2-pulse-fill me-2"></i><span id="modalTitleText">Log New Consultation</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="consultationForm" class="needs-validation" novalidate>
                <input type="hidden" id="consultationId" name="id">
                
                <div class="modal-body p-4" style="max-height: 60vh; overflow-y: auto;">
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
                            <div class="invalid-feedback">Please select a resident patient.</div>
                        </div>

                        <!-- Consultation Date -->
                        <div class="col-12 col-md-6">
                            <label for="modalDate" class="form-label fw-semibold small">Consultation Date <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-calendar-check"></i></span>
                                <input type="text" class="form-control datepicker" id="modalDate" name="consultation_date" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="invalid-feedback">Date of visit is required.</div>
                        </div>

                        <!-- Symptoms -->
                        <div class="col-12">
                            <label for="modalSymptoms" class="form-label fw-semibold small">Symptoms / Patient Complaints <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="modalSymptoms" name="symptoms" rows="2" required placeholder="Describe symptoms, temperature, vitals, etc..."></textarea>
                            <div class="invalid-feedback">Patient symptoms are required.</div>
                        </div>

                        <!-- Diagnosis -->
                        <div class="col-12 col-md-6">
                            <label for="modalDiagnosis" class="form-label fw-semibold small">Diagnosis <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modalDiagnosis" name="diagnosis" required placeholder="e.g. Acute Bronchitis, Hypertension">
                            <div class="invalid-feedback">Diagnosis is required.</div>
                        </div>

                        <!-- Status -->
                        <div class="col-12 col-md-6">
                            <label for="modalStatus" class="form-label fw-semibold small">Consultation Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="modalStatus" name="status" required>
                                <option value="Completed" selected>Completed</option>
                                <option value="Pending">Pending</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>

                        <!-- Treatment -->
                        <div class="col-12">
                            <label for="modalTreatment" class="form-label fw-semibold small">Treatment Plan / Instructions <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="modalTreatment" name="treatment" rows="2" required placeholder="e.g. Rest for 3 days, drink plenty of water, dosage timeline..."></textarea>
                            <div class="invalid-feedback">Treatment details are required.</div>
                        </div>

                        <!-- Dispense Section -->
                        <div class="col-12 my-1" id="dispenseSectionDivider"><hr></div>
                        <div class="col-12 mb-0" id="dispenseSectionHeader">
                            <h6 class="fw-bold text-success mb-1"><i class="bi bi-capsule-prescription me-2"></i>Primary Dispensed Medicine (Optional)</h6>
                            <small class="text-muted d-block" style="font-size: 0.8rem;">
                                <i class="bi bi-info-circle me-1"></i>If the patient requires multiple medication types, log the primary medication here and additional supplies via the <a href="<?= url('index.php?route=medicine') ?>" target="_blank" class="text-success text-decoration-none fw-medium">Medicine Distribution module <i class="bi bi-box-arrow-up-right"></i></a>.
                            </small>
                        </div>

                        <!-- Medicine Selection -->
                        <div class="col-12 col-md-8" id="medicineDropdownContainer">
                            <label for="modalMedicineId" class="form-label fw-semibold small">Medicine / Supply</label>
                            <select class="form-select" id="modalMedicineId" name="medicine_id">
                                <option value="" selected>No prescription allocation</option>
                                <?php foreach ($medicines as $med): ?>
                                    <option value="<?= $med['id'] ?>" data-stock="<?= $med['stock_qty'] ?>">
                                        <?= escape($med['name'] . ' [' . $med['category'] . '] - Code: ' . $med['code'] . ' (' . $med['stock_qty'] . ' available)') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Quantity -->
                        <div class="col-12 col-md-4" id="medicineQtyContainer">
                            <label for="modalMedicineQty" class="form-label fw-semibold small">Quantity to Dispense</label>
                            <input type="number" class="form-control" id="modalMedicineQty" name="medicine_qty" min="1" placeholder="e.g. 10">
                            <small class="text-muted" id="modalStockHint">Select a medicine to view stock limit.</small>
                            <div class="invalid-feedback" id="modalQtyFeedback">Please enter valid quantity.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4" id="saveButton">Log Consultation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Consultation Modal -->
<div class="modal fade" id="viewConsultationModal" tabindex="-1" aria-labelledby="viewConsultationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-success bg-gradient text-white">
                <h5 class="modal-title fw-bold" id="viewConsultationModalTitle"><i class="bi bi-file-earmark-medical me-2"></i>Consultation Record</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="viewConsultationBody">
                <!-- Loaded dynamically by Javascript -->
            </div>
            <div class="modal-footer border-0 bg-light p-3">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Close</button>
                <button type="button" onclick="printPrescription()" class="btn btn-primary px-4">
                    <i class="bi bi-printer me-1"></i> Print prescription
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Dynamic print container (hidden in normal display) -->
<div id="printSection" style="display: none;"></div>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    #printSection,
    #printSection * {
        visibility: visible;
        display: block !important;
    }
    #printSection {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        padding: 2cm;
    }
    #printSection table,
    #printSection tr {
        display: table !important;
    }
    #printSection td,
    #printSection th {
        display: table-cell !important;
    }
    .main-content-wrapper,
    .sidebar,
    .modal {
        display: none !important;
    }
}
</style>

<?php 
$pageScript = url('js/consultation_js.js');
require_once LAYOUT_PATH . 'footer.php'; 
?>
