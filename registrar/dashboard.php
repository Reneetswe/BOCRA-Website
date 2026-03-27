<?php
require_once __DIR__ . '/../backend/config.php';
requireRegistrar();
$user = getCurrentUser();

// Get statistics
$stats = dbQuery("SELECT 
    COUNT(DISTINCT a.id) as total_applicants,
    COUNT(DISTINCT d.id) as total_domains,
    COUNT(DISTINCT CASE WHEN da.submission_status = 'submitted' THEN da.id END) as pending_submissions,
    COUNT(DISTINCT CASE WHEN d.status = 'active' THEN d.id END) as active_domains
    FROM applicants a
    LEFT JOIN domains d ON a.registrar_id = d.registrar_id
    LEFT JOIN domain_applications da ON a.registrar_id = da.registrar_id
    WHERE a.registrar_id = ?", [$user['registrar_id']])[0];

// Get recent submissions
$recent = dbQuery("SELECT d.*, a.full_name, a.company_name, a.type as applicant_type
    FROM domains d
    LEFT JOIN applicants a ON d.applicant_id = a.id
    WHERE d.registrar_id = ?
    ORDER BY d.created_at DESC LIMIT 5", [$user['registrar_id']]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Dashboard - BOCRA</title>
    <link href="https://fonts.googleapis.com/css2?family=Forum&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/portal-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="top-bar">
                <div>
                    <h1>Registrar Dashboard</h1>
                    <p>Welcome back, <?php echo htmlspecialchars($user['name']); ?></p>
                </div>
            </div>
            
            <div class="content-area">
                <!-- KPI Cards -->
                <div class="kpi-grid">
                    <div class="kpi-card">
                        <div class="kpi-icon"><i class="fas fa-users"></i></div>
                        <div class="kpi-content">
                            <h3><?php echo $stats['total_applicants']; ?></h3>
                            <p>Total Applicants</p>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon"><i class="fas fa-globe"></i></div>
                        <div class="kpi-content">
                            <h3><?php echo $stats['total_domains']; ?></h3>
                            <p>Registered Domains</p>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon"><i class="fas fa-clock"></i></div>
                        <div class="kpi-content">
                            <h3><?php echo $stats['pending_submissions']; ?></h3>
                            <p>Pending Submissions</p>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="kpi-content">
                            <h3><?php echo $stats['active_domains']; ?></h3>
                            <p>Active Domains</p>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <a href="new-applicant.php" class="btn btn-primary">
                                <i class="fas fa-user-plus"></i> New Applicant
                            </a>
                            <a href="register-domain.php" class="btn btn-success">
                                <i class="fas fa-plus-circle"></i> Register Domain
                            </a>
                            <a href="domain-list.php" class="btn btn-secondary">
                                <i class="fas fa-list"></i> View Domain List
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Submissions -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Submissions</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Domain Name</th>
                                        <th>Applicant</th>
                                        <th>Status</th>
                                        <th>Registration Date</th>
                                        <th>Expiry Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent as $domain): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($domain['domain_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($domain['applicant_type'] === 'company' ? $domain['company_name'] : $domain['full_name']); ?></td>
                                            <td><?php echo getStatusBadge($domain['status']); ?></td>
                                            <td><?php echo formatDate($domain['registration_date']); ?></td>
                                            <td><?php echo formatDate($domain['expiry_date']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
