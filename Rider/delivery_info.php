<?php
session_start();
require_once '../Database/db_connection.php';

if (!isset($_GET['id'])) {
    echo "Customer ID not provided.";
    exit;
}

$user_id = intval($_GET['id']);

// DB connection
$db = new Database();
$conn = $db->getConnect();

try {
    $stmt = $conn->prepare("SELECT name, address, latitude, longitude FROM users WHERE user_id = :id");
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$customer) {
        echo "Customer not found.";
        exit;
    }

    $customer_name = $customer['name'];
    $address = $customer['address'];
    $lat = $customer['latitude'];
    $lng = $customer['longitude'];

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}

// Hardcoded sample items & status
$order_id = "#DEL001";
$status = "On the Way"; 

$items = [
    ["name" => "Slim Box type Gallon (20L)", "code" => "SP-204-021", "status" => "Ready"],
    ["name" => "Slim Box type Gallon (20L)", "code" => "SP-204-022", "status" => "Ready"]
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Delivery Info | RCH Water</title>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f4f6fa;
        margin: 0;
        padding: 0;
        color: #333;
    }

    .header {
        background-color: #2196F3;
        color: white;
        padding: 30px 20px 60px;
        border-radius: 0 0 40px 40px;
        position: relative;
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

    .step {
        display: flex;
        align-items: center;
        margin: 10px 0;
    }

    .circle {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background-color: #ccc;
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 12px;
        margin-right: 10px;
    }

    .active-step { background-color: #4CAF50; }

    .items {
        background-color: white;
        border-radius: 15px;
        margin: 20px;
        padding: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #f4f6fa;
        border-radius: 10px;
        padding: 10px;
        margin-bottom: 10px;
    }

    .item-status {
        background-color: #8BC34A;
        color: white;
        border-radius: 12px;
        padding: 2px 8px;
        font-size: 11px;
    }

    #map {
        height: 250px;
        width: 100%;
        border-radius: 10px;
        cursor: pointer;
        margin-top: 10px;
        margin-bottom: 20px;
    }

    .btn {
        display: inline-block;
        border: none;
        border-radius: 20px;
        padding: 10px;
        font-size: 14px;
        cursor: pointer;
        width: 48%;
        text-align: center;
    }

    .btn-primary {
        background-color: #1E90FF;
        color: white;
        width: 100%;
        margin-top: 10px;
    }

    .btn-outline {
        border: 1px solid #1E90FF;
        background-color: #fff;
        color: #1E90FF;
    }

    .action-btn {
        width: 90%;
        margin: 10px auto;
        display: block;
        padding: 12px 0;
        border: none;
        border-radius: 25px;
        color: white;
        font-size: 16px;
        cursor: pointer;
    }

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

    <div id="map" onclick="goFullMap()"></div>

    <button class="btn btn-primary" onclick="goFullMap()">Open Navigation</button>

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

<!-- Complete Delivery button -->


<script>

</script>


<!-- REMOVED SCAN BUTTON -->


<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
const customerLat = <?= $lat ?>;
const customerLng = <?= $lng ?>;

// Initialize map centered on the customer
const map = L.map('map', { zoomControl: false }).setView([customerLat, customerLng], 14);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

// Add customer marker
L.marker([customerLat, customerLng])
  .addTo(map)
  .bindPopup("Customer Location");

// Get live GPS of Rider (Laptop)
if ("geolocation" in navigator) {

    navigator.geolocation.watchPosition(
        (pos) => {
            const riderLat = pos.coords.latitude;
            const riderLng = pos.coords.longitude;

            if (!window.riderMarker) {
                window.riderMarker = L.marker([riderLat, riderLng])
                    .addTo(map)
                    .bindPopup("Your Location");
            } else {
                window.riderMarker.setLatLng([riderLat, riderLng]);
            }

        },
        (err) => {
            console.log("GPS Error:", err);
            alert("Location access denied or unavailable.");
        },
        {
            enableHighAccuracy: true,
            timeout: 5000
        }
    );

} else {
    alert("Browser does not support GPS.");
}


function goFullMap() {
    // Pass the correct latitude, longitude, and user_id to full_map.php
    window.location.href = "full_map.php?lat=<?= $lat ?>&lng=<?= $lng ?>&user_id=<?= $user_id ?>";
}



function callCustomer() { alert("Calling customer..."); }
function messageCustomer() { alert("Opening messaging app..."); }

</script>

</body>
</html>
