<?php
session_start();
require_once __DIR__ . '/../backend/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'cybersecurity_admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$request_id = $_GET['id'] ?? null;

if (!$request_id) {
    header('Location: requests.php');
    exit;
}

$request_result = dbQuery("SELECT * FROM cybersecurity_requests WHERE id = ?", [$request_id]);
if (empty($request_result)) {
    header('Location: requests.php');
    exit;
}
$request = $request_result[0];

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $notes = $_POST['notes'] ?? '';
    
    dbQuery("UPDATE cybersecurity_requests SET status = ?, updated_at = NOW() WHERE id = ?", [$new_status, $request_id]);
    
    if (!empty($notes)) {
        dbQuery("INSERT INTO request_updates (request_id, update_type, message, created_at) VALUES (?, 'status_change', ?, NOW())", [$request_id, $notes]);
    }
    
    // Create notification for requester
    dbQuery("INSERT INTO notifications (user_id, type, title, message, link, created_at) 
            SELECT id, 'request_update', 'Request Status Updated', 
            CONCAT('Your request ', ?, ' has been updated to: ', ?), '/dashboard.html'
            FROM users WHERE email = ? LIMIT 1", [$request['request_number'], ucfirst(str_replace('_', ' ', $new_status)), $request['contact_email']]);
    
    header('Location: view-request.php?id=' . $request_id . '&updated=1');
    exit;
}

// Handle assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_to_me'])) {
    dbQuery("UPDATE cybersecurity_requests SET assigned_to = ?, updated_at = NOW() WHERE id = ?", [$user_id, $request_id]);
    header('Location: view-request.php?id=' . $request_id . '&assigned=1');
    exit;
}

