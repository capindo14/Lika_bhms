<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Resident;
use App\Models\Family;
use App\Models\Consultation;
use App\Models\Immunization;
use App\Models\Medicine;
use App\Models\User;
use App\Config\Database;
use PDO;

class DashboardController {
    /**
     * Render the system dashboard
     */
    public function index(): void {
        // Enforce user authentication
        AuthMiddleware::handle();

        // 1. Fetch Metrics Cards
        $totalResidents = Resident::countAll();
        $totalFamilies = Family::countAll();
        $totalConsultations = Consultation::countAll();
        $totalImmunizations = Immunization::countAll();
        $lowStockCount = Medicine::countLowStock();
        $healthWorkersCount = User::countWorkers();

        // 2. Fetch Lists for widgets
        $latestResidents = Resident::getLatest(5);
        $latestConsultations = Consultation::getLatest(5);
        $upcomingImmunizations = Immunization::getUpcomingSchedule(5);
        
        // Fetch recent activity logs
        $db = Database::getConnection();
        $stmtLogs = $db->query("
            SELECT al.*, u.fullname, u.role
            FROM activity_logs al
            LEFT JOIN users u ON al.user_id = u.id
            ORDER BY al.created_at DESC
            LIMIT 5
        ");
        $recentActivities = $stmtLogs->fetchAll();

        // 3. Fetch Chart Data Sets
        // 3.1 Monthly Consultations (Current Year)
        $monthlyConsultations = Consultation::getMonthlyCounts();
        
        // 3.2 Monthly Immunizations (Current Year)
        $monthlyImmunizations = Immunization::getMonthlyCounts();

        // 3.3 Top 5 Distributed Medicines
        $stmtMedDist = $db->query("
            SELECT m.name, SUM(md.quantity) as count
            FROM medicine_distributions md
            JOIN medicines m ON md.medicine_id = m.id
            WHERE md.deleted_at IS NULL
            GROUP BY md.medicine_id, m.name
            ORDER BY count DESC
            LIMIT 5
        ");
        $medicineDistribution = $stmtMedDist->fetchAll();

        // 3.4 Gender Distribution
        $genderDistribution = Resident::getGenderDistribution();

        // 3.5 Age Distribution
        $ageDistribution = Resident::getAgeDistribution();

        // Load dashboard view inside layouts wrapper
        $pageTitle = 'Dashboard';
        require_once VIEW_PATH . 'dashboard/dashboard.php';
    }
}
