<?php
require_once '../Database/db_connection.php';
session_start();

// Get current user from session (fallback to hardcoded for testing)
$current_user = $_SESSION['user_id'] ?? 3;

$contact_id = isset($_POST['contact_id']) ? intval($_POST['contact_id']) : null;

if (!$contact_id) {
    echo json_encode(['success' => false, 'message' => 'Contact ID is required.']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnect();

    $sql = "UPDATE messages 
            SET is_read = 1 
            WHERE receiver_id = :current_user 
              AND sender_id = :contact 
              AND is_read = 0"; // Only update unread messages

    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':current_user' => $current_user,
        ':contact' => $contact_id
    ]);

    echo json_encode(['success' => true, 'updated_rows' => $stmt->rowCount()]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
