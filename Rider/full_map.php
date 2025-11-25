<?php 
session_start();
require_once '../Database/db_connection.php';

// Validate user_id
$user_id = 9;
if ($user_id <= 0) {
    echo "Invalid user.";
    exit;
}

// Database connection
$db = new Database();
$conn = $db->getConnect();

// Fetch customer details
$customer_stmt = $conn->prepare("SELECT name, address FROM users WHERE user_id = :id");
$customer_stmt->bindParam(':id', $user_id);
$customer_stmt->execute();
$customer = $customer_stmt->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    echo "Customer not found.";
    exit;
}

// Fetch customer's map coordinates
$map_stmt = $conn->prepare("SELECT latitude, longitude FROM map_location WHERE user_id = :id LIMIT 1");
$map_stmt->bindParam(':id', $user_id);
$map_stmt->execute();
$map = $map_stmt->fetch(PDO::FETCH_ASSOC);

// Fallback coordinates if missing
$destLat = (!empty($map['latitude'])) ? floatval($map['latitude']) : 13.8800;
$destLng = (!empty($map['longitude'])) ? floatval($map['longitude']) : 121.1100;

$customer_name = $customer['name'];
$address = $customer['address'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Live GPS | <?= htmlspecialchars($customer_name) ?></title>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css"/>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<style>
body { 
    margin: 0; 
    font-family: 'Poppins', sans-serif; 
    background: #f4f6fa; 
    color: #333; 
    overflow: hidden; 
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}

.phone-container {
    background: white;
    width: 375px;
    height: 750px;
    position: relative;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
}

/* Header */
.header {
    background: linear-gradient(135deg, #60a5fa 0%, #2563eb 100%);
    color: white;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative;
    z-index: 1000;
}

.back-btn {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 20px;
    transition: all 0.2s;
}

.back-btn:hover {
    background: rgba(255,255,255,0.3);
}

.header-title {
    flex: 1;
    text-align: center;
    margin: 0 12px;
}

.header-title h2 {
    font-size: 16px;
    font-weight: 600;
    margin: 0;
    margin-bottom: 2px;
}

.header-title p {
    font-size: 12px;
    opacity: 0.9;
    margin: 0;
}

.header-actions {
    display: flex;
    gap: 8px;
}

.icon-btn {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 18px;
    transition: all 0.2s;
}

.icon-btn:hover {
    background: rgba(255,255,255,0.3);
}

/* Map Container */
#map { 
    height: calc(100% - 180px);
    width: 100%;
    position: relative;
}

/* ETA Overlay */
.eta-overlay {
    position: absolute;
    top: 16px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(255,255,255,0.95);
    padding: 10px 20px;
    border-radius: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 1000;
    display: flex;
    align-items: center;
    gap: 8px;
}

.eta-overlay i {
    color: #2563eb;
    font-size: 16px;
}

.eta-text {
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
}

/* Bottom Action Bar */
.bottom-bar {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    padding: 16px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    gap: 12px;
}

.btn {
    flex: 1;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    padding: 14px 20px;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-family: 'Poppins', sans-serif;
}

.btn-primary { 
    background: linear-gradient(135deg, #60a5fa 0%, #2563eb 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
}

.btn-secondary { 
    background: #f1f5f9;
    color: #2563eb;
    border: 2px solid #e2e8f0;
}

.btn-secondary:hover {
    background: #e2e8f0;
    border-color: #cbd5e1;
}

/* Customer Info Card */
.customer-info {
    position: absolute;
    bottom: 120px;
    left: 16px;
    right: 16px;
    background: white;
    padding: 16px;
    border-radius: 16px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    z-index: 999;
}

.customer-name {
    font-size: 16px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.customer-name i {
    color: #2563eb;
}

.customer-address {
    font-size: 13px;
    color: #64748b;
    display: flex;
    align-items: start;
    gap: 8px;
    line-height: 1.5;
}

.customer-address i {
    color: #f59e0b;
    margin-top: 2px;
    flex-shrink: 0;
}

/* Leaflet popup customization */
.leaflet-popup-content-wrapper {
    border-radius: 12px;
    font-family: 'Poppins', sans-serif;
}

.leaflet-popup-content {
    margin: 12px;
    font-size: 13px;
}

@media (max-width: 480px) {
    .phone-container {
        width: 100vw;
        height: 100vh;
        border-radius: 0;
    }
}
</style>
</head>
<body>

<div class="phone-container">
    <!-- Header -->
    <div class="header">
        <button class="back-btn" onclick="history.back()">
            <i class="bi bi-arrow-left"></i>
        </button>
        <div class="header-title">
            <h2>Live Tracking</h2>
            <p>Delivery in progress</p>
        </div>
        <div class="header-actions">
            <button class="icon-btn" onclick="centerMap()">
                <i class="bi bi-geo-alt-fill"></i>
            </button>
        </div>
    </div>

    <!-- Map -->
    <div id="map">
        <!-- ETA Overlay -->
        <div class="eta-overlay" id="eta">
            <i class="bi bi-clock-fill"></i>
            <span class="eta-text">Calculating route...</span>
        </div>
    </div>

    <!-- Customer Info Card -->
    <div class="customer-info">
        <div class="customer-name">
            <i class="bi bi-person-circle"></i>
            <?= htmlspecialchars($customer_name) ?>
        </div>
        <div class="customer-address">
            <i class="bi bi-geo-alt-fill"></i>
            <span><?= htmlspecialchars($address) ?></span>
        </div>
    </div>

    <!-- Bottom Action Bar -->
    <div class="bottom-bar">
        <button class="btn btn-primary" onclick="window.location.href='qr_scan.php?user_id=<?= $user_id ?>&customer=<?= urlencode($customer_name) ?>&lat=<?= $destLat ?>&lng=<?= $destLng ?>'">
            <i class="bi bi-qr-code-scan"></i>
            Scan QR
        </button>
        <button class="btn btn-secondary" onclick="messageCustomer()">
            <i class="bi bi-chat-dots-fill"></i>
            Message
        </button>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js"></script>

<script>
// Set map to customer's stored coordinates
let map = L.map('map').setView([<?= $destLat ?>, <?= $destLng ?>], 15);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(map);

// Customer marker with custom icon
const customerIcon = L.divIcon({
    html: '<div style="background: #2563eb; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"><i class="bi bi-house-fill" style="color: white; font-size: 14px;"></i></div>',
    className: '',
    iconSize: [32, 32],
    iconAnchor: [16, 16]
});

const destMarker = L.marker([<?= $destLat ?>, <?= $destLng ?>], {icon: customerIcon}).addTo(map)
    .bindPopup("<b><?= htmlspecialchars($customer_name) ?></b><br><?= htmlspecialchars($address) ?>");

// Rider marker with custom icon
const riderIcon = L.divIcon({
    html: '<div style="background: #10b981; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"><i class="bi bi-truck" style="color: white; font-size: 14px;"></i></div>',
    className: '',
    iconSize: [32, 32],
    iconAnchor: [16, 16]
});

let riderMarker = null;
let routingControl = null;

// Update route
function updateRoute(lat, lng) {
    if (routingControl) map.removeControl(routingControl);

    routingControl = L.Routing.control({
        waypoints: [
            L.latLng(lat, lng),
            L.latLng(<?= $destLat ?>, <?= $destLng ?>)
        ],
        router: L.Routing.osrmv1({ serviceUrl: 'https://router.project-osrm.org/route/v1' }),
        addWaypoints: false,
        draggableWaypoints: false,
        lineOptions: { styles: [{ color: '#2563eb', weight: 5, opacity: 0.8 }] },
        createMarker: () => null,
        show: false
    }).addTo(map);

    routingControl.on('routesfound', (e) => {
        let route = e.routes[0];
        let mins = Math.round(route.summary.totalTime / 60);
        let dist = (route.summary.totalDistance / 1000).toFixed(1);

        document.querySelector('.eta-text').innerText = `${mins} min • ${dist} km`;
    });
}

// Center map on both markers
function centerMap() {
    if (riderMarker) {
        const group = new L.featureGroup([riderMarker, destMarker]);
        map.fitBounds(group.getBounds().pad(0.2));
    } else {
        map.setView([<?= $destLat ?>, <?= $destLng ?>], 15);
    }
}

// GPS Tracking
navigator.geolocation.watchPosition((pos) => {
    let lat = pos.coords.latitude;
    let lng = pos.coords.longitude;

    if (!riderMarker) {
        riderMarker = L.marker([lat, lng], {icon: riderIcon}).addTo(map)
            .bindPopup("You are here");
        centerMap();
    } else {
        riderMarker.setLatLng([lat, lng]);
    }

    updateRoute(lat, lng);

}, (err) => {
    console.log("GPS Error:", err);
    document.querySelector('.eta-text').innerText = 'GPS unavailable';
}, { enableHighAccuracy: true, timeout: 10000, maximumAge: 5000 });

function messageCustomer() {
    alert("Opening messaging app…");
}
</script>

</body>
</html>