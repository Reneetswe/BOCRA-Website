<?php
// Direct password fix script
$host = 'localhost';
$db = 'bocra_registry';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Password Fix Script</h2>";
    echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;}</style>";
    
    // Generate fresh password hash for 'password123'
    $correctHash = password_hash('password123', PASSWORD_DEFAULT);
    
    echo "<p>Generated new hash for 'password123': <code>" . htmlspecialchars($correctHash) . "</code></p>";
    
    // Verify the hash works
    if (password_verify('password123', $correctHash)) {
        echo "<p class='success'>✓ New hash verified successfully</p>";
    } else {
        echo "<p class='error'>✗ Hash generation failed!</p>";
        exit;
    }
    
    // Update all users
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    
    $users = [
        'registrar@demo.bw',
        'bocra@demo.bw',
        'kalahari@demo.bw'
    ];
    
    foreach ($users as $email) {
        $stmt->execute([$correctHash, $email]);
        echo "<p class='success'>✓ Updated password for: $email</p>";
    }
    
    echo "<hr>";
    echo "<h3>Verification Test</h3>";
    
    // Verify the updates worked
    $stmt = $pdo->query("SELECT * FROM users");
    $allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($allUsers as $u) {
        echo "<p><strong>" . htmlspecialchars($u['name']) . "</strong> (" . htmlspecialchars($u['email']) . "): ";
        
        if (password_verify('password123', $u['password'])) {
            echo "<span class='success'>✓ Password works!</span></p>";
        } else {
            echo "<span class='error'>✗ Still broken</span></p>";
        }
    }
    
    echo "<hr>";
    echo "<h3>Final Login Test</h3>";
    
    // Test actual login
    $testEmail = 'registrar@demo.bw';
    $testPassword = 'password123';
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$testEmail]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($result)) {
        $user = $result[0];
        
        if (password_verify($testPassword, $user['password'])) {
            echo "<p class='success' style='font-size:20px; font-weight:bold;'>✓✓✓ LOGIN WILL NOW WORK! ✓✓✓</p>";
            echo "<p>You can now login at: <a href='login.php'>login.php</a></p>";
            echo "<p>Email: registrar@demo.bw</p>";
            echo "<p>Password: password123</p>";
        } else {
            echo "<p class='error'>✗ Still failing - something is very wrong</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>Database error: " . $e->getMessage() . "</p>";
}
?>
