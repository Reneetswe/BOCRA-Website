<?php
session_start();
require_once __DIR__ . '/../backend/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'complaints_admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

// Get my assigned complaints
$sql = "SELECT * FROM complaints WHERE assigned_to = ? ORDER BY 
        CASE priority 
            WHEN 'high' THEN 1 
            WHEN 'medium' THEN 2 
            WHEN 'low' THEN 3 
        END,
        submitted_at DESC";
$my_complaints = dbQuery($sql, [$user_id]);

// Get statistics - using simple queries
$stats = [
    'total' => 0,
    'new_count' => 0,
    'investigating' => 0,
    'resolved' => 0,
    'high_priority' => 0
];

try {
    $total_result = dbQuery("SELECT COUNT(*) as total FROM complaints WHERE assigned_to = ?", [$user_id]);
    $stats['total'] = $total_result[0]['total'] ?? 0;
    
    $new_result = dbQuery("SELECT COUNT(*) as count FROM complaints WHERE assigned_to = ? AND status = 'submitted'", [$user_id]);
    $stats['new_count'] = $new_result[0]['count'] ?? 0;
    
    $investigating_result = dbQuery("SELECT COUNT(*) as count FROM complaints WHERE assigned_to = ? AND status = 'under_investigation'", [$user_id]);
    $stats['investigating'] = $investigating_result[0]['count'] ?? 0;
    
    $resolved_result = dbQuery("SELECT COUNT(*) as count FROM complaints WHERE assigned_to = ? AND status = 'resolved'", [$user_id]);
    $stats['resolved'] = $resolved_result[0]['count'] ?? 0;
    
    $high_result = dbQuery("SELECT COUNT(*) as count FROM complaints WHERE assigned_to = ? AND priority = 'high'", [$user_id]);
    $stats['high_priority'] = $high_result[0]['count'] ?? 0;
} catch (Exception $e) {
    // Stats remain at 0
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Assignments - Complaints Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Forum&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/portal-styles.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid #006B5E;
        }
        .stat-card.new { border-left-color: #C9A227; }
        .stat-card.investigating { border-left-color: #1976D2; }
        .stat-card.resolved { border-left-color: #4CAF50; }
        .stat-card.high-priority { border-left-color: #D4415E; }
        .stat-label {
            font-size: 0.875rem;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #2C2C2C;
        }
        .complaints-grid {
            display: grid;
            gap: 1.5rem;
        }
        .complaint-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            border-left: 4px solid #006B5E;
        }
        .complaint-card.high { border-left-color: #D4415E; }
        .complaint-card.medium { border-left-color: #F57C00; }
        .complaint-card.low { border-left-color: #4CAF50; }
        .complaint-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }
        .complaint-number {
            font-size: 1.125rem;
            font-weight: 700;
            color: #006B5E;
        }
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-submitted { background: #FDF6E3; color: #C9A227; }
        .status-under_investigation { background: #E3F2FD; color: #1976D2; }
        .status-resolved { background: #E8F5E9; color: #4CAF50; }
        .priority-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }
        .priority-high { background: #FFEBEE; color: #D4415E; }
        .priority-medium { background: #FFF3E0; color: #F57C00; }
        .priority-low { background: #E8F5E9; color: #4CAF50; }
        .complaint-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .info-item {
            font-size: 0.875rem;
        }
        .info-label {
            color: #888;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }
        .info-value {
            color: #2C2C2C;
            font-weight: 600;
        }
        .complaint-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
        }
        .action-btn {
            padding: 0.5rem 1rem;
            background: #006B5E;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.875rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .action-btn:hover {
            background: #004D43;
        }
        .action-btn.secondary {
            background: #888;
        }
        .action-btn.secondary:hover {
            background: #666;
        }
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 8px;
        }
        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 1rem;
        }
        .empty-state h3 {
            color: #555;
            margin-bottom: 0.5rem;
        }
        .empty-state p {
            color: #888;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content-header">
            <h1>My Assignments</h1>
            <p>Complaints assigned to you for review and resolution</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Assigned</div>
                <div class="stat-value"><?php echo $stats['total']; ?></div>
            </div>
            <div class="stat-card new">
                <div class="stat-label">New</div>
                <div class="stat-value"><?php echo $stats['new_count']; ?></div>
            </div>
            <div class="stat-card investigating">
                <div class="stat-label">Investigating</div>
                <div class="stat-value"><?php echo $stats['investigating']; ?></div>
            </div>
            <div class="stat-card resolved">
                <div class="stat-label">Resolved</div>
                <div class="stat-value"><?php echo $stats['resolved']; ?></div>
            </div>
            <div class="stat-card high-priority">
                <div class="stat-label">High Priority</div>
                <div class="stat-value"><?php echo $stats['high_priority']; ?></div>
            </div>
        </div>

        <div class="complaints-grid">
            <?php if (count($my_complaints) > 0): ?>
                <?php foreach ($my_complaints as $complaint): ?>
                    <div class="complaint-card <?php echo $complaint['priority']; ?>">
                        <div class="complaint-header">
                            <div>
                                <div class="complaint-number"><?php echo htmlspecialchars($complaint['complaint_number']); ?></div>
                                <span class="status-badge status-<?php echo $complaint['status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                                </span>
                                <span class="priority-badge priority-<?php echo $complaint['priority']; ?>">
                                    <?php echo strtoupper($complaint['priority']); ?> PRIORITY
                                </span>
                            </div>
                            <div style="text-align: right; font-size: 0.875rem; color: #888;">
                                Submitted: <?php echo date('d M Y', strtotime($complaint['submitted_at'])); ?>
                            </div>
                        </div>

                        <div class="complaint-info">
                            <div class="info-item">
                                <div class="info-label">Complainant</div>
                                <div class="info-value"><?php echo htmlspecialchars($complaint['complainant_name']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Email</div>
                                <div class="info-value"><?php echo htmlspecialchars($complaint['complainant_email']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Type</div>
                                <div class="info-value"><?php echo ucfirst(str_replace('_', ' ', $complaint['complaint_type'])); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Service Provider</div>
                                <div class="info-value"><?php echo htmlspecialchars($complaint['service_provider'] ?? 'N/A'); ?></div>
                            </div>
                        </div>

                        <div style="margin: 1rem 0;">
                            <div class="info-label">Subject</div>
                            <div style="color: #2C2C2C;"><?php echo htmlspecialchars($complaint['subject']); ?></div>
                        </div>

                        <div class="complaint-actions">
                            <a href="resolve-complaint.php?id=<?php echo $complaint['id']; ?>" class="action-btn">
                                <i class="fas fa-eye"></i> View & Resolve
                            </a>
                            <a href="complaints.php" class="action-btn secondary">
                                <i class="fas fa-list"></i> All Complaints
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No Assignments Yet</h3>
                    <p>You don't have any complaints assigned to you at the moment.</p>
                    <a href="complaints.php" class="action-btn" style="margin-top: 1rem;">
                        <i class="fas fa-list"></i> View All Complaints
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
