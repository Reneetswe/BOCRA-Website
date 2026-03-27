<?php
/**
 * API: Submit License Application
 * Handles license application submissions from public portal
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/../backend/config.php';

// Enable error logging
error_log("=== License Application Submission Started ===");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get POST data
    $raw_input = file_get_contents('php://input');
    error_log("Raw input: " . $raw_input);
    
    $data = json_decode($raw_input, true);
    error_log("Decoded data: " . print_r($data, true));
    
    // Validate required fields
    $required = ['applicant_name', 'applicant_email', 'applicant_phone', 'license_type', 'business_type', 'physical_address', 'business_description', 'proposed_services'];
    
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Generate application number
    $year = date('Y');
    $sql = "SELECT COUNT(*) as count FROM license_applications WHERE YEAR(submission_date) = YEAR(NOW())";
    $result = dbQuery($sql);
    $count = $result[0]['count'] + 1;
    $application_number = sprintf('LIC-%s-%04d', $year, $count);
    
    error_log("Generated application number: " . $application_number);
    
    // Insert application
    $sql = "INSERT INTO license_applications (
        application_number, applicant_name, applicant_email, applicant_phone,
        company_name, license_type, business_type, registration_number, tax_number,
        physical_address, postal_address, business_description, proposed_services,
        technical_capacity, financial_capacity, status, priority, submission_date
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'submitted', 'normal', NOW())";
    
    error_log("Executing INSERT query...");
    
    $params = [
        $application_number,
        sanitize($data['applicant_name']),
        sanitize($data['applicant_email']),
        sanitize($data['applicant_phone']),
        sanitize($data['company_name'] ?? ''),
        sanitize($data['license_type']),
        sanitize($data['business_type']),
        sanitize($data['registration_number'] ?? ''),
        sanitize($data['tax_number'] ?? ''),
        sanitize($data['physical_address']),
        sanitize($data['postal_address'] ?? ''),
        sanitize($data['business_description']),
        sanitize($data['proposed_services']),
        sanitize($data['technical_capacity'] ?? ''),
        sanitize($data['financial_capacity'] ?? '')
    ];
    
    error_log("Insert params: " . print_r($params, true));
    
    dbQuery($sql, $params);
    
    $application_id = getDB()->lastInsertId();
    error_log("Application inserted with ID: " . $application_id);
    
    // Create notification for all licensing admins
    $sql = "SELECT id FROM users WHERE role = 'licensing_admin'";
    $admins = dbQuery($sql);
    
    foreach ($admins as $admin) {
        $sql = "INSERT INTO notifications (user_id, type, title, message, link) 
                VALUES (?, 'license_update', 'New License Application', ?, ?)";
        $message = "New {$data['license_type']} license application received from {$data['applicant_name']}";
        $link = "view-application.php?id={$application_id}";
        dbQuery($sql, [$admin['id'], $message, $link]);
    }
    
    // Log audit
    logAudit('System', 'system', 'application_submitted', 'license_application', $application_id, 
             "New license application submitted: $application_number");
    
    // Send response
    echo json_encode([
        'success' => true,
        'message' => 'Application submitted successfully',
        'application_number' => $application_number,
        'application_id' => $application_id
    ]);
    
} catch (Exception $e) {
    error_log("ERROR in submit-license-application.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_details' => $e->getTraceAsString()
    ]);
}

error_log("=== License Application Submission Completed ===");
?>
