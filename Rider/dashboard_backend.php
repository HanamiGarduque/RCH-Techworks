<?php
// Rider/dashboard_backend.php
session_start();
require_once '../Database/db_connection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['rider_id'])) {
    echo json_encode(['error' => 'unauthenticated']);
    exit;
}
$rider_id = (int)$_SESSION['rider_id'];

$db = (new Database())->getConnect();

$stmt = $db->prepare("SELECT d.* , u.name AS customer_name, u.phone_number AS customer_phone
  FROM rider_deliveries d
  LEFT JOIN users u ON u.user_id = d.customer_id
  WHERE d.rider_id = :rider_id
  ORDER BY d.delivery_date ASC");
$stmt->execute([':rider_id' => $rider_id]);
$deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['deliveries' => $deliveries]);
