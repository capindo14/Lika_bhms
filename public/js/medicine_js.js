/**
 * JS-Driven Medicines CRUD Logic
 */

// Global references for Bootstrap Modal instances
let medicineModal;
let restockModal;
let distributionModal;
let viewDistributionModal;

document.addEventListener('DOMContentLoaded', () => {
    // Initialize Modals
    const modalEl = document.getElementById('medicineModal');
    if (modalEl) {
        medicineModal = new bootstrap.Modal(modalEl);
    }
    const restockModalEl = document.getElementById('restockModal');
    if (restockModalEl) {
        restockModal = new bootstrap.Modal(restockModalEl);
    }
    const distModalEl = document.getElementById('distributionModal');
    if (distModalEl) {
        distributionModal = new bootstrap.Modal(distModalEl);
    }
    const viewDistModalEl = document.getElementById('viewDistributionModal');
    if (viewDistModalEl) {
        viewDistributionModal = new bootstrap.Modal(viewDistModalEl);
    }

    // Load initial data lists
    loadMedicines();
    loadDistributions();

    // Form submission listeners
    const form = document.getElementById('medicineForm');
    if (form) {
        form.addEventListener('submit', handleMedicineSubmit);
    }
    const restockForm = document.getElementById('restockForm');
    if (restockForm) {
        restockForm.addEventListener('submit', handleRestockSubmit);
    }
    const distForm = document.getElementById('distributionForm');
    if (distForm) {
        distForm.addEventListener('submit', handleDistSubmit);
    }

    // Real-time search key listener
    const searchInput = document.getElementById('medicineSearch');
    if (searchInput) {
        searchInput.addEventListener('input', handleSearchAndFilter);
    }
    const catFilter = document.getElementById('medicineCategoryFilter');
    if (catFilter) {
        catFilter.addEventListener('change', handleSearchAndFilter);
    }
    const statusFilter = document.getElementById('medicineStatusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', handleSearchAndFilter);
    }
    const distSearch = document.getElementById('distributionSearch');
    if (distSearch) {
        distSearch.addEventListener('input', handleDistSearchAndFilter);
    }
    const distCatFilter = document.getElementById('distributionCategoryFilter');
    if (distCatFilter) {
        distCatFilter.addEventListener('change', handleDistSearchAndFilter);
    }

    // Select dropdown listener
    const distMedSelect = document.getElementById('distMedicineId');
    if (distMedSelect) {
        distMedSelect.addEventListener('change', handleDistMedChange);
    }
});

/**
 * Fetch all records via JSON API and render using DOM manipulation
 */
