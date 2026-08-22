/**
 * JS-Driven Residents CRUD Controller
 */

let residentModal;
let viewResidentModal;
let currentStatusFilter = 'Active';

document.addEventListener('DOMContentLoaded', () => {
    // Initialize Modals
    const resModalEl = document.getElementById('residentModal');
    if (resModalEl) {
        residentModal = new bootstrap.Modal(resModalEl);
        
        // Re-initialize custom birthdate picker when modal is shown
        resModalEl.addEventListener('shown.bs.modal', () => {
            if (typeof App !== 'undefined' && App.initBirthdatePicker) {
                App.initBirthdatePicker('.birthdate-picker');
            }
        });
    }

    const viewModalEl = document.getElementById('viewResidentModal');
    if (viewModalEl) {
        viewResidentModal = new bootstrap.Modal(viewModalEl);
    }

    // Load initial list
    loadResidents();

    // Form submit listener
    const form = document.getElementById('residentForm');
    if (form) {
        form.addEventListener('submit', handleFormSubmit);
    }

    // Search and gender filter listeners
    const searchInput = document.getElementById('residentSearch');
    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }
    const genderFilter = document.getElementById('genderFilter');
    if (genderFilter) {
        genderFilter.addEventListener('change', applyFilters);
    }

    // Dynamic field listeners for maternal/child conditional fields
    const genderSelect = document.getElementById('resGender');
    if (genderSelect) {
        genderSelect.addEventListener('change', updateConditionalFields);
    }
    const birthdateInput = document.getElementById('resBirthdate');
    if (birthdateInput) {
        birthdateInput.addEventListener('change', updateConditionalFields);
        birthdateInput.addEventListener('input', updateConditionalFields);
    }
});

/**
 * Change status tab active/archived and fetch lists
 */
function setStatusFilter(status) {
    currentStatusFilter = status;
    
    // Toggle active classes on tab buttons
    const tabs = {
        'Active': document.getElementById('tabActive'),
        'Pregnant': document.getElementById('tabPregnant'),
        'Infant': document.getElementById('tabInfant'),
        'Archived': document.getElementById('tabArchived')
    };
    
    Object.keys(tabs).forEach(key => {
        const tab = tabs[key];
        if (!tab) return;
        if (key === status) {
            tab.classList.add('active');
            tab.classList.remove('text-secondary');
        } else {
            tab.classList.remove('active');
            tab.classList.add('text-secondary');
        }
    });

    // Hide gender filter under Pregnant tab (since all are Female)
    const genderFilterCol = document.getElementById('genderFilterCol');
    const genderFilter = document.getElementById('genderFilter');
    if (genderFilterCol) {
        if (status === 'Pregnant') {
            genderFilterCol.style.display = 'none';
            if (genderFilter) genderFilter.value = ''; // Reset selection to avoid conflicting filters
        } else {
            genderFilterCol.style.display = '';
        }
    }

    // Hide Register Resident button except on Active profiles tab
    const btnRegister = document.getElementById('btnRegisterResident');
    if (btnRegister) {
        if (status === 'Active') {
            btnRegister.style.display = '';
        } else {
            btnRegister.style.display = 'none';
        }
    }

    // Refresh directory
    loadResidents();
}

/**
 * Fetch and build the directory table body
 */
