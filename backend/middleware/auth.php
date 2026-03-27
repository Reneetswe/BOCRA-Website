<?php
// To install dependencies open XAMPP Shell and run:
// cd C:\xampp\htdocs\bocra-website\backend
// composer install

// BOCRA-Website — Auth Middleware

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';

function requireAuth(
  bool $require2FA = true
): array {
  $token = getToken();

  if (!$token) {
    error('Unauthorised — no token provided', 401);
  }

  $db   = getDB();
  $stmt = $db->prepare(
    'SELECT s.*,
            u.id           AS uid,
            u.first_name,
            u.last_name,
            u.email,
            u.account_type,
            u.company_name,
            u.status,
            u.two_factor_confirmed,
            u.two_factor_secret
     FROM sessions s
     JOIN users u ON s.user_id = u.id
     WHERE s.token = ?
       AND s.expires_at > NOW()'
  );
  $stmt->execute([$token]);
  $session = $stmt->fetch();

  if (!$session) {
    error('Unauthorised — session expired or invalid',
      401);
  }

  if ($session['status'] === 'suspended') {
    error('Account suspended. Contact BOCRA.', 403);
  }

  if ($require2FA && !$session['is_2fa_done']) {
    error('2FA verification required', 403);
  }

  return $session;
}
