<?php
// update_rider_location.php - RIDER GPS
// RIDER CAN BE ANYWHERE (e.g. lives in San Jose, Batangas).
// Only CUSTOMER DELIVERY ADDRESS must be in Lipa - that check lives in
// order_water_refill.php / update_address.php, NOT here.
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../Database/db_connection.php';
$db = new Database();
$conn = $db->getConnect();

$rider_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;

$input = json_decode(file_get_contents('php://input'), true);
$lat = isset($input['latitude']) ? trim($input['latitude']) : null;
$lng = isset($input['longitude']) ? trim($input['longitude']) : null;
$post_user_id = isset($input['user_id']) ? intval($input['user_id']) : null;
$order_id = isset($input['order_id']) ? intval($input['order_id']) : 0;

$use_user_id = $rider_id ?: $post_user_id;
if (!$use_user_id || $lat === null || $lng === null || $lat === '' || $lng === '') {
    http_response_code(400);
    echo json_encode(['error' => 'missing data']);
    exit;
}

$lat_f = floatval($lat);
$lng_f = floatval($lng);

// Basic sanity check only - NOT a Lipa block.
// Valid world coords + rough Philippines box so a bad GPS (0,0) is ignored,
// but San Jose / Tanauan / Malvar / anywhere else is ACCEPTED.
if ($lat_f < -90 || $lat_f > 90 || $lng_f < -180 || $lng_f > 180) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid coordinates']);
    exit;
}
if ($lat_f == 0 && $lng_f == 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid GPS fix (0,0)']);
    exit;
}

try {
    // 1. Keep old table (for customer location / history)
    $stmt = $conn->prepare("INSERT INTO loc_history (user_id, latitude, longitude, created_at) VALUES (:uid, :lat, :lng, NOW())");
    $stmt->execute([':uid' => $use_user_id, ':lat' => $lat, ':lng' => $lng]);

    // 2. Also write to per-order table so admin/customer can read by order_id
    if ($order_id > 0) {
        $stmt2 = $conn->prepare("INSERT INTO rider_location_history (order_id, latitude, longitude, timestamp) VALUES (:oid, :lat, :lng, NOW())");
        $stmt2->execute([':oid' => $order_id, ':lat' => $lat, ':lng' => $lng]);
    }

    echo json_encode(['success' => true, 'latitude' => $lat_f, 'longitude' => $lng_f]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
