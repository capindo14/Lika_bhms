/**
 * JS-Driven Users & System Logs CRUD Controller
 */

let userModal;

document.addEventListener('DOMContentLoaded', () => {
    // Initialize Bootstrap Modal
    const modalEl = document.getElementById('userModal');
    if (modalEl) {
        userModal = new bootstrap.Modal(modalEl);
    }

    // Load initial list
    loadUsers();

    // Form submit listener
    const form = document.getElementById('userForm');
    if (form) {
        form.addEventListener('submit', handleFormSubmit);
    }

    // Live search indexing
    const searchInput = document.getElementById('usersSearch');
    if (searchInput) {
        searchInput.addEventListener('input', handleSearch);
    }
});

/**
 * Fetch and render all user accounts and activity logs
 */
async function loadUsers() {
    try {
        const response = await fetch('?route=api/users/list');
        if (!response.ok) throw new Error('API server returned error (' + response.status + ')');

        const data = await response.json();
        
        // Render Users
        renderUsers(data.users || []);

        // Render Audit Logs
        renderAuditLogs(data.logs || []);

    } catch (error) {
        console.error(error);
        showToast('error', 'Failed to fetch directory details: ' + error.message);
    }
}

/**
 * Populate users table dynamically
 */
function renderUsers(users) {
    const tbody = document.getElementById('usersTable');
    if (!tbody) return;

    tbody.innerHTML = '';

    if (users.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted py-4">No system accounts found.</td>
            </tr>
        `;
        return;
    }

    users.forEach(u => {
        let roleBadge = '';
        if (u.role === 'Admin') {
            roleBadge = '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle"><i class="bi bi-shield-fill-check me-1"></i> Admin</span>';
        } else if (u.role === 'Health Worker') {
            roleBadge = '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle"><i class="bi bi-heart-pulse-fill me-1"></i> Health Worker</span>';
        } else {
            roleBadge = '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle"><i class="bi bi-person-fill me-1"></i> Staff</span>';
        }

        const statusBadge = u.status === 'Active'
            ? '<span class="badge bg-success bg-opacity-10 text-success border border-success-subtle">Active</span>'
            : '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle">Inactive</span>';

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <div class="fw-bold text-dark">${escapeHtml(u.fullname)}</div>
            </td>
            <td class="font-monospace text-primary fw-semibold">${escapeHtml(u.username)}</td>
            <td>${roleBadge}</td>
            <td>${statusBadge}</td>
            <td class="small text-muted">${escapeHtml(u.created_at || '--')}</td>
            <td class="text-center">
                <div class="btn-group gap-1">
                    <button type="button" onclick="editUser(${u.id})" class="btn btn-outline-primary btn-sm rounded-2" title="Edit Profile">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" onclick="deleteUser(${u.id}, '${escapeHtml(u.username)}')" class="btn btn-outline-danger btn-sm rounded-2" title="Delete Account">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

/**
 * Populate Audit Trail logs dynamically
 */
function renderAuditLogs(logs) {
    const tbody = document.getElementById('auditLogsTable');
    if (!tbody) return;

    tbody.innerHTML = '';

    if (logs.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-muted py-4">No audit logs found.</td>
            </tr>
        `;
        return;
    }

    logs.forEach(log => {
        let actionBadge = '';
        const act = log.action_tag || '';
        
        if (act.includes('CREATE')) {
            actionBadge = '<span class="badge bg-success bg-opacity-10 text-success border border-success-subtle">' + escapeHtml(act) + '</span>';
        } else if (act.includes('DELETE')) {
            actionBadge = '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle">' + escapeHtml(act) + '</span>';
        } else if (act.includes('UPDATE') || act.includes('EDIT')) {
            actionBadge = '<span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle">' + escapeHtml(act) + '</span>';
        } else {
            actionBadge = '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle">' + escapeHtml(act) + '</span>';
        }

        const workerName = log.fullname ? `${escapeHtml(log.fullname)} (${escapeHtml(log.username)})` : '<span class="text-muted small">System / Unknown</span>';

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="small text-muted font-monospace">${escapeHtml(log.created_at)}</td>
            <td class="small fw-semibold">${workerName}</td>
            <td>${actionBadge}</td>
            <td class="small text-dark">${escapeHtml(log.description)}</td>
        `;
        tbody.appendChild(tr);
    });
}

/**
 * Configure modal form for a new system user
 */
function newUser() {
    const form = document.getElementById('userForm');
    if (!form) return;

    form.reset();
    form.classList.remove('was-validated');

    document.getElementById('userId').value = '';
    document.getElementById('modalTitleText').textContent = 'Create System Account';
    document.getElementById('saveButton').textContent = 'Create Account';

    // Password required for new account
    const passInput = document.getElementById('modalPassword');
    passInput.setAttribute('required', 'required');
    document.getElementById('passwordLabel').innerHTML = 'Password <span class="text-danger">*</span>';
    document.getElementById('passwordHelpText').style.display = 'none';

    if (userModal) userModal.show();
}

/**
 * Fetch details and populate form in Edit User Modal
 */
async function editUser(id) {
    try {
        const response = await fetch('?route=api/users/detail&id=' + id);
        if (!response.ok) throw new Error('API server returned error (' + response.status + ')');

        const u = await response.json();
        if (u.status === 'error') throw new Error(u.message);

        const form = document.getElementById('userForm');
        form.reset();
        form.classList.remove('was-validated');

        document.getElementById('userId').value = u.id;
        document.getElementById('modalFullname').value = u.fullname;
        document.getElementById('modalUsername').value = u.username;
        document.getElementById('modalRole').value = u.role;
        document.getElementById('modalStatus').value = u.status;

        // Password optional for edits
        const passInput = document.getElementById('modalPassword');
        passInput.removeAttribute('required');
        document.getElementById('passwordLabel').innerHTML = 'Reset Password';
        document.getElementById('passwordHelpText').style.display = 'block';

        document.getElementById('modalTitleText').textContent = 'Edit System Account Details';
        document.getElementById('saveButton').textContent = 'Update Profile';

        if (userModal) userModal.show();
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

    const id = document.getElementById('userId').value;
    const fullname = document.getElementById('modalFullname').value.trim();
    const username = document.getElementById('modalUsername').value.trim();
    const password = document.getElementById('modalPassword').value;
    const role = document.getElementById('modalRole').value;
    const status = document.getElementById('modalStatus').value;

    const data = {
        fullname,
        username,
        role,
        status
    };

    let apiUrl = '?route=api/users/store';
    if (id !== '') {
        data.id = parseInt(id);
        apiUrl = '?route=api/users/update';
        
        // Pass password only if user filled it out
        if (password !== '') {
            data.password = password;
        }
    } else {
        // Create requires password
        data.password = password;
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
            if (userModal) userModal.hide();
            showToast('success', result.message);
            loadUsers();
        } else {
            showToast('error', result.message);
        }
    } catch (error) {
        showToast('error', 'Request failed: ' + error.message);
    }
}

/**
 * Delete a user account with verification rules
 */
function deleteUser(id, username) {
    const isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';

    Swal.fire({
        title: 'Delete System User?',
        text: `Are you sure you want to delete user account: ${username}? Access to system will be immediately terminated.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel',
        background: isDarkMode ? '#212529' : '#fff',
        color: isDarkMode ? '#f8f9fa' : '#212529'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const response = await fetch('?route=api/users/delete', {
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
                    loadUsers();
                } else {
                    showToast('error', res.message);
                }
            } catch (error) {
                showToast('error', 'Failed to delete user: ' + error.message);
            }
        }
    });
}

/**
 * Local real-time search filter matching active tab layout
 */
function handleSearch(event) {
    const keyword = event.target.value.toLowerCase().trim();
    
    // Find which tab view is currently active
    const activeTab = document.querySelector('.tab-pane.active');
    if (!activeTab) return;

    const rows = activeTab.querySelectorAll('tbody tr');

    rows.forEach(row => {
        // Skip empty rows indicators
        if (row.cells.length === 1 && row.cells[0].colSpan > 1) return;
        
        const rowText = row.innerText.toLowerCase();
        row.style.display = rowText.includes(keyword) ? '' : 'none';
    });
}
