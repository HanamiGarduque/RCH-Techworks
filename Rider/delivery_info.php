<?php
session_start();
// Example only — later connect this dynamically to your database
$order_id = "#DEL001";
$customer_name = "Hanami Garduque";
$address = "387 Washington St, Batangas";
$items = [
    ["name" => "Slim Box type Gallon (20L)", "code" => "SP-204-021", "status" => "Ready"],
    ["name" => "Slim Box type Gallon (20L)", "code" => "SP-204-022", "status" => "Ready"]
];
$instructions = "Call upon arrival.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Delivery Info | RCH Water</title>
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
        top: 25px;
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
        background-color: #8BC34A;
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
    .active-step {
        background-color: #4CAF50;
    }
    .step span {
        font-size: 13px;
    }
    .step small {
        display: block;
        font-size: 11px;
        color: #777;
    }

    .btn {
        display: inline-block;
        text-align: center;
        border: none;
        border-radius: 20px;
        cursor: pointer;
        font-size: 14px;
        padding: 10px 0;
        transition: background 0.2s;
    }
    .btn-primary {
        background-color: #1E90FF;
        color: white;
        width: 100%;
        margin-top: 10px;
    }
    .btn-primary:hover {
        background-color: #0b7dda;
    }
    .btn-outline {
        background-color: #fff;
        color: #1E90FF;
        border: 1px solid #1E90FF;
        width: 48%;
    }

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
    .item-info {
        font-size: 13px;
    }
    .item-status {
        background-color: #8BC34A;
        color: white;
        border-radius: 12px;
        padding: 2px 8px;
        font-size: 11px;
    }

    .special {
        background-color: #e0e0e0;
        border-radius: 15px;
        padding: 10px;
        margin: 20px;
        font-size: 13px;
    }

    .start-btn {
        background-color: #1E90FF;
        color: white;
        border: none;
        border-radius: 25px;
        font-size: 16px;
        padding: 12px 0;
        width: 90%;
        display: block;
        margin: 20px auto;
        cursor: pointer;
    }
    .start-btn:hover {
        background-color: #0b7dda;
    }
</style>
</head>
<body>

<div class="header">
    <div class="back-btn" onclick="history.back()">←</div>
    <span class="status-badge">Active</span>
    <p>Delivery <?= $order_id ?></p>
    <h3><?= htmlspecialchars($customer_name) ?></h3>
</div>

<div class="card">
    <div class="step"><div class="circle active-step">1</div> <div><span>Ready for Pickup</span><small>Order prepared</small></div></div>
    <div class="step"><div class="circle">2</div> <div><span>On the Way</span><small>Heading to location</small></div></div>
    <div class="step"><div class="circle">3</div> <div><span>Completed</span><small>Delivered</small></div></div>
</div>

<div class="card">
    <strong>Delivery Address</strong>
    <p style="font-size:13px; margin:5px 0;">📍 <?= htmlspecialchars($address) ?></p>
    <button class="btn btn-primary" onclick="openMap()">Open Navigation</button>
    <div style="display:flex;justify-content:space-between;margin-top:10px;">
        <button class="btn btn-outline" onclick="callCustomer()">📞 Call</button>
        <button class="btn btn-outline" onclick="messageCustomer()">💬 Message</button>
    </div>
</div>

<div class="items">
    <strong>Items (<?= count($items) ?>)</strong>
    <?php foreach ($items as $item): ?>
        <div class="item">
            <div class="item-info">
                <?= htmlspecialchars($item['name']) ?><br>
                <small><?= htmlspecialchars($item['code']) ?></small>
            </div>
            <div class="item-status"><?= htmlspecialchars($item['status']) ?></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="special">
    <strong>Special Instructions</strong><br>
    <?= htmlspecialchars($instructions) ?>
</div>

<button class="start-btn" onclick="startDelivery()">Start Delivery</button>

<script>
function openMap() {
    window.open("https://www.openstreetmap.org", "_blank");
}
function callCustomer() {
    alert("Calling customer...");
}
function messageCustomer() {
    alert("Opening messaging app...");
}
function startDelivery() {
    window.location.href = "order_arrival.php";
}
</script>

</body>
</html>
