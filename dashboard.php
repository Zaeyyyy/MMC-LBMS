<?php
ob_start();

// Session configuration MUST be before session_start()
ini_set('session.gc_maxlifetime', 3600);

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    header('Location: index.php');
    exit;
}

require_once 'config/config.php';
require_once 'classes/User.php';
require_once 'classes/Report.php';
require_once 'classes/Database.php';

try {
    $user = new User();
    $user_data = $user->getProfile($_SESSION['user_id']);
    $role = $_SESSION['role'];

    // Redirect based on role
    if ($role === 'admin') {
        ob_end_clean();
        header('Location: admin/dashboard.php');
        exit;
    } elseif ($role === 'librarian') {
        ob_end_clean();
        header('Location: librarian/dashboard.php');
        exit;
    } else {
        ob_end_clean();
        header('Location: member/dashboard.php');
        exit;
    }
} catch (Exception $e) {
    ob_end_clean();
    die('Error: ' . htmlspecialchars($e->getMessage()));
}
?>
