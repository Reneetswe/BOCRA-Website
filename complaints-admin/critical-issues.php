<?php
session_start();
require_once __DIR__ . '/../backend/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'complaints_admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

// Get all high priority complaints that are not resolved/closed
$sql = "SELECT * FROM complaints 
        WHERE priority = 'high' 
        AND status NOT IN ('resolved', 'closed')
        ORDER BY submitted_at DESC";
$critical_complaints = dbQuery($sql);

// Get statistics - using simple queries
$stats = [
    'total' => 0,
    'new_count' => 0,
    'investigating' => 0,
    'unassigned' => 0,
    'avg_age_hours' => 0
];

try {
    $total_result = dbQuery("SELECT COUNT(*) as total FROM complaints WHERE priority = 'high' AND status NOT IN ('resolved', 'closed')");
    $stats['total'] = $total_result[0]['total'] ?? 0;
    
    $new_result = dbQuery("SELECT COUNT(*) as count FROM complaints WHERE priority = 'high' AND status = 'submitted'");
    $stats['new_count'] = $new_result[0]['count'] ?? 0;
    
    $investigating_result = dbQuery("SELECT COUNT(*) as count FROM complaints WHERE priority = 'high' AND status = 'under_investigation'");
    $stats['investigating'] = $investigating_result[0]['count'] ?? 0;
    
    $unassigned_result = dbQuery("SELECT COUNT(*) as count FROM complaints WHERE priority = 'high' AND assigned_to IS NULL AND status NOT IN ('resolved', 'closed')");
    $stats['unassigned'] = $unassigned_result[0]['count'] ?? 0;
} catch (Exception $e) {
    // Stats remain at 0
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Critical Issues - Complaints Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Forum&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/portal-styles.css">
    <style>
        .alert-banner {
            background: linear-gradient(135deg, #D4415E 0%, #C62828 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .alert-banner i {
            font-size: 2rem;
        }
        .alert-content h2 {
            margin: 0 0 0.5rem 0;
            font-size: 1.5rem;
        }
        .alert-content p {
            margin: 0;
            opacity: 0.9;
        }
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
            border-left: 4px solid #D4415E;
        }
        .stat-card.new { border-left-color: #C9A227; }
        .stat-card.investigating { border-left-color: #1976D2; }
        .stat-card.unassigned { border-left-color: #F57C00; }
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
        .complaints-table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #f5f5f5;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.875rem;
            color: #555;
            border-bottom: 2px solid #ddd;
        }
        td {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.875rem;
        }
        tr:hover {
            background: #fff9f9;
        }
        .urgent-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #D4415E;
            border-radius: 50%;
            margin-right: 0.5rem;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
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
        .action-btn {
            padding: 0.5rem 1rem;
            background: #D4415E;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.875rem;
            text-decoration: none;
            display: inline-block;
        }
        .action-btn:hover {
            background: #C62828;
        }
        .age-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .age-new { background: #E8F5E9; color: #4CAF50; }
        .age-warning { background: #FFF3E0; color: #F57C00; }
        .age-critical { background: #FFEBEE; color: #D4415E; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <?php if ($stats['total'] > 0): ?>
            <div class="alert-banner">
                <i class="fas fa-exclamation-triangle"></i>
                <div class="alert-content">
                    <h2>Critical Issues Requiring Attention</h2>
                    <p><?php echo $stats['total']; ?> high-priority complaints need immediate review. <?php echo $stats['unassigned']; ?> are unassigned.</p>
                </div>
            </div>
        <?php endif; ?>

        <div class="content-header">
            <h1>Critical Issues</h1>
            <p>High-priority complaints requiring immediate attention</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Critical</div>
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
            <div class="stat-card unassigned">
                <div class="stat-label">Unassigned</div>
                <div class="stat-value"><?php echo $stats['unassigned']; ?></div>
            </div>
        </div>

        <div class="complaints-table">
            <table>
                <thead>
                    <tr>
                        <th>Complaint #</th>
                        <th>Complainant</th>
                        <th>Type</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Age</th>
                        <th>Assigned To</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($critical_complaints) > 0): ?>
                        <?php foreach ($critical_complaints as $complaint): 
                            $age_hours = round((time() - strtotime($complaint['submitted_at'])) / 3600);
                            $age_class = $age_hours < 24 ? 'age-new' : ($age_hours < 48 ? 'age-warning' : 'age-critical');
                        ?>
                            <tr>
                                <td>
                                    <span class="urgent-indicator"></span>
                                    <strong><?php echo htmlspecialchars($complaint['complaint_number']); ?></strong>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($complaint['complainant_name']); ?><br>
                                    <small style="color: #888;"><?php echo htmlspecialchars($complaint['complainant_email']); ?></small>
                                </td>
                                <td><?php echo ucfirst(str_replace('_', ' ', $complaint['complaint_type'])); ?></td>
                                <td><?php echo htmlspecialchars(substr($complaint['subject'], 0, 40)) . (strlen($complaint['subject']) > 40 ? '...' : ''); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $complaint['status']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="age-badge <?php echo $age_class; ?>">
                                        <?php echo $age_hours; ?>h
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    if ($complaint['assigned_to']) {
                                        $assigned_sql = "SELECT name FROM users WHERE id = ?";
                                        $assigned_user = dbQuery($assigned_sql, [$complaint['assigned_to']]);
                                        echo htmlspecialchars($assigned_user[0]['name'] ?? 'Unknown');
                                    } else {
                                        echo '<span style="color: #F57C00; font-weight: 600;">Unassigned</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="resolve-complaint.php?id=<?php echo $complaint['id']; ?>" class="action-btn">
                                        <i class="fas fa-bolt"></i> Urgent Action
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 3rem; color: #888;">
                                <i class="fas fa-check-circle" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3; color: #4CAF50;"></i>
                                <p>No critical issues at the moment. Great work!</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
