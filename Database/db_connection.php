<?php
class Database {
    private $host = 'localhost';
    private $port = '3307';
    private $dbName = 'water_delivery_system';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnect() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->dbName,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // Log error instead of echoing it
            error_log("Database connection error: " . $e->getMessage());
            throw $e;
        }

        return $this->conn;
    }
}
?>
