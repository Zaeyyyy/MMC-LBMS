<?php
/**
 * API: Reserve Book
 */
header('Content-Type: application/json');
session_start();

require_once '../config/config.php';
require_once '../classes/Reservation.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$book_id = $_POST['book_id'] ?? null;

if (!$book_id) {
    echo json_encode(['success' => false, 'message' => 'Book ID required']);
    exit;
}

$reservation = new Reservation();
$result = $reservation->reserveBook($_SESSION['user_id'], $book_id);

echo json_encode($result);
?>
