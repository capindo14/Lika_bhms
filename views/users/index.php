<?php
require_once LAYOUT_PATH . 'header.php';
require_once LAYOUT_PATH . 'sidebar.php';
?>

<div class="main-content-wrapper">
    <?php require_once LAYOUT_PATH . 'navbar.php'; ?>
    
    <div class="container-fluid p-3 p-md-4">
        
        <!-- Header -->
        <div class="mb-4">
            <h3 class="fw-bold mb-1"><?= escape($pageTitle) ?></h3>
            <p class="text-muted small mb-0">Manage system profiles, and review active audit trails.</p>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs border-bottom mb-4" id="usersTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold text-secondary" id="accounts-tab" data-bs-toggle="tab" data-bs-target="#accounts-view" type="button" role="tab" aria-controls="accounts-view" aria-selected="true">
                    <i class="bi bi-person-badge-fill me-1"></i> User Accounts
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-secondary" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs-view" type="button" role="tab" aria-controls="logs-view" aria-selected="false">
                    <i class="bi bi-journal-text me-1"></i> System Audit Trails
                </button>
            </li>
        </ul>

        <!-- Real-time Search Box and Action Buttons -->
        <div class="row align-items-center mb-4 g-3">
            <div class="col-12 col-md-6 col-lg-4">
                <div class="input-group shadow-sm border rounded">
                    <span class="input-group-text bg-white border-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="usersSearch" class="form-control border-0" placeholder="Search users or system audit logs...">
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-8 d-flex justify-content-md-end gap-2">
                <button type="button" class="btn btn-primary" onclick="newUser()">
                    <i class="bi bi-person-plus-fill me-1"></i> Create System Account
                </button>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="tab-content" id="usersTabsContent">
            
            <!-- User Accounts Directory -->
            <div class="tab-pane fade show active" id="accounts-view" role="tabpanel" aria-labelledby="accounts-tab">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Username</th>
                                        <th>Role Authority</th>
                                        <th>Status</th>
                                        <th>Date Created</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="usersTable">
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <div class="text-muted mt-2 small">Loading system accounts...</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Audit Trail Logs -->
            <div class="tab-pane fade" id="logs-view" role="tabpanel" aria-labelledby="logs-tab">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                            <table class="table table-hover table-striped align-middle w-100">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Timestamp</th>
                                        <th>User Account</th>
                                        <th>Action Tag</th>
                                        <th>Log Description</th>
                                    </tr>
                                </thead>
                                <tbody id="auditLogsTable">
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <div class="text-muted small">Select User Accounts tab to trigger initial fetch.</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<!-- Create/Edit User Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary bg-gradient text-white">
                <h5 class="modal-title fw-bold" id="userModalLabel">
                    <i class="bi bi-person-badge-fill me-2"></i><span id="modalTitleText">Create System Account</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="userForm" class="needs-validation" novalidate autocomplete="off">
                <input type="hidden" id="userId" name="id">
                
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Full Name -->
                        <div class="col-12">
                            <label for="modalFullname" class="form-label fw-semibold small">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modalFullname" name="fullname" required placeholder="e.g. Juan De La Cruz" minlength="3">
                            <div class="invalid-feedback">Please enter a valid full name (at least 3 characters).</div>
                        </div>

                        <!-- Username -->
                        <div class="col-12">
                            <label for="modalUsername" class="form-label fw-semibold small">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modalUsername" name="username" required placeholder="e.g. juan_dlc" minlength="4" autocomplete="new-username">
                            <div class="invalid-feedback">Please enter a unique username (at least 4 characters).</div>
                        </div>

                        <!-- Password -->
                        <div class="col-12">
                            <label for="modalPassword" class="form-label fw-semibold small" id="passwordLabel">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="modalPassword" name="password" required placeholder="Enter password (min 6 chars)" minlength="6" autocomplete="new-password">
                            <small class="text-muted" id="passwordHelpText" style="display: none;">Leave blank to retain current password.</small>
                            <div class="invalid-feedback" id="passwordFeedback">Please enter a password with at least 6 characters.</div>
                        </div>

                        <!-- Role -->
                        <div class="col-12 col-md-6">
                            <label for="modalRole" class="form-label fw-semibold small">Role Authority <span class="text-danger">*</span></label>
                            <select class="form-select" id="modalRole" name="role" required>
                                <option value="" selected disabled>Select Role</option>
                                <option value="Admin">Admin (Full Control)</option>
                                <option value="Health Worker">Health Worker (Standard Logs)</option>
                                <option value="Staff">Staff (Read / Dispense)</option>
                            </select>
                            <div class="invalid-feedback">Please assign an account role.</div>
                        </div>

                        <!-- Status -->
                        <div class="col-12 col-md-6">
                            <label for="modalStatus" class="form-label fw-semibold small">Account Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="modalStatus" name="status" required>
                                <option value="Active" selected>Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4" id="saveButton">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
$pageScript = url('js/users_js.js');
require_once LAYOUT_PATH . 'footer.php'; 
?>
