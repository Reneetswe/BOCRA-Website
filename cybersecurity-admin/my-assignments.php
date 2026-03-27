<?php
session_start();
require_once __DIR__ . '/../backend/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'cybersecurity_admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get my assigned requests
$sql = "SELECT * FROM cybersecurity_requests WHERE assigned_to = ? ORDER BY 
        CASE urgency WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 END,
        submitted_at DESC";
$my_requests = dbQuery($sql, [$user_id]);

// Get statistics
$stats = ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'completed' => 0, 'high_urgency' => 0];
try {
    $total_result = dbQuery("SELECT COUNT(*) as total FROM cybersecurity_requests WHERE assigned_to = ?", [$user_id]);
    $stats['total'] = $total_result[0]['total'] ?? 0;
    
    $pending_result = dbQuery("SELECT COUNT(*) as count FROM cybersecurity_requests WHERE assigned_to = ? AND status = 'pending'", [$user_id]);
    $stats['pending'] = $pending_result[0]['count'] ?? 0;
    
    $progress_result = dbQuery("SELECT COUNT(*) as count FROM cybersecurity_requests WHERE assigned_to = ? AND status = 'in_progress'", [$user_id]);
    $stats['in_progress'] = $progress_result[0]['count'] ?? 0;
    
    $completed_result = dbQuery("SELECT COUNT(*) as count FROM cybersecurity_requests WHERE assigned_to = ? AND status = 'completed'", [$user_id]);
    $stats['completed'] = $completed_result[0]['count'] ?? 0;
    
    $high_result = dbQuery("SELECT COUNT(*) as count FROM cybersecurity_requests WHERE assigned_to = ? AND urgency = 'high'", [$user_id]);
    $stats['high_urgency'] = $high_result[0]['count'] ?? 0;
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Assignments - Cybersecurity Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Forum&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/portal-styles.css">
    <style>
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #006B5E; }
        .stat-card.pending { border-left-color: #C9A227; }
        .stat-card.in-progress { border-left-color: #1976D2; }
        .stat-card.completed { border-left-color: #4CAF50; }
        .stat-card.high-urgency { border-left-color: #D4415E; }
        .stat-label { font-size: 0.875rem; color: #888; text-transform: uppercase; margin-bottom: 0.5rem; }
        .stat-value { font-size: 2rem; font-weight: 700; color: #2C2C2C; }
        .request-card { background: white; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem; border-left: 4px solid #006B5E; }
        .request-card.high { border-left-color: #D4415E; }
        .request-card.medium { border-left-color: #F57C00; }
        .request-number { font-size: 1.125rem; font-weight: 700; color: #006B5E; margin-bottom: 0.5rem; }
        .status-badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
        .status-pending { background: #FDF6E3; color: #C9A227; }
        .status-in_progress { background: #E3F2FD; color: #1976D2; }
        .status-completed { background: #E8F5E9; color: #4CAF50; }
        .urgency-badge { display: inline-block; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; margin-left: 0.5rem; }
        .urgency-high { background: #FFEBEE; color: #D4415E; }
        .urgency-medium { background: #FFF3E0; color: #F57C00; }
        .urgency-low { background: #E8F5E9; color: #4CAF50; }
        .action-btn { padding: 0.5rem 1rem; background: #006B5E; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.875rem; text-decoration: none; display: inline-block; margin-top: 1rem; }
        .action-btn:hover { background: #004D43; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content-header">
            <h1>My Assignments</h1>
            <p>Cybersecurity requests assigned to you</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card"><div class="stat-label">Total Assigned</div><div class="stat-value"><?php echo $stats['total']; ?></div></div>
            <div class="stat-card pending"><div class="stat-label">Pending</div><div class="stat-value"><?php echo $stats['pending']; ?></div></div>
            <div class="stat-card in-progress"><div class="stat-label">In Progress</div><div class="stat-value"><?php echo $stats['in_progress']; ?></div></div>
            <div class="stat-card completed"><div class="stat-label">Completed</div><div class="stat-value"><?php echo $stats['completed']; ?></div></div>
            <div class="stat-card high-urgency"><div class="stat-label">High Urgency</div><div class="stat-value"><?php echo $stats['high_urgency']; ?></div></div>
        </div>

        <?php if (count($my_requests) > 0): ?>
            <?php foreach ($my_requests as $request): ?>
                <div class="request-card <?php echo $request['urgency']; ?>">
                    <div class="request-number">
                        <?php echo htmlspecialchars($request['request_number']); ?>
                        <span class="status-badge status-<?php echo $request['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $request['status'])); ?></span>
                        <span class="urgency-badge urgency-<?php echo $request['urgency']; ?>"><?php echo strtoupper($request['urgency']); ?> URGENCY</span>
                    </div>
                    <p><strong>Organization:</strong> <?php echo htmlspecialchars($request['organization_name'] ?? 'N/A'); ?></p>
                    <p><strong>Contact:</strong> <?php echo htmlspecialchars($request['contact_person']); ?> (<?php echo htmlspecialchars($request['contact_email']); ?>)</p>
                    <p><strong>Service Type:</strong> <?php echo htmlspecialchars($request['service_type']); ?></p>
                    <p><strong>Submitted:</strong> <?php echo date('d M Y', strtotime($request['submitted_at'])); ?></p>
                    <a href="view-request.php?id=<?php echo $request['id']; ?>" class="action-btn"><i class="fas fa-eye"></i> View & Manage</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 4rem; background: white; border-radius: 8px;">
                <i class="fas fa-inbox" style="font-size: 4rem; color: #ddd; margin-bottom: 1rem;"></i>
                <h3>No Assignments</h3>
                <p style="color: #888;">You don't have any requests assigned to you.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
