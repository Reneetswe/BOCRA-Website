# BOCRA Domain Registration System - Internal Portals

A focused full-stack domain registration oversight system with two connected internal portals for Botswana domain (.bw) management.

## 🎯 Overview

This system demonstrates the integration between:
1. **Registrar Portal** - Where accredited registrars submit domain registrations
2. **BOCRA Oversight Portal** - Where BOCRA monitors and oversees all submissions

**Key Integration:** When a registrar registers a domain, it immediately appears in the BOCRA oversight portal for monitoring and compliance.

## 🏗️ System Architecture

```
BOCRA-Website/
├── database/
│   ├── schema.sql          # Database structure
│   └── seed.sql            # Demo data with Botswana names
├── backend/
│   └── config.php          # Database connection & helper functions
├── api/
│   ├── domains.php         # Domain operations API
│   ├── applications.php    # Application tracking API
│   ├── applicants.php      # Applicant management API
│   ├── registrars.php      # Registrar data API
│   ├── bocra-metrics.php   # BOCRA dashboard metrics API
│   └── audit-logs.php      # Audit trail API
├── assets/
│   └── portal-styles.css   # Shared portal styling (matching main site)
├── registrar/
│   ├── login.php           # (Uses unified login.php)
│   ├── dashboard.php       # Registrar dashboard with KPIs
│   ├── sidebar.php         # Registrar navigation component
│   ├── new-applicant.php   # Create new applicant form
│   ├── register-domain.php # Domain registration form
│   ├── domain-list.php     # View all registered domains
│   └── submission-history.php # Track all submissions
├── bocra/
│   ├── login.php           # (Uses unified login.php)
│   ├── dashboard.php       # BOCRA oversight dashboard with charts
│   ├── sidebar.php         # BOCRA navigation component
│   ├── incoming-registrations.php # View all registrar submissions
│   ├── domain-monitoring.php      # Monitor all domains
│   ├── registrar-oversight.php    # Registrar performance tracking
│   ├── compliance-alerts.php      # Compliance flags and alerts
│   └── audit-logs.php      # Complete system audit trail
├── login.php               # Unified login (auto-routes by role)
└── logout.php              # Session cleanup
```

## 🚀 Quick Start

### Prerequisites
- **XAMPP** or **Laragon** installed
- **PHP 8.0+**
- **MySQL 5.7+**
- Web browser (Chrome, Firefox, Edge)

### Installation Steps

#### 1. Database Setup

**Option A: Using phpMyAdmin**
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create database: `bocra_registry`
3. Import `database/schema.sql`
4. Import `database/seed.sql`

**Option B: Using Command Line**
```bash
# Navigate to MySQL bin folder
cd C:\xampp\mysql\bin

# Create database
mysql -u root -e "CREATE DATABASE bocra_registry CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"

# Import schema
mysql -u root bocra_registry < "C:\Users\Reneetswe windows\CascadeProjects\BOCRA-Website\database\schema.sql"

# Import seed data
mysql -u root bocra_registry < "C:\Users\Reneetswe windows\CascadeProjects\BOCRA-Website\database\seed.sql"
```

#### 2. Configuration

Edit `backend/config.php` if needed (default settings work with XAMPP):
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'bocra_registry');
define('DB_USER', 'root');
define('DB_PASS', '');
define('BASE_URL', 'http://localhost/BOCRA-Website');
```

#### 3. Access the System

**Unified Login Page:**
`http://localhost/BOCRA-Website/login.php`

**Demo Credentials:**

**Registrar Portal:**
- Email: `registrar@demo.bw`
- Password: `password123`

**BOCRA Oversight Portal:**
- Email: `bocra@demo.bw`
- Password: `password123`

The system automatically routes users to the correct portal based on their role.

## 📊 Database Schema

### Core Tables

**users** - System users (registrar and BOCRA staff)
- Stores login credentials and role information
- Links registrar users to their registrar organization

**registrars** - Accredited domain registrars
- 3 demo registrars: Pula Domains, Kalahari Digital, Botho Net
- Tracks accreditation status and contact information

**applicants** - Domain applicants (individuals or companies)
- 10 demo applicants with Botswana-style names
- Supports both individual and company types
- Includes tax numbers and registration details

**domains** - Registered .bw domains
- 15 demo domains with realistic Botswana names
- Tracks status, category, nameservers, expiry dates
- Links to applicant and registrar

**domain_applications** - Submission tracking
- Records every domain registration submission
- Tracks submission status and review process
- **This is the key integration table** - connects registrar submissions to BOCRA oversight

