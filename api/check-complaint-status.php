<?php
/**
 * API: Check Complaint Status
 * Allows complainants to check their complaint status and updates
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
    $complaint_number = $_GET['complaint_number'] ?? '';
    $email = $_GET['email'] ?? '';
    
    if (empty($complaint_number) || empty($email)) {
        throw new Exception('Complaint number and email are required');
    }
    
    // Fetch complaint
    $sql = "SELECT * FROM complaints WHERE complaint_number = ? AND complainant_email = ?";
    $result = dbQuery($sql, [$complaint_number, $email]);
    
    if (empty($result)) {
        throw new Exception('Complaint not found or email does not match');
    }
    
    $complaint = $result[0];
    
    // Fetch updates visible to user
    $sql = "SELECT * FROM complaint_updates 
            WHERE complaint_id = ? AND is_visible_to_user = TRUE 
            ORDER BY created_at DESC";
    $updates = dbQuery($sql, [$complaint['id']]);
    
    // Prepare response
    $response = [
        'success' => true,
        'complaint' => [
            'complaint_number' => $complaint['complaint_number'],
            'complainant_name' => $complaint['complainant_name'],
            'complaint_type' => $complaint['complaint_type'],
            'sector' => $complaint['sector'],
            'subject' => $complaint['subject'],
            'status' => $complaint['status'],
            'priority' => $complaint['priority'],
            'submitted_at' => $complaint['submitted_at'],
            'resolution' => $complaint['resolution'],
            'resolution_date' => $complaint['resolution_date']
        ],
        'updates' => array_map(function($item) {
            return [
                'type' => $item['update_type'],
                'message' => $item['message'],
                'date' => $item['created_at']
            ];
        }, $updates)
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
