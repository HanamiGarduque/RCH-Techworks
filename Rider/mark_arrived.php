<?php
// Rider/start_delivery.php
session_start();
require_once '../Database/db_connection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['rider_id'])) { echo json_encode(['error'=>'unauth']); exit; }
$rider_id = (int)$_SESSION['rider_id'];
$delivery_id = (int)($_POST['delivery_id'] ?? 0);
if ($delivery_id <= 0) { echo json_encode(['error'=>'missing id']); exit; }

$db = (new Database())->getConnect();

// verify assignment
$chk = $db->prepare("SELECT * FROM rider_deliveries WHERE delivery_id=:id AND rider_id=:rid LIMIT 1");
$chk->execute([':id'=>$delivery_id, ':rid'=>$rider_id]);
if ($chk->rowCount() === 0) { echo json_encode(['error'=>'not assigned']); exit; }

$stmt = $db->prepare("UPDATE rider_deliveries SET status='On Route', start_time = NOW() WHERE delivery_id=:id");
$stmt->execute([':id'=>$delivery_id]);

echo json_encode(['ok'=>true]);
