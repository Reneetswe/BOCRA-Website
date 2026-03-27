<?php
// To install dependencies open XAMPP Shell and run:
// cd C:\xampp\htdocs\bocra-website\backend
// composer install

// BOCRA-Website — Response & Utility Helpers

require_once __DIR__ . '/../config/database.php';

function setCorsHeaders(): void {
  $origin  = $_SERVER['HTTP_ORIGIN'] ?? '';
  $allowed = defined('ALLOWED_ORIGINS')
    ? ALLOWED_ORIGINS : [];

  if (in_array($origin, $allowed, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
  } else {
    header(
      'Access-Control-Allow-Origin: ' .
      'http://localhost/bocra-website'
    );
  }

  header('Access-Control-Allow-Credentials: true');
  header(
    'Access-Control-Allow-Methods: ' .
    'GET, POST, PUT, DELETE, OPTIONS'
  );
  header(
    'Access-Control-Allow-Headers: ' .
    'Content-Type, Authorization, X-Requested-With'
  );
  header('Content-Type: application/json; charset=UTF-8');

  if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
  }
}

function success(
  mixed  $data    = null,
  string $message = 'OK',
  int    $code    = 200
): never {
  http_response_code($code);
  echo json_encode([
    'success' => true,
    'message' => $message,
    'data'    => $data,
  ]);
  exit;
}

function error(
  string $message,
  int    $code   = 400,
  array  $errors = []
): never {
  http_response_code($code);
  $payload = [
    'success' => false,
    'error'   => $message,
  ];
  if (!empty($errors)) {
    $payload['errors'] = $errors;
  }
  echo json_encode($payload);
  exit;
}

function getRequestBody(): array {
  $raw  = file_get_contents('php://input');
  $data = json_decode($raw, true);
  return is_array($data) ? $data : [];
}

function getToken(): ?string {
  // Check multiple possible locations for Authorization header
  $auth = $_SERVER['HTTP_AUTHORIZATION'] 
       ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
       ?? apache_request_headers()['Authorization']
       ?? '';
  
  if (str_starts_with($auth, 'Bearer ')) {
    return substr($auth, 7);
  }
  return null;
}

function generateToken(): string {
  return bin2hex(random_bytes(32));
}

function generateReference(): string {
  $db    = getDB();
  $count = (int) $db
    ->query('SELECT COUNT(*) FROM applications')
    ->fetchColumn();
  return 'APP-' . date('Y') . '-' .
    str_pad($count + 1, 4, '0', STR_PAD_LEFT);
}

function logAction(
  ?int   $userId,
  string $action,
  string $detail = ''
): void {
  try {
    $db   = getDB();
    $stmt = $db->prepare(
      'INSERT INTO audit_log
       (user_id, action, detail, ip_address)
       VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([
      $userId,
      $action,
      $detail,
      $_SERVER['REMOTE_ADDR'] ?? null,
    ]);
  } catch (Throwable) {
    // Logging must never break a request
  }
}
