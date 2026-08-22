<?php
/**
 * Security Sanitization and Escaping Helpers
 */

if (!function_exists('escape')) {
    /**
     * Escape HTML output to prevent XSS
     * 
     * @param mixed $value
     * @return string
     */
    function escape($value): string {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('sanitize_input')) {
    /**
     * Sanitize user input (string trimming and filter sanitization)
     * 
     * @param mixed $value
     * @return mixed
     */
    function sanitize_input($value) {
        if (is_array($value)) {
            return sanitize_array($value);
        }
        
        if ($value === null) {
            return null;
        }

        $value = trim((string)$value);
        // Remove null bytes
        $value = str_replace(chr(0), '', $value);
        // General sanitation, strips HTML tags
        $value = strip_tags($value);
        
        return $value;
    }
}

if (!function_exists('sanitize_array')) {
    /**
     * Sanitize all items in an array recursively
     * 
     * @param array $array
     * @return array
     */
    function sanitize_array(array $array): array {
        $sanitized = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = sanitize_array($value);
            } else {
                $sanitized[$key] = sanitize_input($value);
            }
        }
        return $sanitized;
    }
}

if (!function_exists('db_log')) {
    /**
     * Dynamic helper to log user actions in the database logs
     * 
     * @param string $action e.g., 'CREATE_RESIDENT'
     * @param string $description details
     * @param int|null $userId optional overrides
     * @return void
     */
    function db_log(string $action, string $description, ?int $userId = null): void {
        try {
            // Normalize action and description to remove "JS" annotations
            $action = str_replace('_JS', '', $action);
            $description = str_replace(['via JS CRUD', 'via JS:', 'via JS', '  '], ['', ':', '', ' '], $description);
            $description = trim(str_replace(' : ', ': ', $description));

            $db = \App\Config\Database::getConnection();
            $stmt = $db->prepare("
                INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent)
                VALUES (:user_id, :action, :description, :ip_address, :user_agent)
            ");
            
            $uId = $userId ?? $_SESSION['user_id'] ?? null;
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            
            $stmt->execute([
                ':user_id' => $uId,
                ':action' => $action,
                ':description' => $description,
                ':ip_address' => $ip,
                ':user_agent' => $ua
            ]);
        } catch (\Exception $e) {
            // Silently fail to prevent log exceptions from blocking actual logic, but log to error log
            error_log("Failed to insert activity log: " . $e->getMessage());
        }
    }
}