async function loadResidents() {
    try {
        const response = await fetch(`?route=api/residents/list&status=${currentStatusFilter}`);
        if (!response.ok) throw new Error('API server returned error (' + response.status + ')');
        
        const residents = await response.json();
        const tbody = document.getElementById('residentTable');
        if (!tbody) return;
        
        tbody.innerHTML = '';
        
        if (residents.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No residents found under ${currentStatusFilter} profiles.</td>
                </tr>
            `;
            return;
        }
        
        residents.forEach(res => {
            const isHead = parseInt(res.is_family_head) === 1;
            const designationBadge = isHead 
                ? '<span class="badge bg-success bg-opacity-10 text-success border border-success-subtle">Family Head</span>'
                : '<span class="badge bg-light text-muted border">Member</span>';
                
            let genderBadge = '';
            if (res.gender === 'Male') {
                genderBadge = '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle"><i class="bi bi-gender-male me-1"></i> Male</span>';
            } else if (res.gender === 'Female') {
                genderBadge = '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle"><i class="bi bi-gender-female me-1"></i> Female</span>';
            } else {
                genderBadge = '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle"><i class="bi bi-gender-ambiguous me-1"></i> Other</span>';
            }

            let actionButtons = '';
            if (currentStatusFilter !== 'Archived') {
                actionButtons = `
                    <button type="button" onclick="viewResident(${res.id})" class="btn btn-outline-info btn-sm rounded-2" title="View Profile">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button type="button" onclick="editResident(${res.id})" class="btn btn-outline-primary btn-sm rounded-2" title="Edit Profile">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" onclick="archiveResident(${res.id}, '${escapeHtml(res.last_name.replace(/'/g, "\\'"))}, ${escapeHtml(res.first_name.replace(/'/g, "\\'"))}')" class="btn btn-outline-warning btn-sm rounded-2" title="Archive Profile">
                        <i class="bi bi-archive"></i>
                    </button>
                `;
            } else {
                actionButtons = `
                    <button type="button" onclick="restoreResident(${res.id}, '${escapeHtml(res.last_name.replace(/'/g, "\\'"))}, ${escapeHtml(res.first_name.replace(/'/g, "\\'"))}')" class="btn btn-outline-success btn-sm rounded-2" title="Restore Profile">
                        <i class="bi bi-arrow-counterclockwise"></i> Restore
                    </button>
                `;
            }

            let civilStatusHtml = escapeHtml(res.civil_status);
            if (res.civil_status === 'Married' && res.spouse_name) {
                civilStatusHtml = `Married<br><small class="text-muted" style="font-size: 0.75rem;">with ${escapeHtml(res.spouse_name)} (${escapeHtml(res.spouse_code)})</small>`;
            }

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="font-monospace fw-semibold text-primary">${escapeHtml(res.resident_id)}</td>
                <td>
                    <div class="fw-semibold text-dark">${escapeHtml(res.last_name)}, ${escapeHtml(res.first_name)} ${escapeHtml(res.middle_name || '')}</div>
                    <small class="text-muted text-truncate d-block" style="max-width: 200px;">${escapeHtml(res.address)}</small>
                </td>
                <td>${genderBadge}</td>
                <td>${escapeHtml(res.birthdate)} <small class="text-muted">(${res.age} yrs)</small></td>
                <td>${civilStatusHtml}</td>
                <td>${designationBadge}</td>
                <td class="text-center">
                    <div class="btn-group gap-1">
                        ${actionButtons}
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
        applyFilters();
    } catch (error) {
        console.error(error);
        showToast('error', 'Unable to fetch records: ' + error.message);
    }
}

/**
 * Calculate age from birthdate string (YYYY-MM-DD)
 */
function calculateAgeFromBirthdate(birthdateStr) {
    if (!birthdateStr) return null;
    const dob = new Date(birthdateStr);
    if (isNaN(dob.getTime())) return null;
    
    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const m = today.getMonth() - dob.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
        age--;
    }
    return age;
}

/**
 * Dynamically show/hide Pregnancy and Child Feeding fields based on gender & age
 */
function updateConditionalFields() {
    const gender = document.getElementById('resGender')?.value || '';
    const birthdate = document.getElementById('resBirthdate')?.value || '';
    const age = calculateAgeFromBirthdate(birthdate);

    const pregContainer = document.getElementById('resPregnancyContainer');
    const pregSelect = document.getElementById('resPregnancy');
    const feedContainer = document.getElementById('resFeedingContainer');
    const feedSelect = document.getElementById('resFeeding');

    // 1. Pregnancy Status: Visible for all Females to ensure flexible BHW encoding
    if (gender === 'Female') {
        if (pregContainer) pregContainer.style.display = 'block';
    } else {
        if (pregContainer) pregContainer.style.display = 'none';
        if (pregSelect) pregSelect.value = 'N/A';
    }

    // 2. Child Feeding Type: Only visible for infants & young children (Age < 5 years old) or if already set
    const currentFeedingVal = feedSelect?.value || '';
    if ((age !== null && age < 5) || (currentFeedingVal !== '' && currentFeedingVal !== 'N/A')) {
        if (feedContainer) feedContainer.style.display = 'block';
    } else {
        if (feedContainer) feedContainer.style.display = 'none';
        if (feedSelect) feedSelect.value = '';
    }
}

/**
 * Helper to show/hide standard fields depending on the tab we are editing from
 */
function toggleFormFieldsForTab(tabName) {
    const isPregnantTab = (tabName === 'Pregnant');
    const isInfantTab = (tabName === 'Infant');
    
    // Toggle standard details visibility
    document.querySelectorAll('.standard-details-field').forEach(el => {
        if (isPregnantTab || isInfantTab) {
            el.style.display = 'none';
        } else {
            el.style.display = ''; // Restore default grid layout
        }
    });

    const pregContainer = document.getElementById('resPregnancyContainer');
    const feedContainer = document.getElementById('resFeedingContainer');
    const modalTitle = document.getElementById('modalTitleText');
    const saveBtn = document.getElementById('saveButton');

    if (isPregnantTab) {
        if (pregContainer) pregContainer.style.display = 'block';
        if (feedContainer) feedContainer.style.display = 'none';
        if (modalTitle) modalTitle.textContent = 'Update Pregnancy Status';
        if (saveBtn) saveBtn.textContent = 'Update Status';
    } else if (isInfantTab) {
        if (pregContainer) pregContainer.style.display = 'none';
        if (feedContainer) feedContainer.style.display = 'block';
        if (modalTitle) modalTitle.textContent = 'Update Child Feeding Type';
        if (saveBtn) saveBtn.textContent = 'Update Feeding';
    } else {
        if (modalTitle) modalTitle.textContent = 'Register Resident';
        if (saveBtn) saveBtn.textContent = 'Register Profile';
    }
}

/**
 * Configure form for adding a new profile
 */
function newResident() {
    const form = document.getElementById('residentForm');
    if (!form) return;

    form.reset();
    form.classList.remove('was-validated');

    document.getElementById('residentId').value = '';
    document.getElementById('resIsFamilyHead').checked = false;
    document.getElementById('resPregnancy').value = 'N/A';
    document.getElementById('resFeeding').value = '';

    toggleFormFieldsForTab('Active');
    updateConditionalFields();

    if (residentModal) residentModal.show();
}

/**
 * Fetch resident details and open edit modal
 */
async function editResident(id) {
    try {
        const response = await fetch('?route=api/residents/detail&id=' + id);
        if (!response.ok) throw new Error('API server returned error (' + response.status + ')');
        
        const res = await response.json();
        if (res.status === 'error') throw new Error(res.message);

        const form = document.getElementById('residentForm');
        if (!form) return;

        form.reset();
        form.classList.remove('was-validated');

        document.getElementById('residentId').value = res.id;
        document.getElementById('resFirstName').value = res.first_name;
        document.getElementById('resMiddleName').value = res.middle_name || '';
        document.getElementById('resLastName').value = res.last_name;
        document.getElementById('resGender').value = res.gender;
        document.getElementById('resBirthdate').value = res.birthdate;
        document.getElementById('resCivilStatus').value = res.civil_status;
        document.getElementById('resContactNumber').value = res.contact_number || '';
        document.getElementById('resAddress').value = res.address;
        document.getElementById('resIsFamilyHead').checked = parseInt(res.is_family_head) === 1;
        document.getElementById('resPregnancy').value = res.pregnancy_status || 'N/A';
        document.getElementById('resFeeding').value = res.child_feeding_type || '';

        // Load flatpickr value
        const fp = document.getElementById('resBirthdate')._flatpickr;
        if (fp) {
            fp.setDate(res.birthdate, false);
        }

        const activeTab = currentStatusFilter;
        toggleFormFieldsForTab(activeTab);
        
        if (activeTab === 'Active' || activeTab === 'Archived') {
            updateConditionalFields();
        } else {
            // Keep them shown/hidden strictly by the tab context
            if (activeTab === 'Pregnant') {
                const pregContainer = document.getElementById('resPregnancyContainer');
                if (pregContainer) pregContainer.style.display = 'block';
            } else if (activeTab === 'Infant') {
                const feedContainer = document.getElementById('resFeedingContainer');
                if (feedContainer) feedContainer.style.display = 'block';
            }
        }

        if (activeTab !== 'Pregnant' && activeTab !== 'Infant') {
            document.getElementById('modalTitleText').textContent = 'Edit Resident Profile';
            document.getElementById('saveButton').textContent = 'Update Profile';
        }

        if (residentModal) residentModal.show();
    } catch (error) {
        showToast('error', error.message);
    }
}

/**
 * Fetch history and details for Profile View Modal
 */
async function viewResident(id) {
    try {
        const response = await fetch('?route=residents/detail_json&id=' + id);
        if (!response.ok) throw new Error('API server error (' + response.status + ')');

        const res = await response.json();
        if (!res.success) throw new Error(res.message);

        const r = res.data.resident;
        const cons = res.data.consultations;
        const imms = res.data.immunizations;

        // Bind profile details
        document.getElementById('viewResFullName').textContent = `${r.last_name}, ${r.first_name} ${r.middle_name || ''}`;
        document.getElementById('viewResCode').textContent = r.resident_id;
        document.getElementById('viewResAgeBirthdate').textContent = `${r.birthdate} (${r.age} yrs old)`;
        
        if (r.civil_status === 'Married' && r.spouse_name) {
            document.getElementById('viewResCivilStatus').innerHTML = `Married <span class="text-muted small" style="font-size: 0.85rem;">(with ${escapeHtml(r.spouse_name)} - ${escapeHtml(r.spouse_code)})</span>`;
        } else {
            document.getElementById('viewResCivilStatus').textContent = r.civil_status;
        }

        document.getElementById('viewResContact').textContent = r.contact_number || 'N/A';
        document.getElementById('viewResAddress').textContent = r.address;

        // Pregnancy Status view visibility
        const viewPregContainer = document.getElementById('viewResPregnancyContainer');
        if (r.gender === 'Female' && parseInt(r.age) >= 10 && parseInt(r.age) <= 50) {
            if (viewPregContainer) viewPregContainer.style.display = 'block';
            document.getElementById('viewResPregnancy').textContent = r.pregnancy_status || 'N/A';
        } else {
            if (viewPregContainer) viewPregContainer.style.display = 'none';
        }

        // Child Feeding view visibility
        const viewFeedContainer = document.getElementById('viewResFeedingContainer');
        if (parseInt(r.age) < 5) {
            if (viewFeedContainer) viewFeedContainer.style.display = 'block';
            document.getElementById('viewResFeeding').textContent = r.child_feeding_type || 'N/A';
        } else {
            if (viewFeedContainer) viewFeedContainer.style.display = 'none';
        }

        // Designations badge
        let badgesHtml = '';
        if (r.gender === 'Male') {
            badgesHtml += '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle me-1"><i class="bi bi-gender-male me-1"></i> Male</span>';
        } else if (r.gender === 'Female') {
            badgesHtml += '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle me-1"><i class="bi bi-gender-female me-1"></i> Female</span>';
        } else {
            badgesHtml += '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle me-1"><i class="bi bi-gender-ambiguous me-1"></i> Other</span>';
        }

        if (parseInt(r.is_family_head) === 1) {
            badgesHtml += '<span class="badge bg-success bg-opacity-10 text-success border border-success-subtle">Family Head</span>';
        } else {
            badgesHtml += '<span class="badge bg-light text-muted border">Member</span>';
        }
        document.getElementById('viewResBadges').innerHTML = badgesHtml;

        // Consultation list binding
        document.getElementById('viewResConCount').textContent = cons.length;
        const conList = document.getElementById('viewResConList');
        if (cons.length === 0) {
            conList.innerHTML = '<div class="text-muted text-center py-3 small">No consultation history recorded for this resident.</div>';
        } else {
            let conHtml = '<div class="list-group list-group-flush">';
            cons.forEach(c => {
                conHtml += `
                    <div class="list-group-item px-0 py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong class="text-dark small">${escapeHtml(c.diagnosis)}</strong>
                            <span class="small text-muted">${escapeHtml(c.consultation_date)}</span>
                        </div>
                        <div class="small text-muted">Symptoms: ${escapeHtml(c.symptoms)}</div>
                        <div class="small text-secondary">Worker: ${escapeHtml(c.worker_name)} ${c.medicine_name ? '• Medicine: ' + escapeHtml(c.medicine_name) : ''}</div>
                    </div>
                `;
            });
            conHtml += '</div>';
            conList.innerHTML = conHtml;
        }

        // Immunization list binding
        document.getElementById('viewResImmCount').textContent = imms.length;
        const immList = document.getElementById('viewResImmList');
        if (imms.length === 0) {
            immList.innerHTML = '<div class="text-muted text-center py-3 small">No immunization records recorded for this resident.</div>';
        } else {
            let immHtml = '<div class="list-group list-group-flush">';
            imms.forEach(i => {
                immHtml += `
                    <div class="list-group-item px-0 py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong class="text-dark small">${escapeHtml(i.vaccine_name)} (${escapeHtml(i.dose)})</strong>
                            <span class="badge bg-${i.status === 'Completed' ? 'success' : 'warning'} bg-opacity-10 text-${i.status === 'Completed' ? 'success' : 'warning'}">${escapeHtml(i.status)}</span>
                        </div>
                        <div class="small text-muted">Date: ${i.date_given || 'Scheduled: ' + i.next_schedule}</div>
                    </div>
                `;
            });
            immHtml += '</div>';
            immList.innerHTML = immHtml;
        }

        if (viewResidentModal) viewResidentModal.show();
    } catch (error) {
        showToast('error', 'Failed to retrieve profile history: ' + error.message);
    }
}

/**
 * Submit form handler (prevents reload, executes JSON POST store/update API)
 */
async function handleFormSubmit(event) {
    event.preventDefault();
    const form = event.target;

    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    const id = document.getElementById('residentId').value;
    const first_name = document.getElementById('resFirstName').value.trim();
    const middle_name = document.getElementById('resMiddleName').value.trim();
    const last_name = document.getElementById('resLastName').value.trim();
    const gender = document.getElementById('resGender').value;
    const birthdate = document.getElementById('resBirthdate').value;
    const civil_status = document.getElementById('resCivilStatus').value;
    const contact_number = document.getElementById('resContactNumber').value.trim();
    const address = document.getElementById('resAddress').value.trim();
    const is_family_head = document.getElementById('resIsFamilyHead').checked ? 1 : 0;
    const pregnancy_status = document.getElementById('resPregnancy').value;
    const child_feeding_type = document.getElementById('resFeeding').value.trim();

    const data = {
        first_name,
        middle_name,
        last_name,
        gender,
        birthdate,
        civil_status,
        contact_number,
        address,
        is_family_head,
        pregnancy_status,
        child_feeding_type
    };

    let url = '?route=api/residents/store';
    if (id !== '') {
        data.id = parseInt(id);
        url = '?route=api/residents/update';
    }

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        if (!response.ok) throw new Error('API server returned error (' + response.status + ')');

        const result = await response.json();
        if (result.status === 'success') {
            if (residentModal) residentModal.hide();
            showToast('success', result.message);
            loadResidents();
        } else {
            showToast('error', result.message);
        }
    } catch (error) {
        showToast('error', 'Request failed: ' + error.message);
    }
}

/**
 * Archive a resident profile
 */
function archiveResident(id, name) {
    const isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';

    Swal.fire({
        title: 'Archive Profile?',
        text: `Are you sure you want to archive the profile of ${name}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#10b981', // green
        cancelButtonColor: '#ef4444', // red
        confirmButtonText: 'Yes, Archive',
        cancelButtonText: 'Cancel',
        background: isDarkMode ? '#212529' : '#fff',
        color: isDarkMode ? '#f8f9fa' : '#212529'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const response = await fetch('?route=api/residents/delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: id, action: 'Archive' })
                });

                if (!response.ok) throw new Error('API response failed.');

                const res = await response.json();
                if (res.status === 'success') {
                    showToast('success', res.message);
                    loadResidents();
                } else {
                    showToast('error', res.message);
                }
            } catch (error) {
                showToast('error', 'Failed to archive: ' + error.message);
            }
        }
    });
}

