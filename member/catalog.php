<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once '../config/config.php';
require_once '../classes/Book.php';
require_once '../classes/Metadata.php';

$book = new Book();
$category = new Category();

$search_query = $_GET['q'] ?? '';
$category_filter = $_GET['category'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * ITEMS_PER_PAGE;

$books = [];
$categories = $category->getAllCategories();

if ($search_query) {
    $filters = [];
    if ($category_filter) {
        $filters['category_id'] = $category_filter;
    }
    $books = $book->searchBooks($search_query, $filters);
} else {
    $books = $book->getAllBooks(ITEMS_PER_PAGE, $offset);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Books - Library Management</title>
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
                <h3>Search & Filter</h3>
                <form method="GET" style="margin-bottom: 1.5rem;">
                    <div class="form-group">
                        <label for="search">Search Books</label>
                        <input type="text" id="search" name="q" placeholder="Title, ISBN, author..." value="<?php echo htmlspecialchars($search_query); ?>">
                    </div>

                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Search</button>
                </form>

                <h3>Categories</h3>
                <ul style="list-style: none; padding: 0;">
                    <?php foreach ($categories as $cat): ?>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="?category=<?php echo $cat['id']; ?>" style="color: #3498db; text-decoration: none;">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </aside>

            <main class="main-content">
                <h1>Book Catalog</h1>

                <?php if (!empty($search_query)): ?>
                <p>Search results for: <strong><?php echo htmlspecialchars($search_query); ?></strong></p>
                <?php endif; ?>

                <?php if (count($books) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author(s)</th>
                            <th>ISBN</th>
                            <th>Category</th>
                            <th>Year</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($books as $b): ?>
                        <tr>
                            <td><a href="book-details.php?id=<?php echo $b['id']; ?>" style="color: #3498db;"><?php echo htmlspecialchars($b['title']); ?></a></td>
                            <td><?php echo htmlspecialchars($b['authors'] ?? 'Unknown'); ?></td>
                            <td><?php echo htmlspecialchars($b['isbn']); ?></td>
                            <td><?php echo htmlspecialchars($b['category_name']); ?></td>
                            <td><?php echo htmlspecialchars($b['publication_year']); ?></td>
                            <td>
                                <span class="badge <?php echo $b['status'] === 'available' ? 'badge-success' : 'badge-warning'; ?>">
                                    <?php echo ucfirst($b['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($b['status'] === 'available' || $b['quantity_available'] > 0): ?>
                                    <a href="book-details.php?id=<?php echo $b['id']; ?>" class="btn btn-small btn-primary">Details</a>
                                    <a href="reserve.php?id=<?php echo $b['id']; ?>" class="btn btn-small btn-success">Reserve</a>
                                <?php else: ?>
                                    <a href="book-details.php?id=<?php echo $b['id']; ?>" class="btn btn-small btn-primary">Details</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="pagination">
                    <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo $search_query ? '&q=' . urlencode($search_query) : ''; ?>">Previous</a>
                    <?php endif; ?>
                    
                    <span>Page <?php echo $page; ?></span>
                    
                    <a href="?page=<?php echo $page + 1; ?><?php echo $search_query ? '&q=' . urlencode($search_query) : ''; ?>">Next</a>
                </div>
                <?php else: ?>
                <p>No books found matching your search.</p>
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
