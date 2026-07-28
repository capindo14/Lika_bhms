<?php
$currentUser = get_logged_in_user();
?>
<header class="health-topbar navbar navbar-expand-lg navbar-top border-bottom">
    <div class="container-fluid d-flex align-items-center justify-content-between px-0">
        
        <!-- Left: Sidebar Toggle & System Date -->
        <div class="health-topbar-left">
            <button class="btn btn-link text-dark p-0 me-3 d-lg-none" id="sidebar-toggle-btn" aria-label="Toggle Navigation">
                <i class="bi bi-list fs-3"></i>
            </button>
            <div class="health-sys-datetime d-none d-md-flex">
                <i class="bi bi-calendar3 me-1"></i>
                <span id="nav-system-date"><?= date('F d, Y') ?></span>
                <span class="mx-2">|</span>
                <i class="bi bi-clock me-1"></i>
                <span id="nav-system-time">00:00:00 AM</span>
            </div>
        </div>

        <!-- Right: Actions & User Details -->
        <div class="health-topbar-right">
            
            <!-- Dark Mode Toggle Button -->
            <button class="btn btn-outline-secondary border-0 btn-sm rounded-circle px-2" id="theme-toggle-btn" title="Toggle Theme">
                <i class="bi bi-moon-fill" id="theme-icon"></i>
            </button>
            
            <!-- User Profile Badge -->
            <div class="health-user-profile">
                <div class="health-avatar">
                    <?php
                        $initials = '';
                        if ($currentUser) {
                            $words = explode(' ', $currentUser['fullname']);
                            $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
                        }
                        echo escape($initials ?: 'BH');
                    ?>
                </div>
                <div class="d-none d-sm-block text-start">
                    <div class="health-user-name text-truncate" style="max-width: 140px;"><?= escape($currentUser['fullname'] ?? 'Barangay Health') ?></div>
                    <div class="health-user-role"><?= escape($currentUser['role'] ?? 'Staff') ?></div>
                </div>
            </div>
        </div>

    </div>
</header>
