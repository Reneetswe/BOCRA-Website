<?php
// Direct database test - no config.php dependencies
$host = 'localhost';
$db = 'bocra_registry';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Direct Database Test</h2>";
    echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;}</style>";
    
    // Get all users
    $stmt = $pdo->query("SELECT * FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p class='success'>Found " . count($users) . " users</p>";
    
    // Test each user
    foreach ($users as $user) {
        echo "<hr>";
        echo "<h3>" . htmlspecialchars($user['name']) . "</h3>";
        echo "<p>Email: " . htmlspecialchars($user['email']) . "</p>";
        echo "<p>Role: " . $user['role'] . "</p>";
        echo "<p>Status: " . $user['status'] . "</p>";
        echo "<p>Password hash: " . htmlspecialchars($user['password']) . "</p>";
        
        // Test password
        $testPass = 'password123';
        if (password_verify($testPass, $user['password'])) {
            echo "<p class='success'>✓ Password 'password123' WORKS!</p>";
        } else {
            echo "<p class='error'>✗ Password 'password123' FAILED</p>";
            
            // Generate correct hash
            $correctHash = password_hash($testPass, PASSWORD_DEFAULT);
            echo "<p>Run this SQL to fix:</p>";
            echo "<code>UPDATE users SET password = '$correctHash' WHERE id = " . $user['id'] . ";</code>";
        }
    }
    
    echo "<hr>";
    echo "<h3>Manual Login Test</h3>";
    
    // Simulate login
    $testEmail = 'registrar@demo.bw';
    $testPassword = 'password123';
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$testEmail]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($result)) {
        $user = $result[0];
        echo "<p class='success'>User found: " . htmlspecialchars($user['name']) . "</p>";
        
        if (password_verify($testPassword, $user['password'])) {
            echo "<p class='success'>✓✓✓ LOGIN WOULD SUCCEED ✓✓✓</p>";
            echo "<p>Role: " . $user['role'] . "</p>";
            echo "<p>Would redirect to: " . ($user['role'] === 'registrar' ? 'registrar/dashboard.php' : 'bocra/dashboard.php') . "</p>";
        } else {
            echo "<p class='error'>✗✗✗ PASSWORD VERIFICATION FAILED ✗✗✗</p>";
        }
    } else {
        echo "<p class='error'>User not found with email: $testEmail</p>";
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>Database error: " . $e->getMessage() . "</p>";
}
?>
