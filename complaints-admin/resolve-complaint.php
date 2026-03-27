<?php
session_start();
require_once __DIR__ . '/../backend/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'complaints_admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

// Get complaint ID
$complaint_id = $_GET['id'] ?? null;

if (!$complaint_id) {
    header('Location: complaints.php');
    exit;
}

// Get complaint details
$sql = "SELECT * FROM complaints WHERE id = ?";
$complaint_result = dbQuery($sql, [$complaint_id]);

if (empty($complaint_result)) {
    header('Location: complaints.php');
    exit;
}

$complaint = $complaint_result[0];

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $notes = $_POST['notes'] ?? '';
    
    $sql = "UPDATE complaints SET status = ?, updated_at = NOW() WHERE id = ?";
    dbQuery($sql, [$new_status, $complaint_id]);
    
    // Add update record
    if (!empty($notes)) {
        $sql = "INSERT INTO complaint_updates (complaint_id, update_type, message, is_visible_to_user, created_at) 
                VALUES (?, 'status_change', ?, TRUE, NOW())";
        dbQuery($sql, [$complaint_id, $notes]);
    }
    
    // Create notification for complainant
    $sql = "INSERT INTO notifications (user_id, type, title, message, link, created_at) 
            SELECT id, 'complaint_update', 'Complaint Status Updated', 
            CONCAT('Your complaint ', ?, ' has been updated to: ', ?),
            '/dashboard.html'
            FROM users WHERE email = ? LIMIT 1";
    dbQuery($sql, [$complaint['complaint_number'], ucfirst(str_replace('_', ' ', $new_status)), $complaint['complainant_email']]);
    
    header('Location: resolve-complaint.php?id=' . $complaint_id . '&updated=1');
    exit;
}

// Handle assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_to_me'])) {
    $sql = "UPDATE complaints SET assigned_to = ?, updated_at = NOW() WHERE id = ?";
    dbQuery($sql, [$user_id, $complaint_id]);
    
    header('Location: resolve-complaint.php?id=' . $complaint_id . '&assigned=1');
    exit;
}

