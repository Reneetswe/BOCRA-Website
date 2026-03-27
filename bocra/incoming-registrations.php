<?php
require_once __DIR__ . '/../backend/config.php';
requireBOCRA();
$user = getCurrentUser();

// Get all domain applications
$applications = dbQuery("SELECT da.*, d.domain_name, d.status as domain_status, d.category,
    a.full_name, a.company_name, a.type as applicant_type,
    r.name as registrar_name, r.accreditation_number
    FROM domain_applications da
    LEFT JOIN domains d ON da.domain_id = d.id
    LEFT JOIN applicants a ON da.applicant_id = a.id
    LEFT JOIN registrars r ON da.registrar_id = r.id
    ORDER BY da.submitted_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incoming Registrations - BOCRA</title>
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
                    <h1>Incoming Registrations</h1>
                    <p>Domain submissions from accredited registrars</p>
                </div>
            </div>
            
            <div class="content-area">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">All Submissions (<?php echo count($applications); ?>)</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Registrar</th>
                                        <th>Domain Name</th>
                                        <th>Applicant</th>
                                        <th>Category</th>
                                        <th>Submission Status</th>
                                        <th>Domain Status</th>
                                        <th>Submitted Date</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($applications as $app): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($app['registrar_name']); ?></strong><br>
                                                <small style="color: var(--light);"><?php echo htmlspecialchars($app['accreditation_number']); ?></small>
                                            </td>
                                            <td><strong><?php echo htmlspecialchars($app['domain_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($app['applicant_type'] === 'company' ? $app['company_name'] : $app['full_name']); ?></td>
                                            <td><?php echo ucfirst($app['category']); ?></td>
                                            <td><?php echo getStatusBadge($app['submission_status']); ?></td>
                                            <td><?php echo getStatusBadge($app['domain_status']); ?></td>
                                            <td><?php echo formatDate($app['submitted_at'], 'Y-m-d H:i'); ?></td>
                                            <td style="max-width: 200px; font-size: 0.8125rem;">
                                                <?php echo htmlspecialchars(substr($app['notes'] ?? 'No notes', 0, 50)); ?>
                                            </td>
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
