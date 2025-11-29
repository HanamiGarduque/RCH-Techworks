<?php
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('admin');

require_once '../Database/db_connection.php';

$database = new Database();
$db = $database->getConnect();

$orderNumber = isset($_GET['order_number']) ? $_GET['order_number'] : '';

if (empty($orderNumber)) {
    header('Location: customer_orders.php');
    exit;
}

// Fetch order details with customer and rider information
$orderQuery = "SELECT 
    r.refill_id,
    r.order_number,
    r.total_amount,
    r.pickup_datetime,
    r.delivery_datetime,
    r.order_status,
    r.created_at,
    r.user_id,
    r.delay_reason,
    u.name as customer_name,
    u.phone_number as customer_phone,
    u.address as customer_address,
    rider.name as rider_name,
    rider.phone_number as rider_phone
FROM water_refills r
LEFT JOIN users u ON r.user_id = u.user_id
LEFT JOIN users rider ON r.rider_id = rider.user_id
WHERE r.order_number = :order_number
LIMIT 1";

$stmt = $db->prepare($orderQuery);
$stmt->bindParam(':order_number', $orderNumber);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: customer_orders.php');
    exit;
}

// Fetch order items with inventory details
$itemsQuery = "SELECT 
    ri.product_id,
    ri.gallon_type,
    ri.gallon_size,
    ri.quantity,
    ri.unit_price,
    ri.subtotal,
    ri.gallon_owner,
    ri.processed_quantity,
    i.item_name
FROM water_refill_items ri
LEFT JOIN inventory i ON ri.product_id = i.item_id
WHERE ri.refill_id = :refill_id";

$itemsStmt = $db->prepare($itemsQuery);
$itemsStmt->bindParam(':refill_id', $order['refill_id']);
$itemsStmt->execute();
$orderItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

// Check if all station-owned gallons are processed
$stationGallons = array_filter($orderItems, function ($item) {
    return $item['gallon_owner'] === 'station';
});

$allGallonsProcessed = true;
foreach ($stationGallons as $item) {
    $processed = $item['processed_quantity'] ?? 0;
    if ($processed < $item['quantity']) {
        $allGallonsProcessed = false;
        break;
    }
}

// Calculate totals
$subtotal = array_sum(array_column($orderItems, 'subtotal'));
$taxRate = 0.00;
$taxAmount = $subtotal * $taxRate;
$deliveryFee = 0.00;
$totalCost = $subtotal + $taxAmount + $deliveryFee;

function formatCurrency($amount)
{
    return '₱' . number_format($amount, 2);
}

// Fetch available riders for assignment
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
        body {
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
            scrollbar-gutter: stable;
        }

        html {
            overflow-y: scroll;
        }

        body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown) {
            overflow-y: hidden !important;
            padding-right: 0 !important;
        }

        body.swal2-height-auto {
            height: 100% !important;
        }

        .sidebar-class {
            z-index: 1000;
            /* Higher than the map's default z-index of 400 */
        }

        #delivery-map {
            position: relative;
            z-index: 1;
        }

        .page-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .content-wrapper {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #9ca3af;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
        }
    </style>
</head>

