<?php
// To install dependencies open XAMPP Shell and run:
// cd C:\xampp\htdocs\bocra-website\backend
// composer install

// BOCRA-Website — GET|POST /api/applications.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/validator.php';
require_once __DIR__ . '/../helpers/mailer.php';
require_once __DIR__ . '/../middleware/auth.php';

setCorsHeaders();

$session = requireAuth();
$db      = getDB();
$method  = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
  $stmt = $db->prepare(
    'SELECT a.*,
            COUNT(d.id) AS document_count
     FROM applications a
     LEFT JOIN documents d
       ON d.application_id = a.id
     WHERE a.user_id = ?
     GROUP BY a.id
     ORDER BY a.submitted_at DESC'
  );
  $stmt->execute([$session['uid']]);
  success([
    'applications' => $stmt->fetchAll()
  ]);
}

if ($method === 'POST') {
  $data   = getRequestBody();
  $errors = validateRequired($data,
    ['license_type']);

  if (!empty($errors)) {
    error('Validation failed', 422, $errors);
  }

  $reference = generateReference();

  $db->prepare(
    'INSERT INTO applications
     (user_id, reference, license_type, status)
     VALUES (?, ?, ?, "submitted")'
  )->execute([
    $session['uid'],
    $reference,
    sanitize($data['license_type']),
  ]);

  $appId = (int) $db->lastInsertId();

  if (!empty($data['document_id'])) {
    $db->prepare(
      'UPDATE documents
       SET application_id = ?
       WHERE id = ?
         AND application_id IS NULL'
    )->execute([
      $appId,
      (int) $data['document_id'],
    ]);
  }

  $app = $db->prepare(
    'SELECT * FROM applications WHERE id = ?'
  );
  $app->execute([$appId]);
  $application = $app->fetch();

  $usr = $db->prepare(
    'SELECT * FROM users WHERE id = ?'
  );
  $usr->execute([$session['uid']]);
  $user = $usr->fetch();

  emailApplicationSubmitted($user, $application);

  logAction(
    $session['uid'],
    'APPLICATION_SUBMITTED',
    $reference
  );

  success([
    'application' => $application,
    'reference'   => $reference,
  ], 'Application submitted successfully', 201);
}

error('Method not allowed', 405);
