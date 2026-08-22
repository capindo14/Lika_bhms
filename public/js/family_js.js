/**
 * JS-Driven Families CRUD Controller
 */

// Global Bootstrap Modal instances
let familyModal;
let membersModal;

document.addEventListener('DOMContentLoaded', () => {
    const famModalEl = document.getElementById('familyModal');
    if (famModalEl) {
        familyModal = new bootstrap.Modal(famModalEl);
        
        // Add default row when opening Create modal
        famModalEl.addEventListener('show.bs.modal', () => {
            const tbody = document.getElementById('modalMembersTable');
            if (tbody && tbody.children.length === 0) {
                addMemberRow();
            }
        });
    }

    const memModalEl = document.getElementById('membersModal');
    if (memModalEl) {
        membersModal = new bootstrap.Modal(memModalEl);
    }

    // Load initial list
    loadFamilies();

    // Auto-fill address and toggle indicators on Head selection (using jQuery for cross-compatibility with App.initSearchableSelect)
    $('#modalHeadId').on('change', function() {
        const option = this.options[this.selectedIndex];
        if (option) {
            const address = option.getAttribute('data-address');
            if (address) {
                document.getElementById('modalAddress').value = address;
            }
            toggleGenderIndicators(option);
        }
    });

    // Form submit
    const form = document.getElementById('familyForm');
    if (form) {
        form.addEventListener('submit', handleFormSubmit);
    }

    // Search filter
    const searchInput = document.getElementById('familySearch');
    if (searchInput) {
        searchInput.addEventListener('input', handleSearch);
    }

    // Auto-open family members modal if redirected from Distribution Details with ?open_family_id=
    const urlParams = new URLSearchParams(window.location.search);
    const openFamilyId = urlParams.get('open_family_id');
    if (openFamilyId) {
        // Wait for loadFamilies to finish, then auto-open the target family
        setTimeout(() => {
            viewFamilyMembers(parseInt(openFamilyId));
        }, 600);
    }
});

/**
 * Fetch all family profiles and render them dynamically
 */