async function loadMedicines() {
    try {
        const response = await fetch('?route=api/medicine/list');
        if (!response.ok) throw new Error('API server error (' + response.status + ')');
        
        const medicines = await response.json();
        const tbody = document.getElementById('medicineTable');
        if (!tbody) return;
        
        tbody.innerHTML = '';
        
        if (medicines.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No inventory items found.</td>
                </tr>
            `;
            return;
        }
        
        medicines.forEach(med => {
            const isLow = parseInt(med.stock_qty) <= parseInt(med.reorder_level);
            const isOut = parseInt(med.stock_qty) === 0;
            
            let statusBadge = '';
            if (isOut) {
                statusBadge = '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle">Out of Stock</span>';
            } else if (isLow) {
                statusBadge = '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle">Low Stock</span>';
            } else {
                statusBadge = '<span class="badge bg-success bg-opacity-10 text-success border border-success-subtle">Good Stock</span>';
            }
            
            let categoryIcon = '';
            if (med.category === 'Medicine') {
                categoryIcon = '<i class="bi bi-capsule me-1 text-primary"></i>';
            } else if (med.category === 'Vaccine') {
                categoryIcon = '<i class="bi bi-shield-plus me-1 text-success"></i>';
            } else if (med.category === 'Family Planning') {
                categoryIcon = '<i class="bi bi-heart-pulse me-1 text-danger"></i>';
            } else if (med.category === 'Supply') {
                categoryIcon = '<i class="bi bi-box-seam me-1 text-info"></i>';
            } else {
                categoryIcon = '<i class="bi bi-box me-1 text-muted"></i>';
            }
            
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="font-monospace fw-semibold text-success">${escapeHtml(med.code)}</td>
                <td class="fw-bold">${escapeHtml(med.name)}</td>
                <td>${categoryIcon} ${escapeHtml(med.category)}</td>
                <td>
                    <span class="badge ${isOut ? 'bg-danger' : (isLow ? 'bg-warning text-dark' : 'bg-success')} bg-opacity-10 text-${isOut ? 'danger' : (isLow ? 'warning' : 'success')} border">
                        ${med.stock_qty} pcs
                    </span>
                </td>
                <td class="text-center">${med.reorder_level} pcs</td>
                <td class="text-center">${statusBadge}</td>
                <td class="text-center">
                    <div class="btn-group gap-1">
                        <button type="button" onclick="editMedicine(${med.id})" class="btn btn-outline-primary btn-sm rounded-2" title="Edit Item">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" onclick="triggerRestock(${med.id}, '${escapeHtml(med.name.replace(/'/g, "\\'"))}', '${escapeHtml(med.code)}')" class="btn btn-outline-success btn-sm rounded-2" title="Restock Item">
                            <i class="bi bi-plus-circle"></i>
                        </button>
                        <button type="button" onclick="deleteMedicine(${med.id}, '${escapeHtml(med.name.replace(/'/g, "\\'"))}')" class="btn btn-outline-danger btn-sm rounded-2" title="Delete Item">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    } catch (error) {
        console.error(error);
        showToast('error', 'Failed to fetch inventory: ' + error.message);
    }
}

/**
 * Open medicine modal in "Add" mode
 */
function newMedicine() {
    document.getElementById('modalTitleText').innerText = 'Add Inventory Item';
    document.getElementById('medicineId').value = '';
    document.getElementById('medicineForm').reset();
    document.getElementById('medicineForm').classList.remove('was-validated');
    
    const stockContainer = document.getElementById('stockQtyContainer');
    if (stockContainer) stockContainer.style.display = '';
    
    document.getElementById('saveButton').innerText = 'Save Item';
    if (medicineModal) medicineModal.show();
}

/**
 * Fetch details and open medicine modal in "Edit" mode
 */
async function editMedicine(id) {
    try {
        const response = await fetch('?route=api/medicine/detail&id=' + id);
        if (!response.ok) throw new Error('API server returned error (' + response.status + ')');
        
        const med = await response.json();
        
        document.getElementById('modalTitleText').innerText = 'Edit Inventory Item';
        document.getElementById('medicineId').value = med.id;
        document.getElementById('medName').value = med.name;
        document.getElementById('medCategory').value = med.category;
        document.getElementById('medStockQty').value = med.stock_qty;
        document.getElementById('medReorderLevel').value = med.reorder_level;
        document.getElementById('medDescription').value = med.description || '';
        
        // Hide stock qty container in edit mode
        const stockContainer = document.getElementById('stockQtyContainer');
        if (stockContainer) stockContainer.style.display = 'none';
        
        document.getElementById('saveButton').innerText = 'Save Changes';
        if (medicineModal) medicineModal.show();
    } catch (error) {
        showToast('error', 'Error loading details: ' + error.message);
    }
}

/**
 * Handle medicine form submit via JSON POST
 */
async function handleMedicineSubmit(e) {
    e.preventDefault();
    
    const form = e.target;
    if (!form.checkValidity()) {
        e.stopPropagation();
        form.classList.add('was-validated');
        return;
    }
    
    const id = document.getElementById('medicineId').value;
    const name = document.getElementById('medName').value.trim();
    const category = document.getElementById('medCategory').value;
    const stock_qty = parseInt(document.getElementById('medStockQty').value);
    const reorder_level = parseInt(document.getElementById('medReorderLevel').value);
    const description = document.getElementById('medDescription').value.trim();
    
    const data = {
        name,
        category,
        stock_qty,
        reorder_level,
        description
    };
    
    let apiUrl = '?route=api/medicine/store';
    if (id !== '') {
        data.id = parseInt(id);
        apiUrl = '?route=api/medicine/update';
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
            if (medicineModal) medicineModal.hide();
            loadMedicines();
        } else {
            showToast('error', result.message);
        }
    } catch (error) {
        showToast('error', 'Failed to save item: ' + error.message);
    }
}

/**
 * Delete medicine with SweetAlert confirmation
 */
function deleteMedicine(id, name) {
    const isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    
    Swal.fire({
        title: 'Delete Item?',
        text: 'Are you sure you want to remove ' + name + ' from the inventory? This cannot be undone.',
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
                const response = await fetch('?route=api/medicine/delete', {
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
                    loadMedicines();
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
 * Filter visible rows instantly based on keyword, category, and status (Client-side Search)
 */
function handleSearchAndFilter() {
    const searchInput = document.getElementById('medicineSearch');
    const categorySelect = document.getElementById('medicineCategoryFilter');
    const statusSelect = document.getElementById('medicineStatusFilter');
    
    const keyword = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const categoryFilter = categorySelect ? categorySelect.value.toLowerCase() : '';
    const statusFilter = statusSelect ? statusSelect.value.toLowerCase() : '';
    
    const rows = document.querySelectorAll('#medicineTable tr');
    
    rows.forEach(row => {
        // Skip loading/empty rows
        if (row.cells.length === 1 && row.cells[0].colSpan > 1) return;
        
        const rowText = row.innerText.toLowerCase();
        // Category is in the 3rd column (index 2)
        const categoryCellText = row.cells[2] ? row.cells[2].innerText.toLowerCase() : '';
        // Status is in the 6th column (index 5)
        const statusCellText = row.cells[5] ? row.cells[5].innerText.toLowerCase() : '';
        
        const matchesSearch = rowText.includes(keyword);
        const matchesCategory = categoryFilter === '' || categoryCellText.includes(categoryFilter);
        const matchesStatus = statusFilter === '' || statusCellText.includes(statusFilter);
        
        row.style.display = (matchesSearch && matchesCategory && matchesStatus) ? '' : 'none';
    });
}

// Use the global `showToast(icon, message)` helper defined in `public/js/app.js`

/**
 * Open the restock modal with target item info
 */
function triggerRestock(id, name, code) {
    document.getElementById('restockMedicineId').value = id;
    document.getElementById('restockItemName').textContent = name;
    document.getElementById('restockItemCode').textContent = code;
    document.getElementById('restockQty').value = '';
    if (restockModal) restockModal.show();
}

/**
 * Handle restock form submit
 */
async function handleRestockSubmit(e) {
    e.preventDefault();
    const id = document.getElementById('restockMedicineId').value;
    const qty = parseInt(document.getElementById('restockQty').value);
    
    if (!qty || qty <= 0) {
        showToast('error', 'Please enter a valid quantity.');
        return;
    }
    
    try {
        const response = await fetch('?route=api/medicine/restock', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ medicine_id: id, restock_qty: qty })
        });
        
        if (!response.ok) throw new Error('API server returned error (' + response.status + ')');
        
        const result = await response.json();
        if (result.status === 'success') {
            if (restockModal) restockModal.hide();
            showToast('success', result.message);
            loadMedicines();
        } else {
            showToast('error', result.message);
        }
    } catch (error) {
        showToast('error', 'Restock failed: ' + error.message);
    }
}

/**
 * Fetch all distribution records via JSON API and render in Table Tab 2
 */
async function loadDistributions() {
    try {
        const response = await fetch('?route=medicine/distribution_list');
        if (!response.ok) throw new Error('API server error (' + response.status + ')');
        
        const distributions = await response.json();
        const tbody = document.getElementById('distributionTable');
        if (!tbody) return;
        
        tbody.innerHTML = '';
        
        if (distributions.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No distribution logs recorded yet.</td>
                </tr>
            `;
            return;
        }
        
        distributions.forEach(dist => {
            let categoryIcon = '';
            if (dist.medicine_category === 'Medicine') {
                categoryIcon = '<i class="bi bi-capsule me-1 text-primary"></i>';
            } else if (dist.medicine_category === 'Vaccine') {
                categoryIcon = '<i class="bi bi-shield-plus me-1 text-success"></i>';
            } else if (dist.medicine_category === 'Family Planning') {
                categoryIcon = '<i class="bi bi-heart-pulse me-1 text-danger"></i>';
            } else if (dist.medicine_category === 'Supply') {
                categoryIcon = '<i class="bi bi-box-seam me-1 text-info"></i>';
            } else {
                categoryIcon = '<i class="bi bi-box me-1 text-secondary"></i>';
            }

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="small fw-semibold">${escapeHtml(dist.distribution_date)}</td>
                <td>
                    <div class="fw-bold">${escapeHtml(dist.medicine_name)}</div>
                    <small class="text-muted text-primary font-monospace">${escapeHtml(dist.medicine_code)}</small>
                </td>
                <td>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle">
                        ${categoryIcon} ${escapeHtml(dist.medicine_category)}
                    </span>
                </td>
                <td>
                    <div class="fw-semibold text-dark">${escapeHtml(dist.resident_name)}</div>
                    <small class="text-muted text-truncate font-monospace small">ID: ${escapeHtml(dist.resident_code)}</small>
                </td>
                <td class="fw-bold text-success">${parseInt(dist.quantity).toLocaleString()} pcs</td>
                <td class="text-muted small">${escapeHtml(dist.worker_name)}</td>
                <td class="text-center">
                    <div class="btn-group gap-1">
                        <button type="button" onclick="viewDistribution(${dist.id})" class="btn btn-outline-info btn-sm rounded-2" title="View Details & Family Profile">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button type="button" onclick="editDistribution(${dist.id})" class="btn btn-outline-primary btn-sm rounded-2" title="Edit Distribution Log">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" onclick="deleteDistribution(${dist.id}, '${escapeHtml(dist.medicine_name.replace(/'/g, "\\'"))}', ${dist.quantity})" class="btn btn-outline-danger btn-sm rounded-2" title="Cancel Distribution">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    } catch (error) {
        console.error(error);
        showToast('error', 'Unable to fetch distribution logs: ' + error.message);
    }
}

