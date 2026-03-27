<?php
// Test authentication header

header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Get all headers
$headers = getallheaders();

// Check for Authorization header
$auth = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

echo json_encode([
    'all_headers' => $headers,
    'http_authorization' => $auth,
    'server_vars' => [
        'HTTP_AUTHORIZATION' => $_SERVER['HTTP_AUTHORIZATION'] ?? 'NOT SET',
        'REDIRECT_HTTP_AUTHORIZATION' => $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? 'NOT SET'
    ]
]);
