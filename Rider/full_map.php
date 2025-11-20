<?php 
session_start();
require_once '../Database/db_connection.php';

// Destination info
$customer_name = isset($_GET['customer']) ? $_GET['customer'] : "Christine May Padua";
$destLat = isset($_GET['lat']) ? floatval($_GET['lat']) : 13.9434;
$destLng = isset($_GET['lng']) ? floatval($_GET['lng']) : 121.1279;
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 1;
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
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
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

body { margin:0; font-family:'Poppins',sans-serif; background:#f4f6fa; color:#333; overflow:hidden; }
#map { height:70vh; width:95%; margin:10px auto; border-radius:10px; }
.direction-control { background: rgba(255,255,255,0.85); padding: 6px 10px; border-radius:5px; font-size:14px; box-shadow:0 2px 5px rgba(0,0,0,0.3); position:absolute; top:10px; left:50%; transform:translateX(-50%); z-index:1000; }
.back-btn { position: absolute; top: 10px; left: 60px; background: rgba(255,255,255,0.9); padding: 6px 12px; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; z-index: 1000; box-shadow: 0 2px 5px rgba(0,0,0,0.3); user-select: none; }
.back-btn:hover { background: rgba(255,255,255,1); }
.bottom-bar { position:absolute; bottom:70px; left:50%; transform:translateX(-50%); width:50%; background:rgba(255,255,255,0.95); border-radius:15px; padding:15px; box-shadow:0 3px 10px rgba(0,0,0,0.2); text-align:center; font-family:'Poppins',sans-serif; display:flex; flex-direction: column; gap:8px; align-items:center; }
.btn { display:inline-block; border:none; border-radius:25px; cursor:pointer; font-size:14px; padding:10px 20px; transition:0.2s; }
.btn-green { background-color:#1E90FF; color:white; width:80%; }
.btn-green:hover { background-color:#0b7dda; }
.btn-outline { background:#fff; color:#1E90FF; border:1px solid #1E90FF; width:45%; }
#qrModal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:9999; justify-content:center; align-items:center; }
#qrModal div { position:relative; width:90%; max-width:400px; background:white; border-radius:15px; padding:20px; text-align:center; }
#notification { display:none; position: fixed; top: 40%; left: 50%; transform: translate(-50%, -50%); background: #4CAF50; color: white; padding: 25px 30px; border-radius: 15px; text-align: center; z-index: 9999; box-shadow: 0 2px 8px rgba(0,0,0,0.3); }
#notification button { margin-top: 15px; padding: 8px 20px; border: none; border-radius: 20px; background: white; color: #4CAF50; font-weight: 600; cursor: pointer; }
@media (max-width:480px){ #map { height:75vh; width:95%; } .bottom-bar { width:95%; padding:12px; } }
</style>
</head>
<body>

<div class="back-btn" onclick="history.back()">←</div>
<div id="map"></div>
<div class="direction-control" id="eta">Calculating route...</div>

<div class="bottom-bar" style="flex-direction: row; justify-content: space-between;">
    <button class="btn btn-green btn-outline" style="flex:1; margin-right:5px;" onclick="openQRModal()">Scan QR</button>
    <button class="btn btn-outline" style="flex:1; margin-left:5px;" onclick="messageCustomer()">💬 Message</button>
</div>

<div id="qrModal">
    <div>
        <h3>Scan QR Code</h3>
        <div id="qr-reader" style="width:100%;"></div>
        <button onclick="closeQR()">Cancel</button>
    </div>
</div>

<div id="notification">
    <div>Delivery Successful ✅</div>
    <button onclick="goDashboard()">OK</button>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js"></script>
<script src="https://unpkg.com/html5-qrcode@2.3.8/minified/html5-qrcode.min.js"></script>

<script>
let map = L.map('map').setView([<?= $destLat ?>, <?= $destLng ?>], 14);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
    attribution:'© OpenStreetMap contributors'
}).addTo(map);

const destMarker = L.marker([<?= $destLat ?>, <?= $destLng ?>]).addTo(map)
    .bindPopup("<b><?= htmlspecialchars($customer_name) ?></b>").openPopup();

let riderMarker = null;
let routingControl = null;
const orderId = <?= $order_id ?>;

function updateRoute(lat,lng){
    if(routingControl) map.removeControl(routingControl);
    routingControl = L.Routing.control({
        waypoints:[ L.latLng(lat,lng), L.latLng(<?= $destLat ?>, <?= $destLng ?>) ],
        router:L.Routing.osrmv1({serviceUrl:'https://router.project-osrm.org/route/v1'}),
        routeWhileDragging:false,
        addWaypoints:false,
        draggableWaypoints:false,
        show:false,
        lineOptions:{styles:[{color:'#007bff',weight:5}]},
        createMarker:function(){return null;}
    }).addTo(map);

    routingControl.on('routesfound',function(e){
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

    if(!riderMarker){
        riderMarker = L.marker([lat,lng]).addTo(map).bindPopup("You are here").openPopup();
    } else riderMarker.setLatLng([lat,lng]);

    updateRoute(lat,lng);
    map.setView([lat,lng]);

    fetch('update_rider_location.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({lat,lng,order_id:orderId})
    });
},function(err){console.error(err);},{enableHighAccuracy:true, maximumAge:0});

// QR Scanner
let qrScanner = null;
function openQRModal(){
    document.getElementById('qrModal').style.display = 'flex';
    qrScanner = new Html5Qrcode("qr-reader");
    Html5Qrcode.getCameras().then(cameras => {
        if(cameras && cameras.length){
            qrScanner.start(
                cameras[0].id,
                { fps: 10, qrbox: 250 },
                qrCodeMessage => {
                    qrScanner.stop().then(()=>{
                        document.getElementById('qrModal').style.display = 'none';
                        showDeliverySuccess();
                    });
                },
                errorMessage => {}
            );
        } else alert("No camera found.");
    }).catch(err => console.error(err));
}

function closeQR(){
    if(qrScanner){
        qrScanner.stop().catch(()=>{});
        qrScanner.clear().catch(()=>{});
    }
    document.getElementById('qrModal').style.display = 'none';
}

function showDeliverySuccess(){
    document.getElementById('notification').style.display = 'block';
}

function goDashboard(){ window.location.href = 'dashboard.php'; }
function messageCustomer(){ alert("Opening messaging app..."); }
</script>

</body>
</html>
