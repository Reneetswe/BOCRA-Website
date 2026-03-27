<?php
/**
 * API: Get My Applications
 * Returns all applications submitted by the logged-in user
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../backend/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get email from query parameter
    $email = $_GET['email'] ?? '';
    
    if (empty($email)) {
        throw new Exception("Email parameter is required");
    }
    
    // Get all applications for this email
    $sql = "SELECT * FROM license_applications WHERE applicant_email = ? ORDER BY submission_date DESC";
    $applications = dbQuery($sql, [$email]);
    
    // Get complaints for this email
    $sql = "SELECT * FROM complaints WHERE complainant_email = ? ORDER BY submitted_at DESC";
    $complaints = dbQuery($sql, [$email]);
    
    // Get cybersecurity requests for this email
    $sql = "SELECT * FROM cybersecurity_requests WHERE contact_email = ? ORDER BY submitted_at DESC";
    $cyber_requests = dbQuery($sql, [$email]);
    
    // Send response
    echo json_encode([
        'success' => true,
        'applications' => $applications,
        'complaints' => $complaints,
        'cyber_requests' => $cyber_requests
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
