<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use Exception;

class Medicine {
    /**
     * Get all active inventory records
     * 
     * @return array
     */
    public static function getAll(): array {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM medicines WHERE deleted_at IS NULL ORDER BY category, name");
        return $stmt->fetchAll();
    }

    /**
     * Get list of medicines by category
     * 
     * @param string $category
     * @return array
     */
    public static function getByCategory(string $category): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM medicines WHERE category = :category AND deleted_at IS NULL ORDER BY name");
        $stmt->execute([':category' => $category]);
        return $stmt->fetchAll();
    }

    /**
     * Find medicine by primary key
     * 
     * @param int $id
     * @return array|null
     */
    public static function getById(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM medicines WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute([':id' => $id]);
        $med = $stmt->fetch();
        return $med ?: null;
    }

    /**
     * Generate dynamic code MED-0001
     * 
     * @return string
     */
    public static function generateCode(): string {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT COUNT(*) FROM medicines");
        $count = (int)$stmt->fetchColumn() + 1;
        return "MED-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Insert new stock item
     * 
     * @param array $data
     * @return int
     */
    public static function create(array $data): int {
        $db = Database::getConnection();
        
        $code = self::generateCode();
        
        $stmt = $db->prepare("
            INSERT INTO medicines (code, name, description, category, stock_qty, reorder_level)
            VALUES (:code, :name, :description, :category, :stock_qty, :reorder_level)
        ");

        $stmt->execute([
            ':code' => $code,
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
            ':category' => $data['category'],
            ':stock_qty' => (int)$data['stock_qty'],
            ':reorder_level' => (int)$data['reorder_level']
        ]);

        return (int)$db->lastInsertId();
    }

    /**
     * Update stock item
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public static function update(int $id, array $data): bool {
        $db = Database::getConnection();

        $stmt = $db->prepare("
            UPDATE medicines 
            SET name = :name, 
                description = :description, 
                category = :category, 
                stock_qty = :stock_qty, 
                reorder_level = :reorder_level
            WHERE id = :id AND deleted_at IS NULL
        ");

        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
            ':category' => $data['category'],
            ':stock_qty' => (int)$data['stock_qty'],
            ':reorder_level' => (int)$data['reorder_level']
        ]);
    }

    /**
     * Deduct stock level
     * 
     * @param int $id
     * @param int $quantity
     * @return bool
     */
    public static function deductStock(int $id, int $quantity): bool {
        if ($quantity <= 0) {
            throw new Exception("Quantity must be greater than zero.");
        }

        $db = Database::getConnection();

        $stmt = $db->prepare("
            UPDATE medicines
                        SET stock_qty = stock_qty - :deduct_qty
            WHERE id = :id
              AND deleted_at IS NULL
                            AND stock_qty >= :available_qty
        ");

        $stmt->execute([
            ':id' => $id,
                        ':deduct_qty' => $quantity,
                        ':available_qty' => $quantity
        ]);

        if ($stmt->rowCount() !== 1) {
            $current = self::getById($id);
            $available = $current['stock_qty'] ?? 0;
            throw new Exception("Insufficient stock quantity available. Available: " . $available);
        }

        return true;
    }

    /**
     * Add stock level
     * 
     * @param int $id
     * @param int $quantity
     * @return bool
     */
    public static function addStock(int $id, int $quantity): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE medicines SET stock_qty = stock_qty + :qty WHERE id = :id");
        return $stmt->execute([':id' => $id, ':qty' => $quantity]);
    }

    /**
     * Soft delete medicine record
     * 
     * @param int $id
     * @return bool
     */
    public static function delete(int $id): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE medicines SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Get list of items running low on stock
     * 
     * @return array
     */
    public static function getLowStockAlerts(): array {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT * FROM medicines 
            WHERE stock_qty <= reorder_level AND deleted_at IS NULL
            ORDER BY stock_qty ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Count low stock items
     * 
     * @return int
     */
    public static function countLowStock(): int {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT COUNT(*) FROM medicines WHERE stock_qty <= reorder_level AND deleted_at IS NULL");
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get all distribution records
     * 
     * @return array
     */
    public static function getDistributions(): array {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT md.*, m.name AS medicine_name, m.code AS medicine_code, m.category AS medicine_category,
                    CONCAT(r.last_name, ', ', r.first_name, ' ', COALESCE(r.middle_name, '')) AS resident_name, r.resident_id AS resident_code,
                    u.fullname AS worker_name
            FROM medicine_distributions md
            JOIN medicines m ON md.medicine_id = m.id
            JOIN residents r ON md.resident_id = r.id
            JOIN users u ON md.user_id = u.id
            WHERE md.deleted_at IS NULL
            ORDER BY md.distribution_date DESC, md.id DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get single distribution record by id
     * 
     * @param int $id
     * @return array|null
     */
    public static function getDistributionById(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT md.*, m.name AS medicine_name, m.code AS medicine_code, m.category AS medicine_category,
                   CONCAT(r.last_name, ', ', r.first_name, ' ', COALESCE(r.middle_name, '')) AS resident_name, r.resident_id AS resident_code,
                   u.fullname AS worker_name
            FROM medicine_distributions md
            JOIN medicines m ON md.medicine_id = m.id
            JOIN residents r ON md.resident_id = r.id
            JOIN users u ON md.user_id = u.id
            WHERE md.id = :id AND md.deleted_at IS NULL
        ");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Log a new distribution transaction (with transaction tracking)
     * 
     * @param int $medicineId
     * @param int $residentId
     * @param int $quantity
     * @param string $date
     * @param int $userId
     * @return int
     */
    public static function logDistribution(int $medicineId, int $residentId, int $quantity, string $date, int $userId): int {
        $db = Database::getConnection();
        try {
            $db->beginTransaction();
            
            // Deduct stock first
            self::deductStock($medicineId, $quantity);
            
            // Insert log
            $stmt = $db->prepare("
                INSERT INTO medicine_distributions (medicine_id, resident_id, quantity, distribution_date, user_id)
                VALUES (:medicine_id, :resident_id, :quantity, :distribution_date, :user_id)
            ");
            $stmt->execute([
                ':medicine_id' => $medicineId,
                ':resident_id' => $residentId,
                ':quantity' => $quantity,
                ':distribution_date' => $date ?: date('Y-m-d'),
                ':user_id' => $userId
            ]);
            
            $id = (int)$db->lastInsertId();
            $db->commit();
            return $id;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Delete distribution log and refund stock
     * 
     * @param int $id
     * @return bool
     */
    public static function deleteDistribution(int $id): bool {
        $db = Database::getConnection();
        try {
            $db->beginTransaction();
            
            // Get record to know quantity & medicine
            $stmt = $db->prepare("SELECT * FROM medicine_distributions WHERE id = :id AND deleted_at IS NULL");
            $stmt->execute([':id' => $id]);
            $dist = $stmt->fetch();
            if (!$dist) throw new Exception("Distribution record not found.");
            
            // Refund stock
            self::addStock((int)$dist['medicine_id'], (int)$dist['quantity']);
            
            // Soft delete distribution record
            $stmtDel = $db->prepare("UPDATE medicine_distributions SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id");
            $stmtDel->execute([':id' => $id]);
            
            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Update distribution log record and recalculate stocks
     */
    public static function updateDistribution(int $id, int $medicineId, int $residentId, int $quantity, string $date): bool {
        $db = Database::getConnection();
        try {
            $db->beginTransaction();
            
            // 1. Get original record details
            $stmtOrig = $db->prepare("SELECT * FROM medicine_distributions WHERE id = :id AND deleted_at IS NULL");
            $stmtOrig->execute([':id' => $id]);
            $orig = $stmtOrig->fetch();
            if (!$orig) throw new Exception("Original distribution record not found.");
            
            // 2. Refund the original quantity back to stock
            self::addStock((int)$orig['medicine_id'], (int)$orig['quantity']);
            
            // 3. Deduct the new quantity from stock (and check limits)
            self::deductStock($medicineId, $quantity);
            
            // 4. Update distribution details
            $stmtUpd = $db->prepare("
                UPDATE medicine_distributions 
                SET medicine_id = :medicine_id, 
                    resident_id = :resident_id, 
                    quantity = :quantity, 
                    distribution_date = :distribution_date
                WHERE id = :id
            ");
            $stmtUpd->execute([
                ':id' => $id,
                ':medicine_id' => $medicineId,
                ':resident_id' => $residentId,
                ':quantity' => $quantity,
                ':distribution_date' => $date
            ]);
            
            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
