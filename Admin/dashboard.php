<?php
require_once __DIR__ . '/../session.php';
require_once '../Database/db_connection.php';
redirectIfRoleMismatch('admin'); // only admins can access
// FIX: check_delays sends delay emails. Buffer it so any stray output never prints at top of dashboard.
ob_start();
include_once '../Cron/check_delays.php';
if (ob_get_level() > 0) { $cronOutput = ob_get_clean(); if (trim($cronOutput) !== '') error_log('check_delays output: ' . $cronOutput); }

$database = new Database();
$db = $database->getConnect();

// ---------- METRIC CARDS & DATA QUERIES ----------

// Ensure PDO fetch mode default if not already set
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Orders this month
$stmt = $db->prepare("
    SELECT COUNT(*) AS total 
    FROM water_refills 
    WHERE MONTH(created_at) = MONTH(CURRENT_DATE())
      AND YEAR(created_at) = YEAR(CURRENT_DATE())
");
$stmt->execute();
$ordersMonth = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total gallons handed out (sum of quantity across water_refill_items)
$stmt = $db->prepare("SELECT IFNULL(SUM(quantity),0) AS total FROM water_refill_items");
$stmt->execute();
$gallonsHandout = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Active customers (distinct users who placed a refill OR POS orders in last year)
$stmt = $db->prepare("
    SELECT COUNT(DISTINCT user_id) AS total
    FROM (
        SELECT user_id FROM water_refills
        UNION ALL
        SELECT user_id FROM orders WHERE user_id IS NOT NULL
    ) t
");
$stmt->execute();
$activeCustomers = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $db->prepare("
    SELECT 
        COUNT(*) AS total_refills,
        AVG(total_amount) AS avg_amount,
        SUM(CASE WHEN order_status = 'pickup_delayed' OR order_status = 'delivery_delayed' THEN 1 ELSE 0 END) AS delayed_refills
    FROM water_refills
");
$stmt->execute();
$refillStats = $stmt->fetch(PDO::FETCH_ASSOC);

// Average delivery time (hours) across completed deliveries
$stmt = $db->prepare("
    SELECT AVG(TIMESTAMPDIFF(MINUTE, pickup_datetime, delivery_datetime))/60.0 AS avg_hrs
    FROM water_refills
    WHERE pickup_datetime IS NOT NULL
      AND delivery_datetime IS NOT NULL
");
$stmt->execute();
$avgDeliveryTime = $stmt->fetch(PDO::FETCH_ASSOC)['avg_hrs'];
$avgDeliveryTime = $avgDeliveryTime !== null ? round((float)$avgDeliveryTime, 2) : 0;

// ORDERS OVER TIME (last 30 days)
$stmt = $db->prepare("
    SELECT DATE(created_at) AS day, COUNT(*) AS total
    FROM water_refills
    WHERE created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 29 DAY)
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at)
");
$stmt->execute();
$ordersDailyData = ['labels' => [], 'data' => []];
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $ordersDailyData['labels'][] = $r['day'];
    $ordersDailyData['data'][] = (int)$r['total'];
}
if (empty($ordersDailyData['labels'])) {
    // fallback to last 7 days if no data
    for ($i = 6; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $ordersDailyData['labels'][] = $d;
        $ordersDailyData['data'][] = 0;
    }
}

// ORDERS OVER TIME - WEEKLY (last 12 weeks, grouped by week starting Monday)
$stmt = $db->prepare("
    SELECT DATE(DATE_SUB(created_at, INTERVAL WEEKDAY(created_at) DAY)) AS week_start, COUNT(*) AS total
    FROM water_refills
    WHERE created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 12 WEEK)
    GROUP BY DATE(DATE_SUB(created_at, INTERVAL WEEKDAY(created_at) DAY))
    ORDER BY week_start
");
$stmt->execute();
$ordersWeeklyData = ['labels' => [], 'data' => []];
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $ordersWeeklyData['labels'][] = $r['week_start'];
    $ordersWeeklyData['data'][] = (int)$r['total'];
}
if (empty($ordersWeeklyData['labels'])) {
    for ($i = 11; $i >= 0; $i--) {
        $monday = date('Y-m-d', strtotime("monday this week -$i weeks"));
        $ordersWeeklyData['labels'][] = $monday;
        $ordersWeeklyData['data'][] = 0;
    }
}

// ORDERS OVER TIME - YEARLY (grouped by year)
$stmt = $db->prepare("
    SELECT YEAR(created_at) AS yr, COUNT(*) AS total
    FROM water_refills
    GROUP BY YEAR(created_at)
    ORDER BY yr
");
$stmt->execute();
$ordersYearlyData = ['labels' => [], 'data' => []];
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $ordersYearlyData['labels'][] = (string)$r['yr'];
    $ordersYearlyData['data'][] = (int)$r['total'];
}
if (empty($ordersYearlyData['labels'])) {
    $ordersYearlyData['labels'][] = date('Y');
    $ordersYearlyData['data'][] = 0;
}

// ORDER TYPE BREAKDOWN: delivery (water_refills) vs pos/walk-in (order_items)
$stmt = $db->prepare("SELECT COUNT(*) FROM water_refills");
$stmt->execute();
$deliveryCount = (int)$stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE transaction_type IS NOT NULL");
$stmt->execute();
$posCount = (int)$stmt->fetchColumn();

$orderTypeData = ['delivery' => $deliveryCount, 'pos' => $posCount];

// DELIVERY COMPLETION RATE
$stmt = $db->prepare("SELECT COUNT(*) FROM water_refills WHERE order_status = 'delivered'");
$stmt->execute();
$completed = (int)$stmt->fetchColumn();
$stmt = $db->prepare("SELECT COUNT(*) FROM water_refills");
$stmt->execute();
$totalOrders = (int)$stmt->fetchColumn();
$completionRate = $totalOrders > 0 ? round(($completed / $totalOrders) * 100, 2) : 0;

// AVERAGE DELIVERY HOURS PER DAY (last 7 days)
$stmt = $db->prepare("
    SELECT DATE(delivery_datetime) AS day, AVG(TIMESTAMPDIFF(MINUTE, pickup_datetime, delivery_datetime))/60.0 AS avg_hours
    FROM water_refills
    WHERE pickup_datetime IS NOT NULL
      AND delivery_datetime IS NOT NULL
      AND delivery_datetime >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 DAY)
    GROUP BY DATE(delivery_datetime)
    ORDER BY DATE(delivery_datetime)
");
$stmt->execute();
$deliveryHours = ['labels' => [], 'data' => []];
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $deliveryHours['labels'][] = $r['day'];
    $deliveryHours['data'][] = round((float)$r['avg_hours'], 2);
}
if (empty($deliveryHours['labels'])) {
    // fallback last 7 days
    for ($i = 6; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $deliveryHours['labels'][] = $d;
        $deliveryHours['data'][] = 0;
    }
}

