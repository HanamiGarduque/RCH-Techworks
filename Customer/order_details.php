<?php
// order_details.php

session_start();
require_once '../Database/db_connection.php';
$db = new Database();
$conn = $db->getConnect();

// ============================================================================
// 1. GET STATUS FROM URL
// ============================================================================
$rawStatus = isset($_GET['status']) ? strtolower($_GET['status']) : 'pending';

switch ($rawStatus) {
    case 'pending':
        $orderStatus = "Pending";
        break;
    case 'on_delivery':
        $orderStatus = "On Delivery";
        break;
    case 'completed':
        $orderStatus = "Delivered";
        break;
    default:
        $orderStatus = "Cancelled";
        break;
}

// ============================================================================
// 2. PAGE INFORMATION
// ============================================================================
$pageTitle = "My Orders";
$pageSubtitle = "View and track your orders and deliveries in real time.";
$currentPage = 'order_details.php';

// Default customer & order info (if DB fetch fails)
$customerName = "Christine May Padua";
$customerPhone = "09161891555";
$deliveryAddress = "116 Purok 1, Tibig, Lipa City, Batangas 4217";
$orderId = "ORD-110325001";
$paymentMethod = "Cash on Delivery";
$orderDateTime = "03-11-2025 | 8:38";
$shipDateTime = "05-11-2025 | 11:00";

$orderItems = [
    ['name' => '20L Slim Container Gallon', 'qty' => 2, 'unitPrice' => 170.00, 'totalPrice' => 340.00],
    ['name' => 'Slim Gallon Faucet - Press', 'qty' => 10, 'unitPrice' => 25.00, 'totalPrice' => 250.00]
];
$subtotal = array_sum(array_column($orderItems, 'totalPrice'));
$taxRate = 0.08;
$taxAmount = $subtotal * $taxRate;
$deliveryFee = 40.00;
$totalCost = $subtotal + $taxAmount + $deliveryFee;

function formatCurrency($amount)
{
    return '₱' . number_format($amount, 2);
}

// ============================================================================
// 3. STATUS COLORS & BUTTON LOGIC
// ============================================================================
$statusColor = '';
$buttonText = '';
$buttonClass = '';

if ($orderStatus === 'Pending') {
    $statusColor = 'text-yellow-600 font-semibold';
    $buttonText = "Back";
    $buttonClass = "bg-blue-600 hover:bg-blue-700";
} elseif ($orderStatus === 'On Delivery') {
    $statusColor = 'text-blue-600 font-semibold';
    $buttonText = "Back";
    $buttonClass = "bg-blue-600 hover:bg-blue-700";
} elseif ($orderStatus === 'Delivered') {
    $statusColor = 'text-green-600 font-semibold';
    $buttonText = "Back";
    $buttonClass = "bg-blue-600 hover:bg-blue-700";
} else {
    $statusColor = 'text-red-600 font-semibold';
    $buttonText = "Back";
    $buttonClass = "bg-blue-600 hover:bg-blue-700";
}

// ============================================================================
// 4. MAP / LOCATION HANDLING
//    - Hardcode customer lat/lng
//    - Fetch rider location by AJAX via loc_history
// ============================================================================
$refill_id = isset($_GET['refill_id']) ? intval($_GET['refill_id']) : null;

$customerLat = 13.9587;   // Tibig, Lipa City (hardcoded) :contentReference[oaicite:1]{index=1}
$customerLng = 121.1444;

$rider_user_id = null;
if ($refill_id) {
    $qr = $conn->prepare("SELECT rider_id, order_number, user_id FROM water_refills WHERE refill_id = :rid LIMIT 1");
    $qr->bindParam(':rid', $refill_id, PDO::PARAM_INT);
    $qr->execute();
    if ($row = $qr->fetch(PDO::FETCH_ASSOC)) {
        $rider_user_id = $row['rider_id'];
        $orderId = $row['order_number'] ?: $orderId;
    }
}

