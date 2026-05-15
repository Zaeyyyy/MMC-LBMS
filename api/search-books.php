<?php
/**
 * API: Search Books
 */
header('Content-Type: application/json');
session_start();

require_once '../config/config.php';
require_once '../classes/Book.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$query = $_GET['q'] ?? '';

if (strlen($query) < 2) {
    echo json_encode(['success' => false, 'message' => 'Query too short']);
    exit;
}

$book = new Book();
$results = $book->searchBooks($query);

echo json_encode([
    'success' => true,
    'data' => $results,
    'count' => count($results)
]);
?>
