<?php
/**
 * Member: Book Details & Borrow
 */
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once '../config/config.php';
require_once '../classes/Book.php';
require_once '../classes/Reservation.php';
require_once '../classes/BorrowRecord.php';

$book_id = $_GET['id'] ?? null;

if (!$book_id) {
    header('Location: catalog.php');
    exit;
}

$book = new Book();
$book_data = $book->getBook($book_id);

if (!$book_data) {
    header('Location: catalog.php');
    exit;
}

$reservation = new Reservation();
$borrow = new BorrowRecord();

$user_reservations = $reservation->getUserReservations($_SESSION['user_id']);
$has_reserved = false;

foreach ($user_reservations as $res) {
    if ($res['book_id'] == $book_id) {
        $has_reserved = true;
        break;
    }
}

$queue = $reservation->getReservationQueue($book_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book_data['title']); ?> - Library Management</title>
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
                <div class="card">
                    <h3 class="card-title">Actions</h3>
                    <?php if ($book_data['quantity_available'] > 0): ?>
                        <p>This book is <span class="badge badge-success">Available</span></p>
                        <a href="borrow.php?id=<?php echo $book_data['id']; ?>" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Borrow Book</a>
                    <?php else: ?>
                        <p>This book is not available right now.</p>
                        <?php if (!$has_reserved): ?>
                            <a href="reserve.php?id=<?php echo $book_data['id']; ?>" class="btn btn-success" style="width: 100%; margin-top: 1rem;">Reserve Book</a>
                        <?php else: ?>
                            <p><span class="badge badge-warning">Already Reserved</span></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <h3 class="card-title">Book Info</h3>
                    <p><strong>ISBN:</strong> <?php echo htmlspecialchars($book_data['isbn']); ?></p>
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($book_data['category_name']); ?></p>
                    <p><strong>Published:</strong> <?php echo htmlspecialchars($book_data['publication_year']); ?></p>
                    <p><strong>Edition:</strong> <?php echo htmlspecialchars($book_data['edition']); ?></p>
                    <p><strong>Pages:</strong> <?php echo htmlspecialchars($book_data['pages']); ?></p>
                    <p><strong>Language:</strong> <?php echo htmlspecialchars($book_data['language']); ?></p>
                </div>
            </aside>

            <main class="main-content">
                <a href="catalog.php" style="color: #3498db; text-decoration: none;">&larr; Back to Catalog</a>

                <div class="card">
                    <h1><?php echo htmlspecialchars($book_data['title']); ?></h1>
                    
                    <table style="margin-top: 2rem;">
                        <tr>
                            <td><strong>Author(s):</strong></td>
                            <td><?php echo htmlspecialchars($book_data['authors'] ?? 'Unknown'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Publisher:</strong></td>
                            <td><?php echo htmlspecialchars($book_data['publisher_name'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>ISBN:</strong></td>
                            <td><?php echo htmlspecialchars($book_data['isbn']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Category:</strong></td>
                            <td><?php echo htmlspecialchars($book_data['category_name']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="badge <?php echo $book_data['status'] === 'available' ? 'badge-success' : 'badge-warning'; ?>">
                                    <?php echo ucfirst($book_data['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Quantity Available:</strong></td>
                            <td><?php echo htmlspecialchars($book_data['quantity_available']); ?>/<?php echo htmlspecialchars($book_data['quantity_total']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Book Value:</strong></td>
                            <td>$<?php echo number_format($book_data['book_value'], 2); ?></td>
                        </tr>
                    </table>

                    <h3 class="card-title" style="margin-top: 2rem;">Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($book_data['description'] ?? 'No description available')); ?></p>
                </div>

                <?php if (count($queue) > 0): ?>
                <div class="card">
                    <h3 class="card-title">Reservation Queue</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Position</th>
                                <th>User</th>
                                <th>Reserved On</th>
                                <th>Hold Until</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($queue as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['position']); ?></td>
                                <td><?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($item['reservation_date'])); ?></td>
                                <td><?php echo htmlspecialchars($item['hold_until_date']); ?></td>
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
</body>
</html>
