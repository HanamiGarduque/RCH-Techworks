<?php

require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('customer'); // only customers can access

require_once '../Database/db_connection.php';

$database = new Database();
$db = $database->getConnect();

// Get order_number from URL parameter
$orderNumber = isset($_GET['order_number']) ? $_GET['order_number'] : null;

if (!$orderNumber) {
    header('Location: my_orders.php');
    exit;
}

// Get logged-in user ID from session
$userId = $_SESSION['user_id'];

// ============================================================================
// FETCH ORDER DETAILS FROM DATABASE
// ============================================================================

try {
    // Fetch main order information
    $orderQuery = "
        SELECT 
            wr.refill_id,
            wr.order_number,
            wr.total_amount,
            wr.pickup_datetime,
            wr.delivery_datetime,
            wr.order_status,
            wr.gallon_owner,
            wr.created_at,
            wr.delay_reason,
            u.name as customer_name,
            u.phone_number as customer_phone,
            u.address as delivery_address,
            r.name as rider_name,
            r.phone_number as rider_phone
        FROM water_refills wr
        INNER JOIN users u ON wr.user_id = u.user_id
        LEFT JOIN users r ON wr.rider_id = r.user_id
        WHERE wr.order_number = :order_number 
        AND wr.user_id = :user_id
    ";
    
    $stmt = $db->prepare($orderQuery);
    $stmt->bindParam(':order_number', $orderNumber);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        header('Location: my_orders.php');
        exit;
    }
    
    // Fetch order items
    $itemsQuery = "
        SELECT 
            wri.item_id,
            wri.gallon_type,
            wri.gallon_size,
            wri.quantity,
            wri.unit_price,
            wri.subtotal,
            p.product_name
        FROM water_refill_items wri
        LEFT JOIN products p ON wri.product_id = p.product_id
        WHERE wri.refill_id = :refill_id
    ";
    
    $itemsStmt = $db->prepare($itemsQuery);
    $itemsStmt->bindParam(':refill_id', $order['refill_id']);
    $itemsStmt->execute();
    
    $orderItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// ============================================================================
// PAGE INFORMATION
// ============================================================================
$pageTitle = "My Orders";
$pageSubtitle = "View and track your orders and deliveries in real time.";
$currentPage = 'order_details.php';

// Extract order data
$customerName = $order['customer_name'];
$customerPhone = $order['customer_phone'];
$deliveryAddress = $order['delivery_address'];
$orderId = $order['order_number'];
$orderStatus = $order['order_status'];
$orderDateTime = date('d-m-Y | H:i', strtotime($order['created_at']));
$shipDateTime = $order['delivery_datetime'] ? date('d-m-Y | H:i', strtotime($order['delivery_datetime'])) : 'N/A';
$riderName = $order['rider_name'] ?? 'Not Assigned';
$riderPhone = $order['rider_phone'] ?? 'N/A';
$paymentMethod = "Cash on Delivery"; // Add payment method field to DB if needed

// Calculate totals
$subtotal = 0;
foreach ($orderItems as $item) {
    $subtotal += $item['subtotal'];
}

$taxRate = 0.08;
$taxAmount = $subtotal * $taxRate;
$deliveryFee = 40.00; // You may want to add this to the database
$totalCost = $order['total_amount'];

function formatCurrency($amount)
{
    return '₱' . number_format($amount, 2);
}

// ============================================================================
// STATUS COLORS & BUTTON LOGIC
// ============================================================================
$statusColor = '';
$buttonText = 'Back';
$buttonClass = 'bg-blue-600 hover:bg-blue-700';

