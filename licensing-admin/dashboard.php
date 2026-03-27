<?php
/**
 * BOCRA Licensing Admin - Dashboard
 * Analytics and overview for licensing administrator
 */

session_start();
require_once __DIR__ . '/../backend/config.php';

// Check authentication and role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'licensing_admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$user_name = $_SESSION['name'];
$user_id = $_SESSION['user_id'];

// Fetch dashboard statistics
$stats = [];

// Total applications
$sql = "SELECT COUNT(*) as total FROM license_applications";
$result = dbQuery($sql);
$stats['total_applications'] = $result[0]['total'];

// Applications by status
$sql = "SELECT status, COUNT(*) as count FROM license_applications GROUP BY status";
$result = dbQuery($sql);
$stats['by_status'] = [];
foreach ($result as $row) {
    $stats['by_status'][$row['status']] = $row['count'];
}

// This week's applications
$sql = "SELECT COUNT(*) as count FROM license_applications WHERE submission_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$result = dbQuery($sql);
$stats['this_week'] = $result[0]['count'];

// Last week's applications
$sql = "SELECT COUNT(*) as count FROM license_applications WHERE submission_date >= DATE_SUB(NOW(), INTERVAL 14 DAY) AND submission_date < DATE_SUB(NOW(), INTERVAL 7 DAY)";
$result = dbQuery($sql);
$stats['last_week'] = $result[0]['count'];

// Calculate percentage change
if ($stats['last_week'] > 0) {
    $stats['week_change'] = round((($stats['this_week'] - $stats['last_week']) / $stats['last_week']) * 100, 1);
} else {
    $stats['week_change'] = $stats['this_week'] > 0 ? 100 : 0;
}

// Most requested license type
$sql = "SELECT license_type, COUNT(*) as count FROM license_applications GROUP BY license_type ORDER BY count DESC LIMIT 1";
$result = dbQuery($sql);
$stats['top_license_type'] = $result[0]['license_type'] ?? 'N/A';
$stats['top_license_count'] = $result[0]['count'] ?? 0;

// Average processing time (in days)
$sql = "SELECT AVG(TIMESTAMPDIFF(DAY, submission_date, COALESCE(reviewed_at, NOW()))) as avg_days FROM license_applications WHERE status != 'draft'";
$result = dbQuery($sql);
$stats['avg_processing_days'] = round($result[0]['avg_days'] ?? 0, 1);

// Pending applications requiring action
$sql = "SELECT COUNT(*) as count FROM license_applications WHERE status IN ('submitted', 'pending_documents')";
$result = dbQuery($sql);
$stats['pending_action'] = $result[0]['count'];

// Applications by license type (for chart)
$sql = "SELECT license_type, COUNT(*) as count FROM license_applications GROUP BY license_type ORDER BY count DESC";
$license_type_data = dbQuery($sql);

// Monthly trend (last 6 months)
$sql = "SELECT DATE_FORMAT(submission_date, '%Y-%m') as month, COUNT(*) as count 
        FROM license_applications 
        WHERE submission_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(submission_date, '%Y-%m')
        ORDER BY month ASC";
$monthly_trend = dbQuery($sql);

// Recent applications
$sql = "SELECT * FROM license_applications ORDER BY submission_date DESC LIMIT 5";
$recent_applications = dbQuery($sql);

// My assigned applications
$sql = "SELECT * FROM license_applications WHERE assigned_to = ? AND status IN ('under_review', 'pending_documents') ORDER BY submission_date ASC LIMIT 5";
$my_applications = dbQuery($sql, [$user_id]);

