<?php
session_start();
require_once __DIR__ . '/../backend/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'cybersecurity_admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// Get all high urgency requests
$sql = "SELECT * FROM cybersecurity_requests WHERE urgency = 'high' AND status NOT IN ('completed', 'cancelled') ORDER BY submitted_at DESC";
$critical_requests = dbQuery($sql);

// Get statistics
$stats = ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'unassigned' => 0];
try {
    $total_result = dbQuery("SELECT COUNT(*) as total FROM cybersecurity_requests WHERE urgency = 'high' AND status NOT IN ('completed', 'cancelled')");
    $stats['total'] = $total_result[0]['total'] ?? 0;
    
    $pending_result = dbQuery("SELECT COUNT(*) as count FROM cybersecurity_requests WHERE urgency = 'high' AND status = 'pending'");
    $stats['pending'] = $pending_result[0]['count'] ?? 0;
    
    $progress_result = dbQuery("SELECT COUNT(*) as count FROM cybersecurity_requests WHERE urgency = 'high' AND status = 'in_progress'");
    $stats['in_progress'] = $progress_result[0]['count'] ?? 0;
    
    $unassigned_result = dbQuery("SELECT COUNT(*) as count FROM cybersecurity_requests WHERE urgency = 'high' AND assigned_to IS NULL AND status NOT IN ('completed', 'cancelled')");
    $stats['unassigned'] = $unassigned_result[0]['count'] ?? 0;
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Critical/Urgent - Cybersecurity Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Forum&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/portal-styles.css">
    <style>
        .alert-banner { background: linear-gradient(135deg, #D4415E 0%, #C62828 100%); color: white; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; display: flex; align-items: center; gap: 1rem; }
        .alert-banner i { font-size: 2rem; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #D4415E; }
        .stat-label { font-size: 0.875rem; color: #888; text-transform: uppercase; margin-bottom: 0.5rem; }
        .stat-value { font-size: 2rem; font-weight: 700; color: #2C2C2C; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; }
        th { background: #f5f5f5; padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem; color: #555; border-bottom: 2px solid #ddd; }
        td { padding: 1rem; border-bottom: 1px solid #f0f0f0; font-size: 0.875rem; }
        tr:hover { background: #fff9f9; }
        .urgent-indicator { display: inline-block; width: 8px; height: 8px; background: #D4415E; border-radius: 50%; margin-right: 0.5rem; animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        .status-badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
        .status-pending { background: #FDF6E3; color: #C9A227; }
        .status-in_progress { background: #E3F2FD; color: #1976D2; }
        .action-btn { padding: 0.5rem 1rem; background: #D4415E; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.875rem; text-decoration: none; display: inline-block; }
        .action-btn:hover { background: #C62828; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <?php if ($stats['total'] > 0): ?>
            <div class="alert-banner">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <h2 style="margin: 0 0 0.5rem 0;">Critical/Urgent Requests Requiring Attention</h2>
                    <p style="margin: 0; opacity: 0.9;"><?php echo $stats['total']; ?> high-urgency requests need immediate review. <?php echo $stats['unassigned']; ?> are unassigned.</p>
                </div>
            </div>
        <?php endif; ?>

        <div class="content-header">
            <h1>Critical/Urgent Requests</h1>
            <p>High-urgency cybersecurity requests requiring immediate attention</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card"><div class="stat-label">Total Critical</div><div class="stat-value"><?php echo $stats['total']; ?></div></div>
            <div class="stat-card"><div class="stat-label">Pending</div><div class="stat-value"><?php echo $stats['pending']; ?></div></div>
            <div class="stat-card"><div class="stat-label">In Progress</div><div class="stat-value"><?php echo $stats['in_progress']; ?></div></div>
            <div class="stat-card"><div class="stat-label">Unassigned</div><div class="stat-value"><?php echo $stats['unassigned']; ?></div></div>
        </div>

        <table>
            <thead>
                <tr><th>Request #</th><th>Contact Person</th><th>Organization</th><th>Service Type</th><th>Status</th><th>Age</th><th>Assigned To</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (count($critical_requests) > 0): ?>
                    <?php foreach ($critical_requests as $request): 
                        $age_hours = round((time() - strtotime($request['submitted_at'])) / 3600);
                    ?>
                        <tr>
                            <td><span class="urgent-indicator"></span><strong><?php echo htmlspecialchars($request['request_number']); ?></strong></td>
                            <td><?php echo htmlspecialchars($request['contact_person']); ?><br><small style="color: #888;"><?php echo htmlspecialchars($request['contact_email']); ?></small></td>
                            <td><?php echo htmlspecialchars($request['organization_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars(substr($request['service_type'], 0, 30)); ?></td>
                            <td><span class="status-badge status-<?php echo $request['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $request['status'])); ?></span></td>
                            <td><?php echo $age_hours; ?>h</td>
                            <td><?php 
                                if ($request['assigned_to']) {
                                    $assigned_sql = "SELECT name FROM users WHERE id = ?";
                                    $assigned_user = dbQuery($assigned_sql, [$request['assigned_to']]);
                                    echo htmlspecialchars($assigned_user[0]['name'] ?? 'Unknown');
                                } else {
                                    echo '<span style="color: #F57C00; font-weight: 600;">Unassigned</span>';
                                }
                            ?></td>
                            <td><a href="view-request.php?id=<?php echo $request['id']; ?>" class="action-btn"><i class="fas fa-bolt"></i> Urgent Action</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" style="text-align: center; padding: 3rem; color: #888;"><i class="fas fa-check-circle" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3; color: #4CAF50;"></i><p>No critical issues at the moment. Great work!</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
