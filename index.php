<?php
/**
 * Root Redirector - Redirects traffic to the secure public entry point
 */

$queryString = $_SERVER['QUERY_STRING'] ?? '';
header('Location: public/index.php' . ($queryString ? '?' . $queryString : ''));
exit;
