<?php
session_start();
require_once '../Database/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'rider') {
    header("Location: login.php");
    exit;
}

$rider_id = $_SESSION['user_id'];

$query = "SELECT name, phone_number, email, created_at FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $rider_id);
$stmt->execute();
$rider = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rider Account | RCH Water</title>
<style>
body {margin:0;font-family:Poppins,sans-serif;background:#f8f8f8}
.header{background:linear-gradient(180deg,#1E90FF 0%,#2196F3 100%);color:white;padding:25px 20px;display:flex;justify-content:space-between;align-items:center;font-weight:600;font-size:20px}
.settings{cursor:pointer;font-size:22px}
.profile-card{background:white;margin:20px;padding:20px;border-radius:20px;box-shadow:0 3px 8px rgba(0,0,0,0.1);text-align:center}
.profile-icon{font-size:50px;margin-bottom:10px}
.profile-name{font-weight:600;font-size:18px}
.profile-role{font-size:14px;color:#666;margin-bottom:15px}
.stats{display:flex;justify-content:space-around;margin-top:10px}
.stat-box{background:#f5f5f5;padding:10px;border-radius:10px;font-size:13px;color:#444}
.account-info,.quick-stats{background:white;margin:20px;padding:20px;border-radius:15px;box-shadow:0 3px 8px rgba(0,0,0,0.1)}
.info-row,.quick-row{display:flex;justify-content:space-between;margin-bottom:10px;font-size:14px}
.label{color:#666}.value{font-weight:500}.amount{color:#1E90FF;font-weight:600}.success{color:green;font-weight:600}
.button{display:block;width:90%;margin:15px auto;padding:12px;border:none;border-radius:10px;font-size:15px;cursor:pointer;transition:.3s;font-weight:500;text-align:center}
.btn-edit{background:#d9d9d9}.btn-edit:hover{background:#1E90FF;color:white}
.btn-logout{background:#d9d9d9;color:#000}.btn-logout:hover{background:#FF5252;color:white}
</style>
</head>
<body>

<div class="header">
    <span>Account</span>
    <span class="settings" onclick="window.location.href='edit_profile.php'">⚙️</span>
</div>

<div class="profile-card">
    <div class="profile-icon">👤</div>
    <div class="profile-name"><?= htmlspecialchars($rider['name']) ?></div>
    <div class="profile-role">Delivery Rider</div>
    <div class="stats">
        <div class="stat-box">
            <div>⭐ 4.7</div>
            <small>Rating</small>
        </div>
        <div class="stat-box">
            <div><?= htmlspecialchars($rider['created_at']) ?></div>
            <small>Joined</small>
        </div>
        <div class="stat-box">
            <div>0</div>
            <small>Completed</small>
        </div>
    </div>
</div>

<div class="account-info">
    <h3>Account Information</h3>
    <div class="info-row"><span class="label">Phone Number</span><span class="value"><?= htmlspecialchars($rider['phone_number']) ?></span></div>
    <div class="info-row"><span class="label">Email</span><span class="value"><?= htmlspecialchars($rider['email']) ?></span></div>
    <div class="info-row"><span class="label">Member Since</span><span class="value"><?= htmlspecialchars($rider['created_at']) ?></span></div>
</div>

<div class="quick-stats">
    <h3>Quick Stats</h3>
    <div class="quick-row"><span>Earnings this Month</span><span class="amount">₱0</span></div>
    <div class="quick-row"><span>Total Deliveries</span><span class="success">0</span></div>
</div>

<button class="button btn-edit" onclick="window.location.href='edit_profile.php'">✏️ Edit Profile</button>
<button class="button btn-logout" onclick="window.location.href='login.php'">🔒 Logout</button>

</body>
</html>
