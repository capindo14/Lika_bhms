<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use Exception;

class Immunization {
    /**
     * Get all immunization records
     * 
     * @return array
     */
    public static function getAll(): array {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT i.*, 
                   CONCAT(r.last_name, ', ', r.first_name) AS resident_name,
                   r.resident_id AS resident_code,
                   TIMESTAMPDIFF(YEAR, r.birthdate, CURDATE()) AS resident_age,
                   m.name AS vaccine_name,
                   u.fullname AS worker_name
            FROM immunizations i
            JOIN residents r ON i.resident_id = r.id
            JOIN medicines m ON i.vaccine_id = m.id
            JOIN users u ON i.user_id = u.id
            WHERE i.deleted_at IS NULL
            ORDER BY i.next_schedule ASC, i.date_given DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Find immunization record by primary key
     * 
     * @param int $id
     * @return array|null
     */
    public static function getById(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT i.*, 
                   CONCAT(r.last_name, ', ', r.first_name) AS resident_name,
                   r.resident_id AS resident_code,
                   TIMESTAMPDIFF(YEAR, r.birthdate, CURDATE()) AS resident_age,
                   m.name AS vaccine_name,
                   u.fullname AS worker_name
            FROM immunizations i
            JOIN residents r ON i.resident_id = r.id
            JOIN medicines m ON i.vaccine_id = m.id
            JOIN users u ON i.user_id = u.id
            WHERE i.id = :id AND i.deleted_at IS NULL
        ");
        $stmt->execute([':id' => $id]);
        $imm = $stmt->fetch();
        return $imm ?: null;
    }

    /**
     * Add new immunization entry (with auto-deduction of vaccine stock if completed)
     * 
     * @param array $data
     * @return int
     */
    public static function create(array $data): int {
        $db = Database::getConnection();
        
        try {
            $db->beginTransaction();

            $status = $data['status'] ?? 'Upcoming';
            $vaccineId = (int)$data['vaccine_id'];

            // Deduct vaccine stock if status is Completed
            if ($status === 'Completed') {
                Medicine::deductStock($vaccineId, 1);
                
                // Track stock distribution log
                $stmtDist = $db->prepare("
                    INSERT INTO medicine_distributions (medicine_id, resident_id, quantity, distribution_date, user_id)
                    VALUES (:med_id, :res_id, 1, :dist_date, :user_id)
                ");
                $stmtDist->execute([
                    ':med_id' => $vaccineId,
                    ':res_id' => $data['resident_id'],
                    ':dist_date' => $data['date_given'] ?: date('Y-m-d'),
                    ':user_id' => $data['user_id']
                ]);
            }

            $stmt = $db->prepare("
                INSERT INTO immunizations (resident_id, vaccine_id, dose, date_given, next_schedule, user_id, status)
                VALUES (:resident_id, :vaccine_id, :dose, :date_given, :next_schedule, :user_id, :status)
            ");

            $stmt->execute([
                ':resident_id' => $data['resident_id'],
                ':vaccine_id' => $vaccineId,
                ':dose' => $data['dose'],
                ':date_given' => !empty($data['date_given']) ? $data['date_given'] : null,
                ':next_schedule' => !empty($data['next_schedule']) ? $data['next_schedule'] : null,
                ':user_id' => $data['user_id'],
                ':status' => $status
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
     * Update immunization details
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
                throw new Exception("Immunization record not found.");
            }

            $newStatus = $data['status'];
            $oldStatus = $existing['status'];
            
            $newVaccineId = (int)$data['vaccine_id'];
            $oldVaccineId = (int)$existing['vaccine_id'];

            // Adjust inventory:
            // 1. If old status was Completed, but new status is not, add 1 back to old vaccine stock.
            if ($oldStatus === 'Completed' && $newStatus !== 'Completed') {
                Medicine::addStock($oldVaccineId, 1);
                
                // Clear matching distribution record
                $db->prepare("
                    DELETE FROM medicine_distributions 
                    WHERE medicine_id = :med_id AND resident_id = :res_id AND quantity = 1 AND distribution_date = :d_date LIMIT 1
                ")->execute([
                    ':med_id' => $oldVaccineId,
                    ':res_id' => $existing['resident_id'],
                    ':d_date' => $existing['date_given']
                ]);
            }
            
            // 2. If old status was not Completed, but new status is Completed, deduct 1 from new vaccine stock.
            if ($oldStatus !== 'Completed' && $newStatus === 'Completed') {
                Medicine::deductStock($newVaccineId, 1);
                
                // Insert distribution log
                $stmtDist = $db->prepare("
                    INSERT INTO medicine_distributions (medicine_id, resident_id, quantity, distribution_date, user_id)
                    VALUES (:med_id, :res_id, 1, :dist_date, :user_id)
                ");
                $stmtDist->execute([
                    ':med_id' => $newVaccineId,
                    ':res_id' => $data['resident_id'],
                    ':dist_date' => $data['date_given'] ?: date('Y-m-d'),
                    ':user_id' => $data['user_id']
                ]);
            }

            // 3. If both completed but vaccine changed
            if ($oldStatus === 'Completed' && $newStatus === 'Completed' && $oldVaccineId !== $newVaccineId) {
                Medicine::addStock($oldVaccineId, 1);
                Medicine::deductStock($newVaccineId, 1);
                
                // Update distribution entry
                $stmtDist = $db->prepare("
                    UPDATE medicine_distributions 
                    SET medicine_id = :new_med_id, distribution_date = :dist_date 
                    WHERE medicine_id = :old_med_id AND resident_id = :res_id AND quantity = 1 AND distribution_date = :old_date LIMIT 1
                ");
                $stmtDist->execute([
                    ':new_med_id' => $newVaccineId,
                    ':dist_date' => $data['date_given'] ?: date('Y-m-d'),
                    ':old_med_id' => $oldVaccineId,
                    ':res_id' => $existing['resident_id'],
                    ':old_date' => $existing['date_given']
                ]);
            }

            $stmt = $db->prepare("
                UPDATE immunizations 
                SET resident_id = :resident_id,
                    vaccine_id = :vaccine_id,
                    dose = :dose,
                    date_given = :date_given,
                    next_schedule = :next_schedule,
                    status = :status
                WHERE id = :id AND deleted_at IS NULL
            ");

            $stmt->execute([
                ':id' => $id,
                ':resident_id' => $data['resident_id'],
                ':vaccine_id' => $newVaccineId,
                ':dose' => $data['dose'],
                ':date_given' => !empty($data['date_given']) ? $data['date_given'] : null,
                ':next_schedule' => !empty($data['next_schedule']) ? $data['next_schedule'] : null,
                ':status' => $newStatus
            ]);

            $db->commit();
            return true;

        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Soft delete immunization record (reverses stock if Completed)
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
                if ($existing['status'] === 'Completed') {
                    Medicine::addStock($existing['vaccine_id'], 1);
                    
                    $db->prepare("
                        DELETE FROM medicine_distributions 
                        WHERE medicine_id = :med_id AND resident_id = :res_id AND quantity = 1 AND distribution_date = :d_date LIMIT 1
                    ")->execute([
                        ':med_id' => $existing['vaccine_id'],
                        ':res_id' => $existing['resident_id'],
                        ':d_date' => $existing['date_given']
                    ]);
                }
                
                $db->prepare("UPDATE immunizations SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id")
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
     * Count total active immunization visits logged
     * 
     * @return int
     */
    public static function countAll(): int {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT COUNT(*) FROM immunizations WHERE deleted_at IS NULL");
        return (int)$stmt->fetchColumn();
    }

    /**
     * Fetch upcoming immunizations
     * 
     * @param int $limit
     * @return array
     */
    public static function getUpcomingSchedule(int $limit = 5): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT i.*, 
                   CONCAT(r.last_name, ', ', r.first_name) AS resident_name, 
                   m.name AS vaccine_name
            FROM immunizations i
            JOIN residents r ON i.resident_id = r.id
            JOIN medicines m ON i.vaccine_id = m.id
            WHERE i.status = 'Upcoming' AND i.next_schedule >= CURDATE() AND i.deleted_at IS NULL
            ORDER BY i.next_schedule ASC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Monthly immunization count trends
     * 
     * @return array
     */
    public static function getMonthlyCounts(): array {
        $db = Database::getConnection();
        $year = date('Y');
        
        $stmt = $db->prepare("
            SELECT MONTH(COALESCE(date_given, next_schedule)) as month, COUNT(*) as count 
            FROM immunizations 
            WHERE YEAR(COALESCE(date_given, next_schedule)) = :year AND deleted_at IS NULL
            GROUP BY MONTH(COALESCE(date_given, next_schedule))
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
