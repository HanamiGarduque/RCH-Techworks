<?php
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('admin');

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

    // Fetch order with customer and rider info
    $checkOrder = $db->prepare("
        SELECT r.refill_id, r.order_status, u.user_id AS customer_id, u.name AS customer_name, u.email AS customer_email,
               rider.user_id AS rider_id, rider.name AS rider_name, rider.email AS rider_email
        FROM water_refills r
        LEFT JOIN users u ON r.user_id = u.user_id
        LEFT JOIN users rider ON r.rider_id = rider.user_id
        WHERE r.order_number = :order_number LIMIT 1
    ");
    $checkOrder->bindParam(':order_number', $orderNumber);
    $checkOrder->execute();
    
    if ($checkOrder->rowCount() === 0) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Order not found.']);
        exit;
    }

    $order = $checkOrder->fetch(PDO::FETCH_ASSOC);

    // Check if already cancelled or delivered
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

    // Update the order
    $updateQuery = "UPDATE water_refills 
                    SET order_status = 'cancelled', delay_reason = :reason 
                    WHERE order_number = :order_number";
    $stmt = $db->prepare($updateQuery);
    $stmt->bindParam(':reason', $reason);
    $stmt->bindParam(':order_number', $orderNumber);
    $stmt->execute();

    // Prepare notification links
    $notificationLinkCustomer = "Customer/order_details.php?order_number=" . $orderNumber;
    $notificationLinkRider = "Rider/order_details.php?order_number=" . $orderNumber;

    // Messages
    $notificationMessageCustomer = "Your order #$orderNumber has been cancelled. Reason: $reason";
    $notificationMessageRider = "The order #$orderNumber assigned to you has been cancelled. Reason: $reason";

    // Notifications
    createNotification($db, $order['customer_id'], "Order Cancelled - #$orderNumber", $notificationMessageCustomer, $notificationLinkCustomer);
    if ($order['rider_id']) {
        createNotification($db, $order['rider_id'], "Order Cancelled - #$orderNumber", $notificationMessageRider, $notificationLinkRider);
    }

    // Emails
    notifyCustomerDeliveryCancelled($order['customer_email'], $order['customer_name'], $orderNumber, $reason);
    if ($order['rider_email']) {
        notifyRiderDeliveryCancelled($order['rider_email'], $order['rider_name'], $orderNumber, $reason);
    }

    $db->commit();

    echo json_encode(['success' => true, 'message' => 'Order cancelled and notifications sent successfully.']);

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
