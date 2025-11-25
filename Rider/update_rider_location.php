<?php
// update_rider_location.php
session_start();
require_once __DIR__ . '/../Database/db_connection.php';
$db = new Database();
$conn = $db->getConnect();

// Basic auth check: only allow logged in riders
$rider_id = $_SESSION['user_id'] ?? null;

// If you prefer token-based or POST user_id from request (less secure), adapt.
// Accept JSON body
$input = json_decode(file_get_contents('php://input'), true);
$lat = isset($input['latitude']) ? trim($input['latitude']) : null;
$lng = isset($input['longitude']) ? trim($input['longitude']) : null;
$post_user_id = isset($input['user_id']) ? intval($input['user_id']) : null;

// Choose final user id (session preferred)
$use_user_id = $rider_id ?: $post_user_id;
if (!$use_user_id || !$lat || !$lng) {
    http_response_code(400);
    echo json_encode(['error' => 'missing data']);
    exit;
}

try {
    $stmt = $conn->prepare("INSERT INTO loc_history (user_id, latitude, longitude, created_at) VALUES (:uid, :lat, :lng, NOW())");
    $stmt->execute([
        ':uid' => $use_user_id,
        ':lat' => $lat,
        ':lng' => $lng
    ]);
    echo json_encode(['success'=>true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()]);
}
