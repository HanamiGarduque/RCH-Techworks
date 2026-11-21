<?php
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('rider'); // Only riders can update order status
require_once '../Database/db_connection.php';

header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Get and validate input
$refill_id = $_POST['refill_id'] ?? null;
$new_status = $_POST['new_status'] ?? null;
$delay_note = $_POST['delay_note'] ?? null;
$qr_code = $_POST['qr_code'] ?? null;

if (!$refill_id || !$new_status) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit;
}

// Validate status
$valid_statuses = [
    'pending',
    'assigned',
    'pickup_in_progress',
    'pickup_delayed',
    'picked_up',
    'on_delivery',
    'delivery_delayed',
    'delivered',
    'cancelled'
];

if (!in_array($new_status, $valid_statuses)) {
    echo json_encode(['success' => false, 'error' => 'Invalid status']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnect();
    
    // Verify rider is assigned to this order
    $rider_id = $_SESSION['id'];
    
    // FIXED: SELECT * instead of listing columns - so it never fails if a column doesn't exist
    $verify_query = "SELECT * FROM water_refills WHERE refill_id = ? AND rider_id = ?";
    $verify_stmt = $db->prepare($verify_query);
    $verify_stmt->execute([$refill_id, $rider_id]);
    $order = $verify_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        echo json_encode(['success' => false, 'error' => 'Order not found or not assigned to you']);
        exit;
    }
    
    // Validate status transition
    $current_status = $order['order_status'];
    $valid_transitions = [
        'pending' => ['assigned', 'pickup_in_progress', 'cancelled'],
        'assigned' => ['pickup_in_progress', 'cancelled'],
        'pickup_in_progress' => ['pickup_delayed', 'picked_up', 'cancelled'],
        'pickup_delayed' => ['pickup_in_progress', 'picked_up', 'cancelled'],
        'picked_up' => ['on_delivery', 'cancelled'],
        'on_delivery' => ['delivery_delayed', 'delivered'],
        'delivery_delayed' => ['on_delivery', 'delivered'],
        'delivered' => [], // Final state
        'cancelled' => [] // Final state
    ];
    
    if (!in_array($new_status, $valid_transitions[$current_status])) {
        echo json_encode([
            'success' => false, 
            'error' => "Cannot transition from '$current_status' to '$new_status'"
        ]);
        exit;
    }
    
    // Begin transaction
    $db->beginTransaction();
    
    // Update order status in water_refills table
    $update_query = "UPDATE water_refills SET order_status = ?";
    $params = [$new_status];
    
    // Add delay reason if provided
    if ($delay_note && (strpos($new_status, 'delayed') !== false)) {
        $update_query .= ", delay_reason = ?";
        $params[] = $delay_note;
    }

    // Save QR proof ONLY if column exists
    if ($qr_code) {
        try {
            $check = $db->query("SHOW COLUMNS FROM water_refills LIKE 'qr_code'")->fetch();
            if ($check) {
                $update_query .= ", qr_code = ?";
                $params[] = $qr_code;
            }
        } catch (Exception $e) {}
    }
    
    $update_query .= " WHERE refill_id = ?";
    $params[] = $refill_id;
    
    $update_stmt = $db->prepare($update_query);
    $update_stmt->execute($params);
    
    // Commit FIRST - so delivery is saved even if notification fails
    $db->commit();

    // ===== NOTIFICATIONS - AFTER COMMIT, NEVER BREAKS DELIVERY =====
    try {
        $order_number = $order['order_number'] ?? ('#' . $refill_id);
        // user_id is the customer id in water_refills - fallback to any customer column if present
        $customer_id = $order['user_id'] ?? $order['customer_id'] ?? $order['customer_user_id'] ?? null;

        $title = '';
        $msg = '';
        if ($new_status === 'delivered') {
            $title = 'Order Delivered';
            $msg = "Order {$order_number} has been delivered. Thank you!";
        } elseif ($new_status === 'on_delivery') {
            $title = 'Out for Delivery';
            $msg = "Order {$order_number} is now out for delivery.";
        } elseif ($new_status === 'picked_up') {
            $title = 'Order Picked Up';
            $msg = "Order {$order_number} has been picked up.";
        } elseif ($new_status === 'pickup_delayed' || $new_status === 'delivery_delayed') {
            $title = 'Delivery Delayed';
            $msg = "Order {$order_number} is delayed. " . ($delay_note ?? '');
        }

        if ($title !== '') {
            $tbl = $db->query("SHOW TABLES LIKE 'notifications'")->fetch();
            if ($tbl) {
                $cols = $db->query("SHOW COLUMNS FROM notifications")->fetchAll(PDO::FETCH_COLUMN);
                $hasUserId = in_array('user_id', $cols);
                $hasTitle = in_array('title', $cols);
                $hasMessage = in_array('message', $cols);
                $hasOrderNumber = in_array('order_number', $cols);
                $hasRefillId = in_array('refill_id', $cols);
                $hasIsRead = in_array('is_read', $cols);
                $hasCreated = in_array('created_at', $cols);
                $hasRole = in_array('role', $cols);
                $hasType = in_array('type', $cols);

                if ($hasUserId && $hasTitle && $hasMessage) {
                    $fields = ['user_id', 'title', 'message'];
                    $placeholders = ['?', '?', '?'];
                    $valsCustomer = [$customer_id, $title, $msg];
                    $valsAdmin = [null, $title, $msg];

                    if ($hasRole) { $fields[] = 'role'; $placeholders[] = '?'; $valsCustomer[] = 'customer'; $valsAdmin[] = 'admin'; }
                    if ($hasType) { $fields[] = 'type'; $placeholders[] = '?'; $valsCustomer[] = 'order_status'; $valsAdmin[] = 'order_status'; }
                    if ($hasOrderNumber) { $fields[] = 'order_number'; $placeholders[] = '?'; $valsCustomer[] = $order_number; $valsAdmin[] = $order_number; }
                    if ($hasRefillId) { $fields[] = 'refill_id'; $placeholders[] = '?'; $valsCustomer[] = $refill_id; $valsAdmin[] = $refill_id; }
                    if ($hasIsRead) { $fields[] = 'is_read'; $placeholders[] = '0'; }
                    if ($hasCreated) { $fields[] = 'created_at'; $placeholders[] = 'NOW()'; }

                    $sql = "INSERT INTO notifications (" . implode(',', $fields) . ") VALUES (" . implode(',', $placeholders) . ")";
                    if ($customer_id) { $db->prepare($sql)->execute($valsCustomer); }
                    $db->prepare($sql)->execute($valsAdmin);
                }
            }
        }
    } catch (Exception $e) {
        error_log('Notification insert failed (delivery still saved): ' . $e->getMessage());
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Order status updated successfully',
        'new_status' => $new_status,
        'refill_id' => $refill_id
    ]);
    
} catch (PDOException $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Order status update error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred',
        'details' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while updating order status'
    ]);
}
?>