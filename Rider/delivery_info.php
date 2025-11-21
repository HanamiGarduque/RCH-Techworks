<?php
session_start();

// Get user_id from URL
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Hardcoded rider info
$rider_name = $_SESSION['rider_name'] ?? "Rider";

// Hardcoded orders (for demonstration)
$orders = [
    1 => [
        "customer_name" => "RM Fontelera",
        "address" => "Purok 4 Mojon Tampoy, San Jose, Batangas",
        "status" => "Ready for Pickup",
        "order_id" => "#DEL001",
        "items" => [
            ["name" => "Slim Box type Gallon (20L)", "code" => "SP-204-021", "status" => "Ready"],
            ["name" => "Slim Box type Gallon (20L)", "code" => "SP-204-022", "status" => "Ready"]
        ]
    ],
    2 => [
        "customer_name" => "Christine May Padua",
        "address" => "116 Purok 4 Sico, Lipa City, Batangas",
        "status" => "Ready for Pickup",
        "order_id" => "#DEL002",
        "items" => [
            ["name" => "Round Gallon 18.9L", "code" => "SP-204-101", "status" => "Ready"],
            ["name" => "20L Slim Container Gallon", "code" => "SP-204-102", "status" => "Ready"]
        ]
    ],
    3 => [
        "customer_name" => "Hanami Garduque",
        "address" => "456 Elm St, Batangas",
        "status" => "Ready for Pickup",
        "order_id" => "#DEL003",
        "items" => [
            ["name" => "Slim Box type Gallon (20L)", "code" => "SP-204-201", "status" => "Ready"]
        ]
    ],
    4 => [
        "customer_name" => "Michael Tan",
        "address" => "789 Oak St, Batangas",
        "status" => "Ready for Pickup",
        "order_id" => "#DEL004",
        "items" => [
            ["name" => "18.9L Round Gallon", "code" => "SP-204-301", "status" => "Ready"]
        ],
        "distance" => "2.5 km",
        "eta" => "6 mins"
    ],
    5 => [
        "customer_name" => "Anna Lopez",
        "address" => "10 Pine St, Batangas",
        "status" => "Ready for Pickup",
        "order_id" => "#DEL005",
        "items" => [
            ["name" => "20L Slim Container Gallon", "code" => "SP-204-401", "status" => "Ready"]
        ],
        "distance" => "1.8 km",
        "eta" => "4 mins"
    ]
];

// Validate user_id and fetch corresponding order
if (!isset($orders[$user_id])) {
    echo "Order not found.";
    exit;
}

$order = $orders[$user_id];
$customer_name = $order['customer_name'];
$address = $order['address'];
$status = $order['status'];
$order_id = $order['order_id'];
$items = $order['items'];
$distance = $order['distance'] ?? "N/A";
$eta = $order['eta'] ?? "N/A";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Pickup | RCH Water</title>

<!-- LEAFLET MAP -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
body { 
    font-family:'Poppins',sans-serif; 
    background:#f4f6fa; 
    margin:0; 
    padding-top: 120px;
    color:#333; 
}

.header { 
    background-color:#2196F3; 
    color:white; 
    padding:30px 20px 60px;
    border-radius:0 0 40px 40px; 
    position: fixed; 
    top: 0;
    left: 0;
    width: 90%;
    z-index: 1000;
}

.back-btn { 
    position:absolute; top:10px; left:20px; font-size:22px; cursor:pointer; 
}

.header h3 { margin:10px 0 0; font-weight:600; font-size:18px; }
.header p { margin:5px 0 0; font-size:14px; }

.status-badge { 
    position:absolute; 
    top:30px; 
    right:20px; 
    background-color:#FF6F61; 
    color:white; 
    font-size:12px; 
    padding:5px 12px; 
    border-radius:20px; 
}

.card { 
    background:white; 
    border-radius:15px; 
    box-shadow:0 2px 8px rgba(0,0,0,0.1); 
    margin:20px; padding:15px; 
}

.step { display:flex; align-items:center; margin:10px 0; }
.circle { width:22px; height:22px; border-radius:50%; background:#ccc; color:white; display:flex; justify-content:center; align-items:center; font-size:12px; margin-right:10px; }
.active-step { background-color:#4CAF50; }

.items {
    background:white; border-radius:15px; margin:20px; padding:15px; 
    box-shadow:0 2px 8px rgba(0,0,0,0.1);
}

.item { 
    display:flex; justify-content:space-between; align-items:center; 
    background:#f4f6fa; border-radius:10px; padding:10px; margin-bottom:10px; 
}

.item-status { 
    background:#8BC34A; color:white; border-radius:12px; padding:2px 8px; font-size:11px; 
}

/* Map */
#map {
    height:200px;
    margin-top:10px;
    border-radius:15px;
    cursor: pointer;
}
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

<!-- DELIVERY ADDRESS + MAP IN SAME CARD -->
<div class="card">
    <strong>Delivery Address</strong>
    <p style="font-size:13px;">📍 <?= htmlspecialchars($address) ?></p>

    <!-- Small Map -->
    <div id="map" onclick="openFullMap()"></div>
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

<script>
// Redirect to Full Map
function openFullMap() {
    window.location.href = "full_map.php?id=<?= $user_id ?>";
}

// CUSTOMER COORD (same as before)
let customerLat = 13.88;  
let customerLng = 121.11;

navigator.geolocation.getCurrentPosition(function(pos) {
    let riderLat = pos.coords.latitude;
    let riderLng = pos.coords.longitude;

    let map = L.map('map').setView([customerLat, customerLng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19
    }).addTo(map);

    L.marker([customerLat, customerLng]).addTo(map).bindPopup("Customer Location");
    L.marker([riderLat, riderLng]).addTo(map).bindPopup("Your Location");
});
</script>

</body>
</html>
