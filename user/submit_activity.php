<?php
// =====================================================
// FILE: user/submit_activity.php
// PURPOSE: Handle activity submission with multiple photos and ALL fields
// =====================================================
session_start();
require_once '../config/db_connect.php';

// Enable error reporting for debugging (remove after fixing)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
    
    // Common fields
    $personnel_count = $_POST['personnel_count'] ?? 1;
    $vehicle_number = $_POST['vehicle_number'] ?? null;
    
    // Violation fields
    $drinking_violations = $_POST['drinking_violations'] ?? 0;
    $smoking_violations = $_POST['smoking_violations'] ?? 0;
    $halfnaked_violations = $_POST['halfnaked_violations'] ?? 0;
    $curfew_violations = $_POST['curfew_violations'] ?? 0;
    $vandalism_violations = $_POST['vandalism_violations'] ?? 0;
    $other_violations = $_POST['other_violations'] ?? 0;
    $other_violations_desc = $_POST['other_violations_desc'] ?? null;
    
    // Disposition fields
    $fixed_count = $_POST['fixed_count'] ?? 0;
    $fined_count = $_POST['fined_count'] ?? 0;
    $warned_count = $_POST['warned_count'] ?? 0;
    $charged_count = $_POST['charged_count'] ?? 0;
    $community_service = $_POST['community_service'] ?? 0;
    $disposition_others = $_POST['disposition_others'] ?? null;
    
    // Oplan specific fields
    $arrests_made = $_POST['arrests_made'] ?? 0;
    $house_visitations = $_POST['house_visitations'] ?? 0;
    $kontra_boga = $_POST['kontra_boga'] ?? 0;
    $anti_vaping = $_POST['anti_vaping'] ?? 0;
    $firearms_seized = $_POST['firearms_seized'] ?? 0;
    $firearms_crs = $_POST['firearms_crs'] ?? 0;
    $fas_deposit = $_POST['fas_deposit'] ?? 0;
    $renewed_fas = $_POST['renewed_fas'] ?? 0;
    $contraband_kg = $_POST['contraband_kg'] ?? 0.00;
    $operations_count = $_POST['operations_count'] ?? 1;
    
    // Checkpoint specific fields
    $border_control_ops = $_POST['border_control_ops'] ?? 0;
    $border_personnel = $_POST['border_personnel'] ?? 0;
    $overlapping_ops = $_POST['overlapping_ops'] ?? 0;
    $mobile_checkpoint_ops = $_POST['mobile_checkpoint_ops'] ?? 0;
    $mobile_personnel = $_POST['mobile_personnel'] ?? 0;
    $tct_ovr_accomplishment = $_POST['tct_ovr_accomplishment'] ?? 0;
    $arrested_accomplishment = $_POST['arrested_accomplishment'] ?? 0;
    
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
        
        // =========================================
        // PATROL ACTIVITIES - COMPLETE FIX
        // =========================================
        if (in_array($activity_type, ['Foot Patrol', 'Mobile Patrol', 'Motorcycle Patrol'])) {
            
            $sql = "INSERT INTO patrol_activities (
                user_id, barangay_id, patrol_type, specific_location, 
                latitude, longitude, gps_accuracy, personnel_count, vehicle_number,
                patrol_date, patrol_time, accomplishment_description,
                drinking_violations, smoking_violations, halfnaked_violations,
                curfew_violations, vandalism_violations, other_violations, other_violations_desc,
                status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'approved')";
            
            $stmt = $conn->prepare($sql);
            
            // 19 parameters
            $stmt->bind_param(
    "iissdddissssiiiiiis",
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
    $accomplishment_description,
    $drinking_violations,
    $smoking_violations,
    $halfnaked_violations,
    $curfew_violations,
    $vandalism_violations,
    $other_violations,
    $other_violations_desc
);
            
            if (!$stmt->execute()) {
                throw new Exception("Patrol insert failed: " . $stmt->error);
            }
            
            $activity_id = $stmt->insert_id;
            $activity_type_db = 'patrol';
            $stmt->close();
            
        // =========================================
        // CHECKPOINT ACTIVITIES - COMPLETE FIX
        // =========================================
        } elseif ($activity_type === 'checkpoint') {
            
            $sql = "INSERT INTO checkpoint_activities (
                user_id, barangay_id, specific_location, 
                checkpoint_date, checkpoint_time,
                border_control_ops, border_personnel, overlapping_ops,
                mobile_checkpoint_ops, mobile_personnel, 
                tct_ovr_accomplishment, arrested_accomplishment,
                accomplishment_description, latitude, longitude, gps_accuracy,
                drinking_violations, smoking_violations, halfnaked_violations,
                curfew_violations, vandalism_violations, other_violations, other_violations_desc,
                fixed_count, fined_count, warned_count, charged_count, community_service, disposition_others,
                status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'approved')";
            
            $stmt = $conn->prepare($sql);
            
            // 29 parameters
