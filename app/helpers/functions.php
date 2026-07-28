<?php
/**
 * Global Helper Functions
 */

if (!function_exists('url')) {
    /**
     * Generate an absolute URL
     * 
     * @param string $path
     * @return string
     */
    function url(string $path = ''): string {
        return BASE_URL . ltrim($path, '/');
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect to a specific route
     * 
     * @param string $route e.g., 'dashboard' or 'residents/edit?id=5'
     * @return void
     */
    function redirect(string $route): void {
        header("Location: " . url('index.php?route=' . ltrim($route, '/')));
        exit;
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Generate or fetch CSRF token for the session
     * 
     * @return string
     */
    function csrf_token(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Output CSRF input tag
     * 
     * @return string
     */
    function csrf_field(): string {
        return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('verify_csrf_token')) {
    /**
     * Verify the request's CSRF token
     * 
     * @param string|null $token
     * @return bool
     */
    function verify_csrf_token(?string $token): bool {
        return !empty($token) && hash_equals(csrf_token(), $token);
    }
}

if (!function_exists('flash')) {
    /**
     * Set or get a flash message
     * 
     * @param string $key
     * @param string|null $message
     * @return string|null
     */
    function flash(string $key, ?string $message = null): ?string {
        if ($message !== null) {
            $_SESSION['flash'][$key] = $message;
            return null;
        }
        
        if (isset($_SESSION['flash'][$key])) {
            $msg = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $msg;
        }
        
        return null;
    }
}

if (!function_exists('is_active_route')) {
    /**
     * Check if the current route matches the target route to add active styling
     * 
     * @param string $route
     * @return bool
     */
    function is_active_route(string $route): bool {
        $currentRoute = $_GET['route'] ?? 'dashboard';
        // Match exact or prefix
        return $currentRoute === $route || strpos($currentRoute, $route . '/') === 0;
    }
}

if (!function_exists('has_role')) {
    /**
     * Check if the logged-in user has specific role(s)
     * 
     * @param string|array $roles
     * @return bool
     */
    function has_role($roles): bool {
        if (empty($_SESSION['user_role'])) {
            return false;
        }
        $roles = is_array($roles) ? $roles : [$roles];
        return in_array($_SESSION['user_role'], $roles);
    }
}

if (!function_exists('get_logged_in_user')) {
    /**
     * Retrieve logged-in user details
     * 
     * @return array|null
     */
    function get_logged_in_user(): ?array {
        if (empty($_SESSION['user_id'])) {
            return null;
        }
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['user_username'],
            'role' => $_SESSION['user_role'],
            'fullname' => $_SESSION['user_fullname']
        ];
    }
}

if (!function_exists('json_response')) {
    /**
     * Output a standard JSON response
     * 
     * @param bool $success
     * @param string $message
     * @param array $data
     * @param int $code
     * @return void
     */
    function json_response(bool $success, string $message = '', array $data = [], int $code = 200): void {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }
}

if (!function_exists('gender_badge')) {
    /**
     * Render a styled HTML gender badge with icon and custom color (Blue for Male, Pink for Female)
     * 
     * @param string|null $gender
     * @return string
     */
    function gender_badge(?string $gender): string {
        $gender = $gender ?? 'Other';
        if ($gender === 'Male') {
            $class = 'badge-gender-male';
            $icon = 'bi-gender-male';
        } elseif ($gender === 'Female') {
            $class = 'badge-gender-female';
            $icon = 'bi-gender-female';
        } else {
            $class = 'badge-gender-other';
            $icon = 'bi-gender-ambiguous';
        }

        return sprintf(
            '<span class="badge %s"><i class="bi %s me-1"></i>%s</span>',
            $class,
            $icon,
            htmlspecialchars($gender, ENT_QUOTES, 'UTF-8')
        );
    }
}
