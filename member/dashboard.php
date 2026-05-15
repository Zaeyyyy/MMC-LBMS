<?php
// Session configuration MUST be before session_start()
ini_set('session.gc_maxlifetime', 3600);

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header('Location: ../index.php');
    exit;
}

require_once '../config/config.php';
require_once '../classes/User.php';
require_once '../classes/BorrowRecord.php';
require_once '../classes/Fine.php';
require_once '../classes/Reservation.php';

$user = new User();
$user_data = $user->getProfile($_SESSION['user_id']);
$borrow = new BorrowRecord();
$fine = new Fine();
$reservation = new Reservation();

$borrowed_books = $borrow->getUserBorrowedBooks($_SESSION['user_id']);
$pending_fines = $fine->getUserFines($_SESSION['user_id']);
$reservations = $reservation->getUserReservations($_SESSION['user_id']);
$total_fines = $fine->getUserTotalFines($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Library Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
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
                <h3>My Menu</h3>
                <ul>
                    <li><a href="dashboard.php" class="active" id="menu-dashboard">Dashboard</a></li>
                    <li><a href="catalog.php" id="menu-catalog">Browse Books</a></li>
                    <li><a href="my-books.php" id="menu-mybooks">My Borrowed Books</a></li>
                    <li><a href="my-reservations.php" id="menu-reservations">My Reservations</a></li>
                    <li><a href="my-fines.php" id="menu-fines">My Fines</a></li>
                    <li><a href="profile.php" id="menu-profile">Profile</a></li>
                </ul>
            </aside>

            <main class="main-content">
                <h1>Welcome, <?php echo htmlspecialchars($user_data['first_name']); ?>!</h1>

                <div class="card-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count($borrowed_books); ?></div>
                        <div class="stat-label">Books Borrowed</div>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #27ae60, #229954);">
                        <div class="stat-number"><?php echo count($reservations); ?></div>
                        <div class="stat-label">Books Reserved</div>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                        <div class="stat-number"><?php echo count($pending_fines); ?></div>
                        <div class="stat-label">Pending Fines</div>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #8e44ad, #7d3c98);">
                        <div class="stat-number">$<?php echo number_format($total_fines, 2); ?></div>
                        <div class="stat-label">Total Due</div>
                    </div>
                </div>

                <div class="card">
                    <h3 class="card-title">My Borrowed Books (<?php echo count($borrowed_books); ?>)</h3>
                    <?php if (count($borrowed_books) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>ISBN</th>
                                <th>Due Date</th>
                                <th>Days Left</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($borrowed_books, 0, 5) as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['title']); ?></td>
                                <td><?php echo htmlspecialchars($item['isbn']); ?></td>
                                <td><?php echo htmlspecialchars($item['due_date']); ?></td>
                                <td>
                                    <span class="badge <?php echo $item['days_left'] <= 3 ? 'badge-warning' : 'badge-success'; ?>">
                                        <?php echo $item['days_left']; ?> days
                                    </span>
                                </td>
                                <td>
                                    <a href="renew-book.php?id=<?php echo $item['id']; ?>" class="btn btn-small btn-primary">Renew</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <a href="my-books.php" class="btn btn-primary">View All</a>
                    <?php else: ?>
                    <p>You haven't borrowed any books yet. <a href="catalog.php">Browse our catalog</a></p>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <h3 class="card-title">My Outstanding Fines (<?php echo count($pending_fines); ?>)</h3>
                    <?php if (count($pending_fines) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Reason</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($pending_fines, 0, 3) as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['reason']); ?></td>
                                <td>$<?php echo number_format($item['amount'], 2); ?></td>
                                <td><span class="badge badge-warning"><?php echo ucfirst($item['status']); ?></span></td>
                                <td>
                                    <a href="pay-fine.php?id=<?php echo $item['id']; ?>" class="btn btn-small btn-success">Pay</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <a href="my-fines.php" class="btn btn-primary">View All Fines</a>
                    <?php else: ?>
                    <p>No outstanding fines. Good job!</p>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <h3 class="card-title">Quick Actions</h3>
                    <div class="btn-group">
                        <a href="catalog.php" class="btn btn-primary">Browse Catalog</a>
                        <a href="my-fines.php" class="btn btn-warning">Pay Fines</a>
                        <a href="profile.php" class="btn btn-primary">Update Profile</a>
                    </div>
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
