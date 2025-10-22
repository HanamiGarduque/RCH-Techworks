<?php

class Inventory
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }
    public function retrieveJobs($user_id) 
    {
        $stmt = $this->conn->prepare("CALL get_jobs(:user_id)");
        $stmt->execute([':user_id' => $user_id]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $result;
    }
}
