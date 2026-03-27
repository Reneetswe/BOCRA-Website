<?php
/**
 * BOCRA Domain Registration System - Configuration
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'bocra_registry');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application settings
define('BASE_URL', 'http://localhost/BOCRA-Website');
define('SESSION_LIFETIME', 3600); // 1 hour

// Timezone
date_default_timezone_set('Africa/Gaborone');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Get database connection
 */
function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    return $pdo;
}

/**
 * Execute query and return results
 */
function dbQuery($sql, $params = []) {
    $pdo = getDB();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Execute query without returning results
 */
function dbExecute($sql, $params = []) {
    $pdo = getDB();
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

/**
 * Get last insert ID
 */
function dbLastInsertId() {
    return getDB()->lastInsertId();
}

/**
 * Sanitize input
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

/**
 * Get current user
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $sql = "SELECT u.*, r.name as registrar_name, r.accreditation_number 
            FROM users u 
            LEFT JOIN registrars r ON u.registrar_id = r.id 
            WHERE u.id = ?";
    $result = dbQuery($sql, [$_SESSION['user_id']]);
    
    return $result[0] ?? null;
}

/**
 * Require login
 */
function requireLogin($role = null) {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
    
    if ($role && $_SESSION['role'] !== $role) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

/**
 * Require registrar role
 */
function requireRegistrar() {
    requireLogin('registrar');
}

/**
 * Require BOCRA role
 */
function requireBOCRA() {
    requireLogin('bocra');
}

/**
 * Log audit trail
 */
function logAudit($actorName, $actorRole, $action, $entityType = null, $entityId = null, $details = null) {
    $sql = "INSERT INTO audit_logs (actor_name, actor_role, action, entity_type, entity_id, details, ip_address) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    dbExecute($sql, [
        $actorName,
        $actorRole,
        $action,
        $entityType,
        $entityId,
        $details,
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);
}

/**
 * Format date
 */
function formatDate($date, $format = 'Y-m-d') {
    if (!$date) return '';
    return date($format, strtotime($date));
}

/**
 * Get status badge HTML
 */
function getStatusBadge($status) {
    $badges = [
        'active' => '<span class="badge badge-success">Active</span>',
        'pending' => '<span class="badge badge-warning">Pending</span>',
        'suspended' => '<span class="badge badge-danger">Suspended</span>',
        'expired' => '<span class="badge badge-secondary">Expired</span>',
        'cancelled' => '<span class="badge badge-secondary">Cancelled</span>',
        'submitted' => '<span class="badge badge-info">Submitted</span>',
        'under_review' => '<span class="badge badge-warning">Under Review</span>',
        'approved' => '<span class="badge badge-success">Approved</span>',
        'rejected' => '<span class="badge badge-danger">Rejected</span>',
        'open' => '<span class="badge badge-warning">Open</span>',
        'investigating' => '<span class="badge badge-info">Investigating</span>',
        'resolved' => '<span class="badge badge-success">Resolved</span>',
        'dismissed' => '<span class="badge badge-secondary">Dismissed</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge badge-secondary">' . ucfirst($status) . '</span>';
}

/**
 * Get severity badge HTML
 */
function getSeverityBadge($severity) {
    $badges = [
        'low' => '<span class="badge badge-info">Low</span>',
        'medium' => '<span class="badge badge-warning">Medium</span>',
        'high' => '<span class="badge badge-danger">High</span>',
        'critical' => '<span class="badge badge-critical">Critical</span>'
    ];
    
    return $badges[$severity] ?? '<span class="badge badge-secondary">' . ucfirst($severity) . '</span>';
}

/**
 * JSON response
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
