<?php
// =====================================================
// FILE: config/db_connect.php
// PURPOSE: Database connection for all pages
// =====================================================

// Database configuration
define('DB_HOST', 'sql300.infinityfree.com');
define('DB_USER', 'if0_41350743');
define('DB_PASS', 'pnpofficertrack');
define('DB_NAME', 'if0_41350743_pnp_database');

// Set Philippine timezone (GLOBAL for the app)
date_default_timezone_set('Asia/Manila');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8");

// Start session for user login management
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../index.php');
        exit();
    }
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Function to require admin privileges
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ../user/user_dashboard.php');
        exit();
    }
}

// Function to log activities
function logActivity($user_id, $action, $table_name, $record_id, $details = '', $conn) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, table_name, record_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ississ", $user_id, $action, $table_name, $record_id, $details, $ip);
    $stmt->execute();
    $stmt->close();
}
?>