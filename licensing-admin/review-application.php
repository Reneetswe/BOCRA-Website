<?php
/**
 * BOCRA Licensing Admin - Review Application
 * Detailed view and review interface for license applications
 */

session_start();
require_once __DIR__ . '/../backend/config.php';

// Check authentication and role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'licensing_admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

// Get application ID
$app_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($app_id === 0) {
    header('Location: applications.php');
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $notes = sanitize($_POST['notes'] ?? '');
    
    $new_status = '';
    $log_message = '';
    
    switch ($action) {
        case 'start_review':
            $new_status = 'under_review';
            $log_message = 'Started reviewing application';
            
            // Assign to current user if not assigned
            $sql = "UPDATE license_applications SET status = ?, assigned_to = ?, review_started_at = NOW() WHERE id = ?";
            dbQuery($sql, [$new_status, $user_id, $app_id]);
            break;
            
        case 'request_documents':
            $new_status = 'pending_documents';
            $log_message = 'Requested additional documents';
            
            $sql = "UPDATE license_applications SET status = ?, reviewer_notes = ? WHERE id = ?";
            dbQuery($sql, [$new_status, $notes, $app_id]);
            break;
            
        case 'approve':
            $new_status = 'approved';
            $log_message = 'Application approved';
            
            $sql = "UPDATE license_applications SET status = ?, reviewed_at = NOW(), approved_at = NOW() WHERE id = ?";
            dbQuery($sql, [$new_status, $app_id]);
            break;
            
        case 'reject':
            $new_status = 'rejected';
            $log_message = 'Application rejected';
            $rejection_reason = sanitize($_POST['rejection_reason'] ?? '');
            
            $sql = "UPDATE license_applications SET status = ?, reviewed_at = NOW(), rejection_reason = ? WHERE id = ?";
            dbQuery($sql, [$new_status, $rejection_reason, $app_id]);
            break;
    }
    
    // Log status change
    if ($new_status) {
        $sql = "INSERT INTO license_status_history (application_id, old_status, new_status, changed_by, notes) 
                SELECT id, status, ?, ?, ? FROM license_applications WHERE id = ?";
        dbQuery($sql, [$new_status, $user_id, $notes, $app_id]);
        
        // Create notification for applicant
        $sql = "SELECT applicant_email, application_number FROM license_applications WHERE id = ?";
        $result = dbQuery($sql, [$app_id]);
        if (!empty($result)) {
            $app_data = $result[0];
            $notification_message = "Your license application {$app_data['application_number']} status has been updated to: " . str_replace('_', ' ', $new_status);
            
            $sql = "INSERT INTO notifications (recipient_email, type, title, message, link) VALUES (?, 'license_update', 'Application Status Update', ?, ?)";
            dbQuery($sql, [$app_data['applicant_email'], $notification_message, '/check-application.php?number=' . $app_data['application_number']]);
        }
        
        // Log audit
        logAudit($user_name, 'licensing_admin', 'application_' . $action, 'license_application', $app_id, $log_message);
        
        $_SESSION['success_message'] = 'Application status updated successfully';
        header('Location: review-application.php?id=' . $app_id);
        exit;
    }
}

// Fetch application details
$sql = "SELECT * FROM license_applications WHERE id = ?";
$result = dbQuery($sql, [$app_id]);

if (empty($result)) {
    header('Location: applications.php');
    exit;
}

$application = $result[0];

// Fetch status history
$sql = "SELECT h.*, u.name as changed_by_name 
        FROM license_status_history h 
        LEFT JOIN users u ON h.changed_by = u.id 
        WHERE h.application_id = ? 
        ORDER BY h.created_at DESC";
$status_history = dbQuery($sql, [$app_id]);

