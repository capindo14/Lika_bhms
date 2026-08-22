/**
 * JS-Driven Consultations CRUD Controller
 */

let consultationModal;
let viewConsultationModal;
let currentViewedCon = null; // Store current viewed consultation details for printing

document.addEventListener('DOMContentLoaded', () => {
    // Initialize Modals
    const modalEl = document.getElementById('consultationModal');
    if (modalEl) {
        consultationModal = new bootstrap.Modal(modalEl);
        
        // Re-initialize Date picker when modal is shown
        modalEl.addEventListener('shown.bs.modal', () => {
            if (typeof flatpickr !== 'undefined') {
                flatpickr('#modalDate', {
                    dateFormat: 'Y-m-d',
                    allowInput: true,
                    maxDate: 'today'
                });
            }
        });
    }

    const viewModalEl = document.getElementById('viewConsultationModal');
    if (viewModalEl) {
        viewConsultationModal = new bootstrap.Modal(viewModalEl);
    }

    // Load initial list
    loadConsultations();

    // Auto-update max input attribute and hint on medicine selection change
    const medSelect = document.getElementById('modalMedicineId');
    if (medSelect) {
        medSelect.addEventListener('change', (e) => {
            const option = e.target.options[e.target.selectedIndex];
            const stock = option.getAttribute('data-stock');
            const qtyInput = document.getElementById('modalMedicineQty');
            const hint = document.getElementById('modalStockHint');
            
            if (stock !== null && stock !== undefined && stock !== '') {
                qtyInput.setAttribute('max', stock);
                hint.textContent = `Available stock: ${stock} pcs`;
                hint.classList.add('text-success');
                hint.classList.remove('text-muted');
            } else {
                qtyInput.removeAttribute('max');
                qtyInput.value = '';
                hint.textContent = 'Select a medicine to view stock limit.';
                hint.classList.remove('text-success');
                hint.classList.add('text-muted');
            }
        });
    }

    // Form submit listener
    const form = document.getElementById('consultationForm');
    if (form) {
        form.addEventListener('submit', handleFormSubmit);
    }

    // Search filter
    const searchInput = document.getElementById('consultationSearch');
    if (searchInput) {
        searchInput.addEventListener('input', handleSearch);
    }
});

/**
 * Fetch and render all consultation records
 */