/**
 * Open the distribution modal, loads medicines to select from dynamically
 */
async function newDistribution() {
    const form = document.getElementById('distributionForm');
    if (!form) return;
    
    form.reset();
    form.classList.remove('was-validated');

    document.getElementById('distributionId').value = '';
    const modalTitle = document.getElementById('distModalTitleText');
    if (modalTitle) modalTitle.textContent = 'Log Distribution / Dispensing';
    const saveButton = document.getElementById('saveDistButton');
    if (saveButton) saveButton.textContent = 'Log Dispensing';
    
    document.getElementById('distStockHint').textContent = 'Select an item to see stock.';
    document.getElementById('distQuantity').removeAttribute('max');

    try {
        const response = await fetch('?route=api/medicine/list');
        const medicines = await response.json();
        const select = document.getElementById('distMedicineId');
        
        select.innerHTML = '<option value="" selected disabled>Choose Item...</option>';
        
        // Show active stock items of category 'Family Planning' only
        medicines.forEach(med => {
            if (med.category !== 'Family Planning') return;
            
            const opt = document.createElement('option');
            opt.value = med.id;
            opt.setAttribute('data-stock', med.stock_qty);
            opt.setAttribute('data-category', med.category);
            opt.textContent = `${med.name} [${med.category}] - Code: ${med.code} (${med.stock_qty} available)`;
            select.appendChild(opt);
        });
        
        // Initially load only female residents in the select box
        populateResidentsDropdown(true);
        
        if (distributionModal) distributionModal.show();
    } catch (error) {
        showToast('error', 'Failed to fetch items list: ' + error.message);
    }
}

