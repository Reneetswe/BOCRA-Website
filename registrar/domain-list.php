<?php
require_once __DIR__ . '/../backend/config.php';
requireRegistrar();
$user = getCurrentUser();

// Get all domains for this registrar
$domains = dbQuery("SELECT d.*, a.full_name, a.company_name, a.type as applicant_type
    FROM domains d
    LEFT JOIN applicants a ON d.applicant_id = a.id
    WHERE d.registrar_id = ?
    ORDER BY d.created_at DESC", [$user['registrar_id']]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domain List - BOCRA Registrar</title>
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
                    <h1>Domain List</h1>
                    <p>All registered domains</p>
                </div>
                <a href="register-domain.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Register New Domain
                </a>
            </div>
            
            <div class="content-area">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Registered Domains (<?php echo count($domains); ?>)</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($domains)): ?>
                            <p style="text-align: center; padding: 3rem; color: var(--mid);">
                                <i class="fas fa-inbox" style="font-size: 3rem; display: block; margin-bottom: 1rem;"></i>
                                No domains registered yet
                            </p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Domain Name</th>
                                            <th>Applicant</th>
                                            <th>Category</th>
                                            <th>Status</th>
                                            <th>Registration Date</th>
                                            <th>Expiry Date</th>
                                            <th>Nameservers</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($domains as $domain): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($domain['domain_name']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($domain['applicant_type'] === 'company' ? $domain['company_name'] : $domain['full_name']); ?></td>
                                                <td><?php echo ucfirst($domain['category']); ?></td>
                                                <td><?php echo getStatusBadge($domain['status']); ?></td>
                                                <td><?php echo formatDate($domain['registration_date']); ?></td>
                                                <td><?php echo formatDate($domain['expiry_date']); ?></td>
                                                <td style="font-size: 0.75rem;">
                                                    <?php echo htmlspecialchars($domain['nameserver_1']); ?><br>
                                                    <?php echo htmlspecialchars($domain['nameserver_2']); ?>
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
