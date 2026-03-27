<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <h3>BOCRA Registrar</h3>
            <p><?php echo htmlspecialchars($user['registrar_name'] ?? 'Registrar Portal'); ?></p>
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
            <div class="nav-section-title">Applicants & Domains</div>
            <a href="new-applicant.php" class="nav-item <?php echo $currentPage === 'new-applicant.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-plus"></i>
                <span>New Applicant</span>
            </a>
            <a href="register-domain.php" class="nav-item <?php echo $currentPage === 'register-domain.php' ? 'active' : ''; ?>">
                <i class="fas fa-plus-circle"></i>
                <span>Register Domain</span>
            </a>
            <a href="domain-list.php" class="nav-item <?php echo $currentPage === 'domain-list.php' ? 'active' : ''; ?>">
                <i class="fas fa-list"></i>
                <span>Domain List</span>
            </a>
            <a href="submission-history.php" class="nav-item <?php echo $currentPage === 'submission-history.php' ? 'active' : ''; ?>">
                <i class="fas fa-history"></i>
                <span>Submission History</span>
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
