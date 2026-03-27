<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/database.php';

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

$required = ['full_name', 'email', 'phone', 'account_type', 'industry_sector', 'services', 'priority_level'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

if (empty($data['declaration_accuracy']) || empty($data['declaration_consent'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Required declarations must be confirmed']);
    exit;
}

$year = date('Y');
$random = str_pad(mt_rand(1000, 9999), 4, '0', STR_PAD_LEFT);
$reference_number = "BOCRA/CYB/{$year}/{$random}";

$check_sql = "SELECT id FROM cybersecurity_requests WHERE reference_number = ?";
$check_stmt = $pdo->prepare($check_sql);
$check_stmt->execute([$reference_number]);
if ($check_stmt->fetch()) {
    $random = str_pad(mt_rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    $reference_number = "BOCRA/CYB/{$year}/{$random}";
}

$services_json = is_array($data['services']) ? json_encode($data['services']) : $data['services'];

try {
    $sql = "INSERT INTO cybersecurity_requests (
        full_name, email, phone, organisation_name, account_type, 
        industry_sector, services, additional_info, priority_level,
        declaration_accuracy, declaration_consent, declaration_updates,
        reference_number, status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $data['full_name'],
        $data['email'],
        $data['phone'],
        $data['organisation_name'] ?? null,
        $data['account_type'],
        $data['industry_sector'],
        $services_json,
        $data['additional_info'] ?? null,
        $data['priority_level'],
        !empty($data['declaration_accuracy']) ? 1 : 0,
        !empty($data['declaration_consent']) ? 1 : 0,
        !empty($data['declaration_updates']) ? 1 : 0,
        $reference_number
    ]);
    
    if ($result) {
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Cybersecurity request submitted successfully',
            'reference_number' => $reference_number,
            'id' => $pdo->lastInsertId()
        ]);
    } else {
        throw new Exception('Failed to insert record');
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
