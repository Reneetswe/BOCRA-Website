<?php
session_start();
require_once __DIR__ . '/../backend/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'complaints_admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

// Handle mark as read
if (isset($_POST['mark_read']) && isset($_POST['notification_id'])) {
    $sql = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
    dbQuery($sql, [$_POST['notification_id'], $user_id]);
    header('Location: notifications.php');
    exit;
}

// Handle mark all as read
if (isset($_POST['mark_all_read'])) {
    $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0";
    dbQuery($sql, [$user_id]);
    header('Location: notifications.php');
    exit;
}

// Get all notifications
$sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$notifications = dbQuery($sql, [$user_id]);

// Get unread count
$sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
$unread_count = dbQuery($sql, [$user_id])[0]['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Complaints Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Forum&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/portal-styles.css">
    <style>
        .notifications-header {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .unread-badge {
            background: #D4415E;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }
        .mark-all-btn {
            padding: 0.5rem 1rem;
            background: #006B5E;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .mark-all-btn:hover {
            background: #004D43;
        }
        .notifications-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .notification-item {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid #006B5E;
            display: flex;
            gap: 1rem;
            align-items: start;
        }
        .notification-item.unread {
            background: #F0F9F8;
            border-left-color: #D4415E;
        }
        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #E8F4F2;
            color: #006B5E;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .notification-item.unread .notification-icon {
            background: #FFEBEE;
            color: #D4415E;
        }
        .notification-content {
            flex: 1;
        }
        .notification-title {
            font-weight: 700;
            font-size: 1rem;
            color: #2C2C2C;
            margin-bottom: 0.25rem;
        }
        .notification-message {
            color: #555;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }
        .notification-time {
            color: #888;
            font-size: 0.75rem;
        }
        .notification-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        .action-link {
            padding: 0.5rem 1rem;
            background: #006B5E;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .action-link:hover {
            background: #004D43;
        }
        .mark-read-btn {
            padding: 0.5rem;
            background: transparent;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            color: #888;
        }
        .mark-read-btn:hover {
            background: #f5f5f5;
            color: #006B5E;
        }
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 8px;
        }
        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content-header">
            <h1>Notifications
                <?php if ($unread_count > 0): ?>
                    <span class="unread-badge"><?php echo $unread_count; ?> New</span>
                <?php endif; ?>
            </h1>
            <p>Stay updated on complaint activities and assignments</p>
        </div>

        <?php if (count($notifications) > 0): ?>
            <div class="notifications-header">
                <div>
                    <strong><?php echo count($notifications); ?></strong> total notifications
                </div>
                <?php if ($unread_count > 0): ?>
                    <form method="POST" style="margin: 0;">
                        <button type="submit" name="mark_all_read" class="mark-all-btn">
                            <i class="fas fa-check-double"></i> Mark All as Read
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="notifications-list">
                <?php foreach ($notifications as $notif): ?>
                    <div class="notification-item <?php echo $notif['is_read'] ? '' : 'unread'; ?>">
                        <div class="notification-icon">
                            <?php if ($notif['type'] === 'complaint_update'): ?>
                                <i class="fas fa-bell"></i>
                            <?php elseif ($notif['type'] === 'assignment'): ?>
                                <i class="fas fa-user-check"></i>
                            <?php else: ?>
                                <i class="fas fa-info-circle"></i>
                            <?php endif; ?>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title"><?php echo htmlspecialchars($notif['title']); ?></div>
                            <div class="notification-message"><?php echo htmlspecialchars($notif['message']); ?></div>
                            <div class="notification-time">
                                <i class="fas fa-clock"></i> 
                                <?php 
                                $time_diff = time() - strtotime($notif['created_at']);
                                if ($time_diff < 3600) {
                                    echo round($time_diff / 60) . ' minutes ago';
                                } elseif ($time_diff < 86400) {
                                    echo round($time_diff / 3600) . ' hours ago';
                                } else {
                                    echo date('d M Y, H:i', strtotime($notif['created_at']));
                                }
                                ?>
                            </div>
                        </div>
                        <div class="notification-actions">
                            <?php if (!empty($notif['link'])): ?>
                                <a href="<?php echo htmlspecialchars($notif['link']); ?>" class="action-link">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            <?php endif; ?>
                            <?php if (!$notif['is_read']): ?>
                                <form method="POST" style="margin: 0;">
                                    <input type="hidden" name="notification_id" value="<?php echo $notif['id']; ?>">
                                    <button type="submit" name="mark_read" class="mark-read-btn" title="Mark as read">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-bell-slash"></i>
                <h3>No Notifications</h3>
                <p>You don't have any notifications yet. They will appear here when complaints are submitted or assigned.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
