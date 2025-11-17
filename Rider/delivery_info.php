<?php
session_start();
require_once '../Database/db_connection.php';

$db = new Database();          // Your Database class
$conn = $db->getConnect();     // Assuming this now returns a PDO connection

if (!$conn) {
    die("Database connection failed");
}

// Get customer ID from URL
if (!isset($_GET['id'])) {
    die("Customer ID not provided.");
}
$customer_id = intval($_GET['id']);

// PDO way
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :id");
$stmt->bindValue(':id', $customer_id, PDO::PARAM_INT);
$stmt->execute();
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    die("Customer not found");
}

// Now you can use $customer['name'], $customer['address'], etc.


// Sample items, replace with actual query if you have order items table
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

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
body {
    font-family: 'Poppins', sans-serif;
    background:#f4f6fa;
    margin:0;
    padding:0;
    color:#333;
}

/* Header */
.header {
    background-color:#2196F3;
    color:white;
    padding:30px 20px 60px;
    border-radius:0 0 40px 40px;
    position:relative;
}
.back-btn {
    position:absolute;
    top:10px;
    left:20px;
    font-size:22px;
    cursor:pointer;
}
.header h3 {
    margin:10px 0 0;
    font-weight:600;
    font-size:18px;
}
.header p {
    margin:5px 0 0;
    font-size:14px;
}
.status-badge {
    position:absolute;
    top:30px;
    right:20px;
    background-color:#8BC34A;
    color:white;
    font-size:12px;
    padding:5px 12px;
    border-radius:20px;
}

/* Card */
.card {
    background:white;
    border-radius:15px;
    box-shadow:0 2px 8px rgba(0,0,0,0.1);
    margin:20px;
    padding:15px;
}

/* Items */
.items {
    background:white;
    border-radius:15px;
    margin:20px;
    padding:15px;
    box-shadow:0 2px 8px rgba(0,0,0,0.1);
}
.item {
    display:flex;
    justify-content:space-between;
    align-items:center;
    background:#f4f6fa;
    border-radius:10px;
    padding:10px;
    margin-bottom:10px;
}
.item-info {
    font-size:13px;
}
.item-status {
    background-color:#8BC34A;
    color:white;
    border-radius:12px;
    padding:2px 8px;
    font-size:11px;
}

/* Start Delivery Button */
.start-btn {
    background-color:#1E90FF;
    color:white;
    border:none;
    border-radius:25px;
    font-size:16px;
    padding:12px 0;
    width:90%;
    display:block;
    margin:20px auto;
    cursor:pointer;
    transition:0.2s;
}
.start-btn:hover {
    background-color:#0b7dda;
}

/* Map Styles */
#map {
    height:200px;
    width:95%;
    margin:10px auto;
    border-radius:10px;
}
.direction-control {
    background: rgba(255,255,255,0.8);
    padding:5px 10px;
    border-radius:5px;
    font-size:13px;
    box-shadow:0 2px 5px rgba(0,0,0,0.2);
}
</style>
</head>
<body>

<div class="header">
    <div class="back-btn" onclick="history.back()">←</div>
    <span class="status-badge">Active</span>
    <p>Delivery <?= htmlspecialchars($customer['order_id'] ?? '#DEL001') ?></p>
    <h3><?= htmlspecialchars($customer['name'] ?? 'Customer Name') ?></h3>
</div>

<div class="card">
    <strong>Delivery Address</strong>
    <p style="font-size:13px; margin:5px 0;">📍 <?= htmlspecialchars($customer['address'] ?? 'Address Here') ?></p>
    <div id="map"></div>
</div>

<div class="items">
    <strong>Items (<?= count($items) ?>)</strong>
    <?php foreach ($items as $item): ?>
    <div class="item">
        <div class="item-info"><?= htmlspecialchars($item['name']) ?><br><small><?= htmlspecialchars($item['code']) ?></small></div>
        <div class="item-status"><?= htmlspecialchars($item['status']) ?></div>
    </div>
    <?php endforeach; ?>
</div>

<button class="start-btn" onclick="startDelivery()">Start Delivery</button>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Map Initialization
let lat = <?= $customer['lat'] ?? 13.7565 ?>;
let lng = <?= $customer['lng'] ?? 121.0583 ?>;
let map = L.map('map').setView([lat, lng], 15);

// Click map to open full map
map.on('click', function () {
    window.location.href = "full_map.php?lat="+lat+"&lng="+lng+"&customer=<?= urlencode($customer['name'] ?? 'Customer') ?>";
});

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);

// Route preview
const routeCoords = [
    [lat, lng],
    [lat+0.0015, lng+0.0020]
];
L.polyline(routeCoords, {color:'#007bff', weight:3}).addTo(map);
L.marker([lat, lng]).addTo(map).bindPopup("<?= htmlspecialchars($customer['name'] ?? 'Customer') ?>").openPopup();

// Turn info
const turnControl = L.control({position: 'topleft'});
turnControl.onAdd = function() {
    const div = L.DomUtil.create('div', 'direction-control');
    div.innerHTML = '⬅ Turn Left – 200 M';
    return div;
};
turnControl.addTo(map);

// Start Delivery Button
function startDelivery() {
    window.open("full_map.php?lat="+lat+"&lng="+lng+"&customer=<?= urlencode($customer['name'] ?? 'Customer') ?>", "_blank");
}
</script>
</body>
</html>
