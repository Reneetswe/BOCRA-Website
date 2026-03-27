<?php
require_once __DIR__ . '/../backend/config.php';
requireBOCRA();
$user = getCurrentUser();

$flags = dbQuery("SELECT cf.*, d.domain_name, a.full_name, a.company_name, a.type as applicant_type
    FROM compliance_flags cf
    LEFT JOIN domains d ON cf.domain_id = d.id
    LEFT JOIN applicants a ON cf.applicant_id = a.id
    ORDER BY 
        CASE cf.severity 
            WHEN 'critical' THEN 1
            WHEN 'high' THEN 2
            WHEN 'medium' THEN 3
            WHEN 'low' THEN 4
        END,
        cf.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compliance Alerts - BOCRA</title>
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
                    <h1>Compliance Alerts</h1>
                    <p>Domain and applicant compliance monitoring</p>
                </div>
            </div>
            
            <div class="content-area">
                <div class="alert alert-info mb-3">
                    <strong><i class="fas fa-info-circle"></i> Compliance Monitoring:</strong>
                    Automated and manual flags for regulatory compliance issues
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Compliance Flags (<?php echo count($flags); ?>)</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Flag Type</th>
                                        <th>Severity</th>
                                        <th>Domain</th>
                                        <th>Applicant</th>
                                        <th>Status</th>
                                        <th>Note</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($flags as $flag): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo ucwords(str_replace('_', ' ', $flag['flag_type'])); ?></strong>
                                            </td>
                                            <td><?php echo getSeverityBadge($flag['severity']); ?></td>
                                            <td><?php echo $flag['domain_name'] ? htmlspecialchars($flag['domain_name']) : '<em>N/A</em>'; ?></td>
                                            <td>
                                                <?php 
                                                if ($flag['applicant_type']) {
                                                    echo htmlspecialchars($flag['applicant_type'] === 'company' ? $flag['company_name'] : $flag['full_name']);
                                                } else {
                                                    echo '<em>N/A</em>';
                                                }
                                                ?>
                                            </td>
                                            <td><?php echo getStatusBadge($flag['status']); ?></td>
                                            <td style="max-width: 250px; font-size: 0.8125rem;">
                                                <?php echo htmlspecialchars($flag['note']); ?>
                                            </td>
                                            <td><?php echo formatDate($flag['created_at'], 'Y-m-d H:i'); ?></td>
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
