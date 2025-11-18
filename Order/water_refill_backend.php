<?php
require_once '../Database/db_connection.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->getConnect();

    $data = json_decode(file_get_contents('php://input'), true);

    // Validate required fields
    if (!isset(
        $data['user_id'],
        $data['order_number'],
        $data['total_amount'],
        $data['pickup_datetime'],
        $data['delivery_datetime'],
        $data['order_status'],
        $data['gallon_owner']
    )) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    // Start transaction
    $db->beginTransaction();

    // Insert main order
    $stmt = $db->prepare("
        INSERT INTO water_refills 
        (user_id, order_number, total_amount, pickup_datetime, delivery_datetime, 
         order_status, gallon_owner, created_at)
        VALUES (:user_id, :order_number, :total_amount, :pickup_datetime, 
                :delivery_datetime, :order_status, :gallon_owner, NOW())
    ");

    $result = $stmt->execute([
        ':user_id' => $data['user_id'],
        ':order_number' => $data['order_number'],
        ':total_amount' => $data['total_amount'],
        ':pickup_datetime' => $data['pickup_datetime'],
        ':delivery_datetime' => $data['delivery_datetime'],
        ':order_status' => $data['order_status'],
        ':gallon_owner' => $data['gallon_owner']
    ]);

    if (!$result) {
        throw new Exception('Failed to insert order');
    }

    $refill_id = $db->lastInsertId();

    // Insert order items if provided
    if (isset($data['orders']) && is_array($data['orders'])) {
        $itemStmt = $db->prepare("
            INSERT INTO water_refill_items 
            (refill_id, gallon_type, gallon_size, quantity, unit_price, subtotal)
            VALUES (:refill_id, :type, :size, :quantity, :unit_price, :subtotal)
        ");

        foreach ($data['orders'] as $item) {
            $itemStmt->execute([
                ':refill_id' => $refill_id,
                ':type' => $item['type'],
                ':size' => $item['size'],
                ':quantity' => $item['quantity'],
                ':unit_price' => $item['unitPrice'],
                ':subtotal' => $item['subtotal']
            ]);
        }
    }

    // --- Insert notifications for admin, customer, and riders ---

    // Create custom messages for each role
    $adminTitle   = "New Customer Refill Order";
    $adminMessage = "A new customer placed a water refill order. Order #{$data['order_number']} — Review details.";

    $riderTitle   = "Refill Order Ready for Delivery";
    $riderMessage = "A new refill order is ready for delivery. Order #{$data['order_number']} — Pickup at {$data['pickup_datetime']}.";

    $customerTitle   = "Order Placed Successfully";
    $customerMessage = "Your water refill order has been placed successfully! Order #{$data['order_number']} — Total: PHP {$data['total_amount']}.";

    $orderLink = "order_details.php?order_id={$refill_id}";

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
    echo json_encode(['success' => true, 'order_id' => $refill_id]);
} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    error_log('Order placement error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
