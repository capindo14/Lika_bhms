<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Medicine;
use Exception;

class MedicineController {
    /**
     * Display a listing of medicine inventory
     */
    public function index(): void {
        AuthMiddleware::handle();
        $pageTitle = 'Medicine & Family Planning';
        $residents = \App\Models\Resident::getAll(); // Fetch active residents for the distribution form dropdown
        require_once VIEW_PATH . 'medicine/index.php';
    }

    /**
     * Get all active inventory records in JSON
     */
    public function apiList(): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');
        $medicines = Medicine::getAll();
        echo json_encode($medicines);
        exit;
    }

    /**
     * Get detail of a single record in JSON
     */
    public function apiDetail(): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');
        
        $id = (int)($_GET['id'] ?? 0);
        $medicine = Medicine::getById($id);

        if (!$medicine) {
            echo json_encode(['status' => 'error', 'message' => 'Medicine record not found']);
            exit;
        }

        echo json_encode($medicine);
        exit;
    }

    /**
     * Insert a new record (JSON POST request)
     */
    public function apiStore(): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');

        try {
            $data = read_json_input();
        } catch (InvalidArgumentException $e) {
            json_response(false, $e->getMessage(), [], 400);
        }

        $name = trim($data['name'] ?? '');
        $category = trim($data['category'] ?? '');
        $stock_qty = isset($data['stock_qty']) ? (int)$data['stock_qty'] : 0;
        $reorder_level = isset($data['reorder_level']) ? (int)$data['reorder_level'] : 10;
        $description = trim($data['description'] ?? '');

        if ($name === '' || $category === '') {
            echo json_encode([
                "status" => "error",
                "message" => "Name and category are required."
            ]);
            exit;
        }

        try {
            $insertedId = Medicine::create([
                'name' => $name,
                'category' => $category,
                'stock_qty' => $stock_qty,
                'reorder_level' => $reorder_level,
                'description' => $description
            ]);
            
            db_log('CREATE_MEDICINE_JS', "Added medicine via JS CRUD: " . $name . " (ID: " . $insertedId . ")");
            
            $med = Medicine::getById($insertedId);
            $summary = $med ? ($med['name'] . ' (ID: ' . $insertedId . ')') : 'Medicine item';

            echo json_encode([
                "status" => "success",
                "message" => "{$summary} added successfully.",
                "id" => $insertedId
            ]);
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Update an existing record (JSON POST request)
     */
    public function apiUpdate(): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');

        try {
            $data = read_json_input();
        } catch (InvalidArgumentException $e) {
            json_response(false, $e->getMessage(), [], 400);
        }

        $id = isset($data['id']) ? (int)$data['id'] : 0;
        $name = trim($data['name'] ?? '');
        $category = trim($data['category'] ?? '');
        $stock_qty = isset($data['stock_qty']) ? (int)$data['stock_qty'] : 0;
        $reorder_level = isset($data['reorder_level']) ? (int)$data['reorder_level'] : 10;
        $description = trim($data['description'] ?? '');

        if (!$id || $name === '' || $category === '') {
            echo json_encode([
                "status" => "error",
                "message" => "ID, name, and category are required."
            ]);
            exit;
        }

        try {
            Medicine::update($id, [
                'name' => $name,
                'category' => $category,
                'stock_qty' => $stock_qty,
                'reorder_level' => $reorder_level,
                'description' => $description
            ]);
            
            db_log('UPDATE_MEDICINE_JS', "Updated medicine details via JS CRUD for item ID: " . $id);
            
            $med = Medicine::getById($id);
            $summary = $med ? ($med['name'] . ' (ID: ' . $id . ')') : 'Medicine item';

            echo json_encode([
                "status" => "success",
                "message" => "{$summary} updated successfully."
            ]);
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Soft-delete a record (JSON POST request)
     */
    public function apiDelete(): void {
        AuthMiddleware::handle(['Admin', 'Health Worker']);
        header('Content-Type: application/json');

        try {
            $data = read_json_input();
        } catch (InvalidArgumentException $e) {
            json_response(false, $e->getMessage(), [], 400);
        }

        $id = isset($data['id']) ? (int)$data['id'] : 0;

        if (!$id) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid record ID."
            ]);
            exit;
        }

        $medicine = Medicine::getById($id);
        if (!$medicine) {
            echo json_encode([
                "status" => "error",
                "message" => "Inventory record not found."
            ]);
            exit;
        }

        if (Medicine::delete($id)) {
            db_log('DELETE_MEDICINE_JS', "Deleted medicine item via JS CRUD: " . $medicine['name'] . " (ID: " . $id . ")");
            $summary = $medicine['name'] . ' (ID: ' . $id . ')';
            echo json_encode([
                "status" => "success",
                "message" => "{$summary} deleted successfully."
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Failed to delete medicine."
            ]);
        }
        exit;
    }

    /**
     * Restock an item (JSON POST request)
     */
    public function apiRestock(): void {
        AuthMiddleware::handle(['Admin', 'Health Worker']);
        header('Content-Type: application/json');

        try {
            $data = read_json_input();
        } catch (InvalidArgumentException $e) {
            json_response(false, $e->getMessage(), [], 400);
        }

        $id = isset($data['medicine_id']) ? (int)$data['medicine_id'] : 0;
        $qty = isset($data['restock_qty']) ? (int)$data['restock_qty'] : 0;

        if (!$id || $qty <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid restock quantity parameters."
            ]);
            exit;
        }

        try {
            Medicine::addStock($id, $qty);
            $med = Medicine::getById($id);
            db_log('RESTOCK_MEDICINE_JS', "Restocked item via JS CRUD: " . $med['name'] . " with quantity: " . $qty);
            
            $summary = $med ? ($med['name'] . ' (ID: ' . $id . ')') : 'Medicine item';
            echo json_encode([
                "status" => "success",
                "message" => "{$summary} restocked successfully. New stock: {$med['stock_qty']}",
                "new_stock" => $med['stock_qty']
            ]);
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Get list of direct distributions in JSON
     */
    public function distribution_list(): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');
        $distributions = Medicine::getDistributions();
        echo json_encode($distributions);
        exit;
    }

    /**
     * Create a new distribution record (JSON POST request)
     */
    public function distribute(): void {
        AuthMiddleware::handle(['Admin', 'Health Worker']);
        header('Content-Type: application/json');

        try {
            $data = read_json_input();
        } catch (InvalidArgumentException $e) {
            json_response(false, $e->getMessage(), [], 400);
        }

        $medicine_id = isset($data['medicine_id']) ? (int)$data['medicine_id'] : 0;
        $resident_id = isset($data['resident_id']) ? (int)$data['resident_id'] : 0;
        $quantity = isset($data['quantity']) ? (int)$data['quantity'] : 0;
        $distribution_date = trim($data['distribution_date'] ?? '');
        $user = get_logged_in_user();

        if (!$medicine_id || !$resident_id || !$quantity) {
            echo json_encode([
                "status" => "error",
                "message" => "Medicine, recipient, and quantity are required."
            ]);
            exit;
        }

        try {
            $insertedId = Medicine::logDistribution($medicine_id, $resident_id, $quantity, $distribution_date, $user['id']);
            db_log('LOG_DISTRIBUTION', "Logged medicine distribution. ID: " . $insertedId);

            $med = Medicine::getById($medicine_id);
            $resident = \App\Models\Resident::getById($resident_id);
            $summaryMed = $med ? $med['name'] : 'Medicine';
            $summaryRes = $resident ? ($resident['last_name'] . ', ' . $resident['first_name']) : 'Resident';

            echo json_encode([
                "status" => "success",
                "message" => "Distribution of {$summaryMed} to {$summaryRes} logged successfully.",
                "distribution_id" => $insertedId
            ]);
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Get single distribution detail with resident family profile relationship
     */
    public function distribution_detail(): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');
        
        $id = (int)($_GET['id'] ?? 0);
        $dist = Medicine::getDistributionById($id);
        
        if (!$dist) {
            echo json_encode(['success' => false, 'message' => 'Distribution log not found']);
            exit;
        }
        
        $db = \App\Config\Database::getConnection();
        
        // First check if resident is head of a family
        $stmtHead = $db->prepare("
            SELECT f.id AS family_id, f.family_no, 
                   CONCAT(r.last_name, ', ', r.first_name) AS head_name,
                   'Family Head' AS relationship
            FROM families f
            JOIN residents r ON f.head_resident_id = r.id
            WHERE f.head_resident_id = :resident_id AND f.deleted_at IS NULL
        ");
        $stmtHead->execute([':resident_id' => $dist['resident_id']]);
        $familyInfo = $stmtHead->fetch();
        
        // If not head, check if resident is member of a family
        if (!$familyInfo) {
            $stmtMember = $db->prepare("
                SELECT f.id AS family_id, f.family_no, 
                       CONCAT(r_head.last_name, ', ', r_head.first_name) AS head_name,
                       fm.relationship_to_head AS relationship
                FROM family_members fm
                JOIN families f ON fm.family_id = f.id
                JOIN residents r_head ON f.head_resident_id = r_head.id
                WHERE fm.resident_id = :resident_id AND f.deleted_at IS NULL
            ");
            $stmtMember->execute([':resident_id' => $dist['resident_id']]);
            $familyInfo = $stmtMember->fetch();
        }
        
        echo json_encode([
            'success' => true,
            'distribution' => $dist,
            'family' => $familyInfo ?: null
        ]);
        exit;
    }

    /**
     * Delete distribution record (JSON POST request)
     */
    public function delete_distribution(): void {
        AuthMiddleware::handle(['Admin', 'Health Worker']);
        header('Content-Type: application/json');

        try {
            $data = read_json_input();
        } catch (InvalidArgumentException $e) {
            json_response(false, $e->getMessage(), [], 400);
        }

        $id = isset($data['id']) ? (int)$data['id'] : 0;

        if (!$id) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid transaction ID."
            ]);
            exit;
        }

        try {
            Medicine::deleteDistribution($id);
            $dist = Medicine::getDistributionById($id);
            db_log('DELETE_DISTRIBUTION', "Cancelled/deleted distribution record ID: " . $id);
            $medName = $dist ? ($dist['medicine_name'] ?? $dist['name'] ?? 'Medicine') : 'Distribution';
            $residentName = $dist ? ($dist['resident_name'] ?? 'Resident') : 'Resident';

            echo json_encode([
                "status" => "success",
                "message" => "Distribution of {$medName} for {$residentName} cancelled successfully."
            ]);
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Update distribution record (JSON POST request)
     */
    public function update_distribution(): void {
        AuthMiddleware::handle(['Admin', 'Health Worker']);
        header('Content-Type: application/json');

        try {
            $data = read_json_input();
        } catch (InvalidArgumentException $e) {
            json_response(false, $e->getMessage(), [], 400);
        }

        $id = isset($data['id']) ? (int)$data['id'] : 0;
        $medicine_id = isset($data['medicine_id']) ? (int)$data['medicine_id'] : 0;
        $resident_id = isset($data['resident_id']) ? (int)$data['resident_id'] : 0;
        $quantity = isset($data['quantity']) ? (int)$data['quantity'] : 0;
        $distribution_date = trim($data['distribution_date'] ?? '');

        if (!$id || !$medicine_id || !$resident_id || !$quantity || $distribution_date === '') {
            echo json_encode([
                "status" => "error",
                "message" => "All fields are required."
            ]);
            exit;
        }

        try {
            Medicine::updateDistribution($id, $medicine_id, $resident_id, $quantity, $distribution_date);
            db_log('UPDATE_DISTRIBUTION', "Updated distribution record ID: " . $id);
            
            $updatedDist = Medicine::getDistributionById($id);
            $medName = $updatedDist ? ($updatedDist['medicine_name'] ?? $updatedDist['name'] ?? 'Medicine') : 'Distribution';
            $residentName = $updatedDist ? ($updatedDist['resident_name'] ?? 'Resident') : 'Resident';

            echo json_encode([
                "status" => "success",
                "message" => "Distribution of {$medName} for {$residentName} updated successfully."
            ]);
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
        exit;
    }
}
