<?php
/**
 * BOCRA Website Database Setup Script
 * Run this script to set up the database for production
 */

// Prevent execution from web browser in production
if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.\n");
}

echo "🗄️  BOCRA Database Setup Script\n";
echo "===============================\n\n";

// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'bocra_registry';

// Create database connection without specifying database
try {
    $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connected to MySQL server\n";
} catch (PDOException $e) {
    echo "❌ Failed to connect to MySQL: " . $e->getMessage() . "\n";
    exit(1);
}

// Drop existing database if it exists
echo "📋 Dropping existing database (if exists)...\n";
try {
    $pdo->exec("DROP DATABASE IF EXISTS $db_name");
    echo "✅ Dropped existing database\n";
} catch (PDOException $e) {
    echo "⚠️  Could not drop database: " . $e->getMessage() . "\n";
}

// Create new database
echo "📋 Creating new database...\n";
try {
    $pdo->exec("CREATE DATABASE $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Created database: $db_name\n";
} catch (PDOException $e) {
    echo "❌ Failed to create database: " . $e->getMessage() . "\n";
    exit(1);
}

// Select the database
$pdo->exec("USE $db_name");

// Create tables
echo "📋 Creating tables...\n";

// Users table
$sql = "CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('registrar', 'bocra') NOT NULL,
    registrar_id INT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB";

try {
    $pdo->exec($sql);
    echo "✅ Created table: users\n";
} catch (PDOException $e) {
    echo "❌ Failed to create users table: " . $e->getMessage() . "\n";
}

// Registrars table
$sql = "CREATE TABLE registrars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    accreditation_number VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    address TEXT,
    status ENUM('active', 'suspended', 'revoked') DEFAULT 'active',
    accreditation_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_accreditation (accreditation_number)
) ENGINE=InnoDB";

try {
    $pdo->exec($sql);
    echo "✅ Created table: registrars\n";
} catch (PDOException $e) {
    echo "❌ Failed to create registrars table: " . $e->getMessage() . "\n";
}

// Applicants table
$sql = "CREATE TABLE applicants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registrar_id INT NOT NULL,
    type ENUM('individual', 'company') NOT NULL,
    full_name VARCHAR(255),
    company_name VARCHAR(255),
    national_id VARCHAR(50),
    registration_number VARCHAR(50),
    tax_number VARCHAR(50),
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_registrar (registrar_id),
    INDEX idx_email (email),
    INDEX idx_type (type)
) ENGINE=InnoDB";

try {
    $pdo->exec($sql);
    echo "✅ Created table: applicants\n";
} catch (PDOException $e) {
    echo "❌ Failed to create applicants table: " . $e->getMessage() . "\n";
}

// Domains table
$sql = "CREATE TABLE domains (
    id INT AUTO_INCREMENT PRIMARY KEY,
    applicant_id INT NOT NULL,
    domain_name VARCHAR(255) NOT NULL UNIQUE,
    domain_type ENUM('new', 'transfer', 'renewal') NOT NULL,
    status ENUM('pending', 'under_review', 'approved', 'rejected', 'active', 'suspended', 'expired', 'cancelled') DEFAULT 'pending',
    registration_date DATE,
    expiry_date DATE,
    purpose TEXT,
    nameservers JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_applicant (applicant_id),
    INDEX idx_domain_name (domain_name),
    INDEX idx_status (status)
) ENGINE=InnoDB";

try {
    $pdo->exec($sql);
    echo "✅ Created table: domains\n";
} catch (PDOException $e) {
    echo "❌ Failed to create domains table: " . $e->getMessage() . "\n";
}

// Applications table
$sql = "CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    applicant_id INT NOT NULL,
    domain_id INT NULL,
    type ENUM('new_registration', 'transfer', 'renewal', 'modification') NOT NULL,
    status ENUM('draft', 'submitted', 'under_review', 'approved', 'rejected', 'cancelled') DEFAULT 'draft',
    data JSON,
    documents JSON,
    submitted_at TIMESTAMP NULL,
    reviewed_at TIMESTAMP NULL,
    reviewed_by INT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_applicant (applicant_id),
    INDEX idx_domain (domain_id),
    INDEX idx_status (status),
    INDEX idx_type (type)
) ENGINE=InnoDB";

try {
    $pdo->exec($sql);
    echo "✅ Created table: applications\n";
} catch (PDOException $e) {
    echo "❌ Failed to create applications table: " . $e->getMessage() . "\n";
}

