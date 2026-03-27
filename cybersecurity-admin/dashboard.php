<?php
/**
 * BOCRA Cybersecurity Admin - Dashboard
 * Analytics and overview for cybersecurity service requests
 */

session_start();
require_once __DIR__ . '/../backend/config.php';

// Check authentication and role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'cybersecurity_admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$user_name = $_SESSION['name'];
$user_id = $_SESSION['user_id'];

// Fetch dashboard statistics
$stats = [];

// Total requests
$sql = "SELECT COUNT(*) as total FROM cybersecurity_requests";
$result = dbQuery($sql);
$stats['total_requests'] = $result[0]['total'];

// Requests by status
$sql = "SELECT status, COUNT(*) as count FROM cybersecurity_requests GROUP BY status";
$result = dbQuery($sql);
$stats['by_status'] = [];
foreach ($result as $row) {
    $stats['by_status'][$row['status']] = $row['count'];
}

// This month's requests
$sql = "SELECT COUNT(*) as count FROM cybersecurity_requests WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
$result = dbQuery($sql);
$stats['this_month'] = $result[0]['count'];

// Last month's requests
$sql = "SELECT COUNT(*) as count FROM cybersecurity_requests WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 60 DAY) AND submitted_at < DATE_SUB(NOW(), INTERVAL 30 DAY)";
$result = dbQuery($sql);
$stats['last_month'] = $result[0]['count'];

// Calculate percentage change
if ($stats['last_month'] > 0) {
    $stats['month_change'] = round((($stats['this_month'] - $stats['last_month']) / $stats['last_month']) * 100, 1);
} else {
    $stats['month_change'] = $stats['this_month'] > 0 ? 100 : 0;
}

// Requests by sector
$sql = "SELECT sector, COUNT(*) as count FROM cybersecurity_requests GROUP BY sector ORDER BY count DESC";
$sector_data = dbQuery($sql);

// Requests by service type
$sql = "SELECT service_type, COUNT(*) as count FROM cybersecurity_requests GROUP BY service_type ORDER BY count DESC";
$service_data = dbQuery($sql);

