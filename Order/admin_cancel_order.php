<?php
// FIX: start output buffering FIRST - nothing (warnings, email echoes) may print before JSON
ob_start();
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('admin'); // only admins can access

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

// Helper: discard ANY stray output (email echoes, warnings) and send clean JSON only
function sendJson(array $payload) {
    // Throw away everything buffered so far - e.g. "<b>Email send error...</b>"
    if (ob_get_level() > 0) ob_clean();
    echo json_encode($payload);
    exit;
}

// Validate inputs
if (!$orderNumber) {
    sendJson(['success' => false, 'message' => 'Order number is required.']);
}

if (!$reason || trim($reason) === '') {
    sendJson(['success' => false, 'message' => 'Cancellation reason is required.']);
}

try {
    $db->beginTransaction();

    // Verify the order exists
    $checkOrder = $db->prepare("SELECT r.refill_id, r.order_status, u.user_id, u.name, u.email 
                                FROM water_refills r
                                LEFT JOIN users u ON r.user_id = u.user_id
                                WHERE r.order_number = :order_number LIMIT 1");
    $checkOrder->bindParam(':order_number', $orderNumber);
    $checkOrder->execute();
    
    if ($checkOrder->rowCount() === 0) {
        $db->rollBack();
        sendJson(['success' => false, 'message' => 'Order not found.']);
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

    // Create a notification for the customer
    $notificationTitle = "Order Cancelled - #$orderNumber";
    $notificationMessage = "Your order #$orderNumber has been cancelled. Reason: $reason";
    $notificationLink = "Customer/order_details.php?order_number=" . $orderNumber;
    createNotification($db, $order['user_id'], $notificationTitle, $notificationMessage, $notificationLink);

    // Send email to customer
    // FIX: email must NEVER break JSON or roll back the cancel.
    // Buffer + swallow its output, and catch its exception. If Brevo fails, order is still cancelled.
    try {
        ob_start();
        notifyCustomerDeliveryCancelled($order['email'], $order['name'], $orderNumber, $reason);
        $emailOutput = ob_get_clean();
        if ($emailOutput) {
            // Log it for you to see, don't show it to the browser
            error_log('Cancel-order email output for ' . $orderNumber . ': ' . $emailOutput);
        }
    } catch (Throwable $emailErr) {
        if (ob_get_level() > 0) ob_end_clean();
        error_log('Cancel-order email failed for ' . $orderNumber . ': ' . $emailErr->getMessage());
        // Do NOT roll back - cancellation already succeeded
    }

    $db->commit();

    sendJson([
        'success' => true,
        'message' => 'Order cancelled successfully.'
    ]);

} catch (PDOException $e) {
    if ($db->inTransaction()) $db->rollBack();
    error_log('Database Error in cancel_order.php: ' . $e->getMessage());
    sendJson(['success' => false, 'message' => 'Database error occurred.']);
} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    error_log('Error in cancel_order.php: ' . $e->getMessage());
    sendJson(['success' => false, 'message' => 'An error occurred while cancelling order.']);
}