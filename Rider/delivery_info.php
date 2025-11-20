<?php
session_start();

// Hardcoded sample users with correct lat/lng
$users = [
    1 => ["name" => "RM Fontelera", "address" => "Purok 4 Mojon Tampoy, San Jose, Batangas", "phone_number" => "09123456789", "latitude" => 13.9125, "longitude" => 121.1144],
    2 => ["name" => "Christine May Padua", "address" => "116 Purok 1 Sico, Lipa City, Batangas", "phone_number" => "09198765432", "latitude" => 13.9434, "longitude" => 121.1279],
    3 => ["name" => "Hanami Garduque", "address" => "456 Elm St, Batangas", "phone_number" => "09234567890", "latitude" => 13.950, "longitude" => 121.120],
    4 => ["name" => "Michael Tan", "address" => "789 Oak St, Batangas", "phone_number" => "09298765432", "latitude" => 13.920, "longitude" => 121.100],
    5 => ["name" => "Anna Lopez", "address" => "10 Pine St, Batangas", "phone_number" => "09345678901", "latitude" => 13.910, "longitude" => 121.110],
];

// Get user_id from URL
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!isset($users[$user_id])) {
    echo "Customer not found.";
    exit;
}

$customer = $users[$user_id];

// Hardcoded sample items & status
$order_id = "#DEL001";
$status = "On the Way"; 
$items = [
    ["name" => "Slim Box type Gallon (20L)", "code" => "SP-204-021", "status" => "Ready"],
    ["name" => "Slim Box type Gallon (20L)", "code" => "SP-204-022", "status" => "Ready"]
];

