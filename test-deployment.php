<?php
/**
 * BOCRA Website Deployment Test Script
 * Run this script to test if everything is working correctly
 */

echo "🧪 BOCRA Website Deployment Test\n";
echo "=================================\n\n";

$tests_passed = 0;
$tests_failed = 0;

function test($name, $test_function) {
    global $tests_passed, $tests_failed;
    echo "📋 Testing: $name\n";
    
    try {
        $result = $test_function();
        if ($result) {
            echo "✅ PASSED\n";
            $tests_passed++;
        } else {
            echo "❌ FAILED\n";
            $tests_failed++;
        }
    } catch (Exception $e) {
        echo "❌ FAILED: " . $e->getMessage() . "\n";
        $tests_failed++;
    }
    echo "\n";
}

// Test PHP version
test("PHP Version", function() {
    return version_compare(PHP_VERSION, '7.4.0', '>=');
});

// Test required extensions
test("Required PHP Extensions", function() {
    $required = ['pdo', 'pdo_mysql', 'json', 'mbstring', 'curl'];
    foreach ($required as $ext) {
        if (!extension_loaded($ext)) {
            return false;
        }
    }
    return true;
});

// Test database connection
test("Database Connection", function() {
    try {
        require_once 'backend/config.php';
        $pdo = getDB();
        return $pdo !== null;
    } catch (Exception $e) {
        return false;
    }
});

// Test database tables
test("Database Tables", function() {
    try {
        require_once 'backend/config.php';
        $pdo = getDB();
        $tables = ['users', 'registrars', 'applicants', 'domains', 'applications', 'complaints', 'cybersecurity_incidents', 'audit_logs'];
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() === 0) {
                return false;
            }
        }
        return true;
    } catch (Exception $e) {
        return false;
    }
});

// Test CORS headers
test("CORS Headers", function() {
    if (!file_exists('api/cors-header.php')) {
        return false;
    }
    $content = file_get_contents('api/cors-header.php');
    return strpos($content, 'Access-Control-Allow-Origin') !== false;
});

// Test .htaccess exists
test(".htaccess Configuration", function() {
    return file_exists('.htaccess');
});

// Test main files exist
test("Main Files Exist", function() {
    $required_files = [
        'index.html',
        'login.php',
        'logout.php',
        'about.html',
        'chat-widget.php',
        'backend/config.php'
    ];
    foreach ($required_files as $file) {
        if (!file_exists($file)) {
            return false;
        }
    }
    return true;
});

// Test API endpoints exist
test("API Endpoints Exist", function() {
    $api_files = [
        'api/submit-complaint.php',
        'api/submit-license-application.php',
        'api/submit-cybersecurity-request.php',
        'api/check-complaint-status.php',
        'api/check-application-status.php'
    ];
    foreach ($api_files as $file) {
        if (!file_exists($file)) {
            return false;
        }
    }
    return true;
});

// Test file permissions
test("File Permissions", function() {
    $writable_dirs = ['storage', 'assets'];
    foreach ($writable_dirs as $dir) {
        if (is_dir($dir) && !is_writable($dir)) {
            return false;
        }
    }
    return true;
});

// Test admin user exists
test("Default Admin User", function() {
    try {
        require_once 'backend/config.php';
        $pdo = getDB();
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'bocra' AND email = 'admin@bocra.org.bw'");
        $result = $stmt->fetch();
        return $result['count'] > 0;
    } catch (Exception $e) {
        return false;
    }
});

// Test chat widget
test("Chat Widget File", function() {
    return file_exists('chat-widget.php');
});

// Test security configuration
test("Security Configuration", function() {
    if (!file_exists('.htaccess')) {
        return false;
    }
    $htaccess = file_get_contents('.htaccess');
    $security_headers = [
        'X-Content-Type-Options',
        'X-Frame-Options',
        'X-XSS-Protection'
    ];
    foreach ($security_headers as $header) {
        if (strpos($htaccess, $header) === false) {
            return false;
        }
    }
    return true;
});

// Test production config exists
test("Production Configuration", function() {
    return file_exists('backend/config.production.php');
});

// Test deployment files
test("Deployment Files", function() {
    $deployment_files = [
        'deploy.php',
        'setup-database.php',
        'README-DEPLOYMENT.md',
        'DEPLOYMENT-CHECKLIST.md'
    ];
    foreach ($deployment_files as $file) {
        if (!file_exists($file)) {
            return false;
        }
    }
    return true;
});

// Test gitignore
test(".gitignore File", function() {
    return file_exists('.gitignore');
});

// Test maintenance page
test("Maintenance Page", function() {
    return file_exists('maintenance.html');
});

// Results
echo "📊 Test Results:\n";
echo "================\n";
echo "✅ Passed: $tests_passed\n";
echo "❌ Failed: $tests_failed\n";
echo "📈 Success Rate: " . round(($tests_passed / ($tests_passed + $tests_failed)) * 100, 2) . "%\n\n";

if ($tests_failed === 0) {
    echo "🎉 All tests passed! Ready for deployment!\n\n";
    echo "📋 Next Steps:\n";
    echo "1. Review DEPLOYMENT-CHECKLIST.md\n";
    echo "2. Update production configuration\n";
    echo "3. Set up SSL certificate\n";
    echo "4. Deploy to production server\n";
    echo "5. Run this test script on production\n";
    echo "6. Monitor error logs\n";
} else {
    echo "⚠️  Some tests failed. Please fix the issues before deploying.\n\n";
    echo "🔧 Common fixes:\n";
    echo "- Run: php setup-database.php\n";
    echo "- Check file permissions: chmod 755 storage/\n";
    echo "- Install missing PHP extensions\n";
    echo "- Update database credentials\n";
}

echo "\n📞 For support: admin@bocra.org.bw | +267 395-7755\n";
?>
