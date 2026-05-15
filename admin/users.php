<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

require_once '../config/config.php';
require_once '../classes/User.php';

$user = new User();
$action = $_GET['action'] ?? 'list';
$users = $user->getAllUsers(ITEMS_PER_PAGE, 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $address = $_POST['address'] ?? '';

        $result = $user->register($first_name, $last_name, $email, $username, $password, $phone, $address);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'danger';
    }
}

$message = $message ?? '';
$message_type = $message_type ?? 'info';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo"><img src="<?php echo '../' . LOGO_ADMIN; ?>" alt="Logo"></div>
            <button class="nav-toggle">&#9776;</button>
            <ul class="nav-menu">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="books.php">Books</a></li>
                <li><a href="reports.php">Reports</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="dashboard">
            <aside class="sidebar">
                <h3>Admin Menu</h3>
                <ul>
                    <li><a href="dashboard.php" id="menu-dashboard">Dashboard</a></li>
                    <li><a href="users.php" class="active" id="menu-users">User Management</a></li>
                    <li><a href="books.php" id="menu-books">Book Management</a></li>
                    <li><a href="fines.php" id="menu-fines">Fines & Payments</a></li>
                    <li><a href="reports.php" id="menu-reports">Reports</a></li>
                    <li><a href="inventory.php" id="menu-inventory">Inventory</a></li>
                    <li><a href="settings.php" id="menu-settings">System Settings</a></li>
                </ul>
            </aside>

            <main class="main-content">
                <h1>User Management</h1>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="btn-group">
                        <a href="users.php?action=add" class="btn btn-primary">+ Add New User</a>
                    </div>
                </div>

                <?php if ($action === 'add'): ?>
                <div class="card">
                    <h3 class="card-title">Add New User</h3>
                    <form method="POST" onsubmit="return validateForm('addUserForm')">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
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
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="text" id="phone" name="phone">
                            </div>
                            <div class="form-group">
                                <label for="address">Address</label>
                                <input type="text" id="address" name="address">
                            </div>
                        </div>

                        <div class="btn-group">
                            <button type="submit" class="btn btn-success">Add User</button>
                            <a href="users.php" class="btn btn-primary">Cancel</a>
                        </div>
                    </form>
                </div>
                <?php else: ?>

                <div class="card">
                    <h3 class="card-title">All Users</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td><?php echo htmlspecialchars($u['username']); ?></td>
                                <td><span class="badge badge-info"><?php echo ucfirst($u['role']); ?></span></td>
                                <td>
                                    <span class="badge <?php echo $u['status'] === 'active' ? 'badge-success' : 'badge-danger'; ?>">
                                        <?php echo ucfirst($u['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($u['date_registered'])); ?></td>
                                <td>
                                    <a href="user-edit.php?id=<?php echo $u['id']; ?>" class="btn btn-small btn-primary">Edit</a>
                                    <a href="javascript:void(0);" onclick="suspendUser(<?php echo $u['id']; ?>)" class="btn btn-small <?php echo $u['status'] === 'active' ? 'btn-warning' : 'btn-success'; ?>">
                                        <?php echo $u['status'] === 'active' ? 'Suspend' : 'Unsuspend'; ?>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php endif; ?>
            </main>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 Library Management System. All rights reserved.</p>
    </footer>

    <script src="../assets/js/main.js"></script>
    <script>
        function suspendUser(userId) {
            if (confirm('Are you sure you want to suspend/unsuspend this user?')) {
                // AJAX call to suspend/unsuspend user
                makeRequest('../api/suspend-user.php?id=' + userId, 'POST', null, function(result) {
                    if (result.success) {
                        showAlert('User status updated successfully', 'success');
                        location.reload();
                    } else {
                        showAlert(result.message || 'Failed to update user status', 'danger');
                    }
                });
            }
        }
    </script>
</body>
</html>
