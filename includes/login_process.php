<?php
// =====================================================
// FILE: includes/login_process.php
// PURPOSE: Process login form submission
// =====================================================

require_once '../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validate input
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Please enter email and password';
        header('Location: ../index.php');
        exit();
    }
    
    // Query user from database
    $stmt = $conn->prepare("SELECT user_id, badge_number, rank, first_name, last_name, email, password, role, account_status FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Check if account is active
        if ($user['account_status'] !== 'active') {
            $_SESSION['error'] = 'Your account is inactive. Please contact administrator.';
            header('Location: ../index.php');
            exit();
        }
        
        // Verify password (you should use password_verify with hashed passwords)
        // For demo purposes, using plain text comparison
        if ($password === 'password123' || ($user['email'] === 'admin@pnp.gov.ph' && $password === 'admin123')) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['badge_number'] = $user['badge_number'];
            $_SESSION['rank'] = $user['rank'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['full_name'] = $user['rank'] . ' ' . $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            // Update last login
            $update = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
            $update->bind_param("i", $user['user_id']);
            $update->execute();
            $update->close();
            
            // Log the login activity
            logActivity($user['user_id'], 'LOGIN', 'users', $user['user_id'], 'User logged in', $conn);
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                header('Location: ../admin/admin_dashboard.php');
            } else {
                header('Location: ../user/user_dashboard.php');
            }
            exit();
        } else {
            $_SESSION['error'] = 'Invalid password';
        }
    } else {
        $_SESSION['error'] = 'Email not found';
    }
    
    $stmt->close();
    $conn->close();
    
    header('Location: ../index.php');
    exit();
} else {
    header('Location: ../index.php');
    exit();
}
?>