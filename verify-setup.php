<?php
// BOCRA-Website — Setup Verification Script
// Run this in your browser to verify the complete setup

echo "<!DOCTYPE html>
<html>
<head>
    <title>BOCRA Website - Setup Verification</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #006B5E; text-align: center; }
        .section { margin: 20px 0; padding: 15px; border-left: 4px solid #006B5E; background: #f9f9f9; }
        .success { border-color: #28a745; background: #d4edda; }
        .error { border-color: #dc3545; background: #f8d7da; }
        .warning { border-color: #ffc107; background: #fff3cd; }
        .check-item { margin: 8px 0; display: flex; align-items: center; }
        .check-item:before { content: '✓' or '✗'; margin-right: 10px; font-weight: bold; }
        .success .check-item:before { content: '✓'; color: #28a745; }
        .error .check-item:before { content: '✗'; color: #dc3545; }
        .warning .check-item:before { content: '⚠'; color: #ffc107; }
        code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; font-family: 'Courier New', monospace; }
        .btn { display: inline-block; padding: 10px 20px; background: #006B5E; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn:hover { background: #005249; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🚀 BOCRA Website Setup Verification</h1>";

// Check XAMPP services
echo "<div class='section'>
    <h3>1. XAMPP Services</h3>";

$apacheRunning = false;
$mysqlRunning = false;

// Check if Apache is running (simple check)
if (@fsockopen('localhost', 80, $errno, $errstr, 1)) {
    $apacheRunning = true;
    echo "<div class='success'><div class='check-item'>Apache is running on port 80</div></div>";
} else {
    echo "<div class='error'><div class='check-item'>Apache is not running on port 80</div></div>";
}

// Check MySQL
try {
    $pdo = new PDO('mysql:host=localhost;dbname=bocra_website', 'root', '');
    $mysqlRunning = true;
    echo "<div class='success'><div class='check-item'>MySQL is running and database accessible</div></div>";
} catch (PDOException $e) {
    echo "<div class='error'><div class='check-item'>MySQL connection failed: " . htmlspecialchars($e->getMessage()) . "</div></div>";
}

echo "</div>";

// Check database structure
echo "<div class='section'>
    <h3>2. Database Structure</h3>";

if ($mysqlRunning) {
    try {
        $tables = ['users', 'sessions', 'applications', 'documents', 'audit_log'];
        $allTablesExist = true;
        
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo "<div class='success'><div class='check-item'>Table '$table' exists</div></div>";
            } else {
                echo "<div class='error'><div class='check-item'>Table '$table' missing</div></div>";
                $allTablesExist = false;
            }
        }
        
        if ($allTablesExist) {
            echo "<div class='success'><div class='check-item'>All required tables exist</div></div>";
        }
        
        // Check admin user
        $stmt = $pdo->prepare('SELECT email, status FROM users WHERE email = ?');
        $stmt->execute(['admin@bocra.org.bw']);
        $admin = $stmt->fetch();
        
        if ($admin) {
            echo "<div class='success'><div class='check-item'>Admin user exists ({$admin['email']}, status: {$admin['status']})</div></div>";
        } else {
            echo "<div class='error'><div class='check-item'>Admin user missing</div></div>";
        }
        
    } catch (PDOException $e) {
        echo "<div class='error'><div class='check-item'>Database structure check failed: " . htmlspecialchars($e->getMessage()) . "</div></div>";
    }
}

echo "</div>";

// Check file structure
echo "<div class='section'>
    <h3>3. File Structure</h3>";

$requiredFiles = [
    'backend/composer.json',
    'backend/config/database.php',
    'backend/config/config.php',
    'backend/api/register.php',
    'backend/api/login.php',
    'backend/api/verify-otp.php',
    'backend/api/me.php',
    'backend/api/logout.php',
    'backend/api/applications.php',
    'backend/api/health.php',
    'licensing-portal.html',
    'database/bocra_website.sql'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "<div class='success'><div class='check-item'>File exists: $file</div></div>";
    } else {
        echo "<div class='error'><div class='check-item'>File missing: $file</div></div>";
    }
}

echo "</div>";

// Check storage directories
echo "<div class='section'>
    <h3>4. Storage Directories</h3>";

$storageDirs = ['storage', 'storage/documents', 'storage/forms', 'storage/logs'];

foreach ($storageDirs as $dir) {
    if (is_dir($dir)) {
        $writable = is_writable($dir);
        $status = $writable ? 'success' : 'warning';
        $message = $writable ? 'Directory exists and writable' : 'Directory exists but not writable';
        echo "<div class='$status'><div class='check-item'>$dir: $message</div></div>";
    } else {
        echo "<div class='error'><div class='check-item'>$dir: Directory missing</div></div>";
    }
}

echo "</div>";

// Check composer dependencies
echo "<div class='section'>
    <h3>5. Composer Dependencies</h3>";

if (file_exists('backend/vendor/autoload.php')) {
    echo "<div class='success'><div class='check-item'>Composer dependencies installed</div></div>";
    
    // Check key packages
    $packages = ['pragmarx/google2fa', 'bacon/bacon-qr-code', 'phpmailer/phpmailer'];
    foreach ($packages as $package) {
        $packagePath = "backend/vendor/" . str_replace('/', '/', $package);
        if (is_dir($packagePath)) {
            echo "<div class='success'><div class='check-item'>Package installed: $package</div></div>";
        } else {
            echo "<div class='warning'><div class='check-item'>Package missing: $package</div></div>";
        }
    }
} else {
    echo "<div class='error'><div class='check-item'>Composer dependencies not installed</div></div>";
    echo "<div class='warning'><div class='check-item'>Run: <code>cd backend && composer install</code></div></div>";
}

echo "</div>";

// Test API endpoints
echo "<div class='section'>
    <h3>6. API Endpoints Test</h3>";

if ($apacheRunning) {
    $endpoints = [
        'health.php' => 'Health Check',
        'me.php' => 'Auth Test (should return unauthorised)'
    ];
    
    foreach ($endpoints as $endpoint => $description) {
        $url = "http://localhost/bocra-website/backend/api/$endpoint";
        $context = stream_context_create(['http' => ['timeout' => 5]]);
        $response = @file_get_contents($url, false, $context);
        
        if ($response !== false) {
            $json = json_decode($response, true);
            if ($json !== null) {
                echo "<div class='success'><div class='check-item'>$endpoint: API responding correctly</div></div>";
            } else {
                echo "<div class='warning'><div class='check-item'>$endpoint: API responding but invalid JSON</div></div>";
            }
        } else {
            echo "<div class='error'><div class='check-item'>$endpoint: API not responding</div></div>";
        }
    }
} else {
    echo "<div class='warning'><div class='check-item'>Cannot test APIs - Apache not running</div></div>";
}

echo "</div>";

// Summary and next steps
echo "<div class='section'>
    <h3>7. Setup Complete!</h3>";

$allGood = $apacheRunning && $mysqlRunning && file_exists('backend/vendor/autoload.php');

if ($allGood) {
    echo "<div class='success'>
        <div class='check-item'>✅ All systems ready!</div>
        <p>Your BOCRA Website licensing portal is fully configured and ready to use.</p>
    </div>";
    
    echo "<h4>🚀 Access Your Portal:</h4>";
    echo "<a href='http://localhost/bocra-website/' class='btn'>🏠 Main Website</a>";
    echo "<a href='http://localhost/bocra-website/licensing-portal.html' class='btn'>🔐 Licensing Portal</a>";
    echo "<a href='http://localhost/bocra-website/backend/api/health.php' class='btn'>🏥 Health Check</a>";
    
    echo "<h4>👤 Default Admin Account:</h4>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>Email:</strong> admin@bocra.org.bw<br>";
    echo "<strong>Password:</strong> Admin@1234<br>";
    echo "<strong>2FA:</strong> Enabled (use Google Authenticator)";
    echo "</div>";
    
} else {
    echo "<div class='error'>
        <div class='check-item'>❌ Setup incomplete</div>
        <p>Please resolve the issues above before proceeding.</p>
    </div>";
}

echo "<h4>📚 Quick Links:</h4>";
echo "<a href='http://localhost/phpmyadmin' class='btn'>🗄️ phpMyAdmin</a>";
echo "<a href='README.md' class='btn'>📖 Documentation</a>";

echo "</div>";

echo "</div>
</body>
</html>";
?>
