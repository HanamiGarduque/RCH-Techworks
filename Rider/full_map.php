<?php
session_start();
require_once '../Database/db_connection.php';

// Sample coordinates — in real use, load from DB or GPS
$lat = isset($_GET['lat']) ? $_GET['lat'] : 13.7565;
$lng = isset($_GET['lng']) ? $_GET['lng'] : 121.0583;
$customer_name = isset($_GET['customer']) ? $_GET['customer'] : "Christine May Padua";

// Rider’s example location (simulate current position)
$rider_lat = 13.7545;
$rider_lng = 121.0565;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Navigation | RCH Water</title>

<!-- Leaflet & Routing CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css"/>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    background: #f4f6fa;
    color: #333;
    overflow: hidden;
}

#map {
    height: 70vh;
    width: 80%;
    margin: 0 auto;
}

/* Turn direction box (top-left near zoom buttons) */
.direction-control {
    background: rgba(255, 255, 255, 0.85);
    padding: 6px 10px;
    border-radius: 5px;
    font-size: 13px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

/* Bottom info bar */
.bottom-bar {
    position: absolute;
    bottom: 15px;
    left: 50%;
    transform: translateX(-50%);
    width: 90%;
    background: white;
    border-radius: 15px;
    padding: 15px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    text-align: center;
}
.eta {
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 10px;
}
.btn {
    display: inline-block;
    text-align: center;
    border: none;
    border-radius: 20px;
    cursor: pointer;
    font-size: 14px;
    padding: 10px 20px;
    transition: background 0.2s;
}
.btn-green {
    background-color: #4CAF50;
    color: white;
    width: 80%;
    margin-bottom: 10px;
}
.btn-green:hover {
    background-color: #3d8c40;
}
.btn-outline {
    background: #fff;
    color: #4CAF50;
    border: 1px solid #4CAF50;
    width: 45%;
}
</style>
</head>
<body>

<!-- Map -->
<div id="map"></div>

<!-- Bottom Information Bar -->
<div class="bottom-bar">
    <div class="eta" id="eta-text">Calculating route...</div>
    <button class="btn btn-green" onclick="markDelivered()">Mark as Delivered</button><br>
    <button class="btn btn-outline" onclick="callCustomer()">📞 Call</button>
    <button class="btn btn-outline" onclick="messageCustomer()">💬 Message</button>
</div>

<!-- JS Libraries -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js"></script>

<script>
// Coordinates
const riderLat = <?= $rider_lat ?>;
const riderLng = <?= $rider_lng ?>;
const destLat = <?= $lat ?>;
const destLng = <?= $lng ?>;

// Initialize map
const map = L.map('map').setView([riderLat, riderLng], 14);

// Base map tiles
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

// Rider marker
const riderMarker = L.marker([riderLat, riderLng])
    .addTo(map)
    .bindPopup("<b>Rider Location</b>")
    .openPopup();

// Customer marker
const destMarker = L.marker([destLat, destLng])
    .addTo(map)
    .bindPopup("<b><?= htmlspecialchars($customer_name) ?></b>");

// Custom "Turn Left" info control near zoom buttons
const turnControl = L.control({position: 'topleft'});
turnControl.onAdd = function() {
    const div = L.DomUtil.create('div', 'direction-control');
    div.innerHTML = '⬅ Turn Left – 200 M';
    return div;
};
turnControl.addTo(map);

// Routing control
const routingControl = L.Routing.control({
    waypoints: [
        L.latLng(riderLat, riderLng),
        L.latLng(destLat, destLng)
    ],
    router: L.Routing.osrmv1({
        serviceUrl: 'https://router.project-osrm.org/route/v1'
    }),
    routeWhileDragging: false,
    addWaypoints: false,
    draggableWaypoints: false,
    show: false,
    fitSelectedRoutes: true,
    lineOptions: {
        styles: [{ color: '#007bff', weight: 5 }]
    },
    createMarker: function() { return null; }
}).addTo(map);

// Update ETA info
routingControl.on('routesfound', function(e) {
    const route = e.routes[0];
    const summary = route.summary;
    const timeMin = Math.round(summary.totalTime / 60);
    const distKm = (summary.totalDistance / 1000).toFixed(1);
    document.getElementById('eta-text').textContent = `${timeMin} min – ${distKm} km`;
});

// Buttons
function markDelivered() {
    if (confirm("Mark this delivery as completed?")) {
        window.location.href = "delivery_complete.php";
    }
}
function callCustomer() {
    alert("Calling customer...");
}
function messageCustomer() {
    alert("Opening messaging app...");
}
</script>

</body>
</html>
