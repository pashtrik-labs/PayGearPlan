<?php
require_once "lidhjaDatabazes.php";

class Validator {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function checkEmail($email) {
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $exists = ($result->num_rows > 0);
        $stmt->close();
        return $exists;
    }

    public function checkUsername($username) {
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $exists = ($result->num_rows > 0);
        $stmt->close();
        return $exists;
    }

    public function closeConnection() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

// Instantiate the Validator
$validator = new Validator();

if (array_key_exists("email", $_REQUEST)) {
    if (empty($_REQUEST["email"])) {
        echo "* Email is required";
    } else {
        $email = test_input($_REQUEST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "* Invalid email format";
        } else {
            if ($validator->checkEmail($email)) {
                echo "* Email already exists";
            } else {
                echo "";
            }
        }
    }
}

if (array_key_exists("username", $_REQUEST)) {
    if (empty($_REQUEST["username"])) {
        echo "* Name is required";
    } else {
        $username = test_input($_REQUEST["username"]);
        if ($validator->checkUsername($username)) {
            echo "* Username already exists";
        } else {
            echo "";
        }
    }
}

$validator->closeConnection();

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
