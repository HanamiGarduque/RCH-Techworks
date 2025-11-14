<?php
// Rider/notifications_fetch.php
session_start();
require_once '../Database/db_connection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['rider_id'])) { echo json_encode(['error'=>'unauth']); exit; }
$uid = (int)$_SESSION['rider_id'];
$db = (new Database())->getConnect();

$stmt = $db->prepare("SELECT * FROM rider_notifications WHERE user_id=:uid ORDER BY created_at DESC");
$stmt->execute([':uid'=>$uid]);
$notifs = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['notifications'=>$notifs]);