async function loadFamilies() {
    try {
        const response = await fetch('?route=api/family/list');
        if (!response.ok) throw new Error('API server returned error (' + response.status + ')');

        const families = await response.json();
        const tbody = document.getElementById('familyTable');
        if (!tbody) return;

        tbody.innerHTML = '';

        if (families.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No family profiles found.</td>
                </tr>
            `;
            return;
        }

        families.forEach(fam => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="fw-bold text-success">${escapeHtml(fam.family_no)}</td>
                <td>
                    <div class="fw-semibold">${escapeHtml(fam.head_name)}</div>
                    <small class="text-muted">${escapeHtml(fam.head_code)}</small>
                </td>
                <td>
                    <span class="text-truncate d-block" style="max-width: 250px;" title="${escapeHtml(fam.address)}">${escapeHtml(fam.address)}</span>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-light btn-sm border fw-semibold rounded-pill px-3" onclick="viewFamilyMembers(${fam.id}, '${escapeHtml(fam.family_no)}', '${escapeHtml(fam.head_name)}')">
                        <i class="bi bi-people me-1 text-primary"></i> ${fam.total_members} Members
                    </button>
                </td>
                <td class="text-center">
                    <div class="btn-group gap-1">
                        <button type="button" onclick="editFamily(${fam.id})" class="btn btn-outline-primary btn-sm rounded-2" title="Edit Profile">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" onclick="deleteFamily(${fam.id}, '${escapeHtml(fam.family_no)}')" class="btn btn-outline-danger btn-sm rounded-2" title="Delete Profile">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    } catch (error) {
        console.error(error);
        showToast('error', 'Failed to fetch profiles: ' + error.message);
    }
}

/**
 * Fetch members of a family dynamically and display in viewer modal
 */
async function viewFamilyMembers(id, familyNo, headName) {
    try {
        const response = await fetch('?route=api/family/detail&id=' + id);
        if (!response.ok) throw new Error('API server returned error (' + response.status + ')');

        const result = await response.json();
        if (result.status !== 'success') throw new Error(result.message);

        const fam = result.family;
        const members = result.members || [];

        // Build Socio-Economic health summary card
        let detailsHtml = `
            <div class="card bg-light border-0 shadow-sm mb-4">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-3 text-success d-flex align-items-center"><i class="bi bi-heart-pulse-fill me-2"></i>Health & Socio-Economic Indicators</h6>
                    <div class="row g-3">
                        <div class="col-12 col-sm-6 col-md-4">
                            <small class="text-muted d-block">Occupation (Head)</small>
                            <strong class="text-dark small">${escapeHtml(fam.occupation || 'Not Specified')}</strong>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <small class="text-muted d-block">Educational Attainment</small>
                            <strong class="text-dark small">${escapeHtml(fam.educational_attainment || 'Not Specified')}</strong>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <small class="text-muted d-block">Family Planning Method</small>
                            <strong class="text-dark small">${escapeHtml(fam.family_planning_status || 'None')}</strong>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <small class="text-muted d-block">Toilet Facility Type</small>
                            <strong class="text-dark small">${escapeHtml(fam.toilet_type || 'Not Specified')}</strong>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <small class="text-muted d-block">Water Source</small>
                            <strong class="text-dark small">${escapeHtml(fam.water_source || 'Not Specified')}</strong>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <small class="text-muted d-block">Food Production Activity</small>
                            <strong class="text-dark small">${escapeHtml(fam.food_production_activity || 'Not Specified')}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <h6 class="fw-bold mb-3 text-success d-flex align-items-center"><i class="bi bi-people-fill me-2"></i>Household Members List</h6>
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Resident ID</th>
                            <th>Member Name</th>
                            <th>Gender</th>
                            <th>Age</th>
                            <th>Relationship to Head</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        members.forEach(m => {
            const genderBadge = m.gender === 'Male'
                ? '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle"><i class="bi bi-gender-male me-1"></i> Male</span>'
                : (m.gender === 'Female' ? '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle"><i class="bi bi-gender-female me-1"></i> Female</span>' : '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle">Other</span>');
                
            detailsHtml += `
                <tr>
                    <td class="fw-semibold text-primary font-monospace">${escapeHtml(m.resident_code || m.resident_id)}</td>
                    <td class="fw-medium">${escapeHtml(m.last_name)}, ${escapeHtml(m.first_name)} <small class="text-muted">${escapeHtml(m.middle_name || '')}</small></td>
                    <td>${genderBadge}</td>
                    <td>${escapeHtml(m.age)} yrs</td>
                    <td>
                        <span class="badge bg-${m.relationship_to_head === 'Head' || m.relationship_to_head === 'Family Head' ? 'success' : 'secondary'} bg-opacity-10 text-${m.relationship_to_head === 'Head' || m.relationship_to_head === 'Family Head' ? 'success' : 'secondary'} border">
                            ${escapeHtml(m.relationship_to_head)}
                        </span>
                    </td>
                </tr>
            `;
        });

        detailsHtml += `
                    </tbody>
                </table>
            </div>
        `;

        const fNo = familyNo || fam.family_no || '';
        const hName = headName || fam.head_name || '';

        document.getElementById('membersModalTitle').innerHTML = `<i class="bi bi-house-fill me-2"></i>Household: ${fNo} (Head: ${hName})`;
        document.getElementById('membersModalBody').innerHTML = detailsHtml;

        if (membersModal) membersModal.show();
    } catch (error) {
        showToast('error', 'Failed to retrieve family members: ' + error.message);
    }
}
/**
 * Open the modal to create a new family profile
 */
function newFamily() {
    const form = document.getElementById('familyForm');
    if (form) {
        form.reset();
        form.classList.remove('was-validated');
    }

    document.getElementById('familyId').value = '';
    document.getElementById('modalTitleText').innerText = 'Create Family Profile';
    document.getElementById('saveButton').innerText = 'Create Profile';

    // Clear members table and add a default row
    const tbody = document.getElementById('modalMembersTable');
    if (tbody) {
        tbody.innerHTML = '';
        addMemberRow();
    }

    // Reset searchable select on head dropdown
    const headSelect = document.getElementById('modalHeadId');
    if (headSelect) {
        headSelect.value = '';
        if (typeof App !== 'undefined' && App.initSearchableSelect) {
            App.initSearchableSelect(headSelect);
        }
    }

    if (familyModal) familyModal.show();
}

/**
 * Append a row inside the household members table
 */
function addMemberRow(residentId = '', relationship = '') {
    const tbody = document.getElementById('modalMembersTable');
    if (!tbody) return;

    const tr = document.createElement('tr');
    
    // Copy dropdown items from template
    const template = document.getElementById('memberDropdownTemplate');
    const select = template.cloneNode(true);
    select.id = ''; // remove id
    select.className = 'form-select form-select-sm modal-member-select';
    select.name = 'member_resident_id[]';
    select.required = true;
    
    if (residentId) {
        select.value = residentId;
    }

    const tdSelect = document.createElement('td');
    tdSelect.appendChild(select);
    const feedback1 = document.createElement('div');
    feedback1.className = 'invalid-feedback';
    feedback1.textContent = 'Please select a resident member.';
    tdSelect.appendChild(feedback1);

    const tdRel = document.createElement('td');
    tdRel.innerHTML = `<input type="text" class="form-control form-control-sm" name="member_relationship[]" placeholder="e.g. Spouse, Child, Parent" required value="${escapeHtml(relationship)}">
                        <div class="invalid-feedback">Relationship status is required.</div>`;

    const tdBtn = document.createElement('td');
    tdBtn.className = 'text-center';
    tdBtn.innerHTML = `<button type="button" class="btn btn-outline-danger btn-sm" onclick="removeMemberRow(this)" title="Remove Row">
                            <i class="bi bi-trash"></i>
                       </button>`;

    tr.appendChild(tdSelect);
    tr.appendChild(tdRel);
    tr.appendChild(tdBtn);

    tbody.appendChild(tr);

    // Initialize custom searchable select on the newly appended row select
    if (typeof App !== 'undefined' && typeof App.initSearchableSelect === 'function') {
        App.initSearchableSelect(select);
    }
}

/**
 * Remove a row from the household members table
 */
function removeMemberRow(button) {
    const tr = button.closest('tr');
    if (tr) tr.remove();
}

/**
 * Fetch details and populates form in Edit Family Modal
 */
async function editFamily(id) {
    try {
        const response = await fetch('?route=api/family/detail&id=' + id);
        if (!response.ok) throw new Error('API server returned error (' + response.status + ')');

        const result = await response.json();
        if (result.status !== 'success') throw new Error(result.message);

        const fam = result.family;
        const members = result.members || [];

        const form = document.getElementById('familyForm');
        form.reset();
        form.classList.remove('was-validated');

        document.getElementById('familyId').value = fam.id;
        
        const headSelect = document.getElementById('modalHeadId');
        if (headSelect) {
            headSelect.value = fam.head_resident_id;
            headSelect.dispatchEvent(new Event('change'));
        }

        document.getElementById('modalAddress').value = fam.address;
        document.getElementById('modalOccupation').value = fam.occupation || '';
        document.getElementById('modalEducation').value = fam.educational_attainment || '';
        document.getElementById('modalPlanning').value = fam.family_planning_status || '';
        document.getElementById('modalToilet').value = fam.toilet_type || '';
        document.getElementById('modalWater').value = fam.water_source || '';
        document.getElementById('modalFood').value = fam.food_production_activity || '';

        // Clear member rows
        const tbody = document.getElementById('modalMembersTable');
        tbody.innerHTML = '';

        // Populate member rows, excluding head
        members.forEach(m => {
            if (parseInt(m.resident_id) !== parseInt(fam.head_resident_id)) {
                addMemberRow(m.resident_id, m.relationship_to_head);
            }
        });

        // Trigger gender indicators toggle
        if (headSelect) {
            const opt = headSelect.options[headSelect.selectedIndex];
            toggleGenderIndicators(opt);
        }

        document.getElementById('modalTitleText').textContent = 'Edit Family Profile Details';
        document.getElementById('saveButton').textContent = 'Update Profile';

        if (familyModal) familyModal.show();
    } catch (error) {
        showToast('error', 'Error loading details: ' + error.message);
    }
}

async function handleFormSubmit(e) {
    e.preventDefault();

    const form = e.target;
    if (!form.checkValidity()) {
        e.stopPropagation();
        form.classList.add('was-validated');
        return;
    }

    const id = document.getElementById('familyId').value;
    const head_resident_id = document.getElementById('modalHeadId').value;
    const address = document.getElementById('modalAddress').value.trim();
    const occupation = document.getElementById('modalOccupation').value.trim();
    const educational_attainment = document.getElementById('modalEducation').value.trim();
    const family_planning_status = document.getElementById('modalPlanning').value.trim();
    const toilet_type = document.getElementById('modalToilet').value.trim();
    const water_source = document.getElementById('modalWater').value.trim();
    const food_production_activity = document.getElementById('modalFood').value.trim();

    const members = [];
    const memberRows = document.querySelectorAll('#modalMembersTable tr');
    memberRows.forEach(row => {
        const select = row.querySelector('.modal-member-select');
        const input = row.querySelector('input[name="member_relationship[]"]');
        if (select && select.value && input && input.value.trim()) {
            members.push({
                resident_id: parseInt(select.value),
                relationship: input.value.trim()
            });
        }
    });

    const data = {
        head_resident_id: parseInt(head_resident_id),
        address,
        occupation,
        educational_attainment,
        family_planning_status,
        toilet_type,
        water_source,
        food_production_activity,
        members
    };

    let apiUrl = '?route=api/family/store';
    if (id !== '') {
        data.id = parseInt(id);
        apiUrl = '?route=api/family/update';
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
            showToast('success', result.message);
            if (familyModal) familyModal.hide();
            loadFamilies();
        } else {
            showToast('error', result.message);
        }
    } catch (error) {
        showToast('error', 'Failed to save family profile: ' + error.message);
    }
}

