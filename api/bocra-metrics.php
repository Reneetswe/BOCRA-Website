<?php
/**
 * BOCRA Domain Registration System - BOCRA Metrics API
 */

require_once __DIR__ . '/../backend/config.php';

header('Content-Type: application/json');

try {
    // Get BOCRA dashboard metrics
    $sql = "SELECT * FROM bocra_metrics";
    $metrics = dbQuery($sql)[0];
    
    // Get monthly registration trend
    $trendSql = "SELECT 
                 DATE_FORMAT(registration_date, '%Y-%m') as month,
                 COUNT(*) as count
                 FROM domains
                 WHERE registration_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                 GROUP BY DATE_FORMAT(registration_date, '%Y-%m')
                 ORDER BY month";
    $trend = dbQuery($trendSql);
    
    // Get domains by status
    $statusSql = "SELECT status, COUNT(*) as count FROM domains GROUP BY status";
    $byStatus = dbQuery($statusSql);
    
    jsonResponse([
        'success' => true,
        'data' => [
            'metrics' => $metrics,
            'trend' => $trend,
            'by_status' => $byStatus
        ]
    ]);
    
} catch (Exception $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
