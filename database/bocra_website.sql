-- ============================================
-- BOCRA-Website Database Schema
-- Import via: phpMyAdmin > bocra_website > Import
-- ============================================

CREATE DATABASE IF NOT EXISTS bocra_website
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE bocra_website;

-- TABLE 1: users
CREATE TABLE IF NOT EXISTS users (
  id                    INT UNSIGNED NOT NULL
                        AUTO_INCREMENT,
  first_name            VARCHAR(100) NOT NULL,
  last_name             VARCHAR(100) NOT NULL,
  email                 VARCHAR(255) NOT NULL,
  phone                 VARCHAR(30)  NULL,
  password_hash         VARCHAR(255) NOT NULL,
  account_type          ENUM(
                          'individual',
                          'company',
                          'government'
                        ) NOT NULL DEFAULT 'individual',
  company_name          VARCHAR(200) NULL,
  company_registration  VARCHAR(100) NULL,
  two_factor_secret     VARCHAR(64)  NULL,
  two_factor_confirmed  TINYINT(1)   NOT NULL DEFAULT 0,
  two_factor_enabled    TINYINT(1)   NOT NULL DEFAULT 1,
  status                ENUM(
                          'pending',
                          'active',
                          'suspended'
                        ) NOT NULL DEFAULT 'pending',
  failed_2fa_attempts   TINYINT      NOT NULL DEFAULT 0,
  locked_until          DATETIME     NULL,
  email_verified        TINYINT(1)   NOT NULL DEFAULT 0,
  created_at            DATETIME     NOT NULL
                        DEFAULT CURRENT_TIMESTAMP,
  updated_at            DATETIME     NOT NULL
                        DEFAULT CURRENT_TIMESTAMP
                        ON UPDATE CURRENT_TIMESTAMP,
  last_login            DATETIME     NULL,

  PRIMARY KEY (id),
  UNIQUE KEY uq_email (email),
  KEY idx_status (status),
  KEY idx_account_type (account_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- TABLE 2: sessions
CREATE TABLE IF NOT EXISTS sessions (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id      INT UNSIGNED NOT NULL,
  token        VARCHAR(255) NOT NULL,
  is_2fa_done  TINYINT(1)   NOT NULL DEFAULT 0,
  ip_address   VARCHAR(45)  NULL,
  user_agent   VARCHAR(500) NULL,
  created_at   DATETIME     NOT NULL
               DEFAULT CURRENT_TIMESTAMP,
  expires_at   DATETIME     NOT NULL,

  PRIMARY KEY (id),
  UNIQUE KEY uq_token (token),
  KEY idx_user_id (user_id),
  KEY idx_expires_at (expires_at),
  CONSTRAINT fk_sessions_user
    FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- TABLE 3: applications
CREATE TABLE IF NOT EXISTS applications (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id       INT UNSIGNED NOT NULL,
  reference     VARCHAR(20)  NOT NULL,
  license_type  VARCHAR(100) NOT NULL,
  status        ENUM(
                  'submitted',
                  'acknowledged',
                  'under_review',
                  'pending_documents',
                  'approved',
                  'rejected'
                ) NOT NULL DEFAULT 'submitted',
  notes         TEXT         NULL,
  submitted_at  DATETIME     NOT NULL
                DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME     NOT NULL
                DEFAULT CURRENT_TIMESTAMP
                ON UPDATE CURRENT_TIMESTAMP,
  resolved_at   DATETIME     NULL,

  PRIMARY KEY (id),
  UNIQUE KEY uq_reference (reference),
  KEY idx_user_id (user_id),
  KEY idx_status (status),
  CONSTRAINT fk_applications_user
    FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- TABLE 4: documents
CREATE TABLE IF NOT EXISTS documents (
  id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  application_id   INT UNSIGNED NULL,
  file_name        VARCHAR(255) NOT NULL,
  file_path        VARCHAR(500) NOT NULL,
  file_size_bytes  BIGINT       NULL,
  uploaded_at      DATETIME     NOT NULL
                   DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_application_id (application_id),
  CONSTRAINT fk_documents_application
    FOREIGN KEY (application_id)
    REFERENCES applications(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- TABLE 5: audit_log
CREATE TABLE IF NOT EXISTS audit_log (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id     INT UNSIGNED NULL,
  action      VARCHAR(100) NOT NULL,
  detail      TEXT         NULL,
  ip_address  VARCHAR(45)  NULL,
  created_at  DATETIME     NOT NULL
              DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_user_id (user_id),
  KEY idx_action (action),
  KEY idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- Seed: one admin user
-- Password is: Admin@1234
INSERT INTO users (
  first_name, last_name, email,
  password_hash, account_type,
  status, two_factor_confirmed,
  email_verified
) VALUES (
  'BOCRA', 'Admin',
  'admin@bocra.org.bw',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'individual', 'active', 1, 1
);
