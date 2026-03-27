<?php
/**
 * Fix All User Passwords
 * Updates all user passwords to correct bcrypt hash for 'password123'
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Fix All User Passwords</h1>";
echo "<hr>";

// Connect to database
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=bocra_system;charset=utf8mb4',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Connected to database 'bocra_system'<br><br>";
} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "<br>";
    exit;
}

// Generate correct password hash
$password = 'password123';
$correct_hash = password_hash($password, PASSWORD_DEFAULT);

echo "<h2>Generating New Password Hash</h2>";
echo "<strong>Password:</strong> $password<br>";
echo "<strong>New Hash:</strong> $correct_hash<br>";
echo "<strong>Verification Test:</strong> " . (password_verify($password, $correct_hash) ? '✅ PASS' : '❌ FAIL') . "<br><br>";

// Get all users
echo "<h2>Updating All Users</h2>";
$stmt = $pdo->query("SELECT id, name, email FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Status</th></tr>";

$update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");

foreach ($users as $user) {
    try {
        $update_stmt->execute([$correct_hash, $user['id']]);
        $status = "✅ Updated";
    } catch (PDOException $e) {
        $status = "❌ Failed: " . $e->getMessage();
    }
    
    echo "<tr>";
    echo "<td>" . $user['id'] . "</td>";
    echo "<td>" . htmlspecialchars($user['name']) . "</td>";
    echo "<td>" . htmlspecialchars($user['email']) . "</td>";
    echo "<td>" . $status . "</td>";
    echo "</tr>";
}

echo "</table><br>";

// Verify the fix
echo "<h2>Verification: Testing All Passwords</h2>";
$stmt = $pdo->query("SELECT id, name, email, password FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Email</th><th>Password Works?</th></tr>";

$all_good = true;
foreach ($users as $user) {
    $works = password_verify('password123', $user['password']);
    if (!$works) $all_good = false;
    
    echo "<tr>";
    echo "<td>" . $user['id'] . "</td>";
    echo "<td>" . htmlspecialchars($user['email']) . "</td>";
    echo "<td>" . ($works ? '✅ YES' : '❌ NO') . "</td>";
    echo "</tr>";
}

echo "</table><br>";

echo "<hr>";
echo "<h2>Summary</h2>";
if ($all_good) {
    echo "<p style='color: green; font-size: 18px;'><strong>✅ SUCCESS! All passwords have been fixed!</strong></p>";
    echo "<p>You can now login with any account using password: <strong>password123</strong></p>";
    echo "<p><a href='login.php' style='font-size: 18px;'>Go to Login Page</a></p>";
} else {
    echo "<p style='color: red;'><strong>❌ Some passwords still don't work. Please check the errors above.</strong></p>";
}
?>
