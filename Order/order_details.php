<?php
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('admin');

require_once '../Database/db_connection.php';

$database = new Database();
$db = $database->getConnect();

// ============================================================
// Helpers - SAME AS CUSTOMER / RIDER
// Delivery address lives ON THE ORDER, not on the profile.
// ============================================================
function colExists($db, $table, $col) {
    try {
        $s = $db->prepare("SHOW COLUMNS FROM `$table` LIKE :c");
        $s->execute([':c' => $col]);
        return (bool)$s->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) { return false; }
}
function pickCol($db, $table, $candidates) {
    foreach ($candidates as $c) { if (colExists($db, $table, $c)) return $c; }
    return null;
}
$wrAddrCol = pickCol($db, 'water_refills', ['delivery_address', 'customer_address', 'address', 'delivery_addr']);
$wrLatCol  = pickCol($db, 'water_refills', ['customer_lat', 'delivery_lat', 'latitude', 'lat']);
$wrLngCol  = pickCol($db, 'water_refills', ['customer_lng', 'delivery_lng', 'longitude', 'lng']);
$userLatCol = pickCol($db, 'users', ['latitude', 'lat', 'customer_lat']);
$userLngCol = pickCol($db, 'users', ['longitude', 'lng', 'customer_lng']);

function getOrderDeliveryAddress($db, $refillId, $userId, $wrAddrCol) {
    if ($wrAddrCol) {
        try {
            $p = $db->prepare("SELECT `$wrAddrCol` FROM water_refills WHERE refill_id = :id LIMIT 1");
            $p->execute([':id' => $refillId]);
            $a = trim($p->fetchColumn() ?: '');
            if ($a !== '') return $a;
        } catch (Exception $e) {}
    }
    $p = $db->prepare("SELECT address FROM users WHERE user_id = :uid LIMIT 1");
    $p->execute([':uid' => $userId]);
    return trim($p->fetchColumn() ?: '');
}

// ============================================================
// MAP ONLY: same-file location endpoints
// ============================================================
// POST: save_customer_pin - admin drags customer pin to correct house
if (isset($_POST['action']) && $_POST['action'] === 'save_customer_pin') {
    header('Content-Type: application/json; charset=utf-8');
    $on = $_POST['order_number'] ?? '';
    $lat = floatval($_POST['latitude'] ?? 0);
    $lng = floatval($_POST['longitude'] ?? 0);
    if (!$on || !$lat || !$lng) { echo json_encode(['success'=>false,'message'=>'Missing data']); exit; }
    try {
        // Save on THIS order - column may not exist on old DB, so try/catch
        if ($wrLatCol && $wrLngCol) {
            $u = $db->prepare("UPDATE water_refills SET `$wrLatCol` = :lat, `$wrLngCol` = :lng WHERE order_number = :on LIMIT 1");
            $u->execute([':lat'=>$lat, ':lng'=>$lng, ':on'=>$on]);
        }
        if ($userLatCol && $userLngCol) {
            $u2 = $db->prepare("UPDATE users u JOIN water_refills r ON r.user_id = u.user_id SET u.`$userLatCol` = :lat, u.`$userLngCol` = :lng WHERE r.order_number = :on LIMIT 1");
            $u2->execute([':lat'=>$lat, ':lng'=>$lng, ':on'=>$on]);
        }
        echo json_encode(['success'=>true]);
    } catch (Exception $e) { echo json_encode(['success'=>false,'message'=>$e->getMessage()]); }
    exit;
}

