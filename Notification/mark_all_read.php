<?php
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('admin'); // only admins can access

require_once '../Database/db_connection.php';

// Get user ID from session (try multiple possible variable names)
$user_id = null;

if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];
} elseif (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} elseif (isset($_SESSION['admin_id'])) {
    $user_id = $_SESSION['admin_id'];
}

// Return error if no user_id found
if (!$user_id) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'User ID not found in session'
    ]);
    exit;
}

$database = new Database();
$db = $database->getConnect();

try {
    // Update all unread notifications for this user to 'read'
    $stmt = $db->prepare("
        UPDATE notifications 
        SET status = 'read' 
        WHERE user_id = :user_id 
        AND status = 'unread'
    ");
    
    $stmt->execute([':user_id' => $user_id]);
    
    $rowCount = $stmt->rowCount();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'All notifications marked as read',
        'count' => $rowCount
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>