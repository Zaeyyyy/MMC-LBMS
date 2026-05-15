<?php
/**
 * API: Borrow Book
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

$book_id = $_POST['book_id'] ?? null;
$librarian_id = $_SESSION['user_id'];

if (!$book_id) {
    echo json_encode(['success' => false, 'message' => 'Book ID required']);
    exit;
}

$borrow = new BorrowRecord();
$result = $borrow->borrowBook($_SESSION['user_id'], $book_id, $librarian_id);

echo json_encode($result);
?>
