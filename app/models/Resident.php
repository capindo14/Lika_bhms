<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use Exception;

class Resident {
    /**
     * Get all active (non-archived) residents
     * 
     * @return array
     */
    public static function getAll(string $status = 'Active'): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT *, TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) AS age 
            FROM residents 
            WHERE status = :status AND deleted_at IS NULL 
            ORDER BY last_name, first_name
        ");
        $stmt->execute([':status' => $status]);
        return $stmt->fetchAll();
    }

    /**
     * Get resident by database primary key
     * 
     * @param int $id
     * @return array|null
     */
    public static function getById(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT *, TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) AS age 
            FROM residents 
            WHERE id = :id AND deleted_at IS NULL
        ");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Search residents by name, ID, contact
     * 
     * @param string $term
     * @return array
     */
    public static function search(string $term): array {
        $db = Database::getConnection();
        $term = "%{$term}%";
        $stmt = $db->prepare("
            SELECT *, TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) AS age 
            FROM residents 
            WHERE (first_name LIKE :term OR last_name LIKE :term OR resident_id LIKE :term)
            AND status = 'Active' AND deleted_at IS NULL
            LIMIT 50
        ");
        $stmt->execute([':term' => $term]);
        return $stmt->fetchAll();
    }

    /**
     * Generate dynamic resident code RES-YYYY-XXXX
     * 
     * @return string
     */
    public static function generateResidentId(): string {
        $db = Database::getConnection();
        $year = date('Y');
        
        $stmt = $db->prepare("SELECT COUNT(*) FROM residents WHERE resident_id LIKE :prefix");
        $stmt->execute([':prefix' => "RES-{$year}-%"]);
        $count = (int)$stmt->fetchColumn() + 1;
        
        return "RES-{$year}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Insert new resident
     * 
     * @param array $data
     * @return int Inserted ID
     */
    public static function create(array $data): int {
        $db = Database::getConnection();
        
        $residentId = self::generateResidentId();
        
        $stmt = $db->prepare("
            INSERT INTO residents (resident_id, first_name, middle_name, last_name, gender, birthdate, civil_status, contact_number, address, barangay, is_family_head, status)
            VALUES (:resident_id, :first_name, :middle_name, :last_name, :gender, :birthdate, :civil_status, :contact_number, :address, :barangay, :is_family_head, 'Active')
        ");

        $stmt->execute([
            ':resident_id' => $residentId,
            ':first_name' => $data['first_name'],
            ':middle_name' => $data['middle_name'] ?? null,
            ':last_name' => $data['last_name'],
            ':gender' => $data['gender'],
            ':birthdate' => $data['birthdate'],
            ':civil_status' => $data['civil_status'],
            ':contact_number' => $data['contact_number'] ?? null,
            ':address' => $data['address'],
            ':barangay' => $data['barangay'] ?: 'Barangay Health Center',
            ':is_family_head' => isset($data['is_family_head']) ? (int)$data['is_family_head'] : 0
        ]);

        return (int)$db->lastInsertId();
    }

    /**
     * Update resident details
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public static function update(int $id, array $data): bool {
        $db = Database::getConnection();

        $stmt = $db->prepare("
            UPDATE residents 
            SET first_name = :first_name, 
                middle_name = :middle_name, 
                last_name = :last_name, 
                gender = :gender, 
                birthdate = :birthdate, 
                civil_status = :civil_status, 
                contact_number = :contact_number, 
                address = :address, 
                barangay = :barangay, 
                is_family_head = :is_family_head
            WHERE id = :id AND deleted_at IS NULL
        ");

        return $stmt->execute([
            ':id' => $id,
            ':first_name' => $data['first_name'],
            ':middle_name' => $data['middle_name'] ?? null,
            ':last_name' => $data['last_name'],
            ':gender' => $data['gender'],
            ':birthdate' => $data['birthdate'],
            ':civil_status' => $data['civil_status'],
            ':contact_number' => $data['contact_number'] ?? null,
            ':address' => $data['address'],
            ':barangay' => $data['barangay'],
            ':is_family_head' => isset($data['is_family_head']) ? (int)$data['is_family_head'] : 0
        ]);
    }

    /**
     * Toggle archive status (soft delete)
     * 
     * @param int $id
     * @param string $status 'Active' or 'Archived'
     * @return bool
     */
    public static function setStatus(int $id, string $status): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE residents SET status = :status WHERE id = :id AND deleted_at IS NULL");
        return $stmt->execute([':id' => $id, ':status' => $status]);
    }

    /**
     * Soft delete a resident
     * 
     * @param int $id
     * @return bool
     */
    public static function delete(int $id): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE residents SET deleted_at = CURRENT_TIMESTAMP, status = 'Archived' WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Count total active residents
     * 
     * @return int
     */
    public static function countAll(): int {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT COUNT(*) FROM residents WHERE status = 'Active' AND deleted_at IS NULL");
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get Gender distribution
     * 
     * @return array
     */
    public static function getGenderDistribution(): array {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT gender, COUNT(*) as count 
            FROM residents 
            WHERE status = 'Active' AND deleted_at IS NULL 
            GROUP BY gender
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get Age distribution
     * 
     * @return array
     */
    public static function getAgeDistribution(): array {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT 
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) < 1 THEN 1 ELSE 0 END) AS infants,
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 1 AND 12 THEN 1 ELSE 0 END) AS children,
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 13 AND 19 THEN 1 ELSE 0 END) AS teens,
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 20 AND 59 THEN 1 ELSE 0 END) AS adults,
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) >= 60 THEN 1 ELSE 0 END) AS seniors
            FROM residents
            WHERE status = 'Active' AND deleted_at IS NULL
        ");
        return $stmt->fetch() ?: [];
    }

    /**
     * Get newest residents
     * 
     * @param int $limit
     * @return array
     */
    public static function getLatest(int $limit = 5): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT *, TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) AS age 
            FROM residents 
            WHERE status = 'Active' AND deleted_at IS NULL 
            ORDER BY created_at DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
