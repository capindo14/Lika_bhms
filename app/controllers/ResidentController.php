<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Resident;
use Exception;

class ResidentController {
    /**
     * Display a listing of residents
     */
    public function index(): void {
        AuthMiddleware::handle();
        $pageTitle = 'Resident Directory';
        require_once VIEW_PATH . 'residents/index.php';
    }

    /**
     * Get detail JSON for modal view/edit
     */
    public function detail_json(): void {
        AuthMiddleware::handle();

        header('Content-Type: application/json');
        $id = (int)($_GET['id'] ?? 0);
        $resident = Resident::getById($id);

        if (!$resident) {
            echo json_encode(['success' => false, 'message' => 'Resident not found']);
            exit;
        }

        // Fetch Consultation History
        $db = \App\Config\Database::getConnection();
        $stmtCon = $db->prepare("
            SELECT c.*, u.fullname AS worker_name, m.name AS medicine_name 
            FROM consultations c
            JOIN users u ON c.user_id = u.id
            LEFT JOIN medicines m ON c.medicine_id = m.id
            WHERE c.resident_id = :id AND c.deleted_at IS NULL 
            ORDER BY c.consultation_date DESC
        ");
        $stmtCon->execute([':id' => $id]);
        $consultations = $stmtCon->fetchAll();

        // Fetch Immunization History
        $stmtImm = $db->prepare("
            SELECT i.*, u.fullname AS worker_name, m.name AS vaccine_name 
            FROM immunizations i
            JOIN users u ON i.user_id = u.id
            JOIN medicines m ON i.vaccine_id = m.id
            WHERE i.resident_id = :id AND i.deleted_at IS NULL 
            ORDER BY i.date_given DESC, i.next_schedule ASC
        ");
        $stmtImm->execute([':id' => $id]);
        $immunizations = $stmtImm->fetchAll();

        echo json_encode([
            'success' => true,
            'data' => [
                'resident' => $resident,
                'consultations' => $consultations,
                'immunizations' => $immunizations
            ]
        ]);
        exit;
    }


    /**
     * Get list of residents in JSON format
     */
    public function apiList(): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');
        
        $status = $_GET['status'] ?? 'Active';
        if (!in_array($status, ['Active', 'Archived', 'Pregnant', 'Infant'])) {
            $status = 'Active';
        }

        $residents = Resident::getAll($status);
        echo json_encode($residents);
        exit;
    }

    /**
     * Get detail of a resident in JSON format
     */
    public function apiDetail(): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');
        
        $id = (int)($_GET['id'] ?? 0);
        $resident = Resident::getById($id);

        if (!$resident) {
            echo json_encode(['status' => 'error', 'message' => 'Resident not found']);
            exit;
        }

        echo json_encode($resident);
        exit;
    }

    /**
     * Register a new resident (JSON POST)
     */
    public function apiStore(): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');

        try {
            $data = read_json_input();
        } catch (InvalidArgumentException $e) {
            json_response(false, $e->getMessage(), [], 400);
        }

        $first_name = trim($data['first_name'] ?? '');
        $last_name = trim($data['last_name'] ?? '');
        $gender = trim($data['gender'] ?? '');
        $birthdate = trim($data['birthdate'] ?? '');
        $civil_status = trim($data['civil_status'] ?? '');
        $address = trim($data['address'] ?? '');

        if ($first_name === '' || $last_name === '' || $gender === '' || $birthdate === '' || $civil_status === '' || $address === '') {
            echo json_encode([
                "status" => "error",
                "message" => "Please fill in all required fields."
            ]);
            exit;
        }

        try {
            $insertedId = Resident::create($data);

            db_log('CREATE_RESIDENT_JS', "Registered resident via JS CRUD: " . $last_name . ", " . $first_name . " (ID: " . $insertedId . ")");

            $created = Resident::getById($insertedId);
            $fullName = $created ? ($created['last_name'] . ', ' . $created['first_name']) : ($last_name . ', ' . $first_name);

            echo json_encode([
                "status" => "success",
                "message" => "Resident {$fullName} registered successfully.",
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
     * Update resident details (JSON POST)
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
        $first_name = trim($data['first_name'] ?? '');
        $last_name = trim($data['last_name'] ?? '');
        $gender = trim($data['gender'] ?? '');
        $birthdate = trim($data['birthdate'] ?? '');
        $civil_status = trim($data['civil_status'] ?? '');
        $address = trim($data['address'] ?? '');

        if (!$id || $first_name === '' || $last_name === '' || $gender === '' || $birthdate === '' || $civil_status === '' || $address === '') {
            echo json_encode([
                "status" => "error",
                "message" => "Please fill in all required fields."
            ]);
            exit;
        }

        try {
            Resident::update($id, $data);

            db_log('UPDATE_RESIDENT_JS', "Updated resident via JS CRUD: ID " . $id);

            $updated = Resident::getById($id);
            $fullName = $updated ? ($updated['last_name'] . ', ' . $updated['first_name']) : 'Resident';

            echo json_encode([
                "status" => "success",
                "message" => "Resident {$fullName} updated successfully."
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
     * Archive/Restore a resident (JSON POST)
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
        $action = trim($data['action'] ?? 'Archive');

        if (!$id) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid resident ID."
            ]);
            exit;
        }

        // Find resident in active or archived
        $resident = Resident::getById($id);
        if (!$resident) {
            $db = \App\Config\Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM residents WHERE id = :id AND deleted_at IS NULL");
            $stmt->execute([':id' => $id]);
            $resident = $stmt->fetch();
        }

        if (!$resident) {
            echo json_encode([
                "status" => "error",
                "message" => "Resident record not found."
            ]);
            exit;
        }

        try {
            if ($action === 'Restore') {
                Resident::setStatus($id, 'Active');
                db_log('RESTORE_RESIDENT_JS', "Restored resident profile: " . $resident['last_name'] . " (ID: " . $id . ")");
                $name = $resident['last_name'] . ', ' . $resident['first_name'];
                echo json_encode([
                    "status" => "success",
                    "message" => "Resident profile for {$name} restored successfully."
                ]);
            } else {
                Resident::setStatus($id, 'Archived');
                db_log('ARCHIVE_RESIDENT_JS', "Archived resident profile: " . $resident['last_name'] . " (ID: " . $id . ")");
                $name = $resident['last_name'] . ', ' . $resident['first_name'];
                echo json_encode([
                    "status" => "success",
                    "message" => "Resident profile for {$name} archived successfully."
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
        exit;
    }
}
