<?php
require_once '../Database/db_connection.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->getConnect();

    $data = json_decode(file_get_contents('php://input'), true);

    // Validate required fields
    if (!isset($data['user_id'], $data['order_number'], $data['total_amount'], 
               $data['pickup_datetime'], $data['delivery_datetime'], 
               $data['order_status'], $data['gallon_owner'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    // Start transaction
    $db->beginTransaction();

    // Insert main order
    $stmt = $db->prepare("
        INSERT INTO water_refills 
        (user_id, order_number, total_amount, pickup_datetime, delivery_datetime, 
         order_status, gallon_owner, created_at)
        VALUES (:user_id, :order_number, :total_amount, :pickup_datetime, 
                :delivery_datetime, :order_status, :gallon_owner, NOW())
    ");

    $result = $stmt->execute([
        ':user_id' => $data['user_id'],
        ':order_number' => $data['order_number'],
        ':total_amount' => $data['total_amount'],
        ':pickup_datetime' => $data['pickup_datetime'],
        ':delivery_datetime' => $data['delivery_datetime'],
        ':order_status' => $data['order_status'],
        ':gallon_owner' => $data['gallon_owner']
    ]);

    if (!$result) {
        throw new Exception('Failed to insert order');
    }

    $refill_id = $db->lastInsertId();

    // Insert order items if provided
    if (isset($data['orders']) && is_array($data['orders'])) {
        $itemStmt = $db->prepare("
            INSERT INTO water_refill_items 
            (refill_id, gallon_type, gallon_size, quantity, unit_price, subtotal)
            VALUES (:refill_id, :type, :size, :quantity, :unit_price, :subtotal)
        ");

        foreach ($data['orders'] as $item) {
            $itemStmt->execute([
                ':refill_id' => $refill_id,
                ':type' => $item['type'],
                ':size' => $item['size'],
                ':quantity' => $item['quantity'],
                ':unit_price' => $item['unitPrice'],
                ':subtotal' => $item['subtotal']
            ]);
        }
    }

    $db->commit();
    echo json_encode(['success' => true, 'order_id' => $refill_id]);

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    error_log('Order placement error: ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>