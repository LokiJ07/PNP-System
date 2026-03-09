<?php
// =====================================================
// FILE: admin/get_notifications.php
// PURPOSE: Get unread notifications for admin
// =====================================================

session_start();
require_once '../config/db_connect.php';
requireAdmin();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_count':
        // Get unread notification count
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['count'];
        
        echo json_encode(['success' => true, 'count' => $count]);
        break;

    case 'get_notifications':
        // Get recent notifications
        $stmt = $conn->prepare("
            SELECT n.*, 
                   CASE 
                       WHEN n.report_type = 'patrol' THEN CONCAT('view_report.php?type=patrol&id=', n.report_id)
                       WHEN n.report_type = 'checkpoint' THEN CONCAT('view_report.php?type=checkpoint&id=', n.report_id)
                       WHEN n.report_type = 'oplan' THEN CONCAT('view_report.php?type=oplan&id=', n.report_id)
                   END as report_link
            FROM notifications n
            WHERE n.user_id = ?
            ORDER BY n.created_at DESC
            LIMIT 20
        ");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode(['success' => true, 'notifications' => $notifications]);
        break;

    case 'mark_read':
        // Mark notification as read
        $notification_id = $_POST['notification_id'] ?? 0;
        
        if ($notification_id) {
            $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $notification_id, $_SESSION['user_id']);
            $stmt->execute();
        } else {
            // Mark all as read
            $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
        }
        
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();
?>