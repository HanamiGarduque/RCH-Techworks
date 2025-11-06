<?php
require_once '../Database/db_connection.php';
$database = new Database();
$db = $database->getConnect();

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? null;
$ownerName = isset($data['ownerName']) ? trim($data['ownerName']) : '';
$status = $data['status'] ?? null;

// ✅ Ensure Gallon ID exists
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Missing Gallon ID.']);
    exit;
}

try {
    $db->beginTransaction();

    $updateFields = [];
    $params = [':id' => $id];

    // ✅ If ownerName is provided (not empty), update owner
    if (!empty($ownerName)) {
        // Check if customer exists
        $checkUser = $db->prepare("SELECT user_id FROM users WHERE name = :name LIMIT 1");
        $checkUser->bindParam(':name', $ownerName);
        $checkUser->execute();

        if ($checkUser->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'Customer does not exist.']);
            exit;
        }

        $user = $checkUser->fetch(PDO::FETCH_ASSOC);
        $params[':owner_id'] = $user['user_id'];
        $updateFields[] = "owner_id = :owner_id";
    }

    // ✅ If status is provided (not null or empty), update status
    if (!empty($status)) {
        $params[':status'] = $status;
        $updateFields[] = "status = :status";
    }

    // ✅ If nothing was sent (neither owner nor status)
    if (empty($updateFields)) {
        echo json_encode(['success' => false, 'message' => 'No updates were made.']);
        exit;
    }

    // ✅ Build dynamic query
    $sql = "UPDATE gallon_ownership SET " . implode(', ', $updateFields) . " WHERE ownership_id = :id";
    $stmt = $db->prepare($sql);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    if ($stmt->execute()) {
        $db->commit();
        echo json_encode(['success' => true]);
    } else {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to update record.']);
    }

} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
