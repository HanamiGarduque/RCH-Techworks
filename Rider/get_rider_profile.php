<?php
require_once __DIR__ . '/../session.php';
require_once '../Database/db_connection.php';

header('Content-Type: application/json');

// Verify rider role
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'rider') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$database = new Database();
$db = $database->getConnect();
$user_id = $_SESSION['id'];

try {
    // Get rider profile information
    $profile_query = "
        SELECT 
            u.user_id,
            u.name,
            u.email,
            u.phone_number,
            u.address,
            u.status,
            u.created_at,
            ml.latitude,
            ml.longitude
        FROM users u
        LEFT JOIN map_location ml ON u.user_id = ml.user_id
        WHERE u.user_id = :user_id AND u.role = 'rider'
        LIMIT 1
    ";
    
    $stmt = $db->prepare($profile_query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$profile) {
        http_response_code(404);
        echo json_encode(['error' => 'Profile not found']);
        exit;
    }

    // Get vehicle information (you may need to add a vehicles table)
    // For now, using placeholder data
    $vehicle_info = [
        'license_plate' => 'ABC-1234',
        'vehicle_type' => 'Motorcycle',
        'vehicle_model' => 'Honda XRM 125',
        'vehicle_year' => '2022'
    ];

    // Format date
    $joined_date = new DateTime($profile['created_at']);
    $formatted_date = $joined_date->format('F d, Y');

    // Get initials
    $name_parts = explode(' ', $profile['name']);
    $initials = '';
    foreach ($name_parts as $part) {
        if (!empty($part)) {
            $initials .= strtoupper(substr($part, 0, 1));
        }
    }
    if (strlen($initials) > 2) {
        $initials = substr($initials, 0, 2);
    }

    // Get total distance traveled (from location history)
    $distance_query = "
        SELECT 
            COUNT(*) as location_updates,
            MIN(recorded_at) as first_update,
            MAX(recorded_at) as last_update
        FROM loc_history
        WHERE rider_id = :rider_id
    ";
    
    $stmt = $db->prepare($distance_query);
    $stmt->bindParam(':rider_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $distance_data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Prepare response
    $response = [
        'success' => true,
        'profile' => [
            'user_id' => (int)$profile['user_id'],
            'name' => $profile['name'],
            'email' => $profile['email'],
            'phone' => $profile['phone_number'] ?? 'Not provided',
            'address' => $profile['address'] ?? 'Not provided',
            'status' => $profile['status'],
            'joined' => $formatted_date,
            'initials' => $initials,
            'latitude' => $profile['latitude'],
            'longitude' => $profile['longitude']
        ],
        'vehicle' => $vehicle_info,
        'activity' => [
            'location_updates' => (int)$distance_data['location_updates'],
            'first_activity' => $distance_data['first_update'],
            'last_activity' => $distance_data['last_update']
        ]
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error occurred',
        'message' => $e->getMessage()
    ]);
}
?>