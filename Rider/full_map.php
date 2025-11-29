<?php 
session_start();
require_once '../Database/db_connection.php';

// Get the rider's user_id (adjust based on your session management)
$rider_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 9;

if ($rider_id <= 0) {
    echo "Invalid user.";
    exit;
}

// Database connection
$db = new Database();
$conn = $db->getConnect();

// Fetch all active deliveries for this rider with customer locations
$deliveries_query = "
    SELECT 
        wr.refill_id,
        wr.user_id,
        wr.order_number,
        wr.order_status,
        wr.total_amount,
        wr.pickup_datetime,
        wr.delivery_datetime,
        u.name as customer_name,
        u.address as customer_address,
        u.phone_number,
        ml.latitude,
        ml.longitude
    FROM water_refills wr
    INNER JOIN users u ON wr.user_id = u.user_id
    LEFT JOIN map_location ml ON u.user_id = ml.user_id
    WHERE wr.rider_id = :rider_id
    AND wr.order_status IN ('pickup_in_progress', 'on_delivery')
    ORDER BY 
        CASE 
            WHEN wr.order_status = 'on_delivery' THEN 1
            WHEN wr.order_status = 'pickup_in_progress' THEN 2
        END,
        wr.pickup_datetime ASC
";

$stmt = $conn->prepare($deliveries_query);
$stmt->bindParam(':rider_id', $rider_id);
$stmt->execute();
$deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Default center (Philippines)
$centerLat = 13.8800;
$centerLng = 121.1100;

