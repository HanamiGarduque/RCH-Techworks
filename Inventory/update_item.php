<?php
require_once '../Database/db_connection.php';
$database = new Database();
$conn = $database->getConnect();

if (isset($_POST['item_id'])) {
    $id = $_POST['item_id'];
    $name = $_POST['item_name'];
    $qty = $_POST['quantity'];
    $price = $_POST['price'];

    $query = "UPDATE inventory 
              SET item_name = :name, total_quantity = :qty, price = :price 
              WHERE item_id = :id";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':qty', $qty);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        echo "Item updated successfully!";
    } else {
        echo "Error updating item.";
    }
}
?>
