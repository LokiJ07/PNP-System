<?php
// =====================================================
// FILE: includes/login_process.php
// PURPOSE: Process user login with password verification
// FIXED: Added last_login update and session regeneration
// =====================================================
session_start();
require_once '../config/db_connect.php';

// Set Philippine timezone for accurate timestamps
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Please enter both email and password";
        header('Location: ../index.php');
        exit();
    }
    
    // Get user by email
    $stmt = $conn->prepare("SELECT user_id, email, password, first_name, last_name, rank, role, account_status FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Check if account is active
        if ($user['account_status'] !== 'active') {
            $_SESSION['error'] = "Your account is not active. Please contact administrator.";
            header('Location: ../index.php');
            exit();
        }
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Regenerate session ID for security
            session_regenerate_id(true);
            
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['rank'] = $user['rank'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            
            // =====================================================
            // FIX: Update last_login timestamp
            // =====================================================
            $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
            $update_stmt->bind_param("i", $user['user_id']);
            $update_stmt->execute();
            $update_stmt->close();
            
            // Log the login activity (optional)
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            // Handle proxy
            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
            
            $log_stmt = $conn->prepare("
                INSERT INTO activity_logs (user_id, action, table_name, record_id, details, ip_address) 
                VALUES (?, 'LOGIN', 'users', ?, 'User logged in', ?)
            ");
            if ($log_stmt) {
                $log_stmt->bind_param("iis", $user['user_id'], $user['user_id'], $ip);
                $log_stmt->execute();
                $log_stmt->close();
            }
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                header('Location: ../admin/admin_dashboard.php');
            } else {
                header('Location: ../user/user_dashboard.php');
            }
            exit();
        } else {
            error_log("Failed login attempt for email: $email - Invalid password");
            $_SESSION['error'] = "Invalid email or password";
        }
    } else {
        error_log("Failed login attempt for email: $email - User not found");
        $_SESSION['error'] = "Invalid email or password";
    }
    
    $stmt->close();
    header('Location: ../index.php');
    exit();
} else {
    header('Location: ../index.php');
    exit();
}
?>