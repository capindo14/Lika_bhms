<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Family;
use App\Models\Resident;
use Exception;

class FamilyController {
    /**
     * Display a listing of family profiles
     */
    public function index(): void {
        AuthMiddleware::handle();
        $pageTitle = 'Family Profiles';
        require_once VIEW_PATH . 'family/index.php';
    }

    /**
     * Get list of family profiles in JSON
     */
    public function apiList(): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');
        
        $families = Family::getAll();
        echo json_encode($families);
        exit;
    }

    /**
     * Get detail of a family profile in JSON
     */
    public function apiDetail(): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');
        
        $id = (int)($_GET['id'] ?? 0);
        $family = Family::getById($id);

        if (!$family) {
            echo json_encode(['status' => 'error', 'message' => 'Family profile not found']);
            exit;
        }

        $members = Family::getMembers($id);

        echo json_encode([
            'status' => 'success',
            'family' => $family,
            'members' => $members
        ]);
        exit;
    }

    /**
     * Store new family profile (JSON POST)
     */
    public function apiStore(): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents("php://input"), true);
        
        $head_id = isset($data['head_resident_id']) ? (int)$data['head_resident_id'] : 0;
        $address = trim($data['address'] ?? '');

        if (!$head_id || $address === '') {
            echo json_encode([
                "status" => "error",
                "message" => "Family Head and Complete Address are required."
            ]);
            exit;
        }

        $members = [];
        if (isset($data['members']) && is_array($data['members'])) {
            foreach ($data['members'] as $m) {
                $rId = (int)($m['resident_id'] ?? 0);
                $rel = trim($m['relationship'] ?? '');
                if ($rId > 0 && $rel !== '') {
                    $members[] = [
                        'resident_id' => $rId,
                        'relationship' => $rel
                    ];
                }
            }
        }

        try {
            $insertedId = Family::create($data, $members);
            
            db_log('CREATE_FAMILY_JS', "Created family profile FAM# {$insertedId} via JS CRUD");
            
            echo json_encode([
                "status" => "success",
                "message" => "Family profile created successfully.",
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
     * Update family profile (JSON POST)
     */
    public function apiUpdate(): void {
        AuthMiddleware::handle();
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents("php://input"), true);
        
        $id = isset($data['id']) ? (int)$data['id'] : 0;
        $head_id = isset($data['head_resident_id']) ? (int)$data['head_resident_id'] : 0;
        $address = trim($data['address'] ?? '');

        if (!$id || !$head_id || $address === '') {
            echo json_encode([
                "status" => "error",
                "message" => "ID, Family Head, and Complete Address are required."
            ]);
            exit;
        }

        $members = [];
        if (isset($data['members']) && is_array($data['members'])) {
            foreach ($data['members'] as $m) {
                $rId = (int)($m['resident_id'] ?? 0);
                $rel = trim($m['relationship'] ?? '');
                if ($rId > 0 && $rel !== '') {
                    $members[] = [
                        'resident_id' => $rId,
                        'relationship' => $rel
                    ];
                }
            }
        }

        try {
            Family::update($id, $data, $members);
            
            db_log('UPDATE_FAMILY_JS', "Updated family profile ID: {$id} via JS CRUD");
            
            echo json_encode([
                "status" => "success",
                "message" => "Family profile updated successfully."
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
     * Delete family profile (JSON POST)
     */
    public function apiDelete(): void {
        AuthMiddleware::handle(['Admin', 'Health Worker']);
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents("php://input"), true);
        $id = isset($data['id']) ? (int)$data['id'] : 0;

        if (!$id) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid family profile ID."
            ]);
            exit;
        }

        $family = Family::getById($id);
        if (!$family) {
            echo json_encode([
                "status" => "error",
                "message" => "Family profile not found."
            ]);
            exit;
        }

        if (Family::delete($id)) {
            db_log('DELETE_FAMILY_JS', "Deleted family profile: " . $family['family_no'] . " (ID: " . $id . ") via JS CRUD");
            echo json_encode([
                "status" => "success",
                "message" => "Family profile deleted successfully."
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Failed to delete family profile."
            ]);
        }
        exit;
    }
}