/**
 * Restore an archived resident profile
 */
function restoreResident(id, name) {
    const isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';

    Swal.fire({
        title: 'Restore Profile?',
        text: `Are you sure you want to restore the profile of ${name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981', // green
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Restore',
        cancelButtonText: 'Cancel',
        background: isDarkMode ? '#212529' : '#fff',
        color: isDarkMode ? '#f8f9fa' : '#212529'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const response = await fetch('?route=api/residents/delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: id, action: 'Restore' })
                });

                if (!response.ok) throw new Error('API response failed.');

                const res = await response.json();
                if (res.status === 'success') {
                    showToast('success', res.message);
                    loadResidents();
                } else {
                    showToast('error', res.message);
                }
            } catch (error) {
                showToast('error', 'Failed to restore: ' + error.message);
            }
        }
    });
}

/**
 * Filter rows locally by search keywords and gender criteria
 */
function applyFilters() {
    const searchVal = document.getElementById('residentSearch')?.value.toLowerCase().trim() || '';
    const genderVal = document.getElementById('genderFilter')?.value || '';
    const rows = document.querySelectorAll('#residentTable tr');

    rows.forEach(row => {
        // Skip empty table messages or loading indicator rows
        if (row.cells.length === 1 && row.cells[0].colSpan > 1) return;
        
        // 1. Evaluate search query match
        const rowText = row.innerText.toLowerCase();
        const matchesSearch = rowText.includes(searchVal);
        
        // 2. Evaluate gender filter match (gender badge text is in the 3rd column, index 2)
        let matchesGender = true;
        if (genderVal !== '') {
            const genderCellText = row.cells[2]?.innerText.trim() || '';
            matchesGender = (genderCellText === genderVal);
        }
        
        // 3. Toggle row visibility
        row.style.display = (matchesSearch && matchesGender) ? '' : 'none';
    });
}
