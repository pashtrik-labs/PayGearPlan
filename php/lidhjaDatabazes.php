<?php

class Database {
    private $servername = "localhost";
    private $connUsername = "root";
    private $connPassword = "";
    private $dbname = "paygearplanDB";
    private $port = 3308;
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new mysqli($this->servername, $this->connUsername, $this->connPassword, $this->dbname, $this->port);

            if ($this->conn->connect_error) {
                die("Connection failed: " . $this->conn->connect_error);
            }
        } catch (Exception $e) {
            echo "Connection error: " . $e->getMessage();
        }

        return $this->conn;
    }
}

?>
