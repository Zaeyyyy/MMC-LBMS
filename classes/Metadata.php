<?php
/**
 * Category, Author & Publisher Management Classes
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Database.php';

class Category {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllCategories() {
        $result = $this->db->query("SELECT * FROM categories ORDER BY name ASC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getCategory($id) {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function addCategory($name, $description, $dewey_decimal = null, $lc_classification = null) {
        $stmt = $this->db->prepare("
            INSERT INTO categories (name, description, dewey_decimal, lc_classification)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("ssss", $name, $description, $dewey_decimal, $lc_classification);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Category added', 'id' => $this->db->insert_id];
        }
        return ['success' => false, 'message' => 'Failed to add category'];
    }

    public function updateCategory($id, $name, $description, $dewey_decimal = null, $lc_classification = null) {
        $stmt = $this->db->prepare("
            UPDATE categories SET name = ?, description = ?, dewey_decimal = ?, lc_classification = ?
            WHERE id = ?
        ");
        $stmt->bind_param("ssssi", $name, $description, $dewey_decimal, $lc_classification, $id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Category updated'];
        }
        return ['success' => false, 'message' => 'Update failed'];
    }

    public function deleteCategory($id) {
        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Category deleted'];
        }
        return ['success' => false, 'message' => 'Delete failed'];
    }
}

class Author {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllAuthors() {
        $result = $this->db->query("SELECT * FROM authors ORDER BY first_name, last_name ASC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getAuthor($id) {
        $stmt = $this->db->prepare("SELECT * FROM authors WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function searchAuthors($query) {
        $query_escaped = '%' . $this->db->escape_string($query) . '%';
        $stmt = $this->db->prepare("
            SELECT * FROM authors
            WHERE first_name LIKE ? OR last_name LIKE ?
            ORDER BY first_name, last_name ASC
        ");
        $stmt->bind_param("ss", $query_escaped, $query_escaped);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function addAuthor($first_name, $last_name, $biography = null, $birth_date = null, $nationality = null) {
        $stmt = $this->db->prepare("
            INSERT INTO authors (first_name, last_name, biography, birth_date, nationality)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssss", $first_name, $last_name, $biography, $birth_date, $nationality);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Author added', 'id' => $this->db->insert_id];
        }
        return ['success' => false, 'message' => 'Failed to add author'];
    }

    public function updateAuthor($id, $first_name, $last_name, $biography = null, $birth_date = null, $nationality = null) {
        $stmt = $this->db->prepare("
            UPDATE authors SET first_name = ?, last_name = ?, biography = ?, birth_date = ?, nationality = ?
            WHERE id = ?
        ");
        $stmt->bind_param("sssssi", $first_name, $last_name, $biography, $birth_date, $nationality, $id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Author updated'];
        }
        return ['success' => false, 'message' => 'Update failed'];
    }
}

class Publisher {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllPublishers() {
        $result = $this->db->query("SELECT * FROM publishers ORDER BY name ASC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getPublisher($id) {
        $stmt = $this->db->prepare("SELECT * FROM publishers WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function searchPublishers($query) {
        $query_escaped = '%' . $this->db->escape_string($query) . '%';
        $stmt = $this->db->prepare("
            SELECT * FROM publishers
            WHERE name LIKE ?
            ORDER BY name ASC
        ");
        $stmt->bind_param("s", $query_escaped);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function addPublisher($name, $address = null, $phone = null, $email = null) {
        $stmt = $this->db->prepare("
            INSERT INTO publishers (name, address, phone, email)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("ssss", $name, $address, $phone, $email);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Publisher added', 'id' => $this->db->insert_id];
        }
        return ['success' => false, 'message' => 'Failed to add publisher'];
    }

    public function updatePublisher($id, $name, $address = null, $phone = null, $email = null) {
        $stmt = $this->db->prepare("
            UPDATE publishers SET name = ?, address = ?, phone = ?, email = ?
            WHERE id = ?
        ");
        $stmt->bind_param("ssssi", $name, $address, $phone, $email, $id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Publisher updated'];
        }
        return ['success' => false, 'message' => 'Update failed'];
    }
}

class Inventory {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create inventory audit
     */
    public function createAudit($book_id, $quantity_expected, $quantity_found, $condition = 'good', $notes = null, $auditor_id = null) {
        $stmt = $this->db->prepare("
            INSERT INTO inventory_audit (book_id, quantity_expected, quantity_found, condition, notes, auditor_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iiissi", $book_id, $quantity_expected, $quantity_found, $condition, $notes, $auditor_id);

        if ($stmt->execute()) {
            // Update book quantity if needed
            if ($quantity_found != $quantity_expected) {
                $this->db->query("UPDATE books SET quantity_available = $quantity_found WHERE id = $book_id");
            }
            return ['success' => true, 'message' => 'Audit recorded'];
        }
        return ['success' => false, 'message' => 'Audit failed'];
    }

    /**
     * Get audit history
     */
    public function getAuditHistory($book_id = null, $limit = 50) {
        $sql = "SELECT * FROM inventory_audit";
        if ($book_id) {
            $sql .= " WHERE book_id = $book_id";
        }
        $sql .= " ORDER BY audit_date DESC LIMIT $limit";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get missing books
     */
    public function getMissingBooks() {
        $result = $this->db->query("
            SELECT b.*, ia.quantity_expected, ia.quantity_found, ia.audit_date
            FROM books b
            JOIN inventory_audit ia ON b.id = ia.book_id
            WHERE ia.quantity_found < ia.quantity_expected
            AND ia.audit_date = (
                SELECT MAX(audit_date) FROM inventory_audit WHERE book_id = b.id
            )
            ORDER BY ia.audit_date DESC
        ");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>