<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <script src="../Admin/includes/sidebar.js"></script>

        <!-- Main Content -->
        <div class="flex-1 ml-20 group-hover:ml-64 transition-all duration-300 ease-in-out overflow-hidden">
            <div class="page-container">
                <!-- Header -->
                <?php
                $pageTitle = "Order Management";
                $pageSubtitle = "View and manage customer orders and details.";
                include '../Admin/includes/header.php';
                ?>

                <!-- Content Wrapper - Scrollable Area -->
                <div class="content-wrapper p-6">
                    <!-- Back Button and Title -->
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <button onclick="window.location.href='../Order/orders_list.php'"
                                class="flex items-center text-blue-600 hover:text-blue-700 text-2xl transition-colors">
                                <i class="bi bi-arrow-left-circle-fill"></i>
                            </button>
                            <h1 class="text-2xl text-gray-700 font-semibold">Order Details - <?= htmlspecialchars($order['order_number']) ?></h1>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-3">
                            <?php if (!$allGallonsProcessed && $order['order_status'] !== 'cancelled'): ?>
                                <a href="process_order.php?order_number=<?= urlencode($orderNumber) ?>"
                                    class="flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-medium transition shadow-md">
                                    <i class="fas fa-qrcode"></i>
                                    Process Order
                                </a>
                            <?php endif; ?>

                            <?php if ($allGallonsProcessed && $order['order_status'] !== 'cancelled'): ?>
                                <button onclick="openAssignRiderModal()"
                                    class="flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition shadow-md">
                                    <i class="fas fa-user-plus"></i>
                                    <?= $order['rider_name'] ? 'Reassign Rider' : 'Assign Rider' ?>
                                </button>
                            <?php endif; ?>

                            <?php if ($order['order_status'] !== 'cancelled' && $order['order_status'] !== 'delivered'): ?>
                                <button onclick="openRescheduleModal()"
                                    class="flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg font-medium transition shadow-md">
                                    <i class="fas fa-calendar-alt"></i>
                                    Reschedule
                                </button>
                            <?php endif; ?>

                            <?php if ($order['order_status'] !== 'cancelled' && $order['order_status'] !== 'delivered'): ?>
                                <button id="cancelOrderBtn"
                                    class="flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-medium transition shadow-md">
                                    <i class="fas fa-times-circle"></i>
                                    Cancel Order
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Main Content Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                        <!-- Left Column - Order Items and Details -->
                        <div class="lg:col-span-2 space-y-6">

                            <!-- Real-Time Rider Location -->
                            <div class="bg-white border rounded-xl shadow-sm p-5">
                                <div class="flex items-center gap-2 text-blue-600 font-semibold mb-3">
                                    <i class="fas fa-location-arrow"></i> Real-Time Rider Location
                                </div>

                                <div id="delivery-map" class="w-full h-80 rounded-xl border border-gray-200"> <span>Map will be displayed here</span>
                                </div>

                                <div class="border rounded-xl p-4 bg-gray-50">
                                    <div class="font-semibold text-gray-700 mb-1">Rider Information</div>
                                    <?php if ($order['rider_name']): ?>
                                        <div class="flex justify-between items-center text-sm">
                                            <span class="font-medium"><?= htmlspecialchars($order['rider_name']) ?></span>
                                            <span><i class="fa fa-phone mr-1"></i><?= htmlspecialchars($order['rider_phone']) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-sm text-gray-500 italic">No rider assigned yet</div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Order Items -->
                            <div class="bg-white border rounded-xl shadow-sm p-5">
                                <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2 flex items-center gap-2">
                                    <i class="fas fa-shopping-cart text-blue-600"></i>
                                    Order Items
                                </h3>

                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="text-gray-600 border-b">
                                                <th class="text-left pb-2 px-2">Product</th>
                                                <th class="text-center pb-2 px-2">Owner</th>
                                                <th class="text-center pb-2 px-2">Qty</th>
                                                <th class="text-right pb-2 px-2">Unit</th>
                                                <th class="text-right pb-2 px-2">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($orderItems as $i => $item): ?>
                                                <tr class="<?= $i < count($orderItems) - 1 ? 'border-b' : '' ?>">
                                                    <td class="py-3 px-2">
                                                        <?= htmlspecialchars($item['item_name'] ?: ($item['gallon_type'] . ' - ' . $item['gallon_size'])) ?>
                                                    </td>
                                                    <td class="text-center px-2">
                                                        <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full <?= $item['gallon_owner'] === 'station' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' ?>">
                                                            <?= ucfirst($item['gallon_owner']) ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-center font-semibold px-2"><?= $item['quantity'] ?></td>
                                                    <td class="text-right px-2"><?= formatCurrency($item['unit_price']) ?></td>
                                                    <td class="text-right font-semibold px-2"><?= formatCurrency($item['subtotal']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Order Summary -->
                                <div class="mt-4 text-sm space-y-2 pt-4 border-t">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Subtotal</span>
                                        <span><?= formatCurrency($subtotal) ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Tax (<?= $taxRate * 100 ?>%)</span>
                                        <span><?= formatCurrency($taxAmount) ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Delivery Fee</span>
                                        <span><?= formatCurrency($deliveryFee) ?></span>
                                    </div>

                                    <div class="flex justify-between pt-3 text-lg font-semibold border-t">
                                        <span>Total Cost</span>
                                        <span class="text-blue-600"><?= formatCurrency($order['total_amount']) ?></span>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Right Column - Delivery Info and Metadata -->
                        <div class="space-y-6">

                            <!-- Delivery Information -->
                            <div class="bg-white border rounded-xl shadow-sm p-5">
                                <div class="flex items-center gap-2 text-blue-600 font-semibold mb-4 border-b pb-2">
                                    <i class="fas fa-map-marker-alt text-lg"></i> Delivery Information
                                </div>

                                <div class="space-y-3">
                                    <div>
                                        <div class="text-xs text-gray-500 mb-1">Customer Name</div>
                                        <div class="font-semibold text-gray-800"><?= htmlspecialchars($order['customer_name']) ?></div>
                                    </div>

                                    <div>
                                        <div class="text-xs text-gray-500 mb-1">Contact Number</div>
                                        <div class="font-medium text-gray-700"><?= htmlspecialchars($order['customer_phone']) ?></div>
                                    </div>

                                    <div>
                                        <div class="text-xs text-gray-500 mb-1">Delivery Address</div>
                                        <div class="text-sm text-gray-700"><?= htmlspecialchars($order['customer_address']) ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Metadata -->
                            <div class="bg-white border rounded-xl shadow-sm p-5">
                                <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2 flex items-center gap-2">
                                    <i class="fas fa-info-circle text-blue-600"></i>
                                    Order Metadata
                                </h3>

                                <div class="space-y-3 text-sm">
                                    <div class="flex justify-between items-center py-2 border-b">
                                        <span class="text-gray-500">Order Number</span>
                                        <span class="font-semibold text-gray-800"><?= htmlspecialchars($order['order_number']) ?></span>
                                    </div>

                                    <div class="flex justify-between items-center py-2 border-b">
                                        <span class="text-gray-500">Payment Method</span>
                                        <span class="font-medium text-gray-700">Cash on Delivery</span>
                                    </div>

                                    <div class="flex justify-between items-center py-2 border-b">
                                        <span class="text-gray-500">Order Placed</span>
                                        <span class="font-medium text-gray-700"><?= date('M d, Y | H:i', strtotime($order['created_at'])) ?></span>
                                    </div>

                                    <div class="flex justify-between items-center py-2 border-b">
                                        <span class="text-gray-500">Pickup Time</span>
                                        <span class="font-medium text-gray-700"><?= date('M d, Y | H:i', strtotime($order['pickup_datetime'])) ?></span>
                                    </div>

                                    <div class="flex justify-between items-center py-2">
                                        <span class="text-gray-500">Delivery Time</span>
                                        <span class="font-medium text-gray-700"><?= date('M d, Y | H:i', strtotime($order['delivery_datetime'])) ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Status -->
                            <div class="bg-white border rounded-xl shadow-sm p-5">
                                <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2 flex items-center gap-2">
                                    <i class="fas fa-tasks text-blue-600"></i>
                                    Order Status
                                </h3>

                                <div class="space-y-3">
                                    <?php
                                    $statuses = [
                                        'pending' => 'Order Pending',
                                        'processed' => 'Order Processed',
                                        'assigned' => 'Rider Assigned',
                                        'pickup_in_progress' => 'Pickup In Progress',
                                        'picked_up' => 'Picked Up',
                                        'on_delivery' => 'On Delivery',
                                        'delivered' => 'Delivered'
                                    ];

                                    $currentStatus = $order['order_status'];
                                    $statusKeys = array_keys($statuses);

                                    // Determine if order is processed (all gallons scanned)
                                    $isProcessed = $allGallonsProcessed;

                                    $currentIndex = array_search($currentStatus, $statusKeys);

                                    foreach ($statuses as $key => $label):
                                        $keyIndex = array_search($key, $statusKeys);

                                        // Special handling for 'processed' status
                                        if ($key === 'processed') {
                                            $isCompleted = $isProcessed;
                                            $isCurrent = $isProcessed && $currentStatus === 'pending';
                                        } else {
                                            $isCompleted = $keyIndex <= $currentIndex;
                                            $isCurrent = $key === $currentStatus;
                                        }
                                    ?>
                                        <div class="flex items-center gap-3">
                                            <div class="w-2 h-2 rounded-full <?= $isCompleted ? 'bg-green-500' : ($isCurrent ? 'bg-blue-500 animate-pulse' : 'bg-gray-300') ?>"></div>
                                            <span class="text-sm <?= $isCurrent ? 'font-semibold text-gray-700' : ($isCompleted ? 'text-gray-700' : 'text-gray-400') ?>">
                                                <?= $label ?>
                                            </span>
                                        </div>
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
        // Assign Rider Modal
        function openAssignRiderModal() {
            const riders = <?= json_encode($availableRiders) ?>;

            if (riders.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Riders Available',
                    text: 'There are no active riders to assign.',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }

            let riderOptions = '<select id="riderSelect" class="swal2-input" style="width: 90%; padding: 10px;">';
            riderOptions += '<option value="">-- Select a Rider --</option>';
            riders.forEach(rider => {
                riderOptions += `<option value="${rider.user_id}">${rider.name} - ${rider.phone_number}</option>`;
            });
            riderOptions += '</select>';

            Swal.fire({
                title: 'Assign Rider',
                html: `
                    <div style="text-align: left; padding: 10px;">
                        <label style="font-weight: 500; margin-bottom: 8px; display: block;">Select Rider:</label>
                        ${riderOptions}
                    </div>
                `,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Assign',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#6b7280',
                preConfirm: () => {
                    const riderId = document.getElementById('riderSelect').value;
                    if (!riderId) {
                        Swal.showValidationMessage('Please select a rider');
                        return false;
                    }
                    return riderId;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    assignRider(result.value);
                }
            });
        }

        function assignRider(riderId) {
            fetch('assign_rider.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        order_number: '<?= $orderNumber ?>',
                        rider_id: riderId
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Rider Assigned!',
                            text: 'The rider has been successfully assigned to this order.',
                            confirmButtonColor: '#3b82f6'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to assign rider.',
                            confirmButtonColor: '#dc2626'
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Request failed. Please check your connection.',
                        confirmButtonColor: '#dc2626'
                    });
                });
        }

        function cancelOrder() {
            Swal.fire({
                title: 'Cancel Order?',
                html: `
            <p style="margin-bottom: 15px;">Are you sure you want to cancel this order? This action cannot be undone.</p>
            <label style="font-weight: 500; margin-bottom: 8px; display: block; text-align: left;">Reason for Cancellation:</label>
            <textarea id="cancelReason" class="swal2-textarea" placeholder="Enter reason..." 
                style="width: 87%; height: 100px; font-size: 1rem;"></textarea>
        `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Cancel Order',
                cancelButtonText: 'No, Keep Order',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                preConfirm: () => {
                    const reason = document.getElementById('cancelReason').value.trim();
                    if (!reason) {
                        Swal.showValidationMessage('Please provide a reason for cancellation');
                        return false;
                    }
                    return reason;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    performCancelOrder(result.value);
                }
            });
        }

        function performCancelOrder(reason) {
            fetch('admin_cancel_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        order_number: '<?= $orderNumber ?>',
                        reason: reason
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Order Cancelled',
                            text: 'The order has been successfully cancelled.',
                            confirmButtonColor: '#3b82f6'
                        }).then(() => {
                            window.location.href = 'orders_list.php';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to cancel order.',
                            confirmButtonColor: '#dc2626'
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Request failed. Please check your connection.',
                        confirmButtonColor: '#dc2626'
                    });
                });
        }

        function openRescheduleModal() {
            Swal.fire({
                title: 'Reschedule Order',
                html: ` <div style="text-align: left; display: flex; flex-direction: column; gap: 8px; font-size: 0.875rem;"> <label for="rescheduleType" style="font-weight: 500;">Reschedule Type:</label> <select id="rescheduleType" class="swal2-input" style="width: 100%; padding: 6px; font-size: 0.875rem; color: #3b82f6; border: 1px solid #d1d5db; border-radius: 4px;"  
                 onfocus="this.style.outline='2px solid #3b82f6'; this.style.outlineOffset='2px';"  
                 onblur="this.style.outline='';"> <option value="">-- Select Type --</option> <option value="pickup">Pickup Date & Time</option> <option value="delivery">Delivery Date & Time</option> </select>


            <label for="newDate" style="font-weight: 500;">New Date:</label>  
            <input type="date" id="newDate" class="swal2-input" style="width: 87%; padding: 6px; font-size: 0.875rem;" min="${new Date().toISOString().split('T')[0]}">  

            <label for="timeSlot" style="font-weight: 500;">Time Slot:</label>  
            <select id="timeSlot" class="swal2-input" style="width: 100%; padding: 6px; font-size: 0.875rem; color: #3b82f6; border: 1px solid #d1d5db; border-radius: 4px;" disabled  
                onfocus="this.style.outline='2px solid #3b82f6'; this.style.outlineOffset='2px';"  
                onblur="this.style.outline='';">  
                <option value="">-- Select time slot first --</option>  
            </select>  

            <label for="rescheduleReason" style="font-weight: 500;">Reason for Rescheduling:</label>  
            <textarea id="rescheduleReason" class="swal2-textarea" placeholder="Enter reason for rescheduling..." style="width: 87%; padding: 6px; font-size: 0.875rem; height: 90px; resize: vertical;"></textarea>  
        </div>  
            `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Reschedule',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#6b7280',
                didOpen: () => {
                    const rescheduleType = document.getElementById('rescheduleType');
                    const timeSlotSelect = document.getElementById('timeSlot');

                    rescheduleType.addEventListener('change', function() {
                        const type = this.value;
                        timeSlotSelect.disabled = false;
                        timeSlotSelect.innerHTML = '<option value="">-- Select Time Slot --</option>';

                        if (type === 'pickup') {
                            timeSlotSelect.innerHTML += `  
                    <option value="morning">Morning - 7:30 AM</option>  
                    <option value="afternoon">Afternoon - 1:00 PM</option>  
                `;
                        } else if (type === 'delivery') {
                            timeSlotSelect.innerHTML += `  
                    <option value="morning_early">Morning - 9:00 AM</option>  
                    <option value="morning_late">Morning - 11:00 AM</option>  
                    <option value="afternoon_early">Afternoon - 2:00 PM</option>  
                    <option value="afternoon_late">Afternoon - 4:00 PM</option>  
                `;
                        }
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

                    return {
                        reschedule_type: rescheduleTypeVal,
                        new_date: newDateVal,
                        new_time_slot: timeSlotVal,
                        reason: reasonVal
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) performReschedule(result.value);
            });
        }


        function performReschedule(data) {
            // Show loading
            Swal.fire({
                title: 'Rescheduling...',
                text: 'Please wait while we update the schedule.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('reschedule_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        order_number: '<?= $orderNumber ?>',
                        ...data
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Rescheduled Successfully!',
                            text: data.message,
                            confirmButtonColor: '#3b82f6'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to reschedule order.',
                            confirmButtonColor: '#dc2626'
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Request failed. Please check your connection.',
                        confirmButtonColor: '#dc2626'
                    });
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const cancelBtn = document.getElementById('cancelOrderBtn');
            if (cancelBtn) {
                cancelBtn.addEventListener('click', cancelOrder);
            }
        });
    </script>

    <script>
        // Add this to your existing JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.group'); // or whatever class triggers the hover
            const mapContainer = document.getElementById('delivery-map');

            if (sidebar && mapContainer && typeof map !== 'undefined') {
                // Listen for sidebar hover state changes
                sidebar.addEventListener('mouseenter', function() {
                    setTimeout(() => {
                        map.invalidateSize();
                    }, 300); // Match your transition duration
                });

                sidebar.addEventListener('mouseleave', function() {
                    setTimeout(() => {
                        map.invalidateSize();
                    }, 300); // Match your transition duration
                });
            }
        });
        document.addEventListener("DOMContentLoaded", () => {
            const userId = <?= htmlspecialchars($order['user_id']) ?>;
            const orderNumber = "<?= htmlspecialchars($order['order_number']) ?>";
            const hasRider = <?= $order['rider_name'] ? 'true' : 'false' ?>;

            // Initialize map with better zoom (13 shows streets and highways clearly)
            const map = L.map('delivery-map').setView([13.9411, 121.1628], 13);

            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                minZoom: 10,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);
            setTimeout(() => {
                map.invalidateSize();
            }, 500);
            // Station location (Update with your actual RCH Water Station coordinates)
            const stationLat = 13.9411;
            const stationLng = 121.1628;

            // Custom icons
            const stationIcon = L.icon({
                iconUrl: 'https://cdn-icons-png.flaticon.com/512/3448/3448636.png',
                iconSize: [40, 40],
                iconAnchor: [20, 40],
                popupAnchor: [0, -40]
            });

            const customerIcon = L.icon({
                iconUrl: 'https://cdn-icons-png.flaticon.com/512/3177/3177440.png',
                iconSize: [35, 35],
                iconAnchor: [17, 35],
                popupAnchor: [0, -35]
            });

            const riderIcon = L.icon({
                iconUrl: 'https://cdn-icons-png.flaticon.com/512/684/684908.png',
                iconSize: [35, 35],
                iconAnchor: [17, 35],
                popupAnchor: [0, -35]
            });

            // Add station marker
            const stationMarker = L.marker([stationLat, stationLng], {
                    icon: stationIcon
                })
                .addTo(map)
                .bindPopup(`
            <div style="text-align: center; min-width: 150px;">
                <strong style="color: #2563eb; font-size: 14px;">🏢 RCH Water Station</strong><br>
                <small style="color: #6b7280;">Main Office & Warehouse</small>
            </div>
        `);

            let allMarkers = [stationMarker];
            let routePolylines = [];
            let riderMarker = null;
            let updateInterval = null;

            // Fetch customer location
            fetch(`../Location/get_user_location_by_id.php?user_id=${userId}`)
                .then(res => res.json())
                .then(data => {
                    console.log('Customer location:', data);

                    let userLat = 13.9411,
                        userLng = 121.1628;

                    if (data.status === 'has_saved_location' || data.status === 'approx_location') {
                        userLat = data.latitude;
                        userLng = data.longitude;
                    }

                    // Add customer marker
                    const customerMarker = L.marker([userLat, userLng], {
                            icon: customerIcon
                        })
                        .addTo(map)
                        .bindPopup(`
                    <div style="text-align: center; min-width: 180px;">
                        <strong style="color: #16a34a; font-size: 14px;">📍 Customer Location</strong><br>
                        <div style="margin-top: 5px; padding: 5px; background: #f3f4f6; border-radius: 4px;">
                            <small style="font-weight: 600;"><?= htmlspecialchars($order['customer_name']) ?></small><br>
                            <small style="color: #6b7280;"><?= htmlspecialchars($order['customer_address']) ?></small>
                        </div>
                    </div>
                `);

                    allMarkers.push(customerMarker);

                    if (hasRider) {
                        // Fetch rider location
                        fetchRiderLocation(orderNumber, stationLat, stationLng, userLat, userLng);

                        // Update rider location every 10 seconds
                        updateInterval = setInterval(() => {
                            fetchRiderLocation(orderNumber, stationLat, stationLng, userLat, userLng, true);
                        }, 10000);
                    } else {
                        // No rider - show planned route
                        drawPlannedRoute(stationLat, stationLng, userLat, userLng);

                        // Fit map to show all markers
                        const group = new L.featureGroup(allMarkers);
                        map.fitBounds(group.getBounds().pad(0.15));
                    }

                    // Add legend
                    addMapLegend(map, hasRider);

                    // Add scale control
                    L.control.scale({
                        imperial: false,
                        metric: true
                    }).addTo(map);
                })
                .catch(err => {
                    console.error("Failed to fetch customer location:", err);
                    showMapError();
                });

            // Function to fetch and update rider location
            function fetchRiderLocation(orderNum, stationLat, stationLng, customerLat, customerLng, isUpdate = false) {
                fetch(`../Location/get_rider_location.php?order_number=${orderNum}`)
                    .then(res => res.json())
                    .then(riderData => {
                        console.log('Rider location:', riderData);

                        let riderLat, riderLng;
                        let locationStatus = 'estimated';

                        if (riderData.status === 'real_time' && riderData.is_recent) {
                            riderLat = riderData.latitude;
                            riderLng = riderData.longitude;
                            locationStatus = 'real_time';
                        } else if (riderData.status === 'outdated') {
                            riderLat = riderData.latitude;
                            riderLng = riderData.longitude;
                            locationStatus = 'outdated';
                        } else {
                            // Fallback: place rider between station and customer
                            riderLat = (stationLat + customerLat) / 2 + 0.01;
                            riderLng = (stationLng + customerLng) / 2 + 0.01;
                            locationStatus = 'estimated';
                        }

                        if (isUpdate && riderMarker) {
                            // Update existing marker position smoothly
                            riderMarker.setLatLng([riderLat, riderLng]);
                            updateRoutes(stationLat, stationLng, riderLat, riderLng, customerLat, customerLng);
                        } else {
                            // Create new rider marker
                            const statusColor = {
                                'real_time': '#16a34a',
                                'outdated': '#f59e0b',
                                'estimated': '#9ca3af'
                            } [locationStatus];

                            const statusText = {
                                'real_time': '● Live',
                                'outdated': '● Last known',
                                'estimated': '● Estimated'
                            } [locationStatus];

                            riderMarker = L.marker([riderLat, riderLng], {
                                    icon: riderIcon
                                })
                                .addTo(map)
                                .bindPopup(`
                            <div style="text-align: center; min-width: 160px;">
                                <strong style="color: #f59e0b; font-size: 14px;">🏍️ Rider</strong><br>
                                <div style="margin-top: 5px; padding: 5px; background: #fef3c7; border-radius: 4px;">
                                    <small style="font-weight: 600;"><?= htmlspecialchars($order['rider_name'] ?? 'Rider') ?></small><br>
                                    <small style="color: #6b7280;"><?= htmlspecialchars($order['rider_phone'] ?? '') ?></small><br>
                                    <small style="color: ${statusColor}; font-weight: 600;">${statusText}</small>
                                </div>
                            </div>
                        `);

                            allMarkers.push(riderMarker);

                            // Draw routes
                            drawActiveRoutes(stationLat, stationLng, riderLat, riderLng, customerLat, customerLng);

                            // Fit map to show all markers
                            const group = new L.featureGroup(allMarkers);
                            map.fitBounds(group.getBounds().pad(0.15));
                        }
                    })
                    .catch(err => {
                        console.error("Failed to fetch rider location:", err);
                    });
            }

            // Function to draw active routes
            function drawActiveRoutes(stationLat, stationLng, riderLat, riderLng, customerLat, customerLng) {
                // Clear existing routes
                routePolylines.forEach(route => map.removeControl(route));
                routePolylines = [];

                // Route: Station → Rider
                const stationToRider = L.Routing.control({
                    waypoints: [
                        L.latLng(stationLat, stationLng),
                        L.latLng(riderLat, riderLng)
                    ],
                    lineOptions: {
                        styles: [{
                            color: '#3b83f6ff',
                            weight: 4,
                            opacity: 1.0,
                            dashArray: '10,10'
                        }]
                    },
                    createMarker: function() {
                        return null;
                    },
                    addWaypoints: false,
                    routeWhileDragging: false,
                    show: false
                }).addTo(map);

                // Route: Rider → Customer
                const riderToCustomer = L.Routing.control({
                    waypoints: [
                        L.latLng(riderLat, riderLng),
                        L.latLng(customerLat, customerLng)
                    ],
                    lineOptions: {
                        styles: [{
                            color: '#f59e0b',
                            weight: 5,
                            opacity: 0.8
                        }]
                    },
                    createMarker: function() {
                        return null;
                    },
                    addWaypoints: false,
                    routeWhileDragging: false,
                    show: false
                }).addTo(map);

                routePolylines.push(stationToRider, riderToCustomer);
            }

            // Function to update routes
            function updateRoutes(stationLat, stationLng, riderLat, riderLng, customerLat, customerLng) {
                routePolylines.forEach(polyline => map.removeLayer(polyline));
                routePolylines = [];
                drawActiveRoutes(stationLat, stationLng, riderLat, riderLng, customerLat, customerLng);
            }

            // Function to draw planned route
            function drawPlannedRoute(stationLat, stationLng, customerLat, customerLng) {
                // Clear existing routes
                routePolylines.forEach(route => map.removeControl(route));
                routePolylines = [];

                const plannedRouteControl = L.Routing.control({
                    waypoints: [
                        L.latLng(stationLat, stationLng),
                        L.latLng(customerLat, customerLng)
                    ],
                    lineOptions: {
                        styles: [{
                            color: '#0050daff',
                            weight: 4,
                            opacity: 0.5,
                            dashArray: '15,15'
                        }]
                    },
                    createMarker: function() {
                        return null;
                    }, // markers already added
                    addWaypoints: false,
                    routeWhileDragging: false,
                    fitSelectedRoutes: false,
                    show: false
                }).addTo(map);

                routePolylines.push(plannedRouteControl);
            }

            // Function to calculate and display distance
            function addDistanceLabel(map, lat1, lng1, lat2, lng2, color) {
                const distance = calculateDistance(lat1, lng1, lat2, lng2);
                const midLat = (lat1 + lat2) / 2;
                const midLng = (lng1 + lng2) / 2;

                const distanceIcon = L.divIcon({
                    className: 'distance-label',
                    html: `<div style="background: white; padding: 3px 8px; border-radius: 12px; border: 2px solid ${color}; font-size: 11px; font-weight: 600; color: ${color}; white-space: nowrap; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">${distance.toFixed(1)} km</div>`,
                    iconSize: [60, 24]
                });

                L.marker([midLat, midLng], {
                    icon: distanceIcon
                }).addTo(map);
            }

            // Calculate distance between two points (Haversine formula)
            function calculateDistance(lat1, lon1, lat2, lon2) {
                const R = 6371; // Earth's radius in km
                const dLat = (lat2 - lat1) * Math.PI / 180;
                const dLon = (lon2 - lon1) * Math.PI / 180;
                const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                    Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                    Math.sin(dLon / 2) * Math.sin(dLon / 2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                return R * c;
            }

            // Function to show map error
            function showMapError() {
                const mapContainer = document.getElementById('delivery-map');
                const errorDiv = document.createElement('div');
                errorDiv.className = 'absolute inset-0 flex items-center justify-center bg-red-50 bg-opacity-90 z-[1000]';
                errorDiv.innerHTML = `
            <div class="text-center p-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-3xl mb-2"></i>
                <p class="text-red-600 font-semibold">Failed to load map</p>
                <p class="text-sm text-gray-600">Please refresh the page</p>
            </div>
        `;
                mapContainer.appendChild(errorDiv);
            }

            // Cleanup interval on page unload
            window.addEventListener('beforeunload', () => {
                if (updateInterval) clearInterval(updateInterval);
            });
        });

        // Function to add map legend
        function addMapLegend(map, hasRider) {
            const legend = L.control({
                position: 'bottomright'
            });

            legend.onAdd = function() {
                const div = L.DomUtil.create('div', 'map-legend');
                div.style.cssText = 'background: white; padding: 10px 12px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.15); font-size: 12px;';

                let html = '<div style="font-weight: 600; margin-bottom: 8px; color: #374151; font-size: 13px;">📍 Route Info</div>';

                if (hasRider) {
                    html += `
                <div style="display: flex; align-items: center; margin-bottom: 5px;">
                    <div style="width: 20px; height: 3px; background: #3b82f6; margin-right: 8px; opacity: 0.6; border: 1px dashed #3b82f6;"></div>
                    <span style="color: #6b7280;">Completed</span>
                </div>
                <div style="display: flex; align-items: center;">
                    <div style="width: 20px; height: 4px; background: #f59e0b; margin-right: 8px;"></div>
                    <span style="color: #6b7280;">Active Route</span>
                </div>
            `;
                } else {
                    html += `
                <div style="display: flex; align-items: center;">
                    <div style="width: 20px; height: 3px; background: #9ca3af; margin-right: 8px; opacity: 0.5; border: 1px dashed #9ca3af;"></div>
                    <span style="color: #6b7280;">Planned</span>
                </div>
            `;
                }

                div.innerHTML = html;
                return div;
            };

            legend.addTo(map);
        }
    </script>
</body>

</html>