**audit_logs** - Complete activity trail
- Logs every major action in the system
- Tracks who did what, when, and why
- Essential for regulatory compliance

**compliance_flags** - Automated compliance monitoring
- Flags missing tax numbers, duplicate registrations, etc.
- Severity levels: low, medium, high, critical
- Status tracking: open, investigating, resolved, dismissed

## 🔄 System Integration Flow

### Domain Registration Workflow

1. **Registrar creates applicant** (registrar/new-applicant.php)
   - Applicant saved to database
   - Audit log created
   - Compliance check: missing tax number flagged if empty

2. **Registrar registers domain** (registrar/register-domain.php)
   - Domain record created with status "active"
   - Domain application record created with status "submitted"
   - Two audit logs created:
     - Registrar action: "domain_registered"
     - System action: "application_received"

3. **BOCRA sees submission immediately** (bocra/incoming-registrations.php)
   - Queries `domain_applications` table
   - Shows all submissions from all registrars
   - Real-time visibility into registrar activity

4. **BOCRA monitors compliance** (bocra/compliance-alerts.php)
   - Automated flags appear for issues
   - Manual investigation and resolution
   - Audit trail of all compliance actions

## 🎨 Design System

The internal portals match the main BOCRA website design:

**Fonts:**
- Headings: `Forum` (serif)
- Body: `Lato` (sans-serif)

**Colors:**
- Primary Teal: `#006B5E`
- Teal Dark: `#004D43`
- Teal Light: `#E8F4F2`
- Gold Accent: `#C9A227`
- Charcoal: `#2C2C2C`

**UI Components:**
- Clean sidebar navigation
- KPI cards with icons
- Professional data tables
- Status badges (color-coded)
- Chart.js visualizations

## 📋 Features by Portal

### Registrar Portal

**Dashboard**
- KPI cards: Total Applicants, Registered Domains, Pending Submissions, Active Domains
- Recent submissions table
- Quick action buttons

**New Applicant**
- Dynamic form (individual vs company)
- Tax number compliance checking
- Automatic compliance flag creation

**Register Domain**
- Select from existing applicants
- Domain name validation (.bw required)
- Category selection
- Nameserver configuration
- Registration term (1-5 years)
- Automatic submission to BOCRA

**Domain List**
- View all registered domains
- Filter and search capabilities
- Status tracking

**Submission History**
- All domain applications
- Submission status tracking
- Sync state confirmation (shows data is in BOCRA system)

### BOCRA Oversight Portal

**Dashboard**
- 5 KPI cards: Total Registrars, Total Domains, Active Domains, Pending Applications, Compliance Alerts
- Chart.js visualizations:
  - Monthly registration trend (line chart)
  - Domains by status (doughnut chart)
- Quick action buttons

**Incoming Registrations**
- **Real-time view of all registrar submissions**
- Shows registrar name, domain, applicant, status, submission date
- This is where BOCRA sees what registrars submit

**Domain Monitoring**
- All registered .bw domains
- Compliance flag indicators
- Expiry date tracking
- Category and status overview

**Registrar Oversight**
- Performance metrics for each registrar
- Total submissions, active domains, pending items
- Calculated compliance scores
- Accreditation number tracking

**Compliance Alerts**
- All compliance flags sorted by severity
- Flag types: missing tax number, duplicate registration, suspicious activity, incomplete details
- Status tracking and resolution workflow

**Audit Logs**
- Complete system activity trail
- Last 100 entries displayed
- Shows actor, role, action, domain, timestamp
- Essential for regulatory oversight

## 🔐 Security Features

- **Session-based authentication** with role checking
- **Password hashing** using PHP's password_hash()
- **SQL injection protection** via PDO prepared statements
- **XSS prevention** through input sanitization
- **Role-based access control** (registrar vs BOCRA)
- **Complete audit logging** of all actions
- **IP address tracking** in audit logs

## 📊 Demo Data

### Registrars (3)
1. Pula Domains Botswana (REG-BW-2020-001)
2. Kalahari Digital Registrar (REG-BW-2021-002)
3. Botho Net Services (REG-BW-2022-003)

### Sample Domains (15)
- tsotlhemedia.bw
- pulaenergy.bw
- dikgangtech.bw
- kgotlacapital.bw
- nalediworks.bw
- serowedigital.bw
- molepololtech.bw
- botswanaheritage.bw
- gaboronestartups.bw
- kalaharitourism.bw
- And more...

