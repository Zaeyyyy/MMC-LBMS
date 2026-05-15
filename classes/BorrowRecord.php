<?php
/**
 * Borrow/Circulation Management Class
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Database.php';

class BorrowRecord {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Borrow a book
     */
    public function borrowBook($user_id, $book_id, $librarian_id) {
        try {
            $this->db->begin_transaction();

            // Check if book is available
            $book_stmt = $this->db->prepare("SELECT quantity_available FROM books WHERE id = ? AND status = 'available'");
            $book_stmt->bind_param("i", $book_id);
            $book_stmt->execute();
            $book_result = $book_stmt->get_result();

            if ($book_result->num_rows == 0) {
                throw new Exception('Book not available');
            }

            $book = $book_result->fetch_assoc();
            if ($book['quantity_available'] <= 0) {
                throw new Exception('No copies available');
            }

            // Check user borrowing limit
            $borrow_count = $this->db->query("SELECT COUNT(*) as count FROM borrow_records WHERE user_id = $user_id AND status = 'borrowed'")->fetch_assoc()['count'];
            if ($borrow_count >= MAX_BORROW_DURATION) {
                throw new Exception('Borrow limit reached');
            }

            // Create borrow record
            $due_date = date('Y-m-d', strtotime('+' . MAX_BORROW_DURATION . ' days'));
            $borrow_stmt = $this->db->prepare("
                INSERT INTO borrow_records (user_id, book_id, due_date, status, librarian_id)
                VALUES (?, ?, ?, 'borrowed', ?)
            ");
            $borrow_stmt->bind_param("iisi", $user_id, $book_id, $due_date, $librarian_id);
            $borrow_stmt->execute();

            // Update book quantity
            $update_stmt = $this->db->prepare("UPDATE books SET quantity_available = quantity_available - 1 WHERE id = ?");
            $update_stmt->bind_param("i", $book_id);
            $update_stmt->execute();

            // Create notification
            $this->createNotification($user_id, 'Book Borrowed', 'You have borrowed a book. Due date: ' . $due_date, 'due_reminder');

            $this->db->commit();
            return ['success' => true, 'message' => 'Book borrowed successfully', 'due_date' => $due_date];
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Return a book
     */
    public function returnBook($borrow_id, $condition = 'good', $librarian_id = null) {
        try {
            $this->db->begin_transaction();

            // Get borrow record
            $borrow_stmt = $this->db->prepare("
                SELECT br.*, b.book_value FROM borrow_records br
                JOIN books b ON br.book_id = b.id
                WHERE br.id = ?
            ");
            $borrow_stmt->bind_param("i", $borrow_id);
            $borrow_stmt->execute();
            $borrow_result = $borrow_stmt->get_result();

            if ($borrow_result->num_rows == 0) {
                throw new Exception('Borrow record not found');
            }

            $borrow = $borrow_result->fetch_assoc();

            // Check for overdue and create fine if needed
            $today = date('Y-m-d');
            if ($today > $borrow['due_date']) {
                $days_overdue = (strtotime($today) - strtotime($borrow['due_date'])) / 86400;
                $fine_amount = $days_overdue * DAILY_FINE_RATE;
                $this->createFine($borrow['user_id'], $borrow_id, 'late_fee', $fine_amount, "Overdue by $days_overdue days");
            }

            // Handle damaged/lost books
            if ($condition === 'damaged') {
                $damage_fine = ($borrow['book_value'] * DAMAGE_FEE) / 100;
                $this->createFine($borrow['user_id'], $borrow_id, 'damage_fee', $damage_fine, 'Book returned in damaged condition');
            } elseif ($condition === 'lost') {
                $lost_fine = ($borrow['book_value'] * LOST_BOOK_FEE_PERCENT) / 100;
                $this->createFine($borrow['user_id'], $borrow_id, 'lost_book_fee', $lost_fine, 'Book not returned');
            }

            // Update borrow record
            $new_status = ($condition === 'lost' || $condition === 'damaged') ? 'overdue' : 'returned';
            $return_stmt = $this->db->prepare("UPDATE borrow_records SET return_date = NOW(), status = ? WHERE id = ?");
            $return_stmt->bind_param("si", $new_status, $borrow_id);
            $return_stmt->execute();

            // Update book quantity
            $book_stmt = $this->db->prepare("UPDATE books SET quantity_available = quantity_available + 1 WHERE id = ?");
            $book_stmt->bind_param("i", $borrow['book_id']);
            $book_stmt->execute();

            $this->db->commit();
            return ['success' => true, 'message' => 'Book returned successfully'];
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Renew a book
     */
    public function renewBook($borrow_id) {
        try {
            // Get borrow record
            $borrow_stmt = $this->db->prepare("SELECT due_date, renewal_count FROM borrow_records WHERE id = ?");
            $borrow_stmt->bind_param("i", $borrow_id);
            $borrow_stmt->execute();
            $borrow_result = $borrow_stmt->get_result();

            if ($borrow_result->num_rows == 0) {
                throw new Exception('Borrow record not found');
            }

            $borrow = $borrow_result->fetch_assoc();

            // Check renewal limit (max 2 times)
            if ($borrow['renewal_count'] >= 2) {
                throw new Exception('Renewal limit reached');
            }

            // Calculate new due date
            $new_due_date = date('Y-m-d', strtotime($borrow['due_date'] . ' + ' . MAX_BORROW_DURATION . ' days'));

            // Update record
            $update_stmt = $this->db->prepare("
                UPDATE borrow_records
                SET due_date = ?, renewal_count = renewal_count + 1
                WHERE id = ?
            ");
            $update_stmt->bind_param("si", $new_due_date, $borrow_id);

            if ($update_stmt->execute()) {
                return ['success' => true, 'message' => 'Book renewed successfully', 'new_due_date' => $new_due_date];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get user's borrowed books
     */
    public function getUserBorrowedBooks($user_id, $status = 'borrowed') {
        $stmt = $this->db->prepare("
            SELECT br.*, b.title, b.isbn, b.book_value, DATEDIFF(br.due_date, CURDATE()) as days_left
            FROM borrow_records br
            JOIN books b ON br.book_id = b.id
            WHERE br.user_id = ? AND br.status = ?
            ORDER BY br.due_date ASC
        ");
        $stmt->bind_param("is", $user_id, $status);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get overdue books
     */
    public function getOverdueBooks() {
        $today = date('Y-m-d');
        $stmt = $this->db->prepare("
            SELECT br.*, b.title, u.email, u.first_name, u.last_name
            FROM borrow_records br
            JOIN books b ON br.book_id = b.id
            JOIN users u ON br.user_id = u.id
            WHERE br.status = 'borrowed' AND br.due_date < ?
            ORDER BY br.due_date ASC
        ");
        $stmt->bind_param("s", $today);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Create notification
     */
    private function createNotification($user_id, $title, $message, $type) {
        $stmt = $this->db->prepare("
            INSERT INTO notifications (user_id, title, message, notification_type)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isss", $user_id, $title, $message, $type);
        return $stmt->execute();
    }

    /**
     * Create fine
     */
    private function createFine($user_id, $borrow_id, $fine_type, $amount, $reason) {
        $stmt = $this->db->prepare("
            INSERT INTO fines (user_id, borrow_record_id, fine_type, amount, reason, status)
            VALUES (?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->bind_param("iisds", $user_id, $borrow_id, $fine_type, $amount, $reason);
        return $stmt->execute();
    }

    /**
     * Get borrow history
     */
    public function getBorrowHistory($user_id, $limit = 10) {
        $stmt = $this->db->prepare("
            SELECT br.*, b.title, b.isbn
            FROM borrow_records br
            JOIN books b ON br.book_id = b.id
            WHERE br.user_id = ?
            ORDER BY br.borrow_date DESC
            LIMIT ?
        ");
        $stmt->bind_param("ii", $user_id, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>
