<?php
session_start();
require_once '../Database/db_connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Delivery Complete | RCH Water</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f5f6fa;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: flex-start;
    }

    .container {
        background: white;
        width: 380px;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        margin-top: 20px;
    }

    /* Header */
    .header {
        background: linear-gradient(135deg, #007BFF, #57B0FF);
        color: white;
        padding: 25px;
        border-bottom-left-radius: 25px;
        border-bottom-right-radius: 25px;
        text-align: left;
        position: relative;
    }
    .header h2 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
    }
    .header p {
        margin: 5px 0 0;
        font-size: 14px;
    }
    .status-badge {
        position: absolute;
        top: 20px;
        right: 20px;
        background: #4CAF50;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        color: white;
    }

    /* Steps */
    .steps {
        margin: 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        padding: 15px;
    }
    .step {
        display: flex;
        align-items: center;
        margin: 10px 0;
    }
    .circle {
        width: 25px;
        height: 25px;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        color: white;
        font-size: 14px;
        font-weight: 600;
        margin-right: 10px;
    }
    .green { background: #4CAF50; }
    .gray { background: #ccc; }

    /* Delivery Address */
    .address-box {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        margin: 15px;
        padding: 15px;
    }
    .address-box h4 {
        margin: 0 0 10px;
        font-size: 16px;
        font-weight: 600;
    }
    .address {
        font-size: 14px;
        color: #555;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .address-box button {
        display: block;
        width: 100%;
        margin: 12px 0;
        background: #007BFF;
        color: white;
        border: none;
        padding: 10px;
        border-radius: 12px;
        font-size: 14px;
        cursor: pointer;
    }
    .address-box button:hover {
        background: #005FCC;
    }
    .contact-btns {
        display: flex;
        justify-content: space-between;
    }
    .contact-btns button {
        width: 48%;
        background: #fff;
        color: #007BFF;
        border: 1px solid #007BFF;
        padding: 8px;
        border-radius: 12px;
        cursor: pointer;
    }
    .contact-btns button:hover {
        background: #007BFF;
        color: #fff;
    }

    /* Items */
    .items-box {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        margin: 15px;
        padding: 15px;
    }
    .item {
        background: #f8f9fb;
        border-radius: 10px;
        padding: 10px;
        margin-bottom: 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .item-name {
        font-size: 14px;
        font-weight: 500;
    }
    .item-status {
        background: #c8f7c5;
        color: #2e7d32;
        font-size: 12px;
        padding: 3px 8px;
        border-radius: 8px;
    }

    /* Delivery Complete Message */
    .complete-box {
        background: #e7f7e5;
        border: 2px solid #4CAF50;
        border-radius: 12px;
        margin: 20px;
        padding: 15px;
        text-align: center;
    }
    .complete-box h3 {
        color: #2e7d32;
        margin: 0 0 5px;
        font-size: 16px;
        font-weight: 600;
    }
    .complete-box p {
        font-size: 13px;
        color: #444;
        margin: 0;
    }

</style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Delivery #DEL001</h2>
        <p>Hanami Garduque</p>
        <span class="status-badge">Active</span>
    </div>

    <div class="steps">
        <div class="step"><div class="circle green">1</div> Ready for Pickup</div>
        <div class="step"><div class="circle green">2</div> On the Way</div>
        <div class="step"><div class="circle green">3</div> Completed</div>
    </div>

    <div class="address-box">
        <h4>Delivery Address</h4>
        <div class="address">📍 387 Washington St, Batangas</div>
        <button onclick="openNavigation()">Open Navigation</button>
        <div class="contact-btns">
            <button onclick="callCustomer()">📞 Call</button>
            <button onclick="messageCustomer()">💬 Message</button>
        </div>
    </div>

    <div class="items-box">
        <h4>Items (2)</h4>
        <div class="item">
            <span class="item-name">Slim Box type Gallon (20L)</span>
            <span class="item-status">Ready</span>
        </div>
        <div class="item">
            <span class="item-name">Slim Box type Gallon (20L)</span>
            <span class="item-status">Ready</span>
        </div>
    </div>

    <div class="complete-box">
        <h3>✅ Delivery Completed!</h3>
        <p>Customer has received their order.</p>
    </div>
</div>

<script>
function openNavigation() {
    window.location.href = "navigation.php";
}
function callCustomer() {
    alert("Calling customer...");
}
function messageCustomer() {
    alert("Opening messaging app...");
}
</script>

</body>
</html>
