<?php
session_start();
require_once __DIR__ . '/../backend/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'cybersecurity_admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user = dbQuery("SELECT * FROM users WHERE id = ?", [$user_id])[0];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    dbQuery("UPDATE users SET name = ?, email = ? WHERE id = ?", [$name, $email, $user_id]);
    $_SESSION['name'] = $name;
    header('Location: settings.php?updated=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Cybersecurity Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Forum&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/portal-styles.css">
    <style>
        .settings-card { background: white; padding: 2rem; border-radius: 8px; max-width: 600px; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 0.5rem; color: #555; }
        .form-group input { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 0.875rem; }
        .alert-success { background: #E8F5E9; color: #4CAF50; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; border-left: 4px solid #4CAF50; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content-header">
            <h1>Settings</h1>
            <p>Manage your account settings and preferences</p>
        </div>

        <?php if (isset($_GET['updated'])): ?>
            <div class="alert-success"><i class="fas fa-check-circle"></i> Profile updated successfully!</div>
        <?php endif; ?>

        <div class="settings-card">
            <h2 style="margin-top: 0;">Profile Information</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <input type="text" value="Cybersecurity Administrator" disabled style="background: #f5f5f5;">
                </div>
                <button type="submit" name="update_profile" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
            </form>
        </div>
    </div>
</body>
</html>
