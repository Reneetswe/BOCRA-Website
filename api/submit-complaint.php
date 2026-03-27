<?php
/**
 * API: Submit Complaint
 * Handles complaint submissions from public portal
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
require_once 'cors-header.php';
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
    $required = ['complainant_name', 'complainant_email', 'complainant_phone', 'complaint_type', 'sector', 'subject', 'description'];
    
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Generate complaint number
    $year = date('Y');
    $sql = "SELECT COUNT(*) as count FROM complaints WHERE YEAR(submitted_at) = YEAR(NOW())";
    $result = dbQuery($sql);
    $count = $result[0]['count'] + 1;
    $complaint_number = sprintf('CMP-%s-%03d', $year, $count);
    
    // Determine priority based on complaint type
    $priority = 'medium';
    if (in_array($data['complaint_type'], ['fraud', 'data_privacy'])) {
        $priority = 'high';
    } elseif ($data['complaint_type'] === 'network_outage') {
        $priority = 'high';
    }
    
    // Insert complaint
    $sql = "INSERT INTO complaints (
        complaint_number, complainant_name, complainant_email, complainant_phone,
        complaint_type, service_provider, sector, priority, status, subject, description,
        desired_outcome, submitted_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'submitted', ?, ?, ?, NOW())";
    
    dbQuery($sql, [
        $complaint_number,
        sanitize($data['complainant_name']),
        sanitize($data['complainant_email']),
        sanitize($data['complainant_phone']),
        sanitize($data['complaint_type']),
        sanitize($data['service_provider'] ?? null),
        sanitize($data['sector']),
        $priority,
        sanitize($data['subject']),
        sanitize($data['description']),
        sanitize($data['desired_outcome'] ?? null)
    ]);
    
    $complaint_id = getDB()->lastInsertId();
    
    // Create initial update
    $sql = "INSERT INTO complaint_updates (complaint_id, update_type, message, is_visible_to_user) 
            VALUES (?, 'status_change', 'Complaint received and logged in our system. A complaints officer will review your case shortly.', TRUE)";
    dbQuery($sql, [$complaint_id]);
    
    // Create notification for complaints admin
    $sql = "INSERT INTO notifications (user_id, type, title, message, link) 
            SELECT id, 'complaint_update', 'New Complaint Received', 
            CONCAT('New ', ?, ' complaint from ', ?),
            CONCAT('/complaints-admin/resolve-complaint.php?id=', ?)
            FROM users WHERE role = 'complaints_admin' LIMIT 1";
    dbQuery($sql, [$data['complaint_type'], $data['complainant_name'], $complaint_id]);
    
    // Log audit
    logAudit('System', 'system', 'complaint_submitted', 'complaint', $complaint_id, 
             "New complaint submitted: $complaint_number");
    
    // Send response
    echo json_encode([
        'success' => true,
        'message' => 'Complaint submitted successfully',
        'complaint_number' => $complaint_number,
        'complaint_id' => $complaint_id,
        'priority' => $priority
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
