<?php
/**
 * BOCRA Complaints Admin - Dashboard
 * Analytics and overview for complaints management
 */

session_start();
require_once __DIR__ . '/../backend/config.php';

// Check authentication and role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'complaints_admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$user_name = $_SESSION['name'];
$user_id = $_SESSION['user_id'];

// Fetch dashboard statistics
$stats = [];

// Total complaints
$sql = "SELECT COUNT(*) as total FROM complaints";
$result = dbQuery($sql);
$stats['total_complaints'] = $result[0]['total'];

// Complaints by status
$sql = "SELECT status, COUNT(*) as count FROM complaints GROUP BY status";
$result = dbQuery($sql);
$stats['by_status'] = [];
foreach ($result as $row) {
    $stats['by_status'][$row['status']] = $row['count'];
}

// This week's complaints
$sql = "SELECT COUNT(*) as count FROM complaints WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$result = dbQuery($sql);
$stats['this_week'] = $result[0]['count'];

// Last week's complaints
$sql = "SELECT COUNT(*) as count FROM complaints WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 14 DAY) AND submitted_at < DATE_SUB(NOW(), INTERVAL 7 DAY)";
$result = dbQuery($sql);
$stats['last_week'] = $result[0]['count'];

// Calculate percentage change
if ($stats['last_week'] > 0) {
    $stats['week_change'] = round((($stats['this_week'] - $stats['last_week']) / $stats['last_week']) * 100, 1);
} else {
    $stats['week_change'] = $stats['this_week'] > 0 ? 100 : 0;
}

// Most common complaint type
$sql = "SELECT complaint_type, COUNT(*) as count FROM complaints GROUP BY complaint_type ORDER BY count DESC LIMIT 1";
$result = dbQuery($sql);
$stats['top_complaint_type'] = $result[0]['complaint_type'] ?? 'N/A';
$stats['top_complaint_count'] = $result[0]['count'] ?? 0;

// Average resolution time (in hours)
$sql = "SELECT AVG(TIMESTAMPDIFF(HOUR, submitted_at, COALESCE(resolution_date, NOW()))) as avg_hours 
        FROM complaints WHERE status IN ('resolved', 'closed')";
$result = dbQuery($sql);
$stats['avg_resolution_hours'] = round($result[0]['avg_hours'] ?? 0, 1);

// Pending complaints requiring action
$sql = "SELECT COUNT(*) as count FROM complaints WHERE status IN ('submitted', 'acknowledged', 'investigating')";
$result = dbQuery($sql);
$stats['pending_action'] = $result[0]['count'];

// Critical complaints
$sql = "SELECT COUNT(*) as count FROM complaints WHERE priority = 'critical' AND status NOT IN ('resolved', 'closed')";
$result = dbQuery($sql);
$stats['critical_open'] = $result[0]['count'];

// Complaints by type (for chart)
$sql = "SELECT complaint_type, COUNT(*) as count FROM complaints GROUP BY complaint_type ORDER BY count DESC";
$complaint_type_data = dbQuery($sql);

// Complaints by sector (for chart)
$sql = "SELECT sector, COUNT(*) as count FROM complaints GROUP BY sector ORDER BY count DESC";
$sector_data = dbQuery($sql);

// Monthly trend (last 6 months)
$sql = "SELECT DATE_FORMAT(submitted_at, '%Y-%m') as month, COUNT(*) as count 
        FROM complaints 
        WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(submitted_at, '%Y-%m')
        ORDER BY month ASC";
$monthly_trend = dbQuery($sql);

// Recent complaints
$sql = "SELECT * FROM complaints ORDER BY submitted_at DESC LIMIT 5";
$recent_complaints = dbQuery($sql);

// My assigned complaints
$sql = "SELECT * FROM complaints WHERE assigned_to = ? AND status NOT IN ('resolved', 'closed') ORDER BY priority DESC, submitted_at ASC LIMIT 5";
$my_complaints = dbQuery($sql, [$user_id]);

// Resolution rate
$sql = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status IN ('resolved', 'closed') THEN 1 ELSE 0 END) as resolved
        FROM complaints 
        WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
