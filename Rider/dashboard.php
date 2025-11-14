<?php
session_start();
<<<<<<< HEAD
<<<<<<< HEAD
require_once '../Database/db_connection.php';

// Redirect if rider is not logged in
if (!isset($_SESSION['rider_id'])) {
    header("Location: rider_login.php");
    exit();
}

$rider_id = $_SESSION['rider_id'];
$rider_name = $_SESSION['rider_name'];

// Database connection
$db = new Database();
$conn = $db->getConnect();

// Fetch deliveries assigned to this rider
$stmt = $conn->prepare("SELECT * FROM rider_deliveries WHERE rider_id = :rider_id ORDER BY status ASC, delivery_id DESC");
$stmt->bindParam(':rider_id', $rider_id);
$stmt->execute();
$deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count statistics
$today = date('Y-m-d');
$stmt_today = $conn->prepare("SELECT COUNT(*) FROM rider_deliveries WHERE rider_id = :rider_id AND DATE(created_at) = :today");
$stmt_today->bindParam(':rider_id', $rider_id);
$stmt_today->bindParam(':today', $today);
$stmt_today->execute();
$today_count = $stmt_today->fetchColumn();

$stmt_completed = $conn->prepare("SELECT COUNT(*) FROM rider_deliveries WHERE rider_id = :rider_id AND status = 'Completed'");
$stmt_completed->bindParam(':rider_id', $rider_id);
$stmt_completed->execute();
$completed_count = $stmt_completed->fetchColumn();

// Handle "Start Route" action (optional: save to database)
if (isset($_GET['start_delivery'])) {
    $delivery_id = intval($_GET['start_delivery']);
    $update = $conn->prepare("UPDATE rider_deliveries SET status = 'On the Way', started_at = NOW() WHERE delivery_id = :delivery_id AND rider_id = :rider_id");
    $update->bindParam(':delivery_id', $delivery_id);
    $update->bindParam(':rider_id', $rider_id);
    $update->execute();
    header("Location: dashboard.php");
    exit();
}
=======
// Example: replace with actual session login name
$rider_name = isset($_SESSION['rider_username']) ? $_SESSION['rider_username'] : "Joshua Garcia";
>>>>>>> e40151c (Rider mobile view)
=======
$rider_name = isset($_SESSION['rider_username']) ? $_SESSION['rider_username'] : "Rider";
>>>>>>> e70e61c (rider)
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rider Dashboard | RCH Water</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f4f6fa;
        margin: 0;
        padding: 0;
    }
    .header {
        background-color: #2196F3;
        color: white;
        padding: 30px 20px 70px;
        border-radius: 0 0 40px 40px;
        position: relative;
    }
<<<<<<< HEAD
    .header h2 { margin: 0; font-weight: 600; font-size: 22px; }
    .notif { position: absolute; top: 35px; right: 25px; font-size: 20px; cursor: pointer; }
    .stats { display: flex; justify-content: space-around; margin-top: 20px; }
=======
    .header h2 {
        margin: 0;
        font-weight: 600;
        font-size: 22px;
    }
    .notif {
        position: absolute;
        top: 35px;
        right: 25px;
        font-size: 20px;
        cursor: pointer;
    }
    .stats {
        display: flex;
        justify-content: space-around;
        margin-top: 20px;
    }
>>>>>>> e40151c (Rider mobile view)
    .card {
        background-color: rgba(255,255,255,0.3);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        padding: 15px 25px;
        text-align: center;
        color: white;
    }
<<<<<<< HEAD
    .card h3 { margin: 0; font-size: 14px; font-weight: 400; }
    .card p { margin: 5px 0 0; font-size: 18px; font-weight: 600; }

    .deliveries { margin: 30px 20px; }
    .deliveries h3 { font-size: 18px; font-weight: 600; margin-bottom: 10px; }
=======
    .card h3 {
        margin: 0;
        font-size: 14px;
        font-weight: 400;
    }
    .card p {
        margin: 5px 0 0;
        font-size: 18px;
        font-weight: 600;
    }
    .deliveries {
        margin: 30px 20px;
    }
    .deliveries h3 {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 10px;
    }
>>>>>>> e40151c (Rider mobile view)
    .delivery-card {
        background-color: white;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        padding: 15px;
    }
<<<<<<< HEAD
    .delivery-header { display: flex; justify-content: space-between; align-items: center; }
    .delivery-header h4 { margin: 0; font-size: 15px; font-weight: 600; }
=======
    .delivery-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .delivery-header h4 {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
    }
>>>>>>> e40151c (Rider mobile view)
    .status {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        color: white;
    }
