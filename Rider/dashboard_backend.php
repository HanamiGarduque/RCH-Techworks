<?php
session_start();
require_once '../Database/db_connection.php';

if (!isset($_SESSION['rider_id'])) {
    echo json_encode(["error" => "not_logged_in"]);
    exit();
}

$rider_id   = $_SESSION['rider_id'];
$rider_name = $_SESSION['rider_name'];

$db = new Database();
$conn = $db->getConnect();

/* ---- TODAY'S DELIVERY COUNT ---- */
$sqlToday = "SELECT COUNT(*) AS today_count 
             FROM rider_deliveries 
             WHERE rider_id = ? AND DATE(created_at) = CURDATE()";

$stmt = $conn->prepare($sqlToday);
$stmt->bind_param("i", $rider_id);
$stmt->execute();
$today_count = $stmt->get_result()->fetch_assoc()['today_count'];

/* ---- COMPLETED DELIVERY COUNT ---- */
$sqlCompleted = "SELECT COUNT(*) AS completed_count 
                 FROM rider_deliveries 
                 WHERE rider_id = ? AND status = 'completed'";

$stmt = $conn->prepare($sqlCompleted);
$stmt->bind_param("i", $rider_id);
$stmt->execute();
$completed_count = $stmt->get_result()->fetch_assoc()['completed_count'];

/* ---- ACTIVE DELIVERIES ---- */
$sqlActive = "SELECT * FROM rider_deliveries 
              WHERE rider_id = ? 
              AND status IN ('pending', 'ready', 'ontheway')
              ORDER BY delivery_id DESC";

$stmt = $conn->prepare($sqlActive);
$stmt->bind_param("i", $rider_id);
$stmt->execute();
$result = $stmt->get_result();

$deliveries = [];
while ($row = $result->fetch_assoc()) {
    $deliveries[] = $row;
}

echo json_encode([
    "rider_name"  => $rider_name,
    "today"       => $today_count,
    "completed"   => $completed_count,
    "deliveries"  => $deliveries
]);
exit();
?>
