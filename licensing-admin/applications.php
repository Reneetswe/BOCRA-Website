<?php
session_start();
require_once __DIR__ . '/../backend/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'licensing_admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';
$license_type_filter = $_GET['license_type'] ?? 'all';

// Build query
$where_clauses = [];
$params = [];

if ($status_filter !== 'all') {
    $where_clauses[] = "status = ?";
    $params[] = $status_filter;
}

if ($license_type_filter !== 'all') {
    $where_clauses[] = "license_type = ?";
    $params[] = $license_type_filter;
}

if (!empty($search)) {
    $where_clauses[] = "(application_number LIKE ? OR applicant_name LIKE ? OR applicant_email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Get all applications
$sql = "SELECT * FROM license_applications $where_sql ORDER BY submission_date DESC";
$applications = dbQuery($sql, $params);

// Get unique license types for filter
$sql = "SELECT DISTINCT license_type FROM license_applications ORDER BY license_type";
$license_types = dbQuery($sql);

// Get status counts
$sql = "SELECT status, COUNT(*) as count FROM license_applications GROUP BY status";
$status_counts = dbQuery($sql);
$counts = [];
foreach ($status_counts as $row) {
    $counts[$row['status']] = $row['count'];
}
$counts['all'] = array_sum($counts);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Applications - Licensing Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Forum&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/portal-styles.css">
    <style>
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
            gap: 0.5rem;
            align-items: center;
        }
        .filter-group label {
            font-weight: 600;
            font-size: 0.875rem;
            color: #555;
        }
        .filter-group select,
        .filter-group input {
            padding: 0.5rem 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.875rem;
        }
        .status-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        .status-tab {
            padding: 0.5rem 1rem;
            background: white;
            border: 1px solid #ddd;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.2s;
            text-decoration: none;
            color: #555;
        }
        .status-tab:hover {
            background: #f5f5f5;
        }
        .status-tab.active {
            background: #006B5E;
            color: white;
            border-color: #006B5E;
        }
        .status-tab .count {
            background: rgba(0,0,0,0.1);
            padding: 0.125rem 0.5rem;
            border-radius: 12px;
            margin-left: 0.5rem;
            font-size: 0.75rem;
        }
        .applications-table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        .applications-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .applications-table th {
            background: #f8f9fa;
            padding: 1rem;
            text-align: left;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #555;
            border-bottom: 2px solid #e9ecef;
        }
        .applications-table td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            font-size: 0.875rem;
        }
        .applications-table tr:hover {
            background: #f8f9fa;
        }
        .app-number {
            font-weight: 700;
            color: #006B5E;
            font-family: monospace;
        }
        .applicant-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .applicant-name {
            font-weight: 600;
            color: #2C2C2C;
        }
        .applicant-email {
            font-size: 0.75rem;
            color: #888;
        }
        .license-type-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: #E8F4F2;
            color: #006B5E;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .action-btn {
            padding: 0.375rem 0.75rem;
            background: #006B5E;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .action-btn:hover {
            background: #004D43;
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #888;
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content-header">
            <div>
                <h1>All Applications</h1>
                <p>View and manage all license applications</p>
            </div>
        </div>

        <div class="status-tabs">
            <a href="?status=all" class="status-tab <?php echo $status_filter === 'all' ? 'active' : ''; ?>">
                All <span class="count"><?php echo $counts['all'] ?? 0; ?></span>
            </a>
            <a href="?status=submitted" class="status-tab <?php echo $status_filter === 'submitted' ? 'active' : ''; ?>">
                Submitted <span class="count"><?php echo $counts['submitted'] ?? 0; ?></span>
            </a>
            <a href="?status=under_review" class="status-tab <?php echo $status_filter === 'under_review' ? 'active' : ''; ?>">
                Under Review <span class="count"><?php echo $counts['under_review'] ?? 0; ?></span>
            </a>
            <a href="?status=approved" class="status-tab <?php echo $status_filter === 'approved' ? 'active' : ''; ?>">
                Approved <span class="count"><?php echo $counts['approved'] ?? 0; ?></span>
            </a>
            <a href="?status=rejected" class="status-tab <?php echo $status_filter === 'rejected' ? 'active' : ''; ?>">
                Rejected <span class="count"><?php echo $counts['rejected'] ?? 0; ?></span>
            </a>
        </div>

        <div class="filters-bar">
            <form method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; width: 100%;">
                <div class="filter-group">
                    <label>Search:</label>
                    <input type="text" name="search" placeholder="App #, Name, Email..." value="<?php echo htmlspecialchars($search); ?>" style="min-width: 250px;">
                </div>
                <div class="filter-group">
                    <label>License Type:</label>
                    <select name="license_type">
                        <option value="all">All Types</option>
                        <?php foreach ($license_types as $type): ?>
                            <option value="<?php echo htmlspecialchars($type['license_type']); ?>" <?php echo $license_type_filter === $type['license_type'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type['license_type']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="action-btn">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
                <a href="applications.php" class="action-btn" style="background: #888;">
                    <i class="fas fa-times"></i> Clear
                </a>
            </form>
        </div>

        <div class="applications-table">
            <?php if (empty($applications)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No Applications Found</h3>
                    <p>No applications match your current filters.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Application #</th>
                            <th>Applicant</th>
                            <th>License Type</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td>
                                    <span class="app-number"><?php echo htmlspecialchars($app['application_number']); ?></span>
                                </td>
                                <td>
                                    <div class="applicant-info">
                                        <span class="applicant-name"><?php echo htmlspecialchars($app['applicant_name']); ?></span>
                                        <span class="applicant-email"><?php echo htmlspecialchars($app['applicant_email']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="license-type-badge"><?php echo htmlspecialchars($app['license_type']); ?></span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($app['submission_date'])); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $app['status']; ?>">
                                        <?php echo ucwords(str_replace('_', ' ', $app['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="view-application.php?id=<?php echo $app['id']; ?>" class="action-btn">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
