<?php
require_once '../Database/db_connection.php';
require_once '../session.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnect();

$user_id = $_SESSION['id'];
$data = json_decode(file_get_contents('php://input'), true);

$lat = isset($data['latitude']) ? floatval($data['latitude']) : 0;
$lng = isset($data['longitude']) ? floatval($data['longitude']) : 0;

// Lipa City only: do not save location outside Lipa
if ($lat < 13.8500 || $lat > 14.0300 || $lng < 121.0500 || $lng > 121.3000) {
    echo json_encode(["status" => "error", "message" => "We only deliver in Lipa City."]);
    exit;
}

// Check if record exists
$check = $db->prepare("SELECT loc_id FROM map_location WHERE user_id = ?");
$check->execute([$user_id]);
$exists = $check->fetchColumn();

if ($exists) {
    // Update
    $update = $db->prepare("
        UPDATE map_location SET latitude = ?, longitude = ?, updated_at = NOW()
        WHERE user_id = ?
    ");
    $update->execute([$lat, $lng, $user_id]);
} else {
    // Insert
    $insert = $db->prepare("
        INSERT INTO map_location (user_id, latitude, longitude, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    $insert->execute([$user_id, $lat, $lng]);
}

echo json_encode(["status" => "success"]);