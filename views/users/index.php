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
                <p class="text-muted small mb-0">Manage system operator profiles, credential mappings, and review active audit trails.</p>
            </div>
            <div>
                <a href="<?= url('index.php?route=users/create') ?>" class="btn btn-primary">
                    <i class="bi bi-person-plus-fill me-1"></i> Create System Account
                </a>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs border-bottom mb-4" id="usersTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold text-secondary" id="accounts-tab" data-bs-toggle="tab" data-bs-target="#accounts-view" type="button" role="tab" aria-controls="accounts-view" aria-selected="true">
                    <i class="bi bi-person-badge-fill me-1"></i> User Accounts
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-secondary" id="audit-tab" data-bs-toggle="tab" data-bs-target="#audit-view" type="button" role="tab" aria-controls="audit-view" aria-selected="false">
                    <i class="bi bi-shield-lock-fill me-1"></i> System Audit Logs
                </button>
            </li>
        </ul>

        <div class="tab-content" id="usersTabsContent">
            <!-- TAB 1: User Accounts -->
            <div class="tab-pane fade show active" id="accounts-view" role="tabpanel" aria-labelledby="accounts-tab">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table id="users-table" class="table table-hover align-middle w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Username</th>
                                        <th>Role Assigned</th>
                                        <th>Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $u): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary bg-gradient text-white rounded-circle d-flex align-items-center justify-content-center fw-semibold me-2" style="width: 32px; height: 32px; font-size: 0.85rem;">
                                                        <?= strtoupper(substr($u['fullname'], 0, 1)) ?>
                                                    </div>
                                                    <div class="fw-semibold"><?= escape($u['fullname']) ?></div>
                                                </div>
                                            </td>
                                            <td class="fw-bold text-muted"><?= escape($u['username']) ?></td>
                                            <td>
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle">
                                                    <?= escape($u['role']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $u['status'] === 'Active' ? 'success' : 'secondary' ?> bg-opacity-10 text-<?= $u['status'] === 'Active' ? 'success' : 'secondary' ?> border">
                                                    <?= escape($u['status']) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group gap-1">
                                                    <a href="<?= url("index.php?route=users/edit&id={$u['id']}") ?>" class="btn btn-outline-primary btn-sm rounded-2" title="Edit Profile">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                                        <a href="<?= url("index.php?route=users/toggle_status&id={$u['id']}") ?>" class="btn btn-outline-warning btn-sm rounded-2" title="Toggle Active Status">
                                                            <i class="bi bi-power"></i>
                                                        </a>
                                                        <button onclick="confirmDelete(<?= $u['id'] ?>, '<?= escape($u['username']) ?>')" class="btn btn-outline-danger btn-sm rounded-2" title="Remove Account">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="text-muted small fst-italic">Active Session</span>
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

            <!-- TAB 2: System Audit Logs -->
            <div class="tab-pane fade" id="audit-view" role="tabpanel" aria-labelledby="audit-tab">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table id="audit-table" class="table table-hover align-middle w-100 small">
                                <thead class="table-light">
                                    <tr>
                                        <th>Timestamp</th>
                                        <th>Account</th>
                                        <th>Action Taken</th>
                                        <th>Description</th>
                                        <th>IP Address</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($auditLogs as $log): ?>
                                        <tr>
                                            <td class="fw-medium text-nowrap"><?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?></td>
                                            <td>
                                                <div class="fw-semibold text-dark"><?= escape($log['fullname'] ?: 'Guest') ?></div>
                                                <small class="text-muted"><?= escape($log['role'] ?: 'Public IP') ?> (<?= escape($log['username'] ?: 'guest') ?>)</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-primary border border-primary-subtle fw-semibold">
                                                    <?= escape($log['action']) ?>
                                                </span>
                                            </td>
                                            <td class="text-wrap" style="max-width: 300px;"><?= escape($log['description']) ?></td>
                                            <td class="text-muted text-nowrap"><i class="bi bi-laptop me-1"></i><?= escape($log['ip_address']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once LAYOUT_PATH . 'footer.php'; ?>

<script>
$(document).ready(function() {
    $('#users-table').DataTable({
        responsive: true,
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: 4 }
        ]
    });

    $('#audit-table').DataTable({
        responsive: true,
        order: [[0, 'desc']] // Latest activity logs first
    });
});

function confirmDelete(id, username) {
    Swal.fire({
        title: 'Delete User Account?',
        text: `Are you sure you want to permanently delete the profile account of ${username}? This cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Delete'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `index.php?route=users/delete&id=${id}`;
        }
    });
}
</script>
