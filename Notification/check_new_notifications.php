<?php
require_once '../Database/db_connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$adminId = $_SESSION['user_id'];
$db = (new Database())->getConnect();

// Fetch unread notifications for this admin
$stmt = $db->prepare("
    SELECT id, title, message, link 
    FROM notifications 
    WHERE user_id = :user_id AND status = 'unread'
    ORDER BY created_at ASC
");
$stmt->execute([':user_id' => $adminId]);
$notifs = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($notifs);
