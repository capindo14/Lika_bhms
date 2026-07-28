<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Family;
use App\Models\Resident;
use App\Services\ValidationService;
use Exception;

class FamilyController {
    /**
     * Display a listing of family profiles
     */
    public function index(): void {
        AuthMiddleware::handle();

        $families = Family::getAll();
        $residents = Resident::getAll('Active');
        $pageTitle = 'Family Profiles';
        
        require_once VIEW_PATH . 'family/index.php';
    }

    /**
     * Display the family creation form
     */
    public function create(): void {
        AuthMiddleware::handle();

        // Fetch residents who can be family heads (active)
        $residents = Resident::getAll('Active');
        
        $pageTitle = 'Create Family Profile';
        require_once VIEW_PATH . 'family/create.php';
    }

    /**
     * Store a newly created family profile
     */
    public function store(): void {
        AuthMiddleware::handle();

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('error', 'CSRF verification failed. Please try again.');
            redirect('family/create');
        }

        $validator = new ValidationService();
        $data = sanitize_array($_POST);

        $rules = [
            'head_resident_id' => 'required|numeric',
            'address' => 'required'
        ];

        if (!$validator->validate($data, $rules)) {
            flash('error', $validator->getFirstError());
            redirect('family/create');
        }

        // Process Members
        $members = [];
        if (isset($_POST['member_resident_id']) && is_array($_POST['member_resident_id'])) {
            foreach ($_POST['member_resident_id'] as $index => $rId) {
                $rId = (int)$rId;
                $rel = sanitize_input($_POST['member_relationship'][$index] ?? '');
                
                if (!empty($rId) && !empty($rel)) {
                    $members[] = [
                        'resident_id' => $rId,
                        'relationship' => $rel
                    ];
                }
            }
        }

        try {
            $insertedId = Family::create($data, $members);
            
            // Log action
            db_log('CREATE_FAMILY', "Created family profile FAM# {$insertedId} with head ID: " . $data['head_resident_id']);
            
            flash('success', 'Family profile created successfully.');
            redirect('family');
        } catch (Exception $e) {
            flash('error', $e->getMessage());
            redirect('family/create');
        }
    }

    /**
     * Display the family profile edit form
     */
    public function edit(): void {
        AuthMiddleware::handle();

        $id = (int)($_GET['id'] ?? 0);
        $family = Family::getById($id);

        if (!$family) {
            flash('error', 'Family profile not found.');
            redirect('family');
        }

        $members = Family::getMembers($id);
        $residents = Resident::getAll('Active');
        
        $pageTitle = 'Edit Family Profile - ' . $family['family_no'];
        require_once VIEW_PATH . 'family/edit.php';
    }

    /**
     * Update family profile details
     */
    public function update(): void {
        AuthMiddleware::handle();

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            flash('error', 'Invalid family profile.');
            redirect('family');
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('error', 'CSRF verification failed. Please try again.');
            redirect("family");
        }

        $validator = new ValidationService();
        $data = sanitize_array($_POST);

        $rules = [
            'head_resident_id' => 'required|numeric',
            'address' => 'required'
        ];

        if (!$validator->validate($data, $rules)) {
            flash('error', $validator->getFirstError());
            redirect("family");
        }

        // Process Members
        $members = [];
        if (isset($_POST['member_resident_id']) && is_array($_POST['member_resident_id'])) {
            foreach ($_POST['member_resident_id'] as $index => $rId) {
                $rId = (int)$rId;
                $rel = sanitize_input($_POST['member_relationship'][$index] ?? '');
                
                if (!empty($rId) && !empty($rel)) {
                    $members[] = [
                        'resident_id' => $rId,
                        'relationship' => $rel
                    ];
                }
            }
        }

        try {
            Family::update($id, $data, $members);
            
            // Log update
            db_log('UPDATE_FAMILY', "Updated family profile ID: " . $id);
            
            flash('success', 'Family profile updated successfully.');
            redirect('family');
        } catch (Exception $e) {
            flash('error', $e->getMessage());
            redirect("family");
        }
    }

    /**
     * Delete family profile
     */
    public function delete(): void {
        AuthMiddleware::handle(['Admin', 'Health Worker']);

        $id = (int)($_GET['id'] ?? 0);
        $family = Family::getById($id);

        if (!$family) {
            flash('error', 'Family profile not found.');
            redirect('family');
        }

        if (Family::delete($id)) {
            // Log deletion
            db_log('DELETE_FAMILY', "Deleted family profile: " . $family['family_no'] . " (ID: " . $id . ")");
            
            flash('success', 'Family profile deleted successfully.');
        } else {
            flash('error', 'Failed to delete family profile.');
        }

        redirect('family');
    }

    /**
     * Get family profile detail JSON for modal edit
     */
    public function detail_json(): void {
        AuthMiddleware::handle();

        header('Content-Type: application/json');
        $id = (int)($_GET['id'] ?? 0);
        $family = Family::getById($id);

        if (!$family) {
            echo json_encode(['success' => false, 'message' => 'Family profile not found']);
            exit;
        }

        $members = Family::getMembers($id);

        echo json_encode([
            'success' => true,
            'data' => [
                'family' => $family,
                'members' => $members
            ]
        ]);
        exit;
    }
}