// Pending vs Completed
$sql = "SELECT 
        SUM(CASE WHEN status IN ('submitted', 'reviewing', 'scheduled', 'in_progress') THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
        FROM cybersecurity_requests";
$result = dbQuery($sql);
$stats['pending'] = $result[0]['pending'];
$stats['completed'] = $result[0]['completed'];

// Critical/Urgent requests
$sql = "SELECT COUNT(*) as count FROM cybersecurity_requests WHERE urgency IN ('critical', 'urgent') AND status != 'completed'";
$result = dbQuery($sql);
$stats['critical_urgent'] = $result[0]['count'];

// Monthly trend
$sql = "SELECT DATE_FORMAT(submitted_at, '%Y-%m') as month, COUNT(*) as count 
        FROM cybersecurity_requests 
        WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(submitted_at, '%Y-%m')
        ORDER BY month ASC";
$monthly_trend = dbQuery($sql);

// Recent requests
$sql = "SELECT * FROM cybersecurity_requests ORDER BY submitted_at DESC LIMIT 5";
$recent_requests = dbQuery($sql);

// My assigned requests
$sql = "SELECT * FROM cybersecurity_requests WHERE assigned_to = ? AND status NOT IN ('completed', 'cancelled') ORDER BY urgency DESC, submitted_at ASC LIMIT 5";
$my_requests = dbQuery($sql, [$user_id]);

// Most requested service
$sql = "SELECT service_type, COUNT(*) as count FROM cybersecurity_requests GROUP BY service_type ORDER BY count DESC LIMIT 1";
$result = dbQuery($sql);
$stats['top_service'] = $result[0]['service_type'] ?? 'N/A';
$stats['top_service_count'] = $result[0]['count'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cybersecurity Admin Dashboard - BOCRA</title>
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
            border-left: 4px solid #1E88E5;
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
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #E3F2FD;
            color: #1E88E5;
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
        
        .requests-table {
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
            background: #E3F2FD;
        }
        
        .urgency-critical {
            color: #dc2626;
            font-weight: 600;
        }
        
        .urgency-urgent {
            color: #f59e0b;
            font-weight: 600;
        }
        
        .urgency-important {
            color: #3b82f6;
        }
        
        .urgency-routine {
            color: #6b7280;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content-header">
            <div>
                <h1>Cybersecurity Dashboard</h1>
                <p>Overview of cybersecurity service requests and analytics</p>
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
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                    <div class="stat-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                </div>
                <div class="stat-label">Total Requests</div>
                <div class="stat-value"><?php echo number_format($stats['total_requests']); ?></div>
                <div style="margin-top: 0.5rem;">
                    <span class="stat-change <?php echo $stats['month_change'] >= 0 ? 'positive' : 'negative'; ?>">
                        <i class="fas fa-arrow-<?php echo $stats['month_change'] >= 0 ? 'up' : 'down'; ?>"></i>
                        <?php echo abs($stats['month_change']); ?>% this month
                    </span>
                </div>
            </div>

            <div class="stat-card warning">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                    <div class="stat-icon" style="background: #fef3c7; color: #92400e;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="stat-label">Critical/Urgent</div>
                <div class="stat-value"><?php echo $stats['critical_urgent']; ?></div>
                <div style="margin-top: 0.5rem; color: var(--mid); font-size: 0.875rem;">
                    Requires immediate attention
                </div>
            </div>

            <div class="stat-card" style="border-left-color: #f59e0b;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                    <div class="stat-icon" style="background: #fef3c7; color: #92400e;">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
                <div class="stat-label">Pending</div>
                <div class="stat-value"><?php echo $stats['pending']; ?></div>
                <div style="margin-top: 0.5rem; color: var(--mid); font-size: 0.875rem;">
                    In progress or scheduled
                </div>
            </div>

            <div class="stat-card success">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                    <div class="stat-icon" style="background: #d1fae5; color: #065f46;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-label">Completed</div>
                <div class="stat-value"><?php echo $stats['completed']; ?></div>
                <div style="margin-top: 0.5rem; color: var(--mid); font-size: 0.875rem;">
                    <?php echo $stats['total_requests'] > 0 ? round(($stats['completed'] / $stats['total_requests']) * 100, 1) : 0; ?>% completion rate
                </div>
            </div>

            <div class="stat-card">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="stat-label">Most Requested Service</div>
                <div class="stat-value" style="font-size: 1.25rem; text-transform: capitalize;">
                    <?php echo str_replace('_', ' ', $stats['top_service']); ?>
                </div>
                <div style="margin-top: 0.5rem; color: var(--mid); font-size: 0.875rem;">
                    <?php echo $stats['top_service_count']; ?> requests
                </div>
            </div>

            <div class="stat-card">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-label">My Assigned</div>
                <div class="stat-value"><?php echo count($my_requests); ?></div>
                <div style="margin-top: 0.5rem;">
                    <a href="requests.php?filter=assigned_to_me" style="color: #1E88E5; font-size: 0.875rem; text-decoration: none;">
                        View all <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
            <div class="chart-container">
                <h3 style="margin-bottom: 1.5rem;">Requests by Sector</h3>
                <canvas id="sectorChart" height="250"></canvas>
            </div>

            <div class="chart-container">
                <h3 style="margin-bottom: 1.5rem;">Services Requested</h3>
                <canvas id="serviceChart" height="250"></canvas>
            </div>

            <div class="chart-container">
                <h3 style="margin-bottom: 1.5rem;">Pending vs Completed</h3>
                <canvas id="statusChart" height="250"></canvas>
            </div>
        </div>

        <!-- Monthly Trend -->
        <div class="chart-container">
            <h3 style="margin-bottom: 1.5rem;">Monthly Request Trend</h3>
            <canvas id="monthlyTrendChart" height="150"></canvas>
        </div>

        <!-- My Assigned Requests -->
        <?php if (count($my_requests) > 0): ?>
        <div class="requests-table">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3><i class="fas fa-tasks"></i> My Assigned Requests</h3>
                <a href="requests.php?filter=assigned_to_me" class="btn btn-primary">View All</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Request #</th>
                        <th>Organization</th>
                        <th>Sector</th>
                        <th>Service Type</th>
                        <th>Urgency</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($my_requests as $request): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($request['request_number']); ?></strong></td>
                        <td><?php echo htmlspecialchars($request['organization_name']); ?></td>
                        <td style="text-transform: capitalize;"><?php echo $request['sector']; ?></td>
                        <td style="text-transform: capitalize;"><?php echo str_replace('_', ' ', $request['service_type']); ?></td>
                        <td>
                            <span class="urgency-<?php echo $request['urgency']; ?>">
                                <i class="fas fa-circle"></i> <?php echo ucfirst($request['urgency']); ?>
                            </span>
                        </td>
                        <td><span class="status-badge status-<?php echo $request['status']; ?>"><?php echo str_replace('_', ' ', $request['status']); ?></span></td>
                        <td><?php echo date('M d, Y', strtotime($request['submitted_at'])); ?></td>
                        <td>
                            <a href="manage-request.php?id=<?php echo $request['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> Manage
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Recent Requests -->
        <div class="requests-table">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3><i class="fas fa-history"></i> Recent Requests</h3>
                <a href="requests.php" class="btn btn-primary">View All</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Request #</th>
                        <th>Organization</th>
                        <th>Contact Person</th>
                        <th>Sector</th>
                        <th>Service Type</th>
                        <th>Urgency</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_requests as $request): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($request['request_number']); ?></strong></td>
                        <td><?php echo htmlspecialchars($request['organization_name']); ?></td>
                        <td><?php echo htmlspecialchars($request['contact_person']); ?></td>
                        <td style="text-transform: capitalize;"><?php echo $request['sector']; ?></td>
                        <td style="text-transform: capitalize;"><?php echo str_replace('_', ' ', $request['service_type']); ?></td>
                        <td>
                            <span class="urgency-<?php echo $request['urgency']; ?>">
                                <i class="fas fa-circle"></i> <?php echo ucfirst($request['urgency']); ?>
                            </span>
                        </td>
                        <td><span class="status-badge status-<?php echo $request['status']; ?>"><?php echo str_replace('_', ' ', $request['status']); ?></span></td>
                        <td><?php echo date('M d, Y H:i', strtotime($request['submitted_at'])); ?></td>
                        <td>
                            <a href="manage-request.php?id=<?php echo $request['id']; ?>" class="btn btn-sm btn-primary">
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
        // Sector Chart
        const sectorCtx = document.getElementById('sectorChart').getContext('2d');
        new Chart(sectorCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_map('ucfirst', array_column($sector_data, 'sector'))); ?>,
                datasets: [{
                    label: 'Requests',
                    data: <?php echo json_encode(array_column($sector_data, 'count')); ?>,
                    backgroundColor: '#1E88E5'
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

        // Service Type Chart
        const serviceCtx = document.getElementById('serviceChart').getContext('2d');
        new Chart(serviceCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_map(function($item) { return str_replace('_', ' ', ucwords($item['service_type'])); }, $service_data)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($service_data, 'count')); ?>,
                    backgroundColor: [
                        '#1E88E5',
                        '#43A047',
                        '#FB8C00',
                        '#E53935',
                        '#8E24AA',
                        '#00ACC1'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            font: {
                                size: 10
                            }
                        }
                    }
                }
            }
        });

        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: ['Pending', 'Completed'],
                datasets: [{
                    data: [<?php echo $stats['pending']; ?>, <?php echo $stats['completed']; ?>],
                    backgroundColor: ['#f59e0b', '#10b981']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Monthly Trend Chart
        const monthlyCtx = document.getElementById('monthlyTrendChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($monthly_trend, 'month')); ?>,
                datasets: [{
                    label: 'Requests',
                    data: <?php echo json_encode(array_column($monthly_trend, 'count')); ?>,
                    borderColor: '#1E88E5',
                    backgroundColor: 'rgba(30, 136, 229, 0.1)',
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

        // Auto-refresh every 5 minutes
        setTimeout(() => location.reload(), 300000);
    </script>
</body>
</html>
