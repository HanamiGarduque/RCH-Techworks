<?php
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('admin'); // only admins can access

require_once '../Database/db_connection.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnect();

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);
$orderNumber = $data['order_number'] ?? null;
$reason = $data['reason'] ?? null;

// Validate inputs
if (!$orderNumber) {
    echo json_encode(['success' => false, 'message' => 'Order number is required.']);
    exit;
}

if (!$reason || trim($reason) === '') {
    echo json_encode(['success' => false, 'message' => 'Cancellation reason is required.']);
    exit;
}

try {
    $db->beginTransaction();

    // Verify the order exists
    $checkOrder = $db->prepare("SELECT refill_id, order_status FROM water_refills WHERE order_number = :order_number LIMIT 1");
    $checkOrder->bindParam(':order_number', $orderNumber);
    $checkOrder->execute();
    
    if ($checkOrder->rowCount() === 0) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Order not found.']);
        exit;
    }

    $order = $checkOrder->fetch(PDO::FETCH_ASSOC);

    // Check if order is already cancelled
    if ($order['order_status'] === 'cancelled') {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Order is already cancelled.']);
        exit;
    }

    // Check if order is already delivered
    if ($order['order_status'] === 'delivered') {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Cannot cancel a delivered order.']);
        exit;
    }

    // Update the order status to cancelled and save the reason
    $updateQuery = "UPDATE water_refills 
                    SET order_status = 'cancelled', 
                        delay_reason = :reason 
                    WHERE order_number = :order_number";
    
    $stmt = $db->prepare($updateQuery);
    $stmt->bindParam(':reason', $reason);
    $stmt->bindParam(':order_number', $orderNumber);
    
    if ($stmt->execute()) {
        $db->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Order cancelled successfully.'
        ]);
    } else {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to cancel order.']);
    }

} catch (PDOException $e) {
    $db->rollBack();
    error_log('Database Error in cancel_order.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
} catch (Exception $e) {
    $db->rollBack();
    error_log('Error in cancel_order.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while cancelling order.']);
}
?>