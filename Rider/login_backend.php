<?php
require_once '../Database/db_connection.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $db = new Database();
    $conn = $db->getConnect();

    $query = "SELECT * FROM users WHERE (name = :username OR email = :username) AND role = 'rider' AND status = 'active' LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    $rider = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($rider && hash('sha256', $password) === $rider['password_hash']) {
        $_SESSION['id'] = $rider['user_id'];
        $_SESSION['name'] = $rider['name'];
        echo json_encode(['success' => true]);

        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
    }
    exit;
}
?>
