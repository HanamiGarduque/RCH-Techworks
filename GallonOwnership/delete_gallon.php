<?php
require_once '../Database/db_connection.php';
header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->getConnect();

    // Get JSON input from fetch()
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'] ?? null;
    $password = $data['pass'] ?? '';

    if (!$id || !$password) {
        echo json_encode(["success" => false, "message" => "Missing required data."]);
        exit;
    }

    //Verify admin password
    $query = "SELECT password_hash FROM users WHERE role = 'admin' LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();

    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        echo json_encode(["success" => false, "message" => "No admin account found."]);
        exit;
    }

    if (!password_verify($password, $admin['password_hash'])) {
        echo json_encode(["success" => false, "message" => "Invalid admin password."]);
        exit;
    }

    // Delete gallon record
    $deleteQuery = "DELETE FROM gallon_ownership WHERE ownership_id = :id";
    $deleteStmt = $db->prepare($deleteQuery);
    $deleteStmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($deleteStmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to delete gallon record."]);
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>
