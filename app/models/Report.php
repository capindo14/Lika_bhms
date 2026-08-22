<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class Report {
    /**
     * Get residents report data based on filters
     * 
     * @param array $filters
     * @return array
     */
    public static function getResidentsReport(array $filters = []): array {
        $db = Database::getConnection();
        
        $sql = "
            SELECT *, TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) AS age 
            FROM residents 
            WHERE deleted_at IS NULL
        ";
        
        $params = [];
        
        if (!empty($filters['gender'])) {
            $sql .= " AND gender = :gender";
            $params[':gender'] = $filters['gender'];
        }
        
        if (!empty($filters['civil_status'])) {
            $sql .= " AND civil_status = :civil_status";
            $params[':civil_status'] = $filters['civil_status'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['age_group'])) {
            switch ($filters['age_group']) {
                case 'infant':
                    $sql .= " AND TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) < 1";
                    break;
                case 'child':
                    $sql .= " AND TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 1 AND 12";
                    break;
                case 'teen':
                    $sql .= " AND TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 13 AND 19";
                    break;
                case 'adult':
                    $sql .= " AND TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 20 AND 59";
                    break;
                case 'senior':
                    $sql .= " AND TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) >= 60";
                    break;
            }
        }
        
        $sql .= " ORDER BY last_name, first_name";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get consultations report data based on filters
     * 
     * @param array $filters
     * @return array
     */
    public static function getConsultationsReport(array $filters = []): array {
        $db = Database::getConnection();
        
        $sql = "
            SELECT c.*, 
                   CONCAT(r.last_name, ', ', r.first_name) AS resident_name, 
                   r.resident_id AS resident_code,
                   u.fullname AS worker_name, 
                   m.name AS medicine_name
            FROM consultations c
            JOIN residents r ON c.resident_id = r.id
            JOIN users u ON c.user_id = u.id
            LEFT JOIN medicines m ON c.medicine_id = m.id
            WHERE c.deleted_at IS NULL
        ";
        
        $params = [];
        
        if (!empty($filters['start_date'])) {
            $sql .= " AND c.consultation_date >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $sql .= " AND c.consultation_date <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND c.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        $sql .= " ORDER BY c.consultation_date DESC, c.id DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get immunizations report data based on filters
     * 
     * @param array $filters
     * @return array
     */
    public static function getImmunizationsReport(array $filters = []): array {
        $db = Database::getConnection();
        
        $sql = "
            SELECT i.*, 
                   CONCAT(r.last_name, ', ', r.first_name) AS resident_name, 
                   r.resident_id AS resident_code,
                   m.name AS vaccine_name, 
                   u.fullname AS worker_name
            FROM immunizations i
            JOIN residents r ON i.resident_id = r.id
            JOIN medicines m ON i.vaccine_id = m.id
            JOIN users u ON i.user_id = u.id
            WHERE i.deleted_at IS NULL
        ";
        
        $params = [];
        
        if (!empty($filters['start_date'])) {
            $sql .= " AND COALESCE(i.date_given, i.next_schedule) >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $sql .= " AND COALESCE(i.date_given, i.next_schedule) <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND i.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['vaccine_id'])) {
            $sql .= " AND i.vaccine_id = :vaccine_id";
            $params[':vaccine_id'] = $filters['vaccine_id'];
        }
        
        $sql .= " ORDER BY i.next_schedule ASC, i.date_given DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get medicine distribution report data based on filters
     * 
     * @param array $filters
     * @return array
     */
    public static function getMedicinesReport(array $filters = []): array {
        $db = Database::getConnection();
        
        $sql = "
            SELECT md.*, 
                   m.name AS medicine_name, 
                   m.code AS medicine_code,
                   m.category AS medicine_category,
                   CONCAT(r.last_name, ', ', r.first_name) AS resident_name,
                   r.resident_id AS resident_code,
                   u.fullname AS worker_name
            FROM medicine_distributions md
            JOIN medicines m ON md.medicine_id = m.id
            JOIN residents r ON md.resident_id = r.id
            JOIN users u ON md.user_id = u.id
            WHERE md.deleted_at IS NULL
        ";
        
        $params = [];
        
        if (!empty($filters['start_date'])) {
            $sql .= " AND md.distribution_date >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $sql .= " AND md.distribution_date <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }
        
        if (!empty($filters['medicine_id'])) {
            $sql .= " AND md.medicine_id = :medicine_id";
            $params[':medicine_id'] = $filters['medicine_id'];
        }
        
        $sql .= " ORDER BY md.distribution_date DESC, md.id DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get overall health distribution summary data
     * 
     * @return array
     */
    public static function getHealthSummaryData(): array {
        $db = Database::getConnection();
        
        $summary = [];
        $summary['total_residents'] = (int)$db->query("SELECT COUNT(*) FROM residents WHERE status='Active' AND deleted_at IS NULL")->fetchColumn();
        $summary['total_families'] = (int)$db->query("SELECT COUNT(*) FROM families WHERE deleted_at IS NULL")->fetchColumn();
        $summary['total_consultations'] = (int)$db->query("SELECT COUNT(*) FROM consultations WHERE deleted_at IS NULL")->fetchColumn();
        $summary['total_immunizations'] = (int)$db->query("SELECT COUNT(*) FROM immunizations WHERE status='Completed' AND deleted_at IS NULL")->fetchColumn();
        $summary['total_distributions'] = (int)$db->query("SELECT SUM(quantity) FROM medicine_distributions WHERE deleted_at IS NULL")->fetchColumn() ?: 0;
        
        $summary['total_residents_served'] = (int)$db->query("
            SELECT COUNT(DISTINCT resident_id) FROM (
                SELECT resident_id FROM consultations WHERE deleted_at IS NULL
                UNION
                SELECT resident_id FROM immunizations WHERE deleted_at IS NULL
                UNION
                SELECT resident_id FROM medicine_distributions WHERE deleted_at IS NULL
            ) AS served
        ")->fetchColumn();

        if ($summary['total_residents_served'] === 0) {
            $summary['total_residents_served'] = $summary['total_residents'];
        }

        return $summary;
    }

    /**
     * Get detailed health summary report data based on date range filters
     * 
     * @param array $filters
     * @return array
     */
    public static function getHealthSummaryReport(array $filters = []): array {
        $db = Database::getConnection();
        
        $startDate = $filters['start_date'] ?? '';
        $endDate = $filters['end_date'] ?? '';
        
        $conWhere = ["c.deleted_at IS NULL"];
        $immWhere = ["i.deleted_at IS NULL"];
        $medWhere = ["md.deleted_at IS NULL"];
        $conParams = [];
        $immParams = [];
        $medParams = [];
        
        if (!empty($startDate)) {
            $conWhere[] = "c.consultation_date >= :start_date";
            $immWhere[] = "COALESCE(i.date_given, i.next_schedule) >= :start_date";
            $medWhere[] = "md.distribution_date >= :start_date";
            $conParams[':start_date'] = $startDate;
            $immParams[':start_date'] = $startDate;
            $medParams[':start_date'] = $startDate;
        }
        
        if (!empty($endDate)) {
            $conWhere[] = "c.consultation_date <= :end_date";
            $immWhere[] = "COALESCE(i.date_given, i.next_schedule) <= :end_date";
            $medWhere[] = "md.distribution_date <= :end_date";
            $conParams[':end_date'] = $endDate;
            $immParams[':end_date'] = $endDate;
            $medParams[':end_date'] = $endDate;
        }
        
        $conWhereSql = implode(' AND ', $conWhere);
        $immWhereSql = implode(' AND ', $immWhere);
        $medWhereSql = implode(' AND ', $medWhere);

        // 1. Total Consultations
        $stmt = $db->prepare("SELECT COUNT(*) FROM consultations c WHERE {$conWhereSql}");
        $stmt->execute($conParams);
        $totalConsultations = (int)$stmt->fetchColumn();

        // 2. Total Immunizations Conducted
        $immConWhereSql = $immWhereSql . " AND i.status = 'Completed'";
        $stmt = $db->prepare("SELECT COUNT(*) FROM immunizations i WHERE {$immConWhereSql}");
        $stmt->execute($immParams);
        $totalImmunizations = (int)$stmt->fetchColumn();

        // 3. Medicines Distributed
        $stmt = $db->prepare("SELECT SUM(quantity) FROM medicine_distributions md WHERE {$medWhereSql}");
        $stmt->execute($medParams);
        $medicinesDistributed = (int)$stmt->fetchColumn() ?: 0;

        // 4. Total Unique Residents Served
        $resWhereCon = str_replace('c.', '', $conWhereSql);
        $resWhereImm = str_replace('i.', '', $immWhereSql);
        $resWhereMed = str_replace('md.', '', $medWhereSql);

        $servedParams = [];
        if (!empty($startDate)) {
            $servedParams[':start_date_1'] = $startDate;
            $servedParams[':start_date_2'] = $startDate;
            $servedParams[':start_date_3'] = $startDate;
            $resWhereCon = str_replace(':start_date', ':start_date_1', $resWhereCon);
            $resWhereImm = str_replace(':start_date', ':start_date_2', $resWhereImm);
            $resWhereMed = str_replace(':start_date', ':start_date_3', $resWhereMed);
        }
        if (!empty($endDate)) {
            $servedParams[':end_date_1'] = $endDate;
            $servedParams[':end_date_2'] = $endDate;
            $servedParams[':end_date_3'] = $endDate;
            $resWhereCon = str_replace(':end_date', ':end_date_1', $resWhereCon);
            $resWhereImm = str_replace(':end_date', ':end_date_2', $resWhereImm);
            $resWhereMed = str_replace(':end_date', ':end_date_3', $resWhereMed);
        }

        $servedSql = "
            SELECT COUNT(DISTINCT resident_id) FROM (
                SELECT resident_id FROM consultations WHERE {$resWhereCon}
                UNION
                SELECT resident_id FROM immunizations WHERE {$resWhereImm}
                UNION
                SELECT resident_id FROM medicine_distributions WHERE {$resWhereMed}
            ) AS served
        ";
        $stmt = $db->prepare($servedSql);
        $stmt->execute($servedParams);
        $totalResidentsServed = (int)$stmt->fetchColumn();

        if (empty($startDate) && empty($endDate) && $totalResidentsServed === 0) {
            $totalResidentsServed = (int)$db->query("SELECT COUNT(*) FROM residents WHERE status='Active' AND deleted_at IS NULL")->fetchColumn();
        }

        // Consultations list
        $stmt = $db->prepare("
            SELECT c.*, CONCAT(r.last_name, ', ', r.first_name) AS resident_name, r.resident_id AS resident_code, u.fullname AS worker_name, m.name AS medicine_name
            FROM consultations c
            JOIN residents r ON c.resident_id = r.id
            JOIN users u ON c.user_id = u.id
            LEFT JOIN medicines m ON c.medicine_id = m.id
            WHERE {$conWhereSql}
            ORDER BY c.consultation_date DESC, c.id DESC LIMIT 100
        ");
        $stmt->execute($conParams);
        $consultationsList = $stmt->fetchAll();

        // Immunizations list
        $stmt = $db->prepare("
            SELECT i.*, CONCAT(r.last_name, ', ', r.first_name) AS resident_name, r.resident_id AS resident_code, m.name AS vaccine_name, u.fullname AS worker_name
            FROM immunizations i
            JOIN residents r ON i.resident_id = r.id
            JOIN medicines m ON i.vaccine_id = m.id
            JOIN users u ON i.user_id = u.id
            WHERE {$immWhereSql}
            ORDER BY i.date_given DESC, i.next_schedule DESC LIMIT 100
        ");
        $stmt->execute($immParams);
        $immunizationsList = $stmt->fetchAll();

        // Medicines list
        $stmt = $db->prepare("
            SELECT md.*, m.name AS medicine_name, m.code AS medicine_code, m.category AS medicine_category, CONCAT(r.last_name, ', ', r.first_name) AS resident_name, r.resident_id AS resident_code, u.fullname AS worker_name
            FROM medicine_distributions md
            JOIN medicines m ON md.medicine_id = m.id
            JOIN residents r ON md.resident_id = r.id
            JOIN users u ON md.user_id = u.id
            WHERE {$medWhereSql}
            ORDER BY md.distribution_date DESC, md.id DESC LIMIT 100
        ");
        $stmt->execute($medParams);
        $medicinesList = $stmt->fetchAll();

        $stmt = $db->prepare("
            SELECT DISTINCT r.id, r.resident_id AS resident_code, CONCAT(r.last_name, ', ', r.first_name) AS resident_name, r.gender, TIMESTAMPDIFF(YEAR, r.birthdate, CURDATE()) AS age, r.contact_number
            FROM residents r
            WHERE r.deleted_at IS NULL AND r.id IN (
                SELECT resident_id FROM consultations WHERE {$resWhereCon}
                UNION
                SELECT resident_id FROM immunizations WHERE {$resWhereImm}
                UNION
                SELECT resident_id FROM medicine_distributions WHERE {$resWhereMed}
            )
            ORDER BY r.last_name, r.first_name
        ");
        $stmt->execute($servedParams);
        $residentsServedList = $stmt->fetchAll();

        return [
            'total_residents_served' => $totalResidentsServed,
            'total_consultations' => $totalConsultations,
            'total_immunizations' => $totalImmunizations,
            'medicines_distributed' => $medicinesDistributed,
            'consultations_list' => $consultationsList,
            'immunizations_list' => $immunizationsList,
            'medicines_list' => $medicinesList,
            'residents_served_list' => $residentsServedList
        ];
    }
}
