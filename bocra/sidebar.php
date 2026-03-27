<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <h3>BOCRA Oversight</h3>
            <p>Regulatory Monitoring</p>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-title">Main</div>
            <a href="dashboard.php" class="nav-item <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Oversight</div>
            <a href="incoming-registrations.php" class="nav-item <?php echo $currentPage === 'incoming-registrations.php' ? 'active' : ''; ?>">
                <i class="fas fa-inbox"></i>
                <span>Incoming Registrations</span>
            </a>
            <a href="domain-monitoring.php" class="nav-item <?php echo $currentPage === 'domain-monitoring.php' ? 'active' : ''; ?>">
                <i class="fas fa-search"></i>
                <span>Domain Monitoring</span>
            </a>
            <a href="registrar-oversight.php" class="nav-item <?php echo $currentPage === 'registrar-oversight.php' ? 'active' : ''; ?>">
                <i class="fas fa-building"></i>
                <span>Registrar Oversight</span>
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Compliance</div>
            <a href="compliance-alerts.php" class="nav-item <?php echo $currentPage === 'compliance-alerts.php' ? 'active' : ''; ?>">
                <i class="fas fa-flag"></i>
                <span>Compliance Alerts</span>
            </a>
            <a href="audit-logs.php" class="nav-item <?php echo $currentPage === 'audit-logs.php' ? 'active' : ''; ?>">
                <i class="fas fa-clipboard-list"></i>
                <span>Audit Logs</span>
            </a>
        </div>
    </nav>
    
    <div class="sidebar-footer">
        <div class="user-profile">
            <div class="user-avatar">
                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
            </div>
            <div class="user-info">
                <h4><?php echo htmlspecialchars($user['name']); ?></h4>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
        </div>
        <a href="<?php echo BASE_URL; ?>/logout.php" class="btn btn-outline btn-sm" style="width: 100%; margin-top: 1rem;">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</aside>
