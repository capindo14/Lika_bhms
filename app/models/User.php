<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use Exception;

class User {
    /**
     * Fetch all active users
     * 
     * @return array
     */
    public static function getAll(): array {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM users WHERE deleted_at IS NULL ORDER BY role, fullname");
        return $stmt->fetchAll();
    }

    /**
     * Find a user by ID
     * 
     * @param int $id
     * @return array|null
     */
    public static function getById(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Find user by username
     * 
     * @param string $username
     * @return array|null
     */
    public static function getByUsername(string $username): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = :username AND deleted_at IS NULL");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Create a new user
     * 
     * @param array $data
     * @return bool
     */
    public static function create(array $data): bool {
        $db = Database::getConnection();
        
        // Check for duplicates
        if (self::getByUsername($data['username'])) {
            throw new Exception("Username already exists.");
        }

        $stmt = $db->prepare("
            INSERT INTO users (username, password, role, fullname, status)
            VALUES (:username, :password, :role, :fullname, :status)
        ");

        return $stmt->execute([
            ':username' => $data['username'],
            ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
            ':role' => $data['role'],
            ':fullname' => $data['fullname'],
            ':status' => $data['status'] ?? 'Active'
        ]);
    }

    /**
     * Update an existing user
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public static function update(int $id, array $data): bool {
        $db = Database::getConnection();

        // Check if username was changed and if it conflicts
        $existing = self::getById($id);
        if (!$existing) {
            throw new Exception("User not found.");
        }

        if ($data['username'] !== $existing['username']) {
            if (self::getByUsername($data['username'])) {
                throw new Exception("Username already taken.");
            }
        }

        // If password is not empty, hash and update it
        if (!empty($data['password'])) {
            $sql = "
                UPDATE users 
                SET username = :username, password = :password, role = :role, fullname = :fullname, status = :status
                WHERE id = :id
            ";
            $params = [
                ':id' => $id,
                ':username' => $data['username'],
                ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
                ':role' => $data['role'],
                ':fullname' => $data['fullname'],
                ':status' => $data['status']
            ];
        } else {
            $sql = "
                UPDATE users 
                SET username = :username, role = :role, fullname = :fullname, status = :status
                WHERE id = :id
            ";
            $params = [
                ':id' => $id,
                ':username' => $data['username'],
                ':role' => $data['role'],
                ':fullname' => $data['fullname'],
                ':status' => $data['status']
            ];
        }

        return $stmt = $db->prepare($sql)->execute($params);
    }

    /**
     * Soft delete a user
     * 
     * @param int $id
     * @return bool
     */
    public static function delete(int $id): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE users SET deleted_at = CURRENT_TIMESTAMP, status = 'Inactive' WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Count total health workers and staff members
     * 
     * @return int
     */
    public static function countWorkers(): int {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT COUNT(*) FROM users WHERE role IN ('Health Worker', 'Staff') AND status = 'Active' AND deleted_at IS NULL");
        return (int)$stmt->fetchColumn();
    }
}
