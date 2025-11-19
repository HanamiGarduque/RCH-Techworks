<?php
session_start();

// get user_id or order_id (depends on your workflow)
$user_id = $_GET['id'] ?? null;
if (!$user_id) {
    echo "Customer ID not provided.";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>QR Scanner</title>

    <script src="https://unpkg.com/html5-qrcode"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 20px;
        }
        #reader {
            width: 300px;
            margin: auto;
        }

        .popup {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.6);
            display: none;
            justify-content: center;
            align-items: center;
        }
        .popup-box {
            background: white;
            padding: 25px;
            border-radius: 12px;
            width: 300px;
            text-align: center;
            font-size: 20px;
        }
        .ok-btn {
            margin-top: 20px;
            padding: 10px 20px;
            border: none;
            background: #007bff;
            color: white;
            font-size: 18px;
            border-radius: 8px;
            cursor: pointer;
        }
    </style>
</head>

<body>

<h2>Scan QR Code</h2>

<div id="reader"></div>

<!-- SUCCESS POPUP -->
<div class="popup" id="successPopup">
    <div class="popup-box">
        <strong>Delivery Successful!</strong><br><br>
        <button class="ok-btn" onclick="window.location.href='dashboard.php'">OK</button>
    </div>
</div>

<script>
// START QR SCANNER
const html5QrCode = new Html5Qrcode("reader");

html5QrCode.start(
    { facingMode: "environment" }, 
    { fps: 10, qrbox: 250 },
    qrCodeMessage => {

        console.log("QR Scanned:", qrCodeMessage);

        html5QrCode.stop();

        // you can validate QR content here if needed
        document.getElementById("successPopup").style.display = "flex";

        // Optional: send to backend for updating delivery
        fetch("delivery_complete_backend.php?id=<?= $user_id ?>")
    },
    errorMessage => {}
)
.catch(err => {
    console.error("Camera error:", err);
});
</script>

</body>
</html>
