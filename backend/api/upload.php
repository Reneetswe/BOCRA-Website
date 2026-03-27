<?php
// To install dependencies open XAMPP Shell and run:
// cd C:\xampp\htdocs\bocra-website\backend
// composer install

// BOCRA-Website — POST /api/upload.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../middleware/auth.php';

setCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  error('Method not allowed', 405);
}

$session = requireAuth();

if (empty($_FILES['file'])) {
  error('No file was uploaded', 422);
}

$file  = $_FILES['file'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if ($mime !== 'application/pdf') {
  error('Only PDF files are accepted', 422);
}

if ($file['size'] > 10 * 1024 * 1024) {
  error('File exceeds  10MB limit', 422);
}

$userDir = STORAGE_PATH . 'documents' .
  DIRECTORY_SEPARATOR . $session['uid'] .
  DIRECTORY_SEPARATOR;

if (!is_dir($userDir)) {
  mkdir($userDir, 0755, true);
}

$safeName = time() . '_' .
  bin2hex(random_bytes(6)) . '.pdf';
$destPath = $userDir . $safeName;

if (!move_uploaded_file(
  $file['tmp_name'], $destPath
)) {
  error('Failed to save uploaded file', 500);
}

$db   = getDB();
$stmt = $db->prepare(
  'INSERT INTO documents
   (application_id, file_name,
    file_path, file_size_bytes)
   VALUES (NULL, ?, ?, ?)'
);
$stmt->execute([
  $file['name'],
  'documents/' . $session['uid'] .
    '/' . $safeName,
  $file['size'],
]);

$docId = (int) $db->lastInsertId();

logAction(
  $session['uid'],
  'DOCUMENT_UPLOADED',
  $file['name']
);

success([
  'document_id' => $docId,
  'file_name'   => $file['name'],
], 'File uploaded successfully');
