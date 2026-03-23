<?php
// =====================================================
// FILE: admin/get_notifications.php
// PURPOSE: Get unread notifications for admin (auto-approved only)
// FIXED: Removed report_type dependency
// =====================================================

session_start();
require_once '../config/db_connect.php';
requireAdmin();

$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'];

switch ($action) {
    case 'get_count':
        // Get unread notification count
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['count'];
        
        echo json_encode(['success' => true, 'count' => $count]);
        break;

    case 'get_notifications':
        // Get recent notifications - FIXED: removed report_link
        $stmt = $conn->prepare("
            SELECT n.*
            FROM notifications n
            WHERE n.user_id = ?
            ORDER BY n.created_at DESC
            LIMIT 10
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $notifications = [];
        
        while ($row = $result->fetch_assoc()) {
            // Format time for each notification
            $row['time_ago'] = timeAgo($row['created_at']);
            
            // Create a default report link based on message content
            $row['report_link'] = '#';
            if (strpos($row['message'], 'patrol') !== false) {
                $row['report_link'] = 'all_reports.php?view=daily';
            } elseif (strpos($row['message'], 'checkpoint') !== false) {
                $row['report_link'] = 'checkpoint.php';
            } elseif (strpos($row['message'], 'oplan') !== false) {
                $row['report_link'] = 'oplanbakal.php';
            }
            
            $notifications[] = $row;
        }
        
        echo json_encode(['success' => true, 'notifications' => $notifications]);
        break;

    case 'mark_read':
        // Mark notification as read
        $notification_id = $_POST['notification_id'] ?? 0;
        
        if ($notification_id) {
            $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $notification_id, $user_id);
            $stmt->execute();
        } else {
            // Mark all as read
            $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
        }
        
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();

// Helper function for time ago
function timeAgo($timestamp) {
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;
    
    $minutes = round($seconds / 60);
    $hours = round($seconds / 3600);
    $days = round($seconds / 86400);
    $weeks = round($seconds / 604800);
    $months = round($seconds / 2629440);
    $years = round($seconds / 31553280);
    
    if ($seconds <= 60) {
        return "Just now";
    } else if ($minutes <= 60) {
        return ($minutes == 1) ? "1 minute ago" : "$minutes minutes ago";
    } else if ($hours <= 24) {
        return ($hours == 1) ? "1 hour ago" : "$hours hours ago";
    } else if ($days <= 7) {
        return ($days == 1) ? "yesterday" : "$days days ago";
    } else if ($weeks <= 4.3) {
        return ($weeks == 1) ? "1 week ago" : "$weeks weeks ago";
    } else if ($months <= 12) {
        return ($months == 1) ? "1 month ago" : "$months months ago";
    } else {
        return ($years == 1) ? "1 year ago" : "$years years ago";
    }
}
?>