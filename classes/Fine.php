<?php
/**
 * Fine & Payment Management Class
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Database.php';

class Fine {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get user's outstanding fines
     */
    public function getUserFines($user_id, $status = 'pending') {
        $stmt = $this->db->prepare("
            SELECT f.*, b.title
            FROM fines f
            LEFT JOIN borrow_records br ON f.borrow_record_id = br.id
            LEFT JOIN books b ON br.book_id = b.id
            WHERE f.user_id = ? AND f.status = ?
            ORDER BY f.due_date ASC
        ");
        $stmt->bind_param("is", $user_id, $status);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get total outstanding fines
     */
    public function getUserTotalFines($user_id) {
        $stmt = $this->db->prepare("
            SELECT SUM(amount) as total FROM fines
            WHERE user_id = ? AND status = 'pending'
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'] ?? 0;
    }

    /**
     * Record payment for fine
     */
    public function recordPayment($fine_id, $amount, $payment_method, $transaction_id = null) {
        try {
            $this->db->begin_transaction();

            // Get fine details
            $fine_stmt = $this->db->prepare("SELECT user_id, amount FROM fines WHERE id = ?");
            $fine_stmt->bind_param("i", $fine_id);
            $fine_stmt->execute();
            $fine_result = $fine_stmt->get_result();

            if ($fine_result->num_rows == 0) {
                throw new Exception('Fine not found');
            }

            $fine = $fine_result->fetch_assoc();

            if ($amount > $fine['amount']) {
                throw new Exception('Payment amount exceeds fine amount');
            }

            // Generate receipt
            $receipt_number = 'REC' . date('YmdHis') . $fine_id;

            // Create payment record
            $payment_stmt = $this->db->prepare("
                INSERT INTO payments (fine_id, transaction_id, amount, payment_method, receipt_number, status)
                VALUES (?, ?, ?, ?, ?, 'completed')
            ");
            $payment_stmt->bind_param("isds", $fine_id, $transaction_id, $amount, $payment_method);
            $payment_stmt->execute();

            // Update fine status if fully paid
            if ($amount == $fine['amount']) {
                $update_stmt = $this->db->prepare("UPDATE fines SET status = 'paid' WHERE id = ?");
                $update_stmt->bind_param("i", $fine_id);
                $update_stmt->execute();
            }

            // Create notification
            $this->createNotification($fine['user_id'], 'Payment Received', 'Your payment of ' . $amount . ' has been received. Receipt: ' . $receipt_number, 'fine');

            $this->db->commit();
            return ['success' => true, 'message' => 'Payment recorded', 'receipt' => $receipt_number];
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Waive fine (Admin)
     */
    public function waiveFine($fine_id, $reason) {
        $stmt = $this->db->prepare("UPDATE fines SET status = 'waived', reason = ? WHERE id = ?");
        $stmt->bind_param("si", $reason, $fine_id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Fine waived'];
        }
        return ['success' => false, 'message' => 'Failed to waive fine'];
    }

    /**
     * Get all fines report
     */
    public function getFinesReport($date_from = null, $date_to = null) {
        $sql = "
            SELECT f.*, u.first_name, u.last_name, u.email
            FROM fines f
            JOIN users u ON f.user_id = u.id
            WHERE 1=1
        ";

        if ($date_from && $date_to) {
            $sql .= " AND DATE(f.created_at) BETWEEN ? AND ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ss", $date_from, $date_to);
        } else {
            $stmt = $this->db->prepare($sql);
        }

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
}
?>
