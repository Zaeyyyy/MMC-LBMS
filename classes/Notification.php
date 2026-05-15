<?php
/**
 * Notification Management Class
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Database.php';

class Notification {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications($user_id, $limit = 20) {
        $stmt = $this->db->prepare("
            SELECT * FROM notifications
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->bind_param("ii", $user_id, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get unread count
     */
    public function getUnreadCount($user_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = FALSE");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['count'];
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notification_id) {
        $stmt = $this->db->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ?");
        $stmt->bind_param("i", $notification_id);
        return $stmt->execute();
    }

    /**
     * Mark all as read
     */
    public function markAllAsRead($user_id) {
        $stmt = $this->db->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        return $stmt->execute();
    }

    /**
     * Delete notification
     */
    public function deleteNotification($notification_id) {
        $stmt = $this->db->prepare("DELETE FROM notifications WHERE id = ?");
        $stmt->bind_param("i", $notification_id);
        return $stmt->execute();
    }

    /**
     * Send overdue reminders
     */
    public function sendOverdueReminders() {
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $stmt = $this->db->prepare("
            SELECT DISTINCT br.user_id, b.title, br.due_date
            FROM borrow_records br
            JOIN books b ON br.book_id = b.id
            WHERE br.due_date = ? AND br.status = 'borrowed'
        ");
        $stmt->bind_param("s", $tomorrow);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $this->createNotification(
                $row['user_id'],
                'Due Date Reminder',
                'Your book "' . $row['title'] . '" is due on ' . $row['due_date'],
                'due_reminder'
            );
        }

        return true;
    }

    /**
     * Create notification
     */
    public function createNotification($user_id, $title, $message, $type = 'system') {
        $stmt = $this->db->prepare("
            INSERT INTO notifications (user_id, title, message, notification_type)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isss", $user_id, $title, $message, $type);
        return $stmt->execute();
    }
}
?>
