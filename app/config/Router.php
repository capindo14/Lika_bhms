<?php

namespace App\Config;

class Router {
    private $routes = [];

    /**
     * Register a route
     * 
     * @param string $route
     * @param string $handler e.g., 'DashboardController@index'
     * @return void
     */
    public function add(string $route, string $handler): void {
        $this->routes[rtrim($route, '/')] = $handler;
    }

    /**
     * Dispatch the current request
     * 
     * @param string $route
     * @return void
     */
    public function dispatch(string $route): void {
        $route = rtrim($route, '/');
        if ($route === '') {
            $route = 'dashboard';
        }

        // Exact match
        if (isset($this->routes[$route])) {
            $this->execute($this->routes[$route]);
            return;
        }

        // 404 Route Not Found
        $this->handle404("Route '{$route}' not defined in router.");
    }

    /**
     * Execute the controller action
     * 
     * @param string $handler
     * @return void
     */
    private function execute(string $handler): void {
        list($controllerName, $action) = explode('@', $handler);
        $controllerClass = "App\\Controllers\\" . $controllerName;

        if (!class_exists($controllerClass)) {
            $this->handle404("Controller class '{$controllerClass}' does not exist.");
        }

        $controllerInstance = new $controllerClass();

        if (!method_exists($controllerInstance, $action)) {
            $this->handle404("Action method '{$action}' not found in controller '{$controllerClass}'.");
        }

        try {
            $controllerInstance->$action();
        } catch (\Exception $e) {
            error_log("Unhandled Application Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            http_response_code(500);
            $this->renderErrorPage($e);
        }
    }

    /**
     * Render the 404 error page
     */
    public function handle404(string $debugMessage = ''): void {
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

    /**
     * Render the 500 system error page
     */
    private function renderErrorPage(\Exception $e): void {
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
}