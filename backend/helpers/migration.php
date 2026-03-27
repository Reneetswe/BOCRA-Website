<?php
// To install dependencies open XAMPP Shell and run:
// cd C:\xampp\htdocs\bocra-website\backend
// composer install

// BOCRA-Website — Database Migration Helper

require_once __DIR__ . '/../config/database.php';

function runMigrations(): void {
    $db = getDB();
    
    // Create migrations table if not exists
    $db->exec("
        CREATE TABLE IF NOT EXISTS migrations (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            executed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // List of migrations to run
    $migrations = [
        '001_create_users_table',
        '002_create_sessions_table', 
        '003_create_applications_table',
        '004_create_documents_table',
        '005_create_audit_log_table'
    ];
    
    foreach ($migrations as $migration) {
        if (!migrationExists($migration)) {
            runMigration($migration);
        }
    }
}

function migrationExists(string $migration): bool {
    $db = getDB();
    $stmt = $db->prepare('SELECT COUNT(*) FROM migrations WHERE migration = ?');
    $stmt->execute([$migration]);
    return (int) $stmt->fetchColumn() > 0;
}

function runMigration(string $migration): void {
    $db = getDB();
    
    switch ($migration) {
        case '001_create_users_table':
            $db->exec("
                CREATE TABLE IF NOT EXISTS users (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    first_name VARCHAR(100) NOT NULL,
                    last_name VARCHAR(100) NOT NULL,
                    email VARCHAR(255) NOT NULL,
                    phone VARCHAR(30) NULL,
                    password_hash VARCHAR(255) NOT NULL,
                    account_type ENUM('individual', 'company', 'government') NOT NULL DEFAULT 'individual',
                    company_name VARCHAR(200) NULL,
                    company_registration VARCHAR(100) NULL,
                    two_factor_secret VARCHAR(64) NULL,
                    two_factor_confirmed TINYINT(1) NOT NULL DEFAULT 0,
                    two_factor_enabled TINYINT(1) NOT NULL DEFAULT 1,
                    status ENUM('pending', 'active', 'suspended') NOT NULL DEFAULT 'pending',
                    failed_2fa_attempts TINYINT NOT NULL DEFAULT 0,
                    locked_until DATETIME NULL,
                    email_verified TINYINT(1) NOT NULL DEFAULT 0,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    last_login DATETIME NULL,
                    PRIMARY KEY (id),
                    UNIQUE KEY uq_email (email),
                    KEY idx_status (status),
                    KEY idx_account_type (account_type)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            break;
            
        case '002_create_sessions_table':
            $db->exec("
                CREATE TABLE IF NOT EXISTS sessions (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    user_id INT UNSIGNED NOT NULL,
                    token VARCHAR(255) NOT NULL,
                    is_2fa_done TINYINT(1) NOT NULL DEFAULT 0,
                    ip_address VARCHAR(45) NULL,
                    user_agent VARCHAR(500) NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    expires_at DATETIME NOT NULL,
                    PRIMARY KEY (id),
                    UNIQUE KEY uq_token (token),
                    KEY idx_user_id (user_id),
                    KEY idx_expires_at (expires_at),
                    CONSTRAINT fk_sessions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            break;
            
        case '003_create_applications_table':
            $db->exec("
                CREATE TABLE IF NOT EXISTS applications (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    user_id INT UNSIGNED NOT NULL,
                    reference VARCHAR(20) NOT NULL,
                    license_type VARCHAR(100) NOT NULL,
                    status ENUM('submitted', 'acknowledged', 'under_review', 'pending_documents', 'approved', 'rejected') NOT NULL DEFAULT 'submitted',
                    notes TEXT NULL,
                    submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    resolved_at DATETIME NULL,
                    PRIMARY KEY (id),
                    UNIQUE KEY uq_reference (reference),
                    KEY idx_user_id (user_id),
                    KEY idx_status (status),
                    CONSTRAINT fk_applications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            break;
            
        case '004_create_documents_table':
            $db->exec("
                CREATE TABLE IF NOT EXISTS documents (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    application_id INT UNSIGNED NULL,
                    file_name VARCHAR(255) NOT NULL,
                    file_path VARCHAR(500) NOT NULL,
                    file_size_bytes BIGINT NULL,
                    uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY idx_application_id (application_id),
                    CONSTRAINT fk_documents_application FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            break;
            
        case '005_create_audit_log_table':
            $db->exec("
                CREATE TABLE IF NOT EXISTS audit_log (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    user_id INT UNSIGNED NULL,
                    action VARCHAR(100) NOT NULL,
                    detail TEXT NULL,
                    ip_address VARCHAR(45) NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY idx_user_id (user_id),
                    KEY idx_action (action),
                    KEY idx_created_at (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            break;
    }
    
    // Record migration as executed
    $stmt = $db->prepare('INSERT INTO migrations (migration) VALUES (?)');
    $stmt->execute([$migration]);
}

// Seed initial data
function seedData(): void {
    $db = getDB();
    
    // Check if admin user exists
    $stmt = $db->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
    $stmt->execute(['admin@bocra.org.bw']);
    
    if ((int) $stmt->fetchColumn() === 0) {
        // Create admin user
        $passwordHash = password_hash('Admin@1234', PASSWORD_DEFAULT);
        $stmt = $db->prepare("
            INSERT INTO users (
                first_name, last_name, email,
                password_hash, account_type,
                status, two_factor_confirmed,
                email_verified
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            'BOCRA', 'Admin',
            'admin@bocra.org.bw',
            $passwordHash,
            'individual', 'active', 1, 1
        ]);
    }
}