// AVERAGE DELIVERY HOURS - WEEKLY (last 12 weeks)
$stmt = $db->prepare("
    SELECT DATE(DATE_SUB(delivery_datetime, INTERVAL WEEKDAY(delivery_datetime) DAY)) AS week_start, AVG(TIMESTAMPDIFF(MINUTE, pickup_datetime, delivery_datetime))/60.0 AS avg_hours
    FROM water_refills
    WHERE pickup_datetime IS NOT NULL
      AND delivery_datetime IS NOT NULL
      AND delivery_datetime >= DATE_SUB(CURRENT_DATE(), INTERVAL 12 WEEK)
    GROUP BY DATE(DATE_SUB(delivery_datetime, INTERVAL WEEKDAY(delivery_datetime) DAY))
    ORDER BY week_start
");
$stmt->execute();
$deliveryHoursWeekly = ['labels' => [], 'data' => []];
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $deliveryHoursWeekly['labels'][] = $r['week_start'];
    $deliveryHoursWeekly['data'][] = round((float)$r['avg_hours'], 2);
}
if (empty($deliveryHoursWeekly['labels'])) {
    for ($i = 11; $i >= 0; $i--) {
        $monday = date('Y-m-d', strtotime("monday this week -$i weeks"));
        $deliveryHoursWeekly['labels'][] = $monday;
        $deliveryHoursWeekly['data'][] = 0;
    }
}

