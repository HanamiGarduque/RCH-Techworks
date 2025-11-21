<?php
session_start();

// Hardcoded rider info
$rider_name = $_SESSION['rider_name'] ?? "Rider";

// Hardcoded Active Deliveries
$active_orders = [
    [
        "customer_name" => "RM Fontelera",
        "address" => "Purok 4 Mojon Tampoy, San Jose, Batangas",
        "user_id" => 1,
        "items" => "Slim Box type Gallon (20L)",
        "distance" => "2 km",
        "eta" => "5 mins"
    ],
    [
        "customer_name" => "Christine May Padua",
        "address" => "116 Purok 4 Sico, Lipa City, Batangas",
        "user_id" => 2,
        "items" => "Round Gallon 18.9L",
        "distance" => "3 km",
        "eta" => "7 mins"
    ]
];

// Hardcoded Pending Deliveries
$pending_orders = [
    [
        "name" => "Hanami Garduque",
        "address" => "456 Elm St, Batangas",
        "order_id" => 101,
        "user_id" => 3,
        "items" => "20L Slim Container Gallon",
        "distance" => "1.2 km",
        "eta" => "3 mins"
    ],
    [
        "name" => "Michael Tan",
        "address" => "789 Oak St, Batangas",
        "order_id" => 102,
        "user_id" => 4,
        "items" => "18.9L Round Gallon",
        "distance" => "2.5 km",
        "eta" => "6 mins"
    ],
    [
        "name" => "Anna Lopez",
        "address" => "10 Pine St, Batangas",
        "order_id" => 103,
        "user_id" => 5,
        "items" => "20L Slim Container Gallon",
        "distance" => "1.8 km",
        "eta" => "4 mins"
    ]
];
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
            font-weight: 400;
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
            align-items: center;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s;
        }

        .dashboard-card:hover {
            transform: scale(1.05);
        }

        .dashboard-card h3 {
            margin: 0;
            font-size: 14px;
        }

        .dashboard-card p {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            display: none;
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            padding: 15px;
        }

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

        .status {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            color: white;
        }

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

        .start-btn,
        .view-btn {
            width: 100%;
            padding: 8px;
            border-radius: 10px;
            font-size: 14px;
            cursor: pointer;
            border: none;
        }

        .start-btn {
            background-color: #1E90FF;
            color: white;
        }

        .start-btn:hover {
            background-color: #0b7dda;
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
            <button type="button" onclick="window.location.href='notification.php'" class="btn btn-link text-white position-relative" style="font-size: 20px;">
                <i class="bi bi-bell"></i>
                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
            </button>
        </div>

        <!-- Cards inside header -->
        <div class="header-cards">
            <div class="dashboard-card" onclick="scrollToTop()">Today's Delivery
                <p></p>
            </div>
            <div class="dashboard-card" onclick="scrollToPending()">Pending Delivery
                <p></p>
            </div>
            <div class="dashboard-card">Completed
                <p></p>
            </div>
        </div>
    </div>

    <!-- ACTIVE DELIVERIES -->
    <div class="deliveries" id="active-deliveries">
        <h3>Active Deliveries</h3>
        <?php foreach ($active_orders as $row): ?>
            <div class="delivery-card">
                <div class="delivery-header">
                    <h4><?= htmlspecialchars($row['customer_name']) ?></h4>
                    <span class="status ontheway">On the Way</span>
                </div>
                <div class="delivery-info">📍 <?= htmlspecialchars($row['address']) ?></div>
                <div class="details">
                    <span>Items: <?= $row['items'] ?></span>
                    <span>Distance: <?= $row['distance'] ?></span>
                    <span>ETA: <?= $row['eta'] ?></span>
                </div>
                <button class="view-btn" onclick="window.location.href='order_pickup.php?id=<?= $row['user_id'] ?>'">View Delivery</button>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- PENDING DELIVERIES -->
    <div class="deliveries" id="pending-deliveries">
        <h3>Pending Deliveries</h3>
        <?php foreach ($pending_orders as $row): ?>
            <div class="delivery-card">
                <div class="delivery-header">
                    <h4><?= htmlspecialchars($row['name']) ?></h4>
                    <span class="status ready">Pending</span>
                </div>
                <div class="delivery-info">📍 <?= htmlspecialchars($row['address']) ?></div>
                <div class="details">
                    <span>Items: <?= $row['items'] ?></span>
                    <span>Distance: <?= $row['distance'] ?></span>
                    <span>ETA: <?= $row['eta'] ?></span>
                </div>
                <button class="start-btn" onclick="startDelivery(<?= $row['user_id'] ?>)">Start Delivery</button>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        function startDelivery(userId) {
            window.location.href = "order_pickup.php?id=" + userId;
        }

        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        function scrollToPending() {
            const yOffset = -180; // adjust this value to move scroll position up/down
const element = document.getElementById('pending-deliveries');
const y = element.getBoundingClientRect().top + window.pageYOffset + yOffset;
window.scrollTo({ top: y, behavior: 'smooth' });

        }

        function updateDateTime() {
            const now = new Date();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const year = now.getFullYear();
            document.getElementById('datetime').innerText = `${month}/${day}/${year}`;
        }

        setInterval(updateDateTime, 60000);
        updateDateTime();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
