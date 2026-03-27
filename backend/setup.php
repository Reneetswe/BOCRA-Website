<?php
// To install dependencies open XAMPP Shell and run:
// cd C:\xampp\htdocs\bocra-website\backend
// composer install

// BOCRA-Website — Setup Script
// Run this to initialize the database and create admin user

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/migration.php';
require_once __DIR__ . '/helpers/response.php';

header('Content-Type: text/plain; charset=UTF-8');

echo "BOCRA Website Setup Script\n";
echo "========================\n\n";

try {
    echo "1. Testing database connection...\n";
    $db = getDB();
    echo "   ✓ Database connection successful\n\n";
    
    echo "2. Running database migrations...\n";
    runMigrations();
    echo "   ✓ All migrations completed\n\n";
    
    echo "3. Seeding initial data...\n";
    seedData();
    echo "   ✓ Admin user created\n\n";
    
    echo "4. Verifying setup...\n";
    
    // Check tables exist
    $tables = ['users', 'sessions', 'applications', 'documents', 'audit_log', 'migrations'];
    foreach ($tables as $table) {
        $result = $db->query("SHOW TABLES LIKE '$table'");
        if ($result->rowCount() > 0) {
            echo "   ✓ Table '$table' exists\n";
        } else {
            echo "   ✗ Table '$table' missing\n";
        }
    }
    
    // Check admin user
    $stmt = $db->prepare('SELECT email, status FROM users WHERE email = ?');
    $stmt->execute(['admin@bocra.org.bw']);
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "   ✓ Admin user exists ({$admin['email']}, status: {$admin['status']})\n";
    } else {
        echo "   ✗ Admin user missing\n";
    }
    
    echo "\n5. Setup complete!\n";
    echo "\nDefault Admin Credentials:\n";
    echo "Email: admin@bocra.org.bw\n";
    echo "Password: Admin@1234\n";
    echo "\nAccess the licensing portal at:\n";
    echo "http://localhost/bocra-website/licensing-portal.html\n";
    
} catch (Exception $e) {
    echo "\n❌ SETUP FAILED\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting:\n";
    echo "1. Ensure XAMPP MySQL is running (green in Control Panel)\n";
    echo "2. Ensure database 'bocra_website' exists in phpMyAdmin\n";
    echo "3. Check database credentials in config/database.php\n";
    echo "4. Ensure storage folders exist and are writable\n";
}
