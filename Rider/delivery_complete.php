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
    $stmt = $conn->prepare("SELECT name, address FROM users WHERE user_id = :id");
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$customer) {
        echo "Customer not found.";
        exit;
    }

    $customer_name = $customer['name'];
    $address = $customer['address'];

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}

// Hardcoded sample items
$items = [
    ["name" => "Slim Box type Gallon (20L)", "code" => "SP-204-021", "status" => "Ready"],
    ["name" => "Slim Box type Gallon (20L)", "code" => "SP-204-022", "status" => "Ready"]
];

$order_id = isset($_GET['id']) ? $_GET['id'] : "Unknown";

$status = "Completed";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Delivery Complete | RCH Water</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

body { font-family:'Poppins',sans-serif; background:#f4f6fa; margin:0; padding:0; color:#333; }

.header { background-color:#2196F3; color:white; padding:30px 20px 60px; border-radius:0 0 40px 40px; position:relative; }
.back-btn { position:absolute; top:10px; left:20px; font-size:22px; cursor:pointer; }
.header h3 { margin:10px 0 0; font-weight:600; font-size:18px; }
.header p { margin:5px 0 0; font-size:14px; }
.status-badge { position:absolute; top:30px; right:20px; background-color:#4CAF50; color:white; font-size:12px; padding:5px 12px; border-radius:20px; }

.card { background:white; border-radius:15px; box-shadow:0 2px 8px rgba(0,0,0,0.1); margin:20px; padding:15px; }
.step { display:flex; align-items:center; margin:10px 0; }
.circle { width:22px; height:22px; border-radius:50%; background:#ccc; color:white; display:flex; justify-content:center; align-items:center; font-size:12px; margin-right:10px; }
.active-step { background-color:#4CAF50; }

.items { background:white; border-radius:15px; margin:20px; padding:15px; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
.item { display:flex; justify-content:space-between; align-items:center; background:#f4f6fa; border-radius:10px; padding:10px; margin-bottom:10px; }
.item-status { background:#8BC34A; color:white; border-radius:12px; padding:2px 8px; font-size:11px; }

.scan-btn { display:block; width:90%; margin:20px auto; padding:12px 0; border:none; border-radius:25px; font-size:16px; background:#1E90FF; color:white; cursor:pointer; }
.scan-btn:hover { background:#0b7dda; text-align:center; }

#notification {
    display: none;
    position: fixed;
    top: 40%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #4CAF50;
    color: white;
    padding: 25px 30px;
    border-radius: 15px;
    text-align: center;
    z-index: 999;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}

#notification button {
    margin-top: 15px;
    padding: 8px 20px;
    border: none;
    border-radius: 20px;
    background: white;
    color: #4CAF50;
    font-weight: 600;
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
    <div class="step"><div class="circle active-step">3</div> <span>Completed</span></div>
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

<button class="scan-btn" onclick="scanQR()">Scan QR</button>

<div id="notification">
    <div>Delivery Successful ✅</div>
    <button onclick="goDashboard()">OK</button>
</div>

<script>
function scanQR() {
    document.getElementById('notification').style.display = 'block';
}

function goDashboard() {
    window.location.href = 'dashboard.php';
}
</script>

</body>
</html>
