<?php
    require_once __DIR__ . '/../session.php';
    redirectIfRoleMismatch('customer');
    require_once '../Database/db_connection.php';

$db = new Database();
$conn = $db->getConnect();

// ============================================================================
// AJAX ENDPOINT FOR RIDER LOCATION
// ============================================================================
if (isset($_GET['action']) && $_GET['action'] === 'rider_location') {
    header('Content-Type: application/json; charset=utf-8');
    $order_number = isset($_GET['order_number']) ? $_GET['order_number'] : '';
    
    try {
        $stmt = $conn->prepare("
            SELECT wr.rider_id 
            FROM water_refills wr 
            WHERE wr.order_number = :order_number
        ");
        $stmt->execute([':order_number' => $order_number]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($order && $order['rider_id']) {
            $locStmt = $conn->prepare("
                SELECT latitude, longitude, created_at 
                FROM loc_history 
                WHERE user_id = :rider_id 
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $locStmt->execute([':rider_id' => $order['rider_id']]);
            $loc = $locStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($loc && $loc['latitude'] !== null && $loc['longitude'] !== null) {
                echo json_encode([
                    'latitude' => (float)$loc['latitude'],
                    'longitude' => (float)$loc['longitude'],
                    'timestamp' => $loc['created_at']
                ]);
            } else {
                echo json_encode(['error' => 'No rider location found']);
            }
        } else {
            echo json_encode(['error' => 'No rider assigned']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// ============================================================================
// AJAX ENDPOINT FOR CANCELLING ORDER
// ============================================================================
if (isset($_POST['action']) && $_POST['action'] === 'cancel_order') {
    header('Content-Type: application/json; charset=utf-8');
    $order_number = isset($_POST['order_number']) ? $_POST['order_number'] : '';
    $cancel_reason = isset($_POST['cancel_reason']) ? trim($_POST['cancel_reason']) : '';
    
    try {
        // Start transaction
        $conn->beginTransaction();
        
        // Verify order belongs to user and is cancellable
        $stmt = $conn->prepare("
            SELECT wr.refill_id, wr.order_status, wr.rider_id, u.name as customer_name
            FROM water_refills wr
            INNER JOIN users u ON wr.user_id = u.user_id
            WHERE wr.order_number = :order_number AND wr.user_id = :user_id
        ");
        $stmt->execute([
            ':order_number' => $order_number,
            ':user_id' => $_SESSION['id']
        ]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            $conn->rollBack();
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            exit;
        }
        
        // Check if order can be cancelled
        if (!in_array($order['order_status'], ['pending', 'assigned'])) {
            $conn->rollBack();
            echo json_encode(['success' => false, 'message' => 'Order cannot be cancelled at this stage']);
            exit;
        }
        
        // Prepare cancellation reason
        $finalReason = $cancel_reason ?: 'Cancelled by customer';
        
        // Update order status to cancelled
        $updateStmt = $conn->prepare("
            UPDATE water_refills 
            SET order_status = 'cancelled', 
                delay_reason = :cancel_reason,
                rider_id = NULL
            WHERE order_number = :order_number AND user_id = :user_id
        ");
        
        $updateStmt->execute([
            ':cancel_reason' => $finalReason,
            ':order_number' => $order_number,
            ':user_id' => $_SESSION['id']
        ]);
        
        // Commit transaction
        $conn->commit();
        
        // Send notifications and emails
        require_once __DIR__ . '/../Notification/send_notification.php';
        require_once __DIR__ . '/../Notification/email.php';
        
        // Get admin users
        $adminStmt = $conn->prepare("SELECT user_id FROM users WHERE role = 'admin'");
        $adminStmt->execute();
        $admins = $adminStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Notify all admins
        foreach ($admins as $admin) {
            createNotification(
                $conn,
                $admin['user_id'],
                'Order Cancelled',
                "Order #$order_number has been cancelled by customer {$order['customer_name']}. Reason: $finalReason",
                "Order/order_details.php?order_number=" . $order_number
            );
        }
        
        // Notify customer (confirmation)
        createNotification(
            $conn,
            $_SESSION['id'],
            'Order Cancelled',
            "Your order #$order_number has been cancelled successfully.",
            "Customer/order_details.php?order_number=" . $order_number
        );
        
        // Notify rider if assigned
        if ($order['rider_id']) {
            createNotification(
                $conn,
                $order['rider_id'],
                'Order Cancelled',
                "Order #$order_number has been cancelled by the customer.",
                "Rider/order_details.php?order_number=" . $order_number
            );
        }
        
        // Send email to admin
        notifyAdminOrderCancelled(
            $order['refill_id'],
            $order_number,
            $order['customer_name'],
            $finalReason
        );
        
        echo json_encode([
            'success' => true, 
            'message' => 'Order cancelled successfully',
            'order_number' => $order_number
        ]);
        
    } catch (Exception $e) {
        // Rollback on error
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// ============================================================================
// GET ORDER DETAILS FROM DATABASE
// ============================================================================
$order_number = isset($_GET['order_number']) ? $_GET['order_number'] : '';

if (!$order_number) {
    header("Location: my_orders.php");
    exit;
}

try {
    // Fetch order details with user and rider information
    $stmt = $conn->prepare("
        SELECT 
            wr.*,
            u.name as customer_name,
            u.phone_number as customer_phone,
            u.address as customer_address,
            rider.name as rider_name,
            rider.phone_number as rider_phone
        FROM water_refills wr
        INNER JOIN users u ON wr.user_id = u.user_id
        LEFT JOIN users rider ON wr.rider_id = rider.user_id
        WHERE wr.order_number = :order_number AND wr.user_id = :user_id
    ");
    
    $stmt->execute([
        ':order_number' => $order_number,
        ':user_id' => $_SESSION['id']
    ]);
    
    $orderData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$orderData) {
        header("Location: my_orders.php");
        exit;
    }
    
    // Fetch order items using refill_id from the order data
    $itemsStmt = $conn->prepare("
        SELECT 
            wri.*,
            i.item_name,
            i.description
        FROM water_refill_items wri
        INNER JOIN inventory i ON wri.product_id = i.item_id
        WHERE wri.refill_id = :refill_id
    ");
    
    $itemsStmt->execute([':refill_id' => $orderData['refill_id']]);
    $orderItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    die("Error fetching order details: " . $e->getMessage());
}

// ============================================================================
// PAGE INFORMATION
// ============================================================================
$pageTitle = "Order Details";
$pageSubtitle = "Track your order in real-time";
$currentPage = 'my_orders.php';

// Extract order data
$orderStatus = ucfirst(str_replace('_', ' ', $orderData['order_status']));
$orderId = $orderData['order_number'];
$customerName = $orderData['customer_name'];
$customerPhone = $orderData['customer_phone'];
$deliveryAddress = $orderData['customer_address'];
$riderName = $orderData['rider_name'] ?? 'Not assigned yet';
$riderPhone = $orderData['rider_phone'] ?? '—';
$orderDateTime = date('F d, Y | h:i A', strtotime($orderData['created_at']));
$deliveryDateTime = $orderData['delivery_datetime'] ? date('F d, Y | h:i A', strtotime($orderData['delivery_datetime'])) : 'Pending';
$pickupDateTime = $orderData['pickup_datetime'] ? date('F d, Y | h:i A', strtotime($orderData['pickup_datetime'])) : 'Pending';

// Calculate totals
$subtotal = 0;
foreach ($orderItems as $item) {
    $subtotal += $item['subtotal'];
}

// Verify with stored total_amount
$totalCost = $orderData['total_amount'];
$deliveryFee = 40.00; // You may want to make this dynamic
$taxRate = 0.08;
$taxAmount = ($subtotal * $taxRate);

// Function to format currency
function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

// Status styling
$statusColors = [
    'Pending' => 'text-yellow-600 bg-yellow-50',
    'Assigned' => 'text-blue-600 bg-blue-50',
    'Confirmed' => 'text-blue-600 bg-blue-50',
    'On delivery' => 'text-purple-600 bg-purple-50',
    'Delivered' => 'text-green-600 bg-green-50',
    'Cancelled' => 'text-red-600 bg-red-50'
];

$statusColor = $statusColors[$orderStatus] ?? 'text-gray-600 bg-gray-50';

// Check if order can be cancelled
$canCancel = in_array(strtolower($orderData['order_status']), ['pending', 'assigned']);

// Customer location (extract from address or use default)
$customerLat = 13.9587;
$customerLng = 121.1444;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RCH Water - <?= htmlspecialchars($pageTitle) ?></title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            padding-left: 5rem;
            transition: padding-left 0.5s;
            margin: 0;
            padding-top: 0;
            padding-bottom: 0;
            padding-right: 0;
        }

        #rider-map {
            width: 100%;
            height: 400px;
            border-radius: 0.75rem;
            z-index: 1 !important;
        }

        .group {
            z-index: 9999 !important;
        }

        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
        }
    </style>
</head>

<body class="bg-gray-100">

    <script src="includes/sidebar.js"></script>

    <!-- MAIN CONTENT -->
    <div class="flex h-screen">
        <div class="flex-1">
            <main class="bg-white h-full overflow-hidden">
                <div class="main-header bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                    <?php
                    $pageTitle = "Order Details";
                    $pageSubtitle = "Track your order in real-time";
                    include 'includes/header.php';
                    ?>
                </div>

                <div class="p-8 h-[calc(100vh-80px)] overflow-y-auto">
                    <div class="max-w-full mx-auto">
            
            <!-- Header -->
            <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
                <div class="flex items-center gap-3 mb-2">
                    <button onclick="window.location.href='my_orders.php'" class="text-blue-600 hover:text-blue-700 transition-colors">
                        <i class="bi bi-arrow-left-circle-fill text-3xl"></i>
                    </button>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Order #<?= htmlspecialchars($orderId) ?></h1>
                        <p class="text-sm text-gray-500"><?= htmlspecialchars($orderDateTime) ?></p>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="status-badge <?= $statusColor ?>">
                        <i class="fas fa-circle text-xs mr-2"></i><?= htmlspecialchars($orderStatus) ?>
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Real-Time Tracking - Always Show Container -->
                    <div class="bg-white rounded-xl shadow-sm border p-6">
                        <div class="flex items-center gap-2 text-blue-600 font-semibold mb-4">
                            <i class="fas fa-location-arrow"></i>
                            <span>Delivery Tracking</span>
                        </div>
                        
                        <?php if ($orderStatus === 'On delivery' && $orderData['rider_id']): ?>
                            <div id="rider-map" class="mb-4"></div>
                            <div class="bg-blue-50 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">Rider</p>
                                        <p class="font-semibold text-gray-800"><?= htmlspecialchars($riderName) ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-gray-500 mb-1">Contact</p>
                                        <p class="font-semibold text-gray-800">
                                            <i class="fas fa-phone text-blue-600 mr-1"></i><?= htmlspecialchars($riderPhone) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="bg-gray-50 rounded-lg p-8 text-center">
                                <div class="text-gray-400 mb-3">
                                    <i class="fas fa-map-marked-alt text-5xl"></i>
                                </div>
                                <p class="text-gray-600 font-medium">Real-time tracking not available</p>
                                <p class="text-gray-500 text-sm mt-1">
                                    <?php if (!$orderData['rider_id']): ?>
                                        Waiting for rider assignment
                                    <?php else: ?>
                                        Tracking will be available when order is out for delivery
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Order Items -->
                    <div class="bg-white rounded-xl shadow-sm border p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">
                            <i class="fas fa-shopping-bag text-blue-600 mr-2"></i>Order Items
                        </h2>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="border-b-2 border-gray-200">
                                    <tr class="text-gray-600">
                                        <th class="text-left py-3 px-2">Product</th>
                                        <th class="text-center py-3 px-2">Qty</th>
                                        <th class="text-right py-3 px-2">Unit Price</th>
                                        <th class="text-right py-3 px-2">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orderItems as $item): ?>
                                    <tr class="border-b border-gray-100">
                                        <td class="py-4 px-2">
                                            <div class="font-medium text-gray-800"><?= htmlspecialchars($item['item_name']) ?></div>
                                            <?php if ($item['gallon_type']): ?>
                                            <div class="text-xs text-gray-500 mt-1"><?= htmlspecialchars($item['gallon_type']) ?> - <?= htmlspecialchars($item['gallon_size']) ?></div>
                                            <?php endif; ?>
                                            <?php if ($item['gallon_owner']): ?>
                                            <div class="text-xs text-blue-600 mt-1">
                                                <i class="fas fa-user-circle mr-1"></i><?= htmlspecialchars($item['gallon_owner']) ?>
                                            </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center font-semibold text-gray-700 px-2"><?= intval($item['quantity']) ?></td>
                                        <td class="text-right text-gray-700 px-2"><?= formatCurrency($item['unit_price']) ?></td>
                                        <td class="text-right font-semibold text-gray-800 px-2"><?= formatCurrency($item['subtotal']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Cost Breakdown -->
                        <div class="mt-6 pt-4 border-t space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="text-gray-800"><?= formatCurrency($subtotal) ?></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Tax (<?= $taxRate * 100 ?>%)</span>
                                <span class="text-gray-800"><?= formatCurrency($taxAmount) ?></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Delivery Fee</span>
                                <span class="text-gray-800"><?= formatCurrency($deliveryFee) ?></span>
                            </div>
                            <div class="flex justify-between pt-3 border-t-2 text-lg font-bold">
                                <span class="text-gray-800">Total</span>
                                <span class="text-blue-600"><?= formatCurrency($totalCost) ?></span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    
                    <!-- Delivery Information -->
                    <div class="bg-white rounded-xl shadow-sm border p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">
                            <i class="fas fa-map-marker-alt text-blue-600 mr-2"></i>Delivery To
                        </h2>
                        <div class="space-y-3">
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Name</p>
                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($customerName) ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Phone</p>
                                <p class="text-gray-700"><?= htmlspecialchars($customerPhone) ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Address</p>
                                <p class="text-gray-700"><?= htmlspecialchars($deliveryAddress) ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Order Information -->
                    <div class="bg-white rounded-xl shadow-sm border p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">
                            <i class="fas fa-info-circle text-blue-600 mr-2"></i>Order Info
                        </h2>
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Order Date</span>
                                <span class="text-gray-800 font-medium"><?= htmlspecialchars($orderDateTime) ?></span>
                            </div>
                            <?php if ($pickupDateTime !== 'Pending'): ?>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Pickup Date</span>
                                <span class="text-gray-800 font-medium"><?= htmlspecialchars($pickupDateTime) ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Delivery Date</span>
                                <span class="text-gray-800 font-medium"><?= htmlspecialchars($deliveryDateTime) ?></span>
                            </div>
                            <div class="pt-3 border-t">
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-600">Payment Method</span>
                                </div>
                                <span class="inline-block bg-green-50 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">
                                    Cash on Delivery
                                </span>
                            </div>
                        </div>
                    </div>

                    <?php if ($orderData['rider_id']): ?>
                    <!-- Rider Information -->
                    <div class="bg-white rounded-xl shadow-sm border p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">
                            <i class="fas fa-motorcycle text-blue-600 mr-2"></i>Rider
                        </h2>
                        <div class="space-y-3">
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Name</p>
                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($riderName) ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Phone</p>
                                <p class="text-gray-700"><?= htmlspecialchars($riderPhone) ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($orderData['delay_reason']): ?>
                    <!-- Delay Notice -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                        <div class="flex items-start gap-2">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mt-1"></i>
                            <div>
                                <p class="font-semibold text-yellow-800 mb-1">Delay Notice</p>
                                <p class="text-sm text-yellow-700"><?= htmlspecialchars($orderData['delay_reason']) ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>

            </div>

            <!-- Cancel Order Button (Bottom Right) -->
            <?php if ($canCancel): ?>
            <div class="mt-6 flex justify-end">
                <button id="cancelOrderBtn" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-medium transition-colors inline-flex items-center gap-2 shadow-sm">
                    <i class="fas fa-times-circle"></i>
                    <span>Cancel Order</span>
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
            </main>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const refillId = <?= json_encode($refill_id) ?>;
        const customerLat = <?= json_encode($customerLat) ?>;
        const customerLng = <?= json_encode($customerLng) ?>;
        const hasRider = <?= json_encode($orderData['rider_id'] !== null) ?>;
        const orderStatus = <?= json_encode($orderStatus) ?>;

        let map, riderMarker, customerMarker, routeLine;

        function initMap() {
            const mapElement = document.getElementById('rider-map');
            if (!mapElement) return;

            map = L.map('rider-map').setView([customerLat, customerLng], 14);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);

            // Customer marker (destination)
            const customerIcon = L.divIcon({
                html: '<i class="fas fa-home text-2xl text-red-600"></i>',
                className: 'custom-marker',
                iconSize: [30, 30],
                iconAnchor: [15, 30]
            });

            customerMarker = L.marker([customerLat, customerLng], {
                icon: customerIcon,
                title: 'Delivery Destination'
            }).addTo(map);

            customerMarker.bindPopup(`
                <div class="p-2">
                    <b class="text-blue-600">Delivery Destination</b><br>
                    <span class="text-sm"><?= addslashes($deliveryAddress) ?></span>
                </div>
            `);

            // Rider marker
            const riderIcon = L.divIcon({
                html: '<i class="fas fa-motorcycle text-2xl text-blue-600"></i>',
                className: 'custom-marker',
                iconSize: [30, 30],
                iconAnchor: [15, 30]
            });

            riderMarker = L.marker([customerLat, customerLng], {
                icon: riderIcon,
                title: 'Rider Location'
            }).addTo(map);

            riderMarker.bindPopup(`
                <div class="p-2">
                    <b class="text-blue-600">Rider Location</b><br>
                    <span class="text-sm">Fetching location...</span>
                </div>
            `);

            // Fetch rider location if order is on delivery
            if (orderStatus === 'On delivery' && hasRider) {
                fetchRiderLocation();
                setInterval(fetchRiderLocation, 5000); // Update every 5 seconds
            }
        }

        async function fetchRiderLocation() {
            if (!refillId || !hasRider) return;

            try {
                const resp = await fetch(
                    `order_details.php?refill_id=${refillId}&action=rider_location`,
                    { cache: "no-store" }
                );

                const data = await resp.json();

                if (data.latitude && data.longitude) {
                    const lat = parseFloat(data.latitude);
                    const lng = parseFloat(data.longitude);

                    // Update rider marker position
                    riderMarker.setLatLng([lat, lng]);
                    riderMarker.getPopup().setContent(`
                        <div class="p-2">
                            <b class="text-blue-600">Rider Location</b><br>
                            <span class="text-xs text-gray-600">Last updated: ${new Date(data.timestamp).toLocaleTimeString()}</span>
                        </div>
                    `);

                    // Draw route line
                    if (routeLine) {
                        map.removeLayer(routeLine);
                    }
                    
                    routeLine = L.polyline([
                        [lat, lng],
                        [customerLat, customerLng]
                    ], {
                        color: '#3B82F6',
                        weight: 3,
                        opacity: 0.7,
                        dashArray: '10, 10'
                    }).addTo(map);

                    // Fit bounds to show both markers
                    const bounds = L.latLngBounds([
                        [lat, lng],
                        [customerLat, customerLng]
                    ]);
                    map.fitBounds(bounds, { padding: [50, 50] });
                }
            } catch (err) {
                console.error("Rider location fetch error:", err);
            }
        }

        document.addEventListener('DOMContentLoaded', initMap);
    </script>

    <script>
        // Cancel Order Functionality
        const cancelOrderBtn = document.getElementById('cancelOrderBtn');
        if (cancelOrderBtn) {
            cancelOrderBtn.addEventListener('click', async function() {
                const result = await Swal.fire({
                    title: 'Cancel Order?',
                    text: 'Are you sure you want to cancel this order?',
                    icon: 'warning',
                    input: 'textarea',
                    inputLabel: 'Reason for cancellation (optional)',
                    inputPlaceholder: 'Please tell us why you want to cancel...',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, cancel order',
                    cancelButtonText: 'No, keep order',
                    showLoaderOnConfirm: true,
                    preConfirm: async (reason) => {
                        try {
                            const formData = new FormData();
                            formData.append('action', 'cancel_order');
                            formData.append('order_number', orderNumber);
                            formData.append('cancel_reason', reason || '');

                            const response = await fetch('order_details.php', {
                                method: 'POST',
                                body: formData
                            });

                            const data = await response.json();

                            if (!data.success) {
                                throw new Error(data.message || 'Failed to cancel order');
                            }

                            return data;
                        } catch (error) {
                            Swal.showValidationMessage(`Error: ${error.message}`);
                        }
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                });

                if (result.isConfirmed) {
                    await Swal.fire({
                        title: 'Order Cancelled!',
                        text: 'Your order has been cancelled successfully.',
                        icon: 'success',
                        confirmButtonColor: '#2563eb',
                        confirmButtonText: 'OK'
                    });
                    
                    // Redirect to my orders page
                    window.location.href = 'my_orders.php';
                }
            });
        }
    </script>

</body>
</html>