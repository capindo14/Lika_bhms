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
        $db = Database::getConnection();
        
        // Fetch current stock
        $med = self::getById($id);
        if (!$med) throw new Exception("Medicine not found.");
        
        if ($med['stock_qty'] < $quantity) {
            throw new Exception("Insufficient stock quantity available. Available: " . $med['stock_qty']);
        }
        
        $stmt = $db->prepare("UPDATE medicines SET stock_qty = stock_qty - :qty WHERE id = :id");
        return $stmt->execute([':id' => $id, ':qty' => $quantity]);
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
}
