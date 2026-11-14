<?php
require_once '../Database/db_connection.php';
header('Content-Type: application/json; charset=utf-8');

$db = new Database();
$conn = $db->getConnect();

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
if ($order_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Missing order_id']);
    exit;
}

$loc = null;

// 1. Try per-order table first
$stmt = $conn->prepare("
    SELECT latitude, longitude, timestamp
    FROM rider_location_history
    WHERE order_id = :order_id
    ORDER BY timestamp DESC LIMIT 1
");
$stmt->bindValue(':order_id', $order_id, PDO::PARAM_INT);
$stmt->execute();
$loc = $stmt->fetch(PDO::FETCH_ASSOC);

// 2. Fallback: loc_history via rider_id from water_refills
if (!$loc || $loc['latitude'] === null) {
    $o = $conn->prepare("SELECT rider_id FROM water_refills WHERE refill_id = :oid LIMIT 1");
    $o->execute([':oid' => $order_id]);
    $order = $o->fetch(PDO::FETCH_ASSOC);

    if ($order && !empty($order['rider_id'])) {
        $l = $conn->prepare("
            SELECT latitude, longitude, created_at AS timestamp
            FROM loc_history
            WHERE user_id = :uid
            ORDER BY created_at DESC LIMIT 1
        ");
        $l->execute([':uid' => $order['rider_id']]);
        $loc = $l->fetch(PDO::FETCH_ASSOC);
    }
}

if (!$loc || $loc['latitude'] === null) {
    echo json_encode(['success' => false, 'error' => 'No rider location yet']);
    exit;
}

$age = time() - strtotime($loc['timestamp']);
echo json_encode([
    'success'   => true,
    'latitude'  => (float)$loc['latitude'],
    'longitude' => (float)$loc['longitude'],
    'timestamp' => $loc['timestamp'],
    'is_recent' => $age <= 60,
    'age_seconds' => $age
]);