/**
 * Handle changes in the selected medicine in distribution dropdown
 */
function handleDistMedChange(event) {
    const select = event.target;
    const selectedOpt = select.options[select.selectedIndex];
    const hint = document.getElementById('distStockHint');
    const qtyInput = document.getElementById('distQuantity');
    
    if (selectedOpt && selectedOpt.value !== "") {
        const stock = parseInt(selectedOpt.getAttribute('data-stock')) || 0;
        hint.textContent = `Available stock: ${stock} pcs.`;
        qtyInput.setAttribute('max', stock);
    } else {
        hint.textContent = 'Select an item to see stock.';
        qtyInput.removeAttribute('max');
    }
    populateResidentsDropdown(true);
}

/**
 * Populate or filter the resident selection dropdown dynamically
 */
function populateResidentsDropdown(onlyFemales = true) {
    const select = document.getElementById('distResidentId');
    if (!select || typeof allResidents === 'undefined') return;
    
    const currentValue = select.value;
    select.innerHTML = '<option value="" selected disabled>Choose Resident...</option>';
    
    allResidents.forEach(r => {
        if (onlyFemales && r.gender !== 'Female') return; // Filter to show only females for family planning
        
        const opt = document.createElement('option');
        opt.value = r.id;
        opt.textContent = `${r.name} [${r.gender}, ${r.age} yrs] - ID: ${r.resident_id}`;
        if (r.id == currentValue) opt.selected = true;
        select.appendChild(opt);
    });

    // Rebuild searchable select wrapper
    refreshSearchableSelect('distResidentId');
}

/**
 * Handle distribution form submit via JSON POST
 */
