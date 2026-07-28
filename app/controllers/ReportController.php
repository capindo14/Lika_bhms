<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Report;
use App\Models\Medicine;
use App\Services\ReportService;
use Exception;

class ReportController {
    /**
     * Display the main reports summary dashboard
     */
    public function index(): void {
        AuthMiddleware::handle();

        $summary = Report::getHealthSummaryData();
        $lowStocks = Medicine::getLowStockAlerts();

        $pageTitle = 'Reports Center';
        require_once VIEW_PATH . 'reports/summary_report.php';
    }

    /**
     * Display Health Summary Report with generate report button filter functionality
     */
    public function health_summary(): void {
        AuthMiddleware::handle();

        $filters = [
            'start_date' => sanitize_input($_GET['start_date'] ?? ''),
            'end_date' => sanitize_input($_GET['end_date'] ?? '')
        ];

        $reportData = Report::getHealthSummaryReport($filters);

        // Handle Export requests
        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            $headers = ['Health Metric / Indicator', 'Total Value'];
            $exportData = [
                ['Total Residents Served', $reportData['total_residents_served']],
                ['Total Consultations', $reportData['total_consultations']],
                ['Total Immunizations Conducted', $reportData['total_immunizations']],
                ['Medicines Distributed', $reportData['medicines_distributed']]
            ];
            ReportService::exportToCSV('Health_Summary_Report', $headers, $exportData);
        }

        $pageTitle = 'Health Summary Report';
        require_once VIEW_PATH . 'reports/health_summary_report.php';
    }

    /**
     * Display demographic reports for residents
     */
    public function residents(): void {
        AuthMiddleware::handle();

        $filters = [
            'gender' => sanitize_input($_GET['gender'] ?? ''),
            'civil_status' => sanitize_input($_GET['civil_status'] ?? ''),
            'status' => sanitize_input($_GET['status'] ?? 'Active'),
            'age_group' => sanitize_input($_GET['age_group'] ?? '')
        ];

        $data = Report::getResidentsReport($filters);

        // Handle Export requests
        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            $headers = ['Resident ID', 'Full Name', 'Gender', 'Birthdate', 'Age', 'Civil Status', 'Contact Number', 'Barangay Health Center', 'Role', 'Status'];
            $exportData = [];
            foreach ($data as $r) {
                $exportData[] = [
                    $r['resident_id'],
                    $r['last_name'] . ', ' . $r['first_name'] . ' ' . $r['middle_name'],
                    $r['gender'],
                    $r['birthdate'],
                    $r['age'],
                    $r['civil_status'],
                    $r['contact_number'] ?: 'N/A',
                    $r['barangay'],
                    $r['is_family_head'] ? 'Family Head' : 'Member',
                    $r['status']
                ];
            }
            ReportService::exportToCSV('Residents_Demographics', $headers, $exportData);
        }

        $pageTitle = 'Residents Demographics Report';
        require_once VIEW_PATH . 'reports/resident_report.php';
    }

    /**
     * Display diagnostic reports for consultations
     */
    public function consultations(): void {
        AuthMiddleware::handle();

        $filters = [
            'start_date' => sanitize_input($_GET['start_date'] ?? ''),
            'end_date' => sanitize_input($_GET['end_date'] ?? ''),
            'status' => sanitize_input($_GET['status'] ?? '')
        ];

        $data = Report::getConsultationsReport($filters);

        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            $headers = ['Consultation No.', 'Date', 'Patient Name', 'Patient ID', 'Symptoms', 'Diagnosis', 'Treatment Plan', 'Medicine Dispensed', 'Qty', 'Health Worker', 'Status'];
            $exportData = [];
            foreach ($data as $c) {
                $exportData[] = [
                    $c['consultation_no'],
                    $c['consultation_date'],
                    $c['resident_name'],
                    $c['resident_code'],
                    $c['symptoms'],
                    $c['diagnosis'],
                    $c['treatment'],
                    $c['medicine_name'] ?: 'None',
                    $c['medicine_qty'] ?: 0,
                    $c['worker_name'],
                    $c['status']
                ];
            }
            ReportService::exportToCSV('Consultation_Records', $headers, $exportData);
        }

        $pageTitle = 'Medical Consultations Report';
        require_once VIEW_PATH . 'reports/consultation_report.php';
    }

    /**
     * Display immunization reports
     */
    public function immunizations(): void {
        AuthMiddleware::handle();

        $filters = [
            'start_date' => sanitize_input($_GET['start_date'] ?? ''),
            'end_date' => sanitize_input($_GET['end_date'] ?? ''),
            'status' => sanitize_input($_GET['status'] ?? ''),
            'vaccine_id' => sanitize_input($_GET['vaccine_id'] ?? '')
        ];

        $vaccines = Medicine::getByCategory('Vaccine');
        $data = Report::getImmunizationsReport($filters);

        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            $headers = ['Child/Resident', 'Patient ID', 'Vaccine', 'Dose Level', 'Date Given', 'Next Appointment', 'Status', 'Health Worker Logged'];
            $exportData = [];
            foreach ($data as $i) {
                $exportData[] = [
                    $i['resident_name'],
                    $i['resident_code'],
                    $i['vaccine_name'],
                    $i['dose'],
                    $i['date_given'] ?: 'N/A',
                    $i['next_schedule'] ?: 'None',
                    $i['status'],
                    $i['worker_name']
                ];
            }
            ReportService::exportToCSV('Immunization_Schedules', $headers, $exportData);
        }

        $pageTitle = 'Immunizations Schedules Report';
        require_once VIEW_PATH . 'reports/immunization_report.php';
    }

    /**
     * Display medicine allocation reports
     */
    public function medicine(): void {
        AuthMiddleware::handle();

        $filters = [
            'start_date' => sanitize_input($_GET['start_date'] ?? ''),
            'end_date' => sanitize_input($_GET['end_date'] ?? ''),
            'medicine_id' => sanitize_input($_GET['medicine_id'] ?? '')
        ];

        $medicinesList = Medicine::getAll();
        $data = Report::getMedicinesReport($filters);

        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            $headers = ['Date', 'Item Name', 'Code', 'Category', 'Recipient', 'Patient ID', 'Quantity Dispensed', 'Health Worker Signature'];
            $exportData = [];
            foreach ($data as $md) {
                $exportData[] = [
                    $md['distribution_date'],
                    $md['medicine_name'],
                    $md['medicine_code'],
                    $md['medicine_category'],
                    $md['resident_name'],
                    $md['resident_code'],
                    $md['quantity'],
                    $md['worker_name']
                ];
            }
            ReportService::exportToCSV('Medicine_Distributions', $headers, $exportData);
        }

        $pageTitle = 'Medicine Allocations Report';
        require_once VIEW_PATH . 'reports/medicine_report.php';
    }
}
