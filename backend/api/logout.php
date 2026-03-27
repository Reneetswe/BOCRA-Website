<?php
// To install dependencies open XAMPP Shell and run:
// cd C:\xampp\htdocs\bocra-website\backend
// composer install

// BOCRA-Website — POST /api/logout.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';

setCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  error('Method not allowed', 405);
}

$token = getToken();
if ($token) {
  getDB()->prepare(
    'DELETE FROM sessions WHERE token = ?'
  )->execute([$token]);
}

success(null, 'Logged out successfully');
