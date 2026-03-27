<?php
session_start();
require_once __DIR__ . '/../backend/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'cybersecurity_admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';
$urgency_filter = $_GET['urgency'] ?? 'all';

// Build query
$where_clauses = [];
$params = [];

if ($status_filter !== 'all') {
    $where_clauses[] = "status = ?";
    $params[] = $status_filter;
}

if ($urgency_filter !== 'all') {
    $where_clauses[] = "urgency = ?";
    $params[] = $urgency_filter;
}

if (!empty($search)) {
    $where_clauses[] = "(request_number LIKE ? OR contact_person LIKE ? OR contact_email LIKE ? OR organization_name LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Get all requests
$sql = "SELECT * FROM cybersecurity_requests $where_sql ORDER BY submitted_at DESC";
$requests = dbQuery($sql, $params);

// Get statistics - using simple queries
$stats = [
    'total' => 0,
    'pending' => 0,
    'in_progress' => 0,
    'completed' => 0,
    'high_urgency' => 0
];

try {
    $total_result = dbQuery("SELECT COUNT(*) as total FROM cybersecurity_requests");
    $stats['total'] = $total_result[0]['total'] ?? 0;
    
    $pending_result = dbQuery("SELECT COUNT(*) as count FROM cybersecurity_requests WHERE status = 'pending'");
    $stats['pending'] = $pending_result[0]['count'] ?? 0;
    
    $progress_result = dbQuery("SELECT COUNT(*) as count FROM cybersecurity_requests WHERE status = 'in_progress'");
    $stats['in_progress'] = $progress_result[0]['count'] ?? 0;
    
    $completed_result = dbQuery("SELECT COUNT(*) as count FROM cybersecurity_requests WHERE status = 'completed'");
    $stats['completed'] = $completed_result[0]['count'] ?? 0;
    
    $high_result = dbQuery("SELECT COUNT(*) as count FROM cybersecurity_requests WHERE urgency = 'high'");
    $stats['high_urgency'] = $high_result[0]['count'] ?? 0;
} catch (Exception $e) {
    // Stats remain at 0
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Requests - Cybersecurity Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Forum&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/portal-styles.css">
    <style>
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #006B5E; }
        .stat-card.pending { border-left-color: #C9A227; }
        .stat-card.in-progress { border-left-color: #1976D2; }
        .stat-card.completed { border-left-color: #4CAF50; }
        .stat-card.high-urgency { border-left-color: #D4415E; }
        .stat-label { font-size: 0.875rem; color: #888; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem; }
        .stat-value { font-size: 2rem; font-weight: 700; color: #2C2C2C; }
        .filters-bar { background: white; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; }
        .filter-group { display: flex; flex-direction: column; gap: 0.5rem; }
        .filter-group label { font-size: 0.875rem; font-weight: 600; color: #555; }
        .filter-group select, .filter-group input { padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; font-size: 0.875rem; }
        .requests-table { background: white; border-radius: 8px; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f5f5f5; padding: 1rem; text-align: left; font-weight: 600; font-size: 0.875rem; color: #555; border-bottom: 2px solid #ddd; }
        td { padding: 1rem; border-bottom: 1px solid #f0f0f0; font-size: 0.875rem; }
        tr:hover { background: #f9f9f9; }
        .status-badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
        .status-pending { background: #FDF6E3; color: #C9A227; }
        .status-in_progress { background: #E3F2FD; color: #1976D2; }
        .status-completed { background: #E8F5E9; color: #4CAF50; }
        .urgency-badge { display: inline-block; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; }
        .urgency-high { background: #FFEBEE; color: #D4415E; }
        .urgency-medium { background: #FFF3E0; color: #F57C00; }
        .urgency-low { background: #E8F5E9; color: #4CAF50; }
        .action-btn { padding: 0.5rem 1rem; background: #006B5E; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.875rem; text-decoration: none; display: inline-block; }
        .action-btn:hover { background: #004D43; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content-header">
            <h1>All Requests</h1>
            <p>View and manage all cybersecurity service requests</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card"><div class="stat-label">Total Requests</div><div class="stat-value"><?php echo $stats['total']; ?></div></div>
            <div class="stat-card pending"><div class="stat-label">Pending</div><div class="stat-value"><?php echo $stats['pending']; ?></div></div>
            <div class="stat-card in-progress"><div class="stat-label">In Progress</div><div class="stat-value"><?php echo $stats['in_progress']; ?></div></div>
            <div class="stat-card completed"><div class="stat-label">Completed</div><div class="stat-value"><?php echo $stats['completed']; ?></div></div>
            <div class="stat-card high-urgency"><div class="stat-label">High Urgency</div><div class="stat-value"><?php echo $stats['high_urgency']; ?></div></div>
        </div>

        <form method="GET" class="filters-bar">
            <div class="filter-group">
                <label>Status</label>
                <select name="status" onchange="this.form.submit()">
                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="in_progress" <?php echo $status_filter === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Urgency</label>
                <select name="urgency" onchange="this.form.submit()">
                    <option value="all" <?php echo $urgency_filter === 'all' ? 'selected' : ''; ?>>All Urgency</option>
                    <option value="high" <?php echo $urgency_filter === 'high' ? 'selected' : ''; ?>>High</option>
                    <option value="medium" <?php echo $urgency_filter === 'medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="low" <?php echo $urgency_filter === 'low' ? 'selected' : ''; ?>>Low</option>
                </select>
            </div>
            <div class="filter-group" style="flex: 1;">
                <label>Search</label>
                <input type="text" name="search" placeholder="Search by number, name, email, organization..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="filter-group" style="align-self: flex-end;">
                <button type="submit" class="action-btn"><i class="fas fa-search"></i> Search</button>
            </div>
        </form>

        <div class="requests-table">
            <table>
                <thead>
                    <tr><th>Request #</th><th>Contact Person</th><th>Organization</th><th>Service Type</th><th>Urgency</th><th>Status</th><th>Submitted</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php if (count($requests) > 0): ?>
                        <?php foreach ($requests as $request): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($request['request_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($request['contact_person']); ?><br><small style="color: #888;"><?php echo htmlspecialchars($request['contact_email']); ?></small></td>
                                <td><?php echo htmlspecialchars($request['organization_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars(substr($request['service_type'], 0, 40)); ?></td>
                                <td><span class="urgency-badge urgency-<?php echo $request['urgency']; ?>"><?php echo strtoupper($request['urgency']); ?></span></td>
                                <td><span class="status-badge status-<?php echo $request['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $request['status'])); ?></span></td>
                                <td><?php echo date('d M Y', strtotime($request['submitted_at'])); ?></td>
                                <td><a href="view-request.php?id=<?php echo $request['id']; ?>" class="action-btn"><i class="fas fa-eye"></i> View</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" style="text-align: center; padding: 3rem; color: #888;"><i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i><p>No requests found.</p></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
