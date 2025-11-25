<?php
session_start();
require_once '../Database/db_connection.php';
date_default_timezone_set('Asia/Manila');

$rider_name = $_SESSION['rider_name'] ?? "Rider";
$rider_user_id = $_SESSION['user_id'] ?? null;

// ============================================================================
// GET ACTIVE DELIVERY
// ============================================================================
$db = new Database();
$conn = $db->getConnect();

// Fetch active order for this rider
$activeOrder = null;
if ($rider_user_id) {
    $qr = $conn->prepare("
        SELECT w.refill_id, w.order_number, u.name AS customer_name, u.phone_number, u.address, u.latitude AS customer_lat, u.longitude AS customer_lng
        FROM water_refills w
        JOIN users u ON u.user_id = w.user_id
        WHERE w.rider_id = :rid AND w.order_status IN ('pending','on_delivery')
        ORDER BY w.refill_id DESC LIMIT 1
    ");
    $qr->bindParam(':rid', $rider_user_id, PDO::PARAM_INT);
    $qr->execute();
    $activeOrder = $qr->fetch(PDO::FETCH_ASSOC);
}

$customerName = $activeOrder['customer_name'] ?? "Customer";
$customerPhone = $activeOrder['phone_number'] ?? "—";
$deliveryAddress = $activeOrder['address'] ?? "—";
$orderId = $activeOrder['order_number'] ?? "—";
$customerLat = $activeOrder['customer_lat'] ?? 13.9587;   // default if not set
$customerLng = $activeOrder['customer_lng'] ?? 121.1444;
$refill_id = $activeOrder['refill_id'] ?? null;

// AJAX endpoint for rider location
if (isset($_GET['action']) && $_GET['action'] === 'rider_location' && $rider_user_id) {
    header('Content-Type: application/json; charset=utf-8');
    try {
        // Rider's latest GPS
        $s = $conn->prepare("SELECT latitude, longitude, created_at FROM loc_history WHERE user_id = :rid ORDER BY created_at DESC LIMIT 1");
        $s->bindParam(':rid', $rider_user_id, PDO::PARAM_INT);
        $s->execute();
        $loc = $s->fetch(PDO::FETCH_ASSOC);
        if ($loc && $loc['latitude'] !== null && $loc['longitude'] !== null) {
            echo json_encode([
                'latitude' => (float)$loc['latitude'],
                'longitude' => (float)$loc['longitude'],
                'timestamp' => $loc['created_at']
            ]);
        } else {
            echo json_encode(['error' => 'No location found']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>RCH Water - Rider Dashboard</title>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
#rider-map { width: 100%; height: 400px; border-radius: 0.75rem; overflow: hidden; }
body { font-family: 'Poppins', sans-serif; }
</style>
</head>
<body class="bg-gray-100">

<div class="p-8">
    <h1 class="text-2xl font-semibold text-gray-700 mb-4">Active Delivery</h1>
    <div class="border rounded-xl p-5 mb-6">
        <div id="rider-map"></div>
        <div class="mt-4">
            <div class="text-sm font-semibold">Customer:</div>
            <div><?= htmlspecialchars($customerName) ?> (<?= htmlspecialchars($customerPhone) ?>)</div>
            <div class="text-sm mt-1 text-gray-500">Address: <?= htmlspecialchars($deliveryAddress) ?></div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const riderLat = 13.9587;
const riderLng = 121.1444;
const customerLat = <?= json_encode($customerLat) ?>;
const customerLng = <?= json_encode($customerLng) ?>;
let map, riderMarker, customerMarker;

function initMap() {
    // Center map between rider & customer
    const centerLat = (riderLat + customerLat) / 2;
    const centerLng = (riderLng + customerLng) / 2;

    map = L.map('rider-map').setView([centerLat, centerLng], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Customer destination
    customerMarker = L.marker([customerLat, customerLng], { title: 'Customer Destination' }).addTo(map);
    customerMarker.bindPopup(`<b>Delivery Address</b><br><?= addslashes($deliveryAddress) ?>`);

    // Rider marker
    riderMarker = L.marker([riderLat, riderLng], { title: 'Rider (live)', icon: L.icon({iconUrl: 'https://cdn-icons-png.flaticon.com/512/684/684908.png', iconSize: [32,32]}) }).addTo(map);
    riderMarker.bindPopup(`<b>Rider</b><br>Loading...`);

    fetchRiderLocation();
    setInterval(fetchRiderLocation, 5000);
}

async function fetchRiderLocation() {
    try {
        const resp = await fetch(`?action=rider_location`, { cache: 'no-store' });
        const data = await resp.json();
        if (data.latitude && data.longitude) {
            const lat = parseFloat(data.latitude);
            const lng = parseFloat(data.longitude);
            riderMarker.setLatLng([lat, lng]);
            riderMarker.getPopup().setContent(`<b>Rider</b><br>Last updated: ${data.timestamp}`);
        }
    } catch (err) {
        console.error(err);
    }
}

document.addEventListener('DOMContentLoaded', initMap);
</script>

</body>
</html>