switch ($orderStatus) {
    case 'Pending':
        $statusColor = 'text-yellow-600 font-semibold';
        break;
    case 'On Delivery':
        $statusColor = 'text-blue-600 font-semibold';
        break;
    case 'Delivered':
        $statusColor = 'text-green-600 font-semibold';
        break;
    case 'Cancelled':
        $statusColor = 'text-red-600 font-semibold';
        break;
    default:
        $statusColor = 'text-gray-600 font-semibold';
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

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            padding-left: 5rem;
            transition: .5s;
        }

        .group:hover+.content-area {
            padding-left: 16rem;
        }

        .content-area {
            min-height: 100vh;
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
                        <span class="block whitespace-nowrap transition-all duration-500 opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">${label}</span>
                    </div>
                </a>
            </li>
        `;
        }

        const sidebarHTML = `
        <aside class="group bg-gradient-to-br from-blue-400 to-blue-600 shadow-lg fixed left-0 top-0 h-full w-20 hover:w-64 transition-all duration-500 text-white flex flex-col">
            <div class="px-7 py-7 border-b border-blue-400 flex items-center gap-2">
                <i class="fas fa-tint text-3xl"></i>
                <span class="text-2xl font-semibold opacity-0 group-hover:opacity-100 transition-all translate-x-[-20px] group-hover:translate-x-0">RCH Water</span>
            </div>

            <nav class="mt-6 flex-1">
                <ul class="space-y-1 px-4">
                    ${createNavItem("../Customer/order_water_refill.php", "fas fa-tint", "Order Water Refill", "order_water_refill.php")}
                    ${createNavItem("../Customer/browse_products.php", "fas fa-shopping-basket", "Browse Products", "browse_products.php")}
                    ${createNavItem("../Customer/my_orders.php", "fas fa-box", "My Orders", "my_orders.php")}
                </ul>
            </nav>

            <div class="border-t border-blue-400 p-4">
                <a href="../logout.php" class="flex items-center gap-3 hover:bg-white/20 px-4 py-3 rounded-lg">
                    <i class="fas fa-sign-out-alt text-lg"></i>
                    <span class="opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0 transition-all">Logout</span>
                </a>
            </div>
        </aside>
    `;

        document.addEventListener("DOMContentLoaded", () => {
            document.body.insertAdjacentHTML("afterbegin", sidebarHTML);
        });
    </script>

    <!-- MAIN CONTENT -->
    <div class="content-area">
        <main class="bg-white rounded-xl shadow-lg h-[calc(100vh-2rem)] overflow-y-auto">

            <!-- HEADER -->
            <div class="bg-white border-b p-4 shadow-sm">
                <h1 class="text-3xl font-semibold text-blue-500"><?= $pageTitle ?></h1>
                <p class="text-sm text-gray-700"><?= $pageSubtitle ?></p>
            </div>

            <div class="p-8">
                <div class="bg-white w-full mx-auto rounded-2xl shadow-sm border p-8">

                    <!-- BACK BUTTON -->
                    <div class="flex items-center gap-3 mb-6">
                        <button onclick="window.history.back()"
                            class="flex items-center text-blue-600 hover:text-blue-700 text-2xl">
                            <i class="bi bi-arrow-left-circle-fill"></i>
                        </button>
                        <h1 class="text-2xl text-gray-700 font-semibold">Order Details</h1>
                    </div>

                    <!-- REAL-TIME RIDER LOCATION (Show only if On Delivery) -->
                    <?php if ($orderStatus === 'On Delivery' && $riderName !== 'Not Assigned'): ?>
                    <div class="border rounded-xl p-5 mb-6">
                        <div class="flex items-center gap-2 text-blue-600 font-semibold mb-3">
                            <i class="fas fa-location-arrow"></i> Real-Time Rider Location
                        </div>

                        <!-- MAP CONTAINER -->
                        <div class="w-full h-[300px] bg-gray-50 rounded-xl flex items-center justify-center text-gray-600 mb-4">
                            <span>Map will be displayed here</span>
                        </div>

                        <div class="border rounded-xl p-4 bg-gray-50">
                            <div class="font-semibold text-gray-700 mb-1">Rider Information</div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="font-medium"><?= htmlspecialchars($riderName) ?></span>
                                <span><i class="fa fa-phone mr-1"></i><?= htmlspecialchars($riderPhone) ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- DELIVERY INFORMATION -->
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

                    <!-- ORDER SUMMARY -->
                    <div class="border rounded-xl p-5 mb-6">
                        <p class="<?= $statusColor ?> text-sm font-semibold"><?= htmlspecialchars($orderStatus) ?></p>

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
                                        <td class="py-3">
                                            <?php 
                                            if ($item['product_name']) {
                                                echo htmlspecialchars($item['product_name']);
                                            } else {
                                                echo htmlspecialchars($item['gallon_size']) . ' ' . htmlspecialchars($item['gallon_type']);
                                            }
                                            ?>
                                        </td>
                                        <td class="text-center font-semibold"><?= $item['quantity'] ?></td>
                                        <td class="text-right"><?= formatCurrency($item['unit_price']) ?></td>
                                        <td class="text-right"><?= formatCurrency($item['subtotal']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div class="mt-4 text-sm space-y-2">
                            <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span><?= formatCurrency($subtotal) ?></span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Tax (<?= $taxRate * 100 ?>%)</span><span><?= formatCurrency($taxAmount) ?></span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Delivery Fee</span><span><?= formatCurrency($deliveryFee) ?></span></div>

                            <div class="flex justify-between pt-3 text-lg font-semibold">
                                <span>Total Cost</span>
                                <span class="text-blue-600"><?= formatCurrency($totalCost) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- ORDER META -->
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
                            <span><?= $orderDateTime ?></span>
                        </div>

                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Ship Date</span>
                            <span><?= $shipDateTime ?></span>
                        </div>
                    </div>

                </div>
            </div>

        </main>
    </div>

</body>

</html>