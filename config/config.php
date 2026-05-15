<?php
/**
 * Library Management System - Configuration File
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'library_management');

// Site Configuration
define('SITE_URL', 'http://localhost/library-management/');
define('SITE_NAME', 'Library Management System');
define('ADMIN_EMAIL', 'admin@library.local');

// Logo Configuration (change image paths here to update across all pages)
define('LOGO_MEMBER', 'assets/images/Midwest-logo.png');
define('LOGO_LIBRARIAN', 'assets/images/Midwest-logo.png');
define('LOGO_ADMIN', 'assets/images/Midwest-logo.png');

// Security
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('PASSWORD_MIN_LENGTH', 8);

// Fine & Fee Configuration
define('DAILY_FINE_RATE', 5); // Per day in currency
define('MAX_BORROW_DURATION', 14); // Days
define('MAX_BOOKS_PER_USER', 5);
define('RESERVATION_HOLD_PERIOD', 7); // Days
define('DAMAGE_FEE', 50);
define('LOST_BOOK_FEE_PERCENT', 200); // Percentage of book value

// File Upload Configuration
define('MAX_UPLOAD_SIZE', 5242880); // 5MB in bytes
define('UPLOAD_DIR', 'assets/uploads/');

// Pagination
define('ITEMS_PER_PAGE', 10);

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'logs/error.log');

// Timezone
date_default_timezone_set('UTC');

// Session Configuration
if (!session_id()) {
    ini_set('session.gc_maxlifetime', SESSION_TIMEOUT);
}
?>
