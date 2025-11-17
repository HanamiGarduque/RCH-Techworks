<?php 
session_start();
require_once '../Database/db_connection.php';

// Destination info
$customer_name = isset($_GET['customer']) ? $_GET['customer'] : "Christine May Padua";
$destLat = isset($_GET['lat']) ? floatval($_GET['lat']) : 13.9434;
$destLng = isset($_GET['lng']) ? floatval($_GET['lng']) : 121.1279;
$order_id = isset($_GET['order_id'] ) ? intval($_GET['order_id']) : 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Live GPS | RCH Water</title>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css"/>
<style>
body { margin:0; font-family:'Poppins',sans-serif; background:#f4f6fa; overflow:hidden; }
#map { height:100vh; width:100%; }
.direction-control {
    position:absolute; top:10px; left:50%; transform:translateX(-50%);
    background:rgba(255,255,255,0.9); padding:5px 10px; border-radius:5px; font-size:14px;
    box-shadow:0 2px 5px rgba(0,0,0,0.3); z-index:1000;
}
</style>
</head>
<body>

<div id="map"></div>
<div class="direction-control" id="eta">Calculating route...</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js"></script>
<script>
let map = L.map('map').setView([<?= $destLat ?>, <?= $destLng ?>], 14);

// Map tiles
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
    attribution:'© OpenStreetMap contributors'
}).addTo(map);

// Destination marker
const destMarker = L.marker([<?= $destLat ?>, <?= $destLng ?>]).addTo(map)
    .bindPopup("<b><?= htmlspecialchars($customer_name) ?></b>").openPopup();

let riderMarker = null;
let routingControl = null;

const orderId = <?= $order_id ?>;

// Update route function
function updateRoute(lat,lng){
    if(routingControl) map.removeControl(routingControl);

    routingControl = L.Routing.control({
        waypoints: [
            L.latLng(lat,lng),
            L.latLng(<?= $destLat ?>, <?= $destLng ?>)
        ],
        router: L.Routing.osrmv1({serviceUrl:'https://router.project-osrm.org/route/v1'}),
        routeWhileDragging:false,
        addWaypoints:false,
        draggableWaypoints:false,
        show:false,
        lineOptions:{styles:[{color:'#007bff',weight:5}]},
        createMarker:function(){ return null; }
    }).addTo(map);

    routingControl.on('routesfound', function(e){
        const route = e.routes[0];
        const timeMin = Math.round(route.summary.totalTime/60);
        const distKm = (route.summary.totalDistance/1000).toFixed(1);
        document.getElementById('eta').innerText = `${timeMin} min – ${distKm} km`;
    });
}

// Watch rider GPS
navigator.geolocation.watchPosition(function(pos){
    const lat = pos.coords.latitude;
    const lng = pos.coords.longitude;

    // Rider marker
    if(!riderMarker){
        riderMarker = L.marker([lat,lng]).addTo(map).bindPopup("You are here").openPopup();
    } else riderMarker.setLatLng([lat,lng]);

    updateRoute(lat,lng);
    map.setView([lat,lng]);

    // Send to backend
    fetch('update_rider_location.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({lat,lng,order_id:orderId})
    });

}, function(err){ console.error(err); }, {enableHighAccuracy:true, maximumAge:0});
</script>
</body>
</html>
