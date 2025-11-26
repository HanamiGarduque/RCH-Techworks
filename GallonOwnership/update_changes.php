<?php
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('admin'); // only admins can access

require_once '../Database/db_connection.php';
$database = new Database();
$db = $database->getConnect();

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? null;
$status = $data['status'] ?? null;

// Validate required fields
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Missing Gallon ID.']);
    exit;
}

if (!$status) {
    echo json_encode(['success' => false, 'message' => 'Status is required.']);
    exit;
}

// Validate status value
$allowedStatuses = ['Active', 'Lost/Damaged'];
if (!in_array($status, $allowedStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status value.']);
    exit;
}

try {
    $db->beginTransaction();

    // Check if gallon exists
    $checkGallon = $db->prepare("SELECT ownership_id, status FROM gallon_ownership WHERE ownership_id = :id LIMIT 1");
    $checkGallon->bindParam(':id', $id, PDO::PARAM_INT);
    $checkGallon->execute();

    if ($checkGallon->rowCount() === 0) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Gallon not found.']);
        exit;
    }

    $currentGallon = $checkGallon->fetch(PDO::FETCH_ASSOC);
    
    // Check if status is actually changing
    if ($currentGallon['status'] === $status) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Status is already set to ' . $status . '.']);
        exit;
    }

    // Update the status
    $sql = "UPDATE gallon_ownership SET status = :status WHERE ownership_id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $db->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Status updated successfully.',
            'new_status' => $status
        ]);
    } else {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to update gallon status.']);
    }

} catch (PDOException $e) {
    $db->rollBack();
    error_log('Database Error in update_changes.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
} catch (Exception $e) {
    $db->rollBack();
    error_log('Error in update_changes.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while updating.']);
}
?>