// Get request updates
$updates = dbQuery("SELECT * FROM request_updates WHERE request_id = ? ORDER BY created_at DESC", [$request_id]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Request - <?php echo htmlspecialchars($request['request_number']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Forum&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/portal-styles.css">
    <style>
        .request-header { background: white; padding: 2rem; border-radius: 8px; margin-bottom: 2rem; border-left: 4px solid #006B5E; }
        .request-header.high { border-left-color: #D4415E; }
        .request-header.medium { border-left-color: #F57C00; }
        .request-number { font-size: 1.5rem; font-weight: 700; color: #006B5E; margin-bottom: 0.5rem; }
        .status-badge { display: inline-block; padding: 0.5rem 1rem; border-radius: 12px; font-size: 0.875rem; font-weight: 600; text-transform: uppercase; margin-left: 1rem; }
        .status-pending { background: #FDF6E3; color: #C9A227; }
        .status-in_progress { background: #E3F2FD; color: #1976D2; }
        .status-completed { background: #E8F5E9; color: #4CAF50; }
        .urgency-badge { display: inline-block; padding: 0.5rem 1rem; border-radius: 4px; font-size: 0.875rem; font-weight: 600; margin-left: 0.5rem; }
        .urgency-high { background: #FFEBEE; color: #D4415E; }
        .urgency-medium { background: #FFF3E0; color: #F57C00; }
        .urgency-low { background: #E8F5E9; color: #4CAF50; }
        .details-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-bottom: 2rem; }
        .detail-card { background: white; padding: 1.5rem; border-radius: 8px; }
        .detail-label { font-size: 0.75rem; color: #888; text-transform: uppercase; margin-bottom: 0.5rem; }
        .detail-value { font-size: 1rem; color: #2C2C2C; font-weight: 600; }
        .description-card { background: white; padding: 2rem; border-radius: 8px; margin-bottom: 2rem; }
        .section-title { font-size: 1.25rem; font-weight: 700; color: #2C2C2C; margin-bottom: 1rem; }
        .action-form { background: white; padding: 2rem; border-radius: 8px; margin-bottom: 2rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 0.5rem; color: #555; }
        .form-group select, .form-group textarea { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 0.875rem; }
        .btn-primary { padding: 0.75rem 1.5rem; background: #006B5E; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 0.875rem; }
        .btn-primary:hover { background: #004D43; }
        .btn-secondary { padding: 0.75rem 1.5rem; background: #888; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 0.875rem; text-decoration: none; display: inline-block; }
        .btn-secondary:hover { background: #666; }
        .alert-success { background: #E8F5E9; color: #4CAF50; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; border-left: 4px solid #4CAF50; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content-header">
            <h1>View & Manage Request</h1>
            <p>Review request details and take action</p>
        </div>

        <?php if (isset($_GET['updated'])): ?>
            <div class="alert-success"><i class="fas fa-check-circle"></i> Request status updated successfully!</div>
        <?php endif; ?>

        <?php if (isset($_GET['assigned'])): ?>
            <div class="alert-success"><i class="fas fa-check-circle"></i> Request assigned to you successfully!</div>
        <?php endif; ?>

        <div class="request-header <?php echo $request['urgency']; ?>">
            <div class="request-number">
                <?php echo htmlspecialchars($request['request_number']); ?>
                <span class="status-badge status-<?php echo $request['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $request['status'])); ?></span>
                <span class="urgency-badge urgency-<?php echo $request['urgency']; ?>"><?php echo strtoupper($request['urgency']); ?> URGENCY</span>
            </div>
            <div style="color: #888; font-size: 0.875rem;">Submitted: <?php echo date('d M Y, H:i', strtotime($request['submitted_at'])); ?></div>
        </div>

        <div class="details-grid">
            <div class="detail-card">
                <div class="detail-label">Contact Person</div>
                <div class="detail-value"><?php echo htmlspecialchars($request['contact_person']); ?></div>
            </div>
            <div class="detail-card">
                <div class="detail-label">Email</div>
                <div class="detail-value"><?php echo htmlspecialchars($request['contact_email']); ?></div>
            </div>
            <div class="detail-card">
                <div class="detail-label">Phone</div>
                <div class="detail-value"><?php echo htmlspecialchars($request['contact_phone']); ?></div>
            </div>
            <div class="detail-card">
                <div class="detail-label">Organization</div>
                <div class="detail-value"><?php echo htmlspecialchars($request['organization_name'] ?? 'N/A'); ?></div>
            </div>
            <div class="detail-card">
                <div class="detail-label">Organization Size</div>
                <div class="detail-value"><?php echo ucfirst($request['organization_size']); ?></div>
            </div>
            <div class="detail-card">
                <div class="detail-label">Sector</div>
                <div class="detail-value"><?php echo ucfirst($request['sector']); ?></div>
            </div>
        </div>

        <div class="description-card">
            <div class="section-title">Service Type</div>
            <p><?php echo htmlspecialchars($request['service_type']); ?></p>
            
            <div class="section-title" style="margin-top: 1.5rem;">Description</div>
            <p><?php echo nl2br(htmlspecialchars($request['description'])); ?></p>
            
            <?php if (!empty($request['specific_requirements'])): ?>
                <div class="section-title" style="margin-top: 1.5rem;">Specific Requirements</div>
                <p><?php echo nl2br(htmlspecialchars($request['specific_requirements'])); ?></p>
            <?php endif; ?>
        </div>

        <?php if (!$request['assigned_to'] || $request['assigned_to'] != $user_id): ?>
            <div class="action-form">
                <div class="section-title">Assignment</div>
                <p style="color: #888; margin-bottom: 1rem;">
                    <?php if (!$request['assigned_to']): ?>
                        This request is currently unassigned.
                    <?php else: ?>
                        This request is assigned to another admin.
                    <?php endif; ?>
                </p>
                <form method="POST">
                    <button type="submit" name="assign_to_me" class="btn-primary"><i class="fas fa-user-check"></i> Assign to Me</button>
                </form>
            </div>
        <?php endif; ?>

        <div class="action-form">
            <div class="section-title">Update Status</div>
            <form method="POST">
                <div class="form-group">
                    <label>New Status</label>
                    <select name="status" required>
                        <option value="pending" <?php echo $request['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="in_progress" <?php echo $request['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="completed" <?php echo $request['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Update Notes</label>
                    <textarea name="notes" rows="4" placeholder="Enter update notes..."></textarea>
                </div>
                <button type="submit" name="update_status" class="btn-primary"><i class="fas fa-save"></i> Update Status</button>
                <a href="requests.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Back to All Requests</a>
            </form>
        </div>
    </div>
</body>
</html>
