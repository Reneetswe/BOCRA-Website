<?php
/**
 * BOCRA Domain Registration System - Domains API
 */

require_once __DIR__ . '/../backend/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            // Get all domains with filters
            $registrarId = $_GET['registrar_id'] ?? null;
            $status = $_GET['status'] ?? null;
            $search = $_GET['search'] ?? null;
            
            $sql = "SELECT d.*, 
                    a.full_name, a.company_name, a.type as applicant_type,
                    r.name as registrar_name, r.accreditation_number
                    FROM domains d
                    LEFT JOIN applicants a ON d.applicant_id = a.id
                    LEFT JOIN registrars r ON d.registrar_id = r.id
                    WHERE 1=1";
            
            $params = [];
            
            if ($registrarId) {
                $sql .= " AND d.registrar_id = ?";
                $params[] = $registrarId;
            }
            
            if ($status) {
                $sql .= " AND d.status = ?";
                $params[] = $status;
            }
            
            if ($search) {
                $sql .= " AND (d.domain_name LIKE ? OR a.full_name LIKE ? OR a.company_name LIKE ?)";
                $searchParam = "%$search%";
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }
            
            $sql .= " ORDER BY d.created_at DESC";
            
            $domains = dbQuery($sql, $params);
            
            // Format applicant names
            foreach ($domains as &$domain) {
                $domain['applicant_name'] = $domain['applicant_type'] === 'company' 
                    ? $domain['company_name'] 
                    : $domain['full_name'];
            }
            
            jsonResponse(['success' => true, 'data' => $domains]);
            break;
            
        case 'stats':
            // Get domain statistics
            $registrarId = $_GET['registrar_id'] ?? null;
            
            $sql = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
                    COUNT(CASE WHEN status = 'suspended' THEN 1 END) as suspended,
                    COUNT(CASE WHEN status = 'expired' THEN 1 END) as expired
                    FROM domains";
            
            $params = [];
            
            if ($registrarId) {
                $sql .= " WHERE registrar_id = ?";
                $params[] = $registrarId;
            }
            
            $stats = dbQuery($sql, $params)[0];
            
            jsonResponse(['success' => true, 'data' => $stats]);
            break;
            
        case 'register':
            // Register new domain (registrar only)
            if ($method !== 'POST') {
                jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            $required = ['domain_name', 'applicant_id', 'registrar_id', 'nameserver_1', 'nameserver_2', 'registration_term'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    jsonResponse(['success' => false, 'error' => "Missing required field: $field"], 400);
                }
            }
            
            // Check if domain already exists
            $checkSql = "SELECT id FROM domains WHERE domain_name = ?";
            $existing = dbQuery($checkSql, [$data['domain_name']]);
            
            if (!empty($existing)) {
                jsonResponse(['success' => false, 'error' => 'Domain already registered'], 409);
            }
            
            // Calculate dates
            $registrationDate = date('Y-m-d');
            $expiryDate = date('Y-m-d', strtotime("+{$data['registration_term']} year"));
            
            // Insert domain
            $sql = "INSERT INTO domains (domain_name, applicant_id, registrar_id, status, category, 
                    nameserver_1, nameserver_2, registration_date, expiry_date, registration_term, notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            dbExecute($sql, [
                $data['domain_name'],
                $data['applicant_id'],
                $data['registrar_id'],
                $data['status'] ?? 'active',
                $data['category'] ?? 'commercial',
                $data['nameserver_1'],
                $data['nameserver_2'],
                $registrationDate,
                $expiryDate,
                $data['registration_term'],
                $data['notes'] ?? null
            ]);
            
            $domainId = dbLastInsertId();
            
            // Create domain application
            $appSql = "INSERT INTO domain_applications (domain_id, applicant_id, registrar_id, submission_status, notes)
                       VALUES (?, ?, ?, ?, ?)";
            
            dbExecute($appSql, [
                $domainId,
                $data['applicant_id'],
                $data['registrar_id'],
                'submitted',
                $data['notes'] ?? null
            ]);
            
            jsonResponse(['success' => true, 'domain_id' => $domainId, 'message' => 'Domain registered successfully']);
            break;
            
        default:
            jsonResponse(['success' => false, 'error' => 'Invalid action'], 400);
    }
    
} catch (Exception $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
