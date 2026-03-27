<?php
// To install dependencies open XAMPP Shell and run:
// cd C:\xampp\htdocs\bocra-website\backend
// composer install

// BOCRA-Website — GET /api/me.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../middleware/auth.php';

setCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  error('Method not allowed', 405);
}

$session = requireAuth();
$db      = getDB();

$stmt = $db->prepare(
  'SELECT id, first_name, last_name,
          email, phone, account_type,
          company_name, company_registration,
          status, two_factor_confirmed,
          created_at, last_login
   FROM users WHERE id = ?'
);
$stmt->execute([$session['uid']]);
$user = $stmt->fetch();

if (!$user) {
  error('User not found', 404);
}

success(['user' => $user]);
