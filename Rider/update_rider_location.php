<?php
// update_rider_location.php
session_start();
require_once __DIR__ . '/../Database/db_connection.php';
$db = new Database();
$conn = $db->getConnect();

$rider_id = $_SESSION['user_id'] ?? null;

$input = json_decode(file_get_contents('php://input'), true);
$lat = isset($input['latitude']) ? trim($input['latitude']) : null;
$lng = isset($input['longitude']) ? trim($input['longitude']) : null;
$post_user_id = isset($input['user_id']) ? intval($input['user_id']) : null;
$order_id = isset($input['order_id']) ? intval($input['order_id']) : 0; // <-- NEW: refill_id

$use_user_id = $rider_id ?: $post_user_id;
if (!$use_user_id || !$lat || !$lng) {
    http_response_code(400);
    echo json_encode(['error' => 'missing data']);
    exit;
}

try {
    // 1. Keep old table (for customer location / history)
    $stmt = $conn->prepare("INSERT INTO loc_history (user_id, latitude, longitude, created_at) VALUES (:uid, :lat, :lng, NOW())");
    $stmt->execute([':uid' => $use_user_id, ':lat' => $lat, ':lng' => $lng]);

    // 2. NEW: also write to per-order table so admin can read by order_id
    if ($order_id > 0) {
        $stmt2 = $conn->prepare("INSERT INTO rider_location_history (order_id, latitude, longitude, timestamp) VALUES (:oid, :lat, :lng, NOW())");
        $stmt2->execute([':oid' => $order_id, ':lat' => $lat, ':lng' => $lng]);
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}