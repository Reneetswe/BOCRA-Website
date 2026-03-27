<?php
// To install dependencies open XAMPP Shell and run:
// cd C:\xampp\htdocs\bocra-website\backend
// composer install

// BOCRA-Website — Application Configuration
// Project: C:\xampp\htdocs\bocra-website\

define('APP_NAME',     'BOCRA Website');
define('APP_URL',
  'http://localhost/bocra-website');
define('FRONTEND_URL',
  'http://localhost/bocra-website');
define('STORAGE_PATH',
  dirname(__DIR__, 2) . DIRECTORY_SEPARATOR .
  'storage' . DIRECTORY_SEPARATOR);
define('SESSION_EXPIRY', 86400);

// Mail — update before going live
define('MAIL_HOST',      'smtp.gmail.com');
define('MAIL_PORT',      587);
define('MAIL_USERNAME',  'your@gmail.com');
define('MAIL_PASSWORD',  'your_app_password');
define('MAIL_FROM',      'licensing@bocra.org.bw');
define('MAIL_FROM_NAME', 'BOCRA Website');

define('ALLOWED_ORIGINS', [
  'http://localhost',
  'http://localhost/bocra-website',
  'http://127.0.0.1',
  'http://127.0.0.1/bocra-website',
]);

/*
 ================================================
  XAMPP SETUP STEPS — READ BEFORE RUNNING
 ================================================

 1. START XAMPP
    Open XAMPP Control Panel
    Click Start next to Apache
    Click Start next to MySQL
    Both must show green before anything works.

 2. CREATE THE DATABASE
    Open: http://localhost/phpmyadmin
    Click "New" in left sidebar
    Database name: bocra_website
    Collation: utf8mb4_unicode_ci
    Click "Create"
    Click on bocra_website in left sidebar
    Click "Import" tab at the top
    Click "Choose File"
    Select: C:\xampp\htdocs\bocra-website\
            database\bocra_website.sql
    Click "Go" at the bottom
    You should see a success message.

 3. INSTALL PHP DEPENDENCIES
    Open XAMPP Shell
    (in XAMPP Control Panel click "Shell" button)
    Type these commands one at a time:
      cd C:\xampp\htdocs\bocra-website\backend
      composer install
    Wait for it to finish downloading packages.
    You should see a vendor/ folder appear.

 4. CREATE STORAGE FOLDERS
    In Windows Explorer create these folders
    if they do not already exist:
      C:\xampp\htdocs\bocra-website\storage\
      C:\xampp\htdocs\bocra-website\storage\documents\
      C:\xampp\htdocs\bocra-website\storage\forms\
      C:\xampp\htdocs\bocra-website\storage\logs\

 5. TEST THE API
    Open browser and go to:
    http://localhost/bocra-website/backend/api/me.php
    Expected response:
    {"success":false,"error":"Unauthorised..."}
    That response means the API is working correctly.

 6. MAIL SETUP (optional for demo)
    Edit this file: backend/config/config.php
    Set MAIL_USERNAME to your Gmail address
    Set MAIL_PASSWORD to your Gmail App Password
    To get an App Password:
      Gmail > Account > Security >
      2-Step Verification > App Passwords

 7. OPEN THE PORTAL
    http://localhost/bocra-website/
    http://localhost/bocra-website/licensing-portal.html

/*
 QUICK VERIFICATION CHECKLIST
 ─────────────────────────────
 After setup, test these URLs in your browser:

 ✓ http://localhost/bocra-website/
   Should show: BOCRA Website landing page

 ✓ http://localhost/bocra-website/licensing-portal.html
   Should show: Licensing portal register form

 ✓ http://localhost/bocra-website/backend/api/me.php
   Should return:
   {"success":false,"error":"Unauthorised..."}
   (This confirms Apache + PHP + routing works)

 ✓ http://localhost/phpmyadmin
   Click bocra_website in left sidebar
   Should show 5 tables:
   users, sessions, applications, documents, audit_log

 If any URL shows an error:
   Apache not green → Start it in XAMPP Control Panel
   MySQL not green  → Start it in XAMPP Control Panel
   composer error   → Run: composer install
                      in backend/ folder
   DB error         → Re-import bocra_website.sql
*/
