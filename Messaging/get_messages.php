<?php
require_once '../Database/db_connection.php';
$database = new Database();
$db = $database->getConnect();

$sender_id = $_GET['sender_id'];
$receiver_id = $_GET['receiver_id'];

$query = $db->prepare("
    SELECT message_id, sender_id, receiver_id, message, image_path, created_at
    FROM messages 
    WHERE (sender_id = :sender_id AND receiver_id = :receiver_id)
       OR (sender_id = :receiver_id AND receiver_id = :sender_id)
    ORDER BY created_at ASC
");

$query->execute(['sender_id' => $sender_id, 'receiver_id' => $receiver_id]);
$messages = $query->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($messages);
?>
