<?php
/**
 * BOCRA Domain Registration System - Applicants API
 */

require_once __DIR__ . '/../backend/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            // Get all applicants for a registrar
            $registrarId = $_GET['registrar_id'] ?? null;
            
            if (!$registrarId) {
                jsonResponse(['success' => false, 'error' => 'Registrar ID required'], 400);
            }
            
            $sql = "SELECT * FROM applicants WHERE registrar_id = ? ORDER BY created_at DESC";
            $applicants = dbQuery($sql, [$registrarId]);
            
            jsonResponse(['success' => true, 'data' => $applicants]);
            break;
            
        case 'create':
            // Create new applicant (registrar only)
            if ($method !== 'POST') {
                jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            $required = ['registrar_id', 'type', 'email', 'phone'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    jsonResponse(['success' => false, 'error' => "Missing required field: $field"], 400);
                }
            }
            
            // Type-specific validation
            if ($data['type'] === 'individual' && empty($data['full_name'])) {
                jsonResponse(['success' => false, 'error' => 'Full name required for individual'], 400);
            }
            
            if ($data['type'] === 'company' && empty($data['company_name'])) {
                jsonResponse(['success' => false, 'error' => 'Company name required for company'], 400);
            }
            
            // Insert applicant
            $sql = "INSERT INTO applicants (registrar_id, type, full_name, company_name, national_id, 
                    registration_number, tax_number, email, phone, address, contact_person)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            dbExecute($sql, [
                $data['registrar_id'],
                $data['type'],
                $data['full_name'] ?? null,
                $data['company_name'] ?? null,
                $data['national_id'] ?? null,
                $data['registration_number'] ?? null,
                $data['tax_number'] ?? null,
                $data['email'],
                $data['phone'],
                $data['address'] ?? null,
                $data['contact_person'] ?? null
            ]);
            
            $applicantId = dbLastInsertId();
            
            jsonResponse(['success' => true, 'applicant_id' => $applicantId, 'message' => 'Applicant created successfully']);
            break;
            
        default:
            jsonResponse(['success' => false, 'error' => 'Invalid action'], 400);
    }
    
} catch (Exception $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
