<?php
/**
 * Check the 3 New Admin Accounts Specifically
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Check 3 New Admin Accounts</h1>";
echo "<hr>";

// Connect to database
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=bocra_system;charset=utf8mb4',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Connected to database<br><br>";
} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "<br>";
    exit;
}

$test_accounts = [
    'licensing@demo.bw',
    'complaints@demo.bw',
    'cybersecurity@demo.bw'
];

echo "<h2>Testing Each Admin Account</h2>";

foreach ($test_accounts as $email) {
    echo "<div style='border: 2px solid #ccc; padding: 15px; margin-bottom: 20px;'>";
    echo "<h3>Testing: $email</h3>";
    
    // Query user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "✅ User found in database<br>";
        echo "<strong>ID:</strong> " . $user['id'] . "<br>";
        echo "<strong>Name:</strong> " . htmlspecialchars($user['name']) . "<br>";
        echo "<strong>Role:</strong> " . htmlspecialchars($user['role']) . "<br>";
        echo "<strong>Status:</strong> " . htmlspecialchars($user['status']) . "<br>";
        echo "<strong>Password Hash:</strong> <code style='font-size: 10px;'>" . htmlspecialchars($user['password']) . "</code><br>";
        
        // Test password
        $test_password = 'password123';
        echo "<br><strong>Testing password 'password123':</strong><br>";
        
        if (password_verify($test_password, $user['password'])) {
            echo "<span style='color: green; font-size: 18px; font-weight: bold;'>✅ PASSWORD WORKS!</span><br>";
        } else {
            echo "<span style='color: red; font-size: 18px; font-weight: bold;'>❌ PASSWORD FAILS!</span><br>";
            
            // Generate correct hash and update
            echo "<br><strong>Fixing password now...</strong><br>";
            $correct_hash = password_hash($test_password, PASSWORD_DEFAULT);
            
            $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_stmt->execute([$correct_hash, $user['id']]);
            
            echo "✅ Password updated!<br>";
            
            // Verify the fix
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $updated_user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($test_password, $updated_user['password'])) {
                echo "<span style='color: green; font-weight: bold;'>✅ VERIFIED: Password now works!</span><br>";
            } else {
                echo "<span style='color: red; font-weight: bold;'>❌ ERROR: Still doesn't work!</span><br>";
            }
        }
    } else {
        echo "<span style='color: red;'>❌ User NOT found in database!</span><br>";
    }
    
    echo "</div>";
}

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p>All 3 admin accounts have been checked and fixed if needed.</p>";
echo "<p><strong>Try logging in now at:</strong> <a href='login.php'>login.php</a></p>";
echo "<p>Use any of these emails with password: <strong>password123</strong></p>";
echo "<ul>";
echo "<li>licensing@demo.bw</li>";
echo "<li>complaints@demo.bw</li>";
echo "<li>cybersecurity@demo.bw</li>";
echo "</ul>";
?>
