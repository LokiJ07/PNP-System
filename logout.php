<?php
// =====================================================
// FILE: logout.php
// PURPOSE: Log out user and clear session
// =====================================================

session_start();

// Log the logout activity if user was logged in
if (isset($_SESSION['user_id'])) {
    require_once 'config/db_connect.php';
    logActivity($_SESSION['user_id'], 'LOGOUT', 'users', $_SESSION['user_id'], 'User logged out', $conn);
    $conn->close();
}

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: index.php');
exit();
?>