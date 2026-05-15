<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Library Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
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
            color: #2c3e50;
            margin-bottom: 2rem;
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

        .admin-link {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #ddd;
            font-size: 0.9rem;
        }

        .admin-link a {
            color: #c0392b;
            font-weight: bold;
        }

        .admin-link a:hover {
            text-decoration: underline;
        }
            color: #666;
        }

        .register-footer a {
            color: #3498db;
            text-decoration: none;
        }
    </style>
</head>
<body style="background-color: #cccccc;">
    <div class="register-container">
        <h1 class="register-title">Create Account</h1>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once 'config/config.php';
            require_once 'classes/User.php';

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

            if (empty($errors)) {
                $user = new User();
                $result = $user->register($first_name, $last_name, $email, $username, $password, $phone);

                if ($result['success']) {
                    echo '<div class="alert alert-success">' . $result['message'] . ' <a href="index.php">Login here</a></div>';
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

            <button type="submit" class="btn btn-primary" style="width: 100%;">Register</button>
        </form>

        <div class="register-footer">
            <p>Already have an account? <a href="index.php">Login here</a></p>
            <div class="admin-link">
                <p>🔐 Are you an admin? <a href="admin/register.php">Admin Portal</a></p>
            </div>
        </div>
    </div>
</body>
</html>
