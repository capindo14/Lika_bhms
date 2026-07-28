<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\User;
use App\Services\ValidationService;
use App\Config\Database;
use Exception;

class UserController {
    /**
     * Display a listing of system users and system audit logs
     */
    public function index(): void {
        // Enforce Admin role restriction
        AuthMiddleware::handle(['Admin']);

        $users = User::getAll();

        // Fetch System Audit Logs
        $db = Database::getConnection();
        $stmtLogs = $db->query("
            SELECT al.*, u.username, u.fullname, u.role
            FROM activity_logs al
            LEFT JOIN users u ON al.user_id = u.id
            ORDER BY al.created_at DESC
            LIMIT 500
        ");
        $auditLogs = $stmtLogs->fetchAll();

        $pageTitle = 'User Accounts & System Logs';
        require_once VIEW_PATH . 'users/index.php';
    }

    /**
     * Render registration page
     */
    public function create(): void {
        AuthMiddleware::handle(['Admin']);
        $pageTitle = 'Create System Account';
        require_once VIEW_PATH . 'users/create.php';
    }

    /**
     * Store new system user
     */
    public function store(): void {
        AuthMiddleware::handle(['Admin']);

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('error', 'CSRF verification failed. Please try again.');
            redirect('users/create');
        }

        $validator = new ValidationService();
        $data = sanitize_array($_POST);

        $rules = [
            'username' => 'required|min:4',
            'password' => 'required|min:6',
            'role' => 'required',
            'fullname' => 'required|min:3'
        ];

        if (!$validator->validate($data, $rules)) {
            flash('error', $validator->getFirstError());
            redirect('users/create');
        }

        try {
            User::create($data);
            
            // Log creation
            db_log('CREATE_USER', "Created system account: " . $data['username'] . " (" . $data['role'] . ")");
            
            flash('success', 'User account created successfully.');
            redirect('users');
        } catch (Exception $e) {
            flash('error', $e->getMessage());
            redirect('users/create');
        }
    }

    /**
     * Display edit form
     */
    public function edit(): void {
        AuthMiddleware::handle(['Admin']);

        $id = (int)($_GET['id'] ?? 0);
        $user = User::getById($id);

        if (!$user) {
            flash('error', 'User account not found.');
            redirect('users');
        }

        $pageTitle = 'Edit System Account';
        require_once VIEW_PATH . 'users/edit.php';
    }

    /**
     * Update user details
     */
    public function update(): void {
        AuthMiddleware::handle(['Admin']);

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            flash('error', 'Invalid user record.');
            redirect('users');
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('error', 'CSRF verification failed. Please try again.');
            redirect("users/edit&id={$id}");
        }

        $validator = new ValidationService();
        $data = sanitize_array($_POST);

        $rules = [
            'username' => 'required|min:4',
            'role' => 'required',
            'fullname' => 'required|min:3',
            'status' => 'required'
        ];

        if (!$validator->validate($data, $rules)) {
            flash('error', $validator->getFirstError());
            redirect("users/edit&id={$id}");
        }

