<?php
session_start();
require_once '../Database/db_connection.php';

// Rider session
$rider_name = $_SESSION['rider_name'] ?? "Rider";

// Database connection
$db = new Database();
$conn = $db->getConnect();

// FETCH ACTIVE DELIVERIES
$active_stmt = $conn->prepare("
    SELECT u.user_id, u.name, u.address 
    FROM users u
    WHERE u.role = 'customer'
");
$active_stmt->execute();
$active_orders = $active_stmt->fetchAll(PDO::FETCH_ASSOC);

// FETCH PENDING DELIVERIES
$pending_stmt = $conn->prepare("
    SELECT u.user_id, u.name, u.address 
    FROM users u
    WHERE u.status = 'pending'
");
$pending_stmt->execute();
$pending_orders = $pending_stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rider Dashboard | RCH Water</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f6fa;
            margin: 0;
            padding: 0;
        }

        .header {
            background-color: #2196F3;
            color: white;
            padding: 10px 20px 20px;
            border-radius: 0 0 10px 10px;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .rider-name {
            font-size: 20px;
            font-weight: 600;
        }

        .current-datetime {
            font-size: 14px;
            margin-top: 5px;
        }

        .header-cards {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }

        .dashboard-card {
            flex: 1;
            height: 80px;
            padding: 15px 10px;
            background-color: #FFFFFF4D;
            border-radius: 10px;
            text-align: center;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s;
        }

        .dashboard-card:hover {
            transform: scale(1.05);
        }

        .deliveries {
            margin: 20px;
        }

        .deliveries h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .delivery-card {
            background-color: white;
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .delivery-header {
            display: flex;
            justify-content: space-between;
        }

        .delivery-info {
            font-size: 13px;
            color: #666;
            margin: 5px 0 10px;
        }

        .status {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            color: white;
        }

        .ontheway {
            background-color: #4CAF50;
        }

        .ready {
            background-color: #FF6F61;
        }

        .details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: #333;
        }

        .start-btn,
        .view-btn {
            width: 100%;
            padding: 8px;
            border-radius: 10px;
            border: none;
            font-size: 14px;
        }

        .start-btn {
            background-color: #1E90FF;
            color: white;
        }

        .view-btn {
            background-color: rgb(246, 249, 252);
            color: gray;
        }
    </style>
</head>

<body>

    <div class="header d-flex flex-column">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="rider-name"><?= htmlspecialchars($rider_name); ?></h2>
                <div id="datetime" class="current-datetime"></div>
            </div>
            <button onclick="window.location.href='notification.php'" class="btn btn-link text-white position-relative" style="font-size: 20px;">
                <i class="bi bi-bell"></i>
                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
            </button>
        </div>

        <div class="header-cards">
            <div class="dashboard-card" onclick="scrollToTop()">Today's Delivery</div>
            <div class="dashboard-card" onclick="scrollToPending()">Pending Delivery</div>
            <div class="dashboard-card">Completed</div>
        </div>
    </div>

    <!-- ACTIVE -->
    <div class="deliveries" id="active-deliveries">
        <h3>Active Deliveries</h3>

        <?php if (count($active_orders) === 0): ?>
            <p>No active deliveries.</p>
        <?php endif; ?>

        <?php foreach ($active_orders as $row): ?>
            <div class="delivery-card">
                <div class="delivery-header">
                    <h4><?= htmlspecialchars($row['name']) ?></h4>
                    <span class="status ontheway">On the Way</span>
                </div>
                <div class="delivery-info">📍 <?= htmlspecialchars($row['address']) ?></div>

                <div class="details">
                    <span>Items: Water Gallon</span>
                    <span>Distance: --</span>
                    <span>ETA: --</span>
                </div>

                <button class="view-btn" onclick="window.location.href='order_pickup.php?id=<?= $row['user_id'] ?>'">View Delivery</button>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- PENDING -->
    <div class="deliveries" id="pending-deliveries">
        <h3>Pending Deliveries</h3>

        <?php if (count($pending_orders) === 0): ?>
            <p>No pending deliveries.</p>
        <?php endif; ?>

        <?php foreach ($pending_orders as $row): ?>
            <div class="delivery-card">
                <div class="delivery-header">
                    <h4><?= htmlspecialchars($row['name']) ?></h4>
                    <span class="status ready">Pending</span>
                </div>

                <div class="delivery-info">📍 <?= htmlspecialchars($row['address']) ?></div>

                <div class="details">
                    <span>Items: Water Gallon</span>
                    <span>Distance: --</span>
                    <span>ETA: --</span>
                </div>

                <button class="start-btn" onclick="startDelivery(<?= $row['user_id'] ?>)">Start Delivery</button>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        function startDelivery(id) {
            window.location.href = "order_pickup.php?id=" + id;
        }

        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function scrollToPending() {
            const el = document.getElementById('pending-deliveries');
            const y = el.getBoundingClientRect().top + window.pageYOffset - 180;
            window.scrollTo({ top: y, behavior: 'smooth' });
        }

        function updateDateTime() {
            const n = new Date();
            const m = String(n.getMonth() + 1).padStart(2, '0');
            const d = String(n.getDate()).padStart(2, '0');
            const y = n.getFullYear();
            document.getElementById('datetime').innerText = `${m}/${d}/${y}`;
        }

        updateDateTime();
        setInterval(updateDateTime, 60000);
    </script>

</body>
</html>
