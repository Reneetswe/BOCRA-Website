<?php
require_once __DIR__ . '/../backend/config.php';
requireBOCRA();
$user = getCurrentUser();

// Get metrics
$metrics = dbQuery("SELECT * FROM bocra_metrics")[0];

// Get monthly trend
$trend = dbQuery("SELECT 
    DATE_FORMAT(registration_date, '%Y-%m') as month,
    COUNT(*) as count
    FROM domains
    WHERE registration_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(registration_date, '%Y-%m')
    ORDER BY month");

// Get domains by status
$byStatus = dbQuery("SELECT status, COUNT(*) as count FROM domains GROUP BY status");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BOCRA Oversight Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Forum&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/portal-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="top-bar">
                <div>
                    <h1>BOCRA Oversight Dashboard</h1>
                    <p>Domain registration monitoring and compliance</p>
                </div>
            </div>
            
            <div class="content-area">
                <!-- KPI Cards -->
                <div class="kpi-grid">
                    <div class="kpi-card">
                        <div class="kpi-icon"><i class="fas fa-building"></i></div>
                        <div class="kpi-content">
                            <h3><?php echo $metrics['total_registrars']; ?></h3>
                            <p>Total Registrars</p>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon"><i class="fas fa-globe"></i></div>
                        <div class="kpi-content">
                            <h3><?php echo $metrics['total_domains']; ?></h3>
                            <p>Total Domains</p>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="kpi-content">
                            <h3><?php echo $metrics['active_domains']; ?></h3>
                            <p>Active Domains</p>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon"><i class="fas fa-clock"></i></div>
                        <div class="kpi-content">
                            <h3><?php echo $metrics['pending_applications']; ?></h3>
                            <p>Pending Applications</p>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon"><i class="fas fa-exclamation-triangle"></i></div>
                        <div class="kpi-content">
                            <h3><?php echo $metrics['open_compliance_flags']; ?></h3>
                            <p>Compliance Alerts</p>
                        </div>
                    </div>
                </div>
                
                <!-- Charts -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Registrations by Month</h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="trendChart"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Domains by Status</h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="statusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <a href="incoming-registrations.php" class="btn btn-primary">
                                <i class="fas fa-inbox"></i> View Incoming Registrations
                            </a>
                            <a href="domain-monitoring.php" class="btn btn-secondary">
                                <i class="fas fa-search"></i> Monitor Domains
                            </a>
                            <a href="compliance-alerts.php" class="btn btn-warning">
                                <i class="fas fa-flag"></i> Compliance Alerts
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Trend Chart
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($trend, 'month')); ?>,
                datasets: [{
                    label: 'Registrations',
                    data: <?php echo json_encode(array_column($trend, 'count')); ?>,
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
                    legend: { display: false }
                }
            }
        });
        
        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($byStatus, 'status')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($byStatus, 'count')); ?>,
                    backgroundColor: ['#28A745', '#FFC107', '#DC3545', '#6C757D', '#17A2B8']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>
</body>
</html>
