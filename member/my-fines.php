<?php
/**
 * Member: My Fines
 */
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header('Location: ../index.php');
    exit;
}

require_once '../config/config.php';
require_once '../classes/Fine.php';

$fine = new Fine();
$fines = $fine->getUserFines($_SESSION['user_id']);
$total = $fine->getUserTotalFines($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Fines - Library Management</title>
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
                    <li><a href="dashboard.php" id="menu-dashboard">Dashboard</a></li>
                    <li><a href="catalog.php" id="menu-catalog">Browse Books</a></li>
                    <li><a href="my-books.php" id="menu-mybooks">My Borrowed Books</a></li>
                    <li><a href="my-reservations.php" id="menu-reservations">My Reservations</a></li>
                    <li><a href="my-fines.php" class="active" id="menu-fines">My Fines</a></li>
                    <li><a href="profile.php" id="menu-profile">Profile</a></li>
                </ul>
            </aside>

            <main class="main-content">
                <h1>My Fines</h1>

                <?php if (count($fines) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fines as $f): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($f['title']); ?></td>
                            <td><?php echo formatCurrency($f['amount']); ?></td>
                            <td><?php echo htmlspecialchars($f['due_date']); ?></td>
                            <td><span class="badge badge-<?php echo $f['status'] === 'paid' ? 'success' : 'warning'; ?>"><?php echo ucfirst($f['status']); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="card">
                    <h3>Total Due: <?php echo formatCurrency($total); ?></h3>
                </div>
                <?php else: ?>
                <div class="card">
                    <p>You have no outstanding fines.</p>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 Library Management System. All rights reserved.</p>
    </footer>

    <script src="../assets/js/main.js"></script>
</body>
</html>