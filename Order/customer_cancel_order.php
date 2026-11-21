<?php
// Order/customer_cancel_order.php - Customer cancels their OWN order
// MUST return pure JSON only
ob_start();
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('customer'); // only customers can access

require_once '../Database/db_connection.php';
require_once '../Notification/send_notification.php';
require_once '../Notification/email.php';

header('Content-Type: application/json');

// Helper: discard ALL stray output and send clean JSON only
function sendJson(array $payload) {
    while (ob_get_level() > 0) { ob_end_clean(); }
    echo json_encode($payload);
    exit;
}

$database = new Database();
$db = $database->getConnect();

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);
$orderNumber = $data['order_number'] ?? null;
$reason = $data['reason'] ?? null;

// Validate inputs
if (!$orderNumber) {
    sendJson(['success' => false, 'message' => 'Order number is required.']);
}

if (!$reason || trim($reason) === '') {
    sendJson(['success' => false, 'message' => 'Cancellation reason is required.']);
}

// FIX 1: Your session uses $_SESSION['id'], NOT $_SESSION['user_id']
// Support both so it never breaks again
$loggedInUserId = $_SESSION['id'] ?? $_SESSION['user_id'] ?? null;
if (!$loggedInUserId) {
    sendJson(['success' => false, 'message' => 'Session expired. Please log in again.']);
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
    $checkOrder->bindParam(':user_id', $loggedInUserId);
    $checkOrder->execute();
    
    if ($checkOrder->rowCount() === 0) {
        $db->rollBack();
        sendJson(['success' => false, 'message' => 'Order not found or not yours.']);
    }

    $order = $checkOrder->fetch(PDO::FETCH_ASSOC);

    // Check if order is already cancelled or delivered
    if ($order['order_status'] === 'cancelled') {
        $db->rollBack();
        sendJson(['success' => false, 'message' => 'Order is already cancelled.']);
    }

    if ($order['order_status'] === 'delivered') {
        $db->rollBack();
        sendJson(['success' => false, 'message' => 'Cannot cancel a delivered order.']);
    }

    // Update the order status to cancelled and save the reason
    $updateQuery = "UPDATE water_refills 
                    SET order_status = 'cancelled', delay_reason = :reason 
                    WHERE order_number = :order_number";
    $stmt = $db->prepare($updateQuery);
    $stmt->bindParam(':reason', $reason);
    $stmt->bindParam(':order_number', $orderNumber);
    $stmt->execute();

    // Create a notification for the customer (self) - never let it break the cancel
    try {
        $notificationTitle = "Order Cancelled - #$orderNumber";
        $notificationMessage = "Your order #$orderNumber has been cancelled. Reason: $reason";
        $notificationLink = "Customer/order_details.php?order_number=" . $orderNumber;
        createNotification($db, $order['user_id'], $notificationTitle, $notificationMessage, $notificationLink);
    } catch (Throwable $e) {
        error_log('Customer cancel notification failed: ' . $e->getMessage());
    }

    // Send email to customer - NEVER let email break JSON or roll back the cancel
    // sendEmail() now logs instead of echoing, but still wrap it for safety
    try {
        notifyCustomerDeliveryCancelled($order['email'], $order['name'], $orderNumber, $reason);
    } catch (Throwable $emailErr) {
        error_log('Customer cancel email failed for ' . $orderNumber . ': ' . $emailErr->getMessage());
        // Do NOT roll back - cancellation already succeeded
    }

    $db->commit();

    sendJson([
        'success' => true,
        'message' => 'Order cancelled successfully.'
    ]);

} catch (PDOException $e) {
    if ($db->inTransaction()) $db->rollBack();
    error_log('Database Error in customer_cancel_order.php: ' . $e->getMessage());
    sendJson(['success' => false, 'message' => 'Database error occurred.']);
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    error_log('Error in customer_cancel_order.php: ' . $e->getMessage());
    sendJson(['success' => false, 'message' => 'An error occurred while cancelling order.']);
}