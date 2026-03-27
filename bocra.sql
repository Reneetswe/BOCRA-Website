-- BOCRA Website Database Schema
-- This file creates the necessary tables for the BOCRA website authentication system

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS bocra_website CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bocra_website;

-- Users table with TOTP support
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    phone_number VARCHAR(20),
    role ENUM('user', 'officer', 'admin') DEFAULT 'user',
    totp_secret VARCHAR(64) NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
);

-- Failed login attempts table for rate limiting
CREATE TABLE failed_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    attempt_type ENUM('password', '2fa') NOT NULL,
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    INDEX idx_email_time (email, attempt_time),
    INDEX idx_attempt_time (attempt_time)
);

-- Sample admin user (password: admin123)
INSERT INTO users (email, password_hash, full_name, phone_number, role) VALUES 
('admin@bocra.org.bw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'BOCRA Administrator', '+2673957755', 'admin');

-- Sample regular user for testing
INSERT INTO users (email, password_hash, full_name, phone_number, role) VALUES 
('test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test User', '+2671234567', 'user');
