<?php
session_start();
if (!isset($_SESSION['admin_auth'])) { exit("Unauthorized"); }

require_once "lidhjaDatabazes.php";

class UserDeleter {
    private $db;
    private $conn;

    public function __construct($db_instance) {
        $this->db = $db_instance;
        $this->conn = $this->db->getConnection();
    }

    public function deleteUser($id) {
        // Use a prepared statement for security
        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $stmt->close();
            $this->conn->close();
            header("Location: admin_dashboard.php?msg=deleted");
            exit();
        } else {
            $error = $this->conn->error;
            $stmt->close();
            $this->conn->close();
            echo "Error deleting record: " . $error;
        }
    }
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Instantiate the Database and the Deleter object
    $database = new Database();
    $userDeleter = new UserDeleter($database);
    $userDeleter->deleteUser($id);
}
?>