// GET: rider_location / customer_location
if (isset($_GET['action']) && in_array($_GET['action'], ['rider_location', 'customer_location'], true)) {
    header('Content-Type: application/json; charset=utf-8');
    $on = $_GET['order_number'] ?? '';
    try {
        $s = $db->prepare("SELECT refill_id, user_id, rider_id FROM water_refills WHERE order_number = :on LIMIT 1");
        $s->execute([':on' => $on]);
        $o = $s->fetch(PDO::FETCH_ASSOC);
        if (!$o) { echo json_encode(['success' => false]); exit; }

        if ($_GET['action'] === 'rider_location') {
            if (empty($o['rider_id'])) { echo json_encode(['success' => false, 'error' => 'No rider assigned']); exit; }
            $loc = null;
            try {
                $r = $db->prepare("SELECT latitude, longitude, timestamp AS created_at FROM rider_location_history WHERE order_id = :oid ORDER BY timestamp DESC LIMIT 1");
                $r->execute([':oid' => $o['refill_id']]);
                $loc = $r->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) { $loc = null; }
            if (!$loc || $loc['latitude'] === null) {
                $l = $db->prepare("SELECT latitude, longitude, created_at FROM loc_history WHERE user_id = :uid ORDER BY created_at DESC LIMIT 1");
                $l->execute([':uid' => $o['rider_id']]);
                $loc = $l->fetch(PDO::FETCH_ASSOC);
            }
            if ($loc && $loc['latitude'] !== null && $loc['longitude'] !== null) {
                $age = time() - strtotime($loc['created_at']);
                echo json_encode(['success' => true,'status' => 'real_time','latitude' => (float)$loc['latitude'],'longitude' => (float)$loc['longitude'],'timestamp' => $loc['created_at'],'is_recent' => $age <= 60,'age_seconds' => $age]);
            } else { echo json_encode(['success' => false]); }
            exit;
        }

        // ---- customer_location - SAME AS CUSTOMER / RIDER ----
        // NO loc_history for customer - that was "last GPS anywhere", not the house.
        // 1) Exact pin on THIS order
        if ($wrLatCol && $wrLngCol) {
            $p = $db->prepare("SELECT `$wrLatCol` AS lat, `$wrLngCol` AS lng FROM water_refills WHERE refill_id = :id LIMIT 1");
            $p->execute([':id' => $o['refill_id']]);
            $pin = $p->fetch(PDO::FETCH_ASSOC);
            if ($pin && $pin['lat'] !== null && $pin['lng'] !== null && (float)$pin['lat'] != 0) {
                echo json_encode(['success'=>true,'status'=>'exact_pin','latitude'=>(float)$pin['lat'],'longitude'=>(float)$pin['lng'],'timestamp'=>date('Y-m-d H:i:s'),'is_recent'=>true]);
                exit;
            }
        }
        // 2) Exact pin on customer profile
        if ($userLatCol && $userLngCol) {
            $p = $db->prepare("SELECT `$userLatCol` AS lat, `$userLngCol` AS lng FROM users WHERE user_id = :uid LIMIT 1");
            $p->execute([':uid' => $o['user_id']]);
            $pin = $p->fetch(PDO::FETCH_ASSOC);
            if ($pin && $pin['lat'] !== null && $pin['lng'] !== null && (float)$pin['lat'] != 0) {
                echo json_encode(['success'=>true,'status'=>'exact_pin','latitude'=>(float)$pin['lat'],'longitude'=>(float)$pin['lng'],'timestamp'=>date('Y-m-d H:i:s'),'is_recent'=>true]);
                exit;
            }
        }
        // 3) GEOCODE - ORDER delivery address, NOT profile address - same attempts as Customer/Rider
        $addr = getOrderDeliveryAddress($db, $o['refill_id'], $o['user_id'], $wrAddrCol);
        if ($addr !== '') {
            $full = trim($addr);
            $attempts = [];
            $attempts[] = $full . ', Philippines';
            $noHouseNo = preg_replace('/^\s*\d+\s*,?\s*/', '', $full);
            if ($noHouseNo && $noHouseNo !== $full) $attempts[] = $noHouseNo . ', Philippines';
            $parts = array_values(array_filter(array_map('trim', explode(',', $full))));
            if (count($parts) >= 3) {
                $barangayCity = implode(', ', array_slice($parts, -3));
                if (!in_array($barangayCity . ', Philippines', $attempts)) $attempts[] = $barangayCity . ', Philippines';
            }
            if (count($parts) >= 2) {
                $cityOnly = implode(', ', array_slice($parts, -2));
                if (!in_array($cityOnly . ', Philippines', $attempts)) $attempts[] = $cityOnly . ', Philippines';
            }
            $attempts[] = 'Lipa City, Batangas, Philippines';
            foreach ($attempts as $q) {
                $url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' . urlencode($q);
                $ctx = stream_context_create(['http' => ['header' => "User-Agent: RCH-Water-Admin/1.0\r\n", 'timeout' => 5]]);
                $json = @file_get_contents($url, false, $ctx);
                if (!$json) continue;
                $geo = json_decode($json, true);
                if (!empty($geo[0]['lat'])) {
                    echo json_encode(['success' => true, 'status' => 'geocoded', 'latitude' => (float)$geo[0]['lat'], 'longitude' => (float)$geo[0]['lon'], 'timestamp' => date('Y-m-d H:i:s'), 'is_recent' => true]);
                    exit;
                }
            }
        }
        echo json_encode(['success' => false]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

$orderNumber = isset($_GET['order_number']) ? $_GET['order_number'] : '';
if (empty($orderNumber)) { header('Location: customer_orders.php'); exit; }

// *** SAME AS CUSTOMER: delivery address + exact pin come from THE ORDER ***
$addrSelect = '';
if ($wrAddrCol) $addrSelect = ", r.`$wrAddrCol` AS order_delivery_address";
$pinSelect = '';
if ($wrLatCol && $wrLngCol) $pinSelect = ", r.`$wrLatCol` AS customer_lat, r.`$wrLngCol` AS customer_lng";
elseif ($userLatCol && $userLngCol) $pinSelect = ", u.`$userLatCol` AS customer_lat, u.`$userLngCol` AS customer_lng";

$orderQuery = "SELECT 
    r.refill_id, r.order_number, r.total_amount, r.pickup_datetime, r.delivery_datetime, r.order_status, r.created_at, r.user_id, r.delay_reason,
    u.name as customer_name, u.phone_number as customer_phone, u.address as customer_address,
    rider.name as rider_name, rider.phone_number as rider_phone $addrSelect $pinSelect
FROM water_refills r
LEFT JOIN users u ON r.user_id = u.user_id
LEFT JOIN users rider ON r.rider_id = rider.user_id
WHERE r.order_number = :order_number LIMIT 1";
$stmt = $db->prepare($orderQuery);
$stmt->bindParam(':order_number', $orderNumber);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) { header('Location: customer_orders.php'); exit; }
// Use ORDER delivery address - same as Customer/Rider see
$deliveryAddress = !empty($order['order_delivery_address']) ? $order['order_delivery_address'] : $order['customer_address'];

$itemsQuery = "SELECT ri.product_id, ri.gallon_type, ri.gallon_size, ri.quantity, ri.unit_price, ri.subtotal, ri.gallon_owner, ri.processed_quantity, i.item_name
FROM water_refill_items ri LEFT JOIN inventory i ON ri.product_id = i.item_id WHERE ri.refill_id = :refill_id";
$itemsStmt = $db->prepare($itemsQuery);
$itemsStmt->bindParam(':refill_id', $order['refill_id']);
$itemsStmt->execute();
$orderItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

$stationGallons = array_filter($orderItems, function ($item) { return $item['gallon_owner'] === 'station'; });
$allGallonsProcessed = true;
foreach ($stationGallons as $item) {
    $processed = $item['processed_quantity'] ?? 0;
    if ($processed < $item['quantity']) { $allGallonsProcessed = false; break; }
}
$subtotal = array_sum(array_column($orderItems, 'subtotal'));
$taxRate = 0.00; $taxAmount = $subtotal * $taxRate; $deliveryFee = 0.00; $totalCost = $subtotal + $taxAmount + $deliveryFee;
function formatCurrency($amount) { return '₱' . number_format($amount, 2); }

$ridersQuery = "SELECT user_id, name, phone_number FROM users WHERE role = 'rider' AND status = 'active' ORDER BY name ASC";
$ridersStmt = $db->prepare($ridersQuery);
$ridersStmt->execute();
$availableRiders = $ridersStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RCH Water - Order Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.min.js"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; overflow-x: hidden; scrollbar-gutter: stable; }
        html { overflow-y: scroll; }
        body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown) { overflow-y: hidden !important; padding-right: 0 !important; }
        body.swal2-height-auto { height: 100% !important; }
        #delivery-map { position: relative; z-index: 1; }
        .page-container { height: 100vh; display: flex; flex-direction: column; overflow: hidden; }
        .content-wrapper { flex: 1; overflow-y: auto; overflow-x: hidden; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #9ca3af; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #6b7280; }
        .map-wrap { position: relative; }
        .leaflet-routing-container { display: none !important; }
        .leaflet-pane, .leaflet-top, .leaflet-bottom { z-index: 400 !important; }
        .leaflet-control { z-index: 400 !important; }
        /* ===== MAP MARKER STYLES - same as Customer / Rider ===== */
        .rider-pulse { position: relative; width: 36px; height: 36px; }
        .rider-pulse::before { content: ''; position: absolute; inset: 0; border-radius: 50%; background: rgba(16, 185, 129, 0.35); animation: riderPulse 1.8s ease-out infinite; }
        .rider-pulse .dot { position: absolute; inset: 0; border-radius: 50%; background: #10b981; border: 3px solid #fff; box-shadow: 0 2px 8px rgba(0, 0, 0, .35); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 15px; }
        @keyframes riderPulse { 0% { transform: scale(1); opacity: .9; } 100% { transform: scale(2.2); opacity: 0; } }
        .customer-pin { width: 30px; height: 30px; border-radius: 50%; background: #2563eb; border: 3px solid #fff; box-shadow: 0 2px 8px rgba(0, 0, 0, .3); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 13px; }
        .station-pin { background: #f59e0b; border: 3px solid #fff; border-radius: 50%; width: 18px; height: 18px; box-shadow: 0 0 0 6px rgba(245,158,11,.25); }
    </style>
</head>
<body class="bg-gray-50">
    <!-- SIDEBAR: same sidebar.js as order_list. NO per-page override. -->
    <!-- FIX vs before: wrapper now has "group" so group-hover works, -->
    <!-- and main slides to ml- — sidebar no longer covers content. -->
    <div class="group flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <script src="../Admin/includes/sidebar.js"></script>

        <!-- Main Content -->
        <div class="flex-1 ml-20 group-hover:ml- transition-all duration-500 ease-[cubic-bezier(0.4,0,0.2,1)] overflow-hidden">
            <div class="page-container">
                <?php $pageTitle = "Order Management"; $pageSubtitle = "View and manage customer orders and details."; include '../Admin/includes/header.php'; ?>
                <div class="content-wrapper p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <button onclick="window.location.href='../Order/orders_list.php'" class="flex items-center text-blue-600 hover:text-blue-700 text-2xl transition-colors"><i class="bi bi-arrow-left-circle-fill"></i></button>
                            <h1 class="text-2xl text-gray-700 font-semibold">Order Details - <?= htmlspecialchars($order['order_number']) ?></h1>
                        </div>
                        <div class="flex gap-3">
                            <?php if (!$allGallonsProcessed && $order['order_status'] !== 'cancelled'): ?>
                                <a href="process_order.php?order_number=<?= urlencode($orderNumber) ?>" class="flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-medium transition shadow-md"><i class="fas fa-qrcode"></i> Process Order</a>
                            <?php endif; ?>
                            <?php if ($allGallonsProcessed && $order['order_status'] !== 'cancelled'): ?>
                                <button onclick="openAssignRiderModal()" class="flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition shadow-md"><i class="fas fa-user-plus"></i> <?= $order['rider_name'] ? 'Reassign Rider' : 'Assign Rider' ?></button>
                            <?php endif; ?>
                            <?php if ($order['order_status'] !== 'cancelled' && $order['order_status'] !== 'delivered'): ?>
                                <button onclick="openRescheduleModal()" class="flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg font-medium transition shadow-md"><i class="fas fa-calendar-alt"></i> Reschedule</button>
                            <?php endif; ?>
                            <?php if ($order['order_status'] !== 'cancelled' && $order['order_status'] !== 'delivered'): ?>
                                <button id="cancelOrderBtn" class="flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-medium transition shadow-md"><i class="fas fa-times-circle"></i> Cancel Order</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="lg:col-span-2 space-y-6">
                            <div class="bg-white border rounded-xl shadow-sm p-5">
                                <div class="flex items-center gap-2 text-blue-600 font-semibold mb-3"><i class="fas fa-location-arrow"></i> Real-Time Rider Location</div>
                                <!-- MAP ONLY: clean map, no pills / no dark box / no legend - same as Customer -->
                                <div class="map-wrap">
                                    <div id="delivery-map" class="w-full h-80 rounded-xl border border-gray-200"></div>
                                </div>
                                <div class="border rounded-xl p-4 bg-gray-50 mt-4">
                                    <div class="font-semibold text-gray-700 mb-1">Rider Information</div>
                                    <?php if ($order['rider_name']): ?>
                                        <div class="flex justify-between items-center text-sm"><span class="font-medium"><?= htmlspecialchars($order['rider_name']) ?></span><span><i class="fa fa-phone mr-1"></i><?= htmlspecialchars($order['rider_phone']) ?></span></div>
                                        <div class="text-xs text-gray-500 mt-1" id="riderUpdated">Waiting for rider location…</div>
                                    <?php else: ?>
                                        <div class="text-sm text-gray-500 italic">No rider assigned yet</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="bg-white border rounded-xl shadow-sm p-5">
                                <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2 flex items-center gap-2"><i class="fas fa-shopping-cart text-blue-600"></i> Order Items</h3>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead><tr class="text-gray-600 border-b"><th class="text-left pb-2 px-2">Product</th><th class="text-center pb-2 px-2">Owner</th><th class="text-center pb-2 px-2">Qty</th><th class="text-right pb-2 px-2">Unit</th><th class="text-right pb-2 px-2">Total</th></tr></thead>
                                        <tbody>
                                            <?php foreach ($orderItems as $i => $item): ?>
                                                <tr class="<?= $i < count($orderItems) - 1 ? 'border-b' : '' ?>">
                                                    <td class="py-3 px-2"><?= htmlspecialchars($item['item_name'] ?: ($item['gallon_type'] . ' - ' . $item['gallon_size'])) ?></td>
                                                    <td class="text-center px-2"><span class="inline-block px-2 py-1 text-xs font-semibold rounded-full <?= $item['gallon_owner'] === 'station' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' ?>"><?= ucfirst($item['gallon_owner']) ?></span></td>
                                                    <td class="text-center font-semibold px-2"><?= $item['quantity'] ?></td>
                                                    <td class="text-right px-2"><?= formatCurrency($item['unit_price']) ?></td>
                                                    <td class="text-right font-semibold px-2"><?= formatCurrency($item['subtotal']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-4 text-sm space-y-2 pt-4 border-t">
                                    <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span><?= formatCurrency($subtotal) ?></span></div>
                                    <div class="flex justify-between"><span class="text-gray-500">Tax (<?= $taxRate * 100 ?>%)</span><span><?= formatCurrency($taxAmount) ?></span></div>
                                    <div class="flex justify-between"><span class="text-gray-500">Delivery Fee</span><span><?= formatCurrency($deliveryFee) ?></span></div>
                                    <div class="flex justify-between pt-3 text-lg font-semibold border-t"><span>Total Cost</span><span class="text-blue-600"><?= formatCurrency($order['total_amount']) ?></span></div>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-6">
                            <div class="bg-white border rounded-xl shadow-sm p-5">
                                <div class="flex items-center gap-2 text-blue-600 font-semibold mb-4 border-b pb-2"><i class="fas fa-map-marker-alt text-lg"></i> Delivery Information</div>
                                <div class="space-y-3">
                                    <div><div class="text-xs text-gray-500 mb-1">Customer Name</div><div class="font-semibold text-gray-800"><?= htmlspecialchars($order['customer_name']) ?></div></div>
                                    <div><div class="text-xs text-gray-500 mb-1">Contact Number</div><div class="font-medium text-gray-700"><?= htmlspecialchars($order['customer_phone']) ?></div></div>
                                    <div><div class="text-xs text-gray-500 mb-1">Delivery Address</div><div class="text-sm text-gray-700"><?= htmlspecialchars($deliveryAddress) ?></div></div>
                                </div>
                            </div>
                            <div class="bg-white border rounded-xl shadow-sm p-5">
                                <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2 flex items-center gap-2"><i class="fas fa-info-circle text-blue-600"></i> Order Metadata</h3>
                                <div class="space-y-3 text-sm">
                                    <div class="flex justify-between items-center py-2 border-b"><span class="text-gray-500">Order Number</span><span class="font-semibold text-gray-800"><?= htmlspecialchars($order['order_number']) ?></span></div>
                                    <div class="flex justify-between items-center py-2 border-b"><span class="text-gray-500">Payment Method</span><span class="font-medium text-gray-700">Cash on Delivery</span></div>
                                    <div class="flex justify-between items-center py-2 border-b"><span class="text-gray-500">Order Placed</span><span class="font-medium text-gray-700"><?= date('M d, Y | H:i', strtotime($order['created_at'])) ?></span></div>
                                    <div class="flex justify-between items-center py-2 border-b"><span class="text-gray-500">Pickup Time</span><span class="font-medium text-gray-700"><?= date('M d, Y | H:i', strtotime($order['pickup_datetime'])) ?></span></div>
                                    <div class="flex justify-between items-center py-2"><span class="text-gray-500">Delivery Time</span><span class="font-medium text-gray-700"><?= date('M d, Y | H:i', strtotime($order['delivery_datetime'])) ?></span></div>
                                </div>
                            </div>
                            <div class="bg-white border rounded-xl shadow-sm p-5">
                                <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2 flex items-center gap-2"><i class="fas fa-tasks text-blue-600"></i> Order Status</h3>
                                <div class="space-y-3">
                                    <?php
                                    $statuses = ['pending'=>'Order Pending','processed'=>'Order Processed','assigned'=>'Rider Assigned','pickup_in_progress'=>'Pickup In Progress','picked_up'=>'Picked Up','on_delivery'=>'On Delivery','delivered'=>'Delivered'];
                                    $currentStatus = $order['order_status']; $statusKeys = array_keys($statuses); $isProcessed = $allGallonsProcessed; $currentIndex = array_search($currentStatus, $statusKeys);
                                    foreach ($statuses as $key => $label):
                                        $keyIndex = array_search($key, $statusKeys);
                                        if ($key === 'processed') { $isCompleted = $isProcessed; $isCurrent = $isProcessed && $currentStatus === 'pending'; }
                                        else { $isCompleted = $keyIndex <= $currentIndex; $isCurrent = $key === $currentStatus; }
                                    ?>
                                        <div class="flex items-center gap-3"><div class="w-2 h-2 rounded-full <?= $isCompleted ? 'bg-green-500' : ($isCurrent ? 'bg-blue-500 animate-pulse' : 'bg-gray-300') ?>"></div><span class="text-sm <?= $isCurrent ? 'font-semibold text-gray-700' : ($isCompleted ? 'text-gray-700' : 'text-gray-400') ?>"><?= $label ?></span></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ---------- Helper: fetch JSON safely, show REAL error, never fake "check connection" ----------
        async function fetchJson(url, options) {
            const res = await fetch(url, options);
            const text = await res.text();
            try { return JSON.parse(text); }
            catch (e) { throw new Error('Server returned HTTP ' + res.status + ' - not JSON. Response: ' + text.substring(0, 300)); }
        }
        function openAssignRiderModal() {
            const riders = <?= json_encode($availableRiders) ?>;
            if (riders.length === 0) { Swal.fire({ icon: 'warning', title: 'No Riders Available', text: 'There are no active riders to assign.', confirmButtonColor: '#3b82f6' }); return; }
            let riderOptions = '<select id="riderSelect" class="swal2-input" style="width: 90%; padding: 10px;">';
            riderOptions += '<option value="">-- Select a Rider --</option>';
            riders.forEach(rider => { riderOptions += `<option value="${rider.user_id}">${rider.name} - ${rider.phone_number}</option>`; });
            riderOptions += '</select>';
            Swal.fire({
                title: 'Assign Rider',
                html: `<div style="text-align: left; padding: 10px;"><label style="font-weight: 500; margin-bottom: 8px; display: block;">Select Rider:</label>${riderOptions}</div>`,
                icon: 'info', showCancelButton: true, confirmButtonText: 'Assign', cancelButtonText: 'Cancel',
                confirmButtonColor: '#3b82f6', cancelButtonColor: '#6b7280',
                preConfirm: () => { const riderId = document.getElementById('riderSelect').value; if (!riderId) { Swal.showValidationMessage('Please select a rider'); return false; } return riderId; }
            }).then((result) => { if (result.isConfirmed) { assignRider(result.value); } });
        }
        function assignRider(riderId) {
            fetchJson('assign_rider.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ order_number: '<?= $orderNumber ?>', rider_id: riderId }) })
                .then(data => {
                    if (data.success) { Swal.fire({ icon: 'success', title: 'Rider Assigned!', text: 'The rider has been successfully assigned to this order.', confirmButtonColor: '#3b82f6' }).then(() => { location.reload(); }); }
                    else { Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to assign rider.', confirmButtonColor: '#dc2626' }); }
                })
                .catch(err => { console.error(err); Swal.fire({ icon: 'error', title: 'Error', text: 'Real error: ' + err.message, confirmButtonColor: '#dc2626' }); });
        }
        function cancelOrder() {
            Swal.fire({
                title: 'Cancel Order?',
                html: `<p style="margin-bottom: 15px;">Are you sure you want to cancel this order? This action cannot be undone.</p><label style="font-weight: 500; margin-bottom: 8px; display: block; text-align: left;">Reason for Cancellation:</label><textarea id="cancelReason" class="swal2-textarea" placeholder="Enter reason..." style="width: 87%; height: 100px; font-size: 1rem;"></textarea>`,
                icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, Cancel Order', cancelButtonText: 'No, Keep Order',
                confirmButtonColor: '#dc2626', cancelButtonColor: '#6b7280',
                preConfirm: () => { const reason = document.getElementById('cancelReason').value.trim(); if (!reason) { Swal.showValidationMessage('Please provide a reason for cancellation'); return false; } return reason; }
            }).then((result) => { if (result.isConfirmed) { performCancelOrder(result.value); } });
        }
        function performCancelOrder(reason) {
            fetchJson('admin_cancel_order.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ order_number: '<?= $orderNumber ?>', reason: reason }) })
                .then(data => {
                    if (data.success) { Swal.fire({ icon: 'success', title: 'Order Cancelled', text: 'The order has been successfully cancelled.', confirmButtonColor: '#3b82f6' }).then(() => { window.location.href = 'orders_list.php'; }); }
                    else { Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to cancel order.', confirmButtonColor: '#dc2626' }); }
                })
                .catch(err => { console.error(err); Swal.fire({ icon: 'error', title: 'Error', text: 'Real error: ' + err.message, confirmButtonColor: '#dc2626' }); });
        }
        function openRescheduleModal() {
            Swal.fire({
                title: 'Reschedule Order',
                html: ` <div style="text-align: left; display: flex; flex-direction: column; gap: 8px; font-size: 0.875rem;"> <label for="rescheduleType" style="font-weight: 500;">Reschedule Type:</label> <select id="rescheduleType" class="swal2-input" style="width: 100%; padding: 6px; font-size: 0.875rem; color: #3b82f6; border: 1px solid #d1d5db; border-radius: 4px;"><option value="">-- Select Type --</option><option value="pickup">Pickup Date & Time</option><option value="delivery">Delivery Date & Time</option></select>
            <label for="newDate" style="font-weight: 500;">New Date:</label><input type="date" id="newDate" class="swal2-input" style="width: 87%; padding: 6px; font-size: 0.875rem;" min="${new Date().toISOString().split('T')[0]}">
            <label for="timeSlot" style="font-weight: 500;">Time Slot:</label><select id="timeSlot" class="swal2-input" style="width: 100%; padding: 6px; font-size: 0.875rem; color: #3b82f6; border: 1px solid #d1d5db; border-radius: 4px;" disabled><option value="">-- Select time slot first --</option></select>
            <label for="rescheduleReason" style="font-weight: 500;">Reason for Rescheduling:</label><textarea id="rescheduleReason" class="swal2-textarea" placeholder="Enter reason for rescheduling..." style="width: 87%; padding: 6px; font-size: 0.875rem; height: 90px; resize: vertical;"></textarea></div>`,
                icon: 'question', showCancelButton: true, confirmButtonText: 'Reschedule', cancelButtonText: 'Cancel',
                confirmButtonColor: '#f59e0b', cancelButtonColor: '#6b7280',
                didOpen: () => {
                    const rescheduleType = document.getElementById('rescheduleType');
                    const timeSlotSelect = document.getElementById('timeSlot');
                    rescheduleType.addEventListener('change', function() {
                        const type = this.value; timeSlotSelect.disabled = false; timeSlotSelect.innerHTML = '<option value="">-- Select Time Slot --</option>';
                        if (type === 'pickup') { timeSlotSelect.innerHTML += `<option value="morning">Morning - 7:30 AM</option><option value="afternoon">Afternoon - 1:00 PM</option>`; }
                        else if (type === 'delivery') { timeSlotSelect.innerHTML += `<option value="morning_early">Morning - 9:00 AM</option><option value="morning_late">Morning - 11:00 AM</option><option value="afternoon_early">Afternoon - 2:00 PM</option><option value="afternoon_late">Afternoon - 4:00 PM</option>`; }
                    });
                },
                preConfirm: () => {
                    const rescheduleTypeVal = document.getElementById('rescheduleType').value;
                    const newDateVal = document.getElementById('newDate').value;
                    const timeSlotVal = document.getElementById('timeSlot').value;
                    const reasonVal = document.getElementById('rescheduleReason').value.trim();
                    if (!rescheduleTypeVal) return Swal.showValidationMessage('Please select reschedule type');
                    if (!newDateVal) return Swal.showValidationMessage('Please select a new date');
                    if (!timeSlotVal) return Swal.showValidationMessage('Please select a time slot');
                    if (!reasonVal) return Swal.showValidationMessage('Please provide a reason for rescheduling');
                    return { reschedule_type: rescheduleTypeVal, new_date: newDateVal, new_time_slot: timeSlotVal, reason: reasonVal };
                }
            }).then((result) => { if (result.isConfirmed) performReschedule(result.value); });
        }
        function performReschedule(data) {
            Swal.fire({ title: 'Rescheduling...', text: 'Please wait while we update the schedule.', allowOutsideClick: false, allowEscapeKey: false, didOpen: () => { Swal.showLoading(); } });
            fetchJson('reschedule_order.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ order_number: '<?= $orderNumber ?>', ...data }) })
                .then(data => {
                    if (data.success) { Swal.fire({ icon: 'success', title: 'Rescheduled Successfully!', text: data.message, confirmButtonColor: '#3b82f6' }).then(() => { location.reload(); }); }
                    else { Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to reschedule order.', confirmButtonColor: '#dc2626' }); }
                })
                .catch(err => { console.error(err); Swal.fire({ icon: 'error', title: 'Error', text: 'Real error: ' + err.message, confirmButtonColor: '#dc2626' }); });
        }
        document.addEventListener('DOMContentLoaded', function() {
            const cancelBtn = document.getElementById('cancelOrderBtn');
            if (cancelBtn) { cancelBtn.addEventListener('click', cancelOrder); }
        });
    </script>

    <!-- MAP ONLY: clean map - store + customer + rider live, no pills / no dark box / no legend / no blue line -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const orderNumber = "<?= htmlspecialchars($order['order_number']) ?>";
            const customerAddress = <?= json_encode($deliveryAddress) ?>;
            const savedCustomerLat = <?= isset($order['customer_lat']) && $order['customer_lat'] !== null ? (float)$order['customer_lat'] : 'null' ?>;
            const savedCustomerLng = <?= isset($order['customer_lng']) && $order['customer_lng'] !== null ? (float)$order['customer_lng'] : 'null' ?>;
            const hasRider = <?= $order['rider_name'] ? 'true' : 'false' ?>;
            const STATION = { lat: 13.9411, lng: 121.1628 };
            let customerPos = null, customerResolved = false, riderPos = null, riderMarker = null, routeControl = null, firstFit = true;

            const map = L.map('delivery-map').setView([STATION.lat, STATION.lng], 13);
            window.adminMap = map;
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, minZoom: 10, attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>' }).addTo(map);
            setTimeout(() => map.invalidateSize(), 500);

            // Same icons as Customer / Rider
            const stationIcon = L.divIcon({ className: '', html: '<div class="station-pin"></div>', iconSize: [18, 18], iconAnchor: [9, 9] });
            const customerIcon = L.divIcon({ className: '', html: '<div class="customer-pin"><i class="bi bi-house-fill"></i></div>', iconSize: [30, 30], iconAnchor: [15, 15], popupAnchor: [0, -15] });
            const riderIcon = L.divIcon({ className: '', html: '<div class="rider-pulse"><div class="dot"><i class="bi bi-truck"></i></div></div>', iconSize: [36, 36], iconAnchor: [18, 18], popupAnchor: [0, -18] });

            // 1) STORE - always visible
            L.marker([STATION.lat, STATION.lng], { icon: stationIcon }).addTo(map).bindPopup(`<div style="text-align:center;min-width:150px;"><strong style="color:#2563eb;font-size:14px;">🏢 RCH Water Station</strong></div>`);
            // 2) CUSTOMER - draggable so admin can fix exact house
            const customerMarker = L.marker([STATION.lat, STATION.lng], { icon: customerIcon, draggable: true }).addTo(map).bindPopup(`<div style="text-align:center;min-width:180px;"><strong style="color:#16a34a;font-size:14px;">📍 Customer Location</strong><br><small style="color:#6b7280;"><?= htmlspecialchars($deliveryAddress) ?></small><br><small style="color:#2563eb;">Drag this pin to the exact house to fix it</small></div>`);
            customerMarker.setOpacity(0);

            // Drag to correct - saves to DB, next orders reuse it
            customerMarker.on('dragend', async () => {
                const p = customerMarker.getLatLng();
                const ok = await Swal.fire({ title: 'Save this as correct house location?', text: p.lat.toFixed(6) + ', ' + p.lng.toFixed(6), icon: 'question', showCancelButton: true, confirmButtonText: 'Yes, Save', confirmButtonColor: '#2563eb' });
                if (!ok.isConfirmed) { if (customerPos) customerMarker.setLatLng([customerPos.lat, customerPos.lng]); return; }
                try {
                    const fd = new FormData(); fd.append('action', 'save_customer_pin'); fd.append('order_number', orderNumber); fd.append('latitude', p.lat); fd.append('longitude', p.lng);
                    const res = await fetch('order_details.php', { method: 'POST', body: fd });
                    const d = await res.json();
                    if (d.success) { customerPos = { lat: p.lat, lng: p.lng }; customerResolved = true; Swal.fire({ icon: 'success', title: 'Saved!', text: 'Next orders for this customer will use this exact pin.', confirmButtonColor: '#3b82f6' }); if (hasRider && riderPos) drawRoute(riderPos, customerPos); }
                    else Swal.fire({ icon: 'error', title: 'Error', text: d.message || 'Failed to save pin' });
                } catch (e) { Swal.fire({ icon: 'error', title: 'Error', text: 'Real error: ' + e.message }); }
            });

            function samePoint(a, b) { if (!a || !b) return false; return Math.abs(a.lat - b.lat) < 0.0001 && Math.abs(a.lng - b.lng) < 0.0001; }
            // Route is ONLY rider -> customer. No station -> customer blue line.
            function drawRoute(from, to) {
                if (!from || !to || samePoint(from, to)) return;
                if (routeControl) { map.removeControl(routeControl); routeControl = null; }
                routeControl = L.Routing.control({ waypoints: [L.latLng(from.lat, from.lng), L.latLng(to.lat, to.lng)], lineOptions: { styles: [{ color: '#f59e0b', weight: 5, opacity: 0.9 }] }, createMarker: () => null, addWaypoints: false, routeWhileDragging: false, show: false, fitSelectedRoutes: false }).addTo(map);
            }
            function applyCustomerPos(pos, source) {
                if (!pos) return;
                if (samePoint(pos, STATION) && source !== 'exact_pin') return;
                customerPos = pos; customerResolved = true; customerMarker.setLatLng([pos.lat, pos.lng]); customerMarker.setOpacity(1);
                // REMOVED: no station -> customer blue dashed line
                if (hasRider && riderPos) drawRoute(riderPos, customerPos);
                if (firstFit) { map.fitBounds(L.latLngBounds([L.latLng(STATION.lat, STATION.lng), L.latLng(customerPos.lat, customerPos.lng)]).pad(0.25)); firstFit = false; }
            }
            // *** EXACT COPY of Customer/Rider geocode - same attempts, same order ***
            async function geocodeClientSide() {
                if (!customerAddress) return false;
                const full = customerAddress.trim();
                const attempts = [];
                attempts.push(full + ', Philippines');
                const noHouseNo = full.replace(/^\s*\d+\s*,?\s*/, '');
                if (noHouseNo && noHouseNo !== full) attempts.push(noHouseNo + ', Philippines');
                const parts = full.split(',').map(s => s.trim()).filter(Boolean);
                if (parts.length >= 3) {
                    const barangayCity = parts.slice(-3).join(', ');
                    if (!attempts.includes(barangayCity + ', Philippines')) attempts.push(barangayCity + ', Philippines');
                }
                if (parts.length >= 2) {
                    const cityOnly = parts.slice(-2).join(', ');
                    if (!attempts.includes(cityOnly + ', Philippines')) attempts.push(cityOnly + ', Philippines');
                }
                attempts.push('Lipa City, Batangas, Philippines');
                for (const q of attempts) {
                    try {
                        const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(q)}`, { headers: { 'Accept': 'application/json' } });
                        const data = await res.json();
                        if (data && data[0]) { applyCustomerPos({ lat: parseFloat(data[0].lat), lng: parseFloat(data[0].lon) }, 'geocoded'); return true; }
                    } catch(e){ console.warn('Geocode failed for', q, e); }
                    await new Promise(r => setTimeout(r, 300));
                }
                return false;
            }
            async function loadCustomer() {
                // 1) Exact pin - instant
                if (savedCustomerLat !== null && savedCustomerLng !== null && savedCustomerLat !== 0) {
                    applyCustomerPos({ lat: savedCustomerLat, lng: savedCustomerLng }, 'exact_pin');
                    return;
                }
                // 2) Server endpoint - exact pin or server geocode (same order address)
                try {
                    const res = await fetch(`order_details.php?action=customer_location&order_number=${encodeURIComponent(orderNumber)}`);
                    const d = await res.json();
                    if (d.success) { applyCustomerPos({ lat: d.latitude, lng: d.longitude }, d.status || 'saved'); return; }
                } catch (e) {}
                // 3) Client geocode - same as Customer/Rider
                const ok = await geocodeClientSide();
                if (!ok && firstFit) { map.setView([STATION.lat, STATION.lng], 13); firstFit = false; }
            }
            async function fetchRider() {
                if (!hasRider) return;
                try {
                    const res = await fetch(`order_details.php?action=rider_location&order_number=${encodeURIComponent(orderNumber)}`);
                    const d = await res.json();
                    if (!d.success) { return; }
                    riderPos = { lat: d.latitude, lng: d.longitude };
                    const el = document.getElementById('riderUpdated'); if (el && d.timestamp) el.textContent = 'Updated: ' + new Date(d.timestamp.replace(' ', 'T')).toLocaleTimeString();
                    if (!riderMarker) {
                        riderMarker = L.marker([riderPos.lat, riderPos.lng], { icon: riderIcon, zIndexOffset: 1000 }).addTo(map).bindPopup(`<div style="text-align:center;min-width:160px;"><strong style="color:#f59e0b;font-size:14px;">🏍 Rider</strong><br><small style="font-weight:600;"><?= htmlspecialchars($order['rider_name'] ?? 'Rider') ?></small><br><small style="color:#6b7280;"><?= htmlspecialchars($order['rider_phone'] ?? '') ?></small></div>`);
                        if (customerResolved) map.fitBounds(L.latLngBounds([L.latLng(riderPos.lat, riderPos.lng), L.latLng(customerPos.lat, customerPos.lng)]).pad(0.25));
                    } else { const from = riderMarker.getLatLng(), to = L.latLng(riderPos.lat, riderPos.lng); let i = 0; const anim = setInterval(() => { i++; riderMarker.setLatLng([from.lat + (to.lat - from.lat) * i / 20, from.lng + (to.lng - from.lng) * i / 20]); if (i >= 20) clearInterval(anim); }, 40); }
                    if (customerResolved) drawRoute(riderPos, customerPos);
                } catch (e) { console.error(e); }
            }
            loadCustomer();
            if (hasRider) { fetchRider(); setInterval(fetchRider, 5000); }
        });
    </script>
</body>
</html>