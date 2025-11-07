<?php
session_start();
require_once '../Database/db_connection.php';

// Example session ID (replace with actual session data)
$rider_id = $_SESSION['rider_id'] ?? 1;

// Fetch rider info
$query = "SELECT name, phone, email, rating, total_completed, joined_date, monthly_earnings 
          FROM riders WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $rider_id);
$stmt->execute();
$result = $stmt->get_result();
$rider = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Account</title>
  <style>
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background-color: #f8f8f8;
    }

    .header {
      background: linear-gradient(180deg, #1E90FF 0%, #2196F3 100%);
      color: white;
      padding: 25px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-weight: 600;
      font-size: 20px;
    }

    .settings {
      cursor: pointer;
      font-size: 22px;
    }

    .profile-card {
      background: white;
      margin: 20px;
      padding: 20px;
      border-radius: 20px;
      box-shadow: 0 3px 8px rgba(0,0,0,0.1);
      text-align: center;
    }

    .profile-icon {
      font-size: 50px;
      margin-bottom: 10px;
    }

    .profile-name {
      font-weight: 600;
      font-size: 18px;
    }

    .profile-role {
      font-size: 14px;
      color: #666;
      margin-bottom: 15px;
    }

    .stats {
      display: flex;
      justify-content: space-around;
      margin-top: 10px;
    }

    .stat-box {
      background: #f5f5f5;
      padding: 10px;
      border-radius: 10px;
      font-size: 13px;
      color: #444;
    }

    .account-info, .quick-stats {
      background: white;
      margin: 20px;
      padding: 20px;
      border-radius: 15px;
      box-shadow: 0 3px 8px rgba(0,0,0,0.1);
    }

    .info-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
      font-size: 14px;
    }

    .label {
      color: #666;
    }

    .value {
      font-weight: 500;
    }

    .button {
      display: block;
      width: 90%;
      margin: 15px auto;
      padding: 12px;
      border: none;
      border-radius: 10px;
      font-size: 15px;
      cursor: pointer;
      transition: 0.3s;
      font-weight: 500;
      text-align: center;
    }

    .btn-edit {
      background: #d9d9d9;
    }

    .btn-edit:hover {
      background: #1E90FF;
      color: white;
    }

    .btn-logout {
      background: #d9d9d9;
      color: #000;
    }

    .btn-logout:hover {
      background: #FF5252;
      color: white;
    }

    .quick-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
      font-size: 14px;
    }

    .amount {
      color: #1E90FF;
      font-weight: 600;
    }

    .success {
      color: green;
      font-weight: 600;
    }
  </style>
</head>

<body>

  <div class="header">
    <span>Account</span>
    <span class="settings" onclick="window.location.href='edit_profile.php'">⚙️</span>
  </div>

  <div class="profile-card">
    <div class="profile-icon">👤</div>
    <div class="profile-name"><?= htmlspecialchars($rider['name'] ?? 'Joshua Garcia'); ?></div>
    <div class="profile-role">Delivery Rider</div>

    <div class="stats">
      <div class="stat-box">
        <div>⭐ <?= htmlspecialchars($rider['rating'] ?? '4.7'); ?></div>
        <small>Rating</small>
      </div>
      <div class="stat-box">
        <div><?= htmlspecialchars($rider['total_completed'] ?? '117'); ?></div>
        <small>Completed</small>
      </div>
      <div class="stat-box">
        <div><?= htmlspecialchars($rider['joined_date'] ?? '2mo'); ?></div>
        <small>Joined</small>
      </div>
    </div>
  </div>

  <div class="account-info">
    <h3>Account Information</h3>
    <div class="info-row">
      <span class="label">Phone Number</span>
      <span class="value"><?= htmlspecialchars($rider['phone'] ?? '+63 999 999 9999'); ?></span>
    </div>
    <div class="info-row">
      <span class="label">Email</span>
      <span class="value"><?= htmlspecialchars($rider['email'] ?? 'josh@rch.app'); ?></span>
    </div>
    <div class="info-row">
      <span class="label">Member Since</span>
      <span class="value"><?= htmlspecialchars($rider['joined_date'] ?? 'Mar 2023'); ?></span>
    </div>
  </div>

  <div class="quick-stats">
    <h3>Quick Stats</h3>
    <div class="quick-row">
      <span>Earnings this Month</span>
      <span class="amount">₱<?= htmlspecialchars($rider['monthly_earnings'] ?? '8,450'); ?></span>
    </div>
    <div class="quick-row">
      <span>Total Deliveries</span>
      <span class="success"><?= htmlspecialchars($rider['total_completed'] ?? '117'); ?></span>
    </div>
  </div>

  <button class="button btn-edit" onclick="window.location.href='edit_profile.php'">✏️ Edit Profile</button>
  <button class="button btn-logout" onclick="window.location.href='login.php'">🔒 Logout</button>

</body>
</html>
