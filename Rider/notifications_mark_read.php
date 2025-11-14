<?php
// Rider/notifications_mark_read.php
session_start();
require_once '../Database/db_connection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['rider_id'])) { echo json_encode(['error'=>'unauth']); exit; }

$notif_id = (int)($_POST['notif_id'] ?? 0);
if ($notif_id <= 0) { echo json_encode(['error'=>'missing']); exit; }

$db = (new Database())->getConnect();
$stmt = $db->prepare("UPDATE rider_notifications SET is_read = 1 WHERE id = :id");
$stmt->execute([':id'=>$notif_id]);

echo json_encode(['ok'=>true]);
