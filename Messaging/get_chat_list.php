<?php
require_once '../Database/db_connection.php';
$database = new Database();
$db = $database->getConnect();

$user_id = $_GET['user_id'];

$stmt = $db->prepare("
    SELECT u.user_id, u.name, u.role,
           (SELECT message FROM messages 
            WHERE (sender_id = u.user_id AND receiver_id = :user_id) 
               OR (sender_id = :user_id AND receiver_id = u.user_id)
            ORDER BY created_at DESC LIMIT 1) as last_message,
           (SELECT created_at FROM messages 
            WHERE (sender_id = u.user_id AND receiver_id = :user_id) 
               OR (sender_id = :user_id AND receiver_id = u.user_id)
            ORDER BY created_at DESC LIMIT 1) as last_time
    FROM users u
    WHERE u.user_id != :user_id
    ORDER BY last_time DESC
");
$stmt->execute(['user_id' => $user_id]);
$chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($chats);
?>
