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
        $pageTitle = 'User Accounts & System Logs';
        require_once VIEW_PATH . 'users/index.php';
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


    /**
     * Get list of users and system logs in JSON
     */
    public function apiList(): void {
        AuthMiddleware::handle(['Admin']);
        header('Content-Type: application/json');

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

        echo json_encode([
            "users" => $users,
            "logs" => $auditLogs
        ]);
        exit;
    }

    /**
     * Get details of a single user in JSON
     */
    public function apiDetail(): void {
        AuthMiddleware::handle(['Admin']);
        header('Content-Type: application/json');

        $id = (int)($_GET['id'] ?? 0);
        $user = User::getById($id);

        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'User account not found']);
            exit;
        }

        // Hide password hash for security
        unset($user['password']);

        echo json_encode($user);
        exit;
    }

    /**
     * Store new system user (JSON POST)
     */
    public function apiStore(): void {
        AuthMiddleware::handle(['Admin']);
        header('Content-Type: application/json');

        try {
            $data = read_json_input();
        } catch (InvalidArgumentException $e) {
            json_response(false, $e->getMessage(), [], 400);
        }

        $username = trim($data['username'] ?? '');
        $password = trim($data['password'] ?? '');
        $role = trim($data['role'] ?? '');
        $fullname = trim($data['fullname'] ?? '');
        $status = trim($data['status'] ?? 'Active');

        if ($username === '' || $password === '' || $role === '' || $fullname === '') {
            echo json_encode([
                "status" => "error",
                "message" => "Please fill in all required fields."
            ]);
            exit;
        }

        try {
            User::create([
                'username' => $username,
                'password' => $password,
                'role' => $role,
                'fullname' => $fullname,
                'status' => $status
            ]);
            
            db_log('CREATE_USER_JS', "Created system account: {$username} ({$role}) via JS CRUD");

            echo json_encode([
                "status" => "success",
                "message" => "User account {$username} ({$role}) created successfully."
            ]);
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Update system user details (JSON POST)
     */
    public function apiUpdate(): void {
        AuthMiddleware::handle(['Admin']);
        header('Content-Type: application/json');

        try {
            $data = read_json_input();
        } catch (InvalidArgumentException $e) {
            json_response(false, $e->getMessage(), [], 400);
        }

        $id = isset($data['id']) ? (int)$data['id'] : 0;
        $username = trim($data['username'] ?? '');
        $role = trim($data['role'] ?? '');
        $fullname = trim($data['fullname'] ?? '');
        $status = trim($data['status'] ?? 'Active');
        $password = trim($data['password'] ?? ''); // Optional password reset

        if (!$id || $username === '' || $role === '' || $fullname === '') {
            echo json_encode([
                "status" => "error",
                "message" => "Please fill in all required fields."
            ]);
            exit;
        }

        try {
            User::update($id, [
                'username' => $username,
                'password' => $password,
                'role' => $role,
                'fullname' => $fullname,
                'status' => $status
            ]);
            
            db_log('UPDATE_USER_JS', "Updated user account ID: {$id} via JS CRUD");

            echo json_encode([
                "status" => "success",
                "message" => "User account {$username} updated successfully."
            ]);
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Delete system user (JSON POST)
     */
    public function apiDelete(): void {
        AuthMiddleware::handle(['Admin']);
        header('Content-Type: application/json');

        try {
            $data = read_json_input();
        } catch (InvalidArgumentException $e) {
            json_response(false, $e->getMessage(), [], 400);
        }

        $id = isset($data['id']) ? (int)$data['id'] : 0;

        if (!$id) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid user ID."
            ]);
            exit;
        }

        $user = User::getById($id);
        if (!$user) {
            echo json_encode([
                "status" => "error",
                "message" => "User not found."
            ]);
            exit;
        }

        if ($user['id'] === $_SESSION['user_id']) {
            echo json_encode([
                "status" => "error",
                "message" => "You cannot delete your own administrative account."
            ]);
            exit;
        }

        if (User::delete($id)) {
            db_log('DELETE_USER_JS', "Deleted user account: " . $user['username'] . " (ID: " . $id . ") via JS CRUD");
            $summary = $user['username'] . ' (' . ($user['fullname'] ?? '') . ')';
            echo json_encode([
                "status" => "success",
                "message" => "User account {$summary} deleted successfully."
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Failed to delete user account."
            ]);
        }
        exit;
    }
}
