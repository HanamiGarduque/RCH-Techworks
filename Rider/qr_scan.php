<?php
session_start();
require_once '../Database/db_connection.php';

if (!isset($_GET['id'])) {
    echo "Customer ID not provided.";
    exit;
}

$user_id = intval($_GET['id']']);

// Fetch customer info
$db = new Database();
$conn = $db->getConnect();

try {
    $stmt = $conn->prepare("SELECT name, address FROM users WHERE user_id = :id");
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$customer) {
        echo "Customer not found.";
        exit;
    }

    $customer_name = $customer['name'];
    $address = $customer['address'];
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Scan QR | RCH Water</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
body { font-family:'Poppins',sans-serif; background:#f4f6fa; margin:0; padding:0; color:#333; text-align:center;}
.header { background-color:#2196F3; color:white; padding:30px 20px; }
#reader { width: 100%; max-width: 400px; margin: 20px auto; }
button { padding:10px 20px; border:none; border-radius:10px; background:#4CAF50; color:white; font-size:16px; cursor:pointer; }
button:hover { background:#45a049; }
</style>
</head>
<body>

<div class="header">
    <h3>Scan QR for <?= htmlspecialchars($customer_name) ?></h3>
    <p>Delivery Address: <?= htmlspecialchars($address) ?></p>
</div>

<div id="reader"></div>
<p id="result"></p>
<button id="okBtn" style="display:none;" onclick="goDashboard()">OK</button>

<script src="https://unpkg.com/html5-qrcode/minified/html5-qrcode.min.js"></script>
<script>
const userId = <?= $user_id ?>;
function goDashboard() {
    window.location.href = "dashboard_backend.php";
}

// Initialize QR scanner
function onScanSuccess(decodedText, decodedResult) {
    // Stop scanning after successful scan
    html5QrcodeScanner.clear().then(_ => {
        document.getElementById("result").innerHTML = "✅ Delivery Successful!";
        document.getElementById("okBtn").style.display = "inline-block";
    }).catch(error => {
        console.error("Failed to clear QR scanner", error);
    });
}

var html5QrcodeScanner = new Html5QrcodeScanner(
    "reader", { fps: 10, qrbox: 250 });

html5QrcodeScanner.render(onScanSuccess);
</script>

</body>
</html>
