<?php
require_once '../Database/db_connection.php';

header("Content-Type: application/json");

// Validate order_id
if (!isset($_GET['order_id'])) {
    echo json_encode(["error" => "Missing order_id"]);
    exit;
}

$order_id = intval($_GET['order_id']);

try {
    $db = (new Database())->getConnect();

    // ---- FETCH ORDER + USER ----
    $sqlOrder = "
        SELECT r.*, u.name, u.phone_number, u.address
        FROM water_refills r
        JOIN users u ON r.user_id = u.user_id
        WHERE r.refill_id = :order_id AND r.order_status = 'delivered'
        LIMIT 1
    ";

    $stmt = $db->prepare($sqlOrder);
    $stmt->execute(['order_id' => $order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo json_encode(["error" => "Order not found or not delivered"]);
        exit;
    }

    // ---- FETCH ORDER ITEMS ----
    $sqlItems = "
        SELECT w.*, i.item_name
        FROM water_refill_items w
        JOIN inventory i ON w.item_id = i.item_id
        WHERE w.refill_id = :order_id
    ";

    $stmt2 = $db->prepare($sqlItems);
    $stmt2->execute(['order_id' => $order_id]);
    $items = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "order" => $order,
        "items" => $items
    ]);

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
