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

// Set Philippine timezone
date_default_timezone_set('Asia/Manila');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8");

// Set MySQL timezone
$conn->query("SET time_zone = '+08:00'");

// Start session
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

// Function to log activities - FIXED parameter order
function logActivity($user_id, $action, $table_name, $record_id, $conn, $details = '') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, table_name, record_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ississ", $user_id, $action, $table_name, $record_id, $details, $ip);
    $stmt->execute();
    $stmt->close();
}

// Helper functions
function now() {
    return date('Y-m-d H:i:s');
}

function formatDate($date_string, $format = 'F d, Y h:i A') {
    if (empty($date_string)) return 'N/A';
    return date($format, strtotime($date_string));
}

function phDate($date_string = null) {
    if ($date_string === null) {
        return date('Y-m-d');
    }
    return date('Y-m-d', strtotime($date_string));
}

function phTime($time_string = null) {
    if ($time_string === null) {
        return date('H:i:s');
    }
    return date('H:i:s', strtotime($time_string));
}
// NO CLOSING TAG