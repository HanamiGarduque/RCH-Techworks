<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../session.php';
require_once '../Database/db_connection.php';

// Only riders can access this endpoint
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'rider') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

$rider_id = $_SESSION['id'];

// Get order_number from query parameter
if (!isset($_GET['order_number']) || empty($_GET['order_number'])) {
    echo json_encode(['success' => false, 'error' => 'Order number is required']);
    exit;
}

$order_number = trim($_GET['order_number']);

try {
    $database = new Database();
    $db = $database->getConnect();

    // Fetch order details with customer information
    $query = "
        SELECT 
            wr.refill_id,
            wr.user_id,
            wr.rider_id,
            wr.order_number,
            wr.total_amount,
            wr.pickup_datetime,
            wr.delivery_datetime,
            wr.order_status,
            wr.created_at,
            wr.delay_reason,
            u.name AS customer_name,
            u.phone_number AS customer_phone,
            u.address AS customer_address,
            ml.latitude AS customer_latitude,
            ml.longitude AS customer_longitude
        FROM water_refills wr
        INNER JOIN users u ON wr.user_id = u.user_id
        LEFT JOIN map_location ml ON u.user_id = ml.user_id
        WHERE wr.order_number = :order_number
        AND wr.rider_id = :rider_id
        LIMIT 1
    ";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':order_number', $order_number, PDO::PARAM_STR);
    $stmt->bindParam(':rider_id', $rider_id, PDO::PARAM_INT);
    $stmt->execute();

    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo json_encode(['success' => false, 'error' => 'Order not found or not assigned to you']);
        exit;
    }

    // Fetch order items with inventory details
    $itemsQuery = "
        SELECT 
            wri.item_id,
            wri.refill_id,
            wri.product_id,
            wri.gallon_type,
            wri.gallon_size,
            wri.gallon_owner,
            wri.quantity,
            wri.unit_price,
            wri.subtotal,
            inv.item_name,
            inv.description,
            inv.item_image
        FROM water_refill_items wri
        LEFT JOIN inventory inv ON wri.product_id = inv.item_id
        WHERE wri.refill_id = :refill_id
        ORDER BY wri.item_id ASC
    ";

    $itemsStmt = $db->prepare($itemsQuery);
    $itemsStmt->bindParam(':refill_id', $order['refill_id'], PDO::PARAM_INT);
    $itemsStmt->execute();

    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total subtotal from items
    $total_subtotal = 0;
    foreach ($items as $item) {
        $total_subtotal += floatval($item['subtotal']);
    }

    // Format datetime fields for better display
    $order['pickup_datetime_formatted'] = date('M d, Y h:i A', strtotime($order['pickup_datetime']));
    $order['delivery_datetime_formatted'] = date('M d, Y h:i A', strtotime($order['delivery_datetime']));
    $order['created_at_formatted'] = date('M d, Y h:i A', strtotime($order['created_at']));

    // Add status display information
    $status_info = getStatusInfo($order['order_status']);
    $order['status_display'] = $status_info['text'];
    $order['status_color'] = $status_info['color'];
    $order['status_icon'] = $status_info['icon'];

    // Return successful response
    echo json_encode([
        'success' => true,
        'order' => $order,
        'items' => $items,
        'total_subtotal' => number_format($total_subtotal, 2, '.', ''),
        'item_count' => count($items)
    ]);

} catch (PDOException $e) {
    error_log("Database error in order_details_backend.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log("Error in order_details_backend.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while fetching order details'
    ]);
}

/**
 * Get status display information
 */
function getStatusInfo($status) {
    $status_map = [
        'pending' => [
            'text' => 'Pending',
            'color' => 'gray',
            'icon' => 'bi-clock'
        ],
        'assigned' => [
            'text' => 'Assigned',
            'color' => 'yellow',
            'icon' => 'bi-person-check'
        ],
        'pickup_in_progress' => [
            'text' => 'Pickup In Progress',
            'color' => 'blue',
            'icon' => 'bi-box-seam'
        ],
        'pickup_delayed' => [
            'text' => 'Pickup Delayed',
            'color' => 'orange',
            'icon' => 'bi-exclamation-triangle'
        ],
        'picked_up' => [
            'text' => 'Picked Up',
            'color' => 'green',
            'icon' => 'bi-check-circle'
        ],
        'on_delivery' => [
            'text' => 'On Delivery',
            'color' => 'blue',
            'icon' => 'bi-truck'
        ],
        'delivery_delayed' => [
            'text' => 'Delivery Delayed',
            'color' => 'red',
            'icon' => 'bi-exclamation-circle'
        ],
        'delivered' => [
            'text' => 'Delivered',
            'color' => 'green',
            'icon' => 'bi-check-lg'
        ],
        'cancelled' => [
            'text' => 'Cancelled',
            'color' => 'gray',
            'icon' => 'bi-x-circle'
        ]
    ];

    return $status_map[$status] ?? [
        'text' => 'Unknown',
        'color' => 'gray',
        'icon' => 'bi-question-circle'
    ];
}
?>