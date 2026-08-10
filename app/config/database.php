<?php

namespace App\Config;

use PDO;
use PDOException;
use Exception;

class Database {
    private static $instance = null;

    /**
     * Get Database Connection Instance (Singleton)
     * 
     * @return PDO
     * @throws Exception
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            try {
                // MySQL Connection String
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ];
                
                // Instantiate standard PDO
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
                
            } catch (PDOException $e) {
                // Log the exception message privately and throw a clean error
                error_log("Database Connection Error: " . $e->getMessage());
                throw new Exception("Database connection failed. Please contact your system administrator.");
            }
        }
        return self::$instance;
    }

    /**
     * Prevent cloning or instantiation
     */
    private function __construct() {}
    private function __clone() {}
}