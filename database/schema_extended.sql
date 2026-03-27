-- BOCRA Extended Multi-Portal System - Database Schema
-- Supports 5 roles: registrar, bocra, licensing_admin, complaints_admin, cybersecurity_admin

DROP DATABASE IF EXISTS bocra_system;
CREATE DATABASE bocra_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bocra_system;

-- ═══════════════════════════════════════════════════════
-- CORE TABLES
-- ═══════════════════════════════════════════════════════

-- Users table (5 roles)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('registrar', 'bocra', 'licensing_admin', 'complaints_admin', 'cybersecurity_admin') NOT NULL,
    registrar_id INT NULL,
    department VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status)
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

-- ═══════════════════════════════════════════════════════
-- LICENSING PORTAL TABLES
-- ═══════════════════════════════════════════════════════

-- License Applications
CREATE TABLE license_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_number VARCHAR(50) NOT NULL UNIQUE,
    applicant_name VARCHAR(255) NOT NULL,
    applicant_email VARCHAR(255) NOT NULL,
    applicant_phone VARCHAR(50) NOT NULL,
    company_name VARCHAR(255),
    license_type ENUM('telecommunications', 'broadcasting', 'postal', 'internet_service', 'spectrum', 'equipment_approval') NOT NULL,
    business_type ENUM('individual', 'company', 'ngo', 'government') NOT NULL,
    registration_number VARCHAR(100),
    tax_number VARCHAR(100),
    physical_address TEXT NOT NULL,
    postal_address TEXT,
    business_description TEXT,
    proposed_services TEXT,
    technical_capacity TEXT,
    financial_capacity TEXT,
    status ENUM('draft', 'submitted', 'under_review', 'pending_documents', 'approved', 'rejected', 'withdrawn') DEFAULT 'submitted',
    priority ENUM('normal', 'high', 'urgent') DEFAULT 'normal',
    assigned_to INT NULL,
    reviewer_notes TEXT,
    rejection_reason TEXT,
    documents_uploaded JSON,
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    review_started_at TIMESTAMP NULL,
    reviewed_at TIMESTAMP NULL,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_license_type (license_type),
    INDEX idx_submission_date (submission_date),
    INDEX idx_assigned_to (assigned_to)
) ENGINE=InnoDB;

