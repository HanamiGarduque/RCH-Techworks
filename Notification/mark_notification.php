<?php
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('customer');
require_once '../Database/db_connection.php';

header('Content-Type: application/json');

if (!isset($_POST['notification_id']) || $_POST['notification_id'] === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing notification_id']);
    exit;
}

$user_id = $_SESSION['id'] ?? $_SESSION['user_id'] ?? $_SESSION['customer_id'] ?? null;
if (!$user_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User ID not found in session']);
    exit;
}

$notification_id = $_POST['notification_id'];
$database = new Database();
$db = $database->getConnect();

try {
    // FIX: only mark THIS customer's notification, not anyone's
    $stmt = $db->prepare("UPDATE notifications SET status='read' WHERE notification_id=:id AND user_id=:uid");
    $stmt->execute([':id' => $notification_id, ':uid' => $user_id]);
    echo json_encode(['success' => true, 'updated' => $stmt->rowCount()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>