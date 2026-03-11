<?php
// =====================================================
// FILE: user/submit_activity.php
// PURPOSE: Handle activity submission with multiple photos
// =====================================================
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $activity_type = $_POST['activity_type'] ?? '';
    $latitude = $_POST['latitude'] ?? '';
    $longitude = $_POST['longitude'] ?? '';
    $barangay_id = $_POST['barangay_id'] ?? null;
    $specific_location = $_POST['specific_location'] ?? '';
    $activity_date = $_POST['activity_date'] ?? '';
    $activity_time = $_POST['activity_time'] ?? '';
    $accomplishment_description = $_POST['accomplishment_description'] ?? '';
    $gps_accuracy = $_POST['gps_accuracy'] ?? null;
    
    // Validation
    $errors = [];
    
    if (empty($activity_type)) $errors[] = "Activity type is required";
    if (empty($latitude) || empty($longitude)) $errors[] = "Please select a location on the map";
    if (empty($activity_date)) $errors[] = "Date is required";
    if (empty($activity_time)) $errors[] = "Time is required";
    if (empty($accomplishment_description)) $errors[] = "Accomplishment description is required";
    if (empty($barangay_id)) $errors[] = "Please select a barangay";
    
    if (!empty($errors)) {
        $_SESSION['error'] = implode("<br>", $errors);
        header('Location: user_dashboard.php');
        exit();
    }
    
    // Handle multiple photo uploads
    $uploaded_photos = [];
    $total_size = 0;
    
    if (isset($_FILES['photos']) && !empty($_FILES['photos']['name'][0])) {
        $upload_dir = '../uploads/activity_photos/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_count = count($_FILES['photos']['name']);
        
        // Check if more than 5 files
        if ($file_count > 5) {
            $_SESSION['error'] = "Maximum 5 photos allowed";
            header('Location: user_dashboard.php');
            exit();
        }
        
        // Calculate total size and validate each file
        for ($i = 0; $i < $file_count; $i++) {
            $total_size += $_FILES['photos']['size'][$i];
            
            // Check individual file size (10MB per file)
            if ($_FILES['photos']['size'][$i] > 10 * 1024 * 1024) {
                $_SESSION['error'] = "Each photo must be less than 10MB";
                header('Location: user_dashboard.php');
                exit();
            }
        }
        
        // Check total size (15MB max)
        if ($total_size > 15 * 1024 * 1024) {
            $_SESSION['error'] = "Total photo size must be less than 15MB";
            header('Location: user_dashboard.php');
            exit();
        }
        
        // Upload each file
        for ($i = 0; $i < $file_count; $i++) {
            if ($_FILES['photos']['error'][$i] === UPLOAD_ERR_OK) {
                $extension = strtolower(pathinfo($_FILES['photos']['name'][$i], PATHINFO_EXTENSION));
                
                if (in_array($extension, $allowed)) {
                    $filename = 'activity_' . $user_id . '_' . time() . '_' . $i . '.' . $extension;
                    $filepath = $upload_dir . $filename;
                    $relative_path = 'uploads/activity_photos/' . $filename;
                    
                    if (move_uploaded_file($_FILES['photos']['tmp_name'][$i], $filepath)) {
                        $uploaded_photos[] = $relative_path;
                    }
                }
            }
        }
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        $activity_id = null;
        $activity_type_db = '';
        
        // Determine which table to insert into based on activity type
        if (in_array($activity_type, ['Foot Patrol', 'Mobile Patrol', 'Motorcycle Patrol'])) {
            // Insert into patrol_activities
            $personnel_count = $_POST['personnel_count'] ?? 1;
            $vehicle_number = $_POST['vehicle_number'] ?? null;
            
            // Count: 12 variables + 1 status = 13 total, status is 'approved' in query
            $stmt = $conn->prepare("
                INSERT INTO patrol_activities (
                    user_id, barangay_id, patrol_type, specific_location, 
                    latitude, longitude, gps_accuracy, personnel_count, vehicle_number,
                    patrol_date, patrol_time, accomplishment_description, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'approved')
            ");
            
            // 12 variables = 12 type characters
            $stmt->bind_param(
                "iisssddissss",  // i,i,s,s,s,s,d,d,i,s,s,s = 12 characters
                $user_id, 
                $barangay_id, 
                $activity_type, 
                $specific_location,
                $latitude, 
                $longitude, 
                $gps_accuracy, 
                $personnel_count, 
                $vehicle_number,
                $activity_date, 
                $activity_time, 
                $accomplishment_description
            );
            
            $stmt->execute();
            $activity_id = $stmt->insert_id;
            $activity_type_db = 'patrol';
            $stmt->close();
            
        } elseif ($activity_type === 'checkpoint') {
            // Insert into checkpoint_activities
            $border_control_ops = $_POST['border_control_ops'] ?? 0;
            $border_personnel = $_POST['border_personnel'] ?? 0;
            $overlapping_ops = $_POST['overlapping_ops'] ?? 0;
            $mobile_checkpoint_ops = $_POST['mobile_checkpoint_ops'] ?? 0;
            $mobile_personnel = $_POST['mobile_personnel'] ?? 0;
            $tct_ovr_accomplishment = $_POST['tct_ovr_accomplishment'] ?? 0;
            $arrested_accomplishment = $_POST['arrested_accomplishment'] ?? 0;
            
            // Count: 16 variables + 1 status = 17 total, status is 'approved' in query
            $stmt = $conn->prepare("
                INSERT INTO checkpoint_activities (
                    user_id, barangay_id, specific_location, 
                    checkpoint_date, checkpoint_time,
                    border_control_ops, border_personnel, overlapping_ops,
                    mobile_checkpoint_ops, mobile_personnel, 
                    tct_ovr_accomplishment, arrested_accomplishment,
                    accomplishment_description, latitude, longitude, gps_accuracy,
                    status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'approved')
            ");
            
            // 16 variables = 16 type characters
            $stmt->bind_param(
                "iisssiiiiiiissdd",  // i,i,s,s,s,i,i,i,i,i,i,i,s,s,d,d = 16 characters
                $user_id, 
                $barangay_id, 
                $specific_location,
                $activity_date, 
                $activity_time,
                $border_control_ops, 
                $border_personnel, 
                $overlapping_ops,
                $mobile_checkpoint_ops, 
                $mobile_personnel,
                $tct_ovr_accomplishment, 
                $arrested_accomplishment,
                $accomplishment_description, 
                $latitude, 
                $longitude, 
                $gps_accuracy
            );
            
            $stmt->execute();
            $activity_id = $stmt->insert_id;
            $activity_type_db = 'checkpoint';
            $stmt->close();
            
        } elseif (in_array($activity_type, ['Oplan Bakal', 'Oplan Sita'])) {
            // Insert into oplan_activities
            $personnel_count = $_POST['personnel_count'] ?? 1;
            $operations_count = $_POST['operations_count'] ?? 1;
            $arrests_made = $_POST['arrests_made'] ?? 0;
            $firearms_seized = $_POST['firearms_seized'] ?? 0;
            $contraband_kg = $_POST['contraband_kg'] ?? 0;
            
            // Count: 15 variables + 1 status = 16 total, status is 'approved' in query
            $stmt = $conn->prepare("
                INSERT INTO oplan_activities (
                    user_id, barangay_id, oplan_type, specific_location, 
                    latitude, longitude, gps_accuracy, personnel_count, 
                    operations_count, arrests_made, firearms_seized, contraband_kg,
                    oplan_date, oplan_time, accomplishment_description, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'approved')
            ");
            
            // 15 variables = 15 type characters
            $stmt->bind_param(
                "iissssdiiddssss",  // i,i,s,s,s,s,d,i,i,d,d,s,s,s,s = 15 characters
                $user_id, 
                $barangay_id, 
                $activity_type, 
                $specific_location,
                $latitude, 
                $longitude, 
                $gps_accuracy, 
                $personnel_count,
                $operations_count, 
                $arrests_made, 
                $firearms_seized, 
                $contraband_kg,
                $activity_date, 
                $activity_time, 
                $accomplishment_description
            );
            
            $stmt->execute();
            $activity_id = $stmt->insert_id;
            $activity_type_db = 'oplan';
            $stmt->close();
        }
        
        // Insert photos into activity_photos table if any were uploaded
        if (!empty($uploaded_photos) && $activity_id) {
            $photo_stmt = $conn->prepare("
                INSERT INTO activity_photos (activity_type, activity_id, photo_path)
                VALUES (?, ?, ?)
            ");
            
            foreach ($uploaded_photos as $photo_path) {
                $photo_stmt->bind_param("sis", $activity_type_db, $activity_id, $photo_path);
                $photo_stmt->execute();
            }
            $photo_stmt->close();
        }
        
        // Commit transaction
        $conn->commit();
        $_SESSION['success'] = "Activity reported successfully!";
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error'] = "Failed to submit activity: " . $e->getMessage();
    }
    
    header('Location: user_dashboard.php');
    exit();
    
} else {
    header('Location: user_dashboard.php');
    exit();
}
?>