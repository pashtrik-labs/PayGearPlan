<?php
session_start();

// 1. CLASS DEFINITION
class UserManager {
    private $db;

    public function __construct($db_instance) {
        $this->db = $db_instance;
    }

    // A. Admin Login Method
    public function loginAdmin($email, $password) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("SELECT id FROM admins WHERE email = ? AND password = ?");
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $_SESSION['admin_auth'] = true;
            $stmt->close();
            $conn->close();
            header("Location: admin_dashboard.php"); 
            exit();
        } else {
            // Ensure statements and connections are closed when failing or exiting
            $stmt->close();
            $conn->close();
            echo "<script>alert('Invalid Admin credentials.'); window.location.href='devs.php';</script>";
            exit();
        }
    }

    // B. Regular User Login Method
    public function loginUser($username, $password) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $update = $conn->prepare("UPDATE users SET last_login_date = CURRENT_TIMESTAMP WHERE username = ?");
            $update->bind_param("s", $username);
            $update->execute();
            
            $stmt->close();
            $update->close();
            $conn->close();
            
            header("Location: ../php/produktet.php");
            exit(); 
        } else {
            $stmt->close();
            $conn->close();
            echo "<script>alert('Incorrect credentials, try again!'); window.location.href='login.php';</script>";
            exit();
        }
    }

    // C. Regular User Registration Method
    public function registerUser($email, $username, $password) {
        $conn = $this->db->getConnection();

        $stmt = $conn->prepare("INSERT INTO users (email, username, password, last_login_date) VALUES (?, ?, ?, CURRENT_TIMESTAMP)");
        $stmt->bind_param("sss", $email, $username, $password);

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header("Location: ../php/produktet.php");
            exit();
        } else {
            $error = $stmt->error;
            $stmt->close();
            $conn->close();
            echo "Error: " . $error;
        }
    }

    // D. Helper Function
    public function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
}

// 2. INITIALIZE DATABASE AND USER MANAGER
require_once 'lidhjaDatabazes.php'; 

$database = new Database();
$userManager = new UserManager($database);

// 3. THE TRAFFIC CONTROLLER
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // A. Check if it's an ADMIN LOGIN
    if (isset($_POST['login_type']) && $_POST['login_type'] == 'admin') {
        $email = $userManager->test_input($_POST["email"]);
        $password = $userManager->test_input($_POST["password"]);
        $userManager->loginAdmin($email, $password);
    } 
    // B. Check if it's a REGULAR USER
    else {
        if (isset($_POST["email"]) && !empty($_POST["email"])) {
            $email = $userManager->test_input($_POST["email"]);
            $username = $userManager->test_input($_POST["username"]);
            $password = $userManager->test_input($_POST["password"]);
            $userManager->registerUser($email, $username, $password);
        } else {
            $username = $userManager->test_input($_POST["username"]);
            $password = $userManager->test_input($_POST["password"]);
            $userManager->loginUser($username, $password);
        }
    }
}
?>