// AJAX endpoint for rider location
if (isset($_GET['action']) && $_GET['action'] === 'rider_location' && $rider_user_id) {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $s = $conn->prepare("SELECT latitude, longitude, created_at FROM loc_history WHERE user_id = :rid ORDER BY created_at DESC LIMIT 1");
        $s->bindParam(':rid', $rider_user_id, PDO::PARAM_INT);
        $s->execute();
        $loc = $s->fetch(PDO::FETCH_ASSOC);
        if ($loc && $loc['latitude'] !== null && $loc['longitude'] !== null) {
            echo json_encode([
                'latitude' => (float)$loc['latitude'],
                'longitude' => (float)$loc['longitude'],
                'timestamp' => $loc['created_at']
            ]);
        } else {
            echo json_encode(['error' => 'No rider location found']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

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

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            padding-left: 5rem;
            transition: .5s;
        }

        /* Make the map always stay UNDER the sidebar */
        #rider-map,
        #rider-map * {
            z-index: 0 !important;
        }

        .group {
            z-index: 9999 !important;
            /* Sidebar stays on top */


        }

        .content-area {
            min-height: 100vh;
        }

        #rider-map {
            width: 100%;
            height: 300px;
            border-radius: 0.75rem;
            overflow: hidden;
        }
    </style>
</head>

