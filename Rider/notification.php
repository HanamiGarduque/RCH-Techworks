<?php
session_start();

// Hardcoded rider info
$rider_name = $_SESSION['rider_name'] ?? "Rider";

// Sample notifications (you can replace with database query later)
$notifications = [
    ["message" => "New order assigned: #DEL004", "time" => "2 mins ago", "type" => "info"],
    ["message" => "Order #DEL002 has been completed.", "time" => "10 mins ago", "type" => "success"],
    ["message" => "System maintenance scheduled at 10 PM.", "time" => "1 hour ago", "type" => "warning"]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notifications | RCH Water</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { font-family: 'Poppins', sans-serif; background-color: #f4f6fa; margin: 0; padding: 0; }
.header { background-color: #2196F3; color: white; padding: 30px 20px; border-radius: 0 0 30px 30px; text-align: center; }
.header h2 { margin: 0; font-size: 22px; font-weight: 600; }
.container { margin: 20px auto; max-width: 600px; }
.notification-card { background-color: white; border-radius: 15px; padding: 15px; margin-bottom: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
.notification-message { font-size: 14px; }
.notification-time { font-size: 12px; color: #999; }
.info { border-left: 4px solid #17a2b8; }
.success { border-left: 4px solid #28a745; }
.warning { border-left: 4px solid #ffc107; }
.back-btn { position: absolute; left: 20px; top: 30px; font-size: 20px; cursor: pointer; color: white; }
</style>
</head>
<body>

<div class="header position-relative">
    <div class="back-btn" onclick="history.back()">←</div>
    <h2>Notifications</h2>
</div>

<div class="container">
    <?php if (count($notifications) === 0): ?>
        <p class="text-center text-muted">No notifications available.</p>
    <?php else: ?>
        <?php foreach ($notifications as $note): ?>
            <div class="notification-card <?= $note['type'] ?>">
                <div class="notification-message"><?= htmlspecialchars($note['message']) ?></div>
                <div class="notification-time"><?= htmlspecialchars($note['time']) ?></div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
