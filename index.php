<?php
ob_start();

// Session configuration MUST be before session_start()
ini_set('session.gc_maxlifetime', 3600);

session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    ob_end_clean();
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System - Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 5rem auto;
            padding: 2rem;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .logo-container {
            text-align: center;
            margin-bottom: 1rem;
        }

        .logo-container img {
            max-width: 150px;
            height: auto;
        }

        .login-title {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 2rem;
        }

        .login-form {
            display: flex;
            flex-direction: column;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: #666;
        }

        .login-footer a {
            color: #3498db;
            text-decoration: none;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        .admin-link {
            text-align: center;
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
    </style>
</head>
<body style="background-color: #cccccc; min-height: 100vh; display: flex; flex-direction: column;">
    <div class="login-container">
        <div class="logo-container">
            <img src="assets/images/Midwest-logo.png" alt="Library Management System Logo">
        </div>

        <?php
        $alert_message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once 'config/config.php';
            require_once 'classes/User.php';
            require_once 'classes/Database.php';

            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            try {
                $user = new User();
                $result = $user->login($username, $password);

                if ($result['success']) {
                    if ($result['role'] === 'admin') {
                        header('Location: admin/dashboard.php');
                        exit;
                    } else {
                        header('Location: dashboard.php');
                        exit;
                    }
                } else {
                    $alert_message = $result['message'];
                }
            } catch (Exception $e) {
                $alert_message = 'Error: ' . $e->getMessage();
            }
        }
        ?>

        <?php if ($alert_message): ?>
            <div class="alert alert-danger" style="margin: 0 0 1rem 0; padding: 0.75rem 1rem;">
                <?php echo htmlspecialchars($alert_message); ?>
            </div>
        <?php endif; ?>

        <h1 class="login-title">Library Management System</h1>

        <form method="POST" class="login-form">
            <div class="form-group">
                <label for="username">Username or Email</label>
                <input type="text" id="username" name="username" required placeholder="Enter your username or email">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter your password">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Login</button>
        </form>

        <div class="login-footer">
            <p>Don't have an account? <a href="register.php">Create one here</a></p>
            <div class="admin-link">
                <p>🔐 Are you an admin? <a href="admin/login.php">Admin Portal</a></p>
            </div>
        </div>
    </div>

    <footer style="margin-top: auto; padding-bottom: 2rem;">
        <p>&copy; 2026 Library Management System. All rights reserved.</p>
    </footer>
</body>
</html>
