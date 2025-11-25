<?php
// File: Order/get_orders.php

header('Content-Type: application/json');

require_once __DIR__ . '/../session.php';
require_once '../Database/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$status = isset($_GET['status']) ? $_GET['status'] : 'Pending';

$database = new Database();
$db = $database->getConnect();

try {
    // Fetch orders by status
    $orderQuery = "
        SELECT 
            wr.refill_id,
            wr.order_number,
            wr.total_amount,
            wr.pickup_datetime,
            wr.delivery_datetime,
            wr.order_status,
            wr.created_at
        FROM water_refills wr
        WHERE wr.user_id = :user_id 
        AND wr.order_status = :status
        ORDER BY wr.created_at DESC
    ";
    
    $stmt = $db->prepare($orderQuery);
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':status', $status);
    $stmt->execute();
    
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $result = [];
    
    foreach ($orders as $order) {
        // Fetch items for each order
        $itemsQuery = "
            SELECT 
                wri.item_id,
                wri.quantity,
                wri.unit_price,
                wri.subtotal,
                wri.gallon_type,
                wri.gallon_size,
                p.product_name,
                COALESCE(
                    p.product_name,
                    CONCAT(wri.gallon_size, ' ', wri.gallon_type)
                ) as item_name
            FROM water_refill_items wri
            LEFT JOIN inventory p ON wri.product_id = p.product_id
            WHERE wri.refill_id = :refill_id
        ";
        
        $itemsStmt = $db->prepare($itemsQuery);
        $itemsStmt->bindParam(':refill_id', $order['refill_id']);
        $itemsStmt->execute();
        
        $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $result[] = [
            'order' => $order,
            'items' => $items
        ];
    }
    
    echo json_encode($result);
    
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>