// Calculate center based on all delivery locations
if (!empty($deliveries)) {
    $totalLat = 0;
    $totalLng = 0;
    $count = 0;
    
    foreach ($deliveries as $delivery) {
        if (!empty($delivery['latitude']) && !empty($delivery['longitude'])) {
            $totalLat += floatval($delivery['latitude']);
            $totalLng += floatval($delivery['longitude']);
            $count++;
        }
    }
    
    if ($count > 0) {
        $centerLat = $totalLat / $count;
        $centerLng = $totalLng / $count;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Active Deliveries Map</title>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
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
    height: calc(100% - 120px);
    width: 100%;
    position: relative;
}

/* Stats Overlay */
.stats-overlay {
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
    gap: 16px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
}

.stat-item i {
    font-size: 16px;
}

.stat-item.pickup {
    color: #f59e0b;
}

.stat-item.delivery {
    color: #10b981;
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

/* Leaflet popup customization */
.leaflet-popup-content-wrapper {
    border-radius: 12px;
    font-family: 'Poppins', sans-serif;
}

.leaflet-popup-content {
    margin: 12px;
    font-size: 13px;
}

.popup-header {
    font-weight: 700;
    font-size: 14px;
    margin-bottom: 8px;
    color: #1e293b;
}

.popup-detail {
    margin: 4px 0;
    color: #64748b;
    font-size: 12px;
}

.popup-status {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    margin-top: 6px;
}

.status-pickup {
    background: #fef3c7;
    color: #92400e;
}

.status-delivery {
    background: #d1fae5;
    color: #065f46;
}

.popup-action {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #e2e8f0;
}

.popup-btn {
    display: inline-block;
    padding: 6px 14px;
    background: #2563eb;
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.2s;
}

.popup-btn:hover {
    background: #1d4ed8;
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
            <h2>Active Deliveries</h2>
            <p><?= count($deliveries) ?> active order<?= count($deliveries) != 1 ? 's' : '' ?></p>
        </div>
        <div class="header-actions">
            <button class="icon-btn" onclick="fitAllMarkers()">
                <i class="bi bi-fullscreen"></i>
            </button>
            <button class="icon-btn" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </div>
    </div>

    <!-- Map -->
    <div id="map">
        <!-- Stats Overlay -->
        <?php
        $pickupCount = 0;
        $deliveryCount = 0;
        foreach ($deliveries as $delivery) {
            if ($delivery['order_status'] === 'pickup_in_progress') $pickupCount++;
            if ($delivery['order_status'] === 'on_delivery') $deliveryCount++;
        }
        ?>
        <div class="stats-overlay">
            <div class="stat-item pickup">
                <i class="bi bi-box-seam"></i>
                <span><?= $pickupCount ?> Pickup</span>
            </div>
            <div class="stat-item delivery">
                <i class="bi bi-truck"></i>
                <span><?= $deliveryCount ?> Delivery</span>
            </div>
        </div>
    </div>

    <!-- Bottom Action Bar -->
    <div class="bottom-bar">
        <button class="btn btn-primary" onclick="showMyLocation()">
            <i class="bi bi-geo-alt-fill"></i>
            My Location
        </button>
        <button class="btn btn-secondary" onclick="window.location.href='active_deliveries.php'">
            <i class="bi bi-list-ul"></i>
            List View
        </button>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Initialize map
let map = L.map('map').setView([<?= $centerLat ?>, <?= $centerLng ?>], 13);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(map);

// Store all markers for fitting bounds
let allMarkers = [];
let riderMarker = null;

// Delivery data from PHP
const deliveries = <?= json_encode($deliveries) ?>;

// Create custom icons
function createCustomIcon(status) {
    const isPickup = status === 'pickup_in_progress';
    const color = isPickup ? '#f59e0b' : '#10b981';
    const icon = isPickup ? 'box-seam' : 'house-fill';
    
    return L.divIcon({
        html: `<div style="background: ${color}; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"><i class="bi bi-${icon}" style="color: white; font-size: 16px;"></i></div>`,
        className: '',
        iconSize: [36, 36],
        iconAnchor: [18, 18]
    });
}

// Add markers for each delivery
deliveries.forEach((delivery) => {
    if (delivery.latitude && delivery.longitude) {
        const lat = parseFloat(delivery.latitude);
        const lng = parseFloat(delivery.longitude);
        
        const icon = createCustomIcon(delivery.order_status);
        const statusClass = delivery.order_status === 'pickup_in_progress' ? 'status-pickup' : 'status-delivery';
        const statusText = delivery.order_status === 'pickup_in_progress' ? 'Pickup in Progress' : 'On Delivery';
        
        const popupContent = `
            <div class="popup-header">${delivery.customer_name}</div>
            <div class="popup-detail"><i class="bi bi-geo-alt"></i> ${delivery.customer_address}</div>
            <div class="popup-detail"><i class="bi bi-phone"></i> ${delivery.phone_number || 'N/A'}</div>
            <div class="popup-detail"><i class="bi bi-hash"></i> Order #${delivery.order_number}</div>
            <div class="popup-detail"><i class="bi bi-currency-dollar"></i> ₱${parseFloat(delivery.total_amount).toFixed(2)}</div>
            <div class="popup-status ${statusClass}">${statusText}</div>
            
        `;
        
        const marker = L.marker([lat, lng], {icon: icon})
            .addTo(map)
            .bindPopup(popupContent);
        
        allMarkers.push(marker);
    }
});

// Rider location icon
const riderIcon = L.divIcon({
    html: '<div style="background: #2563eb; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 4px solid white; box-shadow: 0 2px 12px rgba(37, 99, 235, 0.5);"><i class="bi bi-person-fill" style="color: white; font-size: 18px;"></i></div>',
    className: '',
    iconSize: [40, 40],
    iconAnchor: [20, 20]
});

// Fit all markers in view
function fitAllMarkers() {
    if (allMarkers.length > 0) {
        const group = new L.featureGroup(allMarkers);
        map.fitBounds(group.getBounds().pad(0.1));
    }
}

// Show rider's current location
function showMyLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((pos) => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            
            if (!riderMarker) {
                riderMarker = L.marker([lat, lng], {icon: riderIcon})
                    .addTo(map)
                    .bindPopup("<b>Your Location</b>");
                allMarkers.push(riderMarker);
            } else {
                riderMarker.setLatLng([lat, lng]);
            }
            
            map.setView([lat, lng], 15);
        }, (err) => {
            alert("Unable to get your location. Please enable GPS.");
        });
    } else {
        alert("Geolocation is not supported by your browser.");
    }
}

// Initial fit to show all deliveries
if (deliveries.length > 0) {
    fitAllMarkers();
} else {
    // If no deliveries, try to show rider location
    showMyLocation();
}

// Track rider location continuously
if (navigator.geolocation) {
    navigator.geolocation.watchPosition((pos) => {
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;
        
        if (!riderMarker) {
            riderMarker = L.marker([lat, lng], {icon: riderIcon})
                .addTo(map)
                .bindPopup("<b>Your Location</b>");
            allMarkers.push(riderMarker);
        } else {
            riderMarker.setLatLng([lat, lng]);
        }
    }, null, { 
        enableHighAccuracy: true, 
        timeout: 10000, 
        maximumAge: 5000 
    });
}
</script>

</body>
</html>