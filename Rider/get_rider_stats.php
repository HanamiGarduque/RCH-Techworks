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
$rider_id = $_SESSION['id'];

try {
    // Get delivery statistics
    $stats_query = "
        SELECT 
            COUNT(*) as total_deliveries,
            SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN order_status IN ('pickup_in_progress', 'on_delivery') THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN order_status = 'assigned' THEN 1 ELSE 0 END) as pending,
            AVG(CASE WHEN order_status = 'delivered' THEN total_amount ELSE NULL END) as avg_delivery_value
        FROM water_refills
        WHERE rider_id = :rider_id
    ";
    
    $stmt = $db->prepare($stats_query);
    $stmt->bindParam(':rider_id', $rider_id, PDO::PARAM_INT);
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Calculate completion rate
    $completion_rate = 0;
    if ($stats['total_deliveries'] > 0) {
        $completion_rate = round(($stats['completed'] / $stats['total_deliveries']) * 100, 1);
    }

    // Get weekly performance (last 7 days)
    $weekly_query = "
        SELECT 
            DAYNAME(delivery_datetime) as day_name,
            DAYOFWEEK(delivery_datetime) as day_num,
            COUNT(*) as deliveries
        FROM water_refills
        WHERE rider_id = :rider_id
            AND order_status = 'delivered'
            AND delivery_datetime >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DAYNAME(delivery_datetime), DAYOFWEEK(delivery_datetime)
        ORDER BY day_num
    ";
    
    $stmt = $db->prepare($weekly_query);
    $stmt->bindParam(':rider_id', $rider_id, PDO::PARAM_INT);
    $stmt->execute();
    $weekly_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format weekly data for chart (normalize to percentage)
    $days_map = ['Monday' => 0, 'Tuesday' => 1, 'Wednesday' => 2, 'Thursday' => 3, 
                 'Friday' => 4, 'Saturday' => 5, 'Sunday' => 6];
    $weekly = array_fill(0, 7, 0);
    $max_deliveries = 0;

    foreach ($weekly_data as $day) {
        $index = $days_map[$day['day_name']];
        $weekly[$index] = (int)$day['deliveries'];
        $max_deliveries = max($max_deliveries, $weekly[$index]);
    }

    // Convert to percentages (0-100 scale for visualization)
    if ($max_deliveries > 0) {
        $weekly = array_map(function($val) use ($max_deliveries) {
            return round(($val / $max_deliveries) * 100);
        }, $weekly);
    }

    // Calculate average rating (if you have a ratings table, adjust accordingly)
    // For now, using a placeholder - you can add a ratings/reviews table later
    $rating_query = "
        SELECT 
            AVG(rating) as avg_rating,
            COUNT(*) as rating_count
        FROM (
            SELECT 5.0 as rating 
            FROM water_refills 
            WHERE rider_id = :rider_id 
                AND order_status = 'Completed'
            LIMIT 100
        ) as temp_ratings
    ";
    
    $stmt = $db->prepare($rating_query);
    $stmt->bindParam(':rider_id', $rider_id, PDO::PARAM_INT);
    $stmt->execute();
    $rating_data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get monthly earnings
    $earnings_query = "
        SELECT 
            SUM(total_amount) as total_earnings,
            COUNT(*) as deliveries_this_month
        FROM water_refills
        WHERE rider_id = :rider_id
            AND order_status = 'delivered'
            AND MONTH(delivery_datetime) = MONTH(CURDATE())
            AND YEAR(delivery_datetime) = YEAR(CURDATE())
    ";
    
    $stmt = $db->prepare($earnings_query);
    $stmt->bindParam(':rider_id', $rider_id, PDO::PARAM_INT);
    $stmt->execute();
    $earnings = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get recent achievements milestones
    $achievements = [];
    
    if ($stats['completed'] >= 100) {
        $achievements[] = [
            'title' => '100 Deliveries',
            'description' => 'Completed milestone',
            'icon' => 'star-fill',
            'color' => 'yellow'
        ];
    }
    
    if ($stats['completed'] >= 50) {
        $achievements[] = [
            'title' => '50 Deliveries',
            'description' => 'Half century club',
            'icon' => 'trophy-fill',
            'color' => 'yellow'
        ];
    }

    if ($completion_rate >= 95) {
        $achievements[] = [
            'title' => 'Reliability Master',
            'description' => '95%+ completion rate',
            'icon' => 'hand-thumbs-up-fill',
            'color' => 'green'
        ];
    }

    if ($rating_data['avg_rating'] >= 4.8) {
        $achievements[] = [
            'title' => 'Customer Favorite',
            'description' => '5-star rating streak',
            'icon' => 'heart-fill',
            'color' => 'red'
        ];
    }

    // Check for speed (deliveries completed ahead of schedule)
    $speed_query = "
        SELECT COUNT(*) as fast_deliveries
        FROM water_refills
        WHERE rider_id = :rider_id
            AND order_status = 'Completed'
            AND delivery_datetime < pickup_datetime + INTERVAL 2 HOUR
        LIMIT 10
    ";
    
    $stmt = $db->prepare($speed_query);
    $stmt->bindParam(':rider_id', $rider_id, PDO::PARAM_INT);
    $stmt->execute();
    $speed_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($speed_data['fast_deliveries'] >= 5) {
        $achievements[] = [
            'title' => 'Speed Demon',
            'description' => 'Fast delivery streak',
            'icon' => 'lightning-fill',
            'color' => 'blue'
        ];
    }

    // Prepare response
    $response = [
        'success' => true,
        'completed' => (int)$stats['completed'],
        'active' => (int)$stats['active'],
        'pending' => (int)$stats['pending'],
        'total_deliveries' => (int)$stats['total_deliveries'],
        'rating' => number_format((float)$rating_data['avg_rating'], 1),
        'rating_count' => (int)$rating_data['rating_count'],
        'completion_rate' => $completion_rate,
        'weekly' => $weekly,
        'monthly_earnings' => number_format((float)$earnings['total_earnings'], 2),
        'deliveries_this_month' => (int)$earnings['deliveries_this_month'],
        'avg_delivery_value' => number_format((float)$stats['avg_delivery_value'], 2),
        'achievements' => array_slice($achievements, 0, 3) // Show top 3 achievements
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