# BOCRA Multi-Portal System - Complete Setup Guide

## 🎯 System Overview

This is a comprehensive multi-role internal portal system with **5 distinct roles** and real-time bidirectional communication between public portals and admin systems.

### 5 User Roles
1. **Registrar** - Domain registrar portal
2. **BOCRA Oversight** - Regulatory oversight portal
3. **Licensing Admin** - License application management
4. **Complaints Admin** - Consumer complaint resolution
5. **Cybersecurity Admin** - Cybersecurity service requests

---

## 📋 Prerequisites

- XAMPP installed with Apache and MySQL running
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser (Chrome, Firefox, Edge)

---

## 🚀 Installation Steps

### Step 1: Database Setup

1. **Open phpMyAdmin**
   - Navigate to `http://localhost/phpmyadmin`

2. **Import the Extended Schema**
   ```sql
   -- The system will create a new database called 'bocra_system'
   ```
   - Click on "Import" tab
   - Choose file: `C:\xampp\htdocs\BOCRA-Website\database\schema_extended.sql`
   - Click "Go" to execute

3. **Import Seed Data**
   - After schema import completes
   - Click on "Import" tab again
   - Choose file: `C:\xampp\htdocs\BOCRA-Website\database\seed_extended.sql`
   - Click "Go" to execute

4. **Verify Database**
   - You should see database `bocra_system` with these tables:
     - users
     - registrars
     - license_applications
     - license_status_history
     - complaints
     - complaint_updates
     - cybersecurity_requests
     - cybersecurity_updates
     - applicants
     - domains
     - domain_applications
     - audit_logs
     - notifications
     - compliance_flags

### Step 2: Update Configuration

1. **Check Database Connection**
   - Open `C:\xampp\htdocs\BOCRA-Website\backend\config.php`
   - Verify database name is set to `bocra_system`:
   ```php
   define('DB_NAME', 'bocra_system');
   ```

2. **Update if Necessary**
   - If config.php still references old database, update it:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'bocra_system');
   ```

### Step 3: Test the System

1. **Access Login Page**
   - Navigate to: `http://localhost/BOCRA-Website/login.php`

2. **Test Each Role**

   **Licensing Admin:**
   - Email: `licensing@demo.bw`
   - Password: `password123`
   - Should redirect to: `/licensing-admin/dashboard.php`

   **Complaints Admin:**
   - Email: `complaints@demo.bw`
   - Password: `password123`
   - Should redirect to: `/complaints-admin/dashboard.php`

   **Cybersecurity Admin:**
   - Email: `cybersecurity@demo.bw`
   - Password: `password123`
   - Should redirect to: `/cybersecurity-admin/dashboard.php`

   **Registrar:**
   - Email: `registrar@demo.bw`
   - Password: `password123`
   - Should redirect to: `/registrar/dashboard.php`

   **BOCRA Oversight:**
   - Email: `bocra@demo.bw`
   - Password: `password123`
   - Should redirect to: `/bocra/dashboard.php`

---

## 📊 Dashboard Features

### Licensing Admin Dashboard
- **Analytics:**
  - Total applications with week-over-week % change
  - Pending action count
  - Approved applications this month
  - Most requested license type
  - Average processing time
  - Assigned applications

- **Charts:**
  - Monthly application trend (6 months)
  - Applications by license type (doughnut chart)

- **Features:**
  - Review applications
  - Approve/Reject/Request documents
  - Status history tracking
  - Assignment management
  - Real-time notifications

### Complaints Admin Dashboard
- **Analytics:**
  - Total complaints with week-over-week % change
  - Critical open complaints
  - Pending resolution count
  - Resolution rate (30 days)
  - Most common issue type
  - Average resolution time

- **Charts:**
  - Monthly complaint trend (6 months)
  - Complaints by sector (doughnut chart)

- **Features:**
  - Manage complaints
  - Resolve issues
  - Send feedback to users
  - Priority-based sorting
  - Real-time notifications

### Cybersecurity Admin Dashboard
- **Analytics:**
  - Total requests with month-over-month % change
  - Critical/Urgent requests
  - Pending vs Completed
  - Most requested service
  - Assigned requests

- **Charts:**
  - Requests by sector (bar chart)
  - Services requested (doughnut chart)
  - Pending vs Completed (pie chart)
  - Monthly request trend (6 months)

- **Features:**
  - Manage security requests
  - Assign to team members
  - Track progress
  - Complete assessments
  - Real-time notifications

---

## 🔗 API Endpoints

### For Public Portals

**Submit License Application:**
```
POST /api/submit-license-application.php
Content-Type: application/json

{
  "applicant_name": "John Doe",
  "applicant_email": "john@example.bw",
  "applicant_phone": "+267 71234567",
  "company_name": "Example Corp",
  "license_type": "telecommunications",
  "business_type": "company",
  "registration_number": "BW00123456",
  "tax_number": "C12345678",
  "physical_address": "Plot 123, Gaborone",
  "business_description": "...",
  "proposed_services": "..."
}
```

**Submit Complaint:**
```
POST /api/submit-complaint.php
Content-Type: application/json

{
  "complainant_name": "Jane Smith",
  "complainant_email": "jane@example.bw",
  "complainant_phone": "+267 72345678",
  "complaint_type": "service_quality",
  "service_provider": "Provider Name",
  "sector": "telecommunications",
  "subject": "Poor service quality",
  "description": "...",
  "desired_outcome": "..."
}
```

