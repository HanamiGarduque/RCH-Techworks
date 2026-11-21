<?php
    require_once __DIR__ . '/../session.php';
    redirectIfRoleMismatch('customer');
    require_once '../Database/db_connection.php';

    $database = new Database();
    $db = $database->getConnect();

    $userId = $_SESSION['id'];

    // TO PAY: pending, assigned
    $queryToPay = $db->prepare("
        SELECT wr.*,
            SUM(wri.quantity) AS item_count,
            SUM(wri.subtotal) AS total_amount
        FROM water_refills wr
        LEFT JOIN water_refill_items wri ON wr.refill_id = wri.refill_id
        WHERE wr.user_id = ?
        AND wr.order_status IN ('pending', 'assigned', 'pickup_delayed', 'delivery_delayed')
        GROUP BY wr.refill_id
        ORDER BY wr.created_at DESC
    ");
    $queryToPay->execute([$userId]);
    $toPayOrders = $queryToPay->fetchAll(PDO::FETCH_ASSOC);

    // TO RECEIVE: pickup_in_progress, picked_up, on_delivery
    $queryToReceive = $db->prepare("
        SELECT wr.*,
            SUM(wri.quantity) AS item_count,
            SUM(wri.subtotal) AS total_amount
        FROM water_refills wr
        LEFT JOIN water_refill_items wri ON wr.refill_id = wri.refill_id
        WHERE wr.user_id = ?
        AND wr.order_status IN ('pickup_in_progress', 'picked_up', 'on_delivery')
        GROUP BY wr.refill_id
        ORDER BY wr.created_at DESC
    ");
    $queryToReceive->execute([$userId]);
    $toReceiveOrders = $queryToReceive->fetchAll(PDO::FETCH_ASSOC);

    // COMPLETED: delivered
    $queryCompleted = $db->prepare("
        SELECT wr.*,
            SUM(wri.quantity) AS item_count,
            SUM(wri.subtotal) AS total_amount
        FROM water_refills wr
        LEFT JOIN water_refill_items wri ON wr.refill_id = wri.refill_id
        WHERE wr.user_id = ?
        AND wr.order_status = 'delivered'
        GROUP BY wr.refill_id
        ORDER BY wr.created_at DESC
    ");
    $queryCompleted->execute([$userId]);
    $completedOrders = $queryCompleted->fetchAll(PDO::FETCH_ASSOC);

    // CANCELLED
    $queryCancelled = $db->prepare("
        SELECT wr.*,
            SUM(wri.quantity) AS item_count,
            SUM(wri.subtotal) AS total_amount
        FROM water_refills wr
        LEFT JOIN water_refill_items wri ON wr.refill_id = wri.refill_id
        WHERE wr.user_id = ?
        AND wr.order_status = 'cancelled'
        GROUP BY wr.refill_id
        ORDER BY wr.created_at DESC
    ");
    $queryCancelled->execute([$userId]);
    $cancelledOrders = $queryCancelled->fetchAll(PDO::FETCH_ASSOC);

    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>RCH Water - My Orders</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <style>
            body {
                font-family: 'Poppins', sans-serif;
                padding-left: 5rem;
                transition: padding-left 0.5s ease-in-out;
                margin: 0;
                padding-top: 0;
                padding-bottom: 0;
                padding-right: 0;
            }

            .tab-button {
                position: relative;
                transition: all 0.3s ease;
            }

            .tab-button.active {
                background-color: #2563eb;
                color: white;
                box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
            }

            .tab-button:hover:not(.active) {
                background-color: #dbeafe;
            }

            .order-card {
                transition: all 0.3s ease;
                border: 1px solid #e5e7eb;
            }

            .order-card:hover {
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
                transform: translateY(-2px);
            }

            .status-badge {
                padding: 0.375rem 0.75rem;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 600;
                letter-spacing: 0.025em;
            }

            .status-pending {
                background-color: #fef3c7;
                color: #92400e;
            }

            .status-assigned {
                background-color: #fed7aa;
                color: #9a3412;
            }

            .status-pickup_in_progress {
                background-color: #dbeafe;
                color: #1e40af;
            }

            .status-pickup_delayed {
                background-color: #fecaca;
                color: #991b1b;
            }

            .status-picked_up {
                background-color: #cffafe;
                color: #155e75;
            }

            .status-on_delivery {
                background-color: #e0e7ff;
                color: #3730a3;
            }

            .status-delivery_delayed {
                background-color: #fecaca;
                color: #991b1b;
            }

            .status-delivered {
                background-color: #d1fae5;
                color: #065f46;
            }

            .status-cancelled {
                background-color: #fee2e2;
                color: #991b1b;
            }

            .btn-primary {
                background-color: #2563eb;
                transition: all 0.2s ease;
            }

            .btn-primary:hover {
                background-color: #1d4ed8;
                transform: translateY(-1px);
                box-shadow: 0 4px 8px rgba(37, 99, 235, 0.3);
            }

            .btn-danger {
                background-color: #ef4444;
                transition: all 0.2s ease;
            }

            .btn-danger:hover {
                background-color: #dc2626;
                transform: translateY(-1px);
                box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
            }

            .empty-state {
                padding: 4rem 2rem;
                text-align: center;
            }

            .empty-state-icon {
                font-size: 4rem;
                color: #d1d5db;
                margin-bottom: 1rem;
            }

            .order-items-scroll::-webkit-scrollbar {
                width: 6px;
            }

            .order-items-scroll::-webkit-scrollbar-track {
                background: #f3f4f6;
                border-radius: 3px;
            }

            .order-items-scroll::-webkit-scrollbar-thumb {
                background: #d1d5db;
                border-radius: 3px;
            }

            .order-items-scroll::-webkit-scrollbar-thumb:hover {
                background: #9ca3af;
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
                        $pageTitle = "My Orders";
                        $pageSubtitle = "Hydrate made easy—order and we'll handle the rest!";
                        include 'includes/header.php';
                        ?>
                    </div>

                    <div class="p-8 h-[calc(100vh-80px)] flex flex-col">
                        <!-- TABS -->
                        <div class="flex gap-2 p-2 bg-gray-50 rounded-xl mb-6 flex-shrink-0">
                            <button class="tab-button flex-1 text-center py-3 text-sm font-medium rounded-lg" data-tab="topay">
                                <i class="fas fa-clock mr-2"></i>Pending Delivery
                            </button>
                            <button class="tab-button flex-1 text-center py-3 text-sm font-medium rounded-lg" data-tab="toreceive">
                                <i class="fas fa-truck mr-2"></i>To Pickup/Deliver
                            </button>
                            <button class="tab-button flex-1 text-center py-3 text-sm font-medium rounded-lg" data-tab="completed">
                                <i class="fas fa-check-circle mr-2"></i>Completed
                            </button>
                            <button class="tab-button flex-1 text-center py-3 text-sm font-medium rounded-lg" data-tab="cancelled">
                                <i class="fas fa-times-circle mr-2"></i>Cancelled
                            </button>
                        </div>

                        <!-- SCROLLABLE CONTENT AREA -->
                        <div class="flex-1 overflow-y-auto pr-2">
                            <!-- TO PAY -->
                            <div id="topay" class="tab-content space-y-4">
                                <?php if (empty($toPayOrders)): ?>
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="fas fa-inbox"></i>
                                        </div>
                                        <p class="text-gray-500 text-lg font-medium">No pending orders</p>
                                        <p class="text-gray-400 text-sm mt-1">Your pending orders will appear here</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($toPayOrders as $order): ?>
                                        <div class="order-card p-5 rounded-xl bg-white">
                                            <div class="flex justify-between items-start mb-4">
                                                <div>
                                                    <p class="text-xs text-gray-500 mb-1">Order Number</p>
                                                    <p class="font-semibold text-gray-900"><?= htmlspecialchars($order['order_number']) ?></p>
                                                </div>
                                                <span class="status-badge status-<?= htmlspecialchars($order['order_status']) ?>">
                                                    <?= htmlspecialchars(ucwords(str_replace('_', ' ', $order['order_status']))) ?>
                                                </span>
                                            </div>

                                            <?php
                                            $stmtItems = $db->prepare("
                                                SELECT wri.*, i.item_name
                                                FROM water_refill_items wri
                                                LEFT JOIN inventory i ON wri.product_id = i.item_id
                                                WHERE wri.refill_id = ?
                                            ");
                                            $stmtItems->execute([$order['refill_id']]);
                                            $orderItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
                                            ?>

                                            <div class="order-items-scroll max-h-40 overflow-y-auto mb-4 bg-gray-50 rounded-lg p-3">
                                                <?php foreach ($orderItems as $item): ?>
                                                    <div class="flex justify-between items-start mb-3 last:mb-0">
                                                        <div class="flex-1">
                                                            <p class="text-sm font-medium text-gray-900">
                                                                <?= htmlspecialchars($item['item_name']) ?>
                                                            </p>
                                                            <p class="text-xs text-gray-500 mt-1">
                                                                <?= htmlspecialchars($item['quantity']) ?> × ₱<?= number_format($item['unit_price'], 2) ?>
                                                            </p>
                                                            <p class="text-xs text-blue-600 mt-1">
                                                                <i class="fas fa-user-circle mr-1"></i><?= htmlspecialchars($item['gallon_owner']) ?>
                                                            </p>
                                                        </div>
                                                        <p class="text-sm font-semibold text-gray-900">
                                                            ₱<?= number_format($item['subtotal'], 2) ?>
                                                        </p>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>

                                            <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                                                <div>
                                                    <p class="text-xs text-gray-500">Total Items</p>
                                                    <p class="text-sm font-medium text-gray-700"><?= htmlspecialchars($order['item_count'] ?? 0) ?> item(s)</p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-xs text-gray-500 mb-1">Total Amount</p>
                                                    <p class="text-xl font-bold text-blue-600">₱<?= number_format($order['total_amount'], 2) ?></p>
                                                </div>
                                            </div>

                                            <div class="flex justify-end gap-2 mt-4">
                                                <!-- FIX: class not id, + data-order-number so JS knows which order -->
                                                <button type="button" class="btn-danger cancel-order-btn text-white px-5 py-2.5 rounded-lg text-sm font-medium" data-order-number="<?= htmlspecialchars($order['order_number']) ?>">
                                                    <i class="fas fa-times mr-2"></i>Cancel
                                                </button>

                                                <a href="../Customer/order_details.php?order_number=<?= urlencode($order['order_number']) ?>"
                                                    class="btn-primary text-white px-5 py-2.5 rounded-lg text-sm font-medium inline-flex items-center">
                                                    <i class="fas fa-info-circle mr-2"></i>View Details
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <!-- TO RECEIVE -->
                            <div id="toreceive" class="tab-content space-y-4 hidden">
                                <?php if (empty($toReceiveOrders)): ?>
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="fas fa-shipping-fast"></i>
                                        </div>
                                        <p class="text-gray-500 text-lg font-medium">No orders in transit</p>
                                        <p class="text-gray-400 text-sm mt-1">Orders being delivered will appear here</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($toReceiveOrders as $order): ?>
                                        <div class="order-card p-5 rounded-xl bg-white">
                                            <div class="flex justify-between items-start mb-4">
                                                <div>
                                                    <p class="text-xs text-gray-500 mb-1">Order Number</p>
                                                    <p class="font-semibold text-gray-900"><?= htmlspecialchars($order['order_number']) ?></p>
                                                </div>
                                                <span class="status-badge status-<?= htmlspecialchars($order['order_status']) ?>">
                                                    <?= htmlspecialchars(ucwords(str_replace('_', ' ', $order['order_status']))) ?>
                                                </span>
                                            </div>

                                            <?php
                                            $stmtItems = $db->prepare("
                                                SELECT wri.*, i.item_name
                                                FROM water_refill_items wri
                                                LEFT JOIN inventory i ON wri.product_id = i.item_id
                                                WHERE wri.refill_id = ?
                                            ");
                                            $stmtItems->execute([$order['refill_id']]);
                                            $orderItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
                                            ?>

                                            <div class="order-items-scroll max-h-40 overflow-y-auto mb-4 bg-gray-50 rounded-lg p-3">
                                                <?php foreach ($orderItems as $item): ?>
                                                    <div class="flex justify-between items-start mb-3 last:mb-0">
                                                        <div class="flex-1">
                                                            <p class="text-sm font-medium text-gray-900">
                                                                <?= htmlspecialchars($item['item_name']) ?>
                                                            </p>
                                                            <p class="text-xs text-gray-500 mt-1">
                                                                <?= htmlspecialchars($item['quantity']) ?> × ₱<?= number_format($item['unit_price'], 2) ?>
                                                            </p>
                                                            <p class="text-xs text-blue-600 mt-1">
                                                                <i class="fas fa-user-circle mr-1"></i><?= htmlspecialchars($item['gallon_owner']) ?>
                                                            </p>
                                                        </div>
                                                        <p class="text-sm font-semibold text-gray-900">
                                                            ₱<?= number_format($item['subtotal'], 2) ?>
                                                        </p>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>

                                            <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                                                <div>
                                                    <p class="text-xs text-gray-500">Total Items</p>
                                                    <p class="text-sm font-medium text-gray-700"><?= htmlspecialchars($order['item_count'] ?? 0) ?> item(s)</p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-xs text-gray-500 mb-1">Total Amount</p>
                                                    <p class="text-xl font-bold text-blue-600">₱<?= number_format($order['total_amount'], 2) ?></p>
                                                </div>
                                            </div>

                                            <div class="flex justify-end mt-4">
                                                <a href="../Customer/order_details.php?order_number=<?= urlencode($order['order_number']) ?>"
                                                    class="btn-primary text-white px-5 py-2.5 rounded-lg text-sm font-medium inline-flex items-center">
                                                    <i class="fas fa-map-marker-alt mr-2"></i>Track Delivery
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <!-- COMPLETED -->
                            <div id="completed" class="tab-content space-y-4 hidden">
                                <?php if (empty($completedOrders)): ?>
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <p class="text-gray-500 text-lg font-medium">No completed orders</p>
                                        <p class="text-gray-400 text-sm mt-1">Your order history will appear here</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($completedOrders as $order): ?>
                                        <div class="order-card p-5 rounded-xl bg-white">
                                            <div class="flex justify-between items-start mb-4">
                                                <div>
                                                    <p class="text-xs text-gray-500 mb-1">Order Number</p>
                                                    <p class="font-semibold text-gray-900"><?= htmlspecialchars($order['order_number']) ?></p>
                                                </div>
                                                <span class="status-badge status-delivered">Delivered</span>
                                            </div>

                                            <?php
                                            $stmtItems = $db->prepare("
                                                SELECT wri.*, i.item_name
                                                FROM water_refill_items wri
                                                LEFT JOIN inventory i ON wri.product_id = i.item_id
                                                WHERE wri.refill_id = ?
                                            ");
                                            $stmtItems->execute([$order['refill_id']]);
                                            $orderItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
                                            ?>

                                            <div class="order-items-scroll max-h-40 overflow-y-auto mb-4 bg-gray-50 rounded-lg p-3">
                                                <?php foreach ($orderItems as $item): ?>
                                                    <div class="flex justify-between items-start mb-3 last:mb-0">
                                                        <div class="flex-1">
                                                            <p class="text-sm font-medium text-gray-900">
                                                                <?= htmlspecialchars($item['item_name']) ?>
                                                            </p>
                                                            <p class="text-xs text-gray-500 mt-1">
                                                                <?= htmlspecialchars($item['quantity']) ?> × ₱<?= number_format($item['unit_price'], 2) ?>
                                                            </p>
                                                            <p class="text-xs text-blue-600 mt-1">
                                                                <i class="fas fa-user-circle mr-1"></i><?= htmlspecialchars($item['gallon_owner']) ?>
                                                            </p>
                                                        </div>
                                                        <p class="text-sm font-semibold text-gray-900">
                                                            ₱<?= number_format($item['subtotal'], 2) ?>
                                                        </p>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>

                                            <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                                                <div>
                                                    <p class="text-xs text-gray-500">Total Items</p>
                                                    <p class="text-sm font-medium text-gray-700"><?= htmlspecialchars($order['item_count'] ?? 0) ?> item(s)</p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-xs text-gray-500 mb-1">Total Amount</p>
                                                    <p class="text-xl font-bold text-green-600">₱<?= number_format($order['total_amount'], 2) ?></p>
                                                </div>
                                            </div>

                                            <div class="flex justify-end gap-2 mt-4">
                                                <a href="../Customer/order_details.php?order_number=<?= urlencode($order['order_number']) ?>"
                                                    class="btn-primary text-white px-5 py-2.5 rounded-lg text-sm font-medium inline-flex items-center">
                                                    <i class="fas fa-info-circle mr-2"></i>View Details
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <!-- CANCELLED -->
                            <div id="cancelled" class="tab-content space-y-4 hidden">
                                <?php if (empty($cancelledOrders)): ?>
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="fas fa-ban"></i>
                                        </div>
                                        <p class="text-gray-500 text-lg font-medium">No cancelled orders</p>
                                        <p class="text-gray-400 text-sm mt-1">Cancelled orders will appear here</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($cancelledOrders as $order): ?>
                                        <div class="order-card p-5 rounded-xl bg-white opacity-75">
                                            <div class="flex justify-between items-start mb-4">
                                                <div>
                                                    <p class="text-xs text-gray-500 mb-1">Order Number</p>
                                                    <p class="font-semibold text-gray-900"><?= htmlspecialchars($order['order_number']) ?></p>
                                                </div>
                                                <span class="status-badge status-cancelled">Cancelled</span>
                                            </div>

                                            <?php
                                            $stmtItems = $db->prepare("
                                                SELECT wri.*, i.item_name
                                                FROM water_refill_items wri
                                                LEFT JOIN inventory i ON wri.product_id = i.item_id
                                                WHERE wri.refill_id = ?
                                            ");
                                            $stmtItems->execute([$order['refill_id']]);
                                            $orderItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
                                            ?>

                                            <div class="order-items-scroll max-h-40 overflow-y-auto mb-4 bg-gray-50 rounded-lg p-3">
                                                <?php foreach ($orderItems as $item): ?>
                                                    <div class="flex justify-between items-start mb-3 last:mb-0">
                                                        <div class="flex-1">
                                                            <p class="text-sm font-medium text-gray-900">
                                                                <?= htmlspecialchars($item['item_name']) ?>
                                                            </p>
                                                            <p class="text-xs text-gray-500 mt-1">
                                                                <?= htmlspecialchars($item['quantity']) ?> × ₱<?= number_format($item['unit_price'], 2) ?>
                                                            </p>
                                                            <p class="text-xs text-blue-600 mt-1">
                                                                <i class="fas fa-user-circle mr-1"></i><?= htmlspecialchars($item['gallon_owner']) ?>
                                                            </p>
                                                        </div>
                                                        <p class="text-sm font-semibold text-gray-900">
                                                            ₱<?= number_format($item['subtotal'], 2) ?>
                                                        </p>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>

                                            <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                                                <div>
                                                    <p class="text-xs text-gray-500">Total Items</p>
                                                    <p class="text-sm font-medium text-gray-700"><?= htmlspecialchars($order['item_count'] ?? 0) ?> item(s)</p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-xs text-gray-500 mb-1">Total Amount</p>
                                                    <p class="text-xl font-bold text-gray-600">₱<?= number_format($order['total_amount'], 2) ?></p>
                                                </div>
                                            </div>

                                            <div class="flex justify-end mt-4">
                                                <a href="../Customer/order_details.php?order_number=<?= urlencode($order['order_number']) ?>"
                                                    class="btn-primary text-white px-5 py-2.5 rounded-lg text-sm font-medium inline-flex items-center">
                                                    <i class="fas fa-info-circle mr-2"></i>View Details
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <script>
            // Tab switching
            document.addEventListener('DOMContentLoaded', () => {
                const tabButtons = document.querySelectorAll('.tab-button');
                const tabContents = document.querySelectorAll('.tab-content');

                tabButtons.forEach(button => {
                    button.addEventListener('click', () => {
                        const tab = button.getAttribute('data-tab');

                        tabButtons.forEach(btn => btn.classList.remove('active'));
                        tabContents.forEach(content => content.classList.add('hidden'));

                        button.classList.add('active');
                        document.getElementById(tab).classList.remove('hidden');
                    });
                });

                // default open tab
                document.querySelector('[data-tab="topay"]').click();
            });

            // FIXED: Cancel - works for EVERY order card, not just the first one
            // Uses class + delegation, correct URL, shows REAL error
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.cancel-order-btn');
                if (!btn) return;
                e.preventDefault();

                const orderNumber = btn.dataset.orderNumber;
                if (!orderNumber) {
                    Swal.fire('Error!', 'Missing order number on button.', 'error');
                    return;
                }

                Swal.fire({
                    title: 'Cancel Order',
                    html: `
                        <p>Are you sure you want to cancel order #${orderNumber}?</p>
                        <input type="text" id="cancelReason" class="swal2-input" placeholder="Enter cancellation reason">
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, cancel it!',
                    cancelButtonText: 'No, keep it',
                    preConfirm: () => {
                        const reason = Swal.getPopup().querySelector('#cancelReason').value;
                        if (!reason || reason.trim() === '') {
                            Swal.showValidationMessage('Cancellation reason is required');
                            return false;
                        }
                        return reason;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const reason = result.value;

                        // FIX: was 'Customer/cancel_order.php' (404). Now just 'cancel_order.php' because we are already in Customer/
                        fetch('../Order/customer_cancel_order.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ order_number: orderNumber, reason: reason })
                        })
                        .then(async (response) => {
                            const text = await response.text();
                            console.log('Cancel raw:', text);
                            try { return JSON.parse(text); }
                            catch (err) { throw new Error('Server returned HTTP ' + response.status + ' - not JSON. Response: ' + text.substring(0, 300)); }
                        })
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Cancelled!', data.message, 'success').then(() => { location.reload(); });
                            } else {
                                Swal.fire('Error!', data.message || 'Failed to cancel order.', 'error');
                            }
                        })
                        .catch(error => {
                            Swal.fire('Error!', 'Real error: ' + error.message, 'error');
                            console.error('Error:', error);
                        });
                    }
                });
            });
        </script>

    </body>

    </html>