<?php
require_once '../Database/db_connection.php';
$database = new Database();
$db = $database->getConnect();

// For debugging during development
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$item_id = $data['item_id'] ?? null;
$gallon_type = $data['gallon_type'] ?? null;
$status = 'Available';
$owner_id = 1; // default owner

if (!$item_id || !$gallon_type) {
    echo json_encode(['success' => false, 'message' => 'Missing item ID or gallon type.']);
    exit;
}

// Insert new gallon
$query = "INSERT INTO gallon_ownership (item_id, owner_id, gallon_type, status)
          VALUES (:item_id, :owner_id, :gallon_type, :status)";
$stmt = $db->prepare($query);
$stmt->bindParam(':item_id', $item_id);
$stmt->bindParam(':owner_id', $owner_id);
$stmt->bindParam(':gallon_type', $gallon_type);
$stmt->bindParam(':status', $status);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add new gallon.']);
}
?>
