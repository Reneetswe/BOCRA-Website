<?php
require_once __DIR__ . '/../backend/config.php';
requireBOCRA();
$user = getCurrentUser();

$domains = dbQuery("SELECT d.*, a.full_name, a.company_name, a.type as applicant_type,
    r.name as registrar_name, r.accreditation_number,
    (SELECT COUNT(*) FROM compliance_flags WHERE domain_id = d.id AND status = 'open') as compliance_count
    FROM domains d
    LEFT JOIN applicants a ON d.applicant_id = a.id
    LEFT JOIN registrars r ON d.registrar_id = r.id
    ORDER BY d.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domain Monitoring - BOCRA</title>
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
                    <h1>Domain Monitoring</h1>
                    <p>All registered .bw domains</p>
                </div>
            </div>
            
            <div class="content-area">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">All Domains (<?php echo count($domains); ?>)</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Domain Name</th>
                                        <th>Registrar</th>
                                        <th>Applicant</th>
                                        <th>Status</th>
                                        <th>Category</th>
                                        <th>Expiry Date</th>
                                        <th>Compliance Flag</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($domains as $domain): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($domain['domain_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($domain['registrar_name']); ?></td>
                                            <td><?php echo htmlspecialchars($domain['applicant_type'] === 'company' ? $domain['company_name'] : $domain['full_name']); ?></td>
                                            <td><?php echo getStatusBadge($domain['status']); ?></td>
                                            <td><?php echo ucfirst($domain['category']); ?></td>
                                            <td><?php echo formatDate($domain['expiry_date']); ?></td>
                                            <td>
                                                <?php if ($domain['compliance_count'] > 0): ?>
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-flag"></i> <?php echo $domain['compliance_count']; ?> Flag(s)
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check"></i> Clear
                                                    </span>
                                                <?php endif; ?>
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
