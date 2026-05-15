<?php
session_start();

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../config/config.php';
require_once '../classes/User.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit;
}

// Prevent admin from suspending themselves
if ($user_id == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'You cannot suspend your own account']);
    exit;
}

try {
    $user = new User();
    
    // Get current user status
    $current_user = $user->getProfile($user_id);
    
    if (!$current_user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
    // Toggle suspension status
    $new_status = ($current_user['status'] === 'active') ? 'suspended' : 'active';
    
    $result = $user->updateUserStatus($user_id, $new_status);
    
    echo json_encode([
        'success' => $result['success'],
        'message' => $result['message'],
        'new_status' => $new_status
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