async function loadConsultations() {
    try {
        const response = await fetch('?route=api/consultation/list');
        if (!response.ok) throw new Error('API server returned error (' + response.status + ')');

        const consultations = await response.json();
        const tbody = document.getElementById('consultationTable');
        if (!tbody) return;

        tbody.innerHTML = '';

        if (consultations.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No consultation logs found.</td>
                </tr>
            `;
            return;
        }

        consultations.forEach(c => {
            let statusBadge = '';
            if (c.status === 'Completed') {
                statusBadge = '<span class="badge bg-success bg-opacity-10 text-success border border-success-subtle">Completed</span>';
            } else if (c.status === 'Pending') {
                statusBadge = '<span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle">Pending</span>';
            } else {
                statusBadge = '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle">Cancelled</span>';
            }

            const medDispensed = c.medicine_name 
                ? `<span class="fw-semibold text-success">${escapeHtml(c.medicine_name)}</span> <small class="text-muted">(${c.medicine_qty} pcs)</small>`
                : '<span class="text-muted small">None</span>';

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="font-monospace fw-semibold text-primary">${escapeHtml(c.consultation_no)}</td>
                <td class="small fw-medium">${escapeHtml(c.consultation_date)}</td>
                <td>
                    <div class="fw-bold">${escapeHtml(c.patient_name)}</div>
                    <small class="text-muted">${escapeHtml(c.patient_code)}</small>
                </td>
                <td class="fw-semibold text-dark">${escapeHtml(c.diagnosis)}</td>
                <td>${medDispensed}</td>
                <td>${statusBadge}</td>
                <td class="text-center">
                    <div class="btn-group gap-1">
                        <button type="button" onclick="viewConsultation(${c.id})" class="btn btn-outline-info btn-sm rounded-2" title="View details & Rx">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button type="button" onclick="editConsultation(${c.id})" class="btn btn-outline-primary btn-sm rounded-2" title="Edit Log">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" onclick="deleteConsultation(${c.id}, '${escapeHtml(c.consultation_no)}')" class="btn btn-outline-danger btn-sm rounded-2" title="Delete Log">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    } catch (error) {
        console.error(error);
        showToast('error', 'Failed to fetch consultations: ' + error.message);
    }
}

/**
 * Configure form for logging a new consultation
 */
function newConsultation() {
    const form = document.getElementById('consultationForm');
    if (!form) return;

    form.reset();
    form.classList.remove('was-validated');
    $('#modalResidentId').val('').trigger('change');

    document.getElementById('consultationId').value = '';
    document.getElementById('modalTitleText').textContent = 'Log New Consultation';
    document.getElementById('modalStatus').value = 'Completed';
    document.getElementById('saveButton').textContent = 'Log Consultation';

    // Show Dispense Section (Allowed in create)
    document.getElementById('dispenseSectionDivider').style.display = 'block';
    document.getElementById('dispenseSectionHeader').style.display = 'block';
    document.getElementById('medicineDropdownContainer').style.display = 'block';
    document.getElementById('medicineQtyContainer').style.display = 'block';

    const hint = document.getElementById('modalStockHint');
    hint.textContent = 'Select a medicine to view stock limit.';
    hint.classList.remove('text-success');
    hint.classList.add('text-muted');

    if (consultationModal) consultationModal.show();
}

/**
 * Fetch consultation details and populate form in Edit Modal
 */
async function editConsultation(id) {
    try {
        const response = await fetch('?route=api/consultation/detail&id=' + id);
        if (!response.ok) throw new Error('API server returned error (' + response.status + ')');

        const c = await response.json();
        if (c.status === 'error') throw new Error(c.message);

        const form = document.getElementById('consultationForm');
        form.reset();
        form.classList.remove('was-validated');

        document.getElementById('consultationId').value = c.id;
        document.getElementById('modalResidentId').value = c.resident_id;
        $('#modalResidentId').trigger('change');
        document.getElementById('modalDate').value = c.consultation_date;
        document.getElementById('modalSymptoms').value = c.symptoms;
        document.getElementById('modalDiagnosis').value = c.diagnosis;
        document.getElementById('modalStatus').value = c.status;
        document.getElementById('modalTreatment').value = c.treatment;

        // Hide dynamic dispensation during edit to prevent stock tampering/double deductions.
        // It is safer to manage medicine dispensation purely on create logs.
        document.getElementById('dispenseSectionDivider').style.display = 'none';
        document.getElementById('dispenseSectionHeader').style.display = 'none';
        document.getElementById('medicineDropdownContainer').style.display = 'none';
        document.getElementById('medicineQtyContainer').style.display = 'none';

        document.getElementById('modalTitleText').textContent = 'Edit Consultation Details';
        document.getElementById('saveButton').textContent = 'Update Details';

        if (consultationModal) consultationModal.show();
    } catch (error) {
        showToast('error', 'Error loading details: ' + error.message);
    }
}

/**
 * Fetch consultation details and open modal viewer
 */
async function viewConsultation(id) {
    try {
        const response = await fetch('?route=consultation/detail_json&id=' + id);
        if (!response.ok) throw new Error('API server error (' + response.status + ')');

        const result = await response.json();
        if (!result.success) throw new Error(result.message);

        const c = result.data;
        currentViewedCon = c; // Save reference for printing

        let statusBadge = '';
        if (c.status === 'Completed') {
            statusBadge = '<span class="badge bg-success bg-opacity-10 text-success border border-success-subtle">Completed</span>';
        } else if (c.status === 'Pending') {
            statusBadge = '<span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle">Pending</span>';
        } else {
            statusBadge = '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle">Cancelled</span>';
        }

        let medInfo = '';
        if (c.medicine_name) {
            medInfo = `
                <div class="col-12 mt-3 bg-success-subtle bg-opacity-10 border border-success-subtle p-3 rounded">
                    <span class="text-muted small d-block">Prescribed / Dispensed Medicine</span>
                    <strong class="text-success">${escapeHtml(c.medicine_name)}</strong>
                    <span class="text-muted small ms-1">(${c.medicine_qty} pcs allocated)</span>
                </div>
            `;
        } else {
            medInfo = `
                <div class="col-12 mt-3 bg-light border p-3 rounded">
                    <span class="text-muted small d-block">Prescribed / Dispensed Medicine</span>
                    <strong class="text-muted small">No dynamic medicine allocation recorded.</strong>
                </div>
            `;
        }

        const detailsHtml = `
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <span class="text-muted small d-block">Patient Name</span>
                    <strong class="text-dark fs-5">${escapeHtml(c.patient_name)}</strong>
                    <span class="text-muted small d-block">Code: ${escapeHtml(c.patient_code)} | Age: ${escapeHtml(c.patient_age)} yrs | Gender: ${escapeHtml(c.patient_gender)}</span>
                </div>
                <div class="col-12 col-md-6 text-md-end">
                    <span class="text-muted small d-block">Consultation Date</span>
                    <strong class="text-dark">${escapeHtml(c.consultation_date)}</strong>
                </div>
                <div class="col-12"><hr class="my-1"></div>
                <div class="col-12 col-md-6">
                    <span class="text-muted small d-block">Symptoms / Complaints</span>
                    <div class="bg-light p-2.5 rounded border small text-dark">${escapeHtml(c.symptoms)}</div>
                </div>
                <div class="col-12 col-md-6">
                    <span class="text-muted small d-block">Diagnosis</span>
                    <div class="bg-light p-2.5 rounded border small text-dark fw-bold">${escapeHtml(c.diagnosis)}</div>
                </div>
                <div class="col-12 mt-3">
                    <span class="text-muted small d-block">Treatment Plan & Instructions</span>
                    <div class="bg-light p-3 rounded border text-dark font-monospace small" style="white-space: pre-line;">${escapeHtml(c.treatment)}</div>
                </div>
                ${medInfo}
                <div class="col-12 mt-3 d-flex justify-content-between align-items-center border-top pt-3">
                    <div>
                        <small class="text-muted d-block">Health Worker Signature</small>
                        <span class="fw-semibold small">${escapeHtml(c.worker_name)}</span>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">Case Status</small>
                        ${statusBadge}
                    </div>
                </div>
            </div>
        `;

        document.getElementById('viewConsultationBody').innerHTML = detailsHtml;
        if (viewConsultationModal) viewConsultationModal.show();
    } catch (error) {
        showToast('error', 'Error loading record: ' + error.message);
    }
}

/**
 * Handle form submit (JSON POST)
 */
async function handleFormSubmit(event) {
    event.preventDefault();
    const form = event.target;

    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    const id = document.getElementById('consultationId').value;
    const resident_id = parseInt(document.getElementById('modalResidentId').value);
    const consultation_date = document.getElementById('modalDate').value;
    const symptoms = document.getElementById('modalSymptoms').value.trim();
    const diagnosis = document.getElementById('modalDiagnosis').value.trim();
    const status = document.getElementById('modalStatus').value;
    const treatment = document.getElementById('modalTreatment').value.trim();

    const data = {
        resident_id,
        consultation_date,
        symptoms,
        diagnosis,
        status,
        treatment
    };

    let apiUrl = '?route=api/consultation/store';
    
    if (id === '') {
        // Create Mode - capture optional medicine dispensation
        const medId = document.getElementById('modalMedicineId').value;
        const qty = parseInt(document.getElementById('modalMedicineQty').value) || 0;
        
        if (medId !== '') {
            data.medicine_id = parseInt(medId);
            data.medicine_qty = qty;
        }
    } else {
        // Edit Mode
        data.id = parseInt(id);
        apiUrl = '?route=api/consultation/update';
    }

    try {
        const response = await fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        if (!response.ok) throw new Error('API server returned error (' + response.status + ')');

        const result = await response.json();
        if (result.status === 'success') {
            if (consultationModal) consultationModal.hide();
            showToast('success', result.message);
            loadConsultations();
        } else {
            showToast('error', result.message);
        }
    } catch (error) {
        showToast('error', 'Request failed: ' + error.message);
    }
}

/**
 * Delete a consultation record
 */
function deleteConsultation(id, code) {
    const isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';

    Swal.fire({
        title: 'Delete Log?',
        text: `Are you sure you want to remove consultation log: ${code}? This cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        background: isDarkMode ? '#212529' : '#fff',
        color: isDarkMode ? '#f8f9fa' : '#212529'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const response = await fetch('?route=api/consultation/delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: id })
                });

                if (!response.ok) throw new Error('API request failed.');

                const res = await response.json();
                if (res.status === 'success') {
                    showToast('success', res.message);
                    loadConsultations();
                } else {
                    showToast('error', res.message);
                }
            } catch (error) {
                showToast('error', 'Failed to delete: ' + error.message);
            }
        }
    });
}

