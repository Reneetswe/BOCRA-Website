<?php
/**
 * BOCRA Domain Registration System - Logout
 */

session_start();

// Log logout action if user is logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['name']) && isset($_SESSION['role'])) {
    require_once __DIR__ . '/backend/config.php';
    logAudit($_SESSION['name'], $_SESSION['role'], 'user_logout', null, null, 'User logged out');
}

// Clear session
session_unset();
session_destroy();

// Redirect to login
header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login.php');
exit;
