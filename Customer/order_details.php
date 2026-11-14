<?php
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('customer');
require_once '../Database/db_connection.php';

$db = new Database();
$conn = $db->getConnect();

// ============================================================================
// MAP ONLY: same-file endpoints - no ../Location/... 404
// rider_location: reads rider_location_history FIRST, then loc_history
// customer_location: reads loc_history, then geocodes delivery address
// ============================================================================
if (isset($_GET['action']) && in_array($_GET['action'], ['rider_location', 'customer_location'], true)) {
    header('Content-Type: application/json; charset=utf-8');
    $order_number = $_GET['order_number'] ?? '';
    try {
        $stmt = $conn->prepare("SELECT refill_id, user_id, rider_id FROM water_refills WHERE order_number = :on AND user_id = :uid LIMIT 1");
        $stmt->execute([':on' => $order_number, ':uid' => $_SESSION['id']]);
        $o = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$o) { echo json_encode(['success' => false]); exit; }

        if ($_GET['action'] === 'rider_location') {
            if (empty($o['rider_id'])) { echo json_encode(['success' => false, 'error' => 'No rider assigned']); exit; }
            $loc = null;
            // 1) rider_location_history by refill_id
            try {
                $r = $conn->prepare("SELECT latitude, longitude, timestamp AS created_at FROM rider_location_history WHERE order_id = :oid ORDER BY timestamp DESC LIMIT 1");
                $r->execute([':oid' => $o['refill_id']]);
                $loc = $r->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) { $loc = null; }
            // 2) fallback loc_history by rider_id
            if (!$loc || $loc['latitude'] === null) {
                $l = $conn->prepare("SELECT latitude, longitude, created_at FROM loc_history WHERE user_id = :uid ORDER BY created_at DESC LIMIT 1");
                $l->execute([':uid' => $o['rider_id']]);
                $loc = $l->fetch(PDO::FETCH_ASSOC);
            }
            if ($loc && $loc['latitude'] !== null) {
                $age = time() - strtotime($loc['created_at']);
                echo json_encode(['success' => true, 'status' => 'real_time', 'latitude' => (float)$loc['latitude'], 'longitude' => (float)$loc['longitude'], 'timestamp' => $loc['created_at'], 'is_recent' => $age <= 60, 'age_seconds' => $age]);
            } else {
                echo json_encode(['success' => false]);
            }
            exit;
        }

        // customer_location
        $l = $conn->prepare("SELECT latitude, longitude, created_at FROM loc_history WHERE user_id = :uid ORDER BY created_at DESC LIMIT 1");
        $l->execute([':uid' => $o['user_id']]);
        $loc = $l->fetch(PDO::FETCH_ASSOC);
        if ($loc && $loc['latitude'] !== null) {
            $age = time() - strtotime($loc['created_at']);
            echo json_encode(['success' => true, 'status' => 'has_saved_location', 'latitude' => (float)$loc['latitude'], 'longitude' => (float)$loc['longitude'], 'timestamp' => $loc['created_at'], 'is_recent' => $age <= 60]);
            exit;
        }
        // geocode delivery address
        $a = $conn->prepare("SELECT address FROM users WHERE user_id = :uid LIMIT 1");
        $a->execute([':uid' => $o['user_id']]);
        $addr = trim($a->fetchColumn() ?: '');
        if ($addr !== '') {
            $url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' . urlencode($addr . ', Philippines');
            $ctx = stream_context_create(['http' => ['header' => "User-Agent: RCH-Water-Customer/1.0\r\n", 'timeout' => 5]]);
            $json = @file_get_contents($url, false, $ctx);
            if ($json) {
                $geo = json_decode($json, true);
                if (!empty($geo[0]['lat'])) {
                    echo json_encode(['success' => true, 'status' => 'geocoded', 'latitude' => (float)$geo[0]['lat'], 'longitude' => (float)$geo[0]['lon'], 'timestamp' => date('Y-m-d H:i:s'), 'is_recent' => true]);
                    exit;
                }
            }
        }
        echo json_encode(['success' => false]);
    } catch (Exception $e) {
        echo json_encode(['success' => false]);
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
        $conn->beginTransaction();
        $stmt = $conn->prepare("SELECT wr.refill_id, wr.order_status, wr.rider_id, u.name as customer_name FROM water_refills wr INNER JOIN users u ON wr.user_id = u.user_id WHERE wr.order_number = :order_number AND wr.user_id = :user_id");
        $stmt->execute([':order_number' => $order_number, ':user_id' => $_SESSION['id']]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order) { $conn->rollBack(); echo json_encode(['success' => false, 'message' => 'Order not found']); exit; }
        if (!in_array($order['order_status'], ['pending', 'assigned'])) { $conn->rollBack(); echo json_encode(['success' => false, 'message' => 'Order cannot be cancelled at this stage']); exit; }
        $finalReason = $cancel_reason ?: 'Cancelled by customer';
        $updateStmt = $conn->prepare("UPDATE water_refills SET order_status = 'cancelled', delay_reason = :cancel_reason, rider_id = NULL WHERE order_number = :order_number AND user_id = :user_id");
        $updateStmt->execute([':cancel_reason' => $finalReason, ':order_number' => $order_number, ':user_id' => $_SESSION['id']]);
        $conn->commit();
        require_once __DIR__ . '/../Notification/send_notification.php';
        require_once __DIR__ . '/../Notification/email.php';
        $adminStmt = $conn->prepare("SELECT user_id FROM users WHERE role = 'admin'");
        $adminStmt->execute();
        $admins = $adminStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($admins as $admin) { createNotification($conn, $admin['user_id'], 'Order Cancelled', "Order #$order_number has been cancelled by customer {$order['customer_name']}. Reason: $finalReason", "Order/order_details.php?order_number=" . $order_number); }
        createNotification($conn, $_SESSION['id'], 'Order Cancelled', "Your order #$order_number has been cancelled successfully.", "Customer/order_details.php?order_number=" . $order_number);
        if ($order['rider_id']) { createNotification($conn, $order['rider_id'], 'Order Cancelled', "Order #$order_number has been cancelled by the customer.", "Rider/order_details.php?order_number=" . $order_number); }
        notifyAdminOrderCancelled($order['refill_id'], $order_number, $order['customer_name'], $finalReason);
        echo json_encode(['success' => true, 'message' => 'Order cancelled successfully', 'order_number' => $order_number]);
    } catch (Exception $e) {
        if ($conn->inTransaction()) { $conn->rollBack(); }
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// ============================================================================
// GET ORDER DETAILS FROM DATABASE
// ============================================================================
$order_number = isset($_GET['order_number']) ? $_GET['order_number'] : '';
if (!$order_number) { header("Location: my_orders.php"); exit; }

try {
    $stmt = $conn->prepare("SELECT wr.*, u.name as customer_name, u.phone_number as customer_phone, u.address as customer_address, rider.name as rider_name, rider.phone_number as rider_phone FROM water_refills wr INNER JOIN users u ON wr.user_id = u.user_id LEFT JOIN users rider ON wr.rider_id = rider.user_id WHERE wr.order_number = :order_number AND wr.user_id = :user_id");
    $stmt->execute([':order_number' => $order_number, ':user_id' => $_SESSION['id']]);
    $orderData = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$orderData) { header("Location: my_orders.php"); exit; }
    $itemsStmt = $conn->prepare("SELECT wri.*, i.item_name, i.description FROM water_refill_items wri INNER JOIN inventory i ON wri.product_id = i.item_id WHERE wri.refill_id = :refill_id");
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

$subtotal = 0;
foreach ($orderItems as $item) { $subtotal += $item['subtotal']; }
$totalCost = $orderData['total_amount'];
$deliveryFee = 40.00;
$taxRate = 0.08;
$taxAmount = ($subtotal * $taxRate);

function formatCurrency($amount) { return '₱' . number_format($amount, 2); }

$statusColors = [
    'Pending' => 'text-yellow-600 bg-yellow-50',
    'Assigned' => 'text-blue-600 bg-blue-50',
    'Confirmed' => 'text-blue-600 bg-blue-50',
    'On delivery' => 'text-purple-600 bg-purple-50',
    'Delivered' => 'text-green-600 bg-green-50',
    'Cancelled' => 'text-red-600 bg-red-50'
];
$statusColor = $statusColors[$orderStatus] ?? 'text-gray-600 bg-gray-50';
$canCancel = in_array(strtolower($orderData['order_status']), ['pending', 'assigned']);

// MAP ONLY: show map for all live statuses, not just On delivery
$showMap = in_array(strtolower($orderData['order_status']), ['assigned','pickup_in_progress','picked_up','on_delivery']) && !empty($orderData['rider_id']);
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
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
    <style>
        body { font-family: 'Poppins', sans-serif; padding-left: 5rem; transition: padding-left 0.5s; margin: 0; padding-top: 0; padding-bottom: 0; padding-right: 0; }
        #delivery-map { width: 100%; height: 400px; border-radius: 0.75rem; z-index: 1 !important; }
        .group { z-index: 9999 !important; }
        .status-badge { display: inline-block; padding: 0.5rem 1rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 600; }
        .map-wrap { position: relative; }
        .live-pill { position: absolute; top: 10px; left: 10px; z-index: 1000; background: #fff; border-radius: 9999px; padding: 6px 12px; font-size: 12px; font-weight: 600; box-shadow: 0 2px 8px rgba(0,0,0,.15); display: flex; align-items: center; gap: 6px; }
        .live-dot { width: 9px; height: 9px; border-radius: 50%; background: #16a34a; animation: livePulse 1.6s infinite; }
        .live-dot.stale { background: #f59e0b; }
        .live-dot.waiting { background: #9ca3af; animation: none; }
        @keyframes livePulse { 0%{box-shadow:0 0 0 0 rgba(22,163,74,.5)} 70%{box-shadow:0 0 0 8px rgba(22,163,74,0)} 100%{box-shadow:0 0 0 0 rgba(22,163,74,0)} }
        .eta-pill { position: absolute; bottom: 10px; left: 10px; z-index: 1000; background: #111827; color: #fff; border-radius: 12px; padding: 8px 12px; font-size: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.2); }
        .leaflet-routing-container { display: none !important; }
    </style>
</head>
<body class="bg-gray-100">
    <script src="includes/sidebar.js"></script>
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
                            <div class="lg:col-span-2 space-y-6">
                                <!-- Real-Time Tracking -->
                                <div class="bg-white rounded-xl shadow-sm border p-6">
                                    <div class="flex items-center gap-2 text-blue-600 font-semibold mb-4">
                                        <i class="fas fa-location-arrow"></i>
                                        <span>Delivery Tracking</span>
                                    </div>
                                    <?php if ($showMap): ?>
                                        <div class="map-wrap">
                                            <div id="delivery-map" class="w-full h-80 rounded-xl border border-gray-200"></div>
                                            <div class="live-pill"><span id="liveDot" class="live-dot"></span><span id="liveText">Locating rider…</span></div>
                                            <div class="eta-pill" id="etaPill">Calculating route…</div>
                                        </div>
                                        <div class="bg-blue-50 rounded-lg p-4 mt-4">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="text-xs text-gray-500 mb-1">Rider</p>
                                                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($riderName) ?></p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-xs text-gray-500 mb-1">Contact</p>
                                                    <p class="font-semibold text-gray-800"><i class="fas fa-phone text-blue-600 mr-1"></i><?= htmlspecialchars($riderPhone) ?></p>
                                                </div>
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1" id="riderUpdated">Waiting for rider location…</div>
                                        </div>
                                    <?php else: ?>
                                        <div class="bg-gray-50 rounded-lg p-8 text-center">
                                            <div class="text-gray-400 mb-3"><i class="fas fa-map-marked-alt text-5xl"></i></div>
                                            <p class="text-gray-600 font-medium">Real-time tracking not available</p>
                                            <p class="text-gray-500 text-sm mt-1">
                                                <?php if (!$orderData['rider_id']): ?>Waiting for rider assignment
                                                <?php elseif (strtolower($orderData['order_status']) === 'delivered'): ?>Order delivered
                                                <?php elseif (strtolower($orderData['order_status']) === 'pending'): ?>Tracking will be available once a rider is assigned
                                                <?php else: ?>Tracking will be available when order is out for delivery<?php endif; ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <!-- Order Items -->
                                <div class="bg-white rounded-xl shadow-sm border p-6">
                                    <h2 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-shopping-bag text-blue-600 mr-2"></i>Order Items</h2>
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-sm">
                                            <thead class="border-b-2 border-gray-200"><tr class="text-gray-600"><th class="text-left py-3 px-2">Product</th><th class="text-center py-3 px-2">Qty</th><th class="text-right py-3 px-2">Unit Price</th><th class="text-right py-3 px-2">Total</th></tr></thead>
                                            <tbody>
                                                <?php foreach ($orderItems as $item): ?>
                                                    <tr class="border-b border-gray-100">
                                                        <td class="py-4 px-2"><div class="font-medium text-gray-800"><?= htmlspecialchars($item['item_name']) ?></div>
                                                            <?php if ($item['gallon_type']): ?><div class="text-xs text-gray-500 mt-1"><?= htmlspecialchars($item['gallon_type']) ?> - <?= htmlspecialchars($item['gallon_size']) ?></div><?php endif; ?>
                                                            <?php if ($item['gallon_owner']): ?><div class="text-xs text-blue-600 mt-1"><i class="fas fa-user-circle mr-1"></i><?= htmlspecialchars($item['gallon_owner']) ?></div><?php endif; ?>
                                                        </td>
                                                        <td class="text-center font-semibold text-gray-700 px-2"><?= intval($item['quantity']) ?></td>
                                                        <td class="text-right text-gray-700 px-2"><?= formatCurrency($item['unit_price']) ?></td>
                                                        <td class="text-right font-semibold text-gray-800 px-2"><?= formatCurrency($item['subtotal']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-6 pt-4 border-t space-y-2">
                                        <div class="flex justify-between text-sm"><span class="text-gray-600">Subtotal</span><span class="text-gray-800"><?= formatCurrency($subtotal) ?></span></div>
                                        <div class="flex justify-between text-sm"><span class="text-gray-600">Tax (<?= $taxRate * 100 ?>%)</span><span class="text-gray-800"><?= formatCurrency($taxAmount) ?></span></div>
                                        <div class="flex justify-between text-sm"><span class="text-gray-600">Delivery Fee</span><span class="text-gray-800"><?= formatCurrency($deliveryFee) ?></span></div>
                                        <div class="flex justify-between pt-3 border-t-2 text-lg font-bold"><span class="text-gray-800">Total</span><span class="text-blue-600"><?= formatCurrency($totalCost) ?></span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-6">
                                <div class="bg-white rounded-xl shadow-sm border p-6">
                                    <h2 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-map-marker-alt text-blue-600 mr-2"></i>Delivery To</h2>
                                    <div class="space-y-3">
                                        <div><p class="text-xs text-gray-500 mb-1">Name</p><p class="font-semibold text-gray-800"><?= htmlspecialchars($customerName) ?></p></div>
                                        <div><p class="text-xs text-gray-500 mb-1">Phone</p><p class="text-gray-700"><?= htmlspecialchars($customerPhone) ?></p></div>
                                        <div><p class="text-xs text-gray-500 mb-1">Address</p><p class="text-gray-700"><?= htmlspecialchars($deliveryAddress) ?></p></div>
                                    </div>
                                </div>
                                <div class="bg-white rounded-xl shadow-sm border p-6">
                                    <h2 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-info-circle text-blue-600 mr-2"></i>Order Info</h2>
                                    <div class="space-y-3">
                                        <div class="flex justify-between text-sm"><span class="text-gray-600">Order Date</span><span class="text-gray-800 font-medium"><?= htmlspecialchars($orderDateTime) ?></span></div>
                                        <?php if ($pickupDateTime !== 'Pending'): ?><div class="flex justify-between text-sm"><span class="text-gray-600">Pickup Date</span><span class="text-gray-800 font-medium"><?= htmlspecialchars($pickupDateTime) ?></span></div><?php endif; ?>
                                        <div class="flex justify-between text-sm"><span class="text-gray-600">Delivery Date</span><span class="text-gray-800 font-medium"><?= htmlspecialchars($deliveryDateTime) ?></span></div>
                                        <div class="pt-3 border-t"><div class="flex justify-between text-sm mb-1"><span class="text-gray-600">Payment Method</span></div><span class="inline-block bg-green-50 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">Cash on Delivery</span></div>
                                    </div>
                                </div>
                                <?php if ($orderData['rider_id']): ?>
                                    <div class="bg-white rounded-xl shadow-sm border p-6">
                                        <h2 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-motorcycle text-blue-600 mr-2"></i>Rider</h2>
                                        <div class="space-y-3">
                                            <div><p class="text-xs text-gray-500 mb-1">Name</p><p class="font-semibold text-gray-800"><?= htmlspecialchars($riderName) ?></p></div>
                                            <div><p class="text-xs text-gray-500 mb-1">Phone</p><p class="text-gray-700"><?= htmlspecialchars($riderPhone) ?></p></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if ($orderData['delay_reason']): ?>
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4"><div class="flex items-start gap-2"><i class="fas fa-exclamation-triangle text-yellow-600 mt-1"></i><div><p class="font-semibold text-yellow-800 mb-1">Delay Notice</p><p class="text-sm text-yellow-700"><?= htmlspecialchars($orderData['delay_reason']) ?></p></div></div></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($canCancel): ?>
                            <div class="mt-6 flex justify-end"><button id="cancelOrderBtn" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-medium transition-colors inline-flex items-center gap-2 shadow-sm"><i class="fas fa-times-circle"></i><span>Cancel Order</span></button></div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
<script>
    const orderNumber = "<?= htmlspecialchars($orderId) ?>";
    document.addEventListener('DOMContentLoaded', function() {
        const cancelBtn = document.getElementById('cancelOrderBtn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', async function() {
                const result = await Swal.fire({
                    title: 'Cancel Order?', text: 'Are you sure you want to cancel this order?', icon: 'warning',
                    input: 'textarea', inputLabel: 'Reason for cancellation (optional)', inputPlaceholder: 'Please tell us why you want to cancel...',
                    showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, cancel order', cancelButtonText: 'No, keep order', showLoaderOnConfirm: true,
                    preConfirm: async (reason) => {
                        try {
                            const formData = new FormData();
                            formData.append('action', 'cancel_order');
                            formData.append('order_number', orderNumber);
                            formData.append('cancel_reason', reason || '');
                            const response = await fetch('order_details.php', { method: 'POST', body: formData });
                            const data = await response.json();
                            if (!data.success) throw new Error(data.message || 'Failed to cancel order');
                            return data;
                        } catch (error) { Swal.showValidationMessage(`Error: ${error.message}`); }
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                });
                if (result.isConfirmed) {
                    await Swal.fire({ title: 'Order Cancelled!', text: 'Your order has been cancelled successfully.', icon: 'success', confirmButtonColor: '#2563eb', confirmButtonText: 'OK' });
                    window.location.href = 'my_orders.php';
                }
            });
        }
    });
</script>

<?php if ($showMap): ?>
<!-- MAP ONLY: only runs when map div exists -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    const mapEl = document.getElementById('delivery-map');
    if (!mapEl) return;
    const orderNumber = "<?= htmlspecialchars($orderData['order_number']) ?>";
    const customerAddress = <?= json_encode($deliveryAddress) ?>;
    const STATION = { lat: 13.9411, lng: 121.1628 };
    let customerPos = null, customerResolved = false;
    let riderPos = null, riderMarker = null, routeControl = null, firstFit = true;

    const map = L.map('delivery-map').setView([STATION.lat, STATION.lng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, minZoom: 10, attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>' }).addTo(map);
    setTimeout(() => map.invalidateSize(), 500);
    const sidebar = document.querySelector('.group');
    if (sidebar) {
        sidebar.addEventListener('mouseenter', () => setTimeout(() => map.invalidateSize(), 300));
        sidebar.addEventListener('mouseleave', () => setTimeout(() => map.invalidateSize(), 300));
    }
    const stationIcon = L.divIcon({ className: '', html: '<div style="background:#16a34a;border:3px solid #fff;border-radius:50%;width:18px;height:18px;box-shadow:0 0 0 6px rgba(22,163,74,.25);"></div>', iconSize: [18,18], iconAnchor: [9,9] });
    const customerIcon = L.divIcon({ className: '', html: '<div style="background:#2563eb;border:3px solid #fff;border-radius:50%;width:18px;height:18px;box-shadow:0 0 0 6px rgba(37,99,235,.25);"></div>', iconSize: [18,18], iconAnchor: [9,9] });
    const riderIcon = L.divIcon({ className: '', html: '<div style="background:#f59e0b;border:3px solid #fff;border-radius:50%;width:18px;height:18px;box-shadow:0 0 0 6px rgba(245,158,11,.25);"></div>', iconSize: [18,18], iconAnchor: [9,9] });

    L.marker([STATION.lat, STATION.lng], { icon: stationIcon }).addTo(map).bindPopup(`<div style="text-align:center;min-width:150px;"><strong style="color:#2563eb;font-size:14px;">🏢 RCH Water Station</strong></div>`);
    const customerMarker = L.marker([STATION.lat, STATION.lng], { icon: customerIcon }).addTo(map).bindPopup(`<div style="text-align:center;min-width:180px;"><strong style="color:#16a34a;font-size:14px;">📍 Your Location</strong><br><small style="color:#6b7280;"><?= htmlspecialchars($deliveryAddress) ?></small></div>`);
    customerMarker.setOpacity(0);

    function samePoint(a,b){ if(!a||!b) return false; return Math.abs(a.lat-b.lat)<0.0001 && Math.abs(a.lng-b.lng)<0.0001; }
    function drawRoute(from,to,color,dashed){
        if(!from||!to||samePoint(from,to)) return;
        if(routeControl){ map.removeControl(routeControl); routeControl=null; }
        routeControl = L.Routing.control({ waypoints:[L.latLng(from.lat,from.lng), L.latLng(to.lat,to.lng)], lineOptions:{ styles:[{color:color, weight:5, opacity:0.9, dashArray: dashed?'10,10':null}] }, createMarker:()=>null, addWaypoints:false, routeWhileDragging:false, show:false, fitSelectedRoutes:false }).addTo(map);
        routeControl.on('routesfound', e => {
            const r = e.routes[0];
            if((r.summary.totalDistance/1000) < 0.05) return;
            document.getElementById('etaPill').innerHTML = riderPos
                ? `🏍 Rider is <b>${(r.summary.totalDistance/1000).toFixed(1)} km</b> away • ETA <b>~${Math.max(1, Math.round(r.summary.totalTime/60))} min</b>`
                : `📍 Station to you: <b>${(r.summary.totalDistance/1000).toFixed(1)} km</b> • ~${Math.max(1, Math.round(r.summary.totalTime/60))} min`;
        });
        routeControl.on('routingerror', ()=>{ if(!riderPos) document.getElementById('etaPill').textContent = 'Route preview unavailable — map is still working'; });
    }
    function applyCustomerPos(pos, source){
        if(!pos) return;
        if(samePoint(pos, STATION) && source==='geocoded') return;
        customerPos = pos; customerResolved = true;
        customerMarker.setLatLng([pos.lat, pos.lng]); customerMarker.setOpacity(1);
        if(riderPos) drawRoute(riderPos, customerPos, '#f59e0b', false);
        else drawRoute(STATION, customerPos, '#2563eb', true);
        if(firstFit){ map.fitBounds(L.latLngBounds([L.latLng(STATION.lat, STATION.lng), L.latLng(customerPos.lat, customerPos.lng)]).pad(0.25)); firstFit=false; }
    }
    async function geocodeClientSide(){
        if(!customerAddress) return false;
        try{
            const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(customerAddress + ', Philippines')}`);
            const data = await res.json();
            if(data && data[0]){ applyCustomerPos({lat: parseFloat(data[0].lat), lng: parseFloat(data[0].lon)}, 'geocoded'); return true; }
        }catch(e){}
        return false;
    }
    async function loadCustomer(){
        try{
            const res = await fetch(`order_details.php?action=customer_location&order_number=${encodeURIComponent(orderNumber)}`);
            const d = await res.json();
            if(d.success){ applyCustomerPos({lat:d.latitude, lng:d.longitude}, d.status||'saved'); return; }
        }catch(e){}
        const ok = await geocodeClientSide();
        if(!ok) document.getElementById('etaPill').textContent = 'Your location not available — showing station';
    }
    async function fetchRider(){
        try{
            const res = await fetch(`order_details.php?action=rider_location&order_number=${encodeURIComponent(orderNumber)}`);
            const d = await res.json();
            if(!d.success){ document.getElementById('liveText').textContent = 'Waiting for rider location…'; return; }
            riderPos = {lat:d.latitude, lng:d.longitude};
            document.getElementById('liveText').textContent = d.is_recent ? 'LIVE • Rider moving' : 'Last known location';
            document.getElementById('liveDot').classList.toggle('stale', !d.is_recent);
            const el = document.getElementById('riderUpdated');
            if(el && d.timestamp) el.textContent = 'Updated: ' + new Date(d.timestamp.replace(' ','T')).toLocaleTimeString();
            if(!riderMarker){
                riderMarker = L.marker([riderPos.lat, riderPos.lng], {icon: riderIcon}).addTo(map).bindPopup(`<div style="text-align:center;min-width:160px;"><strong style="color:#f59e0b;font-size:14px;">🏍 Rider</strong><br><small style="font-weight:600;"><?= htmlspecialchars($riderName) ?></small><br><small style="color:#6b7280;"><?= htmlspecialchars($riderPhone) ?></small></div>`);
                if(customerResolved) map.fitBounds(L.latLngBounds([L.latLng(riderPos.lat, riderPos.lng), L.latLng(customerPos.lat, customerPos.lng)]).pad(0.25));
            } else {
                const from = riderMarker.getLatLng(), to = L.latLng(riderPos.lat, riderPos.lng); let i=0;
                const anim = setInterval(()=>{ i++; riderMarker.setLatLng([from.lat + (to.lat-from.lat)*i/20, from.lng + (to.lng-from.lng)*i/20]); if(i>=20) clearInterval(anim); },40);
            }
            if(customerResolved) drawRoute(riderPos, customerPos, '#f59e0b', false);
        }catch(e){ console.error(e); }
    }
    loadCustomer();
    fetchRider();
    setInterval(fetchRider, 5000);
});
</script>
<?php endif; ?>
</body>
</html>