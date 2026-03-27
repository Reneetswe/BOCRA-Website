<?php
require_once __DIR__ . '/backend/config.php';

// Get all complaints
$sql = "SELECT * FROM complaints ORDER BY submitted_at DESC";
$complaints = dbQuery($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test - View All Complaints</title>
    <style>
        body { font-family: Arial; padding: 2rem; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 2rem; border-radius: 8px; }
        h1 { color: #006B5E; }
        .count { background: #E8F4F2; padding: 1rem; border-radius: 4px; margin: 1rem 0; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #006B5E; color: white; }
        tr:hover { background: #f9f9f9; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 All Complaints in Database</h1>
        <div class="count">Total Complaints: <strong><?php echo count($complaints); ?></strong></div>

        <?php if (count($complaints) > 0): ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Complaint #</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Type</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Submitted</th>
                </tr>
                <?php foreach ($complaints as $c): ?>
                    <tr>
                        <td><?php echo $c['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($c['complaint_number']); ?></strong></td>
                        <td><?php echo htmlspecialchars($c['complainant_name']); ?></td>
                        <td><?php echo htmlspecialchars($c['complainant_email']); ?></td>
                        <td><?php echo htmlspecialchars($c['complaint_type']); ?></td>
                        <td><?php echo htmlspecialchars(substr($c['subject'], 0, 40)); ?>...</td>
                        <td><?php echo htmlspecialchars($c['status']); ?></td>
                        <td><?php echo date('d M Y', strtotime($c['submitted_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p style="text-align: center; padding: 2rem; color: #888;">
                No complaints in database. Submit one from: 
                <a href="complaints.html">complaints.html</a>
            </p>
        <?php endif; ?>

        <div style="margin-top: 2rem; padding: 1rem; background: #FDF6E3; border-radius: 4px;">
            <h3>Next Steps:</h3>
            <ol>
                <li>If complaints exist above → They should appear in your admin dashboard</li>
                <li>If no complaints → Submit one from <a href="complaints.html">complaints.html</a></li>
                <li>Then check admin dashboard: <a href="complaints-admin/dashboard.php">complaints-admin/dashboard.php</a></li>
            </ol>
        </div>
    </div>
</body>
</html>