<<<<<<< HEAD
    .ready { background-color: #FF6F61; }
    .ontheway { background-color: #4CAF50; }
    .delivery-info { font-size: 13px; color: #666; margin: 5px 0 10px; }
    .details { display: flex; justify-content: space-between; margin-bottom: 10px; color: #333; font-size: 13px; }
=======
    .ready {
        background-color: #FF6F61;
    }
    .ontheway {
        background-color: #4CAF50;
    }
    .delivery-info {
        font-size: 13px;
        color: #666;
        margin: 5px 0 10px;
    }
    .details {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        color: #333;
    }
>>>>>>> e40151c (Rider mobile view)
    .start-btn {
        background-color: #1E90FF;
        color: white;
        border: none;
        width: 100%;
        padding: 8px;
        border-radius: 10px;
        font-size: 14px;
        cursor: pointer;
    }
<<<<<<< HEAD
    .start-btn:hover { background-color: #0b7dda; }
=======
    .start-btn:hover {
        background-color: #0b7dda;
    }
>>>>>>> e40151c (Rider mobile view)
</style>
</head>
<body>

<div class="header">
    <h2><?= htmlspecialchars($rider_name); ?></h2>
<<<<<<< HEAD
<<<<<<< HEAD
    <span class="notif">🔔</span>
    <div class="stats">
        <div class="card">
            <h3>Today's Delivery</h3>
            <p><?= $today_count; ?></p>
        </div>
        <div class="card">
            <h3>Completed</h3>
            <p><?= $completed_count; ?></p>
=======
    <span class="bell" onclick="window.location.href='notifications.php'">🔔</span>
=======
    <span class="notif" onclick="window.location.href='notifications.php'">🔔</span>

>>>>>>> e70e61c (rider)
    <div class="stats">
        <div class="card">
            <h3>Today's Delivery</h3>
            <p id="todayCount">0</p>
        </div>
        <div class="card">
            <h3>Completed</h3>
<<<<<<< HEAD
            <p>1</p>
>>>>>>> e40151c (Rider mobile view)
=======
            <p id="completedCount">0</p>
>>>>>>> e70e61c (rider)
        </div>
    </div>
</div>

<div class="deliveries">
    <h3>Active Deliveries</h3>

<<<<<<< HEAD
<<<<<<< HEAD
    <?php foreach ($deliveries as $delivery): ?>
    <div class="delivery-card">
        <div class="delivery-header">
            <h4><?= htmlspecialchars($delivery['customer_name']); ?></h4>
            <span class="status <?= $delivery['status'] == 'On the Way' ? 'ontheway' : 'ready'; ?>">
                <?= htmlspecialchars($delivery['status']); ?>
            </span>
        </div>
        <div class="delivery-info">📍 <?= htmlspecialchars($delivery['address']); ?></div>
        <div class="details">
            <span><?= htmlspecialchars($delivery['quantity']); ?> Gallons</span>
            <span><?= htmlspecialchars($delivery['distance']); ?> km Distance</span>
            <span><?= htmlspecialchars($delivery['eta']); ?> min ETA</span>
        </div>
        <?php if ($delivery['status'] == 'Ready for Pickup'): ?>
            <a href="dashboard.php?start_delivery=<?= $delivery['delivery_id']; ?>">
                <button class="start-btn">Start Route</button>
            </a>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>

</div>

=======
    <div class="delivery-card">
        <div class="delivery-header">
            <h4>Hanami Garduque</h4>
            <span class="status ready">Ready for Pickup</span>
        </div>
        <div class="delivery-info">📍 123 Main St, Batangas</div>
        <div class="details">
            <span>2 Gallons</span>
            <span>1.4 km Distance</span>
            <span>10 min ETA</span>
        </div>
        <button class="start-btn" onclick="startRoute('Hanami Garduque')">Start Route</button>
    </div>

    <div class="delivery-card">
        <div class="delivery-header">
            <h4>Christine May Padua</h4>
            <span class="status ontheway">On the Way</span>
        </div>
        <div class="delivery-info">📍 387 Washington St, Batangas</div>
        <div class="details">
            <span>1 Gallon</span>
            <span>2.2 km Distance</span>
            <span>17 min ETA</span>
        </div>
        <button class="start-btn" onclick="startRoute('Christine May Padua')">Start Route</button>
    </div>
=======
    <!-- AUTO-GENERATED DELIVERIES -->
    <div id="deliveryList"></div>
>>>>>>> e70e61c (rider)
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    fetch('dashboard_backend.php')
        .then(res => res.json())
        .then(data => {
            console.log(data);

            document.getElementById("todayCount").innerText = data.today;
            document.getElementById("completedCount").innerText = data.completed;

            const container = document.getElementById("deliveryList");
            container.innerHTML = "";

            if (data.deliveries.length === 0) {
                container.innerHTML = "<p>No active deliveries.</p>";
                return;
            }

            data.deliveries.forEach(del => {
                const div = document.createElement("div");
                div.classList.add("delivery-card");

                let statusClass = del.status === "Ready for Pickup" ? "ready" : "ontheway";

                div.innerHTML = `
                    <div class="delivery-header">
                        <h4>${del.customer_name}</h4>
                        <span class="status ${statusClass}">${del.status}</span>
                    </div>

                    <div class="delivery-info">📍 ${del.address}</div>

                    <div class="details">
                        <span>${del.quantity} Gallons</span>
                        <span>${del.distance} km Distance</span>
                        <span>${del.eta} min ETA</span>
                    </div>

                    <button class="start-btn" onclick="startRoute('${del.customer_name}')">Start Route</button>
                `;

                container.appendChild(div);
            });
        })
        .catch(err => {
            console.error("Fetch error:", err);
        });
});

function startRoute(name) {
    alert("Starting route for " + name);
    window.location.href = "rider_map.php?customer=" + encodeURIComponent(name);
}
</script>

>>>>>>> e40151c (Rider mobile view)
</body>
</html>
