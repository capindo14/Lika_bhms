<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Consultation;
use App\Models\Resident;
use App\Models\Medicine;
use Exception;

class ConsultationController {
    /**
     * Display a list of consultations
     */
    public function index(): void {
        AuthMiddleware::handle();
        $residents = Resident::getAll('Active');
        $medicines = Medicine::getAll();
        $pageTitle = 'Medical Consultations';
        require_once VIEW_PATH . 'consultation/index.php';
    }

    /**
     * Get list of consultations in JSON format
     */
    public function apiList(): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');

        $consultations = Consultation::getAll();
        echo json_encode($consultations);
        exit;
    }

    /**
     * Get details of a single consultation in JSON
     */
    public function apiDetail(): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');

        $id = (int)($_GET['id'] ?? 0);
        $consultation = Consultation::getById($id);

        if (!$consultation) {
            echo json_encode(['status' => 'error', 'message' => 'Consultation record not found']);
            exit;
        }

        echo json_encode($consultation);
        exit;
    }

    /**
     * Get details of a single consultation in wrapper JSON format for views
     */
    public function detail_json(): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');

        $id = (int)($_GET['id'] ?? 0);
        $consultation = Consultation::getById($id);

        if (!$consultation) {
            echo json_encode(['success' => false, 'message' => 'Consultation record not found']);
            exit;
        }

        echo json_encode(['success' => true, 'data' => $consultation]);
        exit;
    }

    /**
     * Store a new consultation record (JSON POST)
     */
    public function apiStore(): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents("php://input"), true);
        
        $resident_id = isset($data['resident_id']) ? (int)$data['resident_id'] : 0;
        $symptoms = trim($data['symptoms'] ?? '');
        $diagnosis = trim($data['diagnosis'] ?? '');
        $treatment = trim($data['treatment'] ?? '');
        $consultation_date = trim($data['consultation_date'] ?? '');

        if (!$resident_id || $symptoms === '' || $diagnosis === '' || $treatment === '' || $consultation_date === '') {
            echo json_encode([
                "status" => "error",
                "message" => "Please fill in all required fields."
            ]);
            exit;
        }

        // Add user footprint
        $data['user_id'] = $_SESSION['user_id'];

        try {
            $insertedId = Consultation::create($data);
            
            db_log('CREATE_CONSULTATION_JS', "Logged consultation CON# {$insertedId} via JS CRUD");
            
            echo json_encode([
                "status" => "success",
                "message" => "Consultation logged successfully.",
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
     * Update an existing consultation record (JSON POST)
     */
    public function apiUpdate(): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents("php://input"), true);
        
        $id = isset($data['id']) ? (int)$data['id'] : 0;
        $resident_id = isset($data['resident_id']) ? (int)$data['resident_id'] : 0;
        $symptoms = trim($data['symptoms'] ?? '');
        $diagnosis = trim($data['diagnosis'] ?? '');
        $treatment = trim($data['treatment'] ?? '');
        $consultation_date = trim($data['consultation_date'] ?? '');

        if (!$id || !$resident_id || $symptoms === '' || $diagnosis === '' || $treatment === '' || $consultation_date === '') {
            echo json_encode([
                "status" => "error",
                "message" => "Please fill in all required fields."
            ]);
            exit;
        }

        $data['user_id'] = $_SESSION['user_id'];

        try {
            Consultation::update($id, $data);
            
            db_log('UPDATE_CONSULTATION_JS', "Updated consultation ID: {$id} via JS CRUD");
            
            echo json_encode([
                "status" => "success",
                "message" => "Consultation updated successfully."
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
     * Delete consultation record (JSON POST)
     */
    public function apiDelete(): void {
        AuthMiddleware::handle(['Admin', 'Health Worker']);
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents("php://input"), true);
        $id = isset($data['id']) ? (int)$data['id'] : 0;

        if (!$id) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid consultation ID."
            ]);
            exit;
        }

        $consultation = Consultation::getById($id);
        if (!$consultation) {
            echo json_encode([
                "status" => "error",
                "message" => "Consultation record not found."
            ]);
            exit;
        }

        if (Consultation::delete($id)) {
            db_log('DELETE_CONSULTATION_JS', "Deleted consultation: " . $consultation['consultation_no'] . " (ID: " . $id . ") via JS CRUD");
            echo json_encode([
                "status" => "success",
                "message" => "Consultation deleted successfully."
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Failed to delete consultation."
            ]);
        }
        exit;
    }
}

