<?php
require_once '../Database/db_connection.php';

if(!isset($_POST['notification_id'])) exit;

$notification_id = $_POST['notification_id'];
$database = new Database();
$db = $database->getConnect();

$stmt = $db->prepare("DELETE FROM notifications WHERE notification_id=:id");
$stmt->execute([':id' => $notification_id]);

echo json_encode(['success'=>true]);
?>