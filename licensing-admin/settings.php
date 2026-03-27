<?php
session_start();
require_once __DIR__ . '/../backend/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'licensing_admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Licensing Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Forum&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/portal-styles.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content-header">
            <h1>Settings</h1>
            <p>Manage your account settings</p>
        </div>
        <div style="padding: 2rem;">
            <div class="card">
                <div class="card-body">
                    <p>This page is under development. Settings will be available here.</p>
                    <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
