<?php
/**
 * Logout Entrypoint
 */

require_once __DIR__ . '/../app/config/config.php';

// Call the AuthService logout method which clears the session and logs the activity
\App\Services\AuthService::logout();

// Set success message
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
flash('success', 'You have been logged out successfully.');

// Redirect to login page
header("Location: index.php?route=auth/login");
exit;
