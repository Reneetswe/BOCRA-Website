<?php
// To install dependencies open XAMPP Shell and run:
// cd C:\xampp\htdocs\bocra-website\backend
// composer install

// BOCRA-Website — POST /api/login.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/validator.php';

setCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  error('Method not allowed', 405);
}

$data   = getRequestBody();
$errors = validateRequired($data,
  ['email', 'password']);

if (!empty($errors)) {
  error('Validation failed', 422, $errors);
}

if (!validateEmail($data['email'])) {
  error('Invalid email format', 422);
}

$db   = getDB();
$stmt = $db->prepare(
  'SELECT * FROM users WHERE email = ?'
);
$stmt->execute([
  strtolower(trim($data['email']))
]);
$user = $stmt->fetch();

if (!$user || !password_verify(
  $data['password'],
  $user['password_hash']
)) {
  error('Invalid email or password', 401);
}

if ($user['status'] === 'suspended') {
  error('Account suspended. Contact BOCRA.', 403);
}

if ($user['locked_until'] &&
    strtotime($user['locked_until']) > time()) {
  $mins = ceil(
    (strtotime($user['locked_until']) - time()) / 60
  );
  error(
    "Account locked. Try again in {$mins} minute(s).",
    429
  );
}

$token     = generateToken();
$expiresAt = date(
  'Y-m-d H:i:s',
  time() + SESSION_EXPIRY
);

$db->prepare(
  'INSERT INTO sessions
   (user_id, token, is_2fa_done,
    ip_address, user_agent, expires_at)
   VALUES (?, ?, 0, ?, ?, ?)'
)->execute([
  $user['id'],
  $token,
  $_SERVER['REMOTE_ADDR']  ?? null,
  $_SERVER['HTTP_USER_AGENT'] ?? null,
  $expiresAt,
]);

$db->prepare(
  'UPDATE users
   SET last_login = NOW(),
       failed_2fa_attempts = 0
   WHERE id = ?'
)->execute([$user['id']]);

logAction(
  $user['id'],
  'LOGIN_CREDENTIALS_OK',
  $user['email']
);

success([
  'token'        => $token,
  'requires_2fa' =>
    (bool) $user['two_factor_enabled'],
  'user' => [
    'id'           => $user['id'],
    'first_name'   => $user['first_name'],
    'last_name'    => $user['last_name'],
    'email'        => $user['email'],
    'account_type' => $user['account_type'],
  ],
], 'Credentials verified');
