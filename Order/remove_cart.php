<?php
require_once '../Database/db_connection.php';
$database = new Database();
$db = $database->getConnect();

$response = ["success" => false];

if (isset($_POST['cart_id'])) {
    $cartId = $_POST['cart_id'];

    $stmt = $db->prepare("DELETE FROM cart WHERE cart_id = ?");
    $success = $stmt->execute([$cartId]);

    if ($success) {
        $response["success"] = true;
    }
}

echo json_encode($response);
?>
