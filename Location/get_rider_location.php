<?php
require_once '../Database/db_connection.php';
require_once '../session.php';

header('Content-Type: application/json');

// Get order number from request
$orderNumber = isset($_GET['order_number']) ? $_GET['order_number'] : null;

if (!$orderNumber) {
    echo json_encode([
        "status" => "error", 
        "message" => "Order number required"
    ]);
    exit;
}

$database = new Database();
$db = $database->getConnect();

try {
    // Get rider ID from order
    $orderQuery = "SELECT rider_id FROM water_refills WHERE order_number = :order_number LIMIT 1";
    $orderStmt = $db->prepare($orderQuery);
    $orderStmt->bindParam(':order_number', $orderNumber);
    $orderStmt->execute();
    $order = $orderStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order || !$order['rider_id']) {
        echo json_encode([
            "status" => "no_rider",
            "message" => "No rider assigned to this order"
        ]);
        exit;
    }
    
    $riderId = $order['rider_id'];
    
    // Check if rider has saved location in map_location table
    $locationQuery = "SELECT latitude, longitude, updated_at 
                      FROM map_location 
                      WHERE user_id = :rider_id 
                      ORDER BY updated_at DESC 
                      LIMIT 1";
    $locationStmt = $db->prepare($locationQuery);
    $locationStmt->bindParam(':rider_id', $riderId, PDO::PARAM_INT);
    $locationStmt->execute();
    $location = $locationStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($location) {
        // Check if location is recent (within last 30 minutes)
        $lastUpdate = strtotime($location['updated_at']);
        $thirtyMinutesAgo = time() - (30 * 60);
        
        if ($lastUpdate >= $thirtyMinutesAgo) {
            echo json_encode([
                "status" => "real_time",
                "latitude" => floatval($location['latitude']),
                "longitude" => floatval($location['longitude']),
                "last_update" => $location['updated_at'],
                "is_recent" => true
            ]);
            exit;
        } else {
            echo json_encode([
                "status" => "outdated",
                "latitude" => floatval($location['latitude']),
                "longitude" => floatval($location['longitude']),
                "last_update" => $location['updated_at'],
                "is_recent" => false
            ]);
            exit;
        }
    }
    
    // No saved location found - use fallback (station location or midpoint)
    echo json_encode([
        "status" => "no_location",
        "message" => "Rider location not available",
        "latitude" => 13.9411, // Fallback to station
        "longitude" => 121.1628
    ]);
    
} catch (PDOException $e) {
    error_log('Database Error in get_rider_location.php: ' . $e->getMessage());
    echo json_encode([
        "status" => "error",
        "message" => "Database error occurred"
    ]);
} catch (Exception $e) {
    error_log('Error in get_rider_location.php: ' . $e->getMessage());
    echo json_encode([
        "status" => "error",
        "message" => "An error occurred"
    ]);
}
?>