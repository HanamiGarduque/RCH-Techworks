<?php
require_once '../Database/db_connection.php';
require_once '../Notification/email.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->getConnect();

    $data = json_decode(file_get_contents('php://input'), true);

    // Validate required fields (removed gallon_owner from order level)
    if (!isset(
        $data['user_id'],
        $data['order_number'],
        $data['total_amount'],
        $data['pickup_datetime'],
        $data['delivery_datetime'],
        $data['order_status']
    )) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    // Start transaction
    $db->beginTransaction();

    // Insert main order (removed gallon_owner field)
    $stmt = $db->prepare("
        INSERT INTO water_refills 
        (user_id, order_number, total_amount, pickup_datetime, delivery_datetime, 
         order_status, created_at)
        VALUES (:user_id, :order_number, :total_amount, :pickup_datetime, 
                :delivery_datetime, :order_status, NOW())
    ");

    $result = $stmt->execute([
        ':user_id' => $data['user_id'],
        ':order_number' => $data['order_number'],
        ':total_amount' => $data['total_amount'],
        ':pickup_datetime' => $data['pickup_datetime'],
        ':delivery_datetime' => $data['delivery_datetime'],
        ':order_status' => $data['order_status']
    ]);

    if (!$result) {
        throw new Exception('Failed to insert order');
    }

    $refill_id = $db->lastInsertId();

    // Insert order items with gallon_owner at item level
    if (isset($data['orders']) && is_array($data['orders'])) {
        $itemStmt = $db->prepare("
            INSERT INTO water_refill_items 
            (refill_id, product_id, gallon_type, gallon_size, quantity, unit_price, subtotal, gallon_owner)
            VALUES (:refill_id, :product_id, :type, :size, :quantity, :unit_price, :subtotal, :gallon_owner)
        ");

        foreach ($data['orders'] as $item) {
            // Validate gallon_owner for each item
            $gallonOwner = isset($item['gallonOwner']) ? $item['gallonOwner'] : 'customer';

            // Ensure valid enum value
            if (!in_array($gallonOwner, ['station', 'customer'])) {
                $gallonOwner = 'customer';
            }

            $itemStmt->execute([
                ':refill_id' => $refill_id,
                ':product_id' => $item['productId'],
                ':type' => $item['type'],
                ':size' => $item['size'],
                ':quantity' => $item['quantity'],
                ':unit_price' => $item['unitPrice'],
                ':subtotal' => $item['subtotal'],
                ':gallon_owner' => $gallonOwner
            ]);
        }
    }

    // --- Insert notifications for admin and customer ---

    $adminTitle   = "New Customer Refill Order";
    $adminMessage = "A new customer placed a water refill order. Order #{$data['order_number']} — Review details.";

    $customerTitle   = "Order Placed Successfully";
    $customerMessage = "Your water refill order has been placed successfully! Order #{$data['order_number']} — Total: PHP {$data['total_amount']}.";

    // Admin notification link
    $adminLink    = "Order/order_details.php?order_number={$data['order_number']}";
    $customerLink = "Customer/order_details.php?order_number={$data['order_number']}";

    // Fetch admin IDs
    $adminStmt = $db->prepare("SELECT user_id, email, name FROM users WHERE role = 'admin'");
    $adminStmt->execute();
    $admins = $adminStmt->fetchAll(PDO::FETCH_ASSOC);

    $notifStmt = $db->prepare("
        INSERT INTO notifications (user_id, title, message, link, status, created_at)
        VALUES (:user_id, :title, :message, :link, 'unread', NOW())
    ");

    // Admin notifications + email
    foreach ($admins as $admin) {
        $notifStmt->execute([
            ':user_id' => $admin['user_id'],
            ':title' => $adminTitle,
            ':message' => $adminMessage,
            ':link' => $adminLink
        ]);

        // Send email
        notifyAdminNewOrder(
            $refill_id,
            "Customer", // Optionally you can fetch actual customer name
            $data['total_amount'],
            $data['pickup_datetime'],
            $data['delivery_datetime']
        );
    }

    // Customer notification + email
    $userStmt = $db->prepare("SELECT email, name FROM users WHERE user_id = :user_id");
    $userStmt->execute([':user_id' => $data['user_id']]);
    $customer = $userStmt->fetch(PDO::FETCH_ASSOC);

    $notifStmt->execute([
        ':user_id' => $data['user_id'],
        ':title' => $customerTitle,
        ':message' => $customerMessage,
        ':link' => $customerLink
    ]);

    if ($customer) {
        sendCustomerOrderPlaced(
            $customer['email'],
            $customer['name'],
            $refill_id,
            $data['total_amount'],
            $data['pickup_datetime'],
            $data['delivery_datetime']
        );
    }

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
