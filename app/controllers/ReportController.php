<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Report;
use App\Models\Medicine;
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



        $pageTitle = 'Medicine & Family Planning Report';
        require_once VIEW_PATH . 'reports/medicine_report.php';
    }
}
