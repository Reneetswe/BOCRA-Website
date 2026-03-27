<?php
require_once __DIR__ . '/../backend/config.php';
requireRegistrar();
$user = getCurrentUser();

// Get all submissions for this registrar
$submissions = dbQuery("SELECT da.*, d.domain_name, d.status as domain_status,
    a.full_name, a.company_name, a.type as applicant_type
    FROM domain_applications da
    LEFT JOIN domains d ON da.domain_id = d.id
    LEFT JOIN applicants a ON da.applicant_id = a.id
    WHERE da.registrar_id = ?
    ORDER BY da.submitted_at DESC", [$user['registrar_id']]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission History - BOCRA Registrar</title>
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
                    <h1>Submission History</h1>
                    <p>All domain application submissions</p>
                </div>
            </div>
            
            <div class="content-area">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Submitted Applications (<?php echo count($submissions); ?>)</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($submissions)): ?>
                            <p style="text-align: center; padding: 3rem; color: var(--mid);">
                                <i class="fas fa-inbox" style="font-size: 3rem; display: block; margin-bottom: 1rem;"></i>
                                No submissions yet
                            </p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Domain</th>
                                            <th>Applicant</th>
                                            <th>Submitted Date</th>
                                            <th>Submission Status</th>
                                            <th>Domain Status</th>
                                            <th>Sync State</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($submissions as $submission): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($submission['domain_name']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($submission['applicant_type'] === 'company' ? $submission['company_name'] : $submission['full_name']); ?></td>
                                                <td><?php echo formatDate($submission['submitted_at'], 'Y-m-d H:i'); ?></td>
                                                <td><?php echo getStatusBadge($submission['submission_status']); ?></td>
                                                <td><?php echo getStatusBadge($submission['domain_status']); ?></td>
                                                <td>
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check"></i> Synced to BOCRA
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
