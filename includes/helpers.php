<?php
/**
 * Common Helper Functions
 */

/**
 * Sanitize input
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Format currency
 */
function format_currency($amount) {
    return '$' . number_format($amount, 2);
}

/**
 * Format date
 */
function format_date($date) {
    return date('M d, Y', strtotime($date));
}

/**
 * Format datetime
 */
function format_datetime($datetime) {
    return date('M d, Y H:i', strtotime($datetime));
}

/**
 * Get days until date
 */
function days_until($date) {
    $now = strtotime(date('Y-m-d'));
    $target = strtotime($date);
    $days = floor(($target - $now) / 86400);
    return $days;
}

/**
 * Check if user is admin
 */
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if user is librarian
 */
function is_librarian() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'librarian';
}

/**
 * Check if user is member
 */
function is_member() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'member';
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Redirect with message
 */
function redirect_with_message($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header("Location: $url");
    exit;
}

/**
 * Get and clear flash message
 */
function get_flash_message() {
    $message = $_SESSION['flash_message'] ?? null;
    $type = $_SESSION['flash_type'] ?? 'info';
    
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
    
    return ['message' => $message, 'type' => $type];
}

/**
 * Generate random token
 */
function generate_token($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Log action
 */
function log_action($user_id, $action, $description = null, $table_name = null, $record_id = null, $old_value = null, $new_value = null) {
    global $db;
    
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    
    $stmt = $db->prepare("
        INSERT INTO system_logs (user_id, action, description, table_name, record_id, old_value, new_value, ip_address)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param("isssdsss", $user_id, $action, $description, $table_name, $record_id, $old_value, $new_value, $ip_address);
    return $stmt->execute();
}

/**
 * Get status badge HTML
 */
function get_status_badge($status) {
    $badges = [
        'active' => 'badge-success',
        'suspended' => 'badge-danger',
        'available' => 'badge-success',
        'borrowed' => 'badge-warning',
        'overdue' => 'badge-danger',
        'pending' => 'badge-info',
        'paid' => 'badge-success',
        'lost' => 'badge-danger',
        'damaged' => 'badge-warning'
    ];
    
    $badge_class = $badges[$status] ?? 'badge-info';
    return "<span class='badge {$badge_class}'>" . ucfirst($status) . "</span>";
}

/**
 * Pagination helper
 */
function get_pagination($current_page, $total_items, $items_per_page = ITEMS_PER_PAGE) {
    $total_pages = ceil($total_items / $items_per_page);
    
    return [
        'current' => $current_page,
        'total' => $total_pages,
        'has_prev' => $current_page > 1,
        'has_next' => $current_page < $total_pages,
        'start' => ($current_page - 1) * $items_per_page,
        'limit' => $items_per_page
    ];
}

/**
 * Check permissions
 */
function has_permission($role, $permission) {
    $permissions = [
        'admin' => ['all'],
        'librarian' => ['view_books', 'borrow_books', 'accept_returns', 'manage_fines'],
        'member' => ['view_books', 'borrow_books', 'view_own_records']
    ];
    
    $role_permissions = $permissions[$role] ?? [];
    return in_array('all', $role_permissions) || in_array($permission, $role_permissions);
}

/**
 * Send notification
 */
function send_notification($user_id, $title, $message, $type = 'system') {
    global $db;
    
    $stmt = $db->prepare("
        INSERT INTO notifications (user_id, title, message, notification_type)
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->bind_param("isss", $user_id, $title, $message, $type);
    return $stmt->execute();
}

/**
 * Export array to CSV
 */
function export_to_csv($filename, $data) {
    header('Content-Type: text/csv');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    
    $output = fopen('php://output', 'w');
    
    if (!empty($data)) {
        // Write header
        fputcsv($output, array_keys($data[0]));
        
        // Write rows
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }
    
    fclose($output);
    exit;
}
?>
