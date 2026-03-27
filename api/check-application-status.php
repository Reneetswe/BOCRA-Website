<?php
/**
 * API: Check License Application Status
 * Allows applicants to check their application status
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
    $application_number = $_GET['application_number'] ?? '';
    $email = $_GET['email'] ?? '';
    
    if (empty($application_number) || empty($email)) {
        throw new Exception('Application number and email are required');
    }
    
    // Fetch application
    $sql = "SELECT * FROM license_applications WHERE application_number = ? AND applicant_email = ?";
    $result = dbQuery($sql, [$application_number, $email]);
    
    if (empty($result)) {
        throw new Exception('Application not found or email does not match');
    }
    
    $application = $result[0];
    
    // Fetch status history
    $sql = "SELECT h.*, u.name as changed_by_name 
            FROM license_status_history h 
            LEFT JOIN users u ON h.changed_by = u.id 
            WHERE h.application_id = ? 
            ORDER BY h.created_at DESC";
    $history = dbQuery($sql, [$application['id']]);
    
    // Prepare response
    $response = [
        'success' => true,
        'application' => [
            'application_number' => $application['application_number'],
            'applicant_name' => $application['applicant_name'],
            'license_type' => $application['license_type'],
            'status' => $application['status'],
            'submission_date' => $application['submission_date'],
            'reviewed_at' => $application['reviewed_at'],
            'reviewer_notes' => $application['reviewer_notes'],
            'rejection_reason' => $application['rejection_reason']
        ],
        'status_history' => array_map(function($item) {
            return [
                'status' => $item['new_status'],
                'changed_by' => $item['changed_by_name'],
                'notes' => $item['notes'],
                'date' => $item['created_at']
            ];
        }, $history)
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
