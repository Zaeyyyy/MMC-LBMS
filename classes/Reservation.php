<?php
/**
 * Reservation Management Class
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Database.php';

class Reservation {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Reserve a book
     */
    public function reserveBook($user_id, $book_id) {
        try {
            // Check if book exists
            $book_stmt = $this->db->prepare("SELECT id FROM books WHERE id = ?");
            $book_stmt->bind_param("i", $book_id);
            $book_stmt->execute();

            if ($book_stmt->get_result()->num_rows == 0) {
                throw new Exception('Book not found');
            }

            // Check if already reserved by user
            $check_stmt = $this->db->prepare("
                SELECT id FROM reservations
                WHERE user_id = ? AND book_id = ? AND status IN ('pending', 'ready')
            ");
            $check_stmt->bind_param("ii", $user_id, $book_id);
            $check_stmt->execute();

            if ($check_stmt->get_result()->num_rows > 0) {
                throw new Exception('You already have a reservation for this book');
            }

            // Get queue position
            $position_stmt = $this->db->query("SELECT COUNT(*) as position FROM reservations WHERE book_id = $book_id AND status IN ('pending', 'ready')");
            $position = $position_stmt->fetch_assoc()['position'] + 1;

            // Create reservation
            $hold_until = date('Y-m-d', strtotime('+' . RESERVATION_HOLD_PERIOD . ' days'));
            $reserve_stmt = $this->db->prepare("
                INSERT INTO reservations (user_id, book_id, hold_until_date, status, position)
                VALUES (?, ?, ?, 'pending', ?)
            ");
            $reserve_stmt->bind_param("iisi", $user_id, $book_id, $hold_until, $position);

            if ($reserve_stmt->execute()) {
                // Create notification
                $this->createNotification($user_id, 'Reservation Confirmed', 'Your reservation for the book has been confirmed. Position in queue: ' . $position, 'reservation');

                return ['success' => true, 'message' => 'Book reserved successfully', 'position' => $position];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Cancel reservation
     */
    public function cancelReservation($reservation_id) {
        $stmt = $this->db->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ?");
        $stmt->bind_param("i", $reservation_id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Reservation cancelled'];
        }
        return ['success' => false, 'message' => 'Cancellation failed'];
    }

    /**
     * Get user's reservations
     */
    public function getUserReservations($user_id) {
        $stmt = $this->db->prepare("
            SELECT r.*, b.title, b.isbn, b.book_value
            FROM reservations r
            JOIN books b ON r.book_id = b.id
            WHERE r.user_id = ? AND r.status IN ('pending', 'ready')
            ORDER BY r.position ASC
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get reservation queue for a book
     */
    public function getReservationQueue($book_id) {
        $stmt = $this->db->prepare("
            SELECT r.*, u.first_name, u.last_name, u.email
            FROM reservations r
            JOIN users u ON r.user_id = u.id
            WHERE r.book_id = ? AND r.status IN ('pending', 'ready')
            ORDER BY r.position ASC
        ");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Check if book is available for reservation
     */
    public function isAvailableForReservation($book_id) {
        $stmt = $this->db->prepare("
            SELECT quantity_available FROM books WHERE id = ?
        ");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        return $result && $result['quantity_available'] > 0;
    }

    /**
     * Notify next person in queue when book becomes available
     */
    public function notifyNextInQueue($book_id) {
        $stmt = $this->db->prepare("
            SELECT r.*, u.email FROM reservations r
            JOIN users u ON r.user_id = u.id
            WHERE r.book_id = ? AND r.status = 'pending'
            ORDER BY r.position ASC
            LIMIT 1
        ");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result) {
            // Update status to ready
            $update_stmt = $this->db->prepare("UPDATE reservations SET status = 'ready' WHERE id = ?");
            $update_stmt->bind_param("i", $result['id']);
            $update_stmt->execute();

            // Create notification
            $this->createNotification($result['user_id'], 'Book Ready for Pickup', 'The book you reserved is now available for pickup at the library.', 'reservation');
        }
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
}
?>
