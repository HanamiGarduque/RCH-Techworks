<?php
session_start();
<<<<<<< HEAD
<<<<<<< HEAD
require_once '../Database/db_connection.php';

// Redirect if rider not logged in
if (!isset($_SESSION['rider_id'])) {
    header("Location: rider_login.php");
    exit();
}

$rider_id = $_SESSION['rider_id'];
$delivery_id = isset($_GET['delivery_id']) ? intval($_GET['delivery_id']) : 0;

$db = new Database();
$conn = $db->getConnect();

// Fetch delivery info
$stmt = $conn->prepare("SELECT * FROM rider_deliveries WHERE delivery_id = :delivery_id AND rider_id = :rider_id LIMIT 1");
$stmt->bindParam(':delivery_id', $delivery_id);
$stmt->bindParam(':rider_id', $rider_id);
$stmt->execute();
$delivery = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$delivery) {
    die("Delivery not found.");
}

// Fetch items for this delivery
$stmt_items = $conn->prepare("SELECT * FROM rider_delivery_items WHERE delivery_id = :delivery_id");
$stmt_items->bindParam(':delivery_id', $delivery_id);
$stmt_items->execute();
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

// Start delivery if button clicked
if (isset($_POST['start_delivery'])) {
    $update = $conn->prepare("UPDATE rider_deliveries SET status = 'On the Way', started_at = NOW() WHERE delivery_id = :delivery_id AND rider_id = :rider_id");
    $update->bindParam(':delivery_id', $delivery_id);
    $update->bindParam(':rider_id', $rider_id);
    $update->execute();
    header("Location: delivery_info.php?delivery_id=$delivery_id");
    exit();
}

