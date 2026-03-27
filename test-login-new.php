<?php
/**
 * Login Diagnostic Test for New Multi-Portal System
 * Tests database connection, user lookup, and password verification
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>BOCRA Multi-Portal Login Diagnostic Test</h1>";
echo "<hr>";

// Test 1: Database Connection
echo "<h2>Test 1: Database Connection</h2>";
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=bocra_system;charset=utf8mb4',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ <strong>SUCCESS:</strong> Connected to database 'bocra_system'<br>";
} catch (PDOException $e) {
    echo "❌ <strong>ERROR:</strong> " . $e->getMessage() . "<br>";
    exit;
}

// Test 2: Check if users table exists
echo "<h2>Test 2: Check Users Table</h2>";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✅ <strong>SUCCESS:</strong> 'users' table exists<br>";
    } else {
        echo "❌ <strong>ERROR:</strong> 'users' table does not exist<br>";
        exit;
    }
} catch (PDOException $e) {
    echo "❌ <strong>ERROR:</strong> " . $e->getMessage() . "<br>";
    exit;
}

// Test 3: Count total users
echo "<h2>Test 3: Count Users</h2>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ <strong>Total users in database:</strong> " . $result['count'] . "<br>";
} catch (PDOException $e) {
    echo "❌ <strong>ERROR:</strong> " . $e->getMessage() . "<br>";
}

// Test 4: List all users with their roles
echo "<h2>Test 4: List All Users</h2>";
try {
    $stmt = $pdo->query("SELECT id, name, email, role, status FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr>";
    
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . htmlspecialchars($user['name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . htmlspecialchars($user['role']) . "</td>";
        echo "<td>" . htmlspecialchars($user['status']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} catch (PDOException $e) {
    echo "❌ <strong>ERROR:</strong> " . $e->getMessage() . "<br>";
}

// Test 5: Test specific user lookup (licensing@demo.bw)
echo "<h2>Test 5: Test User Lookup (licensing@demo.bw)</h2>";
$test_email = 'licensing@demo.bw';
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$test_email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "✅ <strong>SUCCESS:</strong> User found<br>";
        echo "<strong>User ID:</strong> " . $user['id'] . "<br>";
        echo "<strong>Name:</strong> " . htmlspecialchars($user['name']) . "<br>";
        echo "<strong>Email:</strong> " . htmlspecialchars($user['email']) . "<br>";
        echo "<strong>Role:</strong> " . htmlspecialchars($user['role']) . "<br>";
        echo "<strong>Status:</strong> " . htmlspecialchars($user['status']) . "<br>";
        echo "<strong>Password Hash:</strong> " . substr($user['password'], 0, 30) . "...<br>";
    } else {
        echo "❌ <strong>ERROR:</strong> User not found with email: $test_email<br>";
    }
} catch (PDOException $e) {
    echo "❌ <strong>ERROR:</strong> " . $e->getMessage() . "<br>";
}

// Test 6: Test password verification
echo "<h2>Test 6: Test Password Verification</h2>";
$test_password = 'password123';

if (isset($user) && $user) {
    echo "<strong>Testing password:</strong> '$test_password'<br>";
    echo "<strong>Stored hash:</strong> " . $user['password'] . "<br><br>";
    
    if (password_verify($test_password, $user['password'])) {
        echo "✅ <strong>SUCCESS:</strong> Password verification PASSED<br>";
        echo "The password 'password123' is correct for this user.<br>";
    } else {
        echo "❌ <strong>FAILED:</strong> Password verification FAILED<br>";
        echo "The password 'password123' does NOT match the stored hash.<br>";
        
        // Generate a new hash for comparison
        echo "<br><strong>Debug Info:</strong><br>";
        $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
        echo "New hash generated: " . $new_hash . "<br>";
        echo "Verification of new hash: " . (password_verify($test_password, $new_hash) ? 'PASS' : 'FAIL') . "<br>";
    }
} else {
    echo "⚠️ <strong>SKIPPED:</strong> No user found to test password<br>";
}

// Test 7: Test all demo accounts
echo "<h2>Test 7: Test All Demo Accounts</h2>";
$demo_accounts = [
    'licensing@demo.bw' => 'licensing_admin',
    'complaints@demo.bw' => 'complaints_admin',
    'cybersecurity@demo.bw' => 'cybersecurity_admin',
    'registrar@demo.bw' => 'registrar',
    'bocra@demo.bw' => 'bocra'
];

echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>Email</th><th>Expected Role</th><th>User Exists?</th><th>Password Valid?</th></tr>";

foreach ($demo_accounts as $email => $expected_role) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $exists = $user ? '✅ Yes' : '❌ No';
    $password_valid = '';
    
    if ($user) {
        $password_valid = password_verify('password123', $user['password']) ? '✅ Valid' : '❌ Invalid';
    }
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($email) . "</td>";
    echo "<td>" . htmlspecialchars($expected_role) . "</td>";
    echo "<td>" . $exists . "</td>";
    echo "<td>" . $password_valid . "</td>";
    echo "</tr>";
}

echo "</table>";

// Test 8: Check backend/config.php
echo "<h2>Test 8: Check Config File</h2>";
$config_file = __DIR__ . '/backend/config.php';
if (file_exists($config_file)) {
    echo "✅ <strong>Config file exists:</strong> $config_file<br>";
    
    $config_content = file_get_contents($config_file);
    if (strpos($config_content, "bocra_system") !== false) {
        echo "✅ <strong>Config uses 'bocra_system' database</strong><br>";
    } else {
        echo "⚠️ <strong>WARNING:</strong> Config may not be using 'bocra_system' database<br>";
    }
} else {
    echo "❌ <strong>ERROR:</strong> Config file not found at: $config_file<br>";
}

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p>If all tests passed, the login should work. If password verification failed, the password hashes in the database are incorrect.</p>";
echo "<p><strong>Next step:</strong> Try logging in at <a href='login.php'>login.php</a> with:<br>";
echo "Email: licensing@demo.bw<br>";
echo "Password: password123</p>";
?>
