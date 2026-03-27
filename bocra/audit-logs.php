<?php
require_once __DIR__ . '/../backend/config.php';
requireBOCRA();
$user = getCurrentUser();

$logs = dbQuery("SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 100");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs - BOCRA</title>
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
                    <h1>Audit Logs</h1>
                    <p>System activity trail</p>
                </div>
            </div>
            
            <div class="content-area">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Activity (Last 100 entries)</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Timestamp</th>
                                        <th>Actor</th>
                                        <th>Role</th>
                                        <th>Action</th>
                                        <th>Domain</th>
                                        <th>Applicant</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($logs as $log): ?>
                                        <tr>
                                            <td style="white-space: nowrap;"><?php echo formatDate($log['created_at'], 'Y-m-d H:i:s'); ?></td>
                                            <td><strong><?php echo htmlspecialchars($log['actor_name']); ?></strong></td>
                                            <td>
                                                <?php if ($log['actor_role'] === 'registrar'): ?>
                                                    <span class="badge badge-info">Registrar</span>
                                                <?php elseif ($log['actor_role'] === 'bocra'): ?>
                                                    <span class="badge badge-success">BOCRA</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">System</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo ucwords(str_replace('_', ' ', $log['action'])); ?></td>
                                            <td><?php echo $log['domain_name'] ? htmlspecialchars($log['domain_name']) : '<em>—</em>'; ?></td>
                                            <td><?php echo $log['applicant_name'] ? htmlspecialchars($log['applicant_name']) : '<em>—</em>'; ?></td>
                                            <td style="max-width: 300px; font-size: 0.8125rem;">
                                                <?php echo htmlspecialchars($log['details'] ?? ''); ?>
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
