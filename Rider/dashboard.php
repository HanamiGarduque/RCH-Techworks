<?php
session_start();
$rider_name = isset($_SESSION['rider_username']) ? $_SESSION['rider_username'] : "Rider";
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
    .card {
        background-color: rgba(255,255,255,0.3);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        padding: 15px 25px;
        text-align: center;
        color: white;
    }
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
    .delivery-card {
        background-color: white;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
    .start-btn:hover {
        background-color: #0b7dda;
    }
</style>
</head>
<body>

<div class="header">
    <h2><?= htmlspecialchars($rider_name); ?></h2>
    <span class="notif" onclick="window.location.href='notifications.php'">🔔</span>

    <div class="stats">
        <div class="card">
            <h3>Today's Delivery</h3>
            <p id="todayCount">0</p>
        </div>
        <div class="card">
            <h3>Completed</h3>
            <p id="completedCount">0</p>
        </div>
    </div>
</div>

<div class="deliveries">
    <h3>Active Deliveries</h3>

    <!-- AUTO-GENERATED DELIVERIES -->
    <div id="deliveryList"></div>
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

</body>
</html>
