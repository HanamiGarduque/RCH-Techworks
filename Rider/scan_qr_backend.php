<?php
// Rider/qr_scan_backend.php
session_start();
require_once '../Database/db_connection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['rider_id'])) { echo json_encode(['error'=>'unauth']); exit; }
$rider_id = (int)$_SESSION['rider_id'];
$delivery_id = (int)($_POST['delivery_id'] ?? 0);
$scanned = trim($_POST['scanned'] ?? '');

if ($delivery_id <= 0 || $scanned === '') { echo json_encode(['error'=>'missing data']); exit; }

$db = (new Database())->getConnect();

$stmt = $db->prepare("SELECT * FROM rider_deliveries WHERE delivery_id=:id AND rider_id=:rid LIMIT 1");
$stmt->execute([':id'=>$delivery_id, ':rid'=>$rider_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) { echo json_encode(['error'=>'not found']); exit; }

$expected = $row['qr_code'] ?? '';
if ($expected === '') { echo json_encode(['error'=>'no qr stored']); exit; }

if ($scanned !== $expected) {
    echo json_encode(['error'=>'invalid qr']);
    exit;
}

// valid QR → mark delivered
$upd = $db->prepare("UPDATE rider_deliveries SET status='Delivered', delivered_time = NOW() WHERE delivery_id=:id");
$upd->execute([':id'=>$delivery_id]);

echo json_encode(['ok'=>true, 'message'=>'Delivery completed']);
