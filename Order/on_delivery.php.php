<?php
require_once '../Database/db_connection.php';

header("Content-Type: application/json");

try {
    $db = (new Database())->getConnect();

    // ---- FETCH ORDERS PENDING PAYMENT ----
    $sqlOrders = "
        SELECT o.*, u.name, u.phone_number, u.address
        FROM orders o
        JOIN users u ON o.user_id = u.user_id
        WHERE o.order_status = 'on_delivery'
        ORDER BY o.order_id DESC
    ";

    $stmt = $db->prepare($sqlOrders);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $allOrders = [];

    foreach ($orders as $order) {
        // Fetch order items
        $sqlItems = "
            SELECT oi.*, i.item_name
            FROM order_items oi
            JOIN inventory i ON oi.product_id = i.item_id
            WHERE oi.order_id = ?
        ";
        $stmt2 = $db->prepare($sqlItems);
        $stmt2->execute([$order['order_id']]);
        $items = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        $allOrders[] = [
            'order' => $order,
            'items' => $items
        ];
    }

    echo json_encode($allOrders);

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
