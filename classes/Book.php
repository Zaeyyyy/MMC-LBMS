<?php
/**
 * Book Management Class
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Database.php';

class Book {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Add new book
     */
    public function addBook($title, $isbn, $publisher_id, $category_id, $description, $year, $edition, $pages, $language, $book_value, $quantity = 1, $shelf = null, $section = null) {
        $status = 'available';
        $stmt = $this->db->prepare("
            INSERT INTO books (title, isbn, publisher_id, category_id, description, publication_year, edition, pages, language, book_value, status, shelf_number, section, quantity_total, quantity_available)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param("ssiisissssssii", $title, $isbn, $publisher_id, $category_id, $description, $year, $edition, $pages, $language, $book_value, $status, $shelf, $section, $quantity, $quantity);

        if ($stmt->execute()) {
            $book_id = $this->db->insert_id;
            // Generate barcode
            $this->generateBarcode($book_id);
            return ['success' => true, 'message' => 'Book added successfully', 'book_id' => $book_id];
        } else {
            return ['success' => false, 'message' => 'Failed to add book'];
        }
    }

    /**
     * Generate barcode for book
     */
    public function generateBarcode($book_id) {
        $barcode = 'LIB' . str_pad($book_id, 8, '0', STR_PAD_LEFT);
        $stmt = $this->db->prepare("INSERT INTO barcodes (book_id, barcode_number) VALUES (?, ?)");
        $stmt->bind_param("is", $book_id, $barcode);
        $stmt->execute();
    }

    /**
     * Get book by ID
     */
    public function getBook($book_id) {
        $stmt = $this->db->prepare("
            SELECT b.*, pub.name as publisher_name, cat.name as category_name, GROUP_CONCAT(CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', ') as authors
            FROM books b
            LEFT JOIN publishers pub ON b.publisher_id = pub.id
            LEFT JOIN categories cat ON b.category_id = cat.id
            LEFT JOIN book_authors ba ON b.id = ba.book_id
            LEFT JOIN authors a ON ba.author_id = a.id
            WHERE b.id = ?
            GROUP BY b.id
        ");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Search books
     */
    public function searchBooks($query, $filters = []) {
        $query_escaped = '%' . $this->db->escape_string($query) . '%';
        $sql = "
            SELECT b.*, pub.name as publisher_name, cat.name as category_name, GROUP_CONCAT(CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', ') as authors
            FROM books b
            LEFT JOIN publishers pub ON b.publisher_id = pub.id
            LEFT JOIN categories cat ON b.category_id = cat.id
            LEFT JOIN book_authors ba ON b.id = ba.book_id
            LEFT JOIN authors a ON ba.author_id = a.id
            WHERE (b.title LIKE ? OR b.isbn LIKE ?)
        ";

        $params = [$query_escaped, $query_escaped];
        $types = "ss";

        if (isset($filters['category_id']) && $filters['category_id']) {
            $sql .= " AND b.category_id = ?";
            $params[] = $filters['category_id'];
            $types .= "i";
        }

        if (isset($filters['status']) && $filters['status']) {
            $sql .= " AND b.status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }

        if (isset($filters['year']) && $filters['year']) {
            $sql .= " AND b.publication_year = ?";
            $params[] = $filters['year'];
            $types .= "i";
        }

        $sql .= " GROUP BY b.id ORDER BY b.title ASC LIMIT 50";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get all books with pagination
     */
    public function getAllBooks($limit = ITEMS_PER_PAGE, $offset = 0) {
        $stmt = $this->db->prepare("
            SELECT b.*, pub.name as publisher_name, cat.name as category_name, GROUP_CONCAT(CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', ') as authors
            FROM books b
            LEFT JOIN publishers pub ON b.publisher_id = pub.id
            LEFT JOIN categories cat ON b.category_id = cat.id
            LEFT JOIN book_authors ba ON b.id = ba.book_id
            LEFT JOIN authors a ON ba.author_id = a.id
            GROUP BY b.id
            ORDER BY b.title ASC
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Update book
     */
    public function updateBook($book_id, $title, $isbn, $publisher_id, $category_id, $description, $year, $edition, $pages, $language, $book_value) {
        $stmt = $this->db->prepare("
            UPDATE books SET title = ?, isbn = ?, publisher_id = ?, category_id = ?, description = ?, publication_year = ?, edition = ?, pages = ?, language = ?, book_value = ?
            WHERE id = ?
        ");

        $stmt->bind_param("ssiississi", $title, $isbn, $publisher_id, $category_id, $description, $year, $edition, $pages, $language, $book_value, $book_id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Book updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Update failed'];
        }
    }

    /**
     * Delete book
     */
    public function deleteBook($book_id) {
        $this->db->begin_transaction();

        try {
            // Delete related records
            $this->db->query("DELETE FROM barcodes WHERE book_id = $book_id");
            $this->db->query("DELETE FROM book_authors WHERE book_id = $book_id");
            $this->db->query("DELETE FROM books WHERE id = $book_id");

            $this->db->commit();
            return ['success' => true, 'message' => 'Book deleted successfully'];
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Delete failed'];
        }
    }

    /**
     * Update book status
     */
    public function updateStatus($book_id, $status) {
        $stmt = $this->db->prepare("UPDATE books SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $book_id);

        if ($stmt->execute()) {
            return ['success' => true];
        }
        return ['success' => false];
    }

    /**
     * Get books by category
     */
    public function getBooksByCategory($category_id, $limit = ITEMS_PER_PAGE, $offset = 0) {
        $stmt = $this->db->prepare("
            SELECT b.*, pub.name as publisher_name, cat.name as category_name, GROUP_CONCAT(CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', ') as authors
            FROM books b
            LEFT JOIN publishers pub ON b.publisher_id = pub.id
            LEFT JOIN categories cat ON b.category_id = cat.id
            LEFT JOIN book_authors ba ON b.id = ba.book_id
            LEFT JOIN authors a ON ba.author_id = a.id
            WHERE b.category_id = ?
            GROUP BY b.id
            ORDER BY b.title ASC
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("iii", $category_id, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get book count
     */
    public function getTotalBooks() {
        $result = $this->db->query("SELECT COUNT(*) as total FROM books");
        return $result->fetch_assoc()['total'];
    }
}
?>
