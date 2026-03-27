<?php
// Create cybersecurity_requests table
require_once __DIR__ . '/config/database.php';

$pdo = getDB();

$sql = "CREATE TABLE IF NOT EXISTS cybersecurity_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    organisation_name VARCHAR(255),
    account_type VARCHAR(100) NOT NULL,
    industry_sector VARCHAR(100) NOT NULL,
    services TEXT NOT NULL,
    additional_info TEXT,
    priority_level VARCHAR(50) NOT NULL DEFAULT 'standard',
    declaration_accuracy TINYINT(1) NOT NULL DEFAULT 0,
    declaration_consent TINYINT(1) NOT NULL DEFAULT 0,
    declaration_updates TINYINT(1) NOT NULL DEFAULT 0,
    reference_number VARCHAR(50) UNIQUE NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_reference (reference_number),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $pdo->exec($sql);
    echo json_encode([
        'success' => true,
        'message' => 'Cybersecurity requests table created successfully'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error creating table: ' . $e->getMessage()
    ]);
}
?>
