<?php
session_start();
require_once '../Database/db_connection.php';

// Ensure rider is logged in
if (!isset($_SESSION['rider_id'])) {
    header("Location: rider_login.php");
    exit();
}

$rider_id = $_SESSION['rider_id'];

// Get delivery info from GET parameter
$delivery_id = isset($_GET['delivery_id']) ? intval($_GET['delivery_id']) : 0;

$db = new Database();
$conn = $db->getConnect();

// Fetch delivery info for this rider
$stmt = $conn->prepare("SELECT * FROM rider_deliveries WHERE delivery_id = :delivery_id AND rider_id = :rider_id");
$stmt->bindParam(':delivery_id', $delivery_id);
$stmt->bindParam(':rider_id', $rider_id);
$stmt->execute();
$delivery = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$delivery) {
    die("Delivery not found.");
}

// Example: Use delivery address coordinates from database if available
$destination_lat = $delivery['latitude'] ?? 13.7620;
$destination_lng = $delivery['longitude'] ?? 121.0650;
?>