// Get complaint updates
$sql = "SELECT * FROM complaint_updates WHERE complaint_id = ? ORDER BY created_at DESC";
$updates = dbQuery($sql, [$complaint_id]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Complaint - <?php echo htmlspecialchars($complaint['complaint_number']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Forum&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/portal-styles.css">
    <style>
        .complaint-header {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border-left: 4px solid #006B5E;
        }
        .complaint-header.high { border-left-color: #D4415E; }
        .complaint-header.medium { border-left-color: #F57C00; }
        .complaint-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #006B5E;
            margin-bottom: 0.5rem;
        }
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-left: 1rem;
        }
        .status-submitted { background: #FDF6E3; color: #C9A227; }
        .status-under_investigation { background: #E3F2FD; color: #1976D2; }
        .status-resolved { background: #E8F5E9; color: #4CAF50; }
        .status-closed { background: #F5F5F5; color: #888; }
        .priority-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }
        .priority-high { background: #FFEBEE; color: #D4415E; }
        .priority-medium { background: #FFF3E0; color: #F57C00; }
        .priority-low { background: #E8F5E9; color: #4CAF50; }
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .detail-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
        }
        .detail-label {
            font-size: 0.75rem;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }
        .detail-value {
            font-size: 1rem;
            color: #2C2C2C;
            font-weight: 600;
        }
        .description-card {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2C2C2C;
            margin-bottom: 1rem;
        }
        .action-form {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #555;
        }
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.875rem;
        }
        .btn-primary {
            padding: 0.75rem 1.5rem;
            background: #006B5E;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.875rem;
        }
        .btn-primary:hover {
            background: #004D43;
        }
        .btn-secondary {
            padding: 0.75rem 1.5rem;
            background: #888;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.875rem;
            text-decoration: none;
            display: inline-block;
        }
        .btn-secondary:hover {
            background: #666;
        }
        .updates-timeline {
            background: white;
            padding: 2rem;
            border-radius: 8px;
        }
        .timeline-item {
            border-left: 2px solid #ddd;
            padding-left: 1.5rem;
            padding-bottom: 1.5rem;
            position: relative;
        }
        .timeline-item:before {
            content: '';
            width: 12px;
            height: 12px;
            background: #006B5E;
            border-radius: 50%;
            position: absolute;
            left: -7px;
            top: 0;
        }
        .timeline-date {
            font-size: 0.75rem;
            color: #888;
            margin-bottom: 0.5rem;
        }
        .timeline-message {
            color: #555;
        }
        .alert-success {
            background: #E8F5E9;
            color: #4CAF50;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            border-left: 4px solid #4CAF50;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content-header">
            <h1>View & Resolve Complaint</h1>
            <p>Review complaint details and take action</p>
        </div>

        <?php if (isset($_GET['updated'])): ?>
            <div class="alert-success">
                <i class="fas fa-check-circle"></i> Complaint status updated successfully!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['assigned'])): ?>
            <div class="alert-success">
                <i class="fas fa-check-circle"></i> Complaint assigned to you successfully!
            </div>
        <?php endif; ?>

        <div class="complaint-header <?php echo $complaint['priority']; ?>">
            <div class="complaint-number">
                <?php echo htmlspecialchars($complaint['complaint_number']); ?>
                <span class="status-badge status-<?php echo $complaint['status']; ?>">
                    <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                </span>
                <span class="priority-badge priority-<?php echo $complaint['priority']; ?>">
                    <?php echo strtoupper($complaint['priority']); ?> PRIORITY
                </span>
            </div>
            <div style="color: #888; font-size: 0.875rem;">
                Submitted: <?php echo date('d M Y, H:i', strtotime($complaint['submitted_at'])); ?>
            </div>
        </div>

        <div class="details-grid">
            <div class="detail-card">
                <div class="detail-label">Complainant Name</div>
                <div class="detail-value"><?php echo htmlspecialchars($complaint['complainant_name']); ?></div>
            </div>
            <div class="detail-card">
                <div class="detail-label">Email</div>
                <div class="detail-value"><?php echo htmlspecialchars($complaint['complainant_email']); ?></div>
            </div>
            <div class="detail-card">
                <div class="detail-label">Phone</div>
                <div class="detail-value"><?php echo htmlspecialchars($complaint['complainant_phone']); ?></div>
            </div>
            <div class="detail-card">
                <div class="detail-label">Complaint Type</div>
                <div class="detail-value"><?php echo ucfirst(str_replace('_', ' ', $complaint['complaint_type'])); ?></div>
            </div>
            <div class="detail-card">
                <div class="detail-label">Service Provider</div>
                <div class="detail-value"><?php echo htmlspecialchars($complaint['service_provider'] ?? 'N/A'); ?></div>
            </div>
            <div class="detail-card">
                <div class="detail-label">Sector</div>
                <div class="detail-value"><?php echo ucfirst($complaint['sector']); ?></div>
            </div>
        </div>

        <div class="description-card">
            <div class="section-title">Subject</div>
            <p><?php echo htmlspecialchars($complaint['subject']); ?></p>
            
            <div class="section-title" style="margin-top: 1.5rem;">Description</div>
            <p><?php echo nl2br(htmlspecialchars($complaint['description'])); ?></p>
            
            <?php if (!empty($complaint['desired_outcome'])): ?>
                <div class="section-title" style="margin-top: 1.5rem;">Desired Outcome</div>
                <p><?php echo nl2br(htmlspecialchars($complaint['desired_outcome'])); ?></p>
            <?php endif; ?>
        </div>

        <?php if (!$complaint['assigned_to'] || $complaint['assigned_to'] != $user_id): ?>
            <div class="action-form">
                <div class="section-title">Assignment</div>
                <p style="color: #888; margin-bottom: 1rem;">
                    <?php if (!$complaint['assigned_to']): ?>
                        This complaint is currently unassigned.
                    <?php else: ?>
                        This complaint is assigned to another admin.
                    <?php endif; ?>
                </p>
                <form method="POST">
                    <button type="submit" name="assign_to_me" class="btn-primary">
                        <i class="fas fa-user-check"></i> Assign to Me
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <div class="action-form">
            <div class="section-title">Update Status</div>
            <form method="POST">
                <div class="form-group">
                    <label>New Status</label>
                    <select name="status" required>
                        <option value="submitted" <?php echo $complaint['status'] === 'submitted' ? 'selected' : ''; ?>>Submitted</option>
                        <option value="under_investigation" <?php echo $complaint['status'] === 'under_investigation' ? 'selected' : ''; ?>>Under Investigation</option>
                        <option value="resolved" <?php echo $complaint['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                        <option value="closed" <?php echo $complaint['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Update Notes (visible to complainant)</label>
                    <textarea name="notes" rows="4" placeholder="Enter update notes..."></textarea>
                </div>
                <button type="submit" name="update_status" class="btn-primary">
                    <i class="fas fa-save"></i> Update Status
                </button>
                <a href="complaints.php" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to All Complaints
                </a>
            </form>
        </div>

        <?php if (count($updates) > 0): ?>
            <div class="updates-timeline">
                <div class="section-title">Update History</div>
                <?php foreach ($updates as $update): ?>
                    <div class="timeline-item">
                        <div class="timeline-date">
                            <?php echo date('d M Y, H:i', strtotime($update['created_at'])); ?>
                        </div>
                        <div class="timeline-message">
                            <?php echo nl2br(htmlspecialchars($update['message'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
