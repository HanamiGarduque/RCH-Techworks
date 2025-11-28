<?php
require_once '../Database/db_connection.php';
require_once '../session.php';

header('Content-Type: application/json');

$user_id = $_SESSION['id'];

$database = new Database();
$db = $database->getConnect();

// 1. Fetch map_location if exists
$stmt = $db->prepare("SELECT latitude, longitude FROM map_location WHERE user_id = ?");
$stmt->execute([$user_id]);
$location = $stmt->fetch(PDO::FETCH_ASSOC);

if ($location) {
    echo json_encode([
        "status" => "has_saved_location",
        "latitude" => floatval($location['latitude']),
        "longitude" => floatval($location['longitude'])
    ]);
    exit;
}

// 2. If no map_location, get user's address
$stmt2 = $db->prepare("SELECT address FROM users WHERE user_id = ?");
$stmt2->execute([$user_id]);
$user = $stmt2->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(["status" => "error", "message" => "User not found"]);
    exit;
}

// Approximate address → call OpenStreetMap Nominatim
$address = urlencode($user['address']);
$url = "https://nominatim.openstreetmap.org/search?format=json&q={$address}";
$response = file_get_contents($url);
$data = json_decode($response, true);

// If geocode works, use first result
if (!empty($data)) {
    echo json_encode([
        "status" => "approx_location",
        "latitude" => floatval($data[0]['lat']),
        "longitude" => floatval($data[0]['lon'])
    ]);
} else {
    // fallback: center on Philippines
    echo json_encode([
        "status" => "fallback",
        "latitude" => 12.8797,
        "longitude" => 121.7740
    ]);
}
