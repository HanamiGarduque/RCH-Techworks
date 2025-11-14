<?php
session_start();
require_once '../Database/db_connection.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role']!=='rider'){
    header("Location: login.php");
    exit;
}

$rider_id = $_SESSION['user_id'];
$query = "SELECT * FROM rider_notifications WHERE rider_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i",$rider_id);
$stmt->execute();
$notifications = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notifications | RCH Water</title>
<style>
body{font-family:Poppins,sans-serif;margin:0;padding:0;background:#f5f6fa;display:flex;justify-content:center}
.container{width:380px;background:white;border-radius:20px;box-shadow:0 4px 12px rgba(0,0,0,0.15);margin-top:20px}
.header{background:linear-gradient(135deg,#007BFF,#57B0FF);color:white;padding:25px;position:relative}
.header h2{margin:0;font-size:20px;font-weight:600}
.notif{background:white;border-radius:12px;box-shadow:0 2px 5px rgba(0,0,0,0.1);margin:15px;padding:15px;display:flex;align-items:flex-start;gap:12px;position:relative}
.notif.read{background:#f1f1f1}
.notif .icon{font-size:20px}
.notif-content h4{margin:0;font-size:15px;font-weight:600}
.notif-content p{margin:5px 0 0;font-size:13px;color:#555}
.time{font-size:11px;color:#888;margin-top:5px}
.dot{position:absolute;right:15px;top:20px;width:8px;height:8px;background:#007BFF;border-radius:50%}
</style>
</head>
<body>
<div class="container">
    <div class="header"><h2>Notifications</h2></div>
    <?php while($notif=$notifications->fetch_assoc()): ?>
    <div class="notif <?= $notif['read_status'] ? 'read' : '' ?>">
        <div class="icon">🔔</div>
        <div class="notif-content">
            <h4><?= htmlspecialchars($notif['title']) ?></h4>
            <p><?= htmlspecialchars($notif['message']) ?></p>
            <div class="time"><?= htmlspecialchars($notif['created_at']) ?></div>
        </div>
        <?php if(!$notif['read_status']): ?><div class="dot"></div><?php endif; ?>
    </div>
    <?php endwhile; ?>
</div>
</body>
</html>
