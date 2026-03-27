<?php
// To install dependencies open XAMPP Shell and run:
// cd C:\xampp\htdocs\bocra-website\backend
// composer install

// BOCRA-Website — GET /api/download-form.php
// Usage: download-form.php?type=cellular-licence

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../middleware/auth.php';

setCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  error('Method not allowed', 405);
}

requireAuth();

$licenseType = $_GET['type'] ?? '';
if (empty($licenseType)) {
  error('License type parameter is required', 422);
}

$slug = strtolower(
  preg_replace(
    '/[^a-zA-Z0-9]+/', '-', trim($licenseType)
  )
);

$formPath = STORAGE_PATH . 'forms' .
  DIRECTORY_SEPARATOR . $slug . '.pdf';

if (!file_exists($formPath)) {
  $formPath = STORAGE_PATH . 'forms' .
    DIRECTORY_SEPARATOR .
    'general-application-form.pdf';
}

if (!file_exists($formPath)) {
  error(
    'Form not yet available for this license type. ' .
    'Contact licensing@bocra.org.bw for assistance.',
    404
  );
}

$fileName = 'BOCRA-Application-' .
  ucwords($slug, '-') . '.pdf';

header('Content-Type: application/pdf');
header(
  'Content-Disposition: attachment; filename="' .
  $fileName . '"'
);
header('Content-Length: ' . filesize($formPath));
header('Cache-Control: no-cache, no-store');
readfile($formPath);
exit;
