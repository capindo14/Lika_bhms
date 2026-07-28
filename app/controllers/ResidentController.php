<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Resident;
use App\Services\ValidationService;
use Exception;

class ResidentController {
    /**
     * Display a listing of residents
     */
    public function index(): void {
        AuthMiddleware::handle();

        $status = $_GET['status'] ?? 'Active';
        if (!in_array($status, ['Active', 'Archived'])) {
            $status = 'Active';
        }

        $residents = Resident::getAll($status);
        $pageTitle = ($status === 'Archived') ? 'Archived Residents' : 'Resident Directory';
        
        require_once VIEW_PATH . 'residents/index.php';
    }

    /**
     * Display the resident registration form
     */
    public function create(): void {
        AuthMiddleware::handle();
        $pageTitle = 'Register Resident';
        require_once VIEW_PATH . 'residents/create.php';
    }

    /**
     * Store a newly created resident
     */
    public function store(): void {
        AuthMiddleware::handle();

        // 1. Verify CSRF
        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('error', 'CSRF verification failed. Please try again.');
            redirect('residents/create');
        }

        // 2. Validate Inputs
        $validator = new ValidationService();
        $data = sanitize_array($_POST);
        
        $rules = [
            'first_name' => 'required|min:2',
            'last_name' => 'required|min:2',
            'gender' => 'required',
            'birthdate' => 'required|date',
            'civil_status' => 'required',
            'address' => 'required'
        ];

        if (!$validator->validate($data, $rules)) {
            flash('error', $validator->getFirstError());
            redirect('residents/create');
        }

        try {
            $insertedId = Resident::create($data);
            
            // Log this action
            db_log('CREATE_RESIDENT', "Registered resident: " . $data['last_name'] . ", " . $data['first_name'] . " (ID: " . $insertedId . ")");
            
            flash('success', 'Resident record created successfully.');
            redirect("residents/view&id=" . $insertedId);
        } catch (Exception $e) {
            flash('error', $e->getMessage());
            redirect('residents/create');
        }
    }

    /**
     * Show details of a specific resident
     */
    public function view(): void {
        AuthMiddleware::handle();

        $id = (int)($_GET['id'] ?? 0);
        $resident = Resident::getById($id);

        if (!$resident) {
            flash('error', 'Resident record not found.');
            redirect('residents');
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

        $pageTitle = 'Resident Profile - ' . $resident['last_name'] . ', ' . $resident['first_name'];
        require_once VIEW_PATH . 'residents/view.php';
    }

    /**
     * Display the resident edit form
     */
    public function edit(): void {
        AuthMiddleware::handle();

        $id = (int)($_GET['id'] ?? 0);
        $resident = Resident::getById($id);

        if (!$resident) {
            flash('error', 'Resident record not found.');
            redirect('residents');
        }

        $pageTitle = 'Edit Resident Details';
        require_once VIEW_PATH . 'residents/edit.php';
    }

    /**
     * Update resident details
     */
    public function update(): void {
        AuthMiddleware::handle();

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            flash('error', 'Invalid resident record.');
            redirect('residents');
        }

        // 1. Verify CSRF
        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('error', 'CSRF verification failed. Please try again.');
            redirect("residents/edit&id={$id}");
        }

        // 2. Validate Inputs
        $validator = new ValidationService();
        $data = sanitize_array($_POST);
        
        $rules = [
            'first_name' => 'required|min:2',
            'last_name' => 'required|min:2',
            'gender' => 'required',
            'birthdate' => 'required|date',
            'civil_status' => 'required',
            'address' => 'required'
        ];

        if (!$validator->validate($data, $rules)) {
            flash('error', $validator->getFirstError());
            redirect("residents/edit&id={$id}");
        }

        try {
            Resident::update($id, $data);
            
            // Log update
            db_log('UPDATE_RESIDENT', "Updated resident: " . $data['last_name'] . ", " . $data['first_name'] . " (ID: " . $id . ")");
            
            flash('success', 'Resident record updated successfully.');
            redirect('residents');
        } catch (Exception $e) {
            flash('error', $e->getMessage());
            redirect("residents");
        }
    }

    /**
     * Archive resident record (soft delete)
     */
    public function archive(): void {
        AuthMiddleware::handle(['Admin', 'Health Worker']);

        $id = (int)($_GET['id'] ?? 0);
        $resident = Resident::getById($id);

        if (!$resident) {
            flash('error', 'Resident record not found.');
            redirect('residents');
        }

        try {
            Resident::setStatus($id, 'Archived');
            
            // Log archiving
            db_log('ARCHIVE_RESIDENT', "Archived resident profile: " . $resident['last_name'] . ", " . $resident['first_name'] . " (ID: " . $id . ")");
            
            flash('success', 'Resident profile archived successfully.');
        } catch (Exception $e) {
            flash('error', 'Failed to archive resident: ' . $e->getMessage());
        }

        redirect('residents');
    }

    /**
     * Restore resident record
     */
    public function restore(): void {
        AuthMiddleware::handle(['Admin', 'Health Worker']);

        $id = (int)($_GET['id'] ?? 0);
        
        // Find in archived residents
        $db = \App\Config\Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM residents WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute([':id' => $id]);
        $resident = $stmt->fetch();

        if (!$resident) {
            flash('error', 'Archived resident record not found.');
            redirect('residents&status=Archived');
        }

        try {
            Resident::setStatus($id, 'Active');
            
            // Log restore
            db_log('RESTORE_RESIDENT', "Restored resident profile: " . $resident['last_name'] . ", " . $resident['first_name'] . " (ID: " . $id . ")");
            
            flash('success', 'Resident profile restored successfully.');
        } catch (Exception $e) {
            flash('error', 'Failed to restore resident: ' . $e->getMessage());
        }

        redirect('residents');
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
}
