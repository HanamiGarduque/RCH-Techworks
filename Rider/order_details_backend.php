<?php
session_start();
require_once '../Database/db_connection.php';

header("Content-Type: application/json");

// Validate refill_id
$refill_id = isset($_GET['refill_id']) ? intval($_GET['refill_id']) : 0;

if ($refill_id <= 0) {
    echo json_encode(["success" => false, "error" => "Invalid refill_id."]);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnect();

    // ---------------------------------------------
    // 1. Fetch order + customer details
    // ---------------------------------------------
    $refill_stmt = $conn->prepare("
        SELECT 
            r.refill_id,
            r.user_id,
            r.order_number,
            r.total_amount,
            r.pickup_datetime,
            r.delivery_datetime,
            r.order_status,
            r.gallon_owner,
            r.created_at,
            
            u.name AS customer_name,
            u.address AS customer_address,
            u.phone_number AS customer_phone

        FROM water_refills r
        JOIN users u ON r.user_id = u.user_id
        WHERE r.refill_id = :refill_id
        LIMIT 1
    ");
    $refill_stmt->bindParam(':refill_id', $refill_id, PDO::PARAM_INT);
    $refill_stmt->execute();
    $order = $refill_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo json_encode(["success" => false, "error" => "Order not found"]);
        exit;
    }

    // ---------------------------------------------
    // 2. Fetch order items with inventory details
    // ---------------------------------------------
    $items_stmt = $conn->prepare("
        SELECT 
            wri.item_id,
            wri.product_id,
            wri.gallon_type,
            wri.gallon_size,
            wri.quantity,
            wri.unit_price,
            wri.subtotal,
            inv.item_name,
            inv.description,
            inv.item_image
        FROM water_refill_items wri
        LEFT JOIN inventory inv ON wri.product_id = inv.item_id
        WHERE wri.refill_id = :refill_id
    ");
    $items_stmt->bindParam(':refill_id', $refill_id, PDO::PARAM_INT);
    $items_stmt->execute();
    $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

    // ---------------------------------------------
    // 3. Calculate total subtotal
    // ---------------------------------------------
    $total_subtotal = 0;
    foreach ($items as $item) {
        $total_subtotal += floatval($item['subtotal']);
    }

    // ---------------------------------------------
    // 4. Prepare JSON Response
    // ---------------------------------------------
    // echo json_encode([
    //     "success" => true,
    //     "order"   => $order,
    //     "items"   => $items,
    //     "total_subtotal" => $total_subtotal
    // ]);
echo json_encode([
    "success" => true,
    "order" => [
        "order_number" => "TEST123",
        "customer_name" => "Debug Customer",
        "customer_address" => "Debug Street",
        "customer_phone" => "09123456789",
        "pickup_datetime" => date("Y-m-d H:i:s", strtotime("+1 minute")),
        "delivery_datetime" => date("Y-m-d H:i:s", strtotime("+45 minutes")),
        "order_status" => "picked_up"
    ],
    "items" => [
        [
            "item_name" => "Water Gallon",
            "gallon_size" => "5L",
            "quantity" => 2,
            "unit_price" => 50,
            "subtotal" => 100
        ]
    ],
    "total_subtotal" => 100
]);
exit;

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => "Server error: " . $e->getMessage()
    ]);
    exit;
}
?>