<?php
session_start();
require_once __DIR__ . '/../backend/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'cybersecurity_admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['mark_read']) && isset($_POST['notification_id'])) {
    dbQuery("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?", [$_POST['notification_id'], $user_id]);
    header('Location: notifications.php');
    exit;
}

if (isset($_POST['mark_all_read'])) {
    dbQuery("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0", [$user_id]);
    header('Location: notifications.php');
    exit;
}

$notifications = dbQuery("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC", [$user_id]);
$unread_count = dbQuery("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0", [$user_id])[0]['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Cybersecurity Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Forum&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/portal-styles.css">
    <style>
        .unread-badge { background: #D4415E; color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.875rem; font-weight: 600; margin-left: 0.5rem; }
        .notifications-list { display: flex; flex-direction: column; gap: 1rem; }
        .notification-item { background: white; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #006B5E; display: flex; gap: 1rem; align-items: start; }
        .notification-item.unread { background: #F0F9F8; border-left-color: #D4415E; }
        .notification-icon { width: 40px; height: 40px; border-radius: 50%; background: #E8F4F2; color: #006B5E; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .notification-item.unread .notification-icon { background: #FFEBEE; color: #D4415E; }
        .notification-content { flex: 1; }
        .notification-title { font-weight: 700; font-size: 1rem; color: #2C2C2C; margin-bottom: 0.25rem; }
        .notification-message { color: #555; font-size: 0.875rem; margin-bottom: 0.5rem; }
        .notification-time { color: #888; font-size: 0.75rem; }
        .action-link { padding: 0.5rem 1rem; background: #006B5E; color: white; border-radius: 4px; text-decoration: none; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.5rem; }
        .action-link:hover { background: #004D43; }
        .mark-read-btn { padding: 0.5rem; background: transparent; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; color: #888; }
        .mark-read-btn:hover { background: #f5f5f5; color: #006B5E; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content-header">
            <h1>Notifications<?php if ($unread_count > 0): ?><span class="unread-badge"><?php echo $unread_count; ?> New</span><?php endif; ?></h1>
            <p>Stay updated on cybersecurity request activities</p>
        </div>

        <?php if (count($notifications) > 0): ?>
            <?php if ($unread_count > 0): ?>
                <div style="background: white; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                    <div><strong><?php echo count($notifications); ?></strong> total notifications</div>
                    <form method="POST" style="margin: 0;"><button type="submit" name="mark_all_read" class="btn btn-primary"><i class="fas fa-check-double"></i> Mark All as Read</button></form>
                </div>
            <?php endif; ?>

            <div class="notifications-list">
                <?php foreach ($notifications as $notif): ?>
                    <div class="notification-item <?php echo $notif['is_read'] ? '' : 'unread'; ?>">
                        <div class="notification-icon"><i class="fas fa-bell"></i></div>
                        <div class="notification-content">
                            <div class="notification-title"><?php echo htmlspecialchars($notif['title']); ?></div>
                            <div class="notification-message"><?php echo htmlspecialchars($notif['message']); ?></div>
                            <div class="notification-time"><i class="fas fa-clock"></i> <?php 
                                $time_diff = time() - strtotime($notif['created_at']);
                                echo $time_diff < 3600 ? round($time_diff / 60) . ' minutes ago' : ($time_diff < 86400 ? round($time_diff / 3600) . ' hours ago' : date('d M Y, H:i', strtotime($notif['created_at'])));
                            ?></div>
                        </div>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <?php if (!empty($notif['link'])): ?><a href="<?php echo htmlspecialchars($notif['link']); ?>" class="action-link"><i class="fas fa-eye"></i> View</a><?php endif; ?>
                            <?php if (!$notif['is_read']): ?><form method="POST" style="margin: 0;"><input type="hidden" name="notification_id" value="<?php echo $notif['id']; ?>"><button type="submit" name="mark_read" class="mark-read-btn" title="Mark as read"><i class="fas fa-check"></i></button></form><?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 4rem; background: white; border-radius: 8px;"><i class="fas fa-bell-slash" style="font-size: 4rem; color: #ddd; margin-bottom: 1rem;"></i><h3>No Notifications</h3><p style="color: #888;">You don't have any notifications yet.</p></div>
        <?php endif; ?>
    </div>
</body>
</html>
