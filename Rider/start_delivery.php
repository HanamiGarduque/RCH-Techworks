<?php
session_start();
require_once '../Database/db_connection.php';

if (!isset($_POST['order_id'])) {
    header("Location: dashboard.php");
    exit();
}

$order_id = intval($_POST['order_id']);

$db = new Database();
$conn = $db->getConnect();

// Update the order status to active
$stmt = $conn->prepare("UPDATE orders SET order_status = 'active' WHERE order_id = :order_id");
$stmt->bindParam(':order_id', $order_id);
$stmt->execute();

// Redirect back to dashboard
header("Location: dashboard.php");
exit();
?>
