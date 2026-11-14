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

if (!isset($_GET['order_number']) || empty($_GET['order_number'])) {
    echo json_encode(['success' => false, 'error' => 'Order number is required']);
    exit;
}

$order_number = trim($_GET['order_number']);

try {
    $database = new Database();
    $db = $database->getConnect();

    // Fetch order details with customer information
    // customer_lat / customer_lng on water_refills = admin-corrected pin (most accurate)
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
            wr.customer_lat,
            wr.customer_lng,
            u.name AS customer_name,
            u.phone_number AS customer_phone,
            u.address AS customer_address,
            u.latitude AS user_latitude,
            u.longitude AS user_longitude,
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

    // ---- Resolve REAL customer lat/lng, in order of accuracy ----
    $custLat = null; $custLng = null; $custSource = null;

    // 1) Admin-corrected pin on THIS order
    if ($order['customer_lat'] !== null && $order['customer_lng'] !== null) {
        $custLat = (float)$order['customer_lat']; $custLng = (float)$order['customer_lng']; $custSource = 'saved_pin';
    }
    // 2) User's saved pin
    elseif ($order['user_latitude'] !== null && $order['user_longitude'] !== null) {
        $custLat = (float)$order['user_latitude']; $custLng = (float)$order['user_longitude']; $custSource = 'saved_pin';
    }
    // 3) map_location table
    elseif ($order['customer_latitude'] !== null && $order['customer_longitude'] !== null) {
        $custLat = (float)$order['customer_latitude']; $custLng = (float)$order['customer_longitude']; $custSource = 'map_location';
    }
    // 4) Last known customer GPS in loc_history
    else {
        try {
            $lh = $db->prepare("SELECT latitude, longitude FROM loc_history WHERE user_id = :uid ORDER BY created_at DESC LIMIT 1");
            $lh->execute([':uid' => $order['user_id']]);
            $lr = $lh->fetch(PDO::FETCH_ASSOC);
            if ($lr && $lr['latitude'] !== null && $lr['longitude'] !== null) {
                $custLat = (float)$lr['latitude']; $custLng = (float)$lr['longitude']; $custSource = 'loc_history';
            }
        } catch (Exception $e) {}
    }

    $order['customer_lat'] = $custLat;
    $order['customer_lng'] = $custLng;
    $order['customer_location_source'] = $custSource; // null = frontend must geocode address

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

    $total_subtotal = 0;
    foreach ($items as $item) {
        $total_subtotal += floatval($item['subtotal']);
    }

    $order['pickup_datetime_formatted'] = date('M d, Y h:i A', strtotime($order['pickup_datetime']));
    $order['delivery_datetime_formatted'] = date('M d, Y h:i A', strtotime($order['delivery_datetime']));
    $order['created_at_formatted'] = date('M d, Y h:i A', strtotime($order['created_at']));

    $status_info = getStatusInfo($order['order_status']);
    $order['status_display'] = $status_info['text'];
    $order['status_color'] = $status_info['color'];
    $order['status_icon'] = $status_info['icon'];

    echo json_encode([
        'success' => true,
        'order' => $order,
        'items' => $items,
        'total_subtotal' => number_format($total_subtotal, 2, '.', ''),
        'item_count' => count($items)
    ]);

} catch (PDOException $e) {
    error_log("Database error in order_details_backend.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
} catch (Exception $e) {
    error_log("Error in order_details_backend.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An error occurred while fetching order details']);
}

function getStatusInfo($status) {
    $status_map = [
        'pending' => ['text' => 'Pending', 'color' => 'gray', 'icon' => 'bi-clock'],
        'assigned' => ['text' => 'Assigned', 'color' => 'yellow', 'icon' => 'bi-person-check'],
        'pickup_in_progress' => ['text' => 'Pickup In Progress', 'color' => 'blue', 'icon' => 'bi-box-seam'],
        'pickup_delayed' => ['text' => 'Pickup Delayed', 'color' => 'orange', 'icon' => 'bi-exclamation-triangle'],
        'picked_up' => ['text' => 'Picked Up', 'color' => 'green', 'icon' => 'bi-check-circle'],
        'on_delivery' => ['text' => 'On Delivery', 'color' => 'blue', 'icon' => 'bi-truck'],
        'delivery_delayed' => ['text' => 'Delivery Delayed', 'color' => 'red', 'icon' => 'bi-exclamation-circle'],
        'delivered' => ['text' => 'Delivered', 'color' => 'green', 'icon' => 'bi-check-lg'],
        'cancelled' => ['text' => 'Cancelled', 'color' => 'gray', 'icon' => 'bi-x-circle']
    ];
    return $status_map[$status] ?? ['text' => 'Unknown', 'color' => 'gray', 'icon' => 'bi-question-circle'];
}