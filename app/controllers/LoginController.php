<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Middleware\AuthMiddleware;

class LoginController {
    /**
     * Display the login page
     */
    public function index(): void {
        // Enforce that already logged-in users cannot see this page
        AuthMiddleware::guestOnly();
        
        $error = flash('error');
        $success = flash('success');
        
        require_once VIEW_PATH . 'auth/login.php';
    }

    /**
     * Process authentication submission
     */
    public function authenticate(): void {
        AuthMiddleware::guestOnly();

        // 1. Verify CSRF Token
        $csrfToken = $_POST['csrf_token'] ?? null;
        if (!verify_csrf_token($csrfToken)) {
            flash('error', 'CSRF verification failed. Please try again.');
            redirect('auth/login');
        }

        // 2. Validate Inputs
        $username = sanitize_input($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $rememberMe = isset($_POST['remember_me']);

        if (empty($username) || empty($password)) {
            flash('error', 'Please enter both username and password.');
            redirect('auth/login');
        }

        // 3. Attempt Login
        $result = AuthService::login($username, $password, $rememberMe);

        if ($result['success']) {
            redirect('dashboard');
        } else {
            flash('error', $result['message']);
            redirect('auth/login');
        }
    }

    /**
     * Terminate the user session and redirect to login
     */
    public function logout(): void {
        AuthService::logout();
        flash('success', 'You have been logged out successfully.');
        redirect('auth/login');
    }
}
