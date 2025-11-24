<?php
class Database {
    private $host = '10.97.88.118';   // your laptop IP
    private $port = '3307';            // your MySQL port
    private $dbName = 'water_delivery_system';
    private $username = 'remote_user'; // the MySQL user you created with host = %
    private $password = 'RCHTechworks@123'; 
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
            error_log("Database connection error: " . $e->getMessage());
            throw $e;
        }

        return $this->conn;
    }
}
?>
