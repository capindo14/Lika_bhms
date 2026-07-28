<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use Exception;

class Family {
    /**
     * Get all family profiles
     * 
     * @return array
     */
    public static function getAll(): array {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT f.*, 
                   CONCAT(r.last_name, ', ', r.first_name) AS head_name,
                   r.resident_id AS head_code,
                   (SELECT COUNT(*) FROM family_members WHERE family_id = f.id) AS total_members
            FROM families f
            JOIN residents r ON f.head_resident_id = r.id
            WHERE f.deleted_at IS NULL
            ORDER BY f.family_no DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Find family by database primary key
     * 
     * @param int $id
     * @return array|null
     */
    public static function getById(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT f.*, 
                   CONCAT(r.last_name, ', ', r.first_name) AS head_name,
                   r.resident_id AS head_code
            FROM families f
            JOIN residents r ON f.head_resident_id = r.id
            WHERE f.id = :id AND f.deleted_at IS NULL
        ");
        $stmt->execute([':id' => $id]);
        $family = $stmt->fetch();
        return $family ?: null;
    }

    /**
     * Get all members in a family
     * 
     * @param int $familyId
     * @return array
     */
    public static function getMembers(int $familyId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT fm.*, 
                   r.resident_id AS resident_code,
                   r.first_name, 
                   r.middle_name, 
                   r.last_name, 
                   r.gender, 
                   r.birthdate,
                   TIMESTAMPDIFF(YEAR, r.birthdate, CURDATE()) AS age
            FROM family_members fm
            JOIN residents r ON fm.resident_id = r.id
            WHERE fm.family_id = :family_id AND r.deleted_at IS NULL
        ");
        $stmt->execute([':family_id' => $familyId]);
        return $stmt->fetchAll();
    }

    /**
     * Generate dynamic family profile code FAM-YYYY-XXXX
     * 
     * @return string
     */
    public static function generateFamilyNo(): string {
        $db = Database::getConnection();
        $year = date('Y');
        
        $stmt = $db->prepare("SELECT COUNT(*) FROM families WHERE family_no LIKE :prefix");
        $stmt->execute([':prefix' => "FAM-{$year}-%"]);
        $count = (int)$stmt->fetchColumn() + 1;
        
        return "FAM-{$year}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create family record and associate members inside a transaction
     * 
     * @param array $data
     * @param array $members Array of elements containing ['resident_id' => int, 'relationship' => string]
     * @return int
     */
    public static function create(array $data, array $members): int {
        $db = Database::getConnection();
        
        try {
            $db->beginTransaction();

            $familyNo = self::generateFamilyNo();

            // 1. Insert Family Header
            $stmt = $db->prepare("
                INSERT INTO families (
                    family_no, head_resident_id, address, 
                    occupation, educational_attainment, pregnancy_status, 
                    family_planning_status, child_feeding_type, toilet_type, 
                    water_source, food_production_activity
                )
                VALUES (
                    :family_no, :head_resident_id, :address, 
                    :occupation, :educational_attainment, :pregnancy_status, 
                    :family_planning_status, :child_feeding_type, :toilet_type, 
                    :water_source, :food_production_activity
                )
            ");
            $stmt->execute([
                ':family_no' => $familyNo,
                ':head_resident_id' => $data['head_resident_id'],
                ':address' => $data['address'],
                ':occupation' => $data['occupation'] ?? null,
                ':educational_attainment' => $data['educational_attainment'] ?? null,
                ':pregnancy_status' => $data['pregnancy_status'] ?? null,
                ':family_planning_status' => $data['family_planning_status'] ?? null,
                ':child_feeding_type' => $data['child_feeding_type'] ?? null,
                ':toilet_type' => $data['toilet_type'] ?? null,
                ':water_source' => $data['water_source'] ?? null,
                ':food_production_activity' => $data['food_production_activity'] ?? null
            ]);
            $familyId = (int)$db->lastInsertId();

            // 2. Mark Head Resident as a Family Head in residents table
            $stmtHead = $db->prepare("UPDATE residents SET is_family_head = 1 WHERE id = :id");
            $stmtHead->execute([':id' => $data['head_resident_id']]);

            // 3. Insert head into family_members
            $stmtMember = $db->prepare("
                INSERT INTO family_members (family_id, resident_id, relationship_to_head)
                VALUES (:family_id, :resident_id, :relationship_to_head)
            ");
            $stmtMember->execute([
                ':family_id' => $familyId,
                ':resident_id' => $data['head_resident_id'],
                ':relationship_to_head' => 'Head'
            ]);

            // 4. Insert other members
            foreach ($members as $m) {
                // Ensure we don't insert duplicate head or empty members
                if ($m['resident_id'] == $data['head_resident_id'] || empty($m['resident_id'])) {
                    continue;
                }

                // Check if resident is already a member of another family
                $check = $db->prepare("SELECT COUNT(*) FROM family_members WHERE resident_id = :r_id");
                $check->execute([':r_id' => $m['resident_id']]);
                if ((int)$check->fetchColumn() > 0) {
                    throw new Exception("One or more selected residents are already members of another family profile.");
                }

                $stmtMember->execute([
                    ':family_id' => $familyId,
                    ':resident_id' => $m['resident_id'],
                    ':relationship_to_head' => $m['relationship']
                ]);
            }

            $db->commit();
            return $familyId;

        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Update family profile and rewrite member list inside a transaction
     * 
     * @param int $id
     * @param array $data
     * @param array $members
     * @return bool
     */
    public static function update(int $id, array $data, array $members): bool {
        $db = Database::getConnection();
        
        try {
            $db->beginTransaction();

            $existing = self::getById($id);
            if (!$existing) {
                throw new Exception("Family profile not found.");
            }

            // 1. Update Head flags if head was changed
            if ($existing['head_resident_id'] != $data['head_resident_id']) {
                // Mark old head as not family head
                $stmtHead = $db->prepare("UPDATE residents SET is_family_head = 0 WHERE id = :id");
                $stmtHead->execute([':id' => $existing['head_resident_id']]);
                
                // Mark new head as family head
                $stmtHead = $db->prepare("UPDATE residents SET is_family_head = 1 WHERE id = :id");
                $stmtHead->execute([':id' => $data['head_resident_id']]);
            }

            // 2. Update Family Header
            $stmt = $db->prepare("
                UPDATE families 
                SET head_resident_id = :head_resident_id, 
                    address = :address,
                    occupation = :occupation,
                    educational_attainment = :educational_attainment,
                    pregnancy_status = :pregnancy_status,
                    family_planning_status = :family_planning_status,
                    child_feeding_type = :child_feeding_type,
                    toilet_type = :toilet_type,
                    water_source = :water_source,
                    food_production_activity = :food_production_activity
                WHERE id = :id AND deleted_at IS NULL
            ");
            $stmt->execute([
                ':id' => $id,
                ':head_resident_id' => $data['head_resident_id'],
                ':address' => $data['address'],
                ':occupation' => $data['occupation'] ?? null,
                ':educational_attainment' => $data['educational_attainment'] ?? null,
                ':pregnancy_status' => $data['pregnancy_status'] ?? null,
                ':family_planning_status' => $data['family_planning_status'] ?? null,
                ':child_feeding_type' => $data['child_feeding_type'] ?? null,
                ':toilet_type' => $data['toilet_type'] ?? null,
                ':water_source' => $data['water_source'] ?? null,
                ':food_production_activity' => $data['food_production_activity'] ?? null
            ]);

            // 3. Clear all old members
            $db->prepare("DELETE FROM family_members WHERE family_id = :id")->execute([':id' => $id]);

            // 4. Re-insert head into family_members
            $stmtMember = $db->prepare("
                INSERT INTO family_members (family_id, resident_id, relationship_to_head)
                VALUES (:family_id, :resident_id, :relationship_to_head)
            ");
            $stmtMember->execute([
                ':family_id' => $id,
                ':resident_id' => $data['head_resident_id'],
                ':relationship_to_head' => 'Head'
            ]);

            // 5. Re-insert other members
            foreach ($members as $m) {
                if ($m['resident_id'] == $data['head_resident_id'] || empty($m['resident_id'])) {
                    continue;
                }

                // Check if resident is in another family
                $check = $db->prepare("SELECT COUNT(*) FROM family_members WHERE resident_id = :r_id");
                $check->execute([':r_id' => $m['resident_id']]);
                if ((int)$check->fetchColumn() > 0) {
                    throw new Exception("One or more selected residents are already members of another family profile.");
                }

                $stmtMember->execute([
                    ':family_id' => $id,
                    ':resident_id' => $m['resident_id'],
                    ':relationship_to_head' => $m['relationship']
                ]);
            }

            $db->commit();
            return true;

        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Soft delete family profile
     * 
     * @param int $id
     * @return bool
     */
    public static function delete(int $id): bool {
        $db = Database::getConnection();
        try {
            $db->beginTransaction();
            
            $family = self::getById($id);
            if ($family) {
                // Clear is_family_head flag on the head
                $db->prepare("UPDATE residents SET is_family_head = 0 WHERE id = :id")
                   ->execute([':id' => $family['head_resident_id']]);
            }
            
            // Delete members
            $db->prepare("DELETE FROM family_members WHERE family_id = :id")->execute([':id' => $id]);
            
            // Soft delete family
            $db->prepare("UPDATE families SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id")->execute([':id' => $id]);
            
            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            return false;
        }
    }

    /**
     * Count total family profiles
     * 
     * @return int
     */
    public static function countAll(): int {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT COUNT(*) FROM families WHERE deleted_at IS NULL");
        return (int)$stmt->fetchColumn();
    }
}
