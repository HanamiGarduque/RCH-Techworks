<?php
require_once __DIR__ . '/../session.php';

require_once '../Database/db_connection.php';
$database = new Database();
$db = $database->getConnect();

//insert session for user
$user_id = $_SESSION['id'];
$stmt = $db->prepare("SELECT user_id, name, phone_number, address FROM users WHERE user_id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// return JSON to frontend
echo json_encode($user);
?>
