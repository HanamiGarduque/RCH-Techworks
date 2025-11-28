<?php
session_start();
require_once '../Database/db_connection.php';
date_default_timezone_set('Asia/Manila');

header('Content-Type: application/json');

// Add error logging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in JSON response
ini_set('log_errors', 1);

$db = new Database();
$conn = $db->getConnect();

// Get the logged-in rider ID from session
$rider_id = $_SESSION['id'] ?? null;

// Debug: Log the rider_id
error_log("Rider ID from session: " . ($rider_id ?? 'NULL'));

if (!$rider_id) {
    echo json_encode(['error' => 'Rider not logged in', 'session' => $_SESSION]);
    exit;
}

$response = [
    'active' => [],
    'pending' => [
        'pickup' => [
            'morning' => [],
            'afternoon' => []
        ],
        'delivery' => [
            'morning' => [],
            'afternoon' => []
        ]
    ],
    'history' => [
        'delayed' => [
            'morning' => [],
            'afternoon' => []
        ],
        'completed' => [
            'morning' => [],
            'afternoon' => []
        ]
    ]
];

try {
    // Fetch ACTIVE deliveries (status: 'pickup_in_progress', 'picked_up', 'on_delivery')
    $activeQuery = "
        SELECT 
            w.refill_id,
            w.order_number,
            w.pickup_datetime,
            w.delivery_datetime,
            w.order_status,
            u.user_id,
            u.name,
            u.address,
            COALESCE(m.latitude, '') as latitude,
            COALESCE(m.longitude, '') as longitude
        FROM water_refills w
        JOIN users u ON w.user_id = u.user_id
        LEFT JOIN map_location m ON u.user_id = m.user_id
        WHERE w.rider_id = :rider_id 
        AND w.order_status IN ('pickup_in_progress', 'picked_up', 'on_delivery')
        ORDER BY w.pickup_datetime ASC, w.delivery_datetime ASC
    ";
    
    $stmt = $conn->prepare($activeQuery);
    $stmt->bindParam(':rider_id', $rider_id, PDO::PARAM_INT);
    $stmt->execute();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $response['active'][] = [
            'refill_id' => $row['refill_id'],
            'order_number' => $row['order_number'],
            'pickup_datetime' => $row['pickup_datetime'],
            'delivery_datetime' => $row['delivery_datetime'],
            'status' => $row['order_status'],
            'user' => [
                'user_id' => $row['user_id'],
                'name' => $row['name'],
                'address' => $row['address'],
                'latitude' => $row['latitude'],
                'longitude' => $row['longitude']
            ]
        ];
    }

    // Fetch PENDING PICKUP deliveries (status: 'assigned')
    $pickupQuery = "
        SELECT 
            w.refill_id,
            w.order_number,
            w.pickup_datetime,
            w.delivery_datetime,
            w.order_status,
            u.user_id,
            u.name,
            u.address,
            COALESCE(m.latitude, '') as latitude,
            COALESCE(m.longitude, '') as longitude
        FROM water_refills w
        JOIN users u ON w.user_id = u.user_id
        LEFT JOIN map_location m ON u.user_id = m.user_id
        WHERE w.rider_id = :rider_id 
        AND w.order_status = 'assigned'
        AND w.pickup_datetime IS NOT NULL
        ORDER BY w.pickup_datetime ASC
    ";
    
    $stmt = $conn->prepare($pickupQuery);
    $stmt->bindParam(':rider_id', $rider_id, PDO::PARAM_INT);
    $stmt->execute();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $pickupTime = new DateTime($row['pickup_datetime']);
        $hour = (int)$pickupTime->format('H');
        
        // Determine if morning (before 12 PM) or afternoon (12 PM and after)
        $period = $hour < 12 ? 'morning' : 'afternoon';
        
        $response['pending']['pickup'][$period][] = [
            'refill_id' => $row['refill_id'],
            'order_number' => $row['order_number'],
            'pickup_datetime' => $row['pickup_datetime'],
            'delivery_datetime' => $row['delivery_datetime'],
            'status' => $row['order_status'],
            'user' => [
                'user_id' => $row['user_id'],
                'name' => $row['name'],
                'address' => $row['address'],
                'latitude' => $row['latitude'],
                'longitude' => $row['longitude']
            ]
        ];
    }

    // Fetch PENDING DELIVERY deliveries (status: 'picked_up')
    $deliveryQuery = "
        SELECT 
            w.refill_id,
            w.order_number,
            w.pickup_datetime,
            w.delivery_datetime,
            w.order_status,
            u.user_id,
            u.name,
            u.address,
            COALESCE(m.latitude, '') as latitude,
            COALESCE(m.longitude, '') as longitude
        FROM water_refills w
        JOIN users u ON w.user_id = u.user_id
        LEFT JOIN map_location m ON u.user_id = m.user_id
        WHERE w.rider_id = :rider_id 
        AND w.order_status = 'picked_up'
        ORDER BY w.delivery_datetime ASC
    ";
    
    $stmt = $conn->prepare($deliveryQuery);
    $stmt->bindParam(':rider_id', $rider_id, PDO::PARAM_INT);
    $stmt->execute();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $deliveryTime = new DateTime($row['delivery_datetime']);
        $hour = (int)$deliveryTime->format('H');
        
        // Determine if morning (before 12 PM) or afternoon (12 PM and after)
        $period = $hour < 12 ? 'morning' : 'afternoon';
        
        $response['pending']['delivery'][$period][] = [
            'refill_id' => $row['refill_id'],
            'order_number' => $row['order_number'],
            'pickup_datetime' => $row['pickup_datetime'],
            'delivery_datetime' => $row['delivery_datetime'],
            'status' => $row['order_status'],
            'user' => [
                'user_id' => $row['user_id'],
                'name' => $row['name'],
                'address' => $row['address'],
                'latitude' => $row['latitude'],
                'longitude' => $row['longitude']
            ]
        ];
    }

    // Fetch DELAYED deliveries (status: 'pickup_delayed' or 'delivery_delayed')
    $delayedQuery = "
        SELECT 
            w.refill_id,
            w.order_number,
            w.pickup_datetime,
            w.delivery_datetime,
            w.order_status,
            u.user_id,
            u.name,
            u.address,
            COALESCE(m.latitude, '') as latitude,
            COALESCE(m.longitude, '') as longitude
        FROM water_refills w
        JOIN users u ON w.user_id = u.user_id
        LEFT JOIN map_location m ON u.user_id = m.user_id
        WHERE w.rider_id = :rider_id 
        AND w.order_status IN ('pickup_delayed', 'delivery_delayed')
        ORDER BY w.delivery_datetime DESC
    ";
    
    $stmt = $conn->prepare($delayedQuery);
    $stmt->bindParam(':rider_id', $rider_id, PDO::PARAM_INT);
    $stmt->execute();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $deliveryTime = new DateTime($row['delivery_datetime']);
        $hour = (int)$deliveryTime->format('H');
        
        // Determine if morning (before 12 PM) or afternoon (12 PM and after)
        $period = $hour < 12 ? 'morning' : 'afternoon';
        
        $response['history']['delayed'][$period][] = [
            'refill_id' => $row['refill_id'],
            'order_number' => $row['order_number'],
            'pickup_datetime' => $row['pickup_datetime'],
            'delivery_datetime' => $row['delivery_datetime'],
            'status' => $row['order_status'],
            'user' => [
                'user_id' => $row['user_id'],
                'name' => $row['name'],
                'address' => $row['address'],
                'latitude' => $row['latitude'],
                'longitude' => $row['longitude']
            ]
        ];
    }

    // Fetch COMPLETED deliveries (status: 'delivered')
    $completedQuery = "
        SELECT 
            w.refill_id,
            w.order_number,
            w.pickup_datetime,
            w.delivery_datetime,
            w.order_status,
            u.user_id,
            u.name,
            u.address,
            COALESCE(m.latitude, '') as latitude,
            COALESCE(m.longitude, '') as longitude
        FROM water_refills w
        JOIN users u ON w.user_id = u.user_id
        LEFT JOIN map_location m ON u.user_id = m.user_id
        WHERE w.rider_id = :rider_id 
        AND w.order_status = 'delivered'
        ORDER BY w.delivery_datetime DESC
    ";
    
    $stmt = $conn->prepare($completedQuery);
    $stmt->bindParam(':rider_id', $rider_id, PDO::PARAM_INT);
    $stmt->execute();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $deliveryTime = new DateTime($row['delivery_datetime']);
        $hour = (int)$deliveryTime->format('H');
        
        // Determine if morning (before 12 PM) or afternoon (12 PM and after)
        $period = $hour < 12 ? 'morning' : 'afternoon';
        
        $response['history']['completed'][$period][] = [
            'refill_id' => $row['refill_id'],
            'order_number' => $row['order_number'],
            'pickup_datetime' => $row['pickup_datetime'],
            'delivery_datetime' => $row['delivery_datetime'],
            'status' => $row['order_status'],
            'user' => [
                'user_id' => $row['user_id'],
                'name' => $row['name'],
                'address' => $row['address'],
                'latitude' => $row['latitude'],
                'longitude' => $row['longitude']
            ]
        ];
    }

    // Log counts for debugging
    error_log("Active orders: " . count($response['active']));
    error_log("Pickup orders: " . (count($response['pending']['pickup']['morning']) + count($response['pending']['pickup']['afternoon'])));
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
}

$conn = null; // Close PDO connection
?>