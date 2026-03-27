<?php
session_start();
require_once __DIR__ . '/../backend/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'complaints_admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';
$priority_filter = $_GET['priority'] ?? 'all';

// Build query
$where_clauses = [];
$params = [];

if ($status_filter !== 'all') {
    $where_clauses[] = "status = ?";
    $params[] = $status_filter;
}

if ($priority_filter !== 'all') {
    $where_clauses[] = "priority = ?";
    $params[] = $priority_filter;
}

if (!empty($search)) {
    $where_clauses[] = "(complaint_number LIKE ? OR complainant_name LIKE ? OR complainant_email LIKE ? OR subject LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Get all complaints
$sql = "SELECT * FROM complaints $where_sql ORDER BY submitted_at DESC";
$complaints = dbQuery($sql, $params);

// Get statistics - using simple query to avoid SQL errors
$stats = [
    'total' => 0,
    'submitted' => 0,
    'investigating' => 0,
    'resolved' => 0,
    'closed' => 0,
    'high_priority' => 0
];

try {
    $total_result = dbQuery("SELECT COUNT(*) as total FROM complaints");
    $stats['total'] = $total_result[0]['total'] ?? 0;
    
    $submitted_result = dbQuery("SELECT COUNT(*) as count FROM complaints WHERE status = 'submitted'");
    $stats['submitted'] = $submitted_result[0]['count'] ?? 0;
    
    $investigating_result = dbQuery("SELECT COUNT(*) as count FROM complaints WHERE status = 'under_investigation'");
    $stats['investigating'] = $investigating_result[0]['count'] ?? 0;
    
    $resolved_result = dbQuery("SELECT COUNT(*) as count FROM complaints WHERE status = 'resolved'");
    $stats['resolved'] = $resolved_result[0]['count'] ?? 0;
    
    $closed_result = dbQuery("SELECT COUNT(*) as count FROM complaints WHERE status = 'closed'");
    $stats['closed'] = $closed_result[0]['count'] ?? 0;
    
    $high_result = dbQuery("SELECT COUNT(*) as count FROM complaints WHERE priority = 'high'");
    $stats['high_priority'] = $high_result[0]['count'] ?? 0;
} catch (Exception $e) {
    // If queries fail, stats remain at 0
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Complaints - Complaints Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Forum&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/portal-styles.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid #006B5E;
        }
        .stat-card.submitted { border-left-color: #C9A227; }
        .stat-card.investigating { border-left-color: #1976D2; }
        .stat-card.resolved { border-left-color: #4CAF50; }
        .stat-card.high-priority { border-left-color: #D4415E; }
        .stat-label {
            font-size: 0.875rem;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #2C2C2C;
        }
        .filters-bar {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .filter-group label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #555;
        }
        .filter-group select,
        .filter-group input {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.875rem;
        }
        .complaints-table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        .table-wrapper {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #f5f5f5;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.875rem;
            color: #555;
            border-bottom: 2px solid #ddd;
        }
        td {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.875rem;
        }
        tr:hover {
            background: #f9f9f9;
        }
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-submitted { background: #FDF6E3; color: #C9A227; }
        .status-under_investigation { background: #E3F2FD; color: #1976D2; }
        .status-resolved { background: #E8F5E9; color: #4CAF50; }
        .status-closed { background: #F5F5F5; color: #888; }
        .priority-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .priority-high { background: #FFEBEE; color: #D4415E; }
        .priority-medium { background: #FFF3E0; color: #F57C00; }
        .priority-low { background: #E8F5E9; color: #4CAF50; }
        .action-btn {
            padding: 0.5rem 1rem;
            background: #006B5E;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.875rem;
            text-decoration: none;
            display: inline-block;
        }
        .action-btn:hover {
            background: #004D43;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content-header">
            <h1>All Complaints</h1>
            <p>View and manage all consumer complaints</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Complaints</div>
                <div class="stat-value"><?php echo $stats['total']; ?></div>
            </div>
            <div class="stat-card submitted">
                <div class="stat-label">Submitted</div>
                <div class="stat-value"><?php echo $stats['submitted']; ?></div>
            </div>
            <div class="stat-card investigating">
                <div class="stat-label">Investigating</div>
                <div class="stat-value"><?php echo $stats['investigating']; ?></div>
            </div>
            <div class="stat-card resolved">
                <div class="stat-label">Resolved</div>
                <div class="stat-value"><?php echo $stats['resolved']; ?></div>
            </div>
            <div class="stat-card high-priority">
                <div class="stat-label">High Priority</div>
                <div class="stat-value"><?php echo $stats['high_priority']; ?></div>
            </div>
        </div>

        <form method="GET" class="filters-bar">
            <div class="filter-group">
                <label>Status</label>
                <select name="status" onchange="this.form.submit()">
                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                    <option value="submitted" <?php echo $status_filter === 'submitted' ? 'selected' : ''; ?>>Submitted</option>
                    <option value="under_investigation" <?php echo $status_filter === 'under_investigation' ? 'selected' : ''; ?>>Under Investigation</option>
                    <option value="resolved" <?php echo $status_filter === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                    <option value="closed" <?php echo $status_filter === 'closed' ? 'selected' : ''; ?>>Closed</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Priority</label>
                <select name="priority" onchange="this.form.submit()">
                    <option value="all" <?php echo $priority_filter === 'all' ? 'selected' : ''; ?>>All Priorities</option>
                    <option value="high" <?php echo $priority_filter === 'high' ? 'selected' : ''; ?>>High</option>
                    <option value="medium" <?php echo $priority_filter === 'medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="low" <?php echo $priority_filter === 'low' ? 'selected' : ''; ?>>Low</option>
                </select>
            </div>
            <div class="filter-group" style="flex: 1;">
                <label>Search</label>
                <input type="text" name="search" placeholder="Search by number, name, email, or subject..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="filter-group" style="align-self: flex-end;">
                <button type="submit" class="action-btn"><i class="fas fa-search"></i> Search</button>
            </div>
        </form>

        <div class="complaints-table">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Complaint #</th>
                            <th>Complainant</th>
                            <th>Type</th>
                            <th>Subject</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($complaints) > 0): ?>
                            <?php foreach ($complaints as $complaint): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($complaint['complaint_number']); ?></strong></td>
                                    <td>
                                        <?php echo htmlspecialchars($complaint['complainant_name']); ?><br>
                                        <small style="color: #888;"><?php echo htmlspecialchars($complaint['complainant_email']); ?></small>
                                    </td>
                                    <td><?php echo ucfirst(str_replace('_', ' ', $complaint['complaint_type'])); ?></td>
                                    <td><?php echo htmlspecialchars(substr($complaint['subject'], 0, 50)) . (strlen($complaint['subject']) > 50 ? '...' : ''); ?></td>
                                    <td>
                                        <span class="priority-badge priority-<?php echo $complaint['priority']; ?>">
                                            <?php echo strtoupper($complaint['priority']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $complaint['status']; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($complaint['submitted_at'])); ?></td>
                                    <td>
                                        <a href="resolve-complaint.php?id=<?php echo $complaint['id']; ?>" class="action-btn">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 3rem; color: #888;">
                                    <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                                    <p>No complaints found.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