-- License Application Status History
CREATE TABLE license_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    old_status VARCHAR(50),
    new_status VARCHAR(50) NOT NULL,
    changed_by INT NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES license_applications(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_application (application_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- ═══════════════════════════════════════════════════════
-- COMPLAINTS PORTAL TABLES
-- ═══════════════════════════════════════════════════════

-- Complaints
CREATE TABLE complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_number VARCHAR(50) NOT NULL UNIQUE,
    complainant_name VARCHAR(255) NOT NULL,
    complainant_email VARCHAR(255) NOT NULL,
    complainant_phone VARCHAR(50) NOT NULL,
    complaint_type ENUM('service_quality', 'billing', 'network_outage', 'customer_service', 'fraud', 'spam', 'data_privacy', 'other') NOT NULL,
    service_provider VARCHAR(255),
    sector ENUM('telecommunications', 'broadcasting', 'postal', 'internet') NOT NULL,
    priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    status ENUM('submitted', 'acknowledged', 'investigating', 'pending_info', 'resolved', 'closed', 'escalated') DEFAULT 'submitted',
    subject VARCHAR(500) NOT NULL,
    description TEXT NOT NULL,
    desired_outcome TEXT,
    evidence_files JSON,
    assigned_to INT NULL,
    resolution TEXT,
    resolution_date TIMESTAMP NULL,
    feedback_rating INT,
    feedback_comment TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    acknowledged_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_complaint_type (complaint_type),
    INDEX idx_priority (priority),
    INDEX idx_sector (sector),
    INDEX idx_submitted (submitted_at)
) ENGINE=InnoDB;

-- Complaint Updates/Communications
CREATE TABLE complaint_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL,
    update_type ENUM('status_change', 'admin_comment', 'user_response', 'resolution', 'escalation') NOT NULL,
    message TEXT NOT NULL,
    updated_by INT NULL,
    is_visible_to_user BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_complaint (complaint_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- ═══════════════════════════════════════════════════════
-- CYBERSECURITY PORTAL TABLES
-- ═══════════════════════════════════════════════════════

-- Cybersecurity Service Requests
CREATE TABLE cybersecurity_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_number VARCHAR(50) NOT NULL UNIQUE,
    organization_name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255) NOT NULL,
    contact_email VARCHAR(255) NOT NULL,
    contact_phone VARCHAR(50) NOT NULL,
    sector ENUM('government', 'telecommunications', 'education', 'finance', 'healthcare', 'energy', 'retail', 'other') NOT NULL,
    organization_size ENUM('small', 'medium', 'large', 'enterprise') NOT NULL,
    service_type ENUM('risk_assessment', 'compliance_review', 'incident_response', 'security_training', 'penetration_testing', 'security_audit', 'consultation') NOT NULL,
    urgency ENUM('routine', 'important', 'urgent', 'critical') DEFAULT 'routine',
    status ENUM('submitted', 'reviewing', 'scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'submitted',
    description TEXT NOT NULL,
    specific_requirements TEXT,
    preferred_date DATE,
    assigned_to INT NULL,
    assigned_team VARCHAR(255),
    findings TEXT,
    recommendations TEXT,
    report_file VARCHAR(500),
    completion_date TIMESTAMP NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_service_type (service_type),
    INDEX idx_sector (sector),
    INDEX idx_urgency (urgency),
    INDEX idx_submitted (submitted_at)
) ENGINE=InnoDB;

-- Cybersecurity Request Updates
CREATE TABLE cybersecurity_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    update_type ENUM('status_change', 'assignment', 'progress_update', 'completion', 'feedback') NOT NULL,
    message TEXT NOT NULL,
    updated_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES cybersecurity_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_request (request_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- ═══════════════════════════════════════════════════════
-- DOMAIN REGISTRY TABLES (from original system)
-- ═══════════════════════════════════════════════════════

-- Applicants table
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

-- Domain Applications table
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

-- ═══════════════════════════════════════════════════════
-- SYSTEM TABLES
-- ═══════════════════════════════════════════════════════

-- Audit Logs
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    actor_name VARCHAR(255) NOT NULL,
    actor_role VARCHAR(50) NOT NULL,
    action VARCHAR(255) NOT NULL,
    entity_type VARCHAR(100),
    entity_id INT,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_actor_role (actor_role),
    INDEX idx_action (action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- Notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    recipient_email VARCHAR(255),
    type ENUM('license_update', 'complaint_update', 'cyber_update', 'system_alert', 'assignment') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(500),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- Compliance Flags
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

-- ═══════════════════════════════════════════════════════
-- ANALYTICS VIEWS
-- ═══════════════════════════════════════════════════════

-- Licensing Analytics View
CREATE VIEW licensing_analytics AS
SELECT 
    DATE(submission_date) as date,
    license_type,
    status,
    COUNT(*) as count,
    AVG(TIMESTAMPDIFF(DAY, submission_date, COALESCE(reviewed_at, NOW()))) as avg_processing_days
FROM license_applications
WHERE submission_date >= DATE_SUB(NOW(), INTERVAL 90 DAY)
GROUP BY DATE(submission_date), license_type, status;

-- Complaints Analytics View
CREATE VIEW complaints_analytics AS
SELECT 
    DATE(submitted_at) as date,
    complaint_type,
    sector,
    status,
    priority,
    COUNT(*) as count,
    AVG(TIMESTAMPDIFF(HOUR, submitted_at, COALESCE(resolution_date, NOW()))) as avg_resolution_hours
FROM complaints
WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
GROUP BY DATE(submitted_at), complaint_type, sector, status, priority;

-- Cybersecurity Analytics View
CREATE VIEW cybersecurity_analytics AS
SELECT 
    DATE(submitted_at) as date,
    sector,
    service_type,
    status,
    urgency,
    COUNT(*) as count
FROM cybersecurity_requests
WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
GROUP BY DATE(submitted_at), sector, service_type, status, urgency;