// Map customer info
$order_id = $delivery['delivery_code'];
$customer_name = $delivery['customer_name'];
$address = $delivery['address'];
$instructions = $delivery['special_instructions'] ?? '';
$status = $delivery['status'];
=======
=======
>>>>>>> e70e61c6d9b34d520c5eb233fb632ee3492ace3b
// Example only — later connect this dynamically to your database
$order_id = "#DEL001";
$customer_name = "Hanami Garduque";
$address = "387 Washington St, Batangas";
$items = [
    ["name" => "Slim Box type Gallon (20L)", "code" => "SP-204-021", "status" => "Ready"],
    ["name" => "Slim Box type Gallon (20L)", "code" => "SP-204-022", "status" => "Ready"]
];
$instructions = "Call upon arrival.";
<<<<<<< HEAD
>>>>>>> e40151c (Rider mobile view)
=======
>>>>>>> e70e61c6d9b34d520c5eb233fb632ee3492ace3b
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Delivery Info | RCH Water</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
<<<<<<< HEAD
<<<<<<< HEAD
    body { font-family: 'Poppins', sans-serif; background-color: #f4f6fa; margin: 0; padding: 0; color: #333; }
    .header { background-color: #2196F3; color: white; padding: 30px 20px 60px; border-radius: 0 0 40px 40px; position: relative; }
    .back-btn { position: absolute; top: 25px; left: 20px; font-size: 22px; cursor: pointer; }
    .header h3 { margin: 10px 0 0; font-weight: 600; font-size: 18px; }
    .header p { margin: 5px 0 0; font-size: 14px; }
    .status-badge { position: absolute; top: 30px; right: 20px; background-color: <?= $status == 'On the Way' ? '#4CAF50' : '#8BC34A'; ?>; color: white; font-size: 12px; padding: 5px 12px; border-radius: 20px; }

    .card, .items, .special { background-color: white; border-radius: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin: 20px; padding: 15px; }
    .step { display: flex; align-items: center; margin: 10px 0; }
    .circle { width: 22px; height: 22px; border-radius: 50%; background-color: #ccc; color: white; display: flex; justify-content: center; align-items: center; font-size: 12px; margin-right: 10px; }
    .active-step { background-color: #4CAF50; }
    .step span { font-size: 13px; }
    .step small { display: block; font-size: 11px; color: #777; }

    .btn { display: inline-block; text-align: center; border: none; border-radius: 20px; cursor: pointer; font-size: 14px; padding: 10px 0; transition: background 0.2s; }
    .btn-primary { background-color: #1E90FF; color: white; width: 100%; margin-top: 10px; }
    .btn-primary:hover { background-color: #0b7dda; }
    .btn-outline { background-color: #fff; color: #1E90FF; border: 1px solid #1E90FF; width: 48%; }

    .item { display: flex; justify-content: space-between; align-items: center; background-color: #f4f6fa; border-radius: 10px; padding: 10px; margin-bottom: 10px; }
    .item-info { font-size: 13px; }
    .item-status { background-color: #8BC34A; color: white; border-radius: 12px; padding: 2px 8px; font-size: 11px; }

    .start-btn { background-color: #1E90FF; color: white; border: none; border-radius: 25px; font-size: 16px; padding: 12px 0; width: 90%; display: block; margin: 20px auto; cursor: pointer; }
    .start-btn:hover { background-color: #0b7dda; }
=======
=======
>>>>>>> e70e61c6d9b34d520c5eb233fb632ee3492ace3b
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
<<<<<<< HEAD
>>>>>>> e40151c (Rider mobile view)
=======
>>>>>>> e70e61c6d9b34d520c5eb233fb632ee3492ace3b
</style>
</head>
<body>

<div class="header">
<<<<<<< HEAD
<<<<<<< HEAD
    <div class="back-btn" onclick="window.location.href='dashboard.php'">←</div>
    <span class="status-badge"><?= htmlspecialchars($status) ?></span>
    <p>Delivery <?= htmlspecialchars($order_id) ?></p>
=======
    <div class="back-btn" onclick="history.back()">←</div>
    <span class="status-badge">Active</span>
    <p>Delivery <?= $order_id ?></p>
>>>>>>> e40151c (Rider mobile view)
=======
    <div class="back-btn" onclick="history.back()">←</div>
    <span class="status-badge">Active</span>
    <p>Delivery <?= $order_id ?></p>
>>>>>>> e70e61c6d9b34d520c5eb233fb632ee3492ace3b
    <h3><?= htmlspecialchars($customer_name) ?></h3>
</div>

<div class="card">
<<<<<<< HEAD
<<<<<<< HEAD
    <div class="step"><div class="circle <?= $status != 'Ready for Pickup' ? 'active-step' : ''; ?>">1</div> <div><span>Ready for Pickup</span><small>Order prepared</small></div></div>
    <div class="step"><div class="circle <?= $status == 'On the Way' || $status == 'Completed' ? 'active-step' : ''; ?>">2</div> <div><span>On the Way</span><small>Heading to location</small></div></div>
    <div class="step"><div class="circle <?= $status == 'Completed' ? 'active-step' : ''; ?>">3</div> <div><span>Completed</span><small>Delivered</small></div></div>
=======
    <div class="step"><div class="circle active-step">1</div> <div><span>Ready for Pickup</span><small>Order prepared</small></div></div>
    <div class="step"><div class="circle">2</div> <div><span>On the Way</span><small>Heading to location</small></div></div>
    <div class="step"><div class="circle">3</div> <div><span>Completed</span><small>Delivered</small></div></div>
>>>>>>> e40151c (Rider mobile view)
=======
    <div class="step"><div class="circle active-step">1</div> <div><span>Ready for Pickup</span><small>Order prepared</small></div></div>
    <div class="step"><div class="circle">2</div> <div><span>On the Way</span><small>Heading to location</small></div></div>
    <div class="step"><div class="circle">3</div> <div><span>Completed</span><small>Delivered</small></div></div>
>>>>>>> e70e61c6d9b34d520c5eb233fb632ee3492ace3b
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
<<<<<<< HEAD
<<<<<<< HEAD
                <?= htmlspecialchars($item['item_name']) ?><br>
                <small><?= htmlspecialchars($item['item_code']) ?></small>
=======
                <?= htmlspecialchars($item['name']) ?><br>
                <small><?= htmlspecialchars($item['code']) ?></small>
>>>>>>> e40151c (Rider mobile view)
=======
                <?= htmlspecialchars($item['name']) ?><br>
                <small><?= htmlspecialchars($item['code']) ?></small>
>>>>>>> e70e61c6d9b34d520c5eb233fb632ee3492ace3b
            </div>
            <div class="item-status"><?= htmlspecialchars($item['status']) ?></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="special">
    <strong>Special Instructions</strong><br>
    <?= htmlspecialchars($instructions) ?>
</div>

<<<<<<< HEAD
<<<<<<< HEAD
<?php if ($status == 'Ready for Pickup'): ?>
<form method="POST">
    <button type="submit" name="start_delivery" class="start-btn">Start Delivery</button>
</form>
<?php endif; ?>

<script>
function openMap() { window.open("https://www.openstreetmap.org", "_blank"); }
function callCustomer() { alert("Calling customer..."); }
function messageCustomer() { alert("Opening messaging app..."); }
=======
=======
>>>>>>> e70e61c6d9b34d520c5eb233fb632ee3492ace3b
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
<<<<<<< HEAD
>>>>>>> e40151c (Rider mobile view)
=======
>>>>>>> e70e61c6d9b34d520c5eb233fb632ee3492ace3b
</script>

</body>
</html>
