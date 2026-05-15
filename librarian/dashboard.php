<?php
// Session configuration MUST be before session_start()
ini_set('session.gc_maxlifetime', 3600);

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'librarian') {
    header('Location: ../index.php');
    exit;
}

require_once '../config/config.php';
require_once '../classes/User.php';
require_once '../classes/BorrowRecord.php';
require_once '../classes/Report.php';

$user = new User();
$user_data = $user->getProfile($_SESSION['user_id']);
$borrow = new BorrowRecord();
$overdue = $borrow->getOverdueBooks();
$report = new Report();
$stats = $report->getDashboardStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librarian Dashboard - Library Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo"><img src="<?php echo '../' . LOGO_LIBRARIAN; ?>" alt="Logo"></div>
            <button class="nav-toggle">&#9776;</button>
            <ul class="nav-menu">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="catalog.php">Catalog</a></li>
                <li><a href="circulation.php">Circulation</a></li>
                <li><a href="reservations.php">Reservations</a></li>
                <li><a href="fines.php">Fines</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="dashboard">
            <aside class="sidebar">
                <h3>Librarian Menu</h3>
                <ul>
                    <li><a href="dashboard.php" class="active" id="menu-dashboard">Dashboard</a></li>
                    <li><a href="catalog.php" id="menu-catalog">Book Catalog</a></li>
                    <li><a href="circulation.php" id="menu-circulation">Borrowing</a></li>
                    <li><a href="returns.php" id="menu-returns">Returns</a></li>
                    <li><a href="reservations.php" id="menu-reservations">Reservations</a></li>
                    <li><a href="fines.php" id="menu-fines">Fines & Fees</a></li>
                    <li><a href="inventory.php" id="menu-inventory">Inventory</a></li>
                </ul>
            </aside>

            <main class="main-content">
                <h1>Librarian Dashboard</h1>

                <div class="card">
                    <p><strong>Welcome, <?php echo htmlspecialchars($user_data['first_name']); ?></strong></p>
                </div>

                <div class="card-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['available_books']; ?></div>
                        <div class="stat-label">Available Books</div>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                        <div class="stat-number"><?php echo $stats['borrowed_books']; ?></div>
                        <div class="stat-label">Books Borrowed</div>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
                        <div class="stat-number"><?php echo count($overdue); ?></div>
                        <div class="stat-label">Overdue Books</div>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #8e44ad, #7d3c98);">
                        <div class="stat-number"><?php echo number_format($stats['outstanding_fines'], 2); ?></div>
                        <div class="stat-label">Outstanding Fines</div>
                    </div>
                </div>

                <div class="card">
                    <h3 class="card-title">Quick Actions</h3>
                    <div class="btn-group">
                        <a href="circulation.php" class="btn btn-primary">Borrow Book</a>
                        <a href="returns.php" class="btn btn-success">Return Book</a>
                        <a href="fines.php" class="btn btn-warning">Manage Fines</a>
                    </div>
                </div>

                <div class="card">
                    <h3 class="card-title">Overdue Books (<?php echo count($overdue); ?>)</h3>
                    <?php if (count($overdue) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Book Title</th>
                                <th>Due Date</th>
                                <th>Days Overdue</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($overdue, 0, 5) as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($item['title']); ?></td>
                                <td><?php echo htmlspecialchars($item['due_date']); ?></td>
                                <td><span class="badge badge-danger"><?php echo $item['days_overdue']; ?></span></td>
                                <td><?php echo htmlspecialchars($item['email']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p>No overdue books</p>
                    <?php endif; ?>
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
