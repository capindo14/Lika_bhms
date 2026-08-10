<?php
/**
 * Application Global Configuration
 */

// Set Default Timezone
date_default_timezone_set('Asia/Manila');

// Start Session if not already started with security parameters
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    
    // In production, we'd enable secure cookies, but in XAMPP localhost we disable it to prevent login issues
    // ini_set('session.cookie_secure', 1); 

    session_start();
}

// App Settings
define('APP_NAME', 'Barangay Health Monitoring System');
define('APP_VERSION', '1.0.0');

// Load Environment Variables
require_once __DIR__ . '/Dotenv.php';
\App\Config\Dotenv::load(__DIR__ . '/../../.env');

// Database Connection Parameters
define('DB_DRIVER', 'mysql'); // Options: 'mysql' or 'sqlite'
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_NAME', getenv('DB_NAME') ?: 'barangay_health');

// Base URL Auto-Detection
if (php_sapi_name() === 'cli') {
    define('BASE_URL', 'http://localhost/barangay-health-system/');
} else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $baseDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $baseDir = rtrim($baseDir, '/') . '/';
    define('BASE_URL', $protocol . $domainName . $baseDir);
}


// Layout Constants
define('LAYOUT_PATH', __DIR__ . '/../../views/layouts/');
define('VIEW_PATH', __DIR__ . '/../../views/');

// Session Security Configuration
define('SESSION_TIMEOUT', 1800); // 30 Minutes

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1); // Set to 0 in production
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/error.log');

// Class Autoloader
spl_autoload_register(function ($class) {
    // Convert namespace backslash to forward slash
    $classPath = str_replace('\\', '/', $class);
    $parts = explode('/', $classPath);
    
    // Check if the namespace starts with App
    if ($parts[0] === 'App') {
        $parts[0] = 'app';
        // Convert all directory names to lowercase, keeping class name casing
        for ($i = 1; $i < count($parts) - 1; $i++) {
            $parts[$i] = strtolower($parts[$i]);
        }
        
        $file = dirname(__DIR__, 2) . '/' . implode('/', $parts) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

// Load Global Helpers
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/sanitizer.php';