async function handleDistSubmit(event) {
    event.preventDefault();
    const form = event.target;
    
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }
    
    const medicine_id = parseInt(document.getElementById('distMedicineId').value);
    const resident_id = parseInt(document.getElementById('distResidentId').value);
    const quantity = parseInt(document.getElementById('distQuantity').value);
    const distribution_date = document.getElementById('distDate').value;
    const idVal = document.getElementById('distributionId').value;
    
    const data = {
        medicine_id,
        resident_id,
        quantity,
        distribution_date
    };
    
    const isEdit = idVal !== '';
    if (isEdit) {
        data.id = parseInt(idVal);
    }
    
    const url = isEdit ? '?route=medicine/update_distribution' : '?route=medicine/distribute';
    
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
            if (distributionModal) distributionModal.hide();
            showToast('success', result.message);
            loadMedicines();
            loadDistributions();
        } else {
            showToast('error', result.message);
        }
    } catch (error) {
        showToast('error', 'Operation failed: ' + error.message);
    }
}

/**
 * Delete / Cancel distribution record (Swal confirmation, JSON POST delete API request)
 */
function deleteDistribution(id, name, qty) {
    const isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    
    Swal.fire({
        title: 'Delete Distribution?',
        text: `Are you sure you want to remove the distribution of ${qty} pcs ${name}? The stock will be returned to inventory.`,
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
                const response = await fetch('?route=medicine/delete_distribution', {
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
                    loadDistributions();
                } else {
                    showToast('error', res.message);
                }
            } catch (error) {
                showToast('error', 'Failed to delete distribution record: ' + error.message);
            }
        }
    });
}

/**
 * Filter distribution table rows by keyword and category
 */
function handleDistSearchAndFilter() {
    const searchInput = document.getElementById('distributionSearch');
    const categorySelect = document.getElementById('distributionCategoryFilter');
    
    const keyword = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const categoryFilter = categorySelect ? categorySelect.value.toLowerCase() : '';
    
    const rows = document.querySelectorAll('#distributionTable tr');
    
    rows.forEach(row => {
        // Skip loading/empty rows
        if (row.cells.length === 1 && row.cells[0].colSpan > 1) return;
        
        const rowText = row.innerText.toLowerCase();
        // Assuming category is in the 3rd column (index 2)
        const categoryCellText = row.cells[2] ? row.cells[2].innerText.toLowerCase() : '';
        
        const matchesSearch = rowText.includes(keyword);
        const matchesCategory = categoryFilter === '' || categoryCellText.includes(categoryFilter);
        
        row.style.display = (matchesSearch && matchesCategory) ? '' : 'none';
    });
}

/**
 * Re-initialize the custom searchable select engine dynamically
 */
function refreshSearchableSelect(selectId) {
    const $select = $('#' + selectId);
    if ($select.length === 0) return;
    
    // Remove the previously built dropdown input wrappers
    $select.next('.searchable-select-wrapper').remove();
    $select.removeClass('searchable-select-initialized');
    
    // Trigger custom searchable select init from App framework
    if (typeof App !== 'undefined' && typeof App.initSearchableSelect === 'function') {
        App.initSearchableSelect($select);
    }
}

/**
 * Fetch and display single distribution detail modal with family profile matching
 */
