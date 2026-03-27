<?php
// To install dependencies open XAMPP Shell and run:
// cd C:\xampp\htdocs\bocra-website\backend
// composer install

// BOCRA-Website — GET /api/health.php
// Health check endpoint for monitoring system status

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/response.php';

setCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  error('Method not allowed', 405);
}

$health = [
  'status' => 'healthy',
  'timestamp' => date('Y-m-d H:i:s'),
  'version' => '1.0.0',
  'services' => []
];

// Check database connection
try {
  $db = getDB();
  $db->query('SELECT 1');
  $health['services']['database'] = [
    'status' => 'healthy',
    'message' => 'Database connection successful'
  ];
  
  // Check table count
  $tables = $db->query('SHOW TABLES')->fetchAll();
  $health['services']['database']['tables'] = count($tables);
  
} catch (Exception $e) {
  $health['services']['database'] = [
    'status' => 'unhealthy',
    'message' => 'Database connection failed: ' . $e->getMessage()
  ];
  $health['status'] = 'degraded';
}

// Check storage directories
$storagePaths = [
  'documents' => STORAGE_PATH . 'documents',
  'forms' => STORAGE_PATH . 'forms', 
  'logs' => STORAGE_PATH . 'logs'
];

foreach ($storagePaths as $name => $path) {
  if (is_dir($path) && is_writable($path)) {
    $health['services']['storage'][$name] = [
      'status' => 'healthy',
      'message' => 'Directory exists and writable'
    ];
  } else {
    $health['services']['storage'][$name] = [
      'status' => 'unhealthy',
      'message' => 'Directory missing or not writable'
    ];
    $health['status'] = 'degraded';
  }
}

// Check PHP extensions
$requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'mbstring', 'openssl'];
$health['services']['php_extensions'] = [];

foreach ($requiredExtensions as $ext) {
  if (extension_loaded($ext)) {
    $health['services']['php_extensions'][$ext] = [
      'status' => 'healthy',
      'loaded' => true
    ];
  } else {
    $health['services']['php_extensions'][$ext] = [
      'status' => 'unhealthy',
      'loaded' => false
    ];
    $health['status'] = 'degraded';
  }
}

// Check composer dependencies
$composerLock = __DIR__ . '/../vendor/autoload.php';
if (file_exists($composerLock)) {
  $health['services']['composer'] = [
    'status' => 'healthy',
    'message' => 'Dependencies installed'
  ];
} else {
  $health['services']['composer'] = [
    'status' => 'unhealthy',
    'message' => 'Run: composer install'
  ];
  $health['status'] = 'degraded';
}

// Set appropriate HTTP status code
$statusCode = $health['status'] === 'healthy' ? 200 : 503;
http_response_code($statusCode);

echo json_encode($health, JSON_PRETTY_PRINT);
exit;
