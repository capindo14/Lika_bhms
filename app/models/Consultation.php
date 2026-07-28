<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use Exception;

class Consultation {
    /**
     * Get all consultations
     * 
     * @return array
     */
    public static function getAll(): array {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT c.*, 
                   CONCAT(r.last_name, ', ', r.first_name) AS resident_name,
                   r.resident_id AS resident_code,
                   u.fullname AS worker_name,
                   m.name AS medicine_name
            FROM consultations c
            JOIN residents r ON c.resident_id = r.id
            JOIN users u ON c.user_id = u.id
            LEFT JOIN medicines m ON c.medicine_id = m.id
            WHERE c.deleted_at IS NULL
            ORDER BY c.consultation_date DESC, c.id DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Find consultation by primary key
     * 
     * @param int $id
     * @return array|null
     */
    public static function getById(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT c.*, 
                   CONCAT(r.last_name, ', ', r.first_name) AS resident_name,
                   r.resident_id AS resident_code,
                   r.gender AS resident_gender,
                   TIMESTAMPDIFF(YEAR, r.birthdate, CURDATE()) AS resident_age,
                   u.fullname AS worker_name,
                   m.name AS medicine_name
            FROM consultations c
            JOIN residents r ON c.resident_id = r.id
            JOIN users u ON c.user_id = u.id
            LEFT JOIN medicines m ON c.medicine_id = m.id
            WHERE c.id = :id AND c.deleted_at IS NULL
        ");
        $stmt->execute([':id' => $id]);
        $con = $stmt->fetch();
        return $con ?: null;
    }

    /**
     * Generate dynamic consultation number CON-YYYY-XXXX
     * 
     * @return string
     */
    public static function generateConsultationNo(): string {
        $db = Database::getConnection();
        $year = date('Y');
        
        $stmt = $db->prepare("SELECT COUNT(*) FROM consultations WHERE consultation_no LIKE :prefix");
        $stmt->execute([':prefix' => "CON-{$year}-%"]);
        $count = (int)$stmt->fetchColumn() + 1;
        
        return "CON-{$year}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Log a consultation and optionally handle medicine inventory deductions
     * 
     * @param array $data
     * @return int Inserted ID
     */
    public static function create(array $data): int {
        $db = Database::getConnection();
        
        try {
            $db->beginTransaction();

            $consultationNo = self::generateConsultationNo();
            $medId = !empty($data['medicine_id']) ? (int)$data['medicine_id'] : null;
            $medQty = !empty($data['medicine_qty']) ? (int)$data['medicine_qty'] : null;

            // 1. Deduct Stock if medicine was selected
            if ($medId !== null && $medQty !== null && $medQty > 0) {
                // Deduct via model (this will throw Exception if insufficient stock)
                Medicine::deductStock($medId, $medQty);
                
                // Add an entry to medicine_distributions table to track it fully
                $stmtDist = $db->prepare("
                    INSERT INTO medicine_distributions (medicine_id, resident_id, quantity, distribution_date, user_id)
                    VALUES (:med_id, :res_id, :qty, :dist_date, :user_id)
                ");
                $stmtDist->execute([
                    ':med_id' => $medId,
                    ':res_id' => $data['resident_id'],
                    ':qty' => $medQty,
                    ':dist_date' => $data['consultation_date'],
                    ':user_id' => $data['user_id']
                ]);
            }

            // 2. Insert Consultation Record
            $stmt = $db->prepare("
                INSERT INTO consultations (consultation_no, resident_id, user_id, symptoms, diagnosis, treatment, medicine_id, medicine_qty, consultation_date, status)
                VALUES (:consultation_no, :resident_id, :user_id, :symptoms, :diagnosis, :treatment, :medicine_id, :medicine_qty, :consultation_date, :status)
            ");

            $stmt->execute([
                ':consultation_no' => $consultationNo,
                ':resident_id' => $data['resident_id'],
                ':user_id' => $data['user_id'],
                ':symptoms' => $data['symptoms'],
                ':diagnosis' => $data['diagnosis'],
                ':treatment' => $data['treatment'],
                ':medicine_id' => $medId,
                ':medicine_qty' => $medQty,
                ':consultation_date' => $data['consultation_date'],
                ':status' => $data['status'] ?? 'Completed'
            ]);

            $insertId = (int)$db->lastInsertId();
            $db->commit();
            
            return $insertId;

        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Update consultation details
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public static function update(int $id, array $data): bool {
        $db = Database::getConnection();
        
        try {
            $db->beginTransaction();

            $existing = self::getById($id);
            if (!$existing) {
                throw new Exception("Consultation record not found.");
            }

            $newMedId = !empty($data['medicine_id']) ? (int)$data['medicine_id'] : null;
            $newMedQty = !empty($data['medicine_qty']) ? (int)$data['medicine_qty'] : null;

            $oldMedId = $existing['medicine_id'];
            $oldMedQty = $existing['medicine_qty'];

            // Handle inventory adjustment
            // If the distributed medicine or quantity changed, we must reverse the old stock and deduct the new stock.
            if ($oldMedId != $newMedId || $oldMedQty != $newMedQty) {
                // Reverse old stock allocation
                if (!empty($oldMedId) && !empty($oldMedQty)) {
                    Medicine::addStock($oldMedId, $oldMedQty);
                    // Remove matching distribution record
                    $db->prepare("
                        DELETE FROM medicine_distributions 
                        WHERE medicine_id = :med_id 
                        AND resident_id = :res_id 
                        AND quantity = :qty 
                        AND distribution_date = :dist_date
                        LIMIT 1
                    ")->execute([
                        ':med_id' => $oldMedId,
                        ':res_id' => $existing['resident_id'],
                        ':qty' => $oldMedQty,
                        ':dist_date' => $existing['consultation_date']
                    ]);
                }

                // Allocate new stock
                if (!empty($newMedId) && !empty($newMedQty) && $newMedQty > 0) {
                    Medicine::deductStock($newMedId, $newMedQty);
                    
                    // Add new distribution record
                    $stmtDist = $db->prepare("
                        INSERT INTO medicine_distributions (medicine_id, resident_id, quantity, distribution_date, user_id)
                        VALUES (:med_id, :res_id, :qty, :dist_date, :user_id)
                    ");
                    $stmtDist->execute([
                        ':med_id' => $newMedId,
                        ':res_id' => $data['resident_id'],
                        ':qty' => $newMedQty,
                        ':dist_date' => $data['consultation_date'],
                        ':user_id' => $data['user_id']
                    ]);
                }
            }

            // Update Consultation Record
            $stmt = $db->prepare("
                UPDATE consultations 
                SET resident_id = :resident_id,
                    symptoms = :symptoms,
                    diagnosis = :diagnosis,
                    treatment = :treatment,
                    medicine_id = :medicine_id,
                    medicine_qty = :medicine_qty,
                    consultation_date = :consultation_date,
                    status = :status
                WHERE id = :id AND deleted_at IS NULL
            ");

            $stmt->execute([
                ':id' => $id,
                ':resident_id' => $data['resident_id'],
                ':symptoms' => $data['symptoms'],
                ':diagnosis' => $data['diagnosis'],
                ':treatment' => $data['treatment'],
                ':medicine_id' => $newMedId,
                ':medicine_qty' => $newMedQty,
                ':consultation_date' => $data['consultation_date'],
                ':status' => $data['status']
            ]);

            $db->commit();
            return true;

        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Soft delete consultation (reverses inventory first)
     * 
     * @param int $id
     * @return bool
     */
    public static function delete(int $id): bool {
        $db = Database::getConnection();
        try {
            $db->beginTransaction();
            
            $existing = self::getById($id);
            if ($existing) {
                // Reverse stock
                if (!empty($existing['medicine_id']) && !empty($existing['medicine_qty'])) {
                    Medicine::addStock($existing['medicine_id'], $existing['medicine_qty']);
                    
                    // Remove matching distribution record
                    $db->prepare("
                        DELETE FROM medicine_distributions 
                        WHERE medicine_id = :med_id 
                        AND resident_id = :res_id 
                        AND quantity = :qty 
                        AND distribution_date = :dist_date
                        LIMIT 1
                    ")->execute([
                        ':med_id' => $existing['medicine_id'],
                        ':res_id' => $existing['resident_id'],
                        ':qty' => $existing['medicine_qty'],
                        ':dist_date' => $existing['consultation_date']
                    ]);
                }
                
                // Soft delete
                $db->prepare("UPDATE consultations SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id")
                   ->execute([':id' => $id]);
            }
            
            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            return false;
        }
    }

    /**
     * Count total consultations
     * 
     * @return int
     */
    public static function countAll(): int {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT COUNT(*) FROM consultations WHERE deleted_at IS NULL");
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get newest consultations
     * 
     * @param int $limit
     * @return array
     */
    public static function getLatest(int $limit = 5): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT c.*, CONCAT(r.last_name, ', ', r.first_name) AS resident_name, u.fullname AS worker_name
            FROM consultations c
            JOIN residents r ON c.resident_id = r.id
            JOIN users u ON c.user_id = u.id
            WHERE c.deleted_at IS NULL
            ORDER BY c.consultation_date DESC, c.id DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get monthly consultation trends for current year
     * 
     * @return array Array of months (1-12) mapped to counts
     */
    public static function getMonthlyCounts(): array {
        $db = Database::getConnection();
        $year = date('Y');
        
        $stmt = $db->prepare("
            SELECT MONTH(consultation_date) as month, COUNT(*) as count 
            FROM consultations 
            WHERE YEAR(consultation_date) = :year AND deleted_at IS NULL
            GROUP BY MONTH(consultation_date)
        ");
        $stmt->execute([':year' => $year]);
        $rows = $stmt->fetchAll();
        
        $counts = array_fill(1, 12, 0);
        foreach ($rows as $row) {
            $counts[(int)$row['month']] = (int)$row['count'];
        }
        return $counts;
    }
}
