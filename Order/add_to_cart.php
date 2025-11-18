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

    $user_id = $data['user_id'] ?? null;
    $product_id = $data['product_id'] ?? null;
    $quantity = $data['quantity'] ?? 1;
    $unit_price = $data['unit_price'] ?? null;

    if (!$user_id || !$product_id || !$unit_price) {
        $response['message'] = 'Missing required fields';
        echo json_encode($response);
        exit;
    }

    $subtotal = $quantity * $unit_price;

    try {
        $database = new Database();
        $db = $database->getConnect();

        // Check if item already exists in cart
        $checkStmt = $db->prepare("SELECT quantity FROM cart WHERE user_id = :user_id AND product_id = :product_id");
        $checkStmt->execute([
            ':user_id' => $user_id,
            ':product_id' => $product_id
        ]);
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Update quantity and subtotal
            $newQty = $existing['quantity'] + $quantity;
            $updateStmt = $db->prepare("UPDATE cart SET quantity = :quantity, subtotal = :subtotal, updated_at = NOW() WHERE user_id = :user_id AND product_id = :product_id");
            $updateStmt->execute([
                ':quantity' => $newQty,
                ':subtotal' => $newQty * $unit_price,
                ':user_id' => $user_id,
                ':product_id' => $product_id
            ]);
        } else {
            // Insert new cart item
            $insertStmt = $db->prepare("INSERT INTO cart (user_id, product_id, quantity, unit_price, subtotal) VALUES (:user_id, :product_id, :quantity, :unit_price, :subtotal)");
            $insertStmt->execute([
                ':user_id' => $user_id,
                ':product_id' => $product_id,
                ':quantity' => $quantity,
                ':unit_price' => $unit_price,
                ':subtotal' => $subtotal
            ]);
        }

        $response['success'] = true;
        $response['message'] = 'Item added to cart successfully';
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
