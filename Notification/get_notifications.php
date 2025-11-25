<?php
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('admin'); // only admins can access

require_once '../Database/db_connection.php';

// // assuming user ID is stored in session
$user_id = $_SESSION['id'];
$database = new Database();
$db = $database->getConnect();

$stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC");
$stmt->execute([':user_id' => $user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($notifications);
?>