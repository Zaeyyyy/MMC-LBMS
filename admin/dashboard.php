<?php
// Session configuration MUST be before session_start()
ini_set('session.gc_maxlifetime', 3600);

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

require_once '../config/config.php';
require_once '../classes/User.php';
require_once '../classes/Report.php';

$user = new User();
$user_data = $user->getProfile($_SESSION['user_id']);
$report = new Report();
$stats = $report->getDashboardStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Library Management</title>
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
                <li><a href="settings.php">Settings</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="dashboard">
            <aside class="sidebar">
                <h3>Admin Menu</h3>
                <ul>
                    <li><a href="dashboard.php" class="active" id="menu-dashboard">Dashboard</a></li>
                    <li><a href="users.php" id="menu-users">User Management</a></li>
                    <li><a href="books.php" id="menu-books">Book Management</a></li>
                    <li><a href="fines.php" id="menu-fines">Fines & Payments</a></li>
                    <li><a href="reports.php" id="menu-reports">Reports</a></li>
                    <li><a href="inventory.php" id="menu-inventory">Inventory</a></li>
                    <li><a href="settings.php" id="menu-settings">System Settings</a></li>
                    <li><a href="logs.php" id="menu-logs">Audit Logs</a></li>
                </ul>
            </aside>

            <main class="main-content">
                <h1>Admin Dashboard</h1>

                <div class="card">
                    <p><strong>Welcome, <?php echo htmlspecialchars($user_data['first_name']); ?></strong></p>
                </div>

                <div class="card-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['total_users']; ?></div>
                        <div class="stat-label">Total Users</div>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #27ae60, #229954);">
                        <div class="stat-number"><?php echo $stats['total_books']; ?></div>
                        <div class="stat-label">Total Books</div>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
                        <div class="stat-number"><?php echo $stats['available_books']; ?></div>
                        <div class="stat-label">Available Books</div>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                        <div class="stat-number"><?php echo $stats['borrowed_books']; ?></div>
                        <div class="stat-label">Books Borrowed</div>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #8e44ad, #7d3c98);">
                        <div class="stat-number"><?php echo $stats['overdue_books']; ?></div>
                        <div class="stat-label">Overdue Books</div>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #16a085, #138d75);">
                        <div class="stat-number"><?php echo number_format($stats['outstanding_fines'], 2); ?></div>
                        <div class="stat-label">Outstanding Fines</div>
                    </div>
                </div>

                <div class="card">
                    <h3 class="card-title">Quick Actions</h3>
                    <div class="btn-group">
                        <a href="users.php?action=add" class="btn btn-primary">Add User</a>
                        <a href="books.php?action=add" class="btn btn-success">Add Book</a>
                        <a href="reports.php" class="btn btn-warning">View Reports</a>
                        <a href="settings.php" class="btn btn-primary">System Settings</a>
                    </div>
                </div>

                <div class="card">
                    <h3 class="card-title">System Information</h3>
                    <table>
                        <tr>
                            <td><strong>Admin Name:</strong></td>
                            <td><?php echo htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td><?php echo htmlspecialchars($user_data['email']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Member Since:</strong></td>
                            <td><?php echo htmlspecialchars($user_data['date_registered']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Last Login:</strong></td>
                            <td><?php echo htmlspecialchars($user_data['last_login'] ?? 'Never'); ?></td>
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
