<?php
/**
 * BOCRA Domain Registration System - Audit Logs API
 */

require_once __DIR__ . '/../backend/config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            // Get audit logs with filters
            $role = $_GET['role'] ?? null;
            $action_filter = $_GET['action_filter'] ?? null;
            $limit = $_GET['limit'] ?? 50;
            
            $sql = "SELECT * FROM audit_logs WHERE 1=1";
            $params = [];
            
            if ($role) {
                $sql .= " AND actor_role = ?";
                $params[] = $role;
            }
            
            if ($action_filter) {
                $sql .= " AND action LIKE ?";
                $params[] = "%$action_filter%";
            }
            
            $sql .= " ORDER BY created_at DESC LIMIT ?";
            $params[] = (int)$limit;
            
            $logs = dbQuery($sql, $params);
            
            jsonResponse(['success' => true, 'data' => $logs]);
            break;
            
        default:
            jsonResponse(['success' => false, 'error' => 'Invalid action'], 400);
    }
    
} catch (Exception $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
