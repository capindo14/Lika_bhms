<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Immunization;
use App\Models\Resident;
use App\Models\Medicine;
use App\Services\ValidationService;
use Exception;

class ImmunizationController {
    /**
     * Display a listing of immunization records
     */
    public function index(): void {
        AuthMiddleware::handle();

        $records = Immunization::getAll();
        $residents = Resident::getAll('Active');
        $vaccines = Medicine::getByCategory('Vaccine');
        $pageTitle = 'Immunization Schedule & Logs';
        
        require_once VIEW_PATH . 'immunization/index.php';
    }

    /**
     * Display log form
     */
    public function create(): void {
        AuthMiddleware::handle();

        $residents = Resident::getAll('Active');
        // Fetch vaccines from inventory
        $vaccines = Medicine::getByCategory('Vaccine');

        $pageTitle = 'Log Vaccination / Schedule';
        require_once VIEW_PATH . 'immunization/create.php';
    }

    /**
     * Store new immunization record
     */
    public function store(): void {
        AuthMiddleware::handle();

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('error', 'CSRF verification failed. Please try again.');
            redirect('immunization/create');
        }

        $validator = new ValidationService();
        $data = sanitize_array($_POST);
        
        // Footprint
        $data['user_id'] = $_SESSION['user_id'];

        $rules = [
            'resident_id' => 'required|numeric',
            'vaccine_id' => 'required|numeric',
            'dose' => 'required',
            'status' => 'required'
        ];

        if (!$validator->validate($data, $rules)) {
            flash('error', $validator->getFirstError());
            redirect('immunization/create');
        }

        // Validate date requirements:
        // If status is Completed, date_given must be filled.
        // If status is Upcoming, next_schedule must be filled.
        if ($data['status'] === 'Completed' && empty($data['date_given'])) {
            flash('error', 'Completed status requires Date Given to be specified.');
            redirect('immunization/create');
        }

        if ($data['status'] === 'Upcoming' && empty($data['next_schedule'])) {
            flash('error', 'Upcoming status requires Next Schedule Date to be specified.');
            redirect('immunization/create');
        }

        try {
            $insertedId = Immunization::create($data);
            
            // Log action
            db_log('CREATE_IMMUNIZATION', "Logged immunization entry ID: {$insertedId} for resident ID: " . $data['resident_id']);
            
            flash('success', 'Immunization record saved successfully.');
            redirect('immunization');
        } catch (Exception $e) {
            flash('error', $e->getMessage());
            redirect('immunization/create');
        }
    }

    /**
     * Display edit form
     */
    public function edit(): void {
        AuthMiddleware::handle();

        $id = (int)($_GET['id'] ?? 0);
        $record = Immunization::getById($id);

        if (!$record) {
            flash('error', 'Immunization record not found.');
            redirect('immunization');
        }

        $residents = Resident::getAll('Active');
        $vaccines = Medicine::getByCategory('Vaccine');

        $pageTitle = 'Edit Immunization Details';
        require_once VIEW_PATH . 'immunization/edit.php';
    }

    /**
     * Update immunization record
     */
    public function update(): void {
        AuthMiddleware::handle();

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            flash('error', 'Invalid immunization record.');
            redirect('immunization');
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('error', 'CSRF verification failed. Please try again.');
            redirect("immunization");
        }

        $validator = new ValidationService();
        $data = sanitize_array($_POST);

        $rules = [
            'resident_id' => 'required|numeric',
            'vaccine_id' => 'required|numeric',
            'dose' => 'required',
            'status' => 'required'
        ];

        if (!$validator->validate($data, $rules)) {
            flash('error', $validator->getFirstError());
            redirect("immunization");
        }

        if ($data['status'] === 'Completed' && empty($data['date_given'])) {
            flash('error', 'Completed status requires Date Given to be specified.');
            redirect("immunization");
        }

        if ($data['status'] === 'Upcoming' && empty($data['next_schedule'])) {
            flash('error', 'Upcoming status requires Next Schedule Date to be specified.');
            redirect("immunization");
        }

        try {
            Immunization::update($id, $data);
            
            // Log update
            db_log('UPDATE_IMMUNIZATION', "Updated immunization ID: " . $id);
            
            flash('success', 'Immunization record updated successfully.');
            redirect('immunization');
        } catch (Exception $e) {
            flash('error', $e->getMessage());
            redirect("immunization");
        }
    }

    /**
     * Delete record
     */
    public function delete(): void {
        AuthMiddleware::handle(['Admin', 'Health Worker']);

        $id = (int)($_GET['id'] ?? 0);
        $record = Immunization::getById($id);

        if (!$record) {
            flash('error', 'Immunization record not found.');
            redirect('immunization');
        }

        if (Immunization::delete($id)) {
            // Log deletion
            db_log('DELETE_IMMUNIZATION', "Deleted immunization record (ID: {$id}) for resident ID: " . $record['resident_id']);
            
            flash('success', 'Immunization record deleted successfully.');
        } else {
            flash('error', 'Failed to delete immunization record.');
        }

        redirect('immunization');
    }

    /**
     * Get detail JSON for modal edit
     */
    public function detail_json(): void {
        AuthMiddleware::handle();

        header('Content-Type: application/json');
        $id = (int)($_GET['id'] ?? 0);
        $record = Immunization::getById($id);

        if (!$record) {
            echo json_encode(['success' => false, 'message' => 'Immunization record not found']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'data' => $record
        ]);
        exit;
    }
}
