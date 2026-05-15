<?php
/**
 * User Management Class
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Database.php';

class User {
    private $db;
    private $id;
    private $username;
    private $email;
    private $role;
    private $status;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * User Registration
     */
    public function register($firstname, $lastname, $email, $username, $password, $phone = null, $address = null) {
        // Validation
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            return ['success' => false, 'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'];
        }

        // Check if user exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return ['success' => false, 'message' => 'Email or username already exists'];
        }

        // Check if this is the first user - they become admin
        $count_stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users");
        $count_stmt->execute();
        $count_result = $count_stmt->get_result()->fetch_assoc();
        $is_first_user = $count_result['count'] == 0;
        $role = $is_first_user ? 'admin' : 'member';

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert user
        $stmt = $this->db->prepare("
            INSERT INTO users (first_name, last_name, email, username, password, phone, address, role, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
        ");

        $stmt->bind_param("ssssssss", $firstname, $lastname, $email, $username, $hashed_password, $phone, $address, $role);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Registration successful', 'user_id' => $this->db->insert_id];
        } else {
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }

    /**
     * User Login (Members/Librarians only - NOT for Admin)
     */
    public function login($username, $password) {
        $stmt = $this->db->prepare("SELECT id, username, email, password, role, status FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            return ['success' => false, 'message' => 'User not found'];
        }

        $user = $result->fetch_assoc();


        if ($user['status'] === 'suspended') {
            return ['success' => false, 'message' => 'Account is suspended'];
        }

        if (password_verify($password, $user['password'])) {
            // Update last login
            $update_stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $update_stmt->bind_param("i", $user['id']);
            $update_stmt->execute();

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            return ['success' => true, 'message' => 'Login successful', 'role' => $user['role']];
        } else {
            return ['success' => false, 'message' => 'Invalid password'];
        }
    }

    /**
     * Admin Login (Admins only)
     */
    public function loginAdmin($username, $password) {
        $stmt = $this->db->prepare("SELECT id, username, email, password, role, status FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            return ['success' => false, 'message' => 'Admin account not found'];
        }

        $user = $result->fetch_assoc();

        // Only allow admin accounts
        if ($user['role'] !== 'admin') {
            return ['success' => false, 'message' => 'Invalid credentials. Admin access only.'];
        }

        if ($user['status'] === 'suspended') {
            return ['success' => false, 'message' => 'Account is suspended'];
        }

        if (password_verify($password, $user['password'])) {
            // Update last login
            $update_stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $update_stmt->bind_param("i", $user['id']);
            $update_stmt->execute();

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            return ['success' => true, 'message' => 'Admin login successful', 'role' => $user['role']];
        } else {
            return ['success' => false, 'message' => 'Invalid password'];
        }
    }

    /**
     * Get User Profile
     */
    public function getProfile($user_id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Update User Profile
     */
    public function updateProfile($user_id, $first_name, $last_name, $phone, $address) {
        $stmt = $this->db->prepare("
            UPDATE users SET first_name = ?, last_name = ?, phone = ?, address = ? WHERE id = ?
        ");
        $stmt->bind_param("ssssi", $first_name, $last_name, $phone, $address, $user_id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Profile updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Profile update failed'];
        }
    }

    /**
     * Change Password
     */
    public function changePassword($user_id, $old_password, $new_password, $confirm_password) {
        if ($new_password !== $confirm_password) {
            return ['success' => false, 'message' => 'Passwords do not match'];
        }

        if (strlen($new_password) < PASSWORD_MIN_LENGTH) {
            return ['success' => false, 'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'];
        }

        // Get current password
        $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if (!password_verify($old_password, $result['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }

        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $update_stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update_stmt->bind_param("si", $hashed_password, $user_id);

        if ($update_stmt->execute()) {
            return ['success' => true, 'message' => 'Password changed successfully'];
        } else {
            return ['success' => false, 'message' => 'Password change failed'];
        }
    }

    /**
     * Get All Users (Admin)
     */
    public function getAllUsers($limit = null, $offset = 0) {
        $sql = "SELECT id, username, email, first_name, last_name, role, status, date_registered FROM users ORDER BY date_registered DESC";

        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ii", $limit, $offset);
            $stmt->execute();
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        }

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Suspend/Activate User
     */
    public function updateUserStatus($user_id, $status) {
        $stmt = $this->db->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $user_id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'User status updated'];
        } else {
            return ['success' => false, 'message' => 'Update failed'];
        }
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Logout
     */
    public static function logout() {
        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }

    /**
     * Admin Registration
     */
    public function registerAdmin($firstname, $lastname, $email, $username, $password, $phone = null, $address = null) {
        // Validation
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            return ['success' => false, 'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'];
        }

        // Check if user exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return ['success' => false, 'message' => 'Email or username already exists'];
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $role = 'admin';

        // Insert user as admin
        $stmt = $this->db->prepare("
            INSERT INTO users (first_name, last_name, email, username, password, phone, address, role, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
        ");

        $stmt->bind_param("ssssssss", $firstname, $lastname, $email, $username, $hashed_password, $phone, $address, $role);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Admin account created successfully', 'user_id' => $this->db->insert_id];
        } else {
            return ['success' => false, 'message' => 'Admin registration failed'];
        }
    }

    /**
     * Check user role
     */
    public static function hasRole($role) {
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }
}
?>
