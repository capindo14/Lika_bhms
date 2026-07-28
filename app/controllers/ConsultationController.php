<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Consultation;
use App\Models\Resident;
use App\Models\Medicine;
use App\Services\ValidationService;
use Exception;

class ConsultationController {
    /**
     * Display a list of consultations
     */
    public function index(): void {
        AuthMiddleware::handle();

        $consultations = Consultation::getAll();
        $residents = Resident::getAll('Active');
        $medicines = Medicine::getAll();
        $pageTitle = 'Medical Consultations';
        
        require_once VIEW_PATH . 'consultation/index.php';
    }

    /**
     * Display log consultation form
     */
    public function create(): void {
        AuthMiddleware::handle();

        $residents = Resident::getAll('Active');
        $medicines = Medicine::getAll(); // Fetch all items in inventory to allow dispensation
        
        $pageTitle = 'Log Consultation';
        require_once VIEW_PATH . 'consultation/create.php';
    }

    /**
     * Store a consultation record
     */
    public function store(): void {
        AuthMiddleware::handle();

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('error', 'CSRF verification failed. Please try again.');
            redirect('consultation/create');
        }

        $validator = new ValidationService();
        $data = sanitize_array($_POST);
        
        // Enforce user footprint
        $data['user_id'] = $_SESSION['user_id'];

        $rules = [
            'resident_id' => 'required|numeric',
            'symptoms' => 'required',
            'diagnosis' => 'required',
            'treatment' => 'required',
            'consultation_date' => 'required|date'
        ];

        if (!$validator->validate($data, $rules)) {
            flash('error', $validator->getFirstError());
            redirect('consultation/create');
        }

        try {
            $insertedId = Consultation::create($data);
            
            // Log action
            db_log('CREATE_CONSULTATION', "Logged consultation CON# {$insertedId} for resident ID: " . $data['resident_id']);
            
            flash('success', 'Consultation record logged successfully.');
            redirect('consultation');
        } catch (Exception $e) {
            flash('error', $e->getMessage());
            redirect('consultation/create');
        }
    }

    /**
     * Display edit form for consultation
     */
    public function edit(): void {
        AuthMiddleware::handle();

        $id = (int)($_GET['id'] ?? 0);
        $consultation = Consultation::getById($id);

        if (!$consultation) {
            flash('error', 'Consultation record not found.');
            redirect('consultation');
        }

        $residents = Resident::getAll('Active');
        $medicines = Medicine::getAll();

        $pageTitle = 'Edit Consultation Details - ' . $consultation['consultation_no'];
        require_once VIEW_PATH . 'consultation/edit.php';
    }

    /**
     * Update consultation record
     */
    public function update(): void {
        AuthMiddleware::handle();

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            flash('error', 'Invalid consultation record.');
            redirect('consultation');
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('error', 'CSRF verification failed. Please try again.');
            redirect("consultation");
        }

        $validator = new ValidationService();
        $data = sanitize_array($_POST);
        
        // Enforce user footprint
        $data['user_id'] = $_SESSION['user_id'];

        $rules = [
            'resident_id' => 'required|numeric',
            'symptoms' => 'required',
            'diagnosis' => 'required',
            'treatment' => 'required',
            'consultation_date' => 'required|date'
        ];

        if (!$validator->validate($data, $rules)) {
            flash('error', $validator->getFirstError());
            redirect("consultation");
        }

        try {
            Consultation::update($id, $data);
            
            // Log update
            db_log('UPDATE_CONSULTATION', "Updated consultation ID: " . $id);
            
            flash('success', 'Consultation record updated successfully.');
            redirect('consultation');
        } catch (Exception $e) {
            flash('error', $e->getMessage());
            redirect("consultation");
        }
    }

    /**
     * Delete consultation record
     */
    public function delete(): void {
        AuthMiddleware::handle(['Admin', 'Health Worker']);

        $id = (int)($_GET['id'] ?? 0);
        $consultation = Consultation::getById($id);

        if (!$consultation) {
            flash('error', 'Consultation record not found.');
            redirect('consultation');
        }

        if (Consultation::delete($id)) {
            // Log action
            db_log('DELETE_CONSULTATION', "Deleted consultation: " . $consultation['consultation_no'] . " (ID: " . $id . ")");
            
            flash('success', 'Consultation record deleted successfully.');
        } else {
            flash('error', 'Failed to delete consultation record.');
        }

        redirect('consultation');
    }

    /**
     * Return consultation details as JSON
     */
    public function detail_json(): void {
        AuthMiddleware::handle();
        $id = (int)($_GET['id'] ?? 0);
        $consultation = Consultation::getById($id);
        
        if ($consultation) {
            json_response(true, 'Consultation retrieved successfully.', $consultation);
        } else {
            json_response(false, 'Consultation not found.');
        }
    }
}

