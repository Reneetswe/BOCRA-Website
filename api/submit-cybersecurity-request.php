<?php
/**
 * API: Submit Cybersecurity Service Request
 * Handles cybersecurity service requests from public portal
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/../backend/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $required = ['organization_name', 'contact_person', 'contact_email', 'contact_phone', 'sector', 'organization_size', 'service_type', 'description'];
    
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Generate request number
    $year = date('Y');
    $sql = "SELECT COUNT(*) as count FROM cybersecurity_requests WHERE YEAR(submitted_at) = YEAR(NOW())";
    $result = dbQuery($sql);
    $count = $result[0]['count'] + 1;
    $request_number = sprintf('CYB-%s-%03d', $year, $count);
    
    // Determine urgency
    $urgency = $data['urgency'] ?? 'routine';
    
    // Insert request
    $sql = "INSERT INTO cybersecurity_requests (
        request_number, organization_name, contact_person, contact_email, contact_phone,
        sector, organization_size, service_type, urgency, status, description,
        specific_requirements, preferred_date, submitted_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'submitted', ?, ?, ?, NOW())";
    
    dbQuery($sql, [
        $request_number,
        sanitize($data['organization_name']),
        sanitize($data['contact_person']),
        sanitize($data['contact_email']),
        sanitize($data['contact_phone']),
        sanitize($data['sector']),
        sanitize($data['organization_size']),
        sanitize($data['service_type']),
        $urgency,
        sanitize($data['description']),
        sanitize($data['specific_requirements'] ?? null),
        $data['preferred_date'] ?? null
    ]);
    
    $request_id = getDB()->lastInsertId();
    
    // Create initial update
    $sql = "INSERT INTO cybersecurity_updates (request_id, update_type, message) 
            VALUES (?, 'status_change', 'Request received and logged. Our cybersecurity team will review your request and contact you shortly.')";
    dbQuery($sql, [$request_id]);
    
    // Create notification for cybersecurity admin
    $sql = "INSERT INTO notifications (user_id, type, title, message, link) 
            SELECT id, 'cyber_update', 'New Cybersecurity Request', 
            CONCAT('New ', ?, ' request from ', ?),
            CONCAT('/cybersecurity-admin/manage-request.php?id=', ?)
            FROM users WHERE role = 'cybersecurity_admin' LIMIT 1";
    dbQuery($sql, [$data['service_type'], $data['organization_name'], $request_id]);
    
    // Log audit
    logAudit('System', 'system', 'cybersecurity_request_submitted', 'cybersecurity_request', $request_id, 
             "New cybersecurity request submitted: $request_number");
    
    // Send response
    echo json_encode([
        'success' => true,
        'message' => 'Request submitted successfully',
        'request_number' => $request_number,
        'request_id' => $request_id,
        'urgency' => $urgency
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
