<?php
/**
 * BOCRA Domain Registration System - Unified Login
 */

require_once __DIR__ . '/backend/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Query user - remove sanitize to avoid issues
    $sql = "SELECT * FROM users WHERE email = ? AND status = 'active'";
    $result = dbQuery($sql, [$email]);
    
    if (!empty($result)) {
        $user = $result[0];
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['registrar_id'] = $user['registrar_id'];
            
            // Log login
            logAudit($user['name'], $user['role'], 'user_login', null, null, 'User logged in successfully');
            
            // Redirect based on role (5 roles)
            switch ($user['role']) {
                case 'registrar':
                    header('Location: ' . BASE_URL . '/registrar/dashboard.php');
                    break;
                case 'bocra':
                    header('Location: ' . BASE_URL . '/bocra/dashboard.php');
                    break;
                case 'licensing_admin':
                    header('Location: ' . BASE_URL . '/licensing-admin/dashboard.php');
                    break;
                case 'complaints_admin':
                    header('Location: ' . BASE_URL . '/complaints-admin/dashboard.php');
                    break;
                case 'cybersecurity_admin':
                    header('Location: ' . BASE_URL . '/cybersecurity-admin/dashboard.php');
                    break;
                default:
                    header('Location: ' . BASE_URL . '/index.html');
                    break;
            }
            exit;
        } else {
            $error = 'Invalid email or password';
        }
    } else {
        $error = 'Invalid email or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BOCRA Domain Registry</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Forum&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/portal-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h1>BOCRA Domain Registry</h1>
                <p>Internal Portal Access</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           placeholder="your.email@example.bw" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="Enter your password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>
            
            <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border);">
                <p style="font-size: 0.875rem; color: var(--mid); margin-bottom: 1rem;">
                    <strong>Demo Credentials:</strong>
                </p>
                <div style="background: var(--teal-light); padding: 1rem; border-radius: 6px; font-size: 0.8125rem;">
                    <p style="margin-bottom: 0.5rem;">
                        <strong>Registrar:</strong> registrar@demo.bw<br>
                        <strong>BOCRA Oversight:</strong> bocra@demo.bw<br>
                        <strong>Licensing Admin:</strong> licensing@demo.bw<br>
                        <strong>Complaints Admin:</strong> complaints@demo.bw<br>
                        <strong>Cybersecurity Admin:</strong> cybersecurity@demo.bw
                    </p>
                    <p style="margin: 0; font-style: italic;">
                        Password for all: password123
                    </p>
                </div>
            </div>
            
            <div style="margin-top: 1.5rem; text-align: center;">
                <a href="<?php echo BASE_URL; ?>/index.html" style="color: var(--teal); text-decoration: none; font-size: 0.875rem;">
                    <i class="fas fa-arrow-left"></i> Back to Main Website
                </a>
            </div>
        </div>
    </div>
</body>
</html>
