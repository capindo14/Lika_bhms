<?php
/**
 * Barangay Health Monitoring System - Front Controller & Router
 */

// Load Global Configuration and Autoloader
require_once __DIR__ . '/app/config/config.php';

// Retrieve route from query parameters (default to dashboard)
$route = $_GET['route'] ?? 'dashboard';
$parts = explode('/', $route);
$controllerKey = strtolower($parts[0] ?? 'dashboard');
$actionKey = strtolower($parts[1] ?? 'index');

// Map route components to controller classes
$routeMap = [
    'auth' => 'App\\Controllers\\LoginController',
    'dashboard' => 'App\\Controllers\\DashboardController',
    'residents' => 'App\\Controllers\\ResidentController',
    'family' => 'App\\Controllers\\FamilyController',
    'consultation' => 'App\\Controllers\\ConsultationController',
    'immunization' => 'App\\Controllers\\ImmunizationController',
    'medicine' => 'App\\Controllers\\MedicineController',
    'reports' => 'App\\Controllers\\ReportController',
    'users' => 'App\\Controllers\\UserController'
];

// Check if controller key exists in mapping
if (!array_key_exists($controllerKey, $routeMap)) {
    handle404("Route key '{$controllerKey}' not found.");
}

$controllerClass = $routeMap[$controllerKey];

// Check if class exists (autoloaded)
if (!class_exists($controllerClass)) {
    handle404("Controller class '{$controllerClass}' does not exist.");
}

$controllerInstance = new $controllerClass();

// Format action key (camelCase if contains dashes/underscores, or standard mapping)
// Standard actions are index, create, store, edit, update, view, archive, restore, etc.
$actionMethod = $actionKey;
if ($controllerKey === 'auth' && $actionKey === 'login') {
    $actionMethod = 'index';
}

// Check if action method exists on controller
if (!method_exists($controllerInstance, $actionMethod)) {
    handle404("Action method '{$actionMethod}' not found in controller '{$controllerClass}'.");
}

try {
    // Dispatch the request to the controller's action
    $controllerInstance->$actionMethod();
} catch (\Exception $e) {
    // System Exception Page
    error_log("Unhandled Application Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Application Error</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    </head>
    <body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <div class="card p-4 shadow border-0 text-center" style="max-width: 500px;">
            <div class="text-danger mb-3"><i class="bi bi-x-circle-fill" style="font-size: 3rem;"></i></div>
            <h4 class="fw-bold text-danger">System Error Happened</h4>
            <p class="text-muted small">An unexpected error occurred while executing your request. The technical details have been logged.</p>
            <div class="alert alert-warning text-start small overflow-auto" style="max-height: 200px;">
                <strong>Error:</strong> <?= htmlspecialchars($e->getMessage()) ?>
            </div>
            <a href="index.php?route=dashboard" class="btn btn-primary mt-2">Go to Dashboard</a>
        </div>
    </body>
    </html>
    <?php
}

/**
 * Handle 404 Route Not Found page
 */
function handle404(string $debugMessage = ''): void {
    error_log("404 Not Found: " . $debugMessage);
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>404 - Page Not Found</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    </head>
    <body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <div class="card p-4 shadow border-0 text-center" style="max-width: 450px;">
            <div class="text-warning mb-3"><i class="bi bi-exclamation-circle-fill" style="font-size: 3rem;"></i></div>
            <h4 class="fw-bold">Page Not Found (404)</h4>
            <p class="text-muted small">The requested page or health record could not be found. It may have been relocated or deleted.</p>
            <a href="index.php?route=dashboard" class="btn btn-primary mt-2">Back to Safety</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}
