<?php
require_once '../Database/db_connection.php';
$database = new Database();
$db = $database->getConnect();

$data = json_decode(file_get_contents("php://input"), true);
$id = intval($data['id'] ?? 0);
$ownerName = trim($data['ownerName'] ?? '');

if ($id <= 0 || $ownerName === '') {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Example update (adjust column/table names if needed)
$stmt = $db->prepare("UPDATE users 
                      SET name = :name 
                      WHERE user_id = (SELECT owner_id FROM gallon_ownership WHERE ownership_id = :id)");
$stmt->bindParam(':name', $ownerName);
$stmt->bindParam(':id', $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database update failed']);
}
