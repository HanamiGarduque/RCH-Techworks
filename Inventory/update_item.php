<?php
require_once '../Database/db_connection.php';
$database = new Database();
$conn = $database->getConnect();

if (isset($_POST['item_id'])) {
    $id = $_POST['item_id'];
    $name = $_POST['item_name'];
    $price = $_POST['price'];

    // Quantity may not be sent for gallons
    $qty = isset($_POST['quantity']) ? $_POST['quantity'] : null;

    // For Sale toggle (0 or 1)
    $for_sale = isset($_POST['for_sale']) ? $_POST['for_sale'] : 0;

    // Build query dynamically to handle optional quantity
    if ($qty !== null) {
        $query = "UPDATE inventory 
                  SET item_name = :name, total_quantity = :qty, price = :price, for_sale = :for_sale 
                  WHERE item_id = :id";
    } else {
        $query = "UPDATE inventory 
                  SET item_name = :name, price = :price, for_sale = :for_sale 
                  WHERE item_id = :id";
    }

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':for_sale', $for_sale, PDO::PARAM_INT);
    $stmt->bindParam(':id', $id);

    if ($qty !== null) {
        $stmt->bindParam(':qty', $qty, PDO::PARAM_INT);
    }

    if ($stmt->execute()) {
        echo "Item updated successfully!";
    } else {
        echo "Error updating item.";
    }
}
?>