### Applicants (10)
Mix of individuals and companies with Botswana-style names:
- Tsotlhe Media Group
- Kefilwe Kgosana
- Pula Energy Solutions
- Dikgang Tech Hub
- Boitumelo Mogwe
- And more...

## 🧪 Testing the Integration

### Test Workflow: Registrar → BOCRA

1. **Login as Registrar**
   - Email: `registrar@demo.bw`
   - Password: `password123`

2. **Create New Applicant**
   - Go to "New Applicant"
   - Fill form (try both individual and company)
   - Submit

3. **Register Domain**
   - Go to "Register Domain"
   - Select the applicant you just created
   - Enter domain name (e.g., `testdomain.bw`)
   - Fill nameservers (e.g., `ns1.example.bw`, `ns2.example.bw`)
   - Submit

4. **View in Submission History**
   - Go to "Submission History"
   - See your new domain with "Synced to BOCRA" status

5. **Logout and Login as BOCRA**
   - Logout
   - Login with: `bocra@demo.bw` / `password123`

6. **Verify in BOCRA Portal**
   - Go to "Incoming Registrations"
   - **See your domain appear immediately**
   - Check "Domain Monitoring" - it's there too
   - Check "Audit Logs" - see the registration events

This demonstrates the **real-time integration** between the two portals.

## 🔧 API Endpoints

All endpoints return JSON responses.

**Domains API** (`/api/domains.php`)
- `GET ?action=list` - List all domains (with filters)
- `GET ?action=stats` - Domain statistics
- `POST ?action=register` - Register new domain

**Applications API** (`/api/applications.php`)
- `GET ?action=list` - List all applications
- `GET ?action=stats` - Application statistics

**Applicants API** (`/api/applicants.php`)
- `GET ?action=list&registrar_id=X` - List applicants for registrar
- `POST ?action=create` - Create new applicant

**Registrars API** (`/api/registrars.php`)
- `GET ?action=list` - List all registrars
- `GET ?action=stats` - Registrar statistics

**BOCRA Metrics API** (`/api/bocra-metrics.php`)
- `GET` - Dashboard metrics, trends, status breakdown

**Audit Logs API** (`/api/audit-logs.php`)
- `GET ?action=list` - Recent audit logs

## 🐛 Troubleshooting

**Database connection failed**
- Check MySQL is running in XAMPP/Laragon
- Verify database name is `bocra_registry`
- Check credentials in `backend/config.php`

**Login not working**
- Ensure seed data was imported
- Password for all demo users is `password123`
- Clear browser cache/cookies

**Pages not loading**
- Check BASE_URL in `backend/config.php`
- Ensure Apache is running
- Verify file paths are correct

**Charts not showing**
- Check browser console for errors
- Ensure Chart.js CDN is accessible
- Verify internet connection

## 📝 Key Business Logic

### Registrar Role
- **CAN:** Create applicants, register domains, view own submissions
- **CANNOT:** See other registrars' data, access BOCRA oversight features

### BOCRA Role
- **CAN:** View all registrar submissions, monitor all domains, track compliance, view audit logs
- **CANNOT:** Register domains directly (oversight only)

### Compliance Automation
- Missing tax number → Medium severity flag
- Incomplete details → Low severity flag
- System automatically creates flags during applicant/domain creation

### Audit Trail
- Every major action is logged
- Includes: actor name, role, action type, domain, applicant, timestamp, IP
- Immutable record for regulatory compliance

## 🎯 Production Considerations

For a real production deployment:

1. **Security Enhancements**
   - Use environment variables for credentials
   - Implement CSRF protection
   - Add rate limiting
   - Enable HTTPS only
   - Strengthen password requirements

2. **Performance**
   - Add database indexing
   - Implement caching (Redis/Memcached)
   - Optimize queries
   - Add pagination to all lists

3. **Features**
   - Email notifications
   - Document upload capability
   - Advanced search and filtering
   - Export to CSV/PDF
   - Two-factor authentication

4. **Monitoring**
   - Error logging
   - Performance monitoring
   - Uptime monitoring
   - Security scanning

## 📞 Support

This is a demo system for internal domain registration oversight.

**Demo Credentials:**
- Registrar: `registrar@demo.bw` / `password123`
- BOCRA: `bocra@demo.bw` / `password123`

---

**Version:** 1.0.0  
**Last Updated:** March 2026  
**Status:** Demo Ready ✅

**Built with:** PHP 8+, MySQL, HTML5, CSS3, JavaScript, Chart.js  
**Design:** Matching BOCRA main website (Forum + Lato fonts, Teal color scheme)
