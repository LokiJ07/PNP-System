<?php
// =====================================================
// FILE: includes/register_process.php
// PURPOSE: Process user registration with password hashing
// =====================================================
session_start();
require_once '../config/db_connect.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../register.php');
    exit();
}

// Get form data
$email = trim($_POST['email'] ?? '');
$rank = $_POST['rank'] ?? '';
$firstname = trim($_POST['firstname'] ?? '');
$lastname = trim($_POST['lastname'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validate inputs
$errors = [];

// Email validation
if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address';
}

// Rank validation
if (empty($rank)) {
    $errors[] = 'Rank is required';
}

// Name validation
if (empty($firstname)) {
    $errors[] = 'First name is required';
}
if (empty($lastname)) {
    $errors[] = 'Last name is required';
}

// Password validation
if (empty($password)) {
    $errors[] = 'Password is required';
} elseif (strlen($password) < 6) {
    $errors[] = 'Password must be at least 6 characters long';
}

if ($password !== $confirm_password) {
    $errors[] = 'Passwords do not match';
}

// Check if email already exists
$check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$check_stmt->bind_param("s", $email);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows > 0) {
    $errors[] = 'Email address is already registered';
}
$check_stmt->close();

// If there are errors, redirect back with errors
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    // Preserve form data
    $_SESSION['old_email'] = $email;
    $_SESSION['old_firstname'] = $firstname;
    $_SESSION['old_lastname'] = $lastname;
    $_SESSION['old_rank'] = $rank;
    header('Location: ../register.php');
    exit();
}

// Generate unique badge number
$year = date('Y');
$badge_number = 'PNP-' . $year . '-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

// Make sure badge number is unique
$unique = false;
$attempts = 0;
while (!$unique && $attempts < 10) {
    $check_badge = $conn->prepare("SELECT user_id FROM users WHERE badge_number = ?");
    $check_badge->bind_param("s", $badge_number);
    $check_badge->execute();
    $check_badge->store_result();
    
    if ($check_badge->num_rows == 0) {
        $unique = true;
    } else {
        // Generate new badge number
        $badge_number = 'PNP-' . $year . '-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }
    $check_badge->close();
    $attempts++;
}

// =====================================================
// IMPORTANT: HASH THE PASSWORD USING password_hash()
// =====================================================
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check if password hashing was successful
if ($hashed_password === false) {
    $_SESSION['errors'] = ['Password hashing failed. Please try again.'];
    header('Location: ../register.php');
    exit();
}

// Insert user into database with hashed password
$stmt = $conn->prepare("
    INSERT INTO users (
        badge_number, rank, first_name, last_name, email, password, 
        role, account_status, date_hired
    ) VALUES (?, ?, ?, ?, ?, ?, 'user', 'active', CURDATE())
");

$stmt->bind_param("ssssss", 
    $badge_number, $rank, $firstname, $lastname, $email, $hashed_password
);

if ($stmt->execute()) {
    $user_id = $stmt->insert_id;
    
    // Log the registration
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $log_stmt = $conn->prepare("
        INSERT INTO activity_logs (user_id, action, table_name, record_id, details, ip_address) 
        VALUES (?, 'REGISTER', 'users', ?, 'New user registered', ?)
    ");
    $log_stmt->bind_param("iis", $user_id, $user_id, $ip);
    $log_stmt->execute();
    $log_stmt->close();
    
    $_SESSION['success'] = 'Registration successful! You can now login with your credentials.';
    header('Location: ../index.php');
    exit();
} else {
    // Log the error for debugging
    error_log("Registration failed: " . $conn->error);
    $_SESSION['errors'] = ['Registration failed. Please try again later.'];
    // Preserve form data
    $_SESSION['old_email'] = $email;
    $_SESSION['old_firstname'] = $firstname;
    $_SESSION['old_lastname'] = $lastname;
    $_SESSION['old_rank'] = $rank;
    header('Location: ../register.php');
    exit();
}

$stmt->close();
$conn->close();
?>