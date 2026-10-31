<?php
require_once __DIR__ . '/../config.php';

class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;

    private $conn;
    private $error;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);

        if($this->conn->connect_error) {
            $this->error = "Connection failed: " . $this->conn->connect_error;
            return false;
        }

        $this->conn->set_charset("utf8mb4");
        return true;
    }

    public function getConnection() {
        return $this->conn;
    }

    public function query($sql) {
        return $this->conn->query($sql);
    }

    public function escape_string($string) {
        return $this->conn->real_escape_string($string);
    }

    public function insert_id() {
        return $this->conn->insert_id;
    }
}

// Create global database instance
$database = new Database();
$conn = $database->getConnection();
?>