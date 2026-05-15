<?php
/**
 * Admin: Book Management
 */
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

require_once '../config/config.php';
require_once '../classes/Book.php';
require_once '../classes/Metadata.php';

$book = new Book();
$category = new Category();
$publisher = new Publisher();
$author = new Author();

$action = $_GET['action'] ?? 'list';
$books = $book->getAllBooks(ITEMS_PER_PAGE, 0);
$categories = $category->getAllCategories();
$publishers = $publisher->getAllPublishers();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
    $title = $_POST['title'] ?? '';
    $isbn = $_POST['isbn'] ?? '';
    $publisher_id = $_POST['publisher_id'] ?? null;
    $category_id = $_POST['category_id'] ?? '';
    $description = $_POST['description'] ?? '';
    $year = $_POST['year'] ?? null;
    $edition = $_POST['edition'] ?? 1;
    $pages = $_POST['pages'] ?? null;
    $language = $_POST['language'] ?? 'English';
    $book_value = $_POST['book_value'] ?? 0;
    $quantity = $_POST['quantity'] ?? 1;

    $result = $book->addBook($title, $isbn, $publisher_id, $category_id, $description, $year, $edition, $pages, $language, $book_value, $quantity);
    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'danger';
}

$message = $message ?? '';
$message_type = $message_type ?? 'info';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Management - Admin</title>
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
                    <li><a href="users.php" id="menu-users">User Management</a></li>
                    <li><a href="books.php" class="active" id="menu-books">Book Management</a></li>
                    <li><a href="fines.php" id="menu-fines">Fines & Payments</a></li>
                    <li><a href="reports.php" id="menu-reports">Reports</a></li>
                    <li><a href="inventory.php" id="menu-inventory">Inventory</a></li>
                    <li><a href="settings.php" id="menu-settings">System Settings</a></li>
                </ul>
            </aside>

            <main class="main-content">
                <h1>Book Management</h1>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="btn-group">
                        <a href="books.php?action=add" class="btn btn-primary">+ Add New Book</a>
                    </div>
                </div>

                <?php if ($action === 'add'): ?>
                <div class="card">
                    <h3 class="card-title">Add New Book</h3>
                    <form method="POST" id="addBookForm">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label for="title">Title *</label>
                                <input type="text" id="title" name="title" required>
                            </div>
                            <div class="form-group">
                                <label for="isbn">ISBN *</label>
                                <input type="text" id="isbn" name="isbn" required>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label for="category_id">Category *</label>
                                <select id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="publisher_id">Publisher</label>
                                <select id="publisher_id" name="publisher_id">
                                    <option value="">Select Publisher</option>
                                    <?php foreach ($publishers as $pub): ?>
                                    <option value="<?php echo $pub['id']; ?>"><?php echo htmlspecialchars($pub['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description"></textarea>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
                            <div class="form-group">
                                <label for="year">Publication Year</label>
                                <input type="number" id="year" name="year" min="1900" max="2099">
                            </div>
                            <div class="form-group">
                                <label for="edition">Edition</label>
                                <input type="number" id="edition" name="edition" value="1">
                            </div>
                            <div class="form-group">
                                <label for="pages">Pages</label>
                                <input type="number" id="pages" name="pages">
                            </div>
                            <div class="form-group">
                                <label for="language">Language</label>
                                <input type="text" id="language" name="language" value="English">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label for="book_value">Book Value ($)</label>
                                <input type="number" id="book_value" name="book_value" step="0.01">
                            </div>
                            <div class="form-group">
                                <label for="quantity">Quantity</label>
                                <input type="number" id="quantity" name="quantity" value="1" min="1">
                            </div>
                        </div>

                        <div class="btn-group">
                            <button type="submit" class="btn btn-success">Add Book</button>
                            <a href="books.php" class="btn btn-primary">Cancel</a>
                        </div>
                    </form>
                </div>
                <?php else: ?>

                <div class="card">
                    <h3 class="card-title">All Books (<?php echo count($books); ?>)</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>ISBN</th>
                                <th>Category</th>
                                <th>Publisher</th>
                                <th>Author(s)</th>
                                <th>Year</th>
                                <th>Quantity</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($books as $b): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($b['title']); ?></td>
                                <td><?php echo htmlspecialchars($b['isbn']); ?></td>
                                <td><?php echo htmlspecialchars($b['category_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($b['publisher_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($b['authors'] ?? 'Unknown'); ?></td>
                                <td><?php echo htmlspecialchars($b['publication_year']); ?></td>
                                <td><?php echo htmlspecialchars($b['quantity_available']) . '/' . htmlspecialchars($b['quantity_total']); ?></td>
                                <td>
                                    <span class="badge <?php echo $b['status'] === 'available' ? 'badge-success' : 'badge-warning'; ?>">
                                        <?php echo ucfirst($b['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="book-edit.php?id=<?php echo $b['id']; ?>" class="btn btn-small btn-primary">Edit</a>
                                    <a href="javascript:void(0);" onclick="deleteBook(<?php echo $b['id']; ?>)" class="btn btn-small btn-danger">Delete</a>
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
        function deleteBook(bookId) {
            if (confirm('Are you sure you want to delete this book?')) {
                // Implement delete functionality
                window.location.href = 'book-delete.php?id=' + bookId;
            }
        }
    </script>
</body>
</html>
