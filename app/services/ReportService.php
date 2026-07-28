<?php

namespace App\Services;

class ReportService {
    /**
     * Export a dataset to a downloadable CSV (Excel compatible) file
     * 
     * @param string $filename Base name of the file (without extension)
     * @param array $headers List of column headers
     * @param array $data 2D array of rows matching columns
     * @return void
     */
    public static function exportToCSV(string $filename, array $headers, array $data): void {
        // Set Headers for Download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '_' . date('Ymd_His') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        
        // Output UTF-8 BOM for Excel compliance (prevents symbol mapping bugs)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Write headers
        fputcsv($output, $headers);

        // Write rows
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }
}
