<?php
/**
 * API: Return Book
 */
header('Content-Type: application/json');
session_start();

require_once '../config/config.php';
require_once '../classes/BorrowRecord.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$borrow_id = $_POST['borrow_id'] ?? null;
$condition = $_POST['condition'] ?? 'good';

if (!$borrow_id) {
    echo json_encode(['success' => false, 'message' => 'Borrow ID required']);
    exit;
}

$borrow = new BorrowRecord();
$result = $borrow->returnBook($borrow_id, $condition, $_SESSION['user_id']);

echo json_encode($result);
?>
