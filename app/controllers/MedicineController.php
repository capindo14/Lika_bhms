<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Medicine;
use App\Services\ValidationService;
use Exception;

class MedicineController {
    /**
     * Display a listing of medicine inventory and distributions
     */
    public function index(): void {
        AuthMiddleware::handle();

        // 1. Fetch Inventory Items
        $medicines = Medicine::getAll();
        
        // 2. Fetch Low Stock Alerts
        $lowStockAlerts = Medicine::getLowStockAlerts();

        // 3. Fetch Recent Distributions
        $db = \App\Config\Database::getConnection();
        $stmtDist = $db->query("
            SELECT md.*, 
                   m.name AS medicine_name, 
                   m.code AS medicine_code,
                   CONCAT(r.last_name, ', ', r.first_name) AS resident_name,
                   u.fullname AS worker_name
            FROM medicine_distributions md
            JOIN medicines m ON md.medicine_id = m.id
            JOIN residents r ON md.resident_id = r.id
            JOIN users u ON md.user_id = u.id
            WHERE md.deleted_at IS NULL
            ORDER BY md.distribution_date DESC, md.id DESC
        ");
        $distributions = $stmtDist->fetchAll();

        $pageTitle = 'Medicine Inventory & Stocks';
        require_once VIEW_PATH . 'medicine/index.php';
    }

    /**
     * Display creation form
     */
    public function create(): void {
        AuthMiddleware::handle();
        $pageTitle = 'Add Inventory Item';
        require_once VIEW_PATH . 'medicine/create.php';
    }

    /**
     * Store new inventory item
     */
    public function store(): void {
        AuthMiddleware::handle();

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('error', 'CSRF verification failed. Please try again.');
            redirect('medicine/create');
        }

        $validator = new ValidationService();
        $data = sanitize_array($_POST);

        $rules = [
            'name' => 'required|min:2',
            'category' => 'required',
            'stock_qty' => 'required|numeric',
            'reorder_level' => 'required|numeric'
        ];

        if (!$validator->validate($data, $rules)) {
            flash('error', $validator->getFirstError());
            redirect('medicine/create');
        }

        try {
            $insertedId = Medicine::create($data);
            
            // Log action
            db_log('CREATE_MEDICINE', "Added inventory stock item: " . $data['name'] . " (ID: " . $insertedId . ")");
            
            flash('success', 'Medicine/vaccine inventory item created.');
            redirect('medicine');
        } catch (Exception $e) {
            flash('error', $e->getMessage());
            redirect('medicine/create');
        }
    }

    /**
     * Display edit form
     */
    public function edit(): void {
        AuthMiddleware::handle();

        $id = (int)($_GET['id'] ?? 0);
        $medicine = Medicine::getById($id);

        if (!$medicine) {
            flash('error', 'Inventory record not found.');
            redirect('medicine');
        }

        $pageTitle = 'Edit Inventory Details - ' . $medicine['code'];
        require_once VIEW_PATH . 'medicine/edit.php';
    }

    /**
     * Update inventory details
     */
    public function update(): void {
        AuthMiddleware::handle();

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            flash('error', 'Invalid inventory record.');
            redirect('medicine');
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('error', 'CSRF verification failed. Please try again.');
            redirect("medicine");
        }

        $validator = new ValidationService();
        $data = sanitize_array($_POST);

        $rules = [
            'name' => 'required|min:2',
            'category' => 'required',
            'stock_qty' => 'required|numeric',
            'reorder_level' => 'required|numeric'
        ];

        if (!$validator->validate($data, $rules)) {
            flash('error', $validator->getFirstError());
            redirect("medicine");
        }

        try {
            Medicine::update($id, $data);
            
            // Log update
            db_log('UPDATE_MEDICINE', "Updated inventory details for item ID: " . $id);
            
            flash('success', 'Inventory details updated successfully.');
            redirect('medicine');
        } catch (Exception $e) {
            flash('error', $e->getMessage());
            redirect("medicine");
        }
    }

    /**
     * Restock action (quick update via post)
     */
    public function restock(): void {
        AuthMiddleware::handle(['Admin', 'Health Worker']);

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('error', 'CSRF verification failed. Please try again.');
            redirect('medicine');
        }

        $id = (int)($_POST['medicine_id'] ?? 0);
        $qty = (int)($_POST['restock_qty'] ?? 0);

        if (!$id || $qty <= 0) {
            flash('error', 'Invalid restock quantity parameters.');
            redirect('medicine');
        }

        try {
            Medicine::addStock($id, $qty);
            $med = Medicine::getById($id);
            
            // Log restock
            db_log('RESTOCK_MEDICINE', "Restocked item " . $med['name'] . " with quantity: " . $qty);
            
            flash('success', "Stock incremented successfully. New stock: {$med['stock_qty']}");
        } catch (Exception $e) {
            flash('error', $e->getMessage());
        }

        redirect('medicine');
    }

    /**
     * Delete inventory record
     */
    public function delete(): void {
        AuthMiddleware::handle(['Admin', 'Health Worker']);

        $id = (int)($_GET['id'] ?? 0);
        $medicine = Medicine::getById($id);

        if (!$medicine) {
            flash('error', 'Inventory record not found.');
            redirect('medicine');
        }

        if (Medicine::delete($id)) {
            // Log deletion
            db_log('DELETE_MEDICINE', "Deleted medicine item: " . $medicine['name'] . " (ID: " . $id . ")");
            
            flash('success', 'Inventory item deleted successfully.');
        } else {
            flash('error', 'Failed to delete inventory item.');
        }

        redirect('medicine');
    }

    /**
     * Get detail JSON for modal edit
     */
    public function detail_json(): void {
        AuthMiddleware::handle();

        header('Content-Type: application/json');
        $id = (int)($_GET['id'] ?? 0);
        $medicine = Medicine::getById($id);

        if (!$medicine) {
            echo json_encode(['success' => false, 'message' => 'Inventory record not found']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'data' => $medicine
        ]);
        exit;
    }
}
