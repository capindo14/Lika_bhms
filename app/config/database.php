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
                if (defined('DB_DRIVER') && DB_DRIVER === 'sqlite') {
                    $dsn = "sqlite:" . DB_SQLITE_PATH;
                    $options = [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ];
                    self::$instance = new CompatiblePDO($dsn, null, null, $options);
                    // Enable SQLite foreign keys checks
                    self::$instance->exec("PRAGMA foreign_keys = ON;");

                    // Register custom SQLite functions to emulate MySQL behaviour
                    self::$instance->sqliteCreateFunction('CURDATE', function() {
                        return date('Y-m-d');
                    });
                    
                    self::$instance->sqliteCreateFunction('CONCAT', function(...$args) {
                        return implode('', $args);
                    });
                    
                    self::$instance->sqliteCreateFunction('MONTH', function($date) {
                        if (empty($date)) return null;
                        $time = strtotime($date);
                        return $time ? (int)date('m', $time) : null;
                    });
                    
                    self::$instance->sqliteCreateFunction('YEAR', function($date) {
                        if (empty($date)) return null;
                        $time = strtotime($date);
                        return $time ? (int)date('Y', $time) : null;
                    });
                    
                    self::$instance->sqliteCreateFunction('TIMESTAMPDIFF', function($unit, $date1, $date2) {
                        if (empty($date1) || empty($date2)) {
                            return null;
                        }
                        $d1 = new \DateTime($date1);
                        $d2 = new \DateTime($date2);
                        $diff = $d1->diff($d2);
                        
                        if (strtoupper($unit) === 'YEAR') {
                            $years = $diff->y;
                            if ($d1 > $d2) {
                                return -$years;
                            }
                            return $years;
                        }
                        
                        if (strtoupper($unit) === 'MONTH') {
                            $months = ($diff->y * 12) + $diff->m;
                            if ($d1 > $d2) {
                                return -$months;
                            }
                            return $months;
                        }
                        
                        if (strtoupper($unit) === 'DAY') {
                            $days = $diff->days;
                            if ($d1 > $d2) {
                                return -$days;
                            }
                            return $days;
                        }
                        
                        return null;
                    });
                } else {
                    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                    $options = [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                    ];
                    self::$instance = new CompatiblePDO($dsn, DB_USER, DB_PASS, $options);
                }
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

/**
 * A custom PDO subclass that intercepts and rewrites queries for SQLite compatibility.
 */
class CompatiblePDO extends PDO {
    #[\ReturnTypeWillChange]
    public function prepare($query, $options = []) {
        $query = $this->rewriteQuery($query);
        return parent::prepare($query, $options);
    }
    
    #[\ReturnTypeWillChange]
    public function query($query, $fetchMode = null, ...$fetchModeArgs) {
        $query = $this->rewriteQuery($query);
        if ($fetchMode === null) {
            return parent::query($query);
        }
        return parent::query($query, $fetchMode, ...$fetchModeArgs);
    }
    
    #[\ReturnTypeWillChange]
    public function exec($statement) {
        $statement = $this->rewriteQuery($statement);
        return parent::exec($statement);
    }
    
    private function rewriteQuery($sql) {
        if (is_string($sql) && defined('DB_DRIVER') && DB_DRIVER === 'sqlite') {
            // Rewrite TIMESTAMPDIFF(YEAR, ... to TIMESTAMPDIFF('YEAR', ...
            $sql = preg_replace('/\bTIMESTAMPDIFF\s*\(\s*(YEAR|MONTH|DAY)\s*,/i', "TIMESTAMPDIFF('$1',", $sql);
        }
        return $sql;
    }
}
