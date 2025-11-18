<?php 
require_once '../Database/db_connection.php';

$db = new Database();
$conn = $db->getConnect();

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

$stmt = $conn->prepare("
    SELECT latitude, longitude 
    FROM rider_location_history 
    WHERE order_id = :order_id 
    ORDER BY timestamp DESC 
    LIMIT 1
");
$stmt->bindValue(':order_id', $order_id, PDO::PARAM_INT);
$stmt->execute();

$loc = $stmt->fetch(PDO::FETCH_ASSOC);
echo json_encode($loc);
?>
