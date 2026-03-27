<?php
/**
 * BOCRA Domain Registration System - Production Configuration
 * This file should be used in production environments
 */

// Database configuration - UPDATE THESE FOR PRODUCTION
define('DB_HOST', 'localhost');
define('DB_NAME', 'bocra_system');
define('DB_USER', 'bocra_user');
define('DB_PASS', 'CHANGE_THIS_PASSWORD');

// Application settings - UPDATE BASE_URL FOR PRODUCTION
define('BASE_URL', 'https://bocra.org.bw'); // Change to your production domain
define('SESSION_LIFETIME', 7200); // 2 hours for production

// Timezone
date_default_timezone_set('Africa/Gaborone');

// Error reporting - DISABLED IN PRODUCTION
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/bocra_errors.log');

// Security settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);

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
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false // Enable SSL in production
                ]
            );
        } catch (PDOException $e) {
            // Log error instead of displaying in production
            error_log("Database connection failed: " . $e->getMessage());
            die("Service temporarily unavailable. Please try again later.");
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
 * JSON response with CORS headers
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    echo json_encode($data);
    exit;
}

/**
 * Handle OPTIONS requests for CORS
 */
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    exit;
}
