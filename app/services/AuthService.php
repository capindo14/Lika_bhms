<?php

namespace App\Services;

use App\Config\Database;
use PDO;
use Exception;

class AuthService {
    /**
     * Authenticate user credentials
     * 
     * @param string $username
     * @param string $password
     * @param bool $rememberMe
     * @return array Array with success status and message
     */
    public static function login(string $username, string $password, bool $rememberMe = false): array {
        try {
            $db = Database::getConnection();
            
            // Check if user exists
            $stmt = $db->prepare("
                SELECT * FROM users 
                WHERE username = :username 
                AND deleted_at IS NULL 
                LIMIT 1
            ");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();
            
            if (!$user) {
                db_log('FAILED_LOGIN', "Failed login attempt for username: " . sanitize_input($username), null);
                return ['success' => false, 'message' => 'Invalid username or password.'];
            }
            
            // Check if user is active
            if ($user['status'] !== 'Active') {
                db_log('FAILED_LOGIN', "Attempted login to deactivated account: " . sanitize_input($username), null);
                return ['success' => false, 'message' => 'Your account has been deactivated. Please contact an administrator.'];
            }
            
            // Verify Password
            if (!password_verify($password, $user['password'])) {
                db_log('FAILED_LOGIN', "Failed login password verification for username: " . sanitize_input($username), null);
                return ['success' => false, 'message' => 'Invalid username or password.'];
            }
            
            // Login Success! Set session details
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['user_username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_fullname'] = $user['fullname'];
            $_SESSION['last_activity'] = time();
            
            // Log successful activity
            db_log('LOGIN', 'Logged into the system successfully.', $user['id']);
            
            // Handle Remember Me (optional)
            if ($rememberMe) {
                // In a production app, generate a secure token, store in a cookie and database.
                // For simplicity and compatibility on XAMPP localhost, we can set session lifetime cookie
                $cookie_lifetime = 86400 * 30; // 30 days
                setcookie(session_name(), session_id(), time() + $cookie_lifetime, "/", "", false, true);
            }
            
            return ['success' => true, 'message' => 'Login successful.'];
            
        } catch (Exception $e) {
            error_log("AuthService login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during authentication. Please try again.'];
        }
    }

    /**
     * Terminate the active session and log activity
     * 
     * @return void
     */
    public static function logout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['user_id'])) {
            db_log('LOGOUT', 'Logged out of the system.', $_SESSION['user_id']);
        }
        
        \App\Middleware\AuthMiddleware::clearSession();
    }
}