function deleteFamily(id, familyNo) {
    Swal.fire({
        title: 'Delete Family Profile?',
        text: `Are you sure you want to delete family profile #${familyNo}? All members will be unlinked.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const response = await fetch('?route=api/family/delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: id })
                });

                if (!response.ok) throw new Error('API server returned error (' + response.status + ')');

                const res = await response.json();
                if (res.status === 'success') {
                    showToast('success', res.message);
                    loadFamilies();
                } else {
                    showToast('error', res.message);
                }
            } catch (error) {
                showToast('error', 'Failed to delete family profile: ' + error.message);
            }
        }
    });
}
/**
 * Show/hide gender indicators based on head gender (e.g. pregnancy, child feeding)
 */
function toggleGenderIndicators(option) {
    const gender = option ? option.getAttribute('data-gender') : '';
    const pregContainer = document.getElementById('modalPregnancyContainer');
    const feedingContainer = document.getElementById('modalFeedingContainer');
    const pregInput = document.getElementById('modalPregnancy');
    const feedingInput = document.getElementById('modalFeeding');

    if (gender === 'Male') {
        if (pregContainer) pregContainer.style.display = 'none';
        if (feedingContainer) feedingContainer.style.display = 'none';
        if (pregInput) pregInput.value = 'N/A';
        if (feedingInput) feedingInput.value = '';
    } else {
        if (pregContainer) pregContainer.style.display = 'block';
        if (feedingContainer) feedingContainer.style.display = 'block';
    }
}
/**
 * Local client-side search filtering
 */
function handleSearch(event) {
    const keyword = event.target.value.toLowerCase().trim();
    const rows = document.querySelectorAll('#familyTable tr');

    rows.forEach(row => {
        if (row.cells.length === 1 && row.cells[0].colSpan > 1) return;
        
        const rowText = row.innerText.toLowerCase();
        row.style.display = rowText.includes(keyword) ? '' : 'none';
    });
}


/**
 * SweetAlert Toast alert helper
 */
function showToast(icon, message) {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    Toast.fire({
        icon: icon,
        title: message
    });
}

/**
 * Helper to escape HTML characters
 */
function escapeHtml(string) {
    if (!string) return '';
    return String(string)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}


