<?php 
session_start();
require_once '../Database/db_connection.php';

// Validate user_id
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($user_id <= 0) {
    echo "Invalid user.";
    exit;
}

// Rider name
$rider_name = $_SESSION['rider_name'] ?? "Rider";

// Database connection
$db = new Database();
$conn = $db->getConnect();

// Fetch customer details
$customer_stmt = $conn->prepare("
    SELECT name, address 
    FROM users 
    WHERE user_id = :id
");
$customer_stmt->bindParam(':id', $user_id);
$customer_stmt->execute();
$customer = $customer_stmt->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    echo "Customer not found.";
    exit;
}

// Hardcoded orders
$orders = [
    4 => [
        "order_id" => "#DEL004",
        "status" => "Ready for Pickup",
        "items" => [
            ["name" => "20L Slim Box Gallon", "code" => "SP-204-021", "status" => "Ready"],
            ["name" => "20L Slim Box Gallon", "code" => "SP-204-022", "status" => "Ready"]
        ]
    ],
    5 => [
        "order_id" => "#DEL005",
        "status" => "Ready for Pickup",
        "items" => [
            ["name" => "18.9L Round Gallon", "code" => "SP-204-101", "status" => "Ready"],
            ["name" => "20L Slim Gallon", "code" => "SP-204-102", "status" => "Ready"]
        ]
    ],
    6 => [
        "order_id" => "#DEL006",
        "status" => "Ready for Pickup",
        "items" => [
            ["name" => "20L Slim Box Gallon", "code" => "SP-204-201", "status" => "Ready"]
        ]
    ],
    7 => [
        "order_id" => "#DEL007",
        "status" => "Ready for Pickup",
        "items" => [
            ["name" => "18.9L Round Gallon", "code" => "SP-204-301", "status" => "Ready"]
        ]
    ],
    8 => [
        "order_id" => "#DEL008",
        "status" => "Ready for Pickup",
        "items" => [
            ["name" => "20L Slim Gallon", "code" => "SP-204-401", "status" => "Ready"]
        ]
    ],
    9 => [
        "order_id" => "#DEL009",
        "status" => "Ready for Pickup",
        "items" => [
            ["name" => "20L Slim Gallon", "code" => "SP-204-401", "status" => "Ready"]
        ]
    ]
];

// Check if order exists
if (!isset($orders[$user_id])) {
    echo "No order assigned for this customer.";
    exit;
}

$order = $orders[$user_id];
$order_id = $order["order_id"];
$status = $order["status"];
$items = $order["items"];

$customer_name = $customer['name'];
$address = $customer['address'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Pickup</title>

<style>
:root {
    --header-height: 90px;       /* ADJUST HEADER HEIGHT */
    --header-padding: 18px;      /* ADJUST INNER SPACING */
    --header-radius: 28px;       /* ADJUST CURVED BOTTOM */
}

body {
    font-family: 'Poppins', sans-serif;
    background: #f4f6fa;
    margin: 0;
    padding-top: calc(var(--header-height) + 40px);
    color: #333;
}

/* REDUCED HEADER SIZE */
.header {
    background-color: #2196F3;
    color: white;
    padding: var(--header-padding);
    height: var(--header-height);
    border-radius: 0 0 var(--header-radius) var(--header-radius);
    position: fixed;
    top: 0;
    left: 0;
    width: 90%;
    z-index: 1000;
}

.back-btn {
    position: absolute;
    top: 10px;
    left: 20px;
    font-size: 22px;
    cursor: pointer;
}

.status-badge {
    position: absolute;
    top: 14px;
    right: 20px;
    background-color: #FF6F61;
    color: white;
    font-size: 12px;
    padding: 4px 10px;
    border-radius: 16px;
}

.header p {
    margin: 36px 0 2px;
    font-size: 14px;
}

.header h3 {
    margin: 0;
    font-size: 20px;
}

.card {
    background: white;
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
    background: #ccc;
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 12px;
    margin-right: 10px;
}

.active-step {
    background-color: #4CAF50;
}

.items {
    background: white;
    border-radius: 15px;
    margin: 20px;
    padding: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f4f6fa;
    border-radius: 10px;
    padding: 10px;
    margin-bottom: 10px;
}

.item-status {
    background: #8BC34A;
    color: white;
    border-radius: 12px;
    padding: 2px 8px;
    font-size: 11px;
}

.action-btn {
    width: 90%;
    margin: 20px auto;
    display: block;
    padding: 12px 0;
    border: none;
    border-radius: 25px;
    color: white;
    font-size: 16px;
    cursor: pointer;
    background: #1E90FF;
}
.action-btn:hover {
    background: #0b7dda;
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

<button class="action-btn" onclick="pickupOrder(<?= $user_id ?>)">Pickup Order</button>

<script>
function pickupOrder(userId) {
    window.location.href = "delivery_info.php?id=" + userId;
}
</script>

</body>
</html>
