<?php
// File: Order/cancel_order.php

header('Content-Type: application/json');

require_once __DIR__ . '/../session.php';
require_once '../Database/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$orderNumber = isset($input['order_number']) ? $input['order_number'] : null;

if (!$orderNumber) {
    echo json_encode(['success' => false, 'error' => 'Order number is required']);
    exit;
}

$database = new Database();
$db = $database->getConnect();

try {
    // Start transaction
    $db->beginTransaction();
    
    // Verify order belongs to user and is in Pending status
    $checkQuery = "
        SELECT refill_id, order_status 
        FROM water_refills 
        WHERE order_number = :order_number 
        AND user_id = :user_id
    ";
    
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':order_number', $orderNumber);
    $checkStmt->bindParam(':user_id', $userId);
    $checkStmt->execute();
    
    $order = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        $db->rollBack();
        echo json_encode(['success' => false, 'error' => 'Order not found']);
        exit;
    }
    
    if ($order['order_status'] !== 'Pending') {
        $db->rollBack();
        echo json_encode(['success' => false, 'error' => 'Only pending orders can be cancelled']);
        exit;
    }
    
    // Update order status to Cancelled
    $updateQuery = "
        UPDATE water_refills 
        SET order_status = 'Cancelled' 
        WHERE order_number = :order_number 
        AND user_id = :user_id
    ";
    
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->bindParam(':order_number', $orderNumber);
    $updateStmt->bindParam(':user_id', $userId);
    $updateStmt->execute();
    
    // Commit transaction
    $db->commit();
    
    echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);
    
} catch (PDOException $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>