// Unread notifications
$sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = FALSE";
$result = dbQuery($sql, [$user_id]);
$unread_notifications = $result[0]['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Licensing Admin Dashboard - BOCRA</title>
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
            border-left: 4px solid var(--teal);
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
            background: var(--teal-light);
            color: var(--teal);
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
            background: #d1fae5;
            color: #065f46;
        }
        
        .stat-change.negative {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .chart-container {
            background: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .chart-header {
            margin-bottom: 1.5rem;
        }
        
        .chart-header h3 {
            font-size: 1.25rem;
            color: var(--charcoal);
            margin-bottom: 0.25rem;
        }
        
        .chart-header p {
            color: var(--mid);
            font-size: 0.875rem;
        }
        
        .applications-table {
            background: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .table-header h3 {
            font-size: 1.25rem;
            color: var(--charcoal);
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
            background: var(--teal-light);
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-submitted {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .status-under_review {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-approved {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .status-pending_documents {
            background: #fce7f3;
            color: #831843;
        }
        
        .action-btn {
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .action-btn.primary {
            background: var(--teal);
            color: #fff;
        }
        
        .action-btn.primary:hover {
            background: var(--teal-dark);
        }
        
        .priority-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.75rem;
        }
        
        .priority-urgent {
            color: #dc2626;
        }
        
        .priority-high {
            color: #f59e0b;
        }
        
        .priority-normal {
            color: #6b7280;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content-header">
            <div>
                <h1>Licensing Dashboard</h1>
                <p>Overview of license applications and analytics</p>
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
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
                <div class="stat-label">Total Applications</div>
                <div class="stat-value"><?php echo number_format($stats['total_applications']); ?></div>
                <div style="margin-top: 0.5rem;">
                    <span class="stat-change <?php echo $stats['week_change'] >= 0 ? 'positive' : 'negative'; ?>">
                        <i class="fas fa-arrow-<?php echo $stats['week_change'] >= 0 ? 'up' : 'down'; ?>"></i>
                        <?php echo abs($stats['week_change']); ?>% this week
                    </span>
                </div>
            </div>

            <div class="stat-card warning">
                <div class="stat-header">
                    <div class="stat-icon" style="background: #fef3c7; color: #92400e;">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
                <div class="stat-label">Pending Action</div>
                <div class="stat-value"><?php echo $stats['pending_action']; ?></div>
                <div style="margin-top: 0.5rem; color: var(--mid); font-size: 0.875rem;">
                    Requires review or documents
                </div>
            </div>

            <div class="stat-card success">
                <div class="stat-header">
                    <div class="stat-icon" style="background: #d1fae5; color: #065f46;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-label">Approved This Month</div>
                <div class="stat-value"><?php echo $stats['by_status']['approved'] ?? 0; ?></div>
                <div style="margin-top: 0.5rem; color: var(--mid); font-size: 0.875rem;">
                    <?php echo round((($stats['by_status']['approved'] ?? 0) / max($stats['total_applications'], 1)) * 100, 1); ?>% approval rate
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="stat-label">Most Requested License</div>
                <div class="stat-value" style="font-size: 1.25rem; text-transform: capitalize;">
                    <?php echo str_replace('_', ' ', $stats['top_license_type']); ?>
                </div>
                <div style="margin-top: 0.5rem; color: var(--mid); font-size: 0.875rem;">
                    <?php echo $stats['top_license_count']; ?> applications
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="stat-label">Avg. Processing Time</div>
                <div class="stat-value"><?php echo $stats['avg_processing_days']; ?> <span style="font-size: 1rem;">days</span></div>
                <div style="margin-top: 0.5rem; color: var(--mid); font-size: 0.875rem;">
                    Target: 14 days
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
                <div class="stat-label">My Assigned</div>
                <div class="stat-value"><?php echo count($my_applications); ?></div>
                <div style="margin-top: 0.5rem;">
                    <a href="applications.php?filter=assigned_to_me" style="color: var(--teal); font-size: 0.875rem; text-decoration: none;">
                        View all <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
            <div class="chart-container">
                <div class="chart-header">
                    <h3>Monthly Application Trend</h3>
                    <p>Last 6 months</p>
                </div>
                <canvas id="monthlyTrendChart" height="250"></canvas>
            </div>

            <div class="chart-container">
                <div class="chart-header">
                    <h3>Applications by License Type</h3>
                    <p>Distribution of license categories</p>
                </div>
                <canvas id="licenseTypeChart" height="250"></canvas>
            </div>
        </div>

        <!-- My Assigned Applications -->
        <?php if (count($my_applications) > 0): ?>
        <div class="applications-table">
            <div class="table-header">
                <h3><i class="fas fa-tasks"></i> My Assigned Applications</h3>
                <a href="applications.php?filter=assigned_to_me" class="action-btn primary">View All</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Application #</th>
                        <th>Applicant</th>
                        <th>License Type</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($my_applications as $app): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($app['application_number']); ?></strong></td>
                        <td><?php echo htmlspecialchars($app['applicant_name']); ?></td>
                        <td style="text-transform: capitalize;"><?php echo str_replace('_', ' ', $app['license_type']); ?></td>
                        <td>
                            <span class="priority-badge priority-<?php echo $app['priority']; ?>">
                                <i class="fas fa-circle"></i> <?php echo ucfirst($app['priority']); ?>
                            </span>
                        </td>
                        <td><span class="status-badge status-<?php echo $app['status']; ?>"><?php echo str_replace('_', ' ', $app['status']); ?></span></td>
                        <td><?php echo date('M d, Y', strtotime($app['submission_date'])); ?></td>
                        <td>
                            <a href="review-application.php?id=<?php echo $app['id']; ?>" class="action-btn primary">
                                <i class="fas fa-eye"></i> Review
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Recent Applications -->
        <div class="applications-table">
            <div class="table-header">
                <h3><i class="fas fa-history"></i> Recent Applications</h3>
                <a href="applications.php" class="action-btn primary">View All</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Application #</th>
                        <th>Applicant</th>
                        <th>License Type</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_applications as $app): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($app['application_number']); ?></strong></td>
                        <td>
                            <?php echo htmlspecialchars($app['applicant_name']); ?>
                            <?php if ($app['company_name']): ?>
                                <br><small style="color: var(--mid);"><?php echo htmlspecialchars($app['company_name']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td style="text-transform: capitalize;"><?php echo str_replace('_', ' ', $app['license_type']); ?></td>
                        <td><span class="status-badge status-<?php echo $app['status']; ?>"><?php echo str_replace('_', ' ', $app['status']); ?></span></td>
                        <td><?php echo date('M d, Y H:i', strtotime($app['submission_date'])); ?></td>
                        <td>
                            <a href="review-application.php?id=<?php echo $app['id']; ?>" class="action-btn primary">
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
                    label: 'Applications',
                    data: <?php echo json_encode(array_column($monthly_trend, 'count')); ?>,
                    borderColor: '#006B5E',
                    backgroundColor: 'rgba(0, 107, 94, 0.1)',
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

        // License Type Chart
        const typeCtx = document.getElementById('licenseTypeChart').getContext('2d');
        new Chart(typeCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_map(function($item) { return str_replace('_', ' ', ucwords($item['license_type'])); }, $license_type_data)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($license_type_data, 'count')); ?>,
                    backgroundColor: [
                        '#006B5E',
                        '#C9A227',
                        '#D4415E',
                        '#1E88E5',
                        '#10b981',
                        '#f59e0b'
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