$result = dbQuery($sql);
$resolution_data = $result[0];
$stats['resolution_rate'] = $resolution_data['total'] > 0 ? round(($resolution_data['resolved'] / $resolution_data['total']) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaints Admin Dashboard - BOCRA</title>
    <link href="https://fonts.googleapis.com/css2?family=Forum&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/portal-styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid #D4415E;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }
        
        .stat-card.warning {
            border-left-color: #f59e0b;
        }
        
        .stat-card.success {
            border-left-color: #10b981;
        }
        
        .stat-card.danger {
            border-left-color: #ef4444;
        }
        
        .stat-card.info {
            border-left-color: #3b82f6;
        }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #FCE4EC;
            color: #D4415E;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--charcoal);
            font-family: 'Forum', serif;
        }
        
        .stat-label {
            color: var(--mid);
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-change {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .stat-change.positive {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .stat-change.negative {
            background: #d1fae5;
            color: #065f46;
        }
        
        .chart-container {
            background: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .complaints-table {
            background: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: var(--bg);
        }
        
        th {
            padding: 0.75rem 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--mid);
            font-size: 0.875rem;
            border-bottom: 2px solid var(--border);
        }
        
        td {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
            font-size: 0.9375rem;
        }
        
        tbody tr:hover {
            background: #FCE4EC;
        }
        
        .priority-critical {
            color: #dc2626;
            font-weight: 600;
        }
        
        .priority-high {
            color: #f59e0b;
            font-weight: 600;
        }
        
        .priority-medium {
            color: #3b82f6;
        }
        
        .priority-low {
            color: #6b7280;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content-header">
            <div>
                <h1>Complaints Dashboard</h1>
                <p>Overview of consumer complaints and resolution analytics</p>
            </div>
            <div style="display: flex; gap: 1rem; align-items: center;">
                <span style="color: var(--mid); font-size: 0.875rem;">
                    <i class="fas fa-clock"></i> Last updated: <?php echo date('H:i'); ?>
                </span>
                <button onclick="location.reload()" class="btn btn-secondary">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                </div>
                <div class="stat-label">Total Complaints</div>
                <div class="stat-value"><?php echo number_format($stats['total_complaints']); ?></div>
                <div style="margin-top: 0.5rem;">
                    <span class="stat-change <?php echo $stats['week_change'] >= 0 ? 'positive' : 'negative'; ?>">
                        <i class="fas fa-arrow-<?php echo $stats['week_change'] >= 0 ? 'up' : 'down'; ?>"></i>
                        <?php echo abs($stats['week_change']); ?>% from last week
                    </span>
                </div>
            </div>

            <div class="stat-card danger">
                <div class="stat-header">
                    <div class="stat-icon" style="background: #fee2e2; color: #dc2626;">
                        <i class="fas fa-fire"></i>
                    </div>
                </div>
                <div class="stat-label">Critical Open</div>
                <div class="stat-value"><?php echo $stats['critical_open']; ?></div>
                <div style="margin-top: 0.5rem; color: var(--mid); font-size: 0.875rem;">
                    Requires immediate attention
                </div>
            </div>

            <div class="stat-card warning">
                <div class="stat-header">
                    <div class="stat-icon" style="background: #fef3c7; color: #92400e;">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
                <div class="stat-label">Pending Resolution</div>
                <div class="stat-value"><?php echo $stats['pending_action']; ?></div>
                <div style="margin-top: 0.5rem; color: var(--mid); font-size: 0.875rem;">
                    Active investigations
                </div>
            </div>

            <div class="stat-card success">
                <div class="stat-header">
                    <div class="stat-icon" style="background: #d1fae5; color: #065f46;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-label">Resolution Rate (30 days)</div>
                <div class="stat-value"><?php echo $stats['resolution_rate']; ?>%</div>
                <div style="margin-top: 0.5rem; color: var(--mid); font-size: 0.875rem;">
                    <?php echo $resolution_data['resolved']; ?> of <?php echo $resolution_data['total']; ?> resolved
                </div>
            </div>

            <div class="stat-card info">
                <div class="stat-header">
                    <div class="stat-icon" style="background: #dbeafe; color: #1e40af;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="stat-label">Most Common Issue</div>
                <div class="stat-value" style="font-size: 1.25rem; text-transform: capitalize;">
                    <?php echo str_replace('_', ' ', $stats['top_complaint_type']); ?>
                </div>
                <div style="margin-top: 0.5rem; color: var(--mid); font-size: 0.875rem;">
                    <?php echo $stats['top_complaint_count']; ?> complaints
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="stat-label">Avg. Resolution Time</div>
                <div class="stat-value"><?php echo $stats['avg_resolution_hours']; ?> <span style="font-size: 1rem;">hrs</span></div>
                <div style="margin-top: 0.5rem; color: var(--mid); font-size: 0.875rem;">
                    Target: 48 hours
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
            <div class="chart-container">
                <h3 style="margin-bottom: 1.5rem;">Monthly Complaint Trend</h3>
                <canvas id="monthlyTrendChart" height="250"></canvas>
            </div>

            <div class="chart-container">
                <h3 style="margin-bottom: 1.5rem;">Complaints by Sector</h3>
                <canvas id="sectorChart" height="250"></canvas>
            </div>
        </div>

        <!-- My Assigned Complaints -->
        <?php if (count($my_complaints) > 0): ?>
        <div class="complaints-table">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3><i class="fas fa-tasks"></i> My Assigned Complaints</h3>
                <a href="complaints.php?filter=assigned_to_me" class="btn btn-primary">View All</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Complaint #</th>
                        <th>Complainant</th>
                        <th>Type</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($my_complaints as $complaint): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($complaint['complaint_number']); ?></strong></td>
                        <td><?php echo htmlspecialchars($complaint['complainant_name']); ?></td>
                        <td style="text-transform: capitalize;"><?php echo str_replace('_', ' ', $complaint['complaint_type']); ?></td>
                        <td>
                            <span class="priority-<?php echo $complaint['priority']; ?>">
                                <i class="fas fa-circle"></i> <?php echo ucfirst($complaint['priority']); ?>
                            </span>
                        </td>
                        <td><span class="status-badge status-<?php echo $complaint['status']; ?>"><?php echo str_replace('_', ' ', $complaint['status']); ?></span></td>
                        <td><?php echo date('M d, Y', strtotime($complaint['submitted_at'])); ?></td>
                        <td>
                            <a href="resolve-complaint.php?id=<?php echo $complaint['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> Manage
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Recent Complaints -->
        <div class="complaints-table">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3><i class="fas fa-history"></i> Recent Complaints</h3>
                <a href="complaints.php" class="btn btn-primary">View All</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Complaint #</th>
                        <th>Complainant</th>
                        <th>Subject</th>
                        <th>Type</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_complaints as $complaint): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($complaint['complaint_number']); ?></strong></td>
                        <td><?php echo htmlspecialchars($complaint['complainant_name']); ?></td>
                        <td><?php echo htmlspecialchars(substr($complaint['subject'], 0, 50)) . (strlen($complaint['subject']) > 50 ? '...' : ''); ?></td>
                        <td style="text-transform: capitalize;"><?php echo str_replace('_', ' ', $complaint['complaint_type']); ?></td>
                        <td>
                            <span class="priority-<?php echo $complaint['priority']; ?>">
                                <i class="fas fa-circle"></i> <?php echo ucfirst($complaint['priority']); ?>
                            </span>
                        </td>
                        <td><span class="status-badge status-<?php echo $complaint['status']; ?>"><?php echo str_replace('_', ' ', $complaint['status']); ?></span></td>
                        <td><?php echo date('M d, Y H:i', strtotime($complaint['submitted_at'])); ?></td>
                        <td>
                            <a href="resolve-complaint.php?id=<?php echo $complaint['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Monthly Trend Chart
        const monthlyCtx = document.getElementById('monthlyTrendChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($monthly_trend, 'month')); ?>,
                datasets: [{
                    label: 'Complaints',
                    data: <?php echo json_encode(array_column($monthly_trend, 'count')); ?>,
                    borderColor: '#D4415E',
                    backgroundColor: 'rgba(212, 65, 94, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Sector Chart
        const sectorCtx = document.getElementById('sectorChart').getContext('2d');
        new Chart(sectorCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_map('ucfirst', array_column($sector_data, 'sector'))); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($sector_data, 'count')); ?>,
                    backgroundColor: [
                        '#D4415E',
                        '#006B5E',
                        '#C9A227',
                        '#1E88E5'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });

        // Auto-refresh every 5 minutes
        setTimeout(() => location.reload(), 300000);
    </script>
</body>
</html>
