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
<style>
body { 
    font-family:'Poppins',sans-serif; 
    background:#f4f6fa; 
    margin:0; 
    padding-top: 120px; /* space for fixed header */
    color:#333; 
}
.header { 
    background-color:#2196F3; 
    color:white; 
    padding:30px 20px 60px; 
    border-radius:0 0 40px 40px; 
    position: fixed; /* fixed at top */
    top: 0;
    left: 0;
    width: 90%;
    z-index: 1000;
    overflow: hidden; /* hide overflow */
}
.back-btn { 
    position:absolute; 
    top:10px; 
    left:20px; 
    font-size:22px; 
    cursor:pointer; 
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

.card { background:white; border-radius:15px; box-shadow:0 2px 8px rgba(0,0,0,0.1); margin:20px; padding:15px; }
.step { display:flex; align-items:center; margin:10px 0; }
.circle { width:22px; height:22px; border-radius:50%; background:#ccc; color:white; display:flex; justify-content:center; align-items:center; font-size:12px; margin-right:10px; }
.active-step { background-color:#4CAF50; }

.items { background:white; border-radius:15px; margin:20px; padding:15px; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
.item { display:flex; justify-content:space-between; align-items:center; background:#f4f6fa; border-radius:10px; padding:10px; margin-bottom:10px; }
.item-status { background:#8BC34A; color:white; border-radius:12px; padding:2px 8px; font-size:11px; }

.details { display:flex; justify-content:space-between; margin-top:10px; font-size:13px; color:#333; }

.action-btn { width:90%; margin:20px auto; display:block; padding:12px 0; border:none; border-radius:25px; color:white; font-size:16px; cursor:pointer; background:#1E90FF; }
.action-btn:hover { background:#0b7dda; }
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
    <div class="step"><div class="circle" id="completedCircle">3</div> <span>Completed</span></div>
</div>

<div class="card">
    <strong>Delivery Address</strong>
    <p style="font-size:13px;">📍 <?= htmlspecialchars($address) ?></p>
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


<!-- Pickup Order button -->
<button class="action-btn" onclick="pickupOrder(<?= $user_id ?>)">Pickup Order</button>

<script>
function pickupOrder(userId) {
    // Redirect to delivery_info.php with the specific user ID
    window.location.href = "delivery_info.php?id=" + userId;
}
</script>


</body>
</html>
