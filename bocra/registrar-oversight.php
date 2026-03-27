<?php
require_once __DIR__ . '/../backend/config.php';
requireBOCRA();
$user = getCurrentUser();

$registrars = dbQuery("SELECT * FROM registrar_stats ORDER BY total_domains DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Oversight - BOCRA</title>
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
                    <h1>Registrar Oversight</h1>
                    <p>Accredited registrar performance monitoring</p>
                </div>
            </div>
            
            <div class="content-area">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Accredited Registrars (<?php echo count($registrars); ?>)</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Registrar Name</th>
                                        <th>Accreditation Number</th>
                                        <th>Total Submissions</th>
                                        <th>Active Domains</th>
                                        <th>Pending Domains</th>
                                        <th>Total Applicants</th>
                                        <th>Compliance Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($registrars as $reg): ?>
                                        <?php
                                        $complianceScore = 100;
                                        if ($reg['pending_domains'] > 5) $complianceScore -= 10;
                                        if ($reg['total_submissions'] > 0) {
                                            $activeRate = ($reg['active_domains'] / $reg['total_submissions']) * 100;
                                            if ($activeRate < 80) $complianceScore -= 15;
                                        }
                                        ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($reg['name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($reg['accreditation_number']); ?></td>
                                            <td><?php echo $reg['total_submissions']; ?></td>
                                            <td><span class="badge badge-success"><?php echo $reg['active_domains']; ?></span></td>
                                            <td><span class="badge badge-warning"><?php echo $reg['pending_domains']; ?></span></td>
                                            <td><?php echo $reg['total_applicants']; ?></td>
                                            <td>
                                                <?php if ($complianceScore >= 90): ?>
                                                    <span class="badge badge-success"><?php echo $complianceScore; ?>%</span>
                                                <?php elseif ($complianceScore >= 75): ?>
                                                    <span class="badge badge-warning"><?php echo $complianceScore; ?>%</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger"><?php echo $complianceScore; ?>%</span>
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
