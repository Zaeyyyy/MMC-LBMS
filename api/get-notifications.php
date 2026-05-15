<?php
/**
 * API: Get User Notifications
 */
header('Content-Type: application/json');
session_start();

require_once '../config/config.php';
require_once '../classes/Notification.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$notification = new Notification();

if (isset($_GET['count'])) {
    // Get unread count
    $count = $notification->getUnreadCount($_SESSION['user_id']);
    echo json_encode(['success' => true, 'count' => $count]);
} else {
    // Get notifications
    $notifications = $notification->getUserNotifications($_SESSION['user_id']);
    echo json_encode(['success' => true, 'data' => $notifications]);
}
?>
