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

        try {
            $data = read_json_input();
        } catch (InvalidArgumentException $e) {
            json_response(false, $e->getMessage(), [], 400);
        }

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

            // Fetch created family to include contextual info
            $created = Family::getById($insertedId);
            $summary = $created ? ($created['family_no'] . ' (' . ($created['head_name'] ?? '') . ')') : 'Family profile';

            db_log('CREATE_FAMILY_JS', "Created family profile FAM# {$insertedId} via JS CRUD");

            echo json_encode([
                "status" => "success",
                "message" => "{$summary} created successfully.",
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

        try {
            $data = read_json_input();
        } catch (InvalidArgumentException $e) {
            json_response(false, $e->getMessage(), [], 400);
        }

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
            
            $updated = Family::getById($id);
            $summary = $updated ? ($updated['family_no'] . ' (' . ($updated['head_name'] ?? '') . ')') : 'Family profile';

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
     * Delete family profile (JSON POST)
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
            $summary = $family['family_no'] . ' (' . ($family['head_name'] ?? '') . ')';
            echo json_encode([
                "status" => "success",
                "message" => "{$summary} deleted successfully."
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
