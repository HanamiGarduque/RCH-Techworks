<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../session.php';
require_once '../Database/db_connection.php';

// Do NOT hard-require send_notification.php - if it has an error, whole file dies.
// We will try to use it if it exists, otherwise we insert directly.
if (file_exists(__DIR__ . '/../Notification/send_notification.php')) {
    require_once '../Notification/send_notification.php';
}

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
        echo json_encode(['success' => false, 'error' => 'Order not found, not assigned to you, or not in delayed status. Current check requires pickup_delayed / delivery_delayed.']);
        exit;
    }

    // FIX 1: Don't filter by status = 'active' - your users.status may be 'Active', 1, or NULL.
    // Try with status filter first, then fallback to all admins.
    $admins = [];
    try {
        $adminStmt = $db->prepare("SELECT user_id FROM users WHERE role = 'admin'");
        $adminStmt->execute();
        $admins = $adminStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $admins = [];
    }

    if (empty($admins)) {
        echo json_encode(['success' => false, 'error' => 'No admin found in users table. Check users.role = admin exists.']);
        exit;
    }

    // Prepare notification details based on delay type
    $delayTypeText = $delay_type === 'delivery' ? 'Delivery' : 'Pickup';
    $notificationTitle = "⚠ {$delayTypeText} Reschedule Request - Order #{$order_number}";
    $notificationMessage = "Rider {$rider_name} is requesting to reschedule the {$delay_type} for order #{$order_number} (Customer: {$order['customer_name']}). Reason: {$delay_reason}";
    $notificationLink = "Order/order_details.php?order_number=" . urlencode($order_number);

    // FIX 2: Try createNotification(), if it fails, insert directly. Don't let one bad helper kill the whole request.
    $notificationsSent = 0;
    $lastError = '';
    foreach ($admins as $admin) {
        $sent = false;

        // Try helper first if it exists
        if (function_exists('createNotification')) {
            try {
                $r = createNotification($db, $admin['user_id'], $notificationTitle, $notificationMessage, $notificationLink);
                if ($r) $sent = true;
            } catch (Exception $e) {
                $lastError = $e->getMessage();
            } catch (Error $e) {
                $lastError = $e->getMessage();
            }
        }

        // Fallback: direct insert, try common notifications table shapes
        if (!$sent) {
            try {
                // Most common shape: user_id, title, message, link, is_read, created_at
                $ins = $db->prepare("INSERT INTO notifications (user_id, title, message, link, is_read, created_at) VALUES (:uid, :title, :msg, :link, 0, NOW())");
                $ins->execute([':uid' => $admin['user_id'], ':title' => $notificationTitle, ':msg' => $notificationMessage, ':link' => $notificationLink]);
                $sent = true;
            } catch (Exception $e) {
                $lastError = $e->getMessage();
                // Try 2nd shape: recipient_id, title, message, type
                try {
                    $ins2 = $db->prepare("INSERT INTO notifications (recipient_id, title, message, type, created_at) VALUES (:uid, :title, :msg, 'reschedule', NOW())");
                    $ins2->execute([':uid' => $admin['user_id'], ':title' => $notificationTitle, ':msg' => $notificationMessage]);
                    $sent = true;
                } catch (Exception $e2) {
                    $lastError = $e2->getMessage();
                }
            }
        }

        if ($sent) $notificationsSent++;
    }

    if ($notificationsSent > 0) {
        // Log the reschedule request - if table doesn't exist, ignore, don't fail
        try {
            $logStmt = $db->prepare("
                INSERT INTO order_reschedule_requests
                (refill_id, order_number, rider_id, delay_type, reason, requested_at)
                VALUES (:refill_id, :order_number, :rider_id, :delay_type, :reason, NOW())
            ");
            $logStmt->execute([
                ':refill_id' => $refill_id,
                ':order_number' => $order_number,
                ':rider_id' => $rider_id,
                ':delay_type' => $delay_type,
                ':reason' => $delay_reason
            ]);
        } catch (PDOException $e) {
            error_log("Could not log reschedule request: " . $e->getMessage());
        }

        echo json_encode([
            'success' => true,
            'message' => 'Reschedule request sent to ' . $notificationsSent . ' admin(s)',
            'notifications_sent' => $notificationsSent
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send notifications. Last DB error: ' . $lastError . ' | Check notifications table columns.']);
    }

} catch (PDOException $e) {
    error_log("Database error in request_reschedule.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("Error in request_reschedule.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
} catch (Error $e) {
    error_log("Fatal error in request_reschedule.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Fatal error: ' . $e->getMessage()]);
}