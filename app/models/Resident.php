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
        $whereClause = "r.status = :status";
        $params = [':status' => $status];

        if ($status === 'Pregnant') {
            $whereClause = "r.status = 'Active' AND r.pregnancy_status = 'Pregnant'";
            $params = [];
        } elseif ($status === 'Infant') {
            $whereClause = "r.status = 'Active' AND (TIMESTAMPDIFF(YEAR, r.birthdate, CURDATE()) < 5 OR (r.child_feeding_type IS NOT NULL AND r.child_feeding_type != '' AND r.child_feeding_type != 'N/A'))";
            $params = [];
        }

        $stmt = $db->prepare("
            SELECT r.*, TIMESTAMPDIFF(YEAR, r.birthdate, CURDATE()) AS age,
                   f.id AS family_id, f.family_no, fm.relationship_to_head,
                   CASE 
                       WHEN fm.relationship_to_head = 'Head' THEN (
                           SELECT fm2.resident_id 
                           FROM family_members fm2 
                           WHERE fm2.family_id = fm.family_id 
                             AND LOWER(fm2.relationship_to_head) IN ('spouse', 'husband', 'wife') 
                           LIMIT 1
                       )
                       WHEN LOWER(fm.relationship_to_head) IN ('spouse', 'husband', 'wife') THEN f.head_resident_id
                       ELSE NULL 
                   END AS spouse_id,
                   CASE 
                       WHEN fm.relationship_to_head = 'Head' THEN (
                           SELECT CONCAT(r2.last_name, ', ', r2.first_name, ' ', COALESCE(r2.middle_name, ''))
                           FROM family_members fm2 
                           JOIN residents r2 ON fm2.resident_id = r2.id
                           WHERE fm2.family_id = fm.family_id 
                             AND LOWER(fm2.relationship_to_head) IN ('spouse', 'husband', 'wife') 
                             AND r2.deleted_at IS NULL
                           LIMIT 1
                       )
                       WHEN LOWER(fm.relationship_to_head) IN ('spouse', 'husband', 'wife') THEN (
                           SELECT CONCAT(r2.last_name, ', ', r2.first_name, ' ', COALESCE(r2.middle_name, ''))
                           FROM residents r2 
                           WHERE r2.id = f.head_resident_id 
                             AND r2.deleted_at IS NULL
                       )
                       ELSE NULL 
                   END AS spouse_name,
                   CASE 
                       WHEN fm.relationship_to_head = 'Head' THEN (
                           SELECT r2.resident_id
                           FROM family_members fm2 
                           JOIN residents r2 ON fm2.resident_id = r2.id
                           WHERE fm2.family_id = fm.family_id 
                             AND LOWER(fm2.relationship_to_head) IN ('spouse', 'husband', 'wife') 
                             AND r2.deleted_at IS NULL
                           LIMIT 1
                       )
                       WHEN LOWER(fm.relationship_to_head) IN ('spouse', 'husband', 'wife') THEN (
                           SELECT r2.resident_id
                           FROM residents r2 
                           WHERE r2.id = f.head_resident_id 
                             AND r2.deleted_at IS NULL
                       )
                       ELSE NULL 
                   END AS spouse_code,
                   CASE 
                        WHEN fm.relationship_to_head = 'Head' THEN (
                            SELECT r2.gender
                            FROM family_members fm2 
                            JOIN residents r2 ON fm2.resident_id = r2.id
                            WHERE fm2.family_id = fm.family_id 
                              AND LOWER(fm2.relationship_to_head) IN ('spouse', 'husband', 'wife') 
                              AND r2.deleted_at IS NULL
                            LIMIT 1
                        )
                        WHEN LOWER(fm.relationship_to_head) IN ('spouse', 'husband', 'wife') THEN (
                            SELECT r2.gender
                            FROM residents r2 
                            WHERE r2.id = f.head_resident_id 
                              AND r2.deleted_at IS NULL
                        )
                        ELSE NULL 
                    END AS spouse_gender
            FROM residents r
            LEFT JOIN family_members fm ON r.id = fm.resident_id
            LEFT JOIN families f ON fm.family_id = f.id
            WHERE {$whereClause} AND r.deleted_at IS NULL 
            ORDER BY r.last_name, r.first_name
        ");
        $stmt->execute($params);
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
            SELECT r.*, TIMESTAMPDIFF(YEAR, r.birthdate, CURDATE()) AS age,
                   f.id AS family_id, f.family_no, fm.relationship_to_head,
                   CASE 
                       WHEN fm.relationship_to_head = 'Head' THEN (
                           SELECT fm2.resident_id 
                           FROM family_members fm2 
                           WHERE fm2.family_id = fm.family_id 
                             AND LOWER(fm2.relationship_to_head) IN ('spouse', 'husband', 'wife') 
                           LIMIT 1
                       )
                       WHEN LOWER(fm.relationship_to_head) IN ('spouse', 'husband', 'wife') THEN f.head_resident_id
                       ELSE NULL 
                   END AS spouse_id,
                   CASE 
                       WHEN fm.relationship_to_head = 'Head' THEN (
                           SELECT CONCAT(r2.last_name, ', ', r2.first_name, ' ', COALESCE(r2.middle_name, ''))
                           FROM family_members fm2 
                           JOIN residents r2 ON fm2.resident_id = r2.id
                           WHERE fm2.family_id = fm.family_id 
                             AND LOWER(fm2.relationship_to_head) IN ('spouse', 'husband', 'wife') 
                             AND r2.deleted_at IS NULL
                           LIMIT 1
                       )
                       WHEN LOWER(fm.relationship_to_head) IN ('spouse', 'husband', 'wife') THEN (
                           SELECT CONCAT(r2.last_name, ', ', r2.first_name, ' ', COALESCE(r2.middle_name, ''))
                           FROM residents r2 
                           WHERE r2.id = f.head_resident_id 
                             AND r2.deleted_at IS NULL
                       )
                       ELSE NULL 
                   END AS spouse_name,
                   CASE 
                       WHEN fm.relationship_to_head = 'Head' THEN (
                           SELECT r2.resident_id
                           FROM family_members fm2 
                           JOIN residents r2 ON fm2.resident_id = r2.id
                           WHERE fm2.family_id = fm.family_id 
                             AND LOWER(fm2.relationship_to_head) IN ('spouse', 'husband', 'wife') 
                             AND r2.deleted_at IS NULL
                           LIMIT 1
                       )
                       WHEN LOWER(fm.relationship_to_head) IN ('spouse', 'husband', 'wife') THEN (
                           SELECT r2.resident_id
                           FROM residents r2 
                           WHERE r2.id = f.head_resident_id 
                             AND r2.deleted_at IS NULL
                       )
                       ELSE NULL 
                   END AS spouse_code,
                   CASE 
                        WHEN fm.relationship_to_head = 'Head' THEN (
                            SELECT r2.gender
                            FROM family_members fm2 
                            JOIN residents r2 ON fm2.resident_id = r2.id
                            WHERE fm2.family_id = fm.family_id 
                              AND LOWER(fm2.relationship_to_head) IN ('spouse', 'husband', 'wife') 
                              AND r2.deleted_at IS NULL
                            LIMIT 1
                        )
                        WHEN LOWER(fm.relationship_to_head) IN ('spouse', 'husband', 'wife') THEN (
                            SELECT r2.gender
                            FROM residents r2 
                            WHERE r2.id = f.head_resident_id 
                              AND r2.deleted_at IS NULL
                        )
                        ELSE NULL 
                    END AS spouse_gender
            FROM residents r
            LEFT JOIN family_members fm ON r.id = fm.resident_id
            LEFT JOIN families f ON fm.family_id = f.id
            WHERE r.id = :id AND r.deleted_at IS NULL
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
            INSERT INTO residents (resident_id, first_name, middle_name, last_name, gender, birthdate, civil_status, contact_number, address, is_family_head, pregnancy_status, child_feeding_type, status)
            VALUES (:resident_id, :first_name, :middle_name, :last_name, :gender, :birthdate, :civil_status, :contact_number, :address, :is_family_head, :pregnancy_status, :child_feeding_type, 'Active')
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
            ':is_family_head' => isset($data['is_family_head']) ? (int)$data['is_family_head'] : 0,
            ':pregnancy_status' => $data['pregnancy_status'] ?? null,
            ':child_feeding_type' => $data['child_feeding_type'] ?? null
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
                is_family_head = :is_family_head,
                pregnancy_status = :pregnancy_status,
                child_feeding_type = :child_feeding_type
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
            ':is_family_head' => isset($data['is_family_head']) ? (int)$data['is_family_head'] : 0,
            ':pregnancy_status' => $data['pregnancy_status'] ?? null,
            ':child_feeding_type' => $data['child_feeding_type'] ?? null
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
            SELECT r.*, TIMESTAMPDIFF(YEAR, r.birthdate, CURDATE()) AS age,
                   f.id AS family_id, f.family_no, fm.relationship_to_head,
                   CASE 
                       WHEN fm.relationship_to_head = 'Head' THEN (
                           SELECT fm2.resident_id 
                           FROM family_members fm2 
                           WHERE fm2.family_id = fm.family_id 
                             AND LOWER(fm2.relationship_to_head) IN ('spouse', 'husband', 'wife') 
                           LIMIT 1
                       )
                       WHEN LOWER(fm.relationship_to_head) IN ('spouse', 'husband', 'wife') THEN f.head_resident_id
                       ELSE NULL 
                   END AS spouse_id,
                   CASE 
                       WHEN fm.relationship_to_head = 'Head' THEN (
                           SELECT CONCAT(r2.last_name, ', ', r2.first_name, ' ', COALESCE(r2.middle_name, ''))
                           FROM family_members fm2 
                           JOIN residents r2 ON fm2.resident_id = r2.id
                           WHERE fm2.family_id = fm.family_id 
                             AND LOWER(fm2.relationship_to_head) IN ('spouse', 'husband', 'wife') 
                             AND r2.deleted_at IS NULL
                           LIMIT 1
                       )
                       WHEN LOWER(fm.relationship_to_head) IN ('spouse', 'husband', 'wife') THEN (
                           SELECT CONCAT(r2.last_name, ', ', r2.first_name, ' ', COALESCE(r2.middle_name, ''))
                           FROM residents r2 
                           WHERE r2.id = f.head_resident_id 
                             AND r2.deleted_at IS NULL
                       )
                       ELSE NULL 
                   END AS spouse_name,
                   CASE 
                       WHEN fm.relationship_to_head = 'Head' THEN (
                           SELECT r2.resident_id
                           FROM family_members fm2 
                           JOIN residents r2 ON fm2.resident_id = r2.id
                           WHERE fm2.family_id = fm.family_id 
                             AND LOWER(fm2.relationship_to_head) IN ('spouse', 'husband', 'wife') 
                             AND r2.deleted_at IS NULL
                           LIMIT 1
                       )
                       WHEN LOWER(fm.relationship_to_head) IN ('spouse', 'husband', 'wife') THEN (
                           SELECT r2.resident_id
                           FROM residents r2 
                           WHERE r2.id = f.head_resident_id 
                             AND r2.deleted_at IS NULL
                       )
                       ELSE NULL 
                   END AS spouse_code,
                   CASE 
                        WHEN fm.relationship_to_head = 'Head' THEN (
                            SELECT r2.gender
                            FROM family_members fm2 
                            JOIN residents r2 ON fm2.resident_id = r2.id
                            WHERE fm2.family_id = fm.family_id 
                              AND LOWER(fm2.relationship_to_head) IN ('spouse', 'husband', 'wife') 
                              AND r2.deleted_at IS NULL
                            LIMIT 1
                        )
                        WHEN LOWER(fm.relationship_to_head) IN ('spouse', 'husband', 'wife') THEN (
                            SELECT r2.gender
                            FROM residents r2 
                            WHERE r2.id = f.head_resident_id 
                              AND r2.deleted_at IS NULL
                        )
                        ELSE NULL 
                    END AS spouse_gender
            FROM residents r
            LEFT JOIN family_members fm ON r.id = fm.resident_id
            LEFT JOIN families f ON fm.family_id = f.id
            WHERE r.status = 'Active' AND r.deleted_at IS NULL 
            ORDER BY r.created_at DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
