<?php
session_start();
require_once __DIR__ . '/../backend/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'licensing_admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle mark as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'mark_read' && isset($_POST['notification_id'])) {
        $sql = "UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?";
        dbQuery($sql, [$_POST['notification_id'], $user_id]);
    } elseif ($_POST['action'] === 'mark_all_read') {
        $sql = "UPDATE notifications SET is_read = TRUE WHERE user_id = ?";
        dbQuery($sql, [$user_id]);
    }
    header('Location: notifications.php');
    exit;
}

// Get all notifications
$sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50";
$notifications = dbQuery($sql, [$user_id]);

// Count unread
$sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = FALSE";
$result = dbQuery($sql, [$user_id]);
$unread_count = $result[0]['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Licensing Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Forum&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/portal-styles.css">
    <style>
        .notifications-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .mark-all-btn {
            padding: 0.5rem 1rem;
            background: white;
            color: #006B5E;
            border: 1px solid #006B5E;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
        }
        .mark-all-btn:hover {
            background: #f8f9fa;
        }
        .notifications-list {
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        .notification-item {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            gap: 1rem;
            align-items: flex-start;
            transition: background 0.2s;
        }
        .notification-item:last-child {
            border-bottom: none;
        }
        .notification-item:hover {
            background: #f8f9fa;
        }
        .notification-item.unread {
            background: #E8F4F2;
        }
        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1.125rem;
        }
        .notification-icon.new_application {
            background: #E8F4F2;
            color: #006B5E;
        }
        .notification-icon.assignment {
            background: #FDF6E3;
            color: #C9A227;
        }
        .notification-icon.status_change {
            background: #E3F2FD;
            color: #1976D2;
        }
        .notification-content {
            flex: 1;
        }
        .notification-title {
            font-weight: 700;
            color: #2C2C2C;
            margin-bottom: 0.25rem;
        }
        .notification-message {
            font-size: 0.875rem;
            color: #555;
            margin-bottom: 0.5rem;
        }
        .notification-time {
            font-size: 0.75rem;
            color: #888;
        }
        .notification-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        .mark-read-btn {
            padding: 0.375rem 0.75rem;
            background: #006B5E;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
        }
        .mark-read-btn:hover {
            background: #004D43;
        }
        .view-link {
            padding: 0.375rem 0.75rem;
            background: white;
            color: #006B5E;
            border: 1px solid #006B5E;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        .view-link:hover {
            background: #f8f9fa;
        }
        .empty-notifications {
            text-align: center;
            padding: 3rem;
            color: #888;
        }
        .empty-notifications i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }
        .unread-badge {
            background: #006B5E;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content-header">
            <div>
                <h1>Notifications</h1>
                <p>Stay updated with important alerts</p>
            </div>
        </div>

        <div class="notifications-header">
            <div>
                <?php if ($unread_count > 0): ?>
                    <span class="unread-badge"><?php echo $unread_count; ?> Unread</span>
                <?php else: ?>
                    <span style="color: #888; font-size: 0.875rem;">All caught up!</span>
                <?php endif; ?>
            </div>
            <?php if ($unread_count > 0): ?>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="mark_all_read">
                    <button type="submit" class="mark-all-btn">
                        <i class="fas fa-check-double"></i> Mark All as Read
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <div class="notifications-list">
            <?php if (empty($notifications)): ?>
                <div class="empty-notifications">
                    <i class="fas fa-bell-slash"></i>
                    <h3>No Notifications</h3>
                    <p>You don't have any notifications yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $notif): 
                    $time_ago = '';
                    $diff = time() - strtotime($notif['created_at']);
                    if ($diff < 60) {
                        $time_ago = 'Just now';
                    } elseif ($diff < 3600) {
                        $time_ago = floor($diff / 60) . ' minutes ago';
                    } elseif ($diff < 86400) {
                        $time_ago = floor($diff / 3600) . ' hours ago';
                    } else {
                        $time_ago = floor($diff / 86400) . ' days ago';
                    }
                ?>
                    <div class="notification-item <?php echo !$notif['is_read'] ? 'unread' : ''; ?>">
                        <div class="notification-icon <?php echo $notif['type']; ?>">
                            <?php
                            $icon = 'fa-bell';
                            if ($notif['type'] === 'new_application') $icon = 'fa-file-alt';
                            elseif ($notif['type'] === 'assignment') $icon = 'fa-user-check';
                            elseif ($notif['type'] === 'status_change') $icon = 'fa-exchange-alt';
                            ?>
                            <i class="fas <?php echo $icon; ?>"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title"><?php echo htmlspecialchars($notif['title']); ?></div>
                            <div class="notification-message"><?php echo htmlspecialchars($notif['message']); ?></div>
                            <div class="notification-time">
                                <i class="fas fa-clock"></i> <?php echo $time_ago; ?>
                            </div>
                        </div>
                        <div class="notification-actions">
                            <?php if (!empty($notif['link'])): ?>
                                <a href="<?php echo htmlspecialchars($notif['link']); ?>" class="view-link">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            <?php endif; ?>
                            <?php if (!$notif['is_read']): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="mark_read">
                                    <input type="hidden" name="notification_id" value="<?php echo $notif['id']; ?>">
                                    <button type="submit" class="mark-read-btn">
                                        <i class="fas fa-check"></i> Mark Read
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
