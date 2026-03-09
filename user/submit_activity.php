<?php
// =====================================================
// FILE: user/submit_activity.php
// PURPOSE: Process activity form submission
// =====================================================
session_start();
require_once '../config/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: user_dashboard.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$activity_type = $_POST['activity_type'] ?? '';
$barangay_id = $_POST['barangay_id'] ?? '';
$specific_location = $_POST['specific_location'] ?? '';
$activity_date = $_POST['activity_date'] ?? '';
$activity_time = $_POST['activity_time'] ?? '';
$latitude = $_POST['latitude'] ?? null;
$longitude = $_POST['longitude'] ?? null;
$gps_accuracy = $_POST['gps_accuracy'] ?? null;
$accomplishment = $_POST['accomplishment_description'] ?? '';

// Validate required fields
if (empty($activity_type) || empty($barangay_id) || empty($specific_location) || 
    empty($activity_date) || empty($activity_time) || empty($accomplishment)) {
    $_SESSION['error'] = 'Please fill in all required fields';
    header('Location: user_dashboard.php');
    exit();
}

$success = false;
$activity_id = null;
$activity_table = '';

// Handle based on activity type
if (strpos($activity_type, 'patrol') !== false) {
    // Patrol activity
    $patrol_type = '';
    switch ($activity_type) {
        case 'foot_patrol': $patrol_type = 'Foot Patrol'; break;
        case 'mobile_patrol': $patrol_type = 'Mobile Patrol'; break;
        case 'motor_patrol': $patrol_type = 'Motorcycle Patrol'; break;
    }
    
    $personnel = $_POST['personnel_count'] ?? 1;
    $vehicle = $_POST['vehicle_number'] ?? null;
    
    $stmt = $conn->prepare("
        INSERT INTO patrol_activities 
        (user_id, barangay_id, patrol_type, specific_location, patrol_date, patrol_time, 
         personnel_count, vehicle_number, accomplishment_description, latitude, longitude, gps_accuracy, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
    ");
    
    $stmt->bind_param("iissssisssdd", 
        $user_id, $barangay_id, $patrol_type, $specific_location, $activity_date, $activity_time,
        $personnel, $vehicle, $accomplishment, $latitude, $longitude, $gps_accuracy
    );
    
    if ($stmt->execute()) {
        $success = true;
        $activity_id = $stmt->insert_id;
        $activity_table = 'patrol_activities';
    }
    
} elseif ($activity_type === 'checkpoint') {
    // Checkpoint activity
    $border_ops = $_POST['border_control_ops'] ?? 0;
    $mobile_ops = $_POST['mobile_checkpoint_ops'] ?? 0;
    $tct_ovr = $_POST['tct_ovr'] ?? 0;
    
    $stmt = $conn->prepare("
        INSERT INTO checkpoint_activities 
        (user_id, barangay_id, specific_location, checkpoint_date, checkpoint_time,
         border_control_ops, mobile_checkpoint_ops, tct_ovr_accomplishment,
         accomplishment_description, latitude, longitude, gps_accuracy, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
    ");
    
    $stmt->bind_param("iisssiiisssdd", 
        $user_id, $barangay_id, $specific_location, $activity_date, $activity_time,
        $border_ops, $mobile_ops, $tct_ovr, $accomplishment, 
        $latitude, $longitude, $gps_accuracy
    );
    
    if ($stmt->execute()) {
        $success = true;
        $activity_id = $stmt->insert_id;
        $activity_table = 'checkpoint_activities';
    }
    
} elseif ($activity_type === 'oplan_bakal' || $activity_type === 'oplan_sita') {
    // Oplan activity
    $oplan_type = ($activity_type === 'oplan_bakal') ? 'Oplan Bakal' : 'Oplan Sita';
    $personnel = $_POST['personnel_count'] ?? 1;
    $operations = $_POST['operations_count'] ?? 1;
    $arrests = $_POST['oplan_arrests'] ?? 0;
    $firearms = $_POST['firearms_seized'] ?? 0;
    $contraband = $_POST['contraband_kg'] ?? 0;
    
    $stmt = $conn->prepare("
        INSERT INTO oplan_activities 
        (user_id, barangay_id, oplan_type, specific_location, oplan_date, oplan_time,
         personnel_count, operations_count, arrests_made, firearms_seized, contraband_kg,
         accomplishment_description, latitude, longitude, gps_accuracy, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
    ");
    
    $stmt->bind_param("iissssiiiddssdd", 
        $user_id, $barangay_id, $oplan_type, $specific_location, $activity_date, $activity_time,
        $personnel, $operations, $arrests, $firearms, $contraband, $accomplishment,
        $latitude, $longitude, $gps_accuracy
    );
    
    if ($stmt->execute()) {
        $success = true;
        $activity_id = $stmt->insert_id;
        $activity_table = 'oplan_activities';
    }
}

// Handle photo upload
if ($success && isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['photo'];
    $file_size = $file['size'] / (1024 * 1024); // Size in MB
    
    if ($file_size <= 15) {
        $upload_dir = '../uploads/';
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $type_dir = $upload_dir . $activity_type . '/';
        if (!file_exists($type_dir)) mkdir($type_dir, 0777, true);
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $activity_id . '_' . time() . '.' . $extension;
        $filepath = $type_dir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $photo_path = 'uploads/' . $activity_type . '/' . $filename;
            $photo_stmt = $conn->prepare("INSERT INTO activity_photos (activity_type, activity_id, photo_path, photo_name, file_size) VALUES (?, ?, ?, ?, ?)");
            $photo_stmt->bind_param("sisss", $activity_type, $activity_id, $photo_path, $file['name'], $file_size);
            $photo_stmt->execute();
            $photo_stmt->close();
        }
    }
}

if ($success) {
    $_SESSION['success'] = 'Activity reported successfully!';
} else {
    $_SESSION['error'] = 'Failed to submit activity. Please try again.';
}

if (isset($stmt)) $stmt->close();
$conn->close();

header('Location: user_dashboard.php');
exit();
?>