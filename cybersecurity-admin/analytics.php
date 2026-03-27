<?php
session_start();
require_once __DIR__ . '/../backend/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'cybersecurity_admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Key metrics
$metrics = ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'completed' => 0, 'high_urgency' => 0];
try {
    $total_result = dbQuery("SELECT COUNT(*) as total FROM cybersecurity_requests WHERE submitted_at BETWEEN ? AND ?", [$start_date, $end_date]);
    $metrics['total'] = $total_result[0]['total'] ?? 0;
    
    $pending_result = dbQuery("SELECT COUNT(*) as count FROM cybersecurity_requests WHERE status = 'pending' AND submitted_at BETWEEN ? AND ?", [$start_date, $end_date]);
    $metrics['pending'] = $pending_result[0]['count'] ?? 0;
    
    $progress_result = dbQuery("SELECT COUNT(*) as count FROM cybersecurity_requests WHERE status = 'in_progress' AND submitted_at BETWEEN ? AND ?", [$start_date, $end_date]);
    $metrics['in_progress'] = $progress_result[0]['count'] ?? 0;
    
    $completed_result = dbQuery("SELECT COUNT(*) as count FROM cybersecurity_requests WHERE status = 'completed' AND submitted_at BETWEEN ? AND ?", [$start_date, $end_date]);
    $metrics['completed'] = $completed_result[0]['count'] ?? 0;
    
    $high_result = dbQuery("SELECT COUNT(*) as count FROM cybersecurity_requests WHERE urgency = 'high' AND submitted_at BETWEEN ? AND ?", [$start_date, $end_date]);
    $metrics['high_urgency'] = $high_result[0]['count'] ?? 0;
} catch (Exception $e) {}

// Requests by status
$by_status = dbQuery("SELECT status, COUNT(*) as count FROM cybersecurity_requests WHERE submitted_at BETWEEN ? AND ? GROUP BY status", [$start_date, $end_date]);

// Requests by urgency
$by_urgency = dbQuery("SELECT urgency, COUNT(*) as count FROM cybersecurity_requests WHERE submitted_at BETWEEN ? AND ? GROUP BY urgency", [$start_date, $end_date]);

// Monthly trend
$monthly_trend = dbQuery("SELECT DATE_FORMAT(submitted_at, '%Y-%m') as month, COUNT(*) as count FROM cybersecurity_requests WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY DATE_FORMAT(submitted_at, '%Y-%m') ORDER BY month ASC");

$completion_rate = $metrics['total'] > 0 ? round(($metrics['completed'] / $metrics['total']) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics & Reports - Cybersecurity Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Forum&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/portal-styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .date-filter { background: white; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; display: flex; gap: 1rem; align-items: end; }
        .filter-group { display: flex; flex-direction: column; gap: 0.5rem; }
        .filter-group label { font-size: 0.875rem; font-weight: 600; color: #555; }
        .filter-group input { padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; }
        .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .metric-card { background: white; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #006B5E; }
        .metric-card.completed { border-left-color: #4CAF50; }
        .metric-card.high-urgency { border-left-color: #D4415E; }
        .metric-card.completion-rate { border-left-color: #C9A227; }
        .metric-label { font-size: 0.875rem; color: #888; text-transform: uppercase; margin-bottom: 0.5rem; }
        .metric-value { font-size: 2rem; font-weight: 700; color: #2C2C2C; }
        .metric-unit { font-size: 1rem; color: #888; font-weight: 400; }
        .charts-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .chart-card { background: white; padding: 1.5rem; border-radius: 8px; }
        .chart-title { font-size: 1.125rem; font-weight: 700; margin-bottom: 1rem; color: #2C2C2C; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content-header">
            <h1>Analytics & Reports</h1>
            <p>Comprehensive cybersecurity analytics and insights</p>
        </div>

        <form method="GET" class="date-filter">
            <div class="filter-group"><label>Start Date</label><input type="date" name="start_date" value="<?php echo $start_date; ?>"></div>
            <div class="filter-group"><label>End Date</label><input type="date" name="end_date" value="<?php echo $end_date; ?>"></div>
            <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1.5rem;"><i class="fas fa-filter"></i> Apply Filter</button>
        </form>

        <div class="metrics-grid">
            <div class="metric-card"><div class="metric-label">Total Requests</div><div class="metric-value"><?php echo $metrics['total']; ?></div></div>
            <div class="metric-card completed"><div class="metric-label">Completed</div><div class="metric-value"><?php echo $metrics['completed']; ?></div></div>
            <div class="metric-card completion-rate"><div class="metric-label">Completion Rate</div><div class="metric-value"><?php echo $completion_rate; ?><span class="metric-unit">%</span></div></div>
            <div class="metric-card high-urgency"><div class="metric-label">High Urgency</div><div class="metric-value"><?php echo $metrics['high_urgency']; ?></div></div>
        </div>

        <div class="charts-grid">
            <div class="chart-card"><div class="chart-title">Requests by Status</div><canvas id="statusChart"></canvas></div>
            <div class="chart-card"><div class="chart-title">Requests by Urgency</div><canvas id="urgencyChart"></canvas></div>
            <div class="chart-card" style="grid-column: 1 / -1;"><div class="chart-title">Monthly Trend (Last 6 Months)</div><canvas id="trendChart"></canvas></div>
        </div>
    </div>

    <script>
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_map(function($s) { return ucfirst(str_replace('_', ' ', $s['status'])); }, $by_status)); ?>,
                datasets: [{ data: <?php echo json_encode(array_column($by_status, 'count')); ?>, backgroundColor: ['#C9A227', '#1976D2', '#4CAF50', '#888'] }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        new Chart(document.getElementById('urgencyChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_map(function($u) { return ucfirst($u['urgency']); }, $by_urgency)); ?>,
                datasets: [{ label: 'Requests', data: <?php echo json_encode(array_column($by_urgency, 'count')); ?>, backgroundColor: '#006B5E' }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_map(function($m) { return date('M Y', strtotime($m['month'] . '-01')); }, $monthly_trend)); ?>,
                datasets: [{ label: 'Requests', data: <?php echo json_encode(array_column($monthly_trend, 'count')); ?>, borderColor: '#006B5E', backgroundColor: 'rgba(0, 107, 94, 0.1)', tension: 0.4, fill: true }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    </script>
</body>
</html>
