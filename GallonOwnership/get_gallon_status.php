<?php
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('admin'); // only admins can access

require_once '../Database/db_connection.php';

// Set JSON header
header('Content-Type: application/json');

// Get the gallon ID from query parameter
$id = $_GET['id'] ?? null;

// Validate ID
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Missing Gallon ID.']);
    exit;
}

if (!is_numeric($id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid Gallon ID.']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnect();

    // Fetch gallon information
    $query = "SELECT 
                ownership_id,
                gallon_type,
                status,
                code_value,
                current_owner_id,
                qr_image
              FROM gallon_ownership 
              WHERE ownership_id = :id 
              LIMIT 1";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Gallon not found.']);
        exit;
    }

    $gallon = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get owner name if exists
    $ownerName = null;
    if ($gallon['current_owner_id']) {
        $ownerQuery = "SELECT name FROM users WHERE user_id = :user_id LIMIT 1";
        $ownerStmt = $db->prepare($ownerQuery);
        $ownerStmt->bindParam(':user_id', $gallon['current_owner_id'], PDO::PARAM_INT);
        $ownerStmt->execute();
        
        if ($ownerStmt->rowCount() > 0) {
            $owner = $ownerStmt->fetch(PDO::FETCH_ASSOC);
            $ownerName = $owner['name'];
        }
    }

    // Return gallon data
    echo json_encode([
        'success' => true,
        'data' => [
            'ownership_id' => $gallon['ownership_id'],
            'gallon_type' => $gallon['gallon_type'],
            'status' => $gallon['status'],
            'code_value' => $gallon['code_value'],
            'owner_name' => $ownerName,
            'qr_image' => $gallon['qr_image']
        ]
    ]);

} catch (PDOException $e) {
    error_log('Database Error in get_gallon_status.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
} catch (Exception $e) {
    error_log('Error in get_gallon_status.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while fetching gallon data.']);
}
?>