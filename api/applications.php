<?php
/**
 * BOCRA Domain Registration System - Applications API
 */

require_once __DIR__ . '/../backend/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            // Get all domain applications
            $registrarId = $_GET['registrar_id'] ?? null;
            $status = $_GET['status'] ?? null;
            
            $sql = "SELECT da.*, 
                    d.domain_name, d.status as domain_status,
                    a.full_name, a.company_name, a.type as applicant_type,
                    r.name as registrar_name, r.accreditation_number
                    FROM domain_applications da
                    LEFT JOIN domains d ON da.domain_id = d.id
                    LEFT JOIN applicants a ON da.applicant_id = a.id
                    LEFT JOIN registrars r ON da.registrar_id = r.id
                    WHERE 1=1";
            
            $params = [];
            
            if ($registrarId) {
                $sql .= " AND da.registrar_id = ?";
                $params[] = $registrarId;
            }
            
            if ($status) {
                $sql .= " AND da.submission_status = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY da.submitted_at DESC";
            
            $applications = dbQuery($sql, $params);
            
            // Format applicant names
            foreach ($applications as &$app) {
                $app['applicant_name'] = $app['applicant_type'] === 'company' 
                    ? $app['company_name'] 
                    : $app['full_name'];
            }
            
            jsonResponse(['success' => true, 'data' => $applications]);
            break;
            
        case 'stats':
            // Get application statistics
            $sql = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN submission_status = 'submitted' THEN 1 END) as submitted,
                    COUNT(CASE WHEN submission_status = 'under_review' THEN 1 END) as under_review,
                    COUNT(CASE WHEN submission_status = 'approved' THEN 1 END) as approved,
                    COUNT(CASE WHEN submission_status = 'rejected' THEN 1 END) as rejected
                    FROM domain_applications";
            
            $stats = dbQuery($sql)[0];
            
            jsonResponse(['success' => true, 'data' => $stats]);
            break;
            
        default:
            jsonResponse(['success' => false, 'error' => 'Invalid action'], 400);
    }
    
} catch (Exception $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