$stmt->bind_param(
"iisssiiiiiisdddiiiiiissiiiiis",
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
$gps_accuracy,
$drinking_violations,
$smoking_violations,
$halfnaked_violations,
$curfew_violations,
$vandalism_violations,
$other_violations,
$other_violations_desc,
$fixed_count,
$fined_count,
$warned_count,
$charged_count,
$community_service,
$disposition_others
);           
            if (!$stmt->execute()) {
                throw new Exception("Checkpoint insert failed: " . $stmt->error);
            }
            
            $activity_id = $stmt->insert_id;
            $activity_type_db = 'checkpoint';
            $stmt->close();
            
        // =========================================
        // OPLAN BAKAL - WORKING
        // =========================================
        } elseif ($activity_type === 'Oplan Bakal') {
            
            $sql = "INSERT INTO oplan_activities (
                user_id, barangay_id, oplan_type, specific_location, 
                latitude, longitude, gps_accuracy, personnel_count, 
                operations_count, arrests_made, house_visitations,
                firearms_seized, firearms_crs, fas_deposit, renewed_fas,
                oplan_date, oplan_time, accomplishment_description,
                status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'approved')";
            
            $stmt = $conn->prepare($sql);
            
            // 18 parameters
            $stmt->bind_param(
                "iissssdiiddiiiisss", 
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
                $house_visitations,
                $firearms_seized,
                $firearms_crs,
                $fas_deposit,
                $renewed_fas,
                $activity_date,
                $activity_time,
                $accomplishment_description
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Oplan Bakal insert failed: " . $stmt->error);
            }
            
            $activity_id = $stmt->insert_id;
            $activity_type_db = 'oplan';
            $stmt->close();
            
        // =========================================
        // OPLAN SITA - COMPLETE FIX
        // =========================================
        } elseif ($activity_type === 'Oplan Sita') {
            
            $sql = "INSERT INTO oplan_activities (
                user_id, barangay_id, oplan_type, specific_location, 
                latitude, longitude, gps_accuracy, personnel_count, 
                operations_count, arrests_made, contraband_kg, kontra_boga, anti_vaping, house_visitations,
                drinking_violations, smoking_violations, halfnaked_violations,
                curfew_violations, vandalism_violations, other_violations, other_violations_desc,
                fixed_count, fined_count, warned_count, charged_count, community_service, disposition_others,
                oplan_date, oplan_time, accomplishment_description,
                status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'approved')";
            
            $stmt = $conn->prepare($sql);
            
            // 29 parameters
$stmt->bind_param(
"iissdddiiidiiiiiiiiisiiiiissss",
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
$contraband_kg,
$kontra_boga,
$anti_vaping,
$house_visitations,
$drinking_violations,
$smoking_violations,
$halfnaked_violations,
$curfew_violations,
$vandalism_violations,
$other_violations,
$other_violations_desc,
$fixed_count,
$fined_count,
$warned_count,
$charged_count,
$community_service,
$disposition_others,
$activity_date,
$activity_time,
$accomplishment_description
);
            
            if (!$stmt->execute()) {
                throw new Exception("Oplan Sita insert failed: " . $stmt->error);
            }
            
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
                if (!$photo_stmt->execute()) {
                    throw new Exception("Photo insert failed: " . $photo_stmt->error);
                }
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
        
        // Log error for debugging
        error_log("Activity submission error: " . $e->getMessage());
    }
    
    header('Location: user_dashboard.php');
    exit();
    
} else {
    header('Location: user_dashboard.php');
    exit();
}
?>