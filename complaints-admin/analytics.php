<?php
session_start();
require_once __DIR__ . '/../backend/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'complaints_admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

// Get date range from query params
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Key metrics - using simple queries
$metrics = [
    'total' => 0,
    'resolved' => 0,
    'closed' => 0,
    'high_priority' => 0,
    'avg_resolution_hours' => 0
];

try {
    $total_result = dbQuery("SELECT COUNT(*) as total FROM complaints WHERE submitted_at BETWEEN ? AND ?", [$start_date, $end_date]);
    $metrics['total'] = $total_result[0]['total'] ?? 0;
    
    $resolved_result = dbQuery("SELECT COUNT(*) as count FROM complaints WHERE status = 'resolved' AND submitted_at BETWEEN ? AND ?", [$start_date, $end_date]);
    $metrics['resolved'] = $resolved_result[0]['count'] ?? 0;
    
    $closed_result = dbQuery("SELECT COUNT(*) as count FROM complaints WHERE status = 'closed' AND submitted_at BETWEEN ? AND ?", [$start_date, $end_date]);
    $metrics['closed'] = $closed_result[0]['count'] ?? 0;
    
    $high_result = dbQuery("SELECT COUNT(*) as count FROM complaints WHERE priority = 'high' AND submitted_at BETWEEN ? AND ?", [$start_date, $end_date]);
    $metrics['high_priority'] = $high_result[0]['count'] ?? 0;
} catch (Exception $e) {
    // Metrics remain at 0
}

// Complaints by status
$sql = "SELECT status, COUNT(*) as count FROM complaints 
        WHERE submitted_at BETWEEN ? AND ?
        GROUP BY status";
$by_status = dbQuery($sql, [$start_date, $end_date]);

// Complaints by type
$sql = "SELECT complaint_type, COUNT(*) as count FROM complaints 
        WHERE submitted_at BETWEEN ? AND ?
        GROUP BY complaint_type ORDER BY count DESC LIMIT 5";
$by_type = dbQuery($sql, [$start_date, $end_date]);

// Complaints by sector
$sql = "SELECT sector, COUNT(*) as count FROM complaints 
        WHERE submitted_at BETWEEN ? AND ?
        GROUP BY sector ORDER BY count DESC";
$by_sector = dbQuery($sql, [$start_date, $end_date]);

// Monthly trend (last 6 months)
$sql = "SELECT DATE_FORMAT(submitted_at, '%Y-%m') as month, COUNT(*) as count 
        FROM complaints 
        WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(submitted_at, '%Y-%m')
        ORDER BY month ASC";
$monthly_trend = dbQuery($sql);

// Resolution rate
$resolution_rate = $metrics['total'] > 0 ? round((($metrics['resolved'] + $metrics['closed']) / $metrics['total']) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics & Reports - Complaints Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Forum&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/portal-styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .date-filter {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            align-items: end;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .filter-group label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #555;
        }
        .filter-group input {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .metric-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid #006B5E;
        }
        .metric-card.resolved { border-left-color: #4CAF50; }
        .metric-card.high-priority { border-left-color: #D4415E; }
        .metric-card.avg-time { border-left-color: #1976D2; }
        .metric-card.resolution-rate { border-left-color: #C9A227; }
        .metric-label {
            font-size: 0.875rem;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }
        .metric-value {
            font-size: 2rem;
            font-weight: 700;
            color: #2C2C2C;
        }
        .metric-unit {
            font-size: 1rem;
            color: #888;
            font-weight: 400;
        }
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .chart-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
        }
        .chart-title {
            font-size: 1.125rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #2C2C2C;
        }
        .data-table {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #f5f5f5;
            padding: 0.75rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.875rem;
            color: #555;
            border-bottom: 2px solid #ddd;
        }
        td {
            padding: 0.75rem;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content-header">
            <h1>Analytics & Reports</h1>
            <p>Comprehensive complaint analytics and insights</p>
        </div>

        <form method="GET" class="date-filter">
            <div class="filter-group">
                <label>Start Date</label>
                <input type="date" name="start_date" value="<?php echo $start_date; ?>">
            </div>
            <div class="filter-group">
                <label>End Date</label>
                <input type="date" name="end_date" value="<?php echo $end_date; ?>">
            </div>
            <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1.5rem;">
                <i class="fas fa-filter"></i> Apply Filter
            </button>
        </form>

        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-label">Total Complaints</div>
                <div class="metric-value"><?php echo $metrics['total']; ?></div>
            </div>
            <div class="metric-card resolved">
                <div class="metric-label">Resolved</div>
                <div class="metric-value"><?php echo $metrics['resolved'] + $metrics['closed']; ?></div>
            </div>
            <div class="metric-card resolution-rate">
                <div class="metric-label">Resolution Rate</div>
                <div class="metric-value"><?php echo $resolution_rate; ?><span class="metric-unit">%</span></div>
            </div>
            <div class="metric-card high-priority">
                <div class="metric-label">High Priority</div>
                <div class="metric-value"><?php echo $metrics['high_priority']; ?></div>
            </div>
            <div class="metric-card avg-time">
                <div class="metric-label">Avg Resolution Time</div>
                <div class="metric-value"><?php echo round($metrics['avg_resolution_hours'] ?? 0, 1); ?><span class="metric-unit">hrs</span></div>
            </div>
        </div>

        <div class="charts-grid">
            <div class="chart-card">
                <div class="chart-title">Complaints by Status</div>
                <canvas id="statusChart"></canvas>
            </div>
            <div class="chart-card">
                <div class="chart-title">Top Complaint Types</div>
                <canvas id="typeChart"></canvas>
            </div>
            <div class="chart-card" style="grid-column: 1 / -1;">
                <div class="chart-title">Monthly Trend (Last 6 Months)</div>
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        <div class="data-table">
            <div class="chart-title">Complaints by Sector</div>
            <table>
                <thead>
                    <tr>
                        <th>Sector</th>
                        <th>Count</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($by_sector as $sector): 
                        $percentage = $metrics['total'] > 0 ? round(($sector['count'] / $metrics['total']) * 100, 1) : 0;
                    ?>
                        <tr>
                            <td><?php echo ucfirst($sector['sector']); ?></td>
                            <td><strong><?php echo $sector['count']; ?></strong></td>
                            <td><?php echo $percentage; ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Status Chart
        const statusData = <?php echo json_encode(array_column($by_status, 'count')); ?>;
        const statusLabels = <?php echo json_encode(array_map(function($s) { return ucfirst(str_replace('_', ' ', $s['status'])); }, $by_status)); ?>;
        
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusData,
                    backgroundColor: ['#C9A227', '#1976D2', '#4CAF50', '#888']
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        // Type Chart
        const typeData = <?php echo json_encode(array_column($by_type, 'count')); ?>;
        const typeLabels = <?php echo json_encode(array_map(function($t) { return ucfirst(str_replace('_', ' ', $t['complaint_type'])); }, $by_type)); ?>;
        
        new Chart(document.getElementById('typeChart'), {
            type: 'bar',
            data: {
                labels: typeLabels,
                datasets: [{
                    label: 'Complaints',
                    data: typeData,
                    backgroundColor: '#006B5E'
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });

        // Trend Chart
        const trendData = <?php echo json_encode(array_column($monthly_trend, 'count')); ?>;
        const trendLabels = <?php echo json_encode(array_map(function($m) { return date('M Y', strtotime($m['month'] . '-01')); }, $monthly_trend)); ?>;
        
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Complaints',
                    data: trendData,
                    borderColor: '#006B5E',
                    backgroundColor: 'rgba(0, 107, 94, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    </script>
</body>
</html>
