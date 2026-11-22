<?php
require_once '../Database/db_connection.php';
require_once '../session.php';

header('Content-Type: application/json');

// Accept user_id from GET parameter, fallback to session id
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : $_SESSION['id'];

$database = new Database();
$db = $database->getConnect();

// 1. Fetch map_location if exists
$stmt = $db->prepare("SELECT latitude, longitude FROM map_location WHERE user_id = ?");
$stmt->execute([$user_id]);
$location = $stmt->fetch(PDO::FETCH_ASSOC);

if ($location) {
    // Lipa City only: ignore saved location if it is outside Lipa
    $lat = floatval($location['latitude']);
    $lng = floatval($location['longitude']);
    if ($lat >= 13.8500 && $lat <= 14.0300 && $lng >= 121.0500 && $lng <= 121.3000) {
        echo json_encode([
            "status" => "has_saved_location",
            "latitude" => $lat,
            "longitude" => $lng
        ]);
        exit;
    }
    // Outside Lipa, fall through to Lipa fallback below
    echo json_encode([
        "status" => "fallback",
        "latitude" => 13.9410,
        "longitude" => 121.1640
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

// Approximate address → call OpenStreetMap Nominatim, limited to Lipa City
$address = urlencode($user['address'] . ', Lipa City, Batangas, Philippines');
$url = "https://nominatim.openstreetmap.org/search?format=json&limit=1&q={$address}";
$response = file_get_contents($url);
$data = json_decode($response, true);

// If geocode works and result is inside Lipa, use it
if (!empty($data)) {
    $lat = floatval($data[0]['lat']);
    $lng = floatval($data[0]['lon']);
    if ($lat >= 13.8500 && $lat <= 14.0300 && $lng >= 121.0500 && $lng <= 121.3000) {
        echo json_encode([
            "status" => "approx_location",
            "latitude" => $lat,
            "longitude" => $lng
        ]);
        exit;
    }
}

// Fallback: center on Lipa City
echo json_encode([
    "status" => "fallback",
    "latitude" => 13.9410,
    "longitude" => 121.1640
]);