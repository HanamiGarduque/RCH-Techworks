<?php
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('admin');

require_once '../Database/db_connection.php';

header('Content-Type: application/json');

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
$allowedStatuses = ['Available', 'In-Use', 'Lost/Damaged'];
if (!in_array($status, $allowedStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status value.']);
    exit;
}

try {
    $db->beginTransaction();

    // Check if gallon exists and get current details
    $checkGallon = $db->prepare("SELECT 
        ownership_id, 
        status, 
        current_owner_id,
        gallon_type,
        code_value
    FROM gallon_ownership 
    WHERE ownership_id = :id 
    LIMIT 1");
    $checkGallon->bindParam(':id', $id, PDO::PARAM_INT);
    $checkGallon->execute();

    if ($checkGallon->rowCount() === 0) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Gallon not found.']);
        exit;
    }

    $currentGallon = $checkGallon->fetch(PDO::FETCH_ASSOC);
    
    // Store previous values for activity log
    $previousStatus = $currentGallon['status'];
    $currentOwnerId = $currentGallon['current_owner_id'];

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
        // CREATE ACTIVITY LOG ENTRY
        $actionType = 'status_change';
        
        // Create descriptive notes based on status change
        $statusChangeMap = [
            'Available' => 'marked as available for use',
            'In-Use' => 'marked as in-use by customer',
            'Lost/Damaged' => 'marked as lost or damaged'
        ];
        
        $logNotes = "Gallon status manually changed from '{$previousStatus}' to '{$status}'. " .
                    "Gallon (Code: {$currentGallon['code_value']}, Type: {$currentGallon['gallon_type']}) " .
                    "was {$statusChangeMap[$status]}.";
        
        $logQuery = "INSERT INTO gallon_activity_logs 
                    (ownership_id, action_type, performed_by, previous_owner_id, new_owner_id, previous_status, new_status, notes) 
                    VALUES 
                    (:ownership_id, :action_type, :performed_by, :previous_owner, :new_owner, :prev_status, :new_status, :notes)";
        
        $logStmt = $db->prepare($logQuery);
        $logStmt->bindParam(':ownership_id', $id, PDO::PARAM_INT);
        $logStmt->bindParam(':action_type', $actionType);
        $logStmt->bindParam(':performed_by', $_SESSION['user_id'], PDO::PARAM_INT);
        $logStmt->bindParam(':previous_owner', $currentOwnerId, PDO::PARAM_INT);
        $logStmt->bindParam(':new_owner', $currentOwnerId, PDO::PARAM_INT); // Owner stays same for status change
        $logStmt->bindParam(':prev_status', $previousStatus);
        $logStmt->bindParam(':new_status', $status);
        $logStmt->bindParam(':notes', $logNotes);
        
        if (!$logStmt->execute()) {
            // Log error but don't fail the transaction
            error_log('Warning: Failed to create activity log for status change');
        }

        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Status updated successfully.',
            'new_status' => $status,
            'previous_status' => $previousStatus
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