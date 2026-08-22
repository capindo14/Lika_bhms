<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Immunization;
use App\Models\Resident;
use App\Models\Medicine;
use Exception;

class ImmunizationController {
    /**
     * Display a listing of immunization records
     */
    public function index(): void {
        AuthMiddleware::handle();
        $residents = Resident::getAll('Active');
        $vaccines = Medicine::getByCategory('Vaccine');
        $pageTitle = 'Immunization Schedule & Logs';
        require_once VIEW_PATH . 'immunization/index.php';
    }

    /**
     * Get list of immunization records in JSON
     */
    public function apiList(): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');

        $records = Immunization::getAll();
        echo json_encode($records);
        exit;
    }

    /**
     * Get details of a single record in JSON
     */
    public function apiDetail(): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');

        $id = (int)($_GET['id'] ?? 0);
        $record = Immunization::getById($id);

        if (!$record) {
            echo json_encode(['status' => 'error', 'message' => 'Immunization record not found']);
            exit;
        }

        echo json_encode($record);
        exit;
    }

    /**
     * Store new immunization record (JSON POST)
     */
    public function apiStore(): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents("php://input"), true);
        
        $resident_id = isset($data['resident_id']) ? (int)$data['resident_id'] : 0;
        $vaccine_id = isset($data['vaccine_id']) ? (int)$data['vaccine_id'] : 0;
        $dose = trim($data['dose'] ?? '');
        $status = trim($data['status'] ?? '');
        $date_given = trim($data['date_given'] ?? '');
        $next_schedule = trim($data['next_schedule'] ?? '');

        if (!$resident_id || !$vaccine_id || $dose === '' || $status === '') {
            echo json_encode([
                "status" => "error",
                "message" => "Please fill in all required fields."
            ]);
            exit;
        }

        if ($status === 'Completed' && $date_given === '') {
            echo json_encode([
                "status" => "error",
                "message" => "Completed status requires Date Given to be specified."
            ]);
            exit;
        }

        if ($status === 'Upcoming' && $next_schedule === '') {
            echo json_encode([
                "status" => "error",
                "message" => "Upcoming status requires Next Schedule Date to be specified."
            ]);
            exit;
        }

        $data['user_id'] = $_SESSION['user_id'];

        try {
            $insertedId = Immunization::create($data);
            
            db_log('CREATE_IMMUNIZATION_JS', "Logged immunization record ID: {$insertedId} via JS CRUD");
            
            echo json_encode([
                "status" => "success",
                "message" => "Immunization record saved successfully.",
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
     * Update immunization record (JSON POST)
     */
    public function apiUpdate(): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents("php://input"), true);
        
        $id = isset($data['id']) ? (int)$data['id'] : 0;
        $resident_id = isset($data['resident_id']) ? (int)$data['resident_id'] : 0;
        $vaccine_id = isset($data['vaccine_id']) ? (int)$data['vaccine_id'] : 0;
        $dose = trim($data['dose'] ?? '');
        $status = trim($data['status'] ?? '');
        $date_given = trim($data['date_given'] ?? '');
        $next_schedule = trim($data['next_schedule'] ?? '');

        if (!$id || !$resident_id || !$vaccine_id || $dose === '' || $status === '') {
            echo json_encode([
                "status" => "error",
                "message" => "Please fill in all required fields."
            ]);
            exit;
        }

        if ($status === 'Completed' && $date_given === '') {
            echo json_encode([
                "status" => "error",
                "message" => "Completed status requires Date Given to be specified."
            ]);
            exit;
        }

        if ($status === 'Upcoming' && $next_schedule === '') {
            echo json_encode([
                "status" => "error",
                "message" => "Upcoming status requires Next Schedule Date to be specified."
            ]);
            exit;
        }

        $data['user_id'] = $_SESSION['user_id'];

        try {
            Immunization::update($id, $data);
            
            db_log('UPDATE_IMMUNIZATION_JS', "Updated immunization record ID: {$id} via JS CRUD");
            
            echo json_encode([
                "status" => "success",
                "message" => "Immunization details updated successfully."
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
     * Delete immunization record (JSON POST)
     */
    public function apiDelete(): void {
        AuthMiddleware::handle(['Admin', 'Health Worker']);
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents("php://input"), true);
        $id = isset($data['id']) ? (int)$data['id'] : 0;

        if (!$id) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid immunization record ID."
            ]);
            exit;
        }

        $record = Immunization::getById($id);
        if (!$record) {
            echo json_encode([
                "status" => "error",
                "message" => "Immunization record not found."
            ]);
            exit;
        }

        if (Immunization::delete($id)) {
            db_log('DELETE_IMMUNIZATION_JS', "Deleted immunization record ID: {$id} via JS CRUD");
            echo json_encode([
                "status" => "success",
                "message" => "Immunization record deleted successfully."
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Failed to delete immunization record."
            ]);
        }
        exit;
    }
}
