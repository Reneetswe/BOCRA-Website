-- BOCRA Domain Registration System - Database Schema
-- Focused internal system for Registrar and BOCRA oversight

DROP DATABASE IF EXISTS bocra_registry;
CREATE DATABASE bocra_registry CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bocra_registry;

-- Users table (registrar and BOCRA staff)
CREATE TABLE users (
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
) ENGINE=InnoDB;

-- Registrars table
CREATE TABLE registrars (
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
) ENGINE=InnoDB;

-- Applicants table (individuals or companies applying for domains)
CREATE TABLE applicants (
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
    contact_person VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (registrar_id) REFERENCES registrars(id) ON DELETE CASCADE,
    INDEX idx_registrar (registrar_id),
    INDEX idx_type (type),
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- Domains table
CREATE TABLE domains (
    id INT AUTO_INCREMENT PRIMARY KEY,
    domain_name VARCHAR(255) NOT NULL UNIQUE,
    applicant_id INT NOT NULL,
    registrar_id INT NOT NULL,
    status ENUM('active', 'pending', 'suspended', 'expired', 'cancelled') DEFAULT 'pending',
    category ENUM('commercial', 'government', 'educational', 'non-profit', 'personal') DEFAULT 'commercial',
    nameserver_1 VARCHAR(255) NOT NULL,
    nameserver_2 VARCHAR(255) NOT NULL,
    registration_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    registration_term INT DEFAULT 1,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (applicant_id) REFERENCES applicants(id) ON DELETE CASCADE,
    FOREIGN KEY (registrar_id) REFERENCES registrars(id) ON DELETE CASCADE,
    INDEX idx_domain_name (domain_name),
    INDEX idx_status (status),
    INDEX idx_registrar (registrar_id),
    INDEX idx_applicant (applicant_id),
    INDEX idx_expiry (expiry_date)
) ENGINE=InnoDB;

-- Domain Applications table (submission tracking)
CREATE TABLE domain_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    domain_id INT NOT NULL,
    applicant_id INT NOT NULL,
    registrar_id INT NOT NULL,
    submission_status ENUM('submitted', 'under_review', 'approved', 'rejected') DEFAULT 'submitted',
    notes TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    reviewed_by INT NULL,
    FOREIGN KEY (domain_id) REFERENCES domains(id) ON DELETE CASCADE,
    FOREIGN KEY (applicant_id) REFERENCES applicants(id) ON DELETE CASCADE,
    FOREIGN KEY (registrar_id) REFERENCES registrars(id) ON DELETE CASCADE,
    INDEX idx_status (submission_status),
    INDEX idx_registrar (registrar_id),
    INDEX idx_submitted (submitted_at)
) ENGINE=InnoDB;

-- Audit Logs table
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    actor_name VARCHAR(255) NOT NULL,
    actor_role ENUM('registrar', 'bocra', 'system') NOT NULL,
    action VARCHAR(255) NOT NULL,
    domain_name VARCHAR(255),
    applicant_name VARCHAR(255),
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_actor_role (actor_role),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- Compliance Flags table
CREATE TABLE compliance_flags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    domain_id INT,
    applicant_id INT,
    flag_type ENUM('missing_tax_number', 'duplicate_registration', 'suspicious_activity', 'incomplete_details', 'expired_documents', 'other') NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    status ENUM('open', 'investigating', 'resolved', 'dismissed') DEFAULT 'open',
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    FOREIGN KEY (domain_id) REFERENCES domains(id) ON DELETE CASCADE,
    FOREIGN KEY (applicant_id) REFERENCES applicants(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_severity (severity),
    INDEX idx_flag_type (flag_type)
) ENGINE=InnoDB;

-- Create views for quick stats
CREATE VIEW registrar_stats AS
SELECT 
    r.id,
    r.name,
    r.accreditation_number,
    COUNT(DISTINCT d.id) as total_domains,
    COUNT(DISTINCT CASE WHEN d.status = 'active' THEN d.id END) as active_domains,
    COUNT(DISTINCT CASE WHEN d.status = 'pending' THEN d.id END) as pending_domains,
    COUNT(DISTINCT da.id) as total_submissions,
    COUNT(DISTINCT a.id) as total_applicants
FROM registrars r
LEFT JOIN domains d ON r.id = d.registrar_id
LEFT JOIN domain_applications da ON r.id = da.registrar_id
LEFT JOIN applicants a ON r.id = a.registrar_id
GROUP BY r.id, r.name, r.accreditation_number;

CREATE VIEW bocra_metrics AS
SELECT 
    (SELECT COUNT(*) FROM registrars WHERE status = 'active') as total_registrars,
    (SELECT COUNT(*) FROM domains) as total_domains,
    (SELECT COUNT(*) FROM domains WHERE status = 'active') as active_domains,
    (SELECT COUNT(*) FROM domains WHERE status = 'pending') as pending_domains,
    (SELECT COUNT(*) FROM domain_applications WHERE submission_status = 'submitted') as pending_applications,
    (SELECT COUNT(*) FROM compliance_flags WHERE status = 'open') as open_compliance_flags;
