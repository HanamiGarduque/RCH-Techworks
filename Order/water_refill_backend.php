
<?php
// Return JSON to the frontend
header('Content-Type: application/json; charset=utf-8');

// Prevent accidental output from included PHP files
ob_start();

require_once __DIR__ . '/../Database/db_connection.php';
require_once __DIR__ . '/../Notification/email.php';

$includeOutput = ob_get_clean();

if (!empty($includeOutput)) {
    error_log('Unexpected output from included files: ' . $includeOutput);
}

$db = null;

try {
    $database = new Database();
    $db = $database->getConnect();

    // Read and validate JSON input
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON request.'
        ]);
        exit;
    }

    $requiredFields = [
        'user_id',
        'order_number',
        'total_amount',
        'pickup_datetime',
        'delivery_datetime',
        'order_status'
    ];

    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || $data[$field] === '') {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => "Missing required field: {$field}"
            ]);
            exit;
        }
    }

    if (
        !isset($data['orders']) ||
        !is_array($data['orders']) ||
        count($data['orders']) === 0
    ) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Your order must contain at least one item.'
        ]);
        exit;
    }

    // Begin database transaction
    $db->beginTransaction();

    // Insert main refill order
    $stmt = $db->prepare("
        INSERT INTO water_refills
        (
            user_id,
            order_number,
            total_amount,
            pickup_datetime,
            delivery_datetime,
            order_status,
            created_at
        )
        VALUES
        (
            :user_id,
            :order_number,
            :total_amount,
            :pickup_datetime,
            :delivery_datetime,
            :order_status,
            NOW()
        )
    ");

    $stmt->execute([
        ':user_id' => $data['user_id'],
        ':order_number' => $data['order_number'],
        ':total_amount' => $data['total_amount'],
        ':pickup_datetime' => $data['pickup_datetime'],
        ':delivery_datetime' => $data['delivery_datetime'],
        ':order_status' => $data['order_status']
    ]);

    $refill_id = $db->lastInsertId();

    // Insert refill order items
    $itemStmt = $db->prepare("
        INSERT INTO water_refill_items
        (
            refill_id,
            product_id,
            gallon_type,
            gallon_size,
            quantity,
            unit_price,
            subtotal,
            gallon_owner
        )
        VALUES
        (
            :refill_id,
            :product_id,
            :type,
            :size,
            :quantity,
            :unit_price,
            :subtotal,
            :gallon_owner
        )
    ");

    foreach ($data['orders'] as $item) {
        $itemRequiredFields = [
            'productId',
            'type',
            'size',
            'quantity',
            'unitPrice',
            'subtotal'
        ];

        foreach ($itemRequiredFields as $field) {
            if (!isset($item[$field])) {
                throw new Exception(
                    "Missing order item field: {$field}"
                );
            }
        }

        $gallonOwner = $item['gallonOwner'] ?? 'customer';

        if (!in_array($gallonOwner, ['station', 'customer'], true)) {
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

    // Prepare notification details
    $adminTitle = 'New Customer Refill Order';
    $adminMessage =
        "A new customer placed a water refill order. Order #" .
        $data['order_number'] . ' — Review details.';

    $customerTitle = 'Order Placed Successfully';
    $customerMessage =
        "Your water refill order has been placed successfully! Order #" .
        $data['order_number'] .
        " — Total: PHP " . $data['total_amount'] . '.';

    $adminLink =
        'Order/order_details.php?order_number=' .
        rawurlencode((string) $data['order_number']);

    $customerLink =
        'Customer/order_details.php?order_number=' .
        rawurlencode((string) $data['order_number']);

    // Get admin accounts
    $adminStmt = $db->prepare("
        SELECT user_id, email, name
        FROM users
        WHERE role = 'admin'
    ");

    $adminStmt->execute();
    $admins = $adminStmt->fetchAll(PDO::FETCH_ASSOC);

    // Notification insert statement
    $notifStmt = $db->prepare("
        INSERT INTO notifications
        (user_id, title, message, link, status, created_at)
        VALUES
        (:user_id, :title, :message, :link, 'unread', NOW())
    ");

    // Create admin notifications
    foreach ($admins as $admin) {
        $notifStmt->execute([
            ':user_id' => $admin['user_id'],
            ':title' => $adminTitle,
            ':message' => $adminMessage,
            ':link' => $adminLink
        ]);
    }

    // Get customer details
    $userStmt = $db->prepare("
        SELECT email, name
        FROM users
        WHERE user_id = :user_id
    ");

    $userStmt->execute([
        ':user_id' => $data['user_id']
    ]);

    $customer = $userStmt->fetch(PDO::FETCH_ASSOC);

    if (!$customer) {
        throw new Exception('Customer account was not found.');
    }

    // Create customer notification
    $notifStmt->execute([
        ':user_id' => $data['user_id'],
        ':title' => $customerTitle,
        ':message' => $customerMessage,
        ':link' => $customerLink
    ]);

    // Save the order and notifications before sending emails
    $db->commit();

    // Email errors should not undo a successfully saved order
    try {
        ob_start();

        notifyAdminNewOrder(
            $refill_id,
            $customer['name'],
            $data['total_amount'],
            $data['pickup_datetime'],
            $data['delivery_datetime']
        );

        $emailOutput = ob_get_clean();

        if ($emailOutput !== '') {
            error_log('Admin email output: ' . $emailOutput);
        }
    } catch (Throwable $emailError) {
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        error_log(
            'Admin order email failed: ' . $emailError->getMessage()
        );
    }

    try {
        ob_start();

        sendCustomerOrderPlaced(
            $customer['email'],
            $customer['name'],
            $refill_id,
            $data['total_amount'],
            $data['pickup_datetime'],
            $data['delivery_datetime']
        );

        $emailOutput = ob_get_clean();

        if ($emailOutput !== '') {
            error_log('Customer email output: ' . $emailOutput);
        }
    } catch (Throwable $emailError) {
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        error_log(
            'Customer order email failed: ' . $emailError->getMessage()
        );
    }

    // Return valid JSON
    echo json_encode([
        'success' => true,
        'message' => 'Your water refill order has been placed successfully.',
        'order_id' => $refill_id
    ]);

} catch (Throwable $e) {
    if ($db instanceof PDO && $db->inTransaction()) {
        $db->rollBack();
    }

    error_log('Order placement error: ' . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to process your order. Please try again.'
    ]);
}

exit;
