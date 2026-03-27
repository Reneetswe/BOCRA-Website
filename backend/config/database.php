<?php
// To install dependencies open XAMPP Shell and run:
// cd C:\xampp\htdocs\bocra-website\backend
// composer install

// BOCRA-Website — Database Configuration
// Project: C:\xampp\htdocs\bocra-website\
// Database: bocra_website

define('DB_HOST',    'localhost');
define('DB_NAME',    'bocra_website');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
  static $pdo = null;
  if ($pdo !== null) return $pdo;

  $dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=%s',
    DB_HOST, DB_NAME, DB_CHARSET
  );
  $options = [
    PDO::ATTR_ERRMODE            =>
      PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE =>
      PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
  ];
  try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
  } catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
      'success' => false,
      'error'   => 'Database connection failed. ' .
                   'Check XAMPP MySQL is running.'
    ]);
    exit;
  }
  return $pdo;
}