**Submit Cybersecurity Request:**
```
POST /api/submit-cybersecurity-request.php
Content-Type: application/json

{
  "organization_name": "Ministry of Finance",
  "contact_person": "John Doe",
  "contact_email": "john@finance.gov.bw",
  "contact_phone": "+267 73456789",
  "sector": "government",
  "organization_size": "large",
  "service_type": "risk_assessment",
  "urgency": "urgent",
  "description": "...",
  "specific_requirements": "...",
  "preferred_date": "2025-04-15"
}
```

**Check Application Status:**
```
GET /api/check-application-status.php?application_number=LIC-2025-001&email=john@example.bw
```

**Check Complaint Status:**
```
GET /api/check-complaint-status.php?complaint_number=CMP-2025-001&email=jane@example.bw
```

---

## 🔄 Workflow Examples

### License Application Workflow

1. **User submits application** via public portal
2. **System creates** application record with status "submitted"
3. **Notification sent** to licensing admin
4. **Admin reviews** application in dashboard
5. **Admin can:**
   - Start review (status → "under_review")
   - Request documents (status → "pending_documents")
   - Approve (status → "approved")
   - Reject (status → "rejected")
6. **User receives** email notification of status change
7. **User can check** status anytime via API

### Complaint Resolution Workflow

1. **User submits complaint** via public portal
2. **System creates** complaint with auto-priority
3. **Notification sent** to complaints admin
4. **Admin acknowledges** complaint
5. **Admin investigates** and updates user
6. **Admin resolves** complaint with feedback
7. **User receives** resolution notification
8. **User can check** updates anytime via API

### Cybersecurity Request Workflow

1. **Organization submits** request via public portal
2. **System creates** request record
3. **Notification sent** to cybersecurity admin
4. **Admin reviews** and assigns to team
5. **Team schedules** assessment
6. **Team completes** assessment
7. **Admin uploads** report and findings
8. **Organization receives** completion notification

---

## 📁 File Structure

```
BOCRA-Website/
├── api/
│   ├── submit-license-application.php
│   ├── submit-complaint.php
│   ├── submit-cybersecurity-request.php
│   ├── check-application-status.php
│   └── check-complaint-status.php
├── licensing-admin/
│   ├── dashboard.php
│   ├── sidebar.php
│   └── review-application.php
├── complaints-admin/
│   ├── dashboard.php
│   └── sidebar.php
├── cybersecurity-admin/
│   ├── dashboard.php
│   └── sidebar.php
├── registrar/
│   └── (existing files)
├── bocra/
│   └── (existing files)
├── backend/
│   ├── config.php
│   └── helpers.php
├── database/
│   ├── schema_extended.sql
│   └── seed_extended.sql
├── assets/
│   ├── css/
│   └── js/
├── login.php
├── logout.php
└── index.html
```

---

## 🎨 Design Consistency

- **Fonts:**
  - Headings: Forum (serif)
  - Body: Lato (sans-serif)

- **Color Scheme:**
  - Teal (#006B5E) - Licensing, Primary
  - Rose (#D4415E) - Complaints
  - Blue (#1E88E5) - Cybersecurity
  - Gold (#C9A227) - Accents

- **Components:**
  - Chart.js for analytics
  - Font Awesome icons
  - Responsive grid layouts
  - Modal dialogs
  - Status badges

---

## 🔧 Troubleshooting

### Database Connection Issues
- Verify XAMPP MySQL is running
- Check `backend/config.php` credentials
- Ensure database `bocra_system` exists

### Login Not Working
- Verify database has been imported
- Check user exists in `users` table
- Password should be: `password123`
- Clear browser cache/cookies

### Dashboard Not Loading
- Check PHP error logs in XAMPP
- Verify all required tables exist
- Check file permissions

### Charts Not Displaying
- Ensure Chart.js CDN is accessible
- Check browser console for errors
- Verify data is being returned from queries

---

## 📈 Next Steps

1. **Customize Content:**
   - Update company information
   - Add real license types
   - Configure email notifications

2. **Add More Features:**
   - Document upload functionality
   - Email integration
   - SMS notifications
   - Advanced reporting

3. **Security Enhancements:**
   - Implement CSRF protection
   - Add rate limiting
   - Enable HTTPS
   - Implement 2FA

4. **Production Deployment:**
   - Change default passwords
   - Configure production database
   - Set up backups
   - Enable error logging

---

## 📞 Support

For issues or questions:
- Check the troubleshooting section
- Review the code comments
- Consult the database schema
- Check audit logs for system activity

---

## ✅ System Verification Checklist

- [ ] Database `bocra_system` created
- [ ] All tables imported successfully
- [ ] Seed data loaded (8 demo users)
- [ ] Config.php updated with correct database
- [ ] Login page accessible
- [ ] All 5 roles can login successfully
- [ ] Licensing admin dashboard loads with charts
- [ ] Complaints admin dashboard loads with charts
- [ ] Cybersecurity admin dashboard loads with charts
- [ ] API endpoints respond correctly
- [ ] Notifications system working

---

**System Version:** 1.0  
**Last Updated:** March 2025  
**Database:** bocra_system  
**PHP Version Required:** 7.4+
