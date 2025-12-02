<?php
require_once __DIR__ . '/../session.php';
// Allow customers only; remove admin restriction
redirectIfRoleMismatch('customer'); // now only customers can access

require_once '../Database/db_connection.php';
require_once '../Notification/send_notification.php';
require_once '../Notification/email.php';

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

    // Verify the order exists AND belongs to the logged-in customer
    $checkOrder = $db->prepare("SELECT r.refill_id, r.order_status, u.user_id, u.name, u.email 
                                FROM water_refills r
                                LEFT JOIN users u ON r.user_id = u.user_id
                                WHERE r.order_number = :order_number
                                AND u.user_id = :user_id
                                LIMIT 1");
    $checkOrder->bindParam(':order_number', $orderNumber);
    $checkOrder->bindParam(':user_id', $_SESSION['user_id']); // ensure only their own orders
    $checkOrder->execute();
    
    if ($checkOrder->rowCount() === 0) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Order not found or not yours.']);
        exit;
    }

    $order = $checkOrder->fetch(PDO::FETCH_ASSOC);

    // Check if order is already cancelled or delivered
    if ($order['order_status'] === 'cancelled') {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Order is already cancelled.']);
        exit;
    }

    if ($order['order_status'] === 'delivered') {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Cannot cancel a delivered order.']);
        exit;
    }

    // Update the order status to cancelled and save the reason
    $updateQuery = "UPDATE water_refills 
                    SET order_status = 'cancelled', delay_reason = :reason 
                    WHERE order_number = :order_number";
    $stmt = $db->prepare($updateQuery);
    $stmt->bindParam(':reason', $reason);
    $stmt->bindParam(':order_number', $orderNumber);
    $stmt->execute();

    // Create a notification for the customer (self)
    $notificationTitle = "Order Cancelled - #$orderNumber";
    $notificationMessage = "Your order #$orderNumber has been cancelled. Reason: $reason";
    $notificationLink = "Customer/order_details.php?order_number=" . $orderNumber;
    createNotification($db, $order['user_id'], $notificationTitle, $notificationMessage, $notificationLink);

    // Send email to customer
    notifyCustomerDeliveryCancelled($order['email'], $order['name'], $orderNumber, $reason);

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Order cancelled successfully.'
    ]);

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
