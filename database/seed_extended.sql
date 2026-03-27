-- BOCRA Extended Multi-Portal System - Seed Data
-- Demo data for all 5 roles and portals

USE bocra_system;

-- ═══════════════════════════════════════════════════════
-- REGISTRARS
-- ═══════════════════════════════════════════════════════

INSERT INTO registrars (name, accreditation_number, email, phone, address, status, accreditation_date) VALUES
('Pula Domains Botswana', 'REG-BW-2020-001', 'info@puladomains.bw', '+267 395 7755', 'Plot 50370, Fairgrounds, Gaborone', 'active', '2020-03-15'),
('Kalahari Digital Registrar', 'REG-BW-2021-002', 'contact@kalaharidigital.bw', '+267 318 0200', 'Plot 64518, Broadhurst, Gaborone', 'active', '2021-06-20'),
('Botho Net Services', 'REG-BW-2022-003', 'support@bothonet.bw', '+267 397 4495', 'Plot 20563, Main Mall, Gaborone', 'active', '2022-01-10');

-- ═══════════════════════════════════════════════════════
-- USERS (5 ROLES)
-- Password for all users: password123
-- ═══════════════════════════════════════════════════════

INSERT INTO users (name, email, password, role, registrar_id, department, status) VALUES
-- Registrar users
('Registrar Demo User', 'registrar@demo.bw', '$2y$10$8Gp53mWQw5uKUg3P96bmFqOELVmfdTuX2KfUU4IKcaPHdgZ6xPXC', 'registrar', 1, NULL, 'active'),
('Kalahari Registrar User', 'kalahari@demo.bw', '$2y$10$8Gp53mWQw5uKUg3P96bmFqOELVmfdTuX2KfUU4IKcaPHdgZ6xPXC', 'registrar', 2, NULL, 'active'),

-- BOCRA Oversight
('BOCRA Oversight Officer', 'bocra@demo.bw', '$2y$10$8Gp53mWQw5uKUg3P96bmFqOELVmfdTuX2KfUU4IKcaPHdgZ6xPXC', 'bocra', NULL, 'Regulatory Oversight', 'active'),

-- Licensing Admin
('Licensing Administrator', 'licensing@demo.bw', '$2y$10$8Gp53mWQw5uKUg3P96bmFqOELVmfdTuX2KfUU4IKcaPHdgZ6xPXC', 'licensing_admin', NULL, 'Licensing Department', 'active'),
('Senior Licensing Officer', 'licensing2@demo.bw', '$2y$10$8Gp53mWQw5uKUg3P96bmFqOELVmfdTuX2KfUU4IKcaPHdgZ6xPXC', 'licensing_admin', NULL, 'Licensing Department', 'active'),

-- Complaints Admin
('Complaints Manager', 'complaints@demo.bw', '$2y$10$8Gp53mWQw5uKUg3P96bmFqOELVmfdTuX2KfUU4IKcaPHdgZ6xPXC', 'complaints_admin', NULL, 'Consumer Protection', 'active'),
('Complaints Officer', 'complaints2@demo.bw', '$2y$10$8Gp53mWQw5uKUg3P96bmFqOELVmfdTuX2KfUU4IKcaPHdgZ6xPXC', 'complaints_admin', NULL, 'Consumer Protection', 'active'),

-- Cybersecurity Admin
('Cybersecurity Manager', 'cybersecurity@demo.bw', '$2y$10$8Gp53mWQw5uKUg3P96bmFqOELVmfdTuX2KfUU4IKcaPHdgZ6xPXC', 'cybersecurity_admin', NULL, 'Cybersecurity Unit', 'active'),
('Security Analyst', 'security@demo.bw', '$2y$10$8Gp53mWQw5uKUg3P96bmFqOELVmfdTuX2KfUU4IKcaPHdgZ6xPXC', 'cybersecurity_admin', NULL, 'Cybersecurity Unit', 'active');

-- ═══════════════════════════════════════════════════════
-- LICENSE APPLICATIONS (Demo Data)
-- ═══════════════════════════════════════════════════════

