<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../session.php';
require_once '../Database/db_connection.php';
require_once '../Notification/send_notification.php';

// Only riders can access this endpoint
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'rider') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

$rider_id = $_SESSION['id'];
$rider_name = $_SESSION['name'] ?? 'Rider';

// Validate required POST parameters
if (!isset($_POST['order_number']) || !isset($_POST['refill_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit;
}

$order_number = trim($_POST['order_number']);
$refill_id = intval($_POST['refill_id']);
$delay_reason = $_POST['delay_reason'] ?? 'Reschedule requested';
$delay_type = $_POST['delay_type'] ?? 'pickup'; // 'pickup' or 'delivery'

try {
    $database = new Database();
    $db = $database->getConnect();

    // Verify the order belongs to this rider and is in delayed status
    $allowedStatuses = ['pickup_delayed', 'delivery_delayed'];
    
    $verifyQuery = "
        SELECT 
            wr.refill_id,
            wr.order_number,
            wr.order_status,
            wr.pickup_datetime,
            wr.delivery_datetime,
            u.name AS customer_name
        FROM water_refills wr
        INNER JOIN users u ON wr.user_id = u.user_id
        WHERE wr.refill_id = :refill_id
        AND wr.order_number = :order_number
        AND wr.rider_id = :rider_id
        AND wr.order_status IN ('pickup_delayed', 'delivery_delayed')
        LIMIT 1
    ";

    $verifyStmt = $db->prepare($verifyQuery);
    $verifyStmt->bindParam(':refill_id', $refill_id, PDO::PARAM_INT);
    $verifyStmt->bindParam(':order_number', $order_number, PDO::PARAM_STR);
    $verifyStmt->bindParam(':rider_id', $rider_id, PDO::PARAM_INT);
    $verifyStmt->execute();

    $order = $verifyStmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo json_encode(['success' => false, 'error' => 'Order not found, not assigned to you, or not in delayed status']);
        exit;
    }

    // Get all admin user IDs
    $adminQuery = "SELECT user_id FROM users WHERE role = 'admin' AND status = 'active'";
    $adminStmt = $db->prepare($adminQuery);
    $adminStmt->execute();
    $admins = $adminStmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($admins)) {
        echo json_encode(['success' => false, 'error' => 'No active admin found']);
        exit;
    }

    // Prepare notification details based on delay type
    $delayTypeText = $delay_type === 'delivery' ? 'Delivery' : 'Pickup';
    $notificationTitle = "⚠️ {$delayTypeText} Reschedule Request - Order #{$order_number}";
    $notificationMessage = "Rider {$rider_name} is requesting to reschedule the {$delay_type} for order #{$order_number} (Customer: {$order['customer_name']}). Reason: {$delay_reason}";
    $notificationLink = "Order/order_details.php?order_number=" . urlencode($order_number);

    // Send notification to all admins
    $notificationsSent = 0;
    foreach ($admins as $admin) {
        if (createNotification($db, $admin['user_id'], $notificationTitle, $notificationMessage, $notificationLink)) {
            $notificationsSent++;
        }
    }

    if ($notificationsSent > 0) {
        // Log the reschedule request (optional - you can create a separate table for this)
        $logQuery = "
            INSERT INTO order_reschedule_requests 
            (refill_id, order_number, rider_id, delay_type, reason, requested_at)
            VALUES (:refill_id, :order_number, :rider_id, :delay_type, :reason, NOW())
        ";
        
        try {
            $logStmt = $db->prepare($logQuery);
            $logStmt->execute([
                ':refill_id' => $refill_id,
                ':order_number' => $order_number,
                ':rider_id' => $rider_id,
                ':delay_type' => $delay_type,
                ':reason' => $delay_reason
            ]);
        } catch (PDOException $e) {
            // Table might not exist, but notification was sent successfully
            error_log("Could not log reschedule request: " . $e->getMessage());
        }

        echo json_encode([
            'success' => true,
            'message' => 'Reschedule request sent to ' . $notificationsSent . ' admin(s)',
            'notifications_sent' => $notificationsSent
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send notifications']);
    }

} catch (PDOException $e) {
    error_log("Database error in request_reschedule.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
} catch (Exception $e) {
    error_log("Error in request_reschedule.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An error occurred while processing the request']);
}
?>