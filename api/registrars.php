<?php
/**
 * BOCRA Domain Registration System - Registrars API
 */

require_once __DIR__ . '/../backend/config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            // Get all registrars
            $sql = "SELECT * FROM registrars ORDER BY name";
            $registrars = dbQuery($sql);
            
            jsonResponse(['success' => true, 'data' => $registrars]);
            break;
            
        case 'stats':
            // Get registrar statistics
            $sql = "SELECT * FROM registrar_stats ORDER BY total_domains DESC";
            $stats = dbQuery($sql);
            
            jsonResponse(['success' => true, 'data' => $stats]);
            break;
            
        default:
            jsonResponse(['success' => false, 'error' => 'Invalid action'], 400);
    }
    
} catch (Exception $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
