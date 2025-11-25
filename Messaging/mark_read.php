<?php
// Prevent any output before JSON
ob_start();

// Set headers
header('Content-Type: application/json');

// Disable error display
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    require_once __DIR__ . '/../Database/db_connection.php';
    
    // Get current user from session
    $current_user = $_SESSION['id'] ?? null;

    if (!$current_user) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'No active session'
        ]);
        exit;
    }

    $contact_id = isset($_POST['contact_id']) ? (int)$_POST['contact_id'] : null;

    if (!$contact_id) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Contact ID is required'
        ]);
        exit;
    }

    $database = new Database();
    $db = $database->getConnect();

    $sql = "UPDATE messages 
            SET is_read = 1 
            WHERE receiver_id = :current_user 
              AND sender_id = :contact 
              AND is_read = 0";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':current_user' => $current_user,
        ':contact' => $contact_id
    ]);

    $updated_rows = $stmt->rowCount();

    ob_clean();
    echo json_encode([
        'success' => true,
        'updated_rows' => $updated_rows,
        'message' => 'Messages marked as read'
    ]);

} catch (PDOException $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error',
        'message' => $e->getMessage()
    ]);
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error', 
        'message' => $e->getMessage()
    ]);
}

ob_end_flush();
?>