/**
 * printPrescription handles generating printable layout window triggers
 */
function printPrescription() {
    if (!currentViewedCon) return;
    
    const printSection = document.getElementById('printSection');
    
    let rxHtml = `
        <div style="border: 2px solid #000; padding: 25px; border-radius: 10px; font-family: sans-serif;">
            <div class="text-center mb-4" style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 25px;">
                <h2 style="margin: 0; font-size: 20pt; letter-spacing: 0.5px;">BARANGAY HEALTH CARE MONITORING CENTER</h2>
                <p style="margin: 5px 0 0; font-size: 11pt; color: #555;">Official Clinical Patient Prescription Details</p>
            </div>
            
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 25px; font-size: 11pt;">
                <tr>
                    <td style="width: 12%; padding: 5px 0;"><strong>PATIENT:</strong></td>
                    <td style="width: 48%; padding: 5px 0; border-bottom: 1px solid #ccc;">${escapeHtml(currentViewedCon.patient_name)}</td>
                    <td style="width: 15%; padding: 5px 0; text-align: right;"><strong>DATE:</strong></td>
                    <td style="width: 25%; padding: 5px 0; border-bottom: 1px solid #ccc; text-align: center;">${escapeHtml(currentViewedCon.consultation_date)}</td>
                </tr>
                <tr>
                    <td style="padding: 5px 0;"><strong>AGE/SEX:</strong></td>
                    <td style="padding: 5px 0; border-bottom: 1px solid #ccc;">${escapeHtml(currentViewedCon.patient_age)} Yrs / ${escapeHtml(currentViewedCon.patient_gender)}</td>
                    <td style="padding: 5px 0; text-align: right;"><strong>RECORD NO:</strong></td>
                    <td style="padding: 5px 0; border-bottom: 1px solid #ccc; text-align: center; font-family: monospace;">${escapeHtml(currentViewedCon.consultation_no)}</td>
                </tr>
            </table>

            <div style="margin-bottom: 20px;">
                <strong style="display: block; font-size: 11pt; margin-bottom: 5px; color: #333; text-transform: uppercase;">Symptoms / Complaints:</strong>
                <div style="background-color: #f9f9f9; border: 1px solid #ddd; padding: 12px; border-radius: 5px; font-size: 11pt; min-height: 50px;">
                    ${escapeHtml(currentViewedCon.symptoms)}
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <strong style="display: block; font-size: 11pt; margin-bottom: 5px; color: #333; text-transform: uppercase;">Diagnosis:</strong>
                <div style="background-color: #f9f9f9; border: 1px solid #ddd; padding: 12px; border-radius: 5px; font-size: 11pt; font-weight: bold;">
                    ${escapeHtml(currentViewedCon.diagnosis)}
                </div>
            </div>

            <div style="margin-bottom: 25px;">
                <strong style="display: block; font-size: 11pt; margin-bottom: 5px; color: #333; text-transform: uppercase;">Rx / Treatment Instructions:</strong>
                <div style="background-color: #f9f9f9; border: 1px solid #ddd; padding: 15px; border-radius: 5px; font-size: 11pt; font-family: monospace; white-space: pre-line; min-height: 80px;">
                    ${escapeHtml(currentViewedCon.treatment)}
                </div>
            </div>
    `;

    if (currentViewedCon.medicine_name) {
        rxHtml += `
            <div style="border: 1px dashed #1e5a22; background-color: #f4faf4; color: #1e5a22; padding: 15px; border-radius: 5px; font-size: 11pt; margin-bottom: 30px;">
                <strong>Dispensed Medicine Allocation:</strong> ${escapeHtml(currentViewedCon.medicine_name)} (${currentViewedCon.medicine_qty} pcs)
            </div>
        `;
    }

    rxHtml += `
            <div style="margin-top: 60px; text-align: right;">
                <div style="display: inline-block; text-align: center; width: 250px;">
                    <div style="border-bottom: 1px solid #000; padding-bottom: 5px; font-weight: bold;">
                        ${escapeHtml(currentViewedCon.worker_name)}
                    </div>
                    <div style="font-size: 9pt; color: #555; margin-top: 5px; text-transform: uppercase;">
                        Officer-in-Charge / Health Worker
                    </div>
                </div>
            </div>
        </div>
    `;

    printSection.innerHTML = rxHtml;
    
    // Add print trigger classes to body and print
    document.body.classList.add('printing-consultation');
    window.print();
    
    // Cleanup printing classes
    document.body.classList.remove('printing-consultation');
}

/**
 * Filter rows locally
 */
function handleSearch(event) {
    const keyword = event.target.value.toLowerCase().trim();
    const rows = document.querySelectorAll('#consultationTable tr');

    rows.forEach(row => {
        if (row.cells.length === 1 && row.cells[0].colSpan > 1) return;
        
        const rowText = row.innerText.toLowerCase();
        row.style.display = rowText.includes(keyword) ? '' : 'none';
    });
}


