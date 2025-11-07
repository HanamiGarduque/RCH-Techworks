<?php
session_start();
require_once '../Database/db_connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notifications | RCH Water</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f5f6fa;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
    }

    .container {
        width: 380px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        margin-top: 20px;
        overflow: hidden;
    }

    /* Header */
    .header {
        background: linear-gradient(135deg, #007BFF, #57B0FF);
        color: white;
        padding: 25px;
        position: relative;
    }

    .header h2 {
        margin: 0;
        font-size: 20px;
        font-weight: 600;
    }

    .bell {
        position: absolute;
        right: 25px;
        top: 25px;
        font-size: 20px;
        cursor: pointer;
    }

    /* Notification Cards */
    .notif {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        margin: 15px;
        padding: 15px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        position: relative;
    }

    .notif.read {
        background: #f1f1f1;
    }

    .notif .icon {
        font-size: 20px;
    }

    .notif-content h4 {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
    }

    .notif-content p {
        margin: 5px 0 0;
        font-size: 13px;
        color: #555;
    }

    .time {
        font-size: 11px;
        color: #888;
        margin-top: 5px;
    }

    .dot {
        position: absolute;
        right: 15px;
        top: 20px;
        width: 8px;
        height: 8px;
        background: #007BFF;
        border-radius: 50%;
    }

</style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Notifications</h2>
        <span class="bell">🔔</span>
    </div>

    <div class="notif unread">
        <div class="icon">🔔</div>
        <div class="notif-content">
            <h4>New Delivery Order</h4>
            <p>Christine May ordered 2 gallons...</p>
            <div class="time">2 mins ago</div>
        </div>
        <div class="dot"></div>
    </div>

    <div class="notif unread">
        <div class="icon">✅</div>
        <div class="notif-content">
            <h4>Delivery Completed</h4>
            <p>Your delivery to Hanami Garduque was successful.</p>
            <div class="time">9 mins ago</div>
        </div>
        <div class="dot"></div>
    </div>

    <div class="notif read">
        <div class="icon">💵</div>
        <div class="notif-content">
            <h4>Payment Verification</h4>
            <p>Please verify the cash payment from customer.</p>
            <div class="time">1 hour ago</div>
        </div>
    </div>

    <div class="notif read">
        <div class="icon">⚠️</div>
        <div class="notif-content">
            <h4>Route Updated</h4>
            <p>Your delivery route has been optimized.</p>
            <div class="time">2 hours ago</div>
        </div>
    </div>
</div>

</body>
</html>
