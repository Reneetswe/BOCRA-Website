<?php
/**
 * Licensing Admin Portal - Sidebar Navigation
 */
if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized');
}
?>
<div class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i class="fas fa-certificate"></i>
        </div>
        <div class="sidebar-title">
            <h2>Licensing Admin</h2>
            <p>BOCRA Portal</p>
        </div>
    </div>

    <div class="sidebar-user">
        <div class="user-avatar">
            <i class="fas fa-user-circle"></i>
        </div>
        <div class="user-info">
            <div class="user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></div>
            <div class="user-role">Licensing Administrator</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i>
            <span>Dashboard</span>
        </a>
        
        <a href="applications.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'applications.php' ? 'active' : ''; ?>">
            <i class="fas fa-file-alt"></i>
            <span>All Applications</span>
            <?php
            $sql = "SELECT COUNT(*) as count FROM license_applications WHERE status IN ('submitted', 'pending_documents')";
            $result = dbQuery($sql);
            $pending = $result[0]['count'];
            if ($pending > 0):
            ?>
            <span class="badge"><?php echo $pending; ?></span>
            <?php endif; ?>
        </a>
        
        <a href="applications.php?filter=assigned_to_me" class="nav-link">
            <i class="fas fa-tasks"></i>
            <span>My Assignments</span>
        </a>
        
        <a href="review-queue.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'review-queue.php' ? 'active' : ''; ?>">
            <i class="fas fa-clipboard-check"></i>
            <span>Review Queue</span>
        </a>
        
        <a href="analytics.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'analytics.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar"></i>
            <span>Analytics & Reports</span>
        </a>
        
        <a href="notifications.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'active' : ''; ?>">
            <i class="fas fa-bell"></i>
            <span>Notifications</span>
            <?php
            $sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = FALSE";
            $result = dbQuery($sql, [$_SESSION['user_id']]);
            $unread = $result[0]['count'];
            if ($unread > 0):
            ?>
            <span class="badge"><?php echo $unread; ?></span>
            <?php endif; ?>
        </a>
        
        <div class="nav-divider"></div>
        
        <a href="settings.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </a>
        
        <a href="<?php echo BASE_URL; ?>/logout.php" class="nav-link">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="quick-stats">
            <div class="quick-stat">
                <div class="stat-value"><?php echo $pending ?? 0; ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="quick-stat">
                <div class="stat-value">
                    <?php
                    $sql = "SELECT COUNT(*) as count FROM license_applications WHERE assigned_to = ? AND status = 'under_review'";
                    $result = dbQuery($sql, [$_SESSION['user_id']]);
                    echo $result[0]['count'];
                    ?>
                </div>
                <div class="stat-label">In Review</div>
            </div>
        </div>
    </div>
</div>
