<?php
session_start();
require_once __DIR__ . '/../backend/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'licensing_admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

// Handle assignment action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'assign_to_me') {
        $app_id = $_POST['app_id'];
        $sql = "UPDATE license_applications SET assigned_to = ?, status = 'under_review' WHERE id = ?";
        dbQuery($sql, [$user_id, $app_id]);
        
        // Create notification for assignment
        $sql = "INSERT INTO notifications (user_id, type, title, message, link) 
                VALUES (?, 'assignment', 'Application Assigned', 'You have been assigned a new application', ?)";
        $link = 'view-application.php?id=' . $app_id;
        dbQuery($sql, [$user_id, $link]);
        
        header('Location: review-queue.php?msg=assigned');
        exit;
    }
}

// Get applications needing review (submitted or pending documents)
$sql = "SELECT * FROM license_applications 
        WHERE status IN ('submitted', 'pending_documents') 
        ORDER BY submission_date ASC";
$pending_applications = dbQuery($sql);

// Get my assigned applications
$sql = "SELECT * FROM license_applications 
        WHERE assigned_to = ? AND status = 'under_review' 
        ORDER BY submission_date ASC";
$my_assigned = dbQuery($sql, [$user_id]);

// Get all licensing admins for assignment dropdown
$sql = "SELECT id, name, email FROM users WHERE role = 'licensing_admin' ORDER BY name";
$admins = dbQuery($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Queue - Licensing Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Forum&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/portal-styles.css">
    <style>
        .queue-section {
            margin-bottom: 2rem;
        }
        .queue-header {
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 8px 8px 0 0;
            border-bottom: 2px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .queue-header h2 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2C2C2C;
            margin: 0;
        }
        .queue-count {
            background: #006B5E;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 700;
        }
        .queue-list {
            background: white;
            border-radius: 0 0 8px 8px;
        }
        .queue-item {
            padding: 1.5rem;
            border-bottom: 1px solid #e9ecef;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 1.5rem;
            align-items: center;
        }
        .queue-item:last-child {
            border-bottom: none;
        }
        .queue-item:hover {
            background: #f8f9fa;
        }
        .item-main {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .item-title {
            font-weight: 700;
            color: #2C2C2C;
            font-size: 1rem;
        }
        .item-subtitle {
            font-size: 0.875rem;
            color: #888;
        }
        .item-meta {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            font-size: 0.875rem;
        }
        .meta-label {
            font-weight: 600;
            color: #555;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .meta-value {
            color: #2C2C2C;
        }
        .priority-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .priority-high {
            background: #FEE;
            color: #C00;
        }
        .priority-normal {
            background: #E8F4F2;
            color: #006B5E;
        }
        .assign-btn {
            padding: 0.5rem 1rem;
            background: #006B5E;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
        }
        .assign-btn:hover {
            background: #004D43;
        }
        .view-btn {
            padding: 0.5rem 1rem;
            background: white;
            color: #006B5E;
            border: 1px solid #006B5E;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .view-btn:hover {
            background: #f8f9fa;
        }
        .empty-queue {
            padding: 3rem;
            text-align: center;
            color: #888;
        }
        .empty-queue i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }
        .success-msg {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #c3e6cb;
        }
        .days-waiting {
            font-size: 0.75rem;
            color: #C00;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content-header">
            <div>
                <h1>Review Queue</h1>
                <p>Applications requiring review and action</p>
            </div>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'assigned'): ?>
            <div class="success-msg">
                <i class="fas fa-check-circle"></i> Application successfully assigned to you!
            </div>
        <?php endif; ?>

        <!-- My Assigned Applications -->
        <div class="queue-section">
            <div class="queue-header">
                <h2><i class="fas fa-user-check"></i> My Assigned Applications</h2>
                <span class="queue-count"><?php echo count($my_assigned); ?></span>
            </div>
            <div class="queue-list">
                <?php if (empty($my_assigned)): ?>
                    <div class="empty-queue">
                        <i class="fas fa-clipboard-check"></i>
                        <h3>No Assigned Applications</h3>
                        <p>You don't have any applications assigned to you at the moment.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($my_assigned as $app): 
                        $days_waiting = floor((time() - strtotime($app['submission_date'])) / 86400);
                    ?>
                        <div class="queue-item">
                            <div class="item-main">
                                <div class="item-title"><?php echo htmlspecialchars($app['applicant_name']); ?></div>
                                <div class="item-subtitle">
                                    <span style="font-family: monospace; font-weight: 600;"><?php echo htmlspecialchars($app['application_number']); ?></span>
                                </div>
                                <span class="license-type-badge"><?php echo htmlspecialchars($app['license_type']); ?></span>
                            </div>
                            <div class="item-meta">
                                <span class="meta-label">Submitted</span>
                                <span class="meta-value"><?php echo date('M d, Y', strtotime($app['submission_date'])); ?></span>
                                <?php if ($days_waiting > 7): ?>
                                    <span class="days-waiting"><?php echo $days_waiting; ?> days waiting</span>
                                <?php endif; ?>
                            </div>
                            <div class="item-meta">
                                <span class="meta-label">Status</span>
                                <span class="badge badge-under_review">Under Review</span>
                            </div>
                            <div>
                                <a href="view-application.php?id=<?php echo $app['id']; ?>" class="view-btn">
                                    <i class="fas fa-eye"></i> Review
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pending Applications (Unassigned) -->
        <div class="queue-section">
            <div class="queue-header">
                <h2><i class="fas fa-inbox"></i> Pending Applications</h2>
                <span class="queue-count"><?php echo count($pending_applications); ?></span>
            </div>
            <div class="queue-list">
                <?php if (empty($pending_applications)): ?>
                    <div class="empty-queue">
                        <i class="fas fa-check-double"></i>
                        <h3>All Caught Up!</h3>
                        <p>There are no pending applications requiring assignment.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($pending_applications as $app): 
                        $days_waiting = floor((time() - strtotime($app['submission_date'])) / 86400);
                        $is_urgent = $days_waiting > 7;
                    ?>
                        <div class="queue-item">
                            <div class="item-main">
                                <div class="item-title"><?php echo htmlspecialchars($app['applicant_name']); ?></div>
                                <div class="item-subtitle">
                                    <span style="font-family: monospace; font-weight: 600;"><?php echo htmlspecialchars($app['application_number']); ?></span>
                                </div>
                                <span class="license-type-badge"><?php echo htmlspecialchars($app['license_type']); ?></span>
                            </div>
                            <div class="item-meta">
                                <span class="meta-label">Submitted</span>
                                <span class="meta-value"><?php echo date('M d, Y', strtotime($app['submission_date'])); ?></span>
                                <?php if ($is_urgent): ?>
                                    <span class="days-waiting"><i class="fas fa-exclamation-triangle"></i> <?php echo $days_waiting; ?> days waiting</span>
                                <?php endif; ?>
                            </div>
                            <div class="item-meta">
                                <span class="meta-label">Priority</span>
                                <span class="priority-badge priority-<?php echo $is_urgent ? 'high' : 'normal'; ?>">
                                    <?php echo $is_urgent ? 'High Priority' : 'Normal'; ?>
                                </span>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="assign_to_me">
                                    <input type="hidden" name="app_id" value="<?php echo $app['id']; ?>">
                                    <button type="submit" class="assign-btn">
                                        <i class="fas fa-hand-paper"></i> Assign to Me
                                    </button>
                                </form>
                                <a href="view-application.php?id=<?php echo $app['id']; ?>" class="view-btn">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
