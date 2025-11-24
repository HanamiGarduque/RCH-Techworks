<?php
header('Content-Type: application/json');

ini_set('display_errors', 0); 
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once '../Database/db_connection.php';

$response = ['success' => false, 'message' => 'Unknown error'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
        exit;
    }

    $transaction_type = $data['transaction_type'] ?? null;
    $customer_name = $data['customer_name'] ?? 'Walk-in Customer';
    $admin_id = $data['admin_id'] ?? null;
    $transaction_number = $data['transaction_number'] ?? null;
    $cart = $data['cart'] ?? [];
    $subtotal = $data['subtotal'] ?? 0;
    $tax = $data['tax'] ?? 0;
    $total_amount = $data['total_amount'] ?? 0;
    $amount_paid = $data['amount_paid'] ?? 0;
    $change = $data['change'] ?? 0;

    if (!$admin_id || empty($cart) || !$total_amount || !$transaction_number) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    try {
        $database = new Database();
        $db = $database->getConnect();
        $db->beginTransaction();

        $stmt = $db->prepare("
            INSERT INTO orders (
                user_id, order_number, total_amount, order_status, transaction_type,
                customer_name, amount_paid, change_amount, created_at
            ) VALUES (
                :user_id, :order_number, :total_amount, 'Completed',
                :transaction_type, :customer_name, :amount_paid, :change_amount, NOW()
            )
        ");

        $stmt->execute([
            ':user_id' => $admin_id,
            ':order_number' => $transaction_number,
            ':total_amount' => $total_amount,
            ':transaction_type' => $transaction_type,
            ':customer_name' => $customer_name,
            ':amount_paid' => $amount_paid,
            ':change_amount' => $change
        ]);

        $order_id = $db->lastInsertId();

        // Insert order items
        $stmtItem = $db->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal)
            VALUES (:order_id, :product_id, :quantity, :unit_price, :subtotal)
        ");

        foreach ($cart as $item) {
            // Frontend now sends 'product_id', 'quantity', 'unit_price'
            $product_id = $item['product_id'] ?? null;
            $quantity   = $item['quantity'] ?? 0;
            $unit_price = $item['unit_price'] ?? 0;
            
            if (!$product_id || !$quantity || !$unit_price) {
                throw new Exception("Invalid cart item data");
            }
            
            $item_subtotal = $quantity * $unit_price;

            $stmtItem->execute([
                ':order_id' => $order_id,
                ':product_id' => $product_id,
                ':quantity' => $quantity,
                ':unit_price' => $unit_price,
                ':subtotal' => $item_subtotal
            ]);

            // Update inventory EXCEPT gallons
            $updateInventory = $db->prepare("
                UPDATE inventory 
                SET total_quantity = total_quantity - :quantity
                WHERE item_id = :item_id
                AND item_name NOT IN ('18.9L Round Gallon', '20L Slim Gallon', '10L Slim Gallon')
            ");

            $updateInventory->execute([
                ':quantity' => $quantity,
                ':item_id' => $product_id
            ]);
        }

        // Create notification
        $notifStmt = $db->prepare("
            INSERT INTO notifications (user_id, title, message, link, status, created_at)
            VALUES (:user_id, :title, :message, :link, 'unread', NOW())
        ");

        $notifStmt->execute([
            ':user_id' => $admin_id,
            ':title' => 'POS Sale Completed',
            ':message' => "Walk-in sale completed. Transaction #{$transaction_number} — Total: ₱{$total_amount}",
            ':link' => "order_details.php?order_id={$order_id}"
        ]);

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Sale completed successfully',
            'order_id' => $order_id,
            'transaction_number' => $transaction_number
        ]);

    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>