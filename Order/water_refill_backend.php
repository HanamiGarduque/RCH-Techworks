<?php
require_once '../Database/db_connection.php';
$database = new Database();
$db = $database->getConnect();

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['user_id'], $data['order_number'], $data['total_amount'], $data['pickup_datetime'], $data['delivery_datetime'], $data['order_status'], $data['gallon_owner'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}


// Prepare statement with named placeholders
$stmt = $db->prepare("
    INSERT INTO water_refills 
    (user_id, order_number, total_amount, pickup_datetime, delivery_datetime, order_status, gallon_owner, created_at)
    VALUES (:user_id, :order_number, :total_amount, :pickup_datetime, :delivery_datetime, :order_status, :gallon_owner, NOW())
");

// Execute with data
$result = $stmt->execute([
    ':user_id' => $data['user_id'],
    ':order_number' => $data['order_number'],
    ':total_amount' => $data['total_amount'],
    ':pickup_datetime' => $data['pickup_datetime'],
    ':delivery_datetime' => $data['delivery_datetime'],
    ':order_status' => $data['order_status'],
    ':gallon_owner' => $data['gallon_owner']
]);

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>
