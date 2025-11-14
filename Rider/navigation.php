<<<<<<< HEAD
<<<<<<< HEAD
<?php include 'navigation_backend.php'; ?>
=======
=======
>>>>>>> e70e61c6d9b34d520c5eb233fb632ee3492ace3b
<?php
session_start();
require_once '../Database/db_connection.php';
?>
<<<<<<< HEAD
>>>>>>> e40151c (Rider mobile view)
=======
>>>>>>> e70e61c6d9b34d520c5eb233fb632ee3492ace3b
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Navigation | RCH Water</title>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
<<<<<<< HEAD
<<<<<<< HEAD
    body { font-family: 'Poppins', sans-serif; margin: 0; background: #f5f6fa; color: #333; overflow: hidden; }
    #map { height: 100vh; width: 100%; position: relative; }

    .direction-box {
        position: absolute; top: 20px; left: 50%; transform: translateX(-50%);
        background: rgba(240,240,240,0.95); padding: 10px 15px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        text-align: center; display: flex; align-items: center; justify-content: center; gap: 10px;
    }
    .direction-box img { width: 25px; vertical-align: middle; cursor: pointer; }
    .distance-text { margin-top: 5px; font-size: 13px; color: #555; }

    .back-btn {
        position: absolute; top: 0; left: 0; margin: 5px 0 0 10px; font-size: 22px; cursor: pointer; color: #2196F3; font-weight: 600;
    }

    .bottom-bar {
        position: absolute; bottom: 15px; left: 50%; transform: translateX(-50%); width: 90%; background: white;
        border-radius: 15px; padding: 15px; box-shadow: 0 3px 10px rgba(0,0,0,0.2); text-align: center;
    }
    .eta { font-weight: 600; font-size: 14px; margin-bottom: 10px; }
    .btn { display: inline-block; text-align: center; border: none; border-radius: 20px; cursor: pointer; font-size: 14px; padding: 10px 20px; transition: background 0.2s; }
    .btn-green { background-color: #4CAF50; color: white; width: 80%; margin-bottom: 10px; }
    .btn-green:hover { background-color: #3d8c40; }
    .btn-outline { background: #fff; color: #4CAF50; border: 1px solid #4CAF50; width: 45%; }
=======
=======
>>>>>>> e70e61c6d9b34d520c5eb233fb632ee3492ace3b
    body {
        font-family: 'Poppins', sans-serif;
        margin: 0;
        background: #f5f6fa;
        color: #333;
        overflow: hidden;
    }

    #map {
        height: 100vh;
        width: 100%;
        position: relative;
    }

    /* Turn direction box */
    .direction-box {
        position: absolute;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(240,240,240,0.95);
        padding: 10px 15px;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        text-align: center;
    }
    .direction-box img {
        width: 25px;
        vertical-align: middle;
    }
    .distance-text {
        margin-top: 5px;
        font-size: 13px;
        color: #555;
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

<<<<<<< HEAD
>>>>>>> e40151c (Rider mobile view)
=======
>>>>>>> e70e61c6d9b34d520c5eb233fb632ee3492ace3b
</style>
</head>
<body>

<<<<<<< HEAD
<<<<<<< HEAD
<div id="map"></div>

<!-- Direction Box with Back Button -->
<div class="direction-box">
    <span class="back-btn" onclick="goBack()">←</span>
    <img src="https://cdn-icons-png.flaticon.com/512/565/565528.png" alt="Turn Left Icon">
    <div>
        <strong>Turn Left</strong>
        <div class="distance-text">200 M</div>
    </div>
=======
=======
>>>>>>> e70e61c6d9b34d520c5eb233fb632ee3492ace3b
<!-- Map Container -->
<div id="map"></div>

<!-- Direction Box -->
<div class="direction-box">
    <img src="https://cdn-icons-png.flaticon.com/512/565/565528.png" alt="Turn Left Icon">
    <div><strong>Turn Left</strong></div>
    <div class="distance-text">200 M</div>
<<<<<<< HEAD
>>>>>>> e40151c (Rider mobile view)
=======
>>>>>>> e70e61c6d9b34d520c5eb233fb632ee3492ace3b
</div>

<!-- Bottom Information Bar -->
<div class="bottom-bar">
    <div class="eta">15 min – 1.2 km</div>
    <button class="btn btn-green" onclick="markDelivered()">Mark as Delivered</button><br>
    <button class="btn btn-outline" onclick="callCustomer()">📞 Call</button>
    <button class="btn btn-outline" onclick="messageCustomer()">💬 Message</button>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<<<<<<< HEAD
<<<<<<< HEAD
<script>
// Initialize the map
const map = L.map('map').setView([13.7565, 121.0583], 14); // Centered in Batangas

// OpenStreetMap tiles
=======
=======
>>>>>>> e70e61c6d9b34d520c5eb233fb632ee3492ace3b

<script>
// Initialize the map
const map = L.map('map').setView([13.7565, 121.0583], 14); // Example coords (Batangas)

// Add OpenStreetMap tiles
<<<<<<< HEAD
>>>>>>> e40151c (Rider mobile view)
=======
>>>>>>> e70e61c6d9b34d520c5eb233fb632ee3492ace3b
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

<<<<<<< HEAD
<<<<<<< HEAD
// Example route coordinates (start -> destination)
=======
// Example route line
>>>>>>> e40151c (Rider mobile view)
=======
// Example route line
>>>>>>> e70e61c6d9b34d520c5eb233fb632ee3492ace3b
const routeCoords = [
    [13.7565, 121.0583],
    [13.7580, 121.0600],
    [13.7605, 121.0625],
<<<<<<< HEAD
<<<<<<< HEAD
    [<?= $destination_lat ?>, <?= $destination_lng ?>]
=======
    [13.7620, 121.0650]
>>>>>>> e40151c (Rider mobile view)
=======
    [13.7620, 121.0650]
>>>>>>> e70e61c6d9b34d520c5eb233fb632ee3492ace3b
];
const routeLine = L.polyline(routeCoords, { color: '#007bff', weight: 4 }).addTo(map);

// Destination marker
<<<<<<< HEAD
<<<<<<< HEAD
const endMarker = L.marker([<?= $destination_lat ?>, <?= $destination_lng ?>]).addTo(map);
endMarker.bindPopup("<b>Delivery Destination</b>").openPopup();

// Fit map to route
map.fitBounds(routeLine.getBounds());

// Button functions
function markDelivered() {
    if (confirm("Mark this delivery as completed?")) {
        window.location.href = "delivery_complete.php?delivery_id=<?= $delivery_id ?>";
    }
}
function callCustomer() { alert("Calling customer..."); }
function messageCustomer() { alert("Opening messaging app..."); }
function goBack() { window.location.href = "delivery_info.php?delivery_id=<?= $delivery_id ?>"; }
=======
=======
>>>>>>> e70e61c6d9b34d520c5eb233fb632ee3492ace3b
const endMarker = L.marker([13.7620, 121.0650]).addTo(map);
endMarker.bindPopup("<b>Delivery Destination</b>").openPopup();

// Auto-fit map to route
map.fitBounds(routeLine.getBounds());

// Button actions
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
<<<<<<< HEAD
>>>>>>> e40151c (Rider mobile view)
=======
>>>>>>> e70e61c6d9b34d520c5eb233fb632ee3492ace3b
</script>

</body>
</html>
