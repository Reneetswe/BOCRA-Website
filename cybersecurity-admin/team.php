<?php
session_start();
require_once __DIR__ . '/../backend/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'cybersecurity_admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// Get all cybersecurity team members
$sql = "SELECT * FROM users WHERE role = 'cybersecurity_admin' ORDER BY name ASC";
$team_members = dbQuery($sql);

// Get workload for each team member
$workload = [];
foreach ($team_members as $member) {
    $sql = "SELECT COUNT(*) as total, SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as active FROM cybersecurity_requests WHERE assigned_to = ?";
    $result = dbQuery($sql, [$member['id']]);
    $workload[$member['id']] = $result[0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Management - Cybersecurity Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Forum&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/portal-styles.css">
    <style>
        .team-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
        .team-card { background: white; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #006B5E; }
        .team-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; }
        .team-avatar { width: 60px; height: 60px; border-radius: 50%; background: #E8F4F2; color: #006B5E; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 700; }
        .team-info h3 { margin: 0 0 0.25rem 0; font-size: 1.125rem; color: #2C2C2C; }
        .team-info p { margin: 0; font-size: 0.875rem; color: #888; }
        .workload-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #f0f0f0; }
        .workload-item { text-align: center; }
        .workload-label { font-size: 0.75rem; color: #888; text-transform: uppercase; }
        .workload-value { font-size: 1.5rem; font-weight: 700; color: #006B5E; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content-header">
            <h1>Team Management</h1>
            <p>Cybersecurity team members and their workload</p>
        </div>

        <div class="team-grid">
            <?php foreach ($team_members as $member): ?>
                <div class="team-card">
                    <div class="team-header">
                        <div class="team-avatar"><?php echo strtoupper(substr($member['name'], 0, 1)); ?></div>
                        <div class="team-info">
                            <h3><?php echo htmlspecialchars($member['name']); ?></h3>
                            <p><?php echo htmlspecialchars($member['email']); ?></p>
                        </div>
                    </div>
                    <div class="workload-stats">
                        <div class="workload-item">
                            <div class="workload-label">Total Assigned</div>
                            <div class="workload-value"><?php echo $workload[$member['id']]['total'] ?? 0; ?></div>
                        </div>
                        <div class="workload-item">
                            <div class="workload-label">Active</div>
                            <div class="workload-value"><?php echo $workload[$member['id']]['active'] ?? 0; ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
