<?php
/**
 * Report & Analytics Class
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Database.php';

class Report {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get borrowing report
     */
    public function getBorrowingReport($date_from = null, $date_to = null) {
        $sql = "
            SELECT br.id, u.first_name, u.last_name, b.title, br.borrow_date, br.due_date, br.return_date, br.status
            FROM borrow_records br
            JOIN users u ON br.user_id = u.id
            JOIN books b ON br.book_id = b.id
            WHERE 1=1
        ";

        if ($date_from && $date_to) {
            $sql .= " AND DATE(br.borrow_date) BETWEEN ? AND ?";
            $stmt = $this->db->prepare($sql . " ORDER BY br.borrow_date DESC");
            $stmt->bind_param("ss", $date_from, $date_to);
        } else {
            $stmt = $this->db->prepare($sql . " ORDER BY br.borrow_date DESC");
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get overdue report
     */
    public function getOverdueReport() {
        $today = date('Y-m-d');
        $stmt = $this->db->prepare("
            SELECT br.*, u.first_name, u.last_name, u.email, b.title, DATEDIFF(?, br.due_date) as days_overdue
            FROM borrow_records br
            JOIN users u ON br.user_id = u.id
            JOIN books b ON br.book_id = b.id
            WHERE br.status = 'borrowed' AND br.due_date < ?
            ORDER BY br.due_date ASC
        ");
        $stmt->bind_param("ss", $today, $today);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get popular books report
     */
    public function getPopularBooks($limit = 10) {
        $stmt = $this->db->prepare("
            SELECT b.*, COUNT(br.id) as borrow_count, GROUP_CONCAT(CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', ') as authors
            FROM books b
            LEFT JOIN borrow_records br ON b.id = br.book_id
            LEFT JOIN book_authors ba ON b.id = ba.book_id
            LEFT JOIN authors a ON ba.author_id = a.id
            GROUP BY b.id
            ORDER BY borrow_count DESC
            LIMIT ?
        ");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get user activity report
     */
    public function getUserActivityReport($user_id = null) {
        if ($user_id) {
            $stmt = $this->db->prepare("
                SELECT COUNT(DISTINCT br.id) as total_borrows, COUNT(DISTINCT res.id) as total_reservations,
                       SUM(CASE WHEN f.status = 'pending' THEN f.amount ELSE 0 END) as outstanding_fines
                FROM users u
                LEFT JOIN borrow_records br ON u.id = br.user_id
                LEFT JOIN reservations res ON u.id = res.user_id
                LEFT JOIN fines f ON u.id = f.user_id
                WHERE u.id = ?
            ");
            $stmt->bind_param("i", $user_id);
        } else {
            $stmt = $this->db->prepare("
                SELECT u.id, u.first_name, u.last_name, u.email,
                       COUNT(DISTINCT br.id) as total_borrows, COUNT(DISTINCT res.id) as total_reservations,
                       SUM(CASE WHEN f.status = 'pending' THEN f.amount ELSE 0 END) as outstanding_fines
                FROM users u
                LEFT JOIN borrow_records br ON u.id = br.user_id
                LEFT JOIN reservations res ON u.id = res.user_id
                LEFT JOIN fines f ON u.id = f.user_id
                WHERE u.role = 'member'
                GROUP BY u.id
                ORDER BY total_borrows DESC
            ");
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get inventory report
     */
    public function getInventoryReport() {
        $stmt = $this->db->query("
            SELECT b.*, cat.name as category_name,
                   GROUP_CONCAT(CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', ') as authors,
                   (b.quantity_total - b.quantity_available) as borrowed,
                   b.quantity_available as available
            FROM books b
            LEFT JOIN categories cat ON b.category_id = cat.id
            LEFT JOIN book_authors ba ON b.id = ba.book_id
            LEFT JOIN authors a ON ba.author_id = a.id
            GROUP BY b.id
            ORDER BY b.title ASC
        ");
        return $stmt->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get financial report
     */
    public function getFinancialReport($date_from = null, $date_to = null) {
        $sql = "
            SELECT f.*, SUM(p.amount) as paid_amount,
                   SUM(CASE WHEN f.status = 'paid' THEN f.amount ELSE 0 END) as collected,
                   SUM(CASE WHEN f.status = 'pending' THEN f.amount ELSE 0 END) as pending,
                   SUM(CASE WHEN f.status = 'waived' THEN f.amount ELSE 0 END) as waived
            FROM fines f
            LEFT JOIN payments p ON f.id = p.fine_id
            WHERE 1=1
        ";

        if ($date_from && $date_to) {
            $sql .= " AND DATE(f.created_at) BETWEEN ? AND ?";
            $stmt = $this->db->prepare($sql . " GROUP BY f.fine_type");
            $stmt->bind_param("ss", $date_from, $date_to);
        } else {
            $stmt = $this->db->prepare($sql . " GROUP BY f.fine_type");
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats() {
        $today = date('Y-m-d');

        $stats = [];

        // Total users
        $result = $this->db->query("SELECT COUNT(*) as count FROM users WHERE role = 'member'");
        $stats['total_users'] = $result->fetch_assoc()['count'];

        // Total books
        $result = $this->db->query("SELECT COUNT(*) as count FROM books");
        $stats['total_books'] = $result->fetch_assoc()['count'];

        // Available books
        $result = $this->db->query("SELECT SUM(quantity_available) as count FROM books");
        $stats['available_books'] = $result->fetch_assoc()['count'];

        // Borrowed books
        $result = $this->db->query("SELECT COUNT(*) as count FROM borrow_records WHERE status = 'borrowed'");
        $stats['borrowed_books'] = $result->fetch_assoc()['count'];

        // Overdue books
        $result = $this->db->query("SELECT COUNT(*) as count FROM borrow_records WHERE status = 'borrowed' AND due_date < '$today'");
        $stats['overdue_books'] = $result->fetch_assoc()['count'];

        // Outstanding fines
        $result = $this->db->query("SELECT SUM(amount) as total FROM fines WHERE status = 'pending'");
        $stats['outstanding_fines'] = $result->fetch_assoc()['total'] ?? 0;

        return $stats;
    }
}
?>