// Get assigned user info
$assigned_user = null;
if ($application['assigned_to']) {
    $sql = "SELECT name, email FROM users WHERE id = ?";
    $result = dbQuery($sql, [$application['assigned_to']]);
    $assigned_user = $result[0] ?? null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Application - Licensing Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Forum&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/portal-styles.css">
    <style>
        .application-header {
            background: linear-gradient(135deg, var(--teal) 0%, var(--teal-dark) 100%);
            color: #fff;
            padding: 2rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        
        .app-number {
            font-size: 2rem;
            font-family: 'Forum', serif;
            margin-bottom: 0.5rem;
        }
        
        .app-meta {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        
        .detail-section {
            background: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }
        
        .section-title {
            font-size: 1.25rem;
            color: var(--charcoal);
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--border);
        }
        
        .detail-row {
            display: grid;
            grid-template-columns: 200px 1fr;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border);
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--mid);
        }
        
        .detail-value {
            color: var(--charcoal);
        }
        
        .action-panel {
            position: sticky;
            top: 2rem;
        }
        
        .action-card {
            background: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }
        
        .action-btn-full {
            width: 100%;
            padding: 0.75rem;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-approve {
            background: #10b981;
            color: #fff;
        }
        
        .btn-approve:hover {
            background: #059669;
        }
        
        .btn-reject {
            background: #ef4444;
            color: #fff;
        }
        
        .btn-reject:hover {
            background: #dc2626;
        }
        
        .btn-request-docs {
            background: #f59e0b;
            color: #fff;
        }
        
        .btn-request-docs:hover {
            background: #d97706;
        }
        
        .btn-start-review {
            background: var(--teal);
            color: #fff;
        }
        
        .btn-start-review:hover {
            background: var(--teal-dark);
        }
        
        .timeline {
            position: relative;
            padding-left: 2rem;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 0.5rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--border);
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .timeline-dot {
            position: absolute;
            left: -1.65rem;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--teal);
            border: 3px solid #fff;
            box-shadow: 0 0 0 2px var(--border);
        }
        
        .timeline-content {
            background: var(--bg);
            padding: 1rem;
            border-radius: 6px;
        }
        
        .timeline-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .timeline-status {
            font-weight: 600;
            color: var(--charcoal);
        }
        
        .timeline-date {
            font-size: 0.875rem;
            color: var(--mid);
        }
        
        .timeline-user {
            font-size: 0.875rem;
            color: var(--mid);
            margin-bottom: 0.5rem;
        }
        
        .timeline-notes {
            font-size: 0.9375rem;
            color: var(--charcoal);
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: #fff;
            border-radius: 8px;
            padding: 2rem;
            max-width: 500px;
            width: 90%;
        }
        
        .modal-header {
            margin-bottom: 1.5rem;
        }
        
        .modal-header h3 {
            font-size: 1.5rem;
            color: var(--charcoal);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--charcoal);
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-family: 'Lato', sans-serif;
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .modal-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        </div>
        <?php endif; ?>
        
        <div class="application-header">
            <div class="app-number"><?php echo htmlspecialchars($application['application_number']); ?></div>
            <div>
                <span class="status-badge status-<?php echo $application['status']; ?>" style="font-size: 1rem; padding: 0.5rem 1rem;">
                    <?php echo str_replace('_', ' ', ucwords($application['status'])); ?>
                </span>
            </div>
            <div class="app-meta">
                <div class="meta-item">
                    <i class="fas fa-calendar"></i>
                    <span>Submitted: <?php echo date('M d, Y H:i', strtotime($application['submission_date'])); ?></span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-certificate"></i>
                    <span><?php echo str_replace('_', ' ', ucwords($application['license_type'])); ?></span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-flag"></i>
                    <span class="priority-<?php echo $application['priority']; ?>">
                        <?php echo ucfirst($application['priority']); ?> Priority
                    </span>
                </div>
            </div>
        </div>

        <div class="content-grid">
            <div>
                <!-- Applicant Information -->
                <div class="detail-section">
                    <h3 class="section-title"><i class="fas fa-user"></i> Applicant Information</h3>
                    <div class="detail-row">
                        <div class="detail-label">Full Name:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($application['applicant_name']); ?></div>
                    </div>
                    <?php if ($application['company_name']): ?>
                    <div class="detail-row">
                        <div class="detail-label">Company Name:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($application['company_name']); ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="detail-row">
                        <div class="detail-label">Email:</div>
                        <div class="detail-value">
                            <a href="mailto:<?php echo htmlspecialchars($application['applicant_email']); ?>">
                                <?php echo htmlspecialchars($application['applicant_email']); ?>
                            </a>
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Phone:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($application['applicant_phone']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Business Type:</div>
                        <div class="detail-value"><?php echo ucwords($application['business_type']); ?></div>
                    </div>
                    <?php if ($application['registration_number']): ?>
                    <div class="detail-row">
                        <div class="detail-label">Registration Number:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($application['registration_number']); ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if ($application['tax_number']): ?>
                    <div class="detail-row">
                        <div class="detail-label">Tax Number:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($application['tax_number']); ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="detail-row">
                        <div class="detail-label">Physical Address:</div>
                        <div class="detail-value"><?php echo nl2br(htmlspecialchars($application['physical_address'])); ?></div>
                    </div>
                </div>

                <!-- Business Details -->
                <div class="detail-section">
                    <h3 class="section-title"><i class="fas fa-briefcase"></i> Business Details</h3>
                    <div class="detail-row">
                        <div class="detail-label">Business Description:</div>
                        <div class="detail-value"><?php echo nl2br(htmlspecialchars($application['business_description'])); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Proposed Services:</div>
                        <div class="detail-value"><?php echo nl2br(htmlspecialchars($application['proposed_services'])); ?></div>
                    </div>
                    <?php if ($application['technical_capacity']): ?>
                    <div class="detail-row">
                        <div class="detail-label">Technical Capacity:</div>
                        <div class="detail-value"><?php echo nl2br(htmlspecialchars($application['technical_capacity'])); ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if ($application['financial_capacity']): ?>
                    <div class="detail-row">
                        <div class="detail-label">Financial Capacity:</div>
                        <div class="detail-value"><?php echo nl2br(htmlspecialchars($application['financial_capacity'])); ?></div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Status History -->
                <div class="detail-section">
                    <h3 class="section-title"><i class="fas fa-history"></i> Status History</h3>
                    <div class="timeline">
                        <?php foreach ($status_history as $history): ?>
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <div class="timeline-status">
                                        <?php echo str_replace('_', ' ', ucwords($history['new_status'])); ?>
                                    </div>
                                    <div class="timeline-date">
                                        <?php echo date('M d, Y H:i', strtotime($history['created_at'])); ?>
                                    </div>
                                </div>
                                <div class="timeline-user">
                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($history['changed_by_name']); ?>
                                </div>
                                <?php if ($history['notes']): ?>
                                <div class="timeline-notes"><?php echo htmlspecialchars($history['notes']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Action Panel -->
            <div class="action-panel">
                <div class="action-card">
                    <h3 class="section-title"><i class="fas fa-tasks"></i> Actions</h3>
                    
                    <?php if ($application['status'] === 'submitted'): ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="start_review">
                        <button type="submit" class="action-btn-full btn-start-review">
                            <i class="fas fa-play"></i> Start Review
                        </button>
                    </form>
                    <?php endif; ?>
                    
                    <?php if (in_array($application['status'], ['under_review', 'pending_documents'])): ?>
                    <button onclick="showModal('approveModal')" class="action-btn-full btn-approve">
                        <i class="fas fa-check"></i> Approve Application
                    </button>
                    
                    <button onclick="showModal('rejectModal')" class="action-btn-full btn-reject">
                        <i class="fas fa-times"></i> Reject Application
                    </button>
                    
                    <button onclick="showModal('requestDocsModal')" class="action-btn-full btn-request-docs">
                        <i class="fas fa-file-upload"></i> Request Documents
                    </button>
                    <?php endif; ?>
                    
                    <a href="applications.php" class="action-btn-full" style="background: var(--border); color: var(--charcoal); text-decoration: none;">
                        <i class="fas fa-arrow-left"></i> Back to Applications
                    </a>
                </div>

                <!-- Assignment Info -->
                <div class="action-card">
                    <h3 class="section-title"><i class="fas fa-user-check"></i> Assignment</h3>
                    <?php if ($assigned_user): ?>
                    <div style="padding: 1rem; background: var(--teal-light); border-radius: 6px;">
                        <div style="font-weight: 600; margin-bottom: 0.5rem;">Assigned To:</div>
                        <div><?php echo htmlspecialchars($assigned_user['name']); ?></div>
                        <div style="font-size: 0.875rem; color: var(--mid);">
                            <?php echo htmlspecialchars($assigned_user['email']); ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <div style="padding: 1rem; background: var(--bg); border-radius: 6px; text-align: center; color: var(--mid);">
                        Not yet assigned
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Processing Time -->
                <div class="action-card">
                    <h3 class="section-title"><i class="fas fa-clock"></i> Processing Time</h3>
                    <div style="text-align: center;">
                        <div style="font-size: 2rem; font-weight: 700; color: var(--teal); font-family: 'Forum', serif;">
                            <?php 
                            $days = floor((time() - strtotime($application['submission_date'])) / 86400);
                            echo $days;
                            ?>
                        </div>
                        <div style="color: var(--mid); font-size: 0.875rem;">days since submission</div>
                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border); font-size: 0.875rem; color: var(--mid);">
                            Target: 14 days
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div id="approveModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-check-circle" style="color: #10b981;"></i> Approve Application</h3>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="approve">
                <div class="form-group">
                    <label class="form-label">Notes (Optional):</label>
                    <textarea name="notes" class="form-control" placeholder="Add any notes about the approval..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" onclick="hideModal('approveModal')" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve Application</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-times-circle" style="color: #ef4444;"></i> Reject Application</h3>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="reject">
                <div class="form-group">
                    <label class="form-label">Rejection Reason (Required):</label>
                    <textarea name="rejection_reason" class="form-control" placeholder="Provide detailed reason for rejection..." required></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" onclick="hideModal('rejectModal')" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Application</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Request Documents Modal -->
    <div id="requestDocsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-file-upload" style="color: #f59e0b;"></i> Request Additional Documents</h3>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="request_documents">
                <div class="form-group">
                    <label class="form-label">Document Requirements:</label>
                    <textarea name="notes" class="form-control" placeholder="Specify which documents are required..." required></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" onclick="hideModal('requestDocsModal')" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-warning">Request Documents</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showModal(modalId) {
            document.getElementById(modalId).classList.add('active');
        }
        
        function hideModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }
        
        // Close modal on outside click
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>
