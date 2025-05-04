<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'db_healthcenter';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // Hide sensitive error from users, log it instead
            error_log("DB Connection error: " . $e->getMessage());
            die("Sorry, we are having technical issues.");
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}
?>