// AVERAGE DELIVERY HOURS - MONTHLY (last 12 months)
$stmt = $db->prepare("
    SELECT DATE_FORMAT(delivery_datetime, '%Y-%m') AS month_label, AVG(TIMESTAMPDIFF(MINUTE, pickup_datetime, delivery_datetime))/60.0 AS avg_hours
    FROM water_refills
    WHERE pickup_datetime IS NOT NULL
      AND delivery_datetime IS NOT NULL
      AND delivery_datetime >= DATE_SUB(CURRENT_DATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(delivery_datetime, '%Y-%m')
    ORDER BY month_label
");
$stmt->execute();
$deliveryHoursMonthly = ['labels' => [], 'data' => []];
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $deliveryHoursMonthly['labels'][] = $r['month_label'];
    $deliveryHoursMonthly['data'][] = round((float)$r['avg_hours'], 2);
}
if (empty($deliveryHoursMonthly['labels'])) {
    for ($i = 11; $i >= 0; $i--) {
        $m = date('Y-m', strtotime("-$i months"));
        $deliveryHoursMonthly['labels'][] = $m;
        $deliveryHoursMonthly['data'][] = 0;
    }
}

// FREQUENT CUSTOMERS (top 5 by number of refills)
$stmt = $db->prepare("
    SELECT u.name, COUNT(r.refill_id) AS total
    FROM users u
    JOIN water_refills r ON u.user_id = r.user_id
    GROUP BY u.user_id
    ORDER BY total DESC
    LIMIT 5
");
$stmt->execute();
$frequentCustomers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// GALLON STATUS (using gallon_ownership.status)
$stmt = $db->prepare("
    SELECT status, COUNT(*) AS c
    FROM gallon_ownership
    GROUP BY status
");
$stmt->execute();

// Map PHP data to the exact labels you want
$gallonStatus = ['Available' => 0, 'In-Use' => 0, 'Lost/Damaged' => 0];
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $s = $r['status']; // case-sensitive if stored consistently
    if ($s === 'Available') $gallonStatus['Available'] = (int)$r['c'];
    elseif ($s === 'In-Use') $gallonStatus['In-Use'] = (int)$r['c'];
    else $gallonStatus['Lost/Damaged'] += (int)$r['c'];
}

// Pass PHP array to JS
$gallonStatusJSON = json_encode($gallonStatus);
$stmt = $db->prepare("
    SELECT gallon_type, COUNT(*) AS count
    FROM gallon_ownership
    GROUP BY gallon_type
");
$stmt->execute();

$gallonTypes = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $gallonTypes[$row['gallon_type']] = (int)$row['count'];
}

$gallonTypesJSON = json_encode($gallonTypes);

// STATION VS CUSTOMER (current_owner_id IS NULL => station)
$stmt = $db->prepare("
    SELECT 
      SUM(CASE WHEN gallon_owner = 'station' THEN 1 ELSE 0 END) AS station_owned,
      SUM(CASE WHEN gallon_owner = 'customer' THEN 1 ELSE 0 END) AS customer_owned
    FROM water_refill_items
");

$stmt->execute();
$sc = $stmt->fetch(PDO::FETCH_ASSOC);
$stationCustomer = [
    'station' => (int)$sc['station_owned'],
    'customer' => (int)$sc['customer_owned']
];

$stmt->execute();
$sc = $stmt->fetch(PDO::FETCH_ASSOC);
$stationCustomer = [
    'station' => (int)$sc['station_owned'],
    'customer' => (int)$sc['customer_owned']
];

// RECENT ORDERS (limit 10) combined from water_refills (refill) and order_items (pos)
$stmt = $db->prepare("
    SELECT r.refill_id AS id, r.order_number, u.name AS customer, 'Refill' AS type, r.total_amount AS amount, r.order_status AS status, r.created_at AS time
    FROM water_refills r
    LEFT JOIN users u ON u.user_id = r.user_id
    UNION ALL
    SELECT o.order_id AS id, o.order_number, COALESCE(u.name, o.customer_name) AS customer, 'POS' AS type, o.total_amount AS amount, o.order_status AS status, o.created_at AS time
    FROM orders o
    LEFT JOIN users u ON u.user_id = o.user_id
    ORDER BY time DESC
    LIMIT 10
");
$stmt->execute();
$recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Safety: ensure JSON numeric types where appropriate
function json_num($v)
{
    return json_encode($v, JSON_NUMERIC_CHECK);
}

$stmt = $db->prepare("
    SELECT order_status, COUNT(*) AS c
    FROM water_refills
    GROUP BY order_status
");
$stmt->execute();
$orderStatusCounts = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $orderStatusCounts[$row['order_status']] = (int)$row['c'];
}
$orderStatusJSON = json_encode($orderStatusCounts);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RCH Water - Performance Metrics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 overflow-hidden">
    <div class="flex h-screen">

        <!-- Sidebar -->
        <script src="../Admin/includes/sidebar.js"></script>

        <!-- Main Content -->
        <div class="flex-1 ml-20 group-hover:ml-64 transition-all duration-300 ease-in-out">
            <div class="flex flex-col h-screen"> <!-- Header -->
                <div class="flex-shrink-0">
                    <?php
                    $pageTitle = "Performance Metrics";
                    $pageSubtitle = "Detailed insights into orders, deliveries, and customer behavior.";
                    include '../Admin/includes/header.php';
                    ?>
                </div>

                <!-- Content Area -->
                <div class="flex-1 overflow-y-auto overflow-x-hidden p-8 space-y-8"> <!-- Metric Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Added icons to metric cards -->
                        <div class="bg-white rounded-lg p-6 shadow-sm flex items-center gap-4 hover:shadow-md transition">
                            <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-shopping-cart text-2xl text-white"></i>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Orders This Month</p>
                                <p class="text-2xl font-bold text-gray-700"><?= $ordersMonth ?></p>
                            </div>
                        </div>

                        <div class="bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg p-6 shadow-sm flex items-center gap-4 text-white hover:shadow-md transition">
                            <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-tint text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-white text-sm">Total Handouts-Gallons</p>
                                <p class="text-2xl font-bold"><?= $gallonsHandout ?>
                                </p>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg p-6 shadow-sm flex items-center gap-4 hover:shadow-md transition">
                            <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-users text-2xl text-white"></i>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Active Customers</p>
                                <p class="text-2xl font-bold text-gray-700"><?= $activeCustomers ?>
                                </p>
                            </div>
                        </div>

                        <div class="bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg p-6 shadow-sm flex items-center gap-4 text-white hover:shadow-md transition">
                            <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-clock text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-white     text-sm">Average Delivery Time</p>
                                <p class="text-2xl font-bold"><?= $avgDeliveryTime ?> hrs
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Order and Delivery Analytics Section -->
                    <div class="bg-blue-50 rounded-lg p-6 border border-blue-200 shadow-sm">
                        <h2 class="text-lg font-bold text-blue-600 mb-6 flex items-center gap-2">
                            <i class="fas fa-chart-column text-blue-600 text-lg"></i>
                            Order & Delivery Analytics
                        </h2>

                        <!-- Charts Grid -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                            <!-- Orders Over Time -->
                            <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="font-semibold text-gray-700 flex items-center gap-2">
                                        <i class="fas fa-arrow-trend-up text-blue-500"></i>
                                        Orders Over Time
                                    </h3>
                                </div>
                                <div class="flex gap-2 mb-4">
                                    <button type="button" data-orders-range="daily" class="orders-range-btn px-4 py-2 bg-blue-600 text-white rounded-full text-sm font-medium hover:bg-blue-700">Daily</button>
                                    <button type="button" data-orders-range="weekly" class="orders-range-btn px-4 py-2 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-gray-300">Weekly</button>
                                    <button type="button" data-orders-range="yearly" class="orders-range-btn px-4 py-2 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-gray-300">Yearly</button>
                                </div>
                                <div class="h-64">
                                    <canvas id="ordersChart"></canvas>
                                </div>
                            </div>

                            <!-- Order Type Breakdown -->
                            <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                                <h3 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                                    <i class="fas fa-chart-pie text-blue-500"></i>
                                    Order Type Breakdown
                                </h3>
                                <div class="h-64 flex items-center justify-center">
                                    <canvas id="orderTypeChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Delivery Completion Rate -->
                            <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                                <h3 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                                    <i class="fas fa-check-circle text-green-500"></i>
                                    Delivery Completion Rate
                                </h3>
                                <div class="h-64 flex items-center justify-center">
                                    <canvas id="completionChart"></canvas>
                                </div>
                            </div>

                            <!-- Average Delivery Hours -->
                            <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="font-semibold text-gray-700 flex items-center gap-2">
                                        <i class="fas fa-hourglass-end text-orange-500"></i>
                                        Average Delivery Hours
                                    </h3>
                                </div>
                                <div class="flex gap-2 mb-4">
                                    <button type="button" data-delivery-range="daily" class="delivery-range-btn px-4 py-2 bg-blue-600 text-white rounded-full text-sm font-medium hover:bg-blue-700">Daily</button>
                                    <button type="button" data-delivery-range="weekly" class="delivery-range-btn px-4 py-2 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-gray-300">Weekly</button>
                                    <button type="button" data-delivery-range="monthly" class="delivery-range-btn px-4 py-2 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-gray-300">Monthly</button>
                                </div>
                                <div class="h-64">
                                    <canvas id="deliveryHoursChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Behaviour Insights Section -->
                    <div class="bg-blue-50 rounded-lg p-6 border border-blue-200 shadow-sm">
                        <h2 class="text-lg font-bold text-blue-600 mb-6 flex items-center gap-2">
                            <i class="fas fa-user-group text-blue-600 text-lg"></i>
                            Customer Behaviour Insights
                        </h2>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Most Frequent Customers Chart -->
                            <!-- Replaced placeholder with actual chart -->
                            <div class="lg:col-span-2 bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                                <h3 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                                    <i class="fas fa-star text-yellow-500"></i>
                                    Most Frequent Customers
                                </h3>
                                <div class="h-64">
                                    <canvas id="frequentCustomersChart"></canvas>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="space-y-4">
                                <!-- Average Orders Per Customer -->
                                <div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-lg p-6 text-white shadow-md">
                                    <h3 class="font-semibold mb-4 text-sm flex items-center gap-2">
                                        <i class="fas fa-chart-line text-lg"></i>
                                        Average Orders/Customer
                                    </h3>
                                    <div class="flex items-center justify-center gap-4">
                                        <div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center flex-shrink-0">
                                            <span class="text-3xl font-bold">8</span>
                                        </div>
                                        <p class="text-blue-100 text-sm">Orders This Month</p>
                                    </div>
                                </div>

                                <!-- Peak Ordering Times -->
                                <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                                    <h3 class="font-semibold text-gray-700 mb-4 text-sm flex items-center gap-2">
                                        <i class="fas fa-clock text-blue-500"></i>
                                        Peak Ordering Times
                                    </h3>
                                    <ol class="space-y-2 text-sm text-gray-700">
                                        <li class="flex items-center justify-between p-2 bg-blue-50 rounded">
                                            <span><i class="fas fa-sun text-yellow-500 mr-2"></i>Morning (8-11AM)</span>
                                            <span class="font-bold text-blue-600 text-xs bg-blue-100 px-2 py-1 rounded">45%</span>
                                        </li>
                                        <li class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                            <span><i class="fas fa-cloud-sun text-orange-400 mr-2"></i>Afternoon (12-4PM)</span>
                                            <span class="font-bold text-blue-600 text-xs bg-blue-100 px-2 py-1 rounded">35%</span>
                                        </li>
                                        <li class="flex items-center justify-between p-2 bg-blue-50 rounded">
                                            <span><i class="fas fa-moon text-indigo-600 mr-2"></i>Evening (5-8PM)</span>
                                            <span class="font-bold text-blue-600 text-xs bg-blue-100 px-2 py-1 rounded">20%</span>
                                        </li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gallon Tracking Insights Section -->
                    <div class="bg-blue-50 rounded-lg p-6 border border-blue-200 shadow-sm mb-8">
                        <h2 class="text-lg font-bold text-blue-600 mb-6 flex items-center gap-2">
                            <i class="fas fa-flask-vial text-blue-600 text-lg"></i>
                            Gallon Tracking Insights
                        </h2>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                            <!-- Gallon Status Overview -->
                            <!-- Replaced placeholder with bar chart -->
                            <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                                <h3 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                                    <i class="fas fa-chart-bar text-blue-500"></i>
                                    Gallon Status Overview
                                </h3>
                                <div class="h-64">
                                    <canvas id="gallonStatusChart"></canvas>
                                </div>
                            </div>

                            <!-- Gallons Owned By Station vs by Customers -->
                            <!-- Replaced placeholder with comparison chart -->
                            <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                                <h3 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                                    <i class="fas fa-code-compare text-purple-500"></i>
                                    Station vs Customer Gallons
                                </h3>
                                <div class="h-64">
                                    <canvas id="stationCustomerChart"></canvas>
                                </div>
                            </div>

                            <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                                <h3 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                                    <i class="fas fa-code-compare text-purple-500"></i>
                                    Refill Orders By Status
                                </h3>
                                <div class="h-64">
                                    <canvas id="refillStatusChart"></canvas>
                                </div>
                            </div>

                            <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                                <h3 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                                    <i class="fas fa-code-compare text-purple-500"></i>
                                    Gallons By Type
                                </h3>
                                <div class="h-64">
                                    <canvas id="gallonTypeChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- QR Code Stats -->

                    </div>

                    <!-- Recent Orders Section -->
                    <!-- Added recent orders table with icons -->
                    <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                        <h2 class="text-lg font-bold text-blue-600 mb-6 flex items-center gap-2">
                            <i class="fas fa-history text-blue-600"></i>
                            Recent Orders
                        </h2>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200 bg-gray-50">
                                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Order ID</th>
                                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Customer</th>
                                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Type</th>
                                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Amount</th>
                                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Status</th>
                                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <tbody>
                                    <?php foreach ($recentOrders as $order):
                                        // friendly status badge mapping
                                        $status = strtolower($order['status']);
                                        $badgeClass = 'bg-gray-100 text-gray-700';
                                        if (strpos($status, 'deliv') !== false || strpos($status, 'delivered') !== false) $badgeClass = 'bg-green-100 text-green-700';
                                        elseif (strpos($status, 'pending') !== false) $badgeClass = 'bg-yellow-100 text-yellow-700';
                                        elseif (strpos($status, 'cancel') !== false) $badgeClass = 'bg-red-100 text-red-700';
                                        elseif (strpos($status, 'assigned') !== false || strpos($status, 'on_delivery') !== false) $badgeClass = 'bg-blue-100 text-blue-700';
                                    ?>
                                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                                            <td class="py-3 px-4"><span class="font-medium text-blue-600"><?= htmlspecialchars($order['order_number']) ?></span></td>
                                            <td class="py-3 px-4"><?= htmlspecialchars($order['customer']) ?></td>
                                            <td class="py-3 px-4"><span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-medium"><?= htmlspecialchars($order['type']) ?></span></td>
                                            <td class="py-3 px-4">₱<?= number_format((float)$order['amount'], 2) ?></td>
                                            <td class="py-3 px-4"><span class="px-3 py-1 <?= $badgeClass ?> rounded-full text-xs font-medium"><?= htmlspecialchars($order['status']) ?></span></td>
                                            <td class="py-3 px-4 text-gray-500"><?= htmlspecialchars($order['time']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <script src="..Admin/includes/admin_toast.js"></script>
    <script>
        /* Server-side data -> client */
        const ordersDailyData = <?= json_encode($ordersDailyData, JSON_NUMERIC_CHECK) ?>;
        const ordersWeeklyData = <?= json_encode($ordersWeeklyData, JSON_NUMERIC_CHECK) ?>;
        const ordersYearlyData = <?= json_encode($ordersYearlyData, JSON_NUMERIC_CHECK) ?>;
        const orderTypeData = <?= json_encode($orderTypeData, JSON_NUMERIC_CHECK) ?>;
        const completionRate = <?= json_encode($completionRate, JSON_NUMERIC_CHECK) ?>;
        const frequentCustomers = <?= json_encode($frequentCustomers, JSON_NUMERIC_CHECK) ?>;
        const gallonStatus = <?= json_encode($gallonStatus, JSON_NUMERIC_CHECK) ?>;
        const stationCustomer = <?= json_encode($stationCustomer, JSON_NUMERIC_CHECK) ?>;
        const deliveryHours = <?= json_encode($deliveryHours, JSON_NUMERIC_CHECK) ?>;
        const deliveryHoursWeekly = <?= json_encode($deliveryHoursWeekly, JSON_NUMERIC_CHECK) ?>;
        const deliveryHoursMonthly = <?= json_encode($deliveryHoursMonthly, JSON_NUMERIC_CHECK) ?>;
    </script>

    <script>
        function initializeCharts() {
            // Helper: ensure element exists
            const exists = id => document.getElementById(id) !== null;

            // Helper: paint active button blue, others gray
            function setActiveButton(buttons, activeBtn) {
                buttons.forEach(function(btn) {
                    if (btn === activeBtn) {
                        btn.classList.remove('bg-gray-200', 'text-gray-700');
                        btn.classList.add('bg-blue-600', 'text-white');
                    } else {
                        btn.classList.remove('bg-blue-600', 'text-white');
                        btn.classList.add('bg-gray-200', 'text-gray-700');
                    }
                });
            }

            // ORDERS OVER TIME (line) - kept in a variable so Daily/Weekly/Yearly can swap its data
            let ordersChartInstance = null;
            if (exists('ordersChart')) {
                ordersChartInstance = new Chart(document.getElementById('ordersChart'), {
                    type: 'line',
                    data: {
                        labels: ordersDailyData.labels,
                        datasets: [{
                            label: 'Orders',
                            data: ordersDailyData.data,
                            borderColor: '#1E90FF',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true,
                            backgroundColor: 'rgba(30,144,255,0.08)',
                            pointRadius: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                ticks: {
                                    autoSkip: true,
                                    maxTicksLimit: 10
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }

            // Orders Over Time toggle: Daily / Weekly / Yearly
            const ordersButtons = Array.from(document.querySelectorAll('.orders-range-btn'));
            ordersButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    if (!ordersChartInstance) return;
                    const range = btn.getAttribute('data-orders-range');
                    let picked = ordersDailyData;
                    if (range === 'weekly') picked = ordersWeeklyData;
                    if (range === 'yearly') picked = ordersYearlyData;
                    ordersChartInstance.data.labels = picked.labels;
                    ordersChartInstance.data.datasets[0].data = picked.data;
                    ordersChartInstance.update();
                    setActiveButton(ordersButtons, btn);
                });
            });

            const gallonTypes = <?= $gallonTypesJSON ?>;
            new Chart(document.getElementById('gallonTypeChart'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(gallonTypes),
                    datasets: [{
                        data: Object.values(gallonTypes),
                        backgroundColor: ['#1E90FF', '#046CD2', '#10B981', '#F59E0B'],
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // ORDER TYPE (pie)
            if (exists('orderTypeChart')) {
                new Chart(document.getElementById('orderTypeChart'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Delivery', 'POS / Walk-in'],
                        datasets: [{
                            data: [orderTypeData.delivery || 0, orderTypeData.pos || 0],
                            backgroundColor: ['#1E90FF', '#046CD2'],
                            borderColor: '#ffffff',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }

            // COMPLETION RATE (doughnut showing percent completed)
            if (exists('completionChart')) {
                new Chart(document.getElementById('completionChart'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Completed %', 'Remaining %'],
                        datasets: [{
                            data: [completionRate, Math.max(0, 100 - completionRate)],
                            backgroundColor: ['#10B981', '#F59E0B'],
                            borderColor: '#fff',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => `${ctx.label}: ${ctx.parsed}%`
                                }
                            }
                        }
                    }
                });
            }

            // AVERAGE DELIVERY HOURS (line) - kept in a variable so Daily/Weekly/Monthly can swap its data
            let deliveryHoursChartInstance = null;
            if (exists('deliveryHoursChart')) {
                deliveryHoursChartInstance = new Chart(document.getElementById('deliveryHoursChart'), {
                    type: 'line',
                    data: {
                        labels: deliveryHours.labels,
                        datasets: [{
                            label: 'Avg Hours',
                            data: deliveryHours.data,
                            borderColor: '#FF8C00',
                            backgroundColor: 'rgba(255,140,0,0.08)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true,
                            pointRadius: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // Average Delivery Hours toggle: Daily / Weekly / Monthly
            const deliveryButtons = Array.from(document.querySelectorAll('.delivery-range-btn'));
            deliveryButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    if (!deliveryHoursChartInstance) return;
                    const range = btn.getAttribute('data-delivery-range');
                    let picked = deliveryHours;
                    if (range === 'weekly') picked = deliveryHoursWeekly;
                    if (range === 'monthly') picked = deliveryHoursMonthly;
                    deliveryHoursChartInstance.data.labels = picked.labels;
                    deliveryHoursChartInstance.data.datasets[0].data = picked.data;
                    deliveryHoursChartInstance.update();
                    setActiveButton(deliveryButtons, btn);
                });
            });

            // MOST FREQUENT CUSTOMERS (horizontal bar)
            if (exists('frequentCustomersChart')) {
                const labels = frequentCustomers.map(c => c.name);
                const data = frequentCustomers.map(c => Number(c.total));
                new Chart(document.getElementById('frequentCustomersChart'), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Orders',
                            data: data,
                            backgroundColor: Array(labels.length).fill('#1E90FF'),
                            borderRadius: 6
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // GALLON STATUS (bar)
            const gallonStatus = <?= $gallonStatusJSON ?>;

            const ctx = document.getElementById('gallonStatusChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Available', 'In-Use', 'Lost/Damaged'],
                        datasets: [{
                            label: 'Gallon Count',
                            data: [
                                gallonStatus['Available'] || 0,
                                gallonStatus['In-Use'] || 0,
                                gallonStatus['Lost/Damaged'] || 0
                            ],
                            backgroundColor: ['#10B981', '#3B82F6', '#EF4444'],
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
            const orderStatus = <?= $orderStatusJSON ?>;

new Chart(document.getElementById('refillStatusChart'), {
    type: 'bar', // change to bar for histogram
    data: {
        labels: Object.keys(orderStatus),
        datasets: [{
            label: 'Number of Orders',
            data: Object.values(orderStatus),
            backgroundColor: ['#10B981', '#F59E0B', '#EF4444', '#3B82F6', '#A78BFA', '#F87171'],
            borderColor: '#fff',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Number of Orders'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Order Status'
                }
            }
        }
    }
});

            // STATION VS CUSTOMER (pie)
            if (exists('stationCustomerChart')) {
                new Chart(document.getElementById('stationCustomerChart'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Station Owned', 'Customer Owned'],
                        datasets: [{
                            data: [stationCustomer.station || 0, stationCustomer.customer || 0],
                            backgroundColor: ['#1E90FF', '#046CD2'],
                            borderColor: '#fff',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }

        } // end initializeCharts

        // init on DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeCharts);
        } else {
            initializeCharts();
        }
    </script>

</body>

</html>