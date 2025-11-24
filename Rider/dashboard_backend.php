<?php
session_start();
require_once '../Database/db_connection.php';
date_default_timezone_set('Asia/Manila');

header('Content-Type: application/json');
$db = new Database();
    $conn = $db->getConnect();
// Get the logged-in rider ID from session
// $rider_id = $_SESSION['rider_id'] ?? null;
$rider_id = 7;

if (!$rider_id) {
    echo json_encode(['error' => 'Rider not logged in']);
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
            u.address
        FROM water_refills w
        JOIN users u ON w.user_id = u.user_id
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
                'address' => $row['address']
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
            u.address
        FROM water_refills w
        JOIN users u ON w.user_id = u.user_id
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
                'address' => $row['address']
            ]
        ];
    }

    // Fetch PENDING DELIVERY deliveries (status: 'picked_up')
    // These are water_refills that have been picked up and ready for delivery
    $deliveryQuery = "
        SELECT 
            w.refill_id,
            w.order_number,
            w.pickup_datetime,
            w.delivery_datetime,
            w.order_status,
            u.user_id,
            u.name,
            u.address
        FROM water_refills w
        JOIN users u ON w.user_id = u.user_id
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
                'address' => $row['address']
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
            u.address
        FROM water_refills w
        JOIN users u ON w.user_id = u.user_id
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
                'address' => $row['address']
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
            u.address
        FROM water_refills w
        JOIN users u ON w.user_id = u.user_id
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
                'address' => $row['address']
            ]
        ];
    }

    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn = null; // Close PDO connection
?>