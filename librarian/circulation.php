<?php
/**
 * Librarian: Book Circulation & Borrowing
 */
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'librarian') {
    header('Location: ../index.php');
    exit;
}

require_once '../config/config.php';
require_once '../classes/Book.php';
require_once '../classes/BorrowRecord.php';

$book = new Book();
$borrow = new BorrowRecord();

$search_query = $_GET['q'] ?? '';
$search_results = [];

if ($search_query) {
    $search_results = $book->searchBooks($search_query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Circulation - Librarian</title>
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
                <h3>Search Books</h3>
                <form method="GET" style="margin-bottom: 1.5rem;">
                    <div class="form-group">
                        <label for="search">Book Search</label>
                        <input type="text" id="search" name="q" placeholder="Title, ISBN, author..." value="<?php echo htmlspecialchars($search_query); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Search</button>
                </form>

                <h3>Operations</h3>
                <ul>
                    <li><a href="circulation.php" class="active">Borrow Books</a></li>
                    <li><a href="returns.php">Process Returns</a></li>
                    <li><a href="reservations.php">Manage Reservations</a></li>
                    <li><a href="fines.php">Collect Fines</a></li>
                </ul>
            </aside>

            <main class="main-content">
                <h1>Book Circulation Management</h1>

                <div class="card">
                    <h3 class="card-title">Search & Borrow</h3>
                    <p>Search for a book to process borrowing, or continue with a barcode scan.</p>
                </div>

                <?php if ($search_query && count($search_results) > 0): ?>
                <div class="card">
                    <h3 class="card-title">Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>ISBN</th>
                                <th>Author(s)</th>
                                <th>Available</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($search_results as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['title']); ?></td>
                                <td><?php echo htmlspecialchars($item['isbn']); ?></td>
                                <td><?php echo htmlspecialchars($item['authors'] ?? 'Unknown'); ?></td>
                                <td><?php echo htmlspecialchars($item['quantity_available']); ?>/<?php echo htmlspecialchars($item['quantity_total']); ?></td>
                                <td>
                                    <span class="badge <?php echo $item['status'] === 'available' ? 'badge-success' : 'badge-warning'; ?>">
                                        <?php echo ucfirst($item['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($item['quantity_available'] > 0): ?>
                                        <button class="btn btn-small btn-primary" onclick="selectBook(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['title']); ?>')">
                                            Select
                                        </button>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Not Available</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <div class="card">
                    <h3 class="card-title">Borrow Process</h3>
                    <div style="background: #f5f5f5; padding: 2rem; border-radius: 5px;">
                        <p><strong>Steps:</strong></p>
                        <ol style="margin-left: 1.5rem; margin-top: 1rem;">
                            <li>Search for the book above by title, ISBN, or author</li>
                            <li>Select the book from search results</li>
                            <li>Enter member ID or card number</li>
                            <li>Confirm borrow details</li>
                            <li>System generates receipt and updates due date</li>
                        </ol>
                    </div>
                </div>

                <div class="card">
                    <h3 class="card-title">Quick Actions</h3>
                    <div class="btn-group">
                        <a href="returns.php" class="btn btn-success">Process Returns</a>
                        <a href="reservations.php" class="btn btn-primary">View Reservations</a>
                        <a href="fines.php" class="btn btn-warning">Collect Fines</a>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 Library Management System. All rights reserved.</p>
    </footer>

    <script src="../assets/js/main.js"></script>
    <script>
        function selectBook(bookId, title) {
            // Store selected book and show user input
            sessionStorage.setItem('selected_book_id', bookId);
            sessionStorage.setItem('selected_book_title', title);
            
            const memberId = prompt('Enter member ID or card number:');
            if (memberId) {
                window.location.href = 'borrow-process.php?book_id=' + bookId + '&member_id=' + memberId;
            }
        }
    </script>
</body>
</html>
