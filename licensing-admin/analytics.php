<?php
session_start();
require_once __DIR__ . '/../backend/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'licensing_admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// Get date range filter
$date_range = $_GET['range'] ?? '30';
$date_filter = "DATE_SUB(NOW(), INTERVAL $date_range DAY)";

// Total applications
$sql = "SELECT COUNT(*) as total FROM license_applications WHERE submission_date >= $date_filter";
$result = dbQuery($sql);
$total_apps = $result[0]['total'];

// Applications by status
$sql = "SELECT status, COUNT(*) as count FROM license_applications WHERE submission_date >= $date_filter GROUP BY status";
$status_data = dbQuery($sql);

// Applications by license type
$sql = "SELECT license_type, COUNT(*) as count FROM license_applications WHERE submission_date >= $date_filter GROUP BY license_type ORDER BY count DESC LIMIT 10";
$license_type_data = dbQuery($sql);

// Monthly trend (last 6 months)
$sql = "SELECT DATE_FORMAT(submission_date, '%Y-%m') as month, COUNT(*) as count 
        FROM license_applications 
        WHERE submission_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(submission_date, '%Y-%m')
        ORDER BY month ASC";
$monthly_trend = dbQuery($sql);

// Average processing time by status
$sql = "SELECT status, AVG(TIMESTAMPDIFF(DAY, submission_date, COALESCE(reviewed_at, NOW()))) as avg_days 
        FROM license_applications 
        WHERE submission_date >= $date_filter
        GROUP BY status";
$processing_times = dbQuery($sql);

// Top performing metrics
$sql = "SELECT 
    COUNT(*) as total_processed,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
    AVG(TIMESTAMPDIFF(DAY, submission_date, reviewed_at)) as avg_processing_days
FROM license_applications 
WHERE submission_date >= $date_filter AND status IN ('approved', 'rejected')";
$metrics = dbQuery($sql);
$metrics = $metrics[0];

// Applications by business type
$sql = "SELECT business_type, COUNT(*) as count FROM license_applications WHERE submission_date >= $date_filter GROUP BY business_type";
$business_type_data = dbQuery($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics & Reports - Licensing Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Forum&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/portal-styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .analytics-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .date-filter {
            display: flex;
            gap: 0.5rem;
        }
        .filter-btn {
            padding: 0.5rem 1rem;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            color: #555;
        }
        .filter-btn:hover {
            background: #f8f9fa;
        }
        .filter-btn.active {
            background: #006B5E;
            color: white;
            border-color: #006B5E;
        }
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .metric-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid #006B5E;
        }
        .metric-label {
            font-size: 0.875rem;
            color: #888;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }
        .metric-value {
            font-size: 2rem;
            font-weight: 700;
            color: #2C2C2C;
            margin-bottom: 0.25rem;
        }
        .metric-change {
            font-size: 0.875rem;
            color: #006B5E;
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
            color: #2C2C2C;
            margin-bottom: 1.5rem;
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
        .data-table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        .data-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th {
            background: #f8f9fa;
            padding: 1rem;
            text-align: left;
            font-weight: 700;
            font-size: 0.875rem;
            color: #555;
            border-bottom: 2px solid #e9ecef;
        }
        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            font-size: 0.875rem;
        }
        .progress-bar {
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 0.5rem;
        }
        .progress-fill {
            height: 100%;
            background: #006B5E;
            transition: width 0.3s;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="analytics-header">
            <div>
                <h1>Analytics & Reports</h1>
                <p>Insights and statistics for license applications</p>
            </div>
            <div class="date-filter">
                <a href="?range=7" class="filter-btn <?php echo $date_range === '7' ? 'active' : ''; ?>">7 Days</a>
                <a href="?range=30" class="filter-btn <?php echo $date_range === '30' ? 'active' : ''; ?>">30 Days</a>
                <a href="?range=90" class="filter-btn <?php echo $date_range === '90' ? 'active' : ''; ?>">90 Days</a>
                <a href="?range=365" class="filter-btn <?php echo $date_range === '365' ? 'active' : ''; ?>">1 Year</a>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-label">Total Applications</div>
                <div class="metric-value"><?php echo number_format($total_apps); ?></div>
                <div class="metric-change">Last <?php echo $date_range; ?> days</div>
            </div>
            <div class="metric-card" style="border-left-color: #C9A227;">
                <div class="metric-label">Approved</div>
                <div class="metric-value"><?php echo number_format($metrics['approved'] ?? 0); ?></div>
                <div class="metric-change">
                    <?php 
                    $approval_rate = $metrics['total_processed'] > 0 ? round(($metrics['approved'] / $metrics['total_processed']) * 100, 1) : 0;
                    echo $approval_rate . '% approval rate';
                    ?>
                </div>
            </div>
            <div class="metric-card" style="border-left-color: #D4415E;">
                <div class="metric-label">Rejected</div>
                <div class="metric-value"><?php echo number_format($metrics['rejected'] ?? 0); ?></div>
                <div class="metric-change">
                    <?php 
                    $rejection_rate = $metrics['total_processed'] > 0 ? round(($metrics['rejected'] / $metrics['total_processed']) * 100, 1) : 0;
                    echo $rejection_rate . '% rejection rate';
                    ?>
                </div>
            </div>
            <div class="metric-card" style="border-left-color: #1976D2;">
                <div class="metric-label">Avg Processing Time</div>
                <div class="metric-value"><?php echo round($metrics['avg_processing_days'] ?? 0, 1); ?></div>
                <div class="metric-change">days</div>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-grid">
            <!-- Status Distribution -->
            <div class="chart-card">
                <div class="chart-title">Applications by Status</div>
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            <!-- License Types -->
            <div class="chart-card">
                <div class="chart-title">Top License Types</div>
                <div class="chart-container">
                    <canvas id="licenseTypeChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Monthly Trend -->
        <div class="chart-card" style="margin-bottom: 2rem;">
            <div class="chart-title">Monthly Trend (Last 6 Months)</div>
            <div class="chart-container" style="height: 250px;">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        <!-- Detailed Table -->
        <div class="data-table">
            <table>
                <thead>
                    <tr>
                        <th>License Type</th>
                        <th>Applications</th>
                        <th>Percentage</th>
                        <th>Distribution</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($license_type_data as $type): 
                        $percentage = $total_apps > 0 ? round(($type['count'] / $total_apps) * 100, 1) : 0;
                    ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($type['license_type']); ?></strong></td>
                            <td><?php echo number_format($type['count']); ?></td>
                            <td><?php echo $percentage; ?>%</td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $percentage; ?>%;"></div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Status Distribution Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($status_data, 'status')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($status_data, 'count')); ?>,
                    backgroundColor: ['#006B5E', '#C9A227', '#1976D2', '#D4415E', '#888888']
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

        // License Type Chart
        const licenseCtx = document.getElementById('licenseTypeChart').getContext('2d');
        new Chart(licenseCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($license_type_data, 'license_type')); ?>,
                datasets: [{
                    label: 'Applications',
                    data: <?php echo json_encode(array_column($license_type_data, 'count')); ?>,
                    backgroundColor: '#006B5E'
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
                        beginAtZero: true
                    }
                }
            }
        });

        // Monthly Trend Chart
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        new Chart(trendCtx, {
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
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
