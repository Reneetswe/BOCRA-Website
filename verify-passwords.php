<?php
/**
 * Verify Current Password Hashes in Database
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Verify Current Password Hashes</h1>";
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

// Check all users
$stmt = $pdo->query("SELECT id, name, email, password FROM users ORDER BY id");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Current Password Hashes in Database</h2>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse; font-size: 12px;'>";
echo "<tr><th>ID</th><th>Email</th><th>Current Hash (first 50 chars)</th><th>Verifies with 'password123'?</th></tr>";

foreach ($users as $user) {
    $hash_preview = substr($user['password'], 0, 50);
    $verifies = password_verify('password123', $user['password']);
    
    echo "<tr>";
    echo "<td>" . $user['id'] . "</td>";
    echo "<td>" . htmlspecialchars($user['email']) . "</td>";
    echo "<td style='font-family: monospace;'>" . htmlspecialchars($hash_preview) . "...</td>";
    echo "<td style='font-weight: bold; color: " . ($verifies ? 'green' : 'red') . ";'>" . ($verifies ? '✅ YES' : '❌ NO') . "</td>";
    echo "</tr>";
}

echo "</table><br>";

// Test login simulation
echo "<h2>Login Simulation Test</h2>";
$test_email = 'licensing@demo.bw';
$test_password = 'password123';

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
$stmt->execute([$test_email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "<strong>Testing login for:</strong> $test_email<br>";
    echo "<strong>Password attempt:</strong> $test_password<br>";
    echo "<strong>User found:</strong> ✅ Yes<br>";
    echo "<strong>Password hash in DB:</strong> " . substr($user['password'], 0, 60) . "...<br>";
    
    if (password_verify($test_password, $user['password'])) {
        echo "<strong style='color: green; font-size: 18px;'>✅ LOGIN SHOULD WORK!</strong><br>";
    } else {
        echo "<strong style='color: red; font-size: 18px;'>❌ LOGIN WILL FAIL - Password doesn't match!</strong><br>";
        
        // Show what the hash should be
        $correct_hash = password_hash($test_password, PASSWORD_DEFAULT);
        echo "<br><strong>What the hash SHOULD be:</strong><br>";
        echo "<code>" . $correct_hash . "</code><br>";
        echo "<strong>Verification of correct hash:</strong> " . (password_verify($test_password, $correct_hash) ? '✅ PASS' : '❌ FAIL') . "<br>";
    }
} else {
    echo "❌ User not found!<br>";
}

echo "<hr>";
echo "<h2>Action Required</h2>";
echo "<p>If passwords still show as INVALID, run: <a href='fix-all-passwords.php'>fix-all-passwords.php</a> again</p>";
echo "<p>Then try: <a href='login.php'>login.php</a></p>";
?>
