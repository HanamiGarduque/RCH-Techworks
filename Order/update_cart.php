<?php
require_once '../Database/db_connection.php';
$database = new Database();
$db = $database->getConnect();

$response = ['success' => false];

if (isset($_POST['cart_id'], $_POST['quantity'], $_POST['action'])) {
    $cartId = $_POST['cart_id'];
    $quantity = (int) $_POST['quantity'];
    $action = $_POST['action'];

    // Adjust quantity
    if ($action === "increment") $quantity++;
    elseif ($action === "decrement" && $quantity > 1) $quantity--;

    // Update in DB
    $stmt = $db->prepare("UPDATE cart SET quantity = ?, subtotal = ? * unit_price, updated_at = NOW() WHERE cart_id = ?");
    $success = $stmt->execute([$quantity, $quantity, $cartId]);

    if ($success) {
        $stmt2 = $db->prepare("SELECT subtotal FROM cart WHERE cart_id = ?");
        $stmt2->execute([$cartId]);
        $subtotal = $stmt2->fetchColumn();

        $response['success'] = true;
        $response['quantity'] = $quantity;
        $response['subtotal'] = number_format($subtotal, 2);
    }
}

echo json_encode($response);
