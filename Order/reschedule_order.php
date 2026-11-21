<?php
// Order/reschedule_order.php - Admin RESCHEDULE ONLY. NEVER cancels.
// MUST return pure JSON only.
ob_start();
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('admin');

require_once '../Database/db_connection.php';
require_once '../Notification/send_notification.php';
require_once '../Notification/email.php';

header('Content-Type: application/json');

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
$newDate     = $data['new_date'] ?? $data['reschedule_date'] ?? $data['delivery_date'] ?? $data['scheduled_date'] ?? null;
$newTime     = $data['new_time'] ?? $data['reschedule_time'] ?? $data['delivery_time'] ?? $data['scheduled_time'] ?? null;
$reason      = $data['reason'] ?? $data['reschedule_reason'] ?? null;

// Validate inputs
if (!$orderNumber) {
    sendJson(['success' => false, 'message' => 'Order number is required.']);
}
if (!$reason || trim($reason) === '') {
    sendJson(['success' => false, 'message' => 'Reschedule reason is required.']);
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
        sendJson(['success' => false, 'message' => 'Order not found.']);
    }

    $order = $checkOrder->fetch(PDO::FETCH_ASSOC);

    // Check if already cancelled or delivered
    if ($order['order_status'] === 'cancelled') {
        $db->rollBack();
        sendJson(['success' => false, 'message' => 'Cannot reschedule a cancelled order.']);
    }
    if ($order['order_status'] === 'delivered') {
        $db->rollBack();
        sendJson(['success' => false, 'message' => 'Cannot reschedule a delivered order.']);
    }

    // FIX: Check what date/time columns actually exist in your table,
    // so we never crash if delivery_date does not exist.
    $cols = [];
    try {
        $colStmt = $db->query("SHOW COLUMNS FROM water_refills");
        foreach ($colStmt->fetchAll(PDO::FETCH_ASSOC) as $c) { $cols[] = $c['Field']; }
    } catch (Throwable $e) { $cols = []; }

    // Build UPDATE dynamically - NEVER set order_status = 'cancelled'
    // Reschedule keeps the SAME status, only updates date/time + reason.
    $sets = [];
    $params = [':order_number' => $orderNumber, ':reason' => $reason];

    // Always save reason in delay_reason (this column exists in your table)
    if (in_array('delay_reason', $cols) || empty($cols)) {
        $sets[] = "delay_reason = :reason";
    }

    // Save date if a date column exists and a new date was sent
    if ($newDate) {
        if (in_array('delivery_date', $cols)) {
            $sets[] = "delivery_date = :new_date";
            $params[':new_date'] = $newDate;
        } elseif (in_array('scheduled_date', $cols)) {
            $sets[] = "scheduled_date = :new_date";
            $params[':new_date'] = $newDate;
        } elseif (in_array('reschedule_date', $cols)) {
            $sets[] = "reschedule_date = :new_date";
            $params[':new_date'] = $newDate;
        }
    }

    // Save time if a time column exists and a new time was sent
    if ($newTime) {
        if (in_array('delivery_time', $cols)) {
            $sets[] = "delivery_time = :new_time";
            $params[':new_time'] = $newTime;
        } elseif (in_array('scheduled_time', $cols)) {
            $sets[] = "scheduled_time = :new_time";
            $params[':new_time'] = $newTime;
        } elseif (in_array('reschedule_time', $cols)) {
            $sets[] = "reschedule_time = :new_time";
            $params[':new_time'] = $newTime;
        }
    }

    // Save reason in reschedule_reason column if it exists
    if (in_array('reschedule_reason', $cols)) {
        $sets[] = "reschedule_reason = :reason";
    }

    if (in_array('updated_at', $cols)) {
        $sets[] = "updated_at = NOW()";
    }

    $updateQuery = "UPDATE water_refills SET " . implode(', ', $sets) . " WHERE order_number = :order_number";
    $stmt = $db->prepare($updateQuery);
    foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
    $stmt->execute();

    // Prepare notification links
    $notificationLinkCustomer = "Customer/order_details.php?order_number=" . $orderNumber;
    $notificationLinkRider = "Rider/order_details.php?order_number=" . $orderNumber;

    // FIX: Messages say RESCHEDULED, not CANCELLED
    $whenText = $newDate ? " to $newDate" . ($newTime ? " $newTime" : "") : "";
    $notificationMessageCustomer = "Your order #$orderNumber has been rescheduled$whenText. Reason: $reason";
    $notificationMessageRider = "The order #$orderNumber assigned to you has been rescheduled$whenText. Reason: $reason";

    // Notifications - never let them break the reschedule
    try {
        createNotification($db, $order['customer_id'], "Order Rescheduled - #$orderNumber", $notificationMessageCustomer, $notificationLinkCustomer);
        if ($order['rider_id']) {
            createNotification($db, $order['rider_id'], "Order Rescheduled - #$orderNumber", $notificationMessageRider, $notificationLinkRider);
        }
    } catch (Throwable $e) {
        error_log('Reschedule notification failed: ' . $e->getMessage());
    }

    // Emails - never let them break JSON or roll back the reschedule
    try {
        if (function_exists('notifyCustomerOrderRescheduled')) {
            notifyCustomerOrderRescheduled($order['customer_email'], $order['customer_name'], $orderNumber, $newDate, $newTime, $reason);
        }
        if ($order['rider_email'] && function_exists('notifyRiderOrderRescheduled')) {
            notifyRiderOrderRescheduled($order['rider_email'], $order['rider_name'], $orderNumber, $newDate, $newTime, $reason);
        }
    } catch (Throwable $e) {
        error_log('Reschedule email failed for ' . $orderNumber . ': ' . $e->getMessage());
    }

    $db->commit();

    sendJson(['success' => true, 'message' => 'Order rescheduled successfully' . $whenText . '.']);

} catch (PDOException $e) {
    if ($db->inTransaction()) $db->rollBack();
    error_log('Database Error in reschedule_order.php: ' . $e->getMessage());
    sendJson(['success' => false, 'message' => 'Database error occurred.']);
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    error_log('Error in reschedule_order.php: ' . $e->getMessage());
    sendJson(['success' => false, 'message' => 'An error occurred while rescheduling order.']);
}