// Complaints table
$sql = "CREATE TABLE complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reference_number VARCHAR(50) NOT NULL UNIQUE,
    complainant_name VARCHAR(255) NOT NULL,
    complainant_email VARCHAR(255) NOT NULL,
    complainant_phone VARCHAR(50),
    complaint_type ENUM('service_quality', 'billing', 'network', 'customer_service', 'unfair_practices', 'other') NOT NULL,
    provider_name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    status ENUM('submitted', 'under_review', 'investigating', 'resolved', 'dismissed') DEFAULT 'submitted',
    assigned_to INT NULL,
    resolution TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_reference (reference_number),
    INDEX idx_status (status),
    INDEX idx_severity (severity),
    INDEX idx_provider (provider_name)
) ENGINE=InnoDB";

try {
    $pdo->exec($sql);
    echo "✅ Created table: complaints\n";
} catch (PDOException $e) {
    echo "❌ Failed to create complaints table: " . $e->getMessage() . "\n";
}

// Cybersecurity incidents table
$sql = "CREATE TABLE cybersecurity_incidents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    incident_number VARCHAR(50) NOT NULL UNIQUE,
    reporter_name VARCHAR(255) NOT NULL,
    reporter_email VARCHAR(255) NOT NULL,
    reporter_phone VARCHAR(50),
    organization VARCHAR(255),
    incident_type ENUM('malware', 'phishing', 'ddos', 'data_breach', 'unauthorized_access', 'other') NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    description TEXT NOT NULL,
    affected_systems TEXT,
    status ENUM('reported', 'under_investigation', 'mitigated', 'resolved', 'closed') DEFAULT 'reported',
    assigned_to INT NULL,
    resolution TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_incident (incident_number),
    INDEX idx_status (status),
    INDEX idx_severity (severity),
    INDEX idx_type (incident_type)
) ENGINE=InnoDB";

try {
    $pdo->exec($sql);
    echo "✅ Created table: cybersecurity_incidents\n";
} catch (PDOException $e) {
    echo "❌ Failed to create cybersecurity_incidents table: " . $e->getMessage() . "\n";
}

// Audit logs table
$sql = "CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    actor_name VARCHAR(255) NOT NULL,
    actor_role VARCHAR(50) NOT NULL,
    action VARCHAR(255) NOT NULL,
    entity_type VARCHAR(100),
    entity_id VARCHAR(100),
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_actor (actor_name),
    INDEX idx_action (action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB";

try {
    $pdo->exec($sql);
    echo "✅ Created table: audit_logs\n";
} catch (PDOException $e) {
    echo "❌ Failed to create audit_logs table: " . $e->getMessage() . "\n";
}

// Insert default data
echo "📋 Inserting default data...\n";

// Create default BOCRA admin user
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute ['BOCRA Administrator', 'admin@bocra.org.bw', $admin_password, 'bocra']);
    echo "✅ Created default admin user (admin@bocra.org.bw / admin123)\n";
} catch (PDOException $e) {
    echo "❌ Failed to create admin user: " . $e->getMessage() . "\n";
}

// Create test registrar
$registrar_password = password_hash('registrar123', PASSWORD_DEFAULT);
$sql = "INSERT INTO registrars (name, accreditation_number, email, phone, address, accreditation_date) VALUES (?, ?, ?, ?, ?, ?)";
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['Test Registrar Ltd', 'REG-001', 'registrar@test.com', '+267 123 4567', '123 Test Street, Gaborone', '2023-01-01']);
    $registrar_id = $pdo->lastInsertId();
    echo "✅ Created test registrar\n";
} catch (PDOException $e) {
    echo "❌ Failed to create test registrar: " . $e->getMessage() . "\n";
    $registrar_id = null;
}

// Create test registrar user
if ($registrar_id) {
    $sql = "INSERT INTO users (name, email, password, role, registrar_id) VALUES (?, ?, ?, ?, ?)";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['Registrar User', 'registrar@test.com', $registrar_password, 'registrar', $registrar_id]);
        echo "✅ Created test registrar user (registrar@test.com / registrar123)\n";
    } catch (PDOException $e) {
        echo "❌ Failed to create registrar user: " . $e->getMessage() . "\n";
    }
}

echo "\n🎉 Database setup complete!\n\n";
echo "📋 Default Login Credentials:\n";
echo "Admin: admin@bocra.org.bw / admin123\n";
echo "Registrar: registrar@test.com / registrar123\n\n";
echo "⚠️  IMPORTANT: Change these passwords in production!\n\n";
echo "✅ Database is ready for use!\n";
?>
