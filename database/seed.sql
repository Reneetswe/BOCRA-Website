-- BOCRA Domain Registration System - Seed Data
-- Botswana-themed demo data

USE bocra_registry;

-- Insert demo users
INSERT INTO users (name, email, password, role, registrar_id, status) VALUES
('Registrar Demo User', 'registrar@demo.bw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'registrar', 1, 'active'),
('BOCRA Oversight Officer', 'bocra@demo.bw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'bocra', NULL, 'active'),
('Kalahari Registrar User', 'kalahari@demo.bw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'registrar', 2, 'active');
-- Password for all users: password123

-- Insert registrars
INSERT INTO registrars (name, accreditation_number, email, phone, address, status, accreditation_date) VALUES
('Pula Domains Botswana', 'REG-BW-2020-001', 'info@puladomains.bw', '+267 395 7755', 'Plot 50370, Fairgrounds, Gaborone', 'active', '2020-03-15'),
('Kalahari Digital Registrar', 'REG-BW-2021-002', 'contact@kalaharidigital.bw', '+267 318 0200', 'Plot 64518, Broadhurst, Gaborone', 'active', '2021-06-20'),
('Botho Net Services', 'REG-BW-2022-003', 'support@bothonet.bw', '+267 397 4495', 'Plot 20563, Main Mall, Gaborone', 'active', '2022-01-10');

-- Insert applicants
INSERT INTO applicants (registrar_id, type, full_name, company_name, national_id, registration_number, tax_number, email, phone, address, contact_person) VALUES
(1, 'company', NULL, 'Tsotlhe Media Group', NULL, 'BW00123456789', 'C12345678', 'info@tsotlhemedia.bw', '+267 71234567', 'Plot 123, Industrial, Gaborone', 'Thabo Moeti'),
(1, 'individual', 'Kefilwe Kgosana', NULL, '123456789', NULL, NULL, 'kefilwe@email.bw', '+267 72345678', 'Plot 456, Extension 10, Gaborone', NULL),
(1, 'company', NULL, 'Pula Energy Solutions', NULL, 'BW00234567890', 'C23456789', 'contact@pulaenergy.bw', '+267 73456789', 'Plot 789, Commerce Park, Gaborone', 'Mpho Seretse'),
(2, 'company', NULL, 'Dikgang Tech Hub', NULL, 'BW00345678901', 'C34567890', 'hello@dikgangtech.bw', '+267 74567890', 'Plot 234, Block 8, Gaborone', 'Lesego Mothibi'),
(2, 'individual', 'Boitumelo Mogwe', NULL, '234567890', NULL, NULL, 'boitumelo@email.bw', '+267 75678901', 'Plot 567, Tlokweng', NULL),
(2, 'company', NULL, 'Kgotla Capital Partners', NULL, 'BW00456789012', 'C45678901', 'info@kgotlacapital.bw', '+267 76789012', 'Plot 890, CBD, Gaborone', 'Kabo Molefe'),
(3, 'company', NULL, 'Naledi Works Consulting', NULL, 'BW00567890123', 'C56789012', 'contact@nalediworks.bw', '+267 77890123', 'Plot 345, Mogoditshane', 'Onthatile Khumo'),
(1, 'individual', 'Tshepiso Gabaake', NULL, '345678901', NULL, NULL, 'tshepiso@email.bw', '+267 78901234', 'Plot 678, Phakalane', NULL),
(3, 'company', NULL, 'Serowe Digital Services', NULL, 'BW00678901234', 'C67890123', 'info@serowedigital.bw', '+267 79012345', 'Plot 901, Serowe', 'Kagiso Tiro'),
(1, 'company', NULL, 'Molepolole Tech Solutions', NULL, 'BW00789012345', 'C78901234', 'support@molepololtech.bw', '+267 70123456', 'Plot 234, Molepolole', 'Neo Seboni');

-- Insert domains
INSERT INTO domains (domain_name, applicant_id, registrar_id, status, category, nameserver_1, nameserver_2, registration_date, expiry_date, registration_term, notes) VALUES
('tsotlhemedia.bw', 1, 1, 'active', 'commercial', 'ns1.puladomains.bw', 'ns2.puladomains.bw', '2024-01-15', '2025-01-15', 1, 'Media and communications company'),
('kefilwekgosana.bw', 2, 1, 'active', 'personal', 'ns1.puladomains.bw', 'ns2.puladomains.bw', '2024-02-20', '2025-02-20', 1, 'Personal portfolio website'),
('pulaenergy.bw', 3, 1, 'active', 'commercial', 'ns1.puladomains.bw', 'ns2.puladomains.bw', '2024-03-10', '2026-03-10', 2, 'Renewable energy solutions provider'),
('dikgangtech.bw', 4, 2, 'active', 'commercial', 'ns1.kalaharidigital.bw', 'ns2.kalaharidigital.bw', '2024-04-05', '2025-04-05', 1, 'Technology innovation hub'),
('boitumelomogwe.bw', 5, 2, 'active', 'personal', 'ns1.kalaharidigital.bw', 'ns2.kalaharidigital.bw', '2024-05-12', '2025-05-12', 1, 'Personal blog'),
('kgotlacapital.bw', 6, 2, 'active', 'commercial', 'ns1.kalaharidigital.bw', 'ns2.kalaharidigital.bw', '2024-06-18', '2025-06-18', 1, 'Investment and financial services'),
('nalediworks.bw', 7, 3, 'active', 'commercial', 'ns1.bothonet.bw', 'ns2.bothonet.bw', '2024-07-22', '2025-07-22', 1, 'Business consulting firm'),
('tshepiso.bw', 8, 1, 'pending', 'personal', 'ns1.puladomains.bw', 'ns2.puladomains.bw', '2024-11-28', '2025-11-28', 1, 'Personal website - pending verification'),
('serowedigital.bw', 9, 3, 'active', 'commercial', 'ns1.bothonet.bw', 'ns2.bothonet.bw', '2024-08-30', '2025-08-30', 1, 'Digital services provider'),
('molepololtech.bw', 10, 1, 'pending', 'commercial', 'ns1.puladomains.bw', 'ns2.puladomains.bw', '2024-11-29', '2025-11-29', 1, 'IT solutions company - new submission'),
('botswanaheritage.bw', 1, 1, 'active', 'non-profit', 'ns1.puladomains.bw', 'ns2.puladomains.bw', '2024-09-15', '2025-09-15', 1, 'Cultural heritage organization'),
('gaboronestartups.bw', 4, 2, 'active', 'commercial', 'ns1.kalaharidigital.bw', 'ns2.kalaharidigital.bw', '2024-10-01', '2025-10-01', 1, 'Startup incubator'),
('kalaharitourism.bw', 3, 1, 'active', 'commercial', 'ns1.puladomains.bw', 'ns2.puladomains.bw', '2024-10-20', '2025-10-20', 1, 'Tourism and safari services'),
('botswanaedu.bw', 7, 3, 'pending', 'educational', 'ns1.bothonet.bw', 'ns2.bothonet.bw', '2024-11-30', '2025-11-30', 1, 'Educational platform - under review'),
('maungaming.bw', 6, 2, 'active', 'commercial', 'ns1.kalaharidigital.bw', 'ns2.kalaharidigital.bw', '2024-11-10', '2025-11-10', 1, 'Gaming and entertainment');

-- Insert domain applications
INSERT INTO domain_applications (domain_id, applicant_id, registrar_id, submission_status, notes, submitted_at) VALUES
(1, 1, 1, 'approved', 'Approved after standard verification', '2024-01-15 09:30:00'),
(2, 2, 1, 'approved', 'Individual applicant verified', '2024-02-20 10:15:00'),
(3, 3, 1, 'approved', 'Company documents verified', '2024-03-10 11:20:00'),
(4, 4, 2, 'approved', 'Tech hub registration approved', '2024-04-05 14:30:00'),
(5, 5, 2, 'approved', 'Personal domain approved', '2024-05-12 09:45:00'),
(6, 6, 2, 'approved', 'Financial services company verified', '2024-06-18 13:00:00'),
(7, 7, 3, 'approved', 'Consulting firm approved', '2024-07-22 10:30:00'),
(8, 8, 1, 'submitted', 'Awaiting verification of personal details', '2024-11-28 15:20:00'),
(9, 9, 3, 'approved', 'Digital services provider approved', '2024-08-30 11:45:00'),
(10, 10, 1, 'submitted', 'New submission - under initial review', '2024-11-29 16:10:00'),
(11, 1, 1, 'approved', 'Non-profit status verified', '2024-09-15 12:00:00'),
(12, 4, 2, 'approved', 'Startup incubator approved', '2024-10-01 09:30:00'),
(13, 3, 1, 'approved', 'Tourism license verified', '2024-10-20 14:15:00'),
(14, 7, 3, 'under_review', 'Educational credentials being verified', '2024-11-30 10:00:00'),
(15, 6, 2, 'approved', 'Gaming license approved', '2024-11-10 13:45:00');

-- Insert audit logs
INSERT INTO audit_logs (actor_name, actor_role, action, domain_name, applicant_name, details, created_at) VALUES
('Registrar Demo User', 'registrar', 'applicant_created', NULL, 'Tsotlhe Media Group', 'New company applicant registered', '2024-01-14 14:30:00'),
('Registrar Demo User', 'registrar', 'domain_registered', 'tsotlhemedia.bw', 'Tsotlhe Media Group', 'Domain registration submitted', '2024-01-15 09:30:00'),
('BOCRA Oversight Officer', 'bocra', 'application_received', 'tsotlhemedia.bw', 'Tsotlhe Media Group', 'Application received and logged', '2024-01-15 09:31:00'),
('BOCRA Oversight Officer', 'bocra', 'application_approved', 'tsotlhemedia.bw', 'Tsotlhe Media Group', 'Application approved after verification', '2024-01-16 11:00:00'),
('Registrar Demo User', 'registrar', 'applicant_created', NULL, 'Kefilwe Kgosana', 'Individual applicant registered', '2024-02-19 16:45:00'),
('Registrar Demo User', 'registrar', 'domain_registered', 'kefilwekgosana.bw', 'Kefilwe Kgosana', 'Personal domain submitted', '2024-02-20 10:15:00'),
('Kalahari Registrar User', 'registrar', 'applicant_created', NULL, 'Dikgang Tech Hub', 'Tech company registered', '2024-04-04 13:20:00'),
('Kalahari Registrar User', 'registrar', 'domain_registered', 'dikgangtech.bw', 'Dikgang Tech Hub', 'Domain registration submitted', '2024-04-05 14:30:00'),
('BOCRA Oversight Officer', 'bocra', 'compliance_flag_created', 'tshepiso.bw', 'Tshepiso Gabaake', 'Missing tax number - flagged for review', '2024-11-28 16:00:00'),
('Registrar Demo User', 'registrar', 'domain_registered', 'molepololtech.bw', 'Molepolole Tech Solutions', 'New domain submission', '2024-11-29 16:10:00'),
('BOCRA Oversight Officer', 'bocra', 'application_received', 'molepololtech.bw', 'Molepolole Tech Solutions', 'Application received - pending review', '2024-11-29 16:11:00'),
('System', 'system', 'database_backup', NULL, NULL, 'Automated daily backup completed', '2024-11-30 02:00:00');

-- Insert compliance flags
INSERT INTO compliance_flags (domain_id, applicant_id, flag_type, severity, status, note, created_at) VALUES
(8, 8, 'missing_tax_number', 'medium', 'open', 'Individual applicant has not provided tax number', '2024-11-28 16:00:00'),
(10, 10, 'incomplete_details', 'low', 'investigating', 'Company registration documents pending verification', '2024-11-29 17:00:00'),
(14, 7, 'incomplete_details', 'medium', 'investigating', 'Educational accreditation documents under review', '2024-11-30 11:00:00'),
(NULL, 1, 'duplicate_registration', 'low', 'resolved', 'Applicant attempted to register similar domain - resolved as different entity', '2024-09-10 14:30:00'),
(NULL, 4, 'suspicious_activity', 'high', 'dismissed', 'Multiple rapid submissions - verified as legitimate business expansion', '2024-10-05 10:00:00');

-- Verify data
SELECT 'Users' as table_name, COUNT(*) as count FROM users
UNION ALL
SELECT 'Registrars', COUNT(*) FROM registrars
UNION ALL
SELECT 'Applicants', COUNT(*) FROM applicants
UNION ALL
SELECT 'Domains', COUNT(*) FROM domains
UNION ALL
SELECT 'Domain Applications', COUNT(*) FROM domain_applications
UNION ALL
SELECT 'Audit Logs', COUNT(*) FROM audit_logs
UNION ALL
SELECT 'Compliance Flags', COUNT(*) FROM compliance_flags;
