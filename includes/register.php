<?php
// =====================================================
// FILE: includes/register_process.php
// PURPOSE: Process registration form submission
// =====================================================

require_once '../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $rank = $_POST['rank'] ?? '';
    $firstname = $_POST['firstname'] ?? '';
    $lastname = $_POST['lastname'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate inputs
    $errors = [];
    
    if (empty($email)) $errors[] = 'Email is required';
    if (empty($rank)) $errors[] = 'Rank is required';
    if (empty($firstname)) $errors[] = 'First name is required';
    if (empty($lastname)) $errors[] = 'Last name is required';
    if (empty($password)) $errors[] = 'Password is required';
    if ($password !== $confirm_password) $errors[] = 'Passwords do not match';
    
    // Check if email already exists
    $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();
    
    if ($check->num_rows > 0) {
        $errors[] = 'Email already registered';
    }
    $check->close();
    
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header('Location: ../register.php');
        exit();
    }
    
    // Generate unique badge number
    $year = date('Y');
    $badge_number = 'PNP-' . $year . '-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    
    // In production, hash the password: password_hash($password, PASSWORD_DEFAULT)
    // For demo, we'll store plain text (NOT SECURE for production)
    $hashed_password = $password; // Change to password_hash in production
    
    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (badge_number, rank, first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?, ?, 'user')");
    $stmt->bind_param("ssssss", $badge_number, $rank, $firstname, $lastname, $email, $hashed_password);
    
    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;
        
        // Log registration
        logActivity($user_id, 'REGISTER', 'users', $user_id, 'New user registered', $conn);
        
        $_SESSION['success'] = 'Registration successful! Please login.';
        header('Location: ../index.php');
    } else {
        $_SESSION['error'] = 'Registration failed: ' . $conn->error;
        header('Location: ../register.php');
    }
    
    $stmt->close();
    $conn->close();
} else {
    header('Location: ../register.php');
    exit();
}
?>