<?php
// To install dependencies open XAMPP Shell and run:
// cd C:\xampp\htdocs\bocra-website\backend
// composer install

// BOCRA-Website — POST /api/register.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/validator.php';
require_once __DIR__ . '/../helpers/logger.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PragmaRX\Google2FA\Google2FA;

setCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  error('Method not allowed', 405);
}

logApiRequest('register.php', 'POST', getRequestBody());

$data = getRequestBody();

// Rate limiting check
$clientId = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!rateLimitCheck($clientId, 5, 300)) {
  error('Too many registration attempts. Please try again later.', 429);
}

$errors = validateRequired($data, [
  'first_name', 'last_name',
  'email', 'password', 'account_type',
]);

if (!empty($errors)) {
  error('Validation failed', 422, $errors);
}

if (!validateEmail($data['email'])) {
  error('Invalid email address', 422,
    ['email' => 'Please enter a valid email address']);
}

// Enhanced password validation
$passwordErrors = validatePassword($data['password']);
if (!empty($passwordErrors)) {
  error('Password requirements not met', 422, [
    'password' => implode(' ', $passwordErrors)
  ]);
}

if (($data['password'] ?? '') !==
    ($data['confirm_password'] ?? '')) {
  error('Passwords do not match', 422, [
    'confirm_password' => 'Passwords do not match',
  ]);
}

$accountType = $data['account_type'];
if (!in_array($accountType,
  ['individual', 'company', 'government'], true)) {
  error('Invalid account type', 422);
}

if ($accountType === 'company' &&
    empty($data['company_name'])) {
  error('Company name required', 422, [
    'company_name' => 'Company name is required',
  ]);
}

try {
  $db = getDB();

  $check = $db->prepare(
    'SELECT id FROM users WHERE email = ?'
  );
  $check->execute([
    strtolower(trim($data['email']))
  ]);
  if ($check->fetch()) {
    error('Email already registered', 409, [
      'email' =>
        'This email address is already registered',
    ]);
  }

  // Generate 2FA secret
  $google2fa = new Google2FA();
  $secret    = $google2fa->generateSecretKey();
  $qrUrl     = $google2fa->getQRCodeUrl(
    APP_NAME,
    $data['email'],
    $secret
  );

  $passwordHash = password_hash(
    $data['password'],
    PASSWORD_DEFAULT
  );

  $stmt = $db->prepare(
    'INSERT INTO users (
       first_name, last_name, email, phone,
       password_hash, account_type,
       company_name, company_registration,
       two_factor_secret, status
     ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "pending")'
  );
  $stmt->execute([
    sanitizeInput($data['first_name']),
    sanitizeInput($data['last_name']),
    strtolower(trim($data['email'])),
    sanitizeInput($data['phone'] ?? ''),
    $passwordHash,
    $accountType,
    sanitizeInput($data['company_name'] ?? ''),
    sanitizeInput($data['company_registration'] ?? ''),
    $secret,
  ]);

  $userId = (int) $db->lastInsertId();
  
  logAction($userId, 'REGISTER', $data['email']);
  logInfo("New user registered: {$data['email']}", ['user_id' => $userId]);

  success([
    'user_id'     => $userId,
    'secret'      => $secret,
    'qr_code_url' => $qrUrl,
  ], 'Account created successfully', 201);

} catch (PDOException $e) {
  logError('Database error during registration', [
    'error' => $e->getMessage(),
    'email' => $data['email'] ?? 'unknown'
  ]);
  error('Registration failed due to database error', 500);
} catch (Exception $e) {
  logError('Unexpected error during registration', [
    'error' => $e->getMessage(),
    'email' => $data['email'] ?? 'unknown'
  ]);
  error('Registration failed. Please try again.', 500);
}
