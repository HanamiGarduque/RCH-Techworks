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
    
    $verify_query = "SELECT refill_id, order_status FROM water_refills WHERE refill_id = ? AND rider_id = ?";
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
    
    $update_query .= " WHERE refill_id = ?";
    $params[] = $refill_id;
    
    $update_stmt = $db->prepare($update_query);
    $update_stmt->execute($params);
    
    // Commit transaction
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Order status updated successfully',
        'new_status' => $new_status,
        'refill_id' => $refill_id
    ]);
    
} catch (PDOException $e) {
    // Rollback on error
    if ($db->inTransaction()) {
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