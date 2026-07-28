<?php

namespace App\Middleware;

class AuthMiddleware {
    /**
     * Enforce authentication and role permissions
     * 
     * @param array $allowedRoles If empty, any authenticated user is allowed.
     * @return void
     */
    public static function handle(array $allowedRoles = []): void {
        // 1. Check Session Status
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 2. Check if User is Logged In
        if (!isset($_SESSION['user_id'])) {
            flash('error', 'You must log in to access this page.');
            // Clear credentials to be safe
            self::clearSession();
            redirect('auth/login');
        }

        // 3. Check Session Timeout
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
            db_log('LOGOUT', 'Session timed out due to inactivity.', $_SESSION['user_id']);
            self::clearSession();
            flash('error', 'Your session has expired due to inactivity. Please log in again.');
            redirect('auth/login');
        }
        
        // Update last activity timestamp
        $_SESSION['last_activity'] = time();

        // 4. Verify Account is Active
        try {
            $db = \App\Config\Database::getConnection();
            $stmt = $db->prepare("SELECT status FROM users WHERE id = :id AND deleted_at IS NULL");
            $stmt->execute([':id' => $_SESSION['user_id']]);
            $user = $stmt->fetch();

            if (!$user || $user['status'] !== 'Active') {
                db_log('LOGOUT', 'Session terminated because user account was deactivated or deleted.', $_SESSION['user_id']);
                self::clearSession();
                flash('error', 'Your account has been deactivated or removed. Contact your administrator.');
                redirect('auth/login');
            }
        } catch (\Exception $e) {
            error_log("AuthMiddleware DB error: " . $e->getMessage());
            // Fail silently on db connection issues but keep the session alive
        }

        // 5. Check Roles (RBAC)
        if (!empty($allowedRoles)) {
            if (!in_array($_SESSION['user_role'], $allowedRoles)) {
                flash('error', 'Access denied. You do not have permission to view that page.');
                redirect('dashboard');
            }
        }
    }

    /**
     * Enforce guest-only access (e.g., login page)
     * 
     * @return void
     */
    public static function guestOnly(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['user_id'])) {
            // Already logged in, redirect to dashboard
            redirect('dashboard');
        }
    }

    /**
     * Clear all session data safely
     * 
     * @return void
     */
    public static function clearSession(): void {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 42000,
                $params["path"], 
                $params["domain"],
                $params["secure"], 
                $params["httponly"]
            );
        }
        session_destroy();
    }
}