INSERT INTO license_applications (
    application_number, applicant_name, applicant_email, applicant_phone, company_name,
    license_type, business_type, registration_number, tax_number, physical_address,
    business_description, proposed_services, status, priority, submission_date
) VALUES
-- Recent applications (last 7 days)
('LIC-2025-001', 'Thabo Moeti', 'thabo@tsotlhemedia.bw', '+267 71234567', 'Tsotlhe Media Group', 'broadcasting', 'company', 'BW00123456789', 'C12345678', 'Plot 123, Industrial, Gaborone', 'Community radio station', 'Local news and music broadcasting', 'under_review', 'high', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('LIC-2025-002', 'Kefilwe Kgosana', 'kefilwe@pulainternet.bw', '+267 72345678', 'Pula Internet Solutions', 'internet_service', 'company', 'BW00234567890', 'C23456789', 'Plot 456, Extension 10, Gaborone', 'ISP services', 'Fiber and wireless internet', 'submitted', 'normal', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('LIC-2025-003', 'Mpho Seretse', 'mpho@bothotel.bw', '+267 73456789', 'Botho Telecom', 'telecommunications', 'company', 'BW00345678901', 'C34567890', 'Plot 789, Commerce Park, Gaborone', 'Mobile network operator', 'Voice and data services', 'submitted', 'urgent', DATE_SUB(NOW(), INTERVAL 5 HOUR)),

-- This week
('LIC-2025-004', 'Lesego Mothibi', 'lesego@dikgangtech.bw', '+267 74567890', 'Dikgang Tech Hub', 'equipment_approval', 'company', 'BW00456789012', 'C45678901', 'Plot 234, Block 8, Gaborone', 'Telecom equipment supplier', 'Import and distribution of network equipment', 'pending_documents', 'normal', DATE_SUB(NOW(), INTERVAL 4 DAY)),
('LIC-2025-005', 'Boitumelo Mogwe', 'boitumelo@email.bw', '+267 75678901', NULL, 'spectrum', 'individual', NULL, NULL, 'Plot 567, Tlokweng', 'Amateur radio operator', 'Amateur radio frequencies', 'approved', 'normal', DATE_SUB(NOW(), INTERVAL 6 DAY)),

-- Last month
('LIC-2024-098', 'Kabo Molefe', 'kabo@kgotlapost.bw', '+267 76789012', 'Kgotla Postal Services', 'postal', 'company', 'BW00567890123', 'C56789012', 'Plot 890, CBD, Gaborone', 'Courier services', 'Express delivery nationwide', 'approved', 'normal', DATE_SUB(NOW(), INTERVAL 15 DAY)),
('LIC-2024-099', 'Neo Khumo', 'neo@radiobw.bw', '+267 77890123', 'Radio Botswana FM', 'broadcasting', 'company', 'BW00678901234', 'C67890123', 'Plot 111, Broadhurst, Gaborone', 'Commercial radio', 'Music and talk shows', 'rejected', 'normal', DATE_SUB(NOW(), INTERVAL 20 DAY)),
('LIC-2024-100', 'Tumi Setlhare', 'tumi@connectbw.bw', '+267 78901234', 'Connect Botswana', 'internet_service', 'company', 'BW00789012345', 'C78901234', 'Plot 222, Mogoditshane', 'Rural ISP', 'Internet for rural areas', 'under_review', 'high', DATE_SUB(NOW(), INTERVAL 10 DAY));

-- ═══════════════════════════════════════════════════════
-- COMPLAINTS (Demo Data)
-- ═══════════════════════════════════════════════════════

INSERT INTO complaints (
    complaint_number, complainant_name, complainant_email, complainant_phone,
    complaint_type, service_provider, sector, priority, status, subject, description,
    desired_outcome, submitted_at
) VALUES
-- Recent complaints (last 7 days)
('CMP-2025-001', 'Olebile Tau', 'olebile@email.bw', '+267 71111111', 'network_outage', 'Mascom Wireless', 'telecommunications', 'high', 'investigating', 'Frequent network disconnections', 'My mobile network keeps disconnecting every few minutes. This has been happening for the past week in Gaborone area.', 'Stable network connection', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('CMP-2025-002', 'Kagiso Moagi', 'kagiso@email.bw', '+267 72222222', 'billing', 'Orange Botswana', 'telecommunications', 'medium', 'submitted', 'Incorrect billing charges', 'I was charged for international calls I never made. My bill is P500 higher than usual.', 'Refund of incorrect charges', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('CMP-2025-003', 'Refilwe Kgosi', 'refilwe@email.bw', '+267 73333333', 'service_quality', 'BTCL', 'internet', 'critical', 'acknowledged', 'Internet speed below advertised', 'Paying for 100Mbps but only getting 10Mbps consistently. Speed tests show poor performance.', 'Service upgrade or refund', DATE_SUB(NOW(), INTERVAL 3 HOUR)),

-- This week
('CMP-2025-004', 'Tumelo Moyo', 'tumelo@email.bw', '+267 74444444', 'customer_service', 'Yarona FM', 'broadcasting', 'low', 'resolved', 'Poor customer service response', 'Submitted advertising request 3 weeks ago, no response from customer service team.', 'Timely response', DATE_SUB(NOW(), INTERVAL 5 DAY)),
('CMP-2025-005', 'Lorato Seboni', 'lorato@email.bw', '+267 75555555', 'spam', 'Unknown', 'telecommunications', 'medium', 'investigating', 'Excessive spam SMS messages', 'Receiving 10-15 spam messages daily from unknown numbers promoting loans and betting.', 'Stop spam messages', DATE_SUB(NOW(), INTERVAL 4 DAY)),

-- Last month
('CMP-2024-098', 'Phenyo Gabaake', 'phenyo@email.bw', '+267 76666666', 'data_privacy', 'Orange Botswana', 'telecommunications', 'high', 'resolved', 'Unauthorized data sharing', 'My personal information was shared with third parties without my consent.', 'Privacy protection', DATE_SUB(NOW(), INTERVAL 18 DAY)),
('CMP-2024-099', 'Kitso Mosimanegape', 'kitso@email.bw', '+267 77777777', 'fraud', 'Mascom Wireless', 'telecommunications', 'critical', 'closed', 'SIM swap fraud', 'Someone swapped my SIM card and accessed my mobile money account.', 'Investigation and compensation', DATE_SUB(NOW(), INTERVAL 25 DAY)),
('CMP-2024-100', 'Onalenna Kgari', 'onalenna@email.bw', '+267 78888888', 'service_quality', 'Botswana Post', 'postal', 'medium', 'resolved', 'Lost parcel', 'My parcel was lost in transit. Tracking shows it arrived but I never received it.', 'Find parcel or compensation', DATE_SUB(NOW(), INTERVAL 12 DAY));

-- ═══════════════════════════════════════════════════════
-- CYBERSECURITY REQUESTS (Demo Data)
-- ═══════════════════════════════════════════════════════

INSERT INTO cybersecurity_requests (
    request_number, organization_name, contact_person, contact_email, contact_phone,
    sector, organization_size, service_type, urgency, status, description,
    specific_requirements, preferred_date, submitted_at
) VALUES
-- Recent requests (last 7 days)
('CYB-2025-001', 'Ministry of Finance', 'Gorata Moeng', 'gorata@finance.gov.bw', '+267 71234567', 'government', 'large', 'risk_assessment', 'urgent', 'in_progress', 'Comprehensive cybersecurity risk assessment for financial systems', 'Assessment of payment systems, data protection, and network security', DATE_ADD(CURDATE(), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
('CYB-2025-002', 'Mascom Wireless', 'Thato Kgomo', 'thato@mascom.bw', '+267 72345678', 'telecommunications', 'enterprise', 'compliance_review', 'important', 'reviewing', 'Annual compliance review for data protection regulations', 'GDPR and local data protection compliance check', DATE_ADD(CURDATE(), INTERVAL 14 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
('CYB-2025-003', 'University of Botswana', 'Lesedi Motlhale', 'lesedi@ub.ac.bw', '+267 73456789', 'education', 'large', 'security_training', 'routine', 'submitted', 'Cybersecurity awareness training for staff and students', 'Training sessions for 500+ participants on phishing, password security, and safe browsing', DATE_ADD(CURDATE(), INTERVAL 21 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),

-- This week
('CYB-2025-004', 'First National Bank', 'Kabelo Mosweu', 'kabelo@fnb.co.bw', '+267 74567890', 'finance', 'enterprise', 'penetration_testing', 'critical', 'scheduled', 'Penetration testing of online banking platform', 'Full security assessment of web and mobile banking applications', DATE_ADD(CURDATE(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY)),
('CYB-2025-005', 'Botswana Power Corporation', 'Mmoloki Seabo', 'mmoloki@bpc.bw', '+267 75678901', 'energy', 'large', 'security_audit', 'urgent', 'in_progress', 'Security audit of SCADA systems', 'Assessment of industrial control systems and critical infrastructure', DATE_ADD(CURDATE(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 4 DAY)),

-- Last month
('CYB-2024-098', 'Choppies Enterprises', 'Khumo Mothibi', 'khumo@choppies.co.bw', '+267 76789012', 'retail', 'large', 'incident_response', 'critical', 'completed', 'Response to ransomware attack', 'Immediate incident response and recovery assistance', NULL, DATE_SUB(NOW(), INTERVAL 20 DAY)),
('CYB-2024-099', 'Botswana Telecommunications', 'Tebogo Kgosana', 'tebogo@btc.bw', '+267 77890123', 'telecommunications', 'enterprise', 'consultation', 'routine', 'completed', 'Cybersecurity strategy consultation', 'Develop 3-year cybersecurity roadmap', NULL, DATE_SUB(NOW(), INTERVAL 15 DAY)),
('CYB-2024-100', 'Ministry of Health', 'Boipelo Tau', 'boipelo@health.gov.bw', '+267 78901234', 'healthcare', 'large', 'risk_assessment', 'important', 'completed', 'Healthcare data security assessment', 'Assessment of patient data protection and hospital systems', NULL, DATE_SUB(NOW(), INTERVAL 10 DAY));

-- ═══════════════════════════════════════════════════════
-- STATUS HISTORY & UPDATES
-- ═══════════════════════════════════════════════════════

-- License status history
INSERT INTO license_status_history (application_id, old_status, new_status, changed_by, notes, created_at) VALUES
(1, 'submitted', 'under_review', 4, 'Application assigned for review', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(5, 'under_review', 'approved', 4, 'All requirements met, license approved', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(6, 'under_review', 'approved', 5, 'Approved after document verification', DATE_SUB(NOW(), INTERVAL 14 DAY)),
(7, 'under_review', 'rejected', 4, 'Incomplete technical capacity documentation', DATE_SUB(NOW(), INTERVAL 19 DAY));

-- Complaint updates
INSERT INTO complaint_updates (complaint_id, update_type, message, updated_by, is_visible_to_user, created_at) VALUES
(1, 'status_change', 'Complaint acknowledged. Investigation started with service provider.', 6, TRUE, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, 'admin_comment', 'Contacted Mascom technical team. Awaiting response on network issues in Gaborone.', 6, TRUE, DATE_SUB(NOW(), INTERVAL 12 HOUR)),
(3, 'status_change', 'Complaint received and acknowledged. Assigned to technical team.', 7, TRUE, DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(4, 'resolution', 'Yarona FM has responded to your advertising request. Contact details provided.', 6, TRUE, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(6, 'resolution', 'Service provider has implemented stricter privacy controls. Case closed.', 6, TRUE, DATE_SUB(NOW(), INTERVAL 17 DAY));

-- Cybersecurity updates
INSERT INTO cybersecurity_updates (request_id, update_type, message, updated_by, created_at) VALUES
(1, 'assignment', 'Request assigned to Senior Security Team. Assessment scheduled.', 8, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 'progress_update', 'Initial assessment completed. Preparing detailed report.', 8, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, 'status_change', 'Request under review. Compliance checklist being prepared.', 9, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(4, 'status_change', 'Penetration testing scheduled for next week. Team assigned.', 8, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(6, 'completion', 'Incident response completed. Full report delivered to organization.', 8, DATE_SUB(NOW(), INTERVAL 19 DAY));

-- ═══════════════════════════════════════════════════════
-- NOTIFICATIONS
-- ═══════════════════════════════════════════════════════

INSERT INTO notifications (user_id, type, title, message, link, is_read, created_at) VALUES
-- Licensing admin notifications
(4, 'license_update', 'New License Application', 'New telecommunications license application received from Botho Telecom', '/licensing-admin/applications.php?id=3', FALSE, DATE_SUB(NOW(), INTERVAL 5 HOUR)),
(4, 'system_alert', 'Pending Applications Alert', 'You have 3 applications pending review for more than 48 hours', '/licensing-admin/dashboard.php', FALSE, DATE_SUB(NOW(), INTERVAL 1 DAY)),

-- Complaints admin notifications
(6, 'complaint_update', 'Critical Complaint Received', 'New critical priority complaint about internet speed from Refilwe Kgosi', '/complaints-admin/complaints.php?id=3', FALSE, DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(6, 'assignment', 'Complaint Assigned', 'Network outage complaint assigned to you for investigation', '/complaints-admin/complaints.php?id=1', TRUE, DATE_SUB(NOW(), INTERVAL 2 DAY)),

-- Cybersecurity admin notifications
(8, 'cyber_update', 'Urgent Security Request', 'Ministry of Finance submitted urgent risk assessment request', '/cybersecurity-admin/requests.php?id=1', TRUE, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(8, 'assignment', 'Penetration Test Scheduled', 'FNB penetration testing scheduled for next week', '/cybersecurity-admin/requests.php?id=4', FALSE, DATE_SUB(NOW(), INTERVAL 5 DAY));

-- ═══════════════════════════════════════════════════════
-- AUDIT LOGS
-- ═══════════════════════════════════════════════════════

INSERT INTO audit_logs (user_id, actor_name, actor_role, action, entity_type, entity_id, details, ip_address, created_at) VALUES
(4, 'Licensing Administrator', 'licensing_admin', 'application_reviewed', 'license_application', 1, 'Changed status from submitted to under_review', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(6, 'Complaints Manager', 'complaints_admin', 'complaint_acknowledged', 'complaint', 1, 'Acknowledged complaint and started investigation', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(8, 'Cybersecurity Manager', 'cybersecurity_admin', 'request_assigned', 'cybersecurity_request', 1, 'Assigned to Senior Security Team', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(4, 'Licensing Administrator', 'licensing_admin', 'application_approved', 'license_application', 5, 'License approved for spectrum use', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(6, 'Complaints Manager', 'complaints_admin', 'complaint_resolved', 'complaint', 4, 'Complaint resolved with service provider cooperation', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 4 DAY));
