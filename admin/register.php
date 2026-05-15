<?php
session_start();

// Check if user is logged in as admin
$is_admin = isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin';
$is_first_user = false;

// Check if this is the first user registration
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    require_once '../config/config.php';
    require_once '../classes/Database.php';
    
    $db = Database::getInstance()->getConnection();
    $count_stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
    $count_stmt->execute();
    $count_result = $count_stmt->get_result()->fetch_assoc();
    $is_first_user = $count_result['count'] == 0;
}

// Redirect if already logged in as admin
if ($is_admin) {
    header('Location: dashboard.php');
    exit;
}

// Redirect if not admin and not first user scenario
if (!$is_admin && !$is_first_user) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration - Library Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .register-container {
            max-width: 500px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .register-title {
            text-align: center;
            color: #c0392b;
            margin-bottom: 1rem;
        }

        .admin-badge {
            text-align: center;
            background-color: #c0392b;
            color: white;
            padding: 0.5rem;
            border-radius: 3px;
            margin-bottom: 1.5rem;
            font-weight: bold;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .register-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: #666;
        }

        .register-footer a {
            color: #3498db;
            text-decoration: none;
        }

        .register-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body style="background-color: #cccccc;">
    <div class="register-container">
        <h1 class="register-title">🔐 Create Admin Account</h1>
        <div class="admin-badge">Administrator Registration</div>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once '../config/config.php';
            require_once '../classes/User.php';

            $first_name = $_POST['first_name'] ?? '';
            $last_name = $_POST['last_name'] ?? '';
            $email = $_POST['email'] ?? '';
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $phone = $_POST['phone'] ?? '';

            // Validation
            $errors = [];

            if (empty($first_name) || empty($last_name)) {
                $errors[] = 'Name fields are required';
            }

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Valid email is required';
            }

            if (empty($username) || strlen($username) < 3) {
                $errors[] = 'Username must be at least 3 characters';
            }

            if (empty($password) || strlen($password) < 8) {
                $errors[] = 'Password must be at least 8 characters';
            }

            // Check authorization: either first user or logged in admin
            if (!$is_first_user && !$is_admin) {
                $errors[] = 'Unauthorized: Admin credentials required';
            }

            if (empty($errors)) {
                $user = new User();
                $result = $user->registerAdmin($first_name, $last_name, $email, $username, $password, $phone);

                if ($result['success']) {
                    echo '<div class="alert alert-success">' . $result['message'] . ' <a href="login.php">Login here</a></div>';
                } else {
                    echo '<div class="alert alert-danger">' . $result['message'] . '</div>';
                }
            } else {
                foreach ($errors as $error) {
                    echo '<div class="alert alert-danger">' . $error . '</div>';
                }
            }
        }
        ?>

        <form method="POST" class="login-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password (min 8 characters)</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone (Optional)</label>
                <input type="text" id="phone" name="phone">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Create Admin Account</button>
        </form>

        <div class="register-footer">
            <p>Already have admin credentials? <a href="login.php">Login here</a></p>
            <p style="margin-top: 1rem;"><a href="../index.php">← Back to User Login</a></p>
        </div>
    </div>
</body>
</html>
