<?php
require_once '../Database/db_connection.php';
header('Content-Type: application/json');

// Enable errors for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $database = new Database();
    $db = $database->getConnect();

    // Get JSON input
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'] ?? null;
    $password = $data['pass'] ?? '';

    if (!$id || !$password) {
        echo json_encode(["success" => false, "message" => "Missing required data."]);
        exit;
    }

    // Fetch admin account
    $query = "SELECT password_hash FROM users WHERE role = 'admin' LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        echo json_encode(["success" => false, "message" => "No admin account found."]);
        exit;
    }

    // Verify password
    if (!password_verify($password, $admin['password_hash'])) {
        echo json_encode(["success" => false, "message" => "Invalid admin password."]);
        exit;
    }

    // Delete the item
    $deleteQuery = "DELETE FROM inventory WHERE item_id = :id";
    $deleteStmt = $db->prepare($deleteQuery);
    $deleteStmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($deleteStmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to delete item."]);
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
