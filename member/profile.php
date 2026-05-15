<?php
/**
 * Member: Profile Management
 */
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header('Location: ../index.php');
    exit;
}

require_once '../config/config.php';
require_once '../classes/User.php';

$user = new User();
$user_data = $user->getProfile($_SESSION['user_id']);

$message = '';
$message_type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $address = $_POST['address'] ?? '';

        $result = $user->updateProfile($_SESSION['user_id'], $first_name, $last_name, $phone, $address);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'danger';

        if ($result['success']) {
            $user_data = $user->getProfile($_SESSION['user_id']);
        }
    } elseif ($action === 'change_password') {
        $old_password = $_POST['old_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        $result = $user->changePassword($_SESSION['user_id'], $old_password, $new_password, $confirm_password);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Library Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="member-profile">
    <header>
        <nav>
            <div class="logo"><img src="<?php echo '../' . LOGO_MEMBER; ?>" alt="Logo"></div>
            <button class="nav-toggle">&#9776;</button>
            <ul class="nav-menu">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="catalog.php">Browse Books</a></li>
                <li><a href="my-books.php">My Books</a></li>
                <li><a href="my-reservations.php">My Reservations</a></li>
                <li><a href="my-fines.php">My Fines</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="dashboard">
            <aside class="sidebar">
                <h3>Profile Menu</h3>
                <ul>
                    <li><a href="#edit-profile" class="active">Edit Profile</a></li>
                    <li><a href="#change-password">Change Password</a></li>
                </ul>
            </aside>

            <main class="main-content">
                <h1>My Profile</h1>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <div class="card" id="edit-profile">
                    <h3 class="card-title">Edit Profile</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user_data['first_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user_data['last_name']); ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email (Read-only)</label>
                            <input type="email" id="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" disabled>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea id="address" name="address"><?php echo htmlspecialchars($user_data['address'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="member_since">Member Since</label>
                            <input type="text" id="member_since" value="<?php echo htmlspecialchars($user_data['date_registered']); ?>" disabled>
                        </div>

                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>

                <div class="card" id="change-password">
                    <h3 class="card-title">Change Password</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">

                        <div class="form-group">
                            <label for="old_password">Current Password</label>
                            <input type="password" id="old_password" name="old_password" required>
                        </div>

                        <div class="form-group">
                            <label for="new_password">New Password (min 8 characters)</label>
                            <input type="password" id="new_password" name="new_password" required minlength="8">
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                        </div>

                        <button type="submit" class="btn btn-primary">Change Password</button>
                    </form>
                </div>

                <div class="card">
                    <h3 class="card-title">Account Information</h3>
                    <table>
                        <tr>
                            <td><strong>Username:</strong></td>
                            <td><?php echo htmlspecialchars($user_data['username']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td><?php echo htmlspecialchars($user_data['email']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Account Status:</strong></td>
                            <td>
                                <span class="badge <?php echo $user_data['status'] === 'active' ? 'badge-success' : 'badge-danger'; ?>">
                                    <?php echo ucfirst($user_data['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Member Since:</strong></td>
                            <td><?php echo htmlspecialchars($user_data['date_registered']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Last Login:</strong></td>
                            <td><?php echo htmlspecialchars($user_data['last_login'] ?? 'Never logged in'); ?></td>
                        </tr>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 Library Management System. All rights reserved.</p>
    </footer>

    <script src="../assets/js/main.js"></script>
</body>
</html>
