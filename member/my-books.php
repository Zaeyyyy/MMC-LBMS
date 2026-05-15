<?php
/**
 * Member: My Borrowed Books
 */
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header('Location: ../index.php');
    exit;
}

require_once '../config/config.php';
require_once '../classes/BorrowRecord.php';

$borrow = new BorrowRecord();
$borrowed_books = $borrow->getUserBorrowedBooks($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Books - Library Management</title>
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
                    <li><a href="my-books.php" class="active" id="menu-mybooks">My Borrowed Books</a></li>
                    <li><a href="my-reservations.php" id="menu-reservations">My Reservations</a></li>
                    <li><a href="my-fines.php" id="menu-fines">My Fines</a></li>
                    <li><a href="profile.php" id="menu-profile">Profile</a></li>
                </ul>
            </aside>

            <main class="main-content">
                <h1>My Borrowed Books</h1>

                <?php if (count($borrowed_books) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>ISBN</th>
                            <th>Borrowed On</th>
                            <th>Due Date</th>
                            <th>Days Left</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($borrowed_books as $book): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($book['title']); ?></td>
                            <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($book['borrow_date'])); ?></td>
                            <td><?php echo htmlspecialchars($book['due_date']); ?></td>
                            <td>
                                <?php if ($book['days_left'] < 0): ?>
                                    <span class="badge badge-danger">Overdue <?php echo abs($book['days_left']); ?> days</span>
                                <?php elseif ($book['days_left'] <= 3): ?>
                                    <span class="badge badge-warning"><?php echo $book['days_left']; ?> days</span>
                                <?php else: ?>
                                    <span class="badge badge-success"><?php echo $book['days_left']; ?> days</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge badge-info"><?php echo ucfirst($book['status']); ?></span></td>
                            <td>
                                <a href="renew-book.php?id=<?php echo $book['id']; ?>" class="btn btn-small btn-primary">Renew</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="card">
                    <p>You haven't borrowed any books yet.</p>
                    <a href="catalog.php" class="btn btn-primary">Browse Catalog</a>
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