$customer_name = $customer['name'];
$address = $customer['address'];
$lat = $customer['latitude'];
$lng = $customer['longitude'];
$phone = $customer['phone_number'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Delivery Info | <?= htmlspecialchars($customer_name) ?></title>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
body { 
    font-family: 'Poppins', sans-serif; 
    background-color: #f4f6fa; 
    margin: 0; 
    padding-top: 120px; /* space for fixed header */
    color: #333; 
    width: 320px;
    justify-content: center;
    align-items: center;
}
.header { 
    background-color: #2196F3; 
    color: white; 
    padding: 30px 20px 60px; 
    border-radius: 0 0 40px 40px; 
    position: fixed; /* fixed at top */
    top: 0;
    left: 0;
    width: 90%;
    z-index: 1000;
    overflow: hidden; /* hide overflow */
}
.back-btn { 
    position: absolute; 
    top: 10px; 
    left: 20px; 
    font-size: 22px; 
    cursor: pointer; 
}
.header h3 { 
    margin: 10px 0 0; 
    font-weight: 600; 
    font-size: 18px; 
}
.header p { 
    margin: 5px 0 0; 
    font-size: 14px; 
}
.status-badge { 
    position: absolute; 
    top: 30px; 
    right: 20px; 
    background-color: #4CAF50; 
    color: white; 
    font-size: 12px; 
    padding: 5px 12px; 
    border-radius: 20px; 
}
.card { 
    background-color: white; 
    border-radius: 15px; 
    box-shadow: 0 2px 8px rgba(0,0,0,0.1); 
    margin: 20px; 
    padding: 15px; 
}
.step { display: flex; align-items: center; margin: 10px 0; }
.circle { width: 22px; height: 22px; border-radius: 50%; background-color: #ccc; color: white; display: flex; justify-content: center; align-items: center; font-size: 12px; margin-right: 10px; }
.active-step { background-color: #4CAF50; }
.items { background-color: white; border-radius: 15px; margin: 20px; padding: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.item { display: flex; justify-content: space-between; align-items: center; background-color: #f4f6fa; border-radius: 10px; padding: 10px; margin-bottom: 10px; }
.item-status { background-color: #8BC34A; color: white; border-radius: 12px; padding: 2px 8px; font-size: 11px; }
#map { height: 250px; width: 100%; border-radius: 10px; cursor: pointer; margin-top: 10px; margin-bottom: 20px; }
.btn { display: inline-block; border: none; border-radius: 20px; padding: 10px; font-size: 14px; cursor: pointer; width: 48%; text-align: center; }
.btn-primary { background-color: #1E90FF; color: white; width: 100%; margin-top: 10px; }
.btn-outline { border: 1px solid #1E90FF; background-color: #fff; color: #1E90FF; }
.action-btn { width: 90%; margin: 10px auto; display: block; padding: 12px 0; border: none; border-radius: 25px; color: white; font-size: 16px; cursor: pointer; }
.deliver-btn { background-color: #4CAF50; }
</style>
</head>
<body>

<div class="header">
    <div class="back-btn" onclick="history.back()">←</div>
    <span class="status-badge"><?= $status ?></span>

    <p>Delivery <?= $order_id ?></p>
    <h3><?= htmlspecialchars($customer_name) ?></h3>
</div>

<div class="card">
    <div class="step"><div class="circle active-step">1</div> <span>Ready for Pickup</span></div>
    <div class="step"><div class="circle active-step">2</div> <span>On the Way</span></div>
    <div class="step"><div class="circle">3</div> <span>Completed</span></div>
</div>

<div class="card">
    <strong>Delivery Address</strong>
    <p style="font-size:13px;">📍 <?= htmlspecialchars($address) ?></p>
    <p style="font-size:13px;">📞 <?= htmlspecialchars($phone) ?></p>

    <div id="map" onclick="goFullMap()"></div>

    <div style="display:flex;justify-content:space-between;margin-top:10px;">
        <button class="btn btn-outline" onclick="callCustomer()">📞 Call</button>
        <button class="btn btn-outline" onclick="messageCustomer()">💬 Message</button>
    </div>
</div>

<div class="items">
    <strong>Items (<?= count($items) ?>)</strong>
    <?php foreach ($items as $item): ?>
        <div class="item">
            <div>
                <?= htmlspecialchars($item['name']) ?><br>
                <small><?= htmlspecialchars($item['code']) ?></small>
            </div>
            <div class="item-status"><?= htmlspecialchars($item['status']) ?></div>
        </div>
    <?php endforeach; ?>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const customerLat = <?= $lat ?>;
const customerLng = <?= $lng ?>;

// Initialize map centered on customer
const map = L.map('map', { zoomControl: false }).setView([customerLat, customerLng], 14);

// OpenStreetMap tiles
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

// Customer marker
L.marker([customerLat, customerLng], {icon: L.icon({iconUrl: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png', iconSize: [32,32], iconAnchor: [16,32]})})
  .addTo(map)
  .bindPopup("Customer Location");

// Rider marker with live GPS
if ("geolocation" in navigator) {
    navigator.geolocation.watchPosition(
        (pos) => {
            const riderLat = pos.coords.latitude;
            const riderLng = pos.coords.longitude;

            if (!window.riderMarker) {
                window.riderMarker = L.marker([riderLat, riderLng], {icon: L.icon({iconUrl: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png', iconSize: [32,32], iconAnchor: [16,32]})})
                    .addTo(map)
                    .bindPopup("Rider Location");
            } else {
                window.riderMarker.setLatLng([riderLat, riderLng]);
            }

        },
        (err) => { console.log("GPS Error:", err); alert("Location access denied or unavailable."); },
        { enableHighAccuracy: true, timeout: 5000 }
    );
} else {
    alert("Browser does not support GPS.");
}

// Redirect to full_map.php with correct coordinates
function goFullMap() {
    window.location.href = `full_map.php?lat=${customerLat}&lng=${customerLng}&user_id=<?= $user_id ?>`;
}

function callCustomer() { alert("Calling customer..."); }
function messageCustomer() { alert("Opening messaging app..."); }
</script>
</body>
</html>
