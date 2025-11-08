<?php
require_once '../Database/db_connection.php';
header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->getConnect();

    // Check if form was submitted with POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(["success" => false, "message" => "Invalid request method."]);
        exit;
    }

    // Get POST data
    $category = $_POST['category'] ?? '';
    $item_name = $_POST['item_name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    $quantity = $_POST['quantity'] ?? '';
    $image = $_FILES['item_image'] ?? null;

    // Validate
    if (!$category || !$item_name || !$price || $quantity === '' || !$image) {
        echo json_encode(["success" => false, "message" => "All fields are required."]);
        exit;
    }

    // Handle file upload
    $targetDir = '../Assets/';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $fileName = basename($image['name']);
    $targetFilePath = $targetDir . $fileName;
    $relativePath = '../Assets/' . $fileName;

    if (!move_uploaded_file($image['tmp_name'], $targetFilePath)) {
        echo json_encode(["success" => false, "message" => "Failed to upload image."]);
        exit;
    }

    // Insert into inventory table
    $query = "INSERT INTO inventory (category, item_name, description, price, total_quantity, item_image) 
              VALUES (:category, :item_name, :description, :price, :quantity, :item_image)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':category', $category);
    $stmt->bindParam(':item_name', $item_name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
    $stmt->bindParam(':item_image', $relativePath);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Item added successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to add item to inventory."]);
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>