<body class="bg-gray-100">

    <!-- SIDEBAR -->
    <script>
        const currentPage = '<?= $currentPage ?>';

        function createNavItem(href, icon, label, fileName) {
            const isActive = currentPage === fileName;
            const base = "flex items-center px-4 py-3 rounded-lg font-medium transition-all duration-200 ";
            const active = "bg-white/20 text-white";
            const inactive = "text-white hover:bg-white/20";
            return `
            <li>
                <a href="${href}" class="${base} ${isActive ? active : inactive}">
                    <i class="${icon} text-lg"></i>
                    <div class="overflow-hidden ml-3">
                        <span class="block whitespace-nowrap opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">${label}</span>
                    </div>
                </a>
            </li>`;
        }

        const sidebarHTML = `
<aside class="group bg-gradient-to-br from-blue-400 to-blue-600 absolute left-0 top-0 h-full w-20 hover:w-64 transition-all duration-500 text-white flex flex-col z-50">
    <div class="px-7 py-7 border-b border-blue-400 flex items-center gap-2">
        <i class="fas fa-tint text-3xl"></i>
        <span class="text-2xl font-semibold opacity-0 group-hover:opacity-100 translate-x-[-20px] group-hover:translate-x-0">RCH Water</span>
    </div>
    <nav class="mt-6 flex-1">
        <ul class="space-y-1 px-4">
            ${createNavItem("../Customer/order_water_refill.php", "fas fa-tint", "Order Water Refill", "order_water_refill.php")}
            ${createNavItem("../Customer/browse_products.php", "fas fa-shopping-basket", "Browse Products", "browse_products.php")}
            ${createNavItem("../Customer/my_orders.php", "fas fa-box", "My Orders", "my_orders.php")}
        </ul>
    </nav>
    <div class="border-t border-blue-400 p-4">
        <a href="#" class="flex items-center gap-3 hover:bg-white/20 px-4 py-3 rounded-lg">
            <i class="fas fa-sign-out-alt text-lg"></i>
            <span class="opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">Logout</span>
        </a>
    </div>
</aside>`;

        document.addEventListener("DOMContentLoaded", () => {
            document.body.insertAdjacentHTML("afterbegin", sidebarHTML);
        });
    </script>

    <!-- MAIN CONTENT -->
    <div class="content-area">
        <main class="bg-white rounded-xl shadow-lg h-[calc(100vh-2rem)] overflow-y-auto">

            <div class="bg-white border-b p-4 shadow-sm">
                <h1 class="text-3xl font-semibold text-blue-500"><?= $pageTitle ?></h1>
                <p class="text-sm text-gray-700"><?= $pageSubtitle ?></p>
            </div>

            <div class="p-8">
                <div class="bg-white w-full mx-auto rounded-2xl shadow-sm border p-8">

                    <div class="flex items-center gap-3 mb-6">
                        <button onclick="window.history.back()" class="flex items-center text-blue-600 hover:text-blue-700 text-2xl">
                            <i class="bi bi-arrow-left-circle-fill"></i>
                        </button>
                        <h1 class="text-2xl text-gray-700 font-semibold">Order Details</h1>
                    </div>

                    <div class="border rounded-xl p-5 mb-6">
                        <div class="flex items-center gap-2 text-blue-600 font-semibold mb-3">
                            <i class="fas fa-location-arrow"></i> Real-Time Rider Location
                        </div>

                        <div id="rider-map" class="bg-gray-50 flex items-center justify-center text-gray-600 mb-4">
                            <!-- Leaflet map renders here -->
                        </div>

                        <div class="border rounded-xl p-4 bg-gray-50">
                            <div class="font-semibold text-gray-700 mb-1">Rider Information</div>
                            <div class="flex justify-between items-center text-sm">
                                <span id="rider-name">Rider</span>
                                <span><i class="fa fa-phone mr-1"></i><span id="rider-phone">—</span></span>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded-xl p-5 mb-6">
                        <div class="flex items-center gap-2 text-blue-600 font-medium mb-2">
                            <i class="fas fa-map-marker-alt text-lg"></i> Delivery Information
                        </div>
                        <div class="flex justify-between text-sm">
                            <div class="font-semibold">
                                <?= htmlspecialchars($customerName) ?><br>
                                <span class="font-normal"><?= htmlspecialchars($customerPhone) ?></span>
                            </div>
                            <div class="text-right max-w-xs"><?= htmlspecialchars($deliveryAddress) ?></div>
                        </div>
                    </div>

                    <div class="border rounded-xl p-5 mb-6">
                        <p class="<?= $statusColor ?> text-sm font-semibold"><?= $orderStatus ?></p>

                        <table class="w-full text-sm mt-3">
                            <thead>
                                <tr class="text-gray-600">
                                    <th class="text-left pb-2">Product</th>
                                    <th class="text-center pb-2">Qty</th>
                                    <th class="text-right pb-2">Unit</th>
                                    <th class="text-right pb-2">Total</th>
                                </tr>
                            </thead>
                            <tbody class="border-t">
                                <?php foreach ($orderItems as $i => $item): ?>
                                    <tr class="<?= $i < count($orderItems) - 1 ? 'border-b' : '' ?>">
                                        <td class="py-3"><?= htmlspecialchars($item['name']) ?></td>
                                        <td class="text-center font-semibold"><?= intval($item['qty']) ?></td>
                                        <td class="text-right"><?= formatCurrency($item['unitPrice']) ?></td>
                                        <td class="text-right"><?= formatCurrency($item['totalPrice']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div class="mt-4 text-sm space-y-2">
                            <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span><?= formatCurrency($subtotal) ?></span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Tax (<?= $taxRate * 100 ?>%)</span><span><?= formatCurrency($taxAmount) ?></span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Delivery Fee</span><span><?= formatCurrency($deliveryFee) ?></span></div>
                            <div class="flex justify-between pt-3 text-lg font-semibold">
                                <span>Total Cost</span><span class="text-blue-600"><?= formatCurrency($totalCost) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded-xl p-5">
                        <div class="flex justify-between text-sm mb-2">
                            <span class="font-semibold">Order ID</span>
                            <span><?= htmlspecialchars($orderId) ?></span>
                        </div>
                        <div class="flex justify-between text-sm border-b pb-3 mb-3">
                            <span class="font-normal">Paid by</span>
                            <span><?= htmlspecialchars($paymentMethod) ?></span>
                        </div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-500">Order Date</span>
                            <span><?= htmlspecialchars($orderDateTime) ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Ship Date</span>
                            <span><?= htmlspecialchars($shipDateTime) ?></span>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    const refillId = <?= $refill_id ? intval($refill_id) : 'null' ?>;
    const customerLat = <?= json_encode($customerLat) ?>;
    const customerLng = <?= json_encode($customerLng) ?>;

    let map, riderMarker, customerMarker;

    function initMap() {
        map = L.map('rider-map').setView([customerLat, customerLng], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // CUSTOMER MARKER
        customerMarker = L.marker([customerLat, customerLng], {
            title: 'Delivery Destination'
        }).addTo(map);

        customerMarker.bindPopup(
            `<b>Delivery Address</b><br><?= addslashes($deliveryAddress) ?>`
        );

        // RIDER MARKER
        riderMarker = L.marker([customerLat, customerLng], {
            title: 'Rider (live)'
        }).addTo(map);

        riderMarker.bindPopup(`<b>Rider</b><br>Fetching location...`);

        fetchRiderLocation();
        setInterval(fetchRiderLocation, 5000);
    }

    async function fetchRiderLocation() {

        if (!refillId) return;

        try {
            const resp = await fetch(
                `order_details.php?refill_id=${refillId}&action=rider_location`,
                { cache: "no-store" }
            );

            const data = await resp.json();

            if (data.latitude && data.longitude) {
                const lat = parseFloat(data.latitude);
                const lng = parseFloat(data.longitude);

                riderMarker.setLatLng([lat, lng]);
            }
        } catch (err) {
            console.error("Rider location fetch error:", err);
        }
    }

    document.addEventListener('DOMContentLoaded', initMap);
</script>

</body>

</html>