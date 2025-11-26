<?php
require_once __DIR__ . '/../session.php';
require_once '../Database/db_connection.php';
redirectIfRoleMismatch('admin'); // only admins can access

$database = new Database();
$db = $database->getConnect();

// Debugging during development
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$item_id = $data['item_id'] ?? null;
$gallon_type = $data['gallon_type'] ?? null;
$quantity = isset($data['quantity']) ? intval($data['quantity']) : 1;

$status = 'Available';
$current_owner_id = 10; // default owner (station)

if (!$item_id || !$gallon_type) {
    echo json_encode(['success' => false, 'message' => 'Missing item ID or gallon type.']);
    exit;
}

if ($quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid quantity.']);
    exit;
}

// Prepare insert statement
$query = "INSERT INTO gallon_ownership (item_id, current_owner_id, gallon_type, status)
          VALUES (:item_id, :current_owner_id, :gallon_type, :status)";
$stmt = $db->prepare($query);

// Insert multiple gallons based on quantity
$inserted = 0;

for ($i = 0; $i < $quantity; $i++) {
    $stmt->bindParam(':item_id', $item_id);
    $stmt->bindParam(':current_owner_id', $current_owner_id);
    $stmt->bindParam(':gallon_type', $gallon_type);
    $stmt->bindParam(':status', $status);

    if ($stmt->execute()) {
        $inserted++;
    }
}

if ($inserted === $quantity) {
    echo json_encode(['success' => true, 'message' => "$inserted gallon(s) added successfully."]);
} else {
    echo json_encode([
        'success' => false,
        'message' => "Inserted $inserted out of $quantity gallons. Some failed."
    ]);
}
?>
