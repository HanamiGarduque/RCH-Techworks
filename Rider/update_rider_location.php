<?php 
session_start();
require_once '../Database/db_connection.php';

$db = new Database();
$conn = $db->getConnect();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $data = json_decode(file_get_contents('php://input'), true);
    if(!$data) exit(json_encode(['status'=>'error','message'=>'No data']));

    $lat = floatval($data['lat']);
    $lng = floatval($data['lng']);
    $order_id = intval($data['order_id']);
    $user_id = isset($_SESSION['rider_id']) ? intval($_SESSION['rider_id']) : 1;

    $stmt = $conn->prepare("
        INSERT INTO rider_location_history (user_id, order_id, latitude, longitude, timestamp) 
        VALUES (:user_id, :order_id, :lat, :lng, NOW())
    ");
    $stmt->bindValue(':user_id', $user_id);
    $stmt->bindValue(':order_id', $order_id);
    $stmt->bindValue(':lat', $lat);
    $stmt->bindValue(':lng', $lng);
    $stmt->execute();

    echo json_encode(['status'=>'ok']);
}
?>