async function viewDistribution(id) {
    try {
        const response = await fetch('?route=medicine/distribution_detail&id=' + id);
        if (!response.ok) throw new Error('API server returned error (' + response.status + ')');

        const result = await response.json();
        if (!result.success) throw new Error(result.message);

        const dist = result.distribution;
        const fam = result.family;

        let familyHtml = '';
        if (fam) {
            familyHtml = `
                <div class="card border-info border-opacity-25 bg-info bg-opacity-10 p-3 rounded mt-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="fw-bold text-info mb-0">
                            <i class="bi bi-house-heart-fill me-1"></i> Family Profile Link
                        </h6>
                        <span class="badge bg-info text-white small">${escapeHtml(fam.relationship)}</span>
                    </div>
                    <div class="row g-2 small text-dark">
                        <div class="col-6"><strong>Family No:</strong></div>
                        <div class="col-6 font-monospace">${escapeHtml(fam.family_no)}</div>
                        <div class="col-6"><strong>Family Head:</strong></div>
                        <div class="col-6">${escapeHtml(fam.head_name)}</div>
                    </div>
                    <div class="mt-3">
                        <a href="?route=family&open_family_id=${fam.family_id}" class="btn btn-info text-white btn-sm w-100 fw-bold">
                            <i class="bi bi-house-heart me-1"></i> Open Family Profiles
                        </a>
                    </div>
                </div>
            `;
        } else {
            familyHtml = `
                <div class="card border-warning border-opacity-25 bg-warning bg-opacity-10 p-3 rounded mt-3">
                    <h6 class="fw-bold text-warning mb-2">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Family Profile Missing
                    </h6>
                    <p class="small text-muted mb-3">This resident is not yet assigned to any family profile in the system.</p>
                    <div>
                        <a href="?route=family" class="btn btn-warning text-white btn-sm w-100 fw-bold">
                            <i class="bi bi-plus-lg me-1"></i> Assign Family Profile
                        </a>
                    </div>
                </div>
            `;
        }

        const html = `
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <span class="text-muted small d-block">Dispensed Item</span>
                    <strong class="text-dark fs-6">${escapeHtml(dist.medicine_name)}</strong>
                    <span class="text-muted small d-block font-monospace">${escapeHtml(dist.medicine_code)}</span>
                </div>
                <div class="col-12 col-md-6 text-md-end">
                    <span class="text-muted small d-block">Log Date</span>
                    <strong class="text-dark">${escapeHtml(dist.distribution_date)}</strong>
                </div>
                
                <div class="col-6 mt-3">
                    <span class="text-muted small d-block">Dispensed Quantity</span>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle fw-bold fs-6 px-3 py-1 mt-1">
                        ${parseInt(dist.quantity).toLocaleString()} pcs
                    </span>
                </div>
                <div class="col-6 mt-3 text-md-end">
                    <span class="text-muted small d-block">Dispensed By</span>
                    <span class="fw-semibold text-dark small">${escapeHtml(dist.worker_name)}</span>
                </div>
                
                <div class="col-12"><hr class="my-2"></div>
                
                <div class="col-12">
                    <span class="text-muted small d-block">Recipient Resident</span>
                    <strong class="text-dark fs-6">${escapeHtml(dist.resident_name)}</strong>
                    <span class="text-muted small d-block font-monospace">ID: ${escapeHtml(dist.resident_code)}</span>
                </div>
                
                <div class="col-12">
                    ${familyHtml}
                </div>
            </div>
        `;

        document.getElementById('viewDistributionBody').innerHTML = html;
        if (viewDistributionModal) viewDistributionModal.show();

    } catch (error) {
        showToast('error', 'Error loading distribution details: ' + error.message);
    }
}

/**
 * Fetch distribution details and populate form in Edit Modal
 */
async function editDistribution(id) {
    try {
        const response = await fetch('?route=medicine/distribution_detail&id=' + id);
        if (!response.ok) throw new Error('API server returned error (' + response.status + ')');

        const result = await response.json();
        if (!result.success) throw new Error(result.message);

        const dist = result.distribution;

        const form = document.getElementById('distributionForm');
        form.reset();
        form.classList.remove('was-validated');

        document.getElementById('distributionId').value = dist.id;
        const modalTitle = document.getElementById('distModalTitleText');
        if (modalTitle) modalTitle.textContent = 'Edit Distribution Log';
        const saveButton = document.getElementById('saveDistButton');
        if (saveButton) saveButton.textContent = 'Update Details';

        // Load items dropdown list
        const itemsResponse = await fetch('?route=api/medicine/list');
        const medicines = await itemsResponse.json();
        const select = document.getElementById('distMedicineId');
        select.innerHTML = '<option value="" disabled>Choose Item...</option>';

        medicines.forEach(med => {
            if (med.category !== 'Family Planning') return;
            const opt = document.createElement('option');
            opt.value = med.id;
            opt.setAttribute('data-stock', med.stock_qty);
            opt.setAttribute('data-category', med.category);
            opt.textContent = `${med.name} [${med.category}] - Code: ${med.code} (${med.stock_qty} available)`;
            if (med.id == dist.medicine_id) opt.selected = true;
            select.appendChild(opt);
        });

        // Populate residents
        populateResidentsDropdown(true);
        document.getElementById('distResidentId').value = dist.resident_id;
        refreshSearchableSelect('distResidentId');

        // Set quantity and date
        document.getElementById('distQuantity').value = dist.quantity;
        document.getElementById('distDate').value = dist.distribution_date;

        // Set stock hint (including the currently allocated quantity)
        const selectedItem = select.options[select.selectedIndex];
        if (selectedItem) {
            const stock = parseInt(selectedItem.getAttribute('data-stock')) || 0;
            const totalStock = stock + parseInt(dist.quantity);
            document.getElementById('distStockHint').textContent = `Available stock: ${totalStock} pcs (includes current transaction allocation).`;
            document.getElementById('distQuantity').setAttribute('max', totalStock);
        }

        if (distributionModal) distributionModal.show();
    } catch (error) {
        showToast('error', 'Failed to retrieve transaction details: ' + error.message);
    }
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
