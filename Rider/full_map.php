<?php 
session_start();
require_once '../Database/db_connection.php';

// Validate user_id
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
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

$destLat = $map['latitude'] ?? 13.88;   // fallback coordinates
$destLng = $map['longitude'] ?? 121.11;
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

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

body { margin:0; font-family:'Poppins',sans-serif; background:#f4f6fa; color:#333; overflow:hidden; }
#map { height:70vh; width:95%; margin:10px auto; border-radius:10px; }

.direction-control {
    background: rgba(255,255,255,0.85);
    padding: 6px 10px;
    border-radius:5px;
    font-size:14px;
    box-shadow:0 2px 5px rgba(0,0,0,0.3);
    position:absolute;
    top:10px;
    left:50%;
    transform:translateX(-50%);
    z-index:1000;
}

.back-btn {
    position: absolute;
    top: 10px;
    left: 20px;
    background: rgba(255,255,255,0.9);
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    z-index: 1000;
    box-shadow: 0 2px 5px rgba(0,0,0,0.3);
    user-select: none;
}

.bottom-bar {
    position:absolute;
    bottom:70px;
    left:50%;
    transform:translateX(-50%);
    width:50%;
    background:rgba(255,255,255,0.95);
    border-radius:15px;
    padding:15px;
    box-shadow:0 3px 10px rgba(0,0,0,0.2);
    text-align:center;
    display:flex;
    flex-direction: row;
    justify-content: space-between;
    gap:8px;
    align-items:center;
}

.btn {
    border:none;
    border-radius:25px;
    cursor:pointer;
    font-size:14px;
    padding:10px 20px;
    transition:0.2s;
}

.btn-green { background-color:#1E90FF; color:white; }
.btn-outline { background:#fff; color:#1E90FF; border:1px solid #1E90FF; }

@media (max-width:480px){
    #map { height:75vh; width:95%; }
    .bottom-bar { width:95%; padding:12px; }
}
</style>
</head>
<body>

<div class="back-btn" onclick="history.back()">←</div>
<div id="map"></div>
<div class="direction-control" id="eta">Calculating route...</div>

<div class="bottom-bar">
    <button class="btn btn-green" onclick="window.location.href='qr_scan.php?user_id=<?= $user_id ?>&customer=<?= urlencode($customer_name) ?>&lat=<?= $destLat ?>&lng=<?= $destLng ?>'">Scan QR</button>
    <button class="btn btn-outline" onclick="messageCustomer()">💬 Message</button>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js"></script>

<script>
let map = L.map('map').setView([<?= $destLat ?>, <?= $destLng ?>], 14);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{ attribution:'© OpenStreetMap' }).addTo(map);

// Customer marker
const destMarker = L.marker([<?= $destLat ?>, <?= $destLng ?>]).addTo(map)
    .bindPopup("<b><?= htmlspecialchars($customer_name) ?></b>").openPopup();

let riderMarker = null;
let routingControl = null;

function updateRoute(lat,lng){
    if(routingControl) map.removeControl(routingControl);

    routingControl = L.Routing.control({
        waypoints: [
            L.latLng(lat,lng),
            L.latLng(<?= $destLat ?>, <?= $destLng ?>)
        ],
        router: L.Routing.osrmv1({ serviceUrl:'https://router.project-osrm.org/route/v1' }),
        addWaypoints:false,
        draggableWaypoints:false,
        lineOptions:{ styles:[{color:'#007bff',weight:5}] },
        createMarker:() => null
    }).addTo(map);

    routingControl.on('routesfound', (e) => {
        let route = e.routes[0];
        let mins = Math.round(route.summary.totalTime / 60);
        let dist = (route.summary.totalDistance / 1000).toFixed(1);
        document.getElementById('eta').innerText = `${mins} min – ${dist} km`;
    });
}

// GPS Tracking
navigator.geolocation.watchPosition((pos)=>{
    let lat = pos.coords.latitude;
    let lng = pos.coords.longitude;

    if(!riderMarker)
        riderMarker = L.marker([lat,lng]).addTo(map).bindPopup("You are here").openPopup();
    else
        riderMarker.setLatLng([lat,lng]);

    updateRoute(lat,lng);
},()=>{}, {enableHighAccuracy:true});

function messageCustomer(){
    alert("Opening messaging app…");
}
</script>

</body>
</html>
