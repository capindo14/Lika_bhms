<?php
$currentUser = get_logged_in_user();
$userRole = $currentUser['role'] ?? 'Staff';
?>
<aside class="health-sidebar sidebar-wrapper" id="sidebar">
    <div class="health-sidebar-header sidebar-header">
        <a href="<?= url('index.php?route=dashboard') ?>" class="d-flex align-items-center text-decoration-none gap-2">
            <span class="fs-4 text-primary"><i class="bi bi-heart-pulse-fill"></i></span>
            <span class="fw-bold tracking-wide fs-5">Lika BHMS</span>
        </a>
    </div>
    
    <div class="health-sidebar-menu py-3">
        <!-- Dashboard -->
        <a class="health-menu-item nav-link <?= is_active_route('dashboard') ? 'active' : '' ?>" href="<?= url('index.php?route=dashboard') ?>">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>

        <!-- Residents Module -->
        <a class="health-menu-item nav-link <?= is_active_route('residents') ? 'active' : '' ?>" href="<?= url('index.php?route=residents') ?>">
            <i class="bi bi-people"></i>
            <span>Residents</span>
        </a>

        <!-- Family Profiles Module -->
        <a class="health-menu-item nav-link <?= is_active_route('family') ? 'active' : '' ?>" href="<?= url('index.php?route=family') ?>">
            <i class="bi bi-house-heart"></i>
            <span>Family Profiles</span>
        </a>

        <!-- Consultations Module -->
        <a class="health-menu-item nav-link <?= is_active_route('consultation') ? 'active' : '' ?>" href="<?= url('index.php?route=consultation') ?>">
            <i class="bi bi-clipboard2-pulse"></i>
            <span>Consultations</span>
        </a>

        <!-- Immunizations Module -->
        <a class="health-menu-item nav-link <?= is_active_route('immunization') ? 'active' : '' ?>" href="<?= url('index.php?route=immunization') ?>">
            <i class="bi bi-shield-plus"></i>
            <span>Immunizations</span>
        </a>

        <!-- Medicine Distribution Module -->
        <a class="health-menu-item nav-link <?= is_active_route('medicine') ? 'active' : '' ?>" href="<?= url('index.php?route=medicine') ?>">
            <i class="bi bi-capsule"></i>
            <span>Medicines / Stocks</span>
        </a>

        <!-- Reports Module -->
        <a class="health-menu-item nav-link <?= is_active_route('reports') ? 'active' : '' ?>" href="<?= url('index.php?route=reports') ?>">
            <i class="bi bi-file-earmark-bar-graph"></i>
            <span>Reports</span>
        </a>

        <!-- User Management (Admins & Health Workers only) -->
        <?php if ($userRole === 'Admin'): ?>
        <a class="health-menu-item nav-link <?= is_active_route('users') && !is_active_route('users/settings') ? 'active' : '' ?>" href="<?= url('index.php?route=users') ?>">
            <i class="bi bi-person-gear"></i>
            <span>Users</span>
        </a>
        <?php endif; ?>

        <!-- Settings -->
        <a class="health-menu-item nav-link <?= is_active_route('users/settings') ? 'active' : '' ?>" href="<?= url('index.php?route=users/settings') ?>">
            <i class="bi bi-gear"></i>
            <span>Settings</span>
        </a>
    </div>

    <div class="health-sidebar-footer">
        <!-- Sign Out -->
        <a class="health-menu-item nav-link text-danger border border-danger border-opacity-10 bg-danger bg-opacity-10 justify-content-center text-center" href="<?= url('logout.php') ?>" onclick="return confirm('Are you sure you want to sign out?')">
            <i class="bi bi-box-arrow-left text-danger"></i>
            <span class="text-danger fw-semibold">Sign Out</span>
        </a>
    </div>
</aside>
