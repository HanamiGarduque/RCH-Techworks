<?php
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('customer'); // FIX: was 'admin', so customers were blocked

require_once '../Database/db_connection.php';

// Get user ID from session (same keys as your other customer files)
$user_id = null;
if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];
} elseif (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} elseif (isset($_SESSION['customer_id'])) {
    $user_id = $_SESSION['customer_id'];
}

if (!$user_id) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User ID not found in session']);
    exit;
}

$database = new Database();
$db = $database->getConnect();

try {
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
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>