<?php
// To install dependencies open XAMPP Shell and run:
// cd C:\xampp\htdocs\bocra-website\backend
// composer install

// BOCRA-Website — POST /api/verify-otp.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PragmaRX\Google2FA\Google2FA;

setCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  error('Method not allowed', 405);
}

$session = requireAuth(false);
$data    = getRequestBody();

if (empty($data['code'])) {
  error('Verification code is required', 422);
}

$code = preg_replace('/\D/', '', $data['code']);

if (strlen($code) !== 6) {
  error('Code must be exactly 6 digits', 422);
}

$db   = getDB();
$stmt = $db->prepare(
  'SELECT * FROM users WHERE id = ?'
);
$stmt->execute([$session['uid']]);
$user = $stmt->fetch();

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

$google2fa = new Google2FA();
$valid     = $google2fa->verifyKey(
  $user['two_factor_secret'],
  $code,
  1
);

if (!$valid) {
  $attempts = $user['failed_2fa_attempts'] + 1;

  if ($attempts >= 3) {
    $lockUntil = date(
      'Y-m-d H:i:s', time() + 600
    );
    $db->prepare(
      'UPDATE users
       SET failed_2fa_attempts = ?,
           locked_until = ?
       WHERE id = ?'
    )->execute([$attempts, $lockUntil, $user['id']]);
    error(
      'Too many failed attempts. ' .
      'Account locked for 10 minutes.',
      429
    );
  }

  $db->prepare(
    'UPDATE users
     SET failed_2fa_attempts = ?
     WHERE id = ?'
  )->execute([$attempts, $user['id']]);

  error(
    'Invalid code. ' .
    (3 - $attempts) . ' attempt(s) remaining.',
    422,
    ['attempts_remaining' => 3 - $attempts]
  );
}

$db->prepare(
  'UPDATE sessions
   SET is_2fa_done = 1
   WHERE token = ?'
)->execute([$session['token']]);

$db->prepare(
  'UPDATE users
   SET two_factor_confirmed = 1,
       failed_2fa_attempts  = 0,
       locked_until         = NULL,
       status               = "active"
   WHERE id = ?'
)->execute([$user['id']]);

logAction(
  $user['id'], '2FA_VERIFIED', $user['email']
);

success([
  'user' => [
    'id'           => $user['id'],
    'first_name'   => $user['first_name'],
    'last_name'    => $user['last_name'],
    'email'        => $user['email'],
    'account_type' => $user['account_type'],
    'company_name' => $user['company_name'],
  ],
], '2FA verified successfully');
