<?php
// Rider/fetch_delivery.php
session_start();
require_once '../Database/db_connection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['rider_id'])) { echo json_encode(['error'=>'unauth']); exit; }
$delivery_id = (int)($_GET['id'] ?? 0);
if ($delivery_id <= 0) { echo json_encode(['error'=>'missing id']); exit; }

$db = (new Database())->getConnect();
$stmt = $db->prepare("SELECT d.*, c.name AS customer_name, c.phone_number AS customer_phone
  FROM rider_deliveries d
  LEFT JOIN users c ON c.user_id = d.customer_id
  WHERE d.delivery_id = :id LIMIT 1");
$stmt->execute([':id'=>$delivery_id]);
$delivery = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode(['delivery' => $delivery]);
