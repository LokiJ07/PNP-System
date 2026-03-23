<?php
// =====================================================
// FILE: logout.php
// PURPOSE: Log out user and clear session
// =====================================================

session_start();

// Log the logout activity if user was logged in AND user_id is valid (>0)
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0) {
    require_once 'config/db_connect.php';
    
    // Check if user exists before logging
    $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
    $check_stmt->bind_param("i", $_SESSION['user_id']);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        // User exists, proceed with logging
        logActivity($_SESSION['user_id'], 'LOGOUT', 'users', $_SESSION['user_id'], $conn, 'User logged out');
    }
    $check_stmt->close();
    $conn->close();
}

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: index.php');
exit();
?>