<?php
require_once '../Database/db_connection.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Unknown error'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        $response['message'] = 'Invalid JSON';
        echo json_encode($response);
        exit;
    }

    // --- Validate required fields ---
    $user_id = $data['user_id'] ?? null;
    $delivery_datetime = $data['delivery_datetime'] ?? null;
    $cart = $data['cart'] ?? [];
    $total_amount = $data['total_amount'] ?? null;
    $order_number = $data['order_number']; 

    if (!$user_id || !$delivery_datetime || empty($cart) || !$total_amount) {
        $response['message'] = 'Missing required fields';
        echo json_encode($response);
        exit;
    }

    try {
        $database = new Database();
        $db = $database->getConnect();
        $db->beginTransaction();

        // --- Generate unique order number ---

        // --- Insert into orders ---
        $stmt = $db->prepare("
            INSERT INTO orders (user_id, order_number, total_amount, delivery_datetime, order_status, created_at)
            VALUES (:user_id, :order_number, :total_amount, :delivery_datetime, 'Pending', NOW())
        ");
        $stmt->execute([
            ':user_id' => $user_id,
            ':order_number' => $order_number,
            ':total_amount' => $total_amount,
            ':delivery_datetime' => $delivery_datetime
        ]);

        $order_id = $db->lastInsertId();

        // --- Insert each cart item ---
        $stmtItem = $db->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal)
            VALUES (:order_id, :product_id, :quantity, :unit_price, :subtotal)
        ");

        foreach ($cart as $item) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            $unit_price = $item['unit_price'];
            $subtotal = $quantity * $unit_price;

            $stmtItem->execute([
                ':order_id' => $order_id,
                ':product_id' => $product_id,
                ':quantity' => $quantity,
                ':unit_price' => $unit_price,
                ':subtotal' => $subtotal
            ]);
        }
// --- Insert notifications for admin, customer, and riders ---

    // Create custom messages for each role
    $adminTitle   = "New Customer Item Order";
    $adminMessage = "A new customer placed an item orders. Order #{$data['order_number']} — Review details.";

    $riderTitle   = "Order Ready for Delivery";
    $riderMessage = "A new order is ready for delivery. Order #{$data['order_number']} — Delivery at {$data['delivery_datetime']}.";

    $customerTitle   = "Order Placed Successfully";
    $customerMessage = "Your order has been placed successfully! Order #{$data['order_number']} — Total: PHP {$data['total_amount']}.";

    $orderLink = "order_details.php?order_id={$order_id}";

    // Fetch admin and rider IDs
    $userStmt = $db->prepare("SELECT user_id, role FROM users WHERE role IN ('admin', 'rider')");
    $userStmt->execute();
    $usersToNotify = $userStmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare insert statement
    $notifStmt = $db->prepare("
    INSERT INTO notifications (user_id, title, message, link, status, created_at)
    VALUES (:user_id, :title, :message, :link, 'unread', NOW())
    ");

    // Admin + Rider notifications
    foreach ($usersToNotify as $u) {
        if ($u['role'] === 'admin') {
            $notifStmt->execute([
                ':user_id' => $u['user_id'],
                ':title' => $adminTitle,
                ':message' => $adminMessage,
                ':link' => $orderLink
            ]);
        }

        if ($u['role'] === 'rider') {
            $notifStmt->execute([
                ':user_id' => $u['user_id'],
                ':title' => $riderTitle,
                ':message' => $riderMessage,
                ':link' => $orderLink
            ]);
        }
    }

    // Customer notification
    $notifStmt->execute([
        ':user_id' => $data['user_id'],
        ':title' => $customerTitle,
        ':message' => $customerMessage,
        ':link' => $orderLink
    ]);
        $db->commit();
        $response['success'] = true;
        $response['message'] = 'Order placed successfully';
        $response['order_number'] = $order_number;

    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        $response['message'] = 'Error: ' . $e->getMessage();
    }

} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