        try {
            User::update($id, $data);
            
            // Log update
            db_log('UPDATE_USER', "Updated system user properties for ID: " . $id);
            
            flash('success', 'User account updated successfully.');
            redirect('users');
        } catch (Exception $e) {
            flash('error', $e->getMessage());
            redirect("users/edit&id={$id}");
        }
    }

    /**
     * Toggle status (Activate/Deactivate user)
     */
    public function toggle_status(): void {
        AuthMiddleware::handle(['Admin']);

        $id = (int)($_GET['id'] ?? 0);
        $user = User::getById($id);

        if (!$user) {
            flash('error', 'User account not found.');
            redirect('users');
        }

        if ($user['id'] === $_SESSION['user_id']) {
            flash('error', 'You cannot deactivate your own administrative account.');
            redirect('users');
        }

        try {
            $newStatus = ($user['status'] === 'Active') ? 'Inactive' : 'Active';
            
            $db = Database::getConnection();
            $stmt = $db->prepare("UPDATE users SET status = :status WHERE id = :id");
            $stmt->execute([':status' => $newStatus, ':id' => $id]);
            
            // Log action
            db_log('STATUS_TOGGLE', "Changed status of user account: " . $user['username'] . " to " . $newStatus);
            
            flash('success', "User account status updated to {$newStatus}.");
        } catch (Exception $e) {
            flash('error', 'Failed to toggle account status: ' . $e->getMessage());
        }

        redirect('users');
    }

    /**
     * Delete user account (soft delete)
     */
    public function delete(): void {
        AuthMiddleware::handle(['Admin']);

        $id = (int)($_GET['id'] ?? 0);
        $user = User::getById($id);

        if (!$user) {
            flash('error', 'User account not found.');
            redirect('users');
        }

        if ($user['id'] === $_SESSION['user_id']) {
            flash('error', 'You cannot delete your own administrative account.');
            redirect('users');
        }

        if (User::delete($id)) {
            // Log action
            db_log('DELETE_USER', "Deleted user account: " . $user['username'] . " (ID: " . $id . ")");
            
            flash('success', 'User account deleted successfully.');
        } else {
            flash('error', 'Failed to delete user account.');
        }

        redirect('users');
    }

    /**
     * Display personal user settings dashboard
     */
    public function settings(): void {
        // Accessible by all roles
        AuthMiddleware::handle();

        $user = User::getById($_SESSION['user_id']);
        
        $pageTitle = 'Account Settings';
        require_once VIEW_PATH . 'users/settings.php';
    }

    /**
     * Update user personal profile name or password
     */
    public function update_settings(): void {
        AuthMiddleware::handle();

        $id = $_SESSION['user_id'];
        
        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('error', 'CSRF verification failed. Please try again.');
            redirect('users/settings');
        }

        $validator = new ValidationService();
        $data = sanitize_array($_POST);

        $rules = [
            'fullname' => 'required|min:3'
        ];

        // If password fields are filled, validate rules
        $passwordChanged = false;
        if (!empty($data['new_password'])) {
            if ($_SESSION['user_role'] !== 'Admin') {
                flash('error', 'Only system administrators are authorized to update account passwords.');
                redirect('users/settings');
            }

            $rules['new_password'] = 'required|min:6';
            $passwordChanged = true;
            
            if ($data['new_password'] !== $data['confirm_password']) {
                flash('error', 'Confirm password does not match.');
                redirect('users/settings');
            }
        }

        if (!$validator->validate($data, $rules)) {
            flash('error', $validator->getFirstError());
            redirect('users/settings');
        }

        try {
            $db = Database::getConnection();

            if ($passwordChanged) {
                // Verify old password
                $user = User::getById($id);
                if (!password_verify($data['current_password'], $user['password'])) {
                    flash('error', 'Invalid current password entered.');
                    redirect('users/settings');
                }

                // Update details with new password
                $stmt = $db->prepare("
                    UPDATE users 
                    SET fullname = :fullname, password = :password 
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':fullname' => $data['fullname'],
                    ':password' => password_hash($data['new_password'], PASSWORD_DEFAULT),
                    ':id' => $id
                ]);
                
                db_log('PASSWORD_CHANGE', "Updated personal account password.");
            } else {
                // Update fullname only
                $stmt = $db->prepare("
                    UPDATE users 
                    SET fullname = :fullname 
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':fullname' => $data['fullname'],
                    ':id' => $id
                ]);
            }

            // Sync session
            $_SESSION['user_fullname'] = $data['fullname'];

            db_log('SETTINGS_UPDATE', "Updated personal settings.");
            flash('success', 'Account profile settings updated successfully.');
            redirect('users/settings');

        } catch (Exception $e) {
            flash('error', $e->getMessage());
            redirect('users/settings');
        }
    }
}
