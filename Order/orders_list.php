<?php
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('admin'); // only admins can access

require_once '../Database/db_connection.php';

$database = new Database();
$db = $database->getConnect();

// Total orders
$totalStmt = $db->query("SELECT COUNT(refill_id) AS total_orders FROM water_refills");
$totalOrders = $totalStmt->fetch(PDO::FETCH_ASSOC)['total_orders'];

// Completed orders
$completedStmt = $db->query("SELECT COUNT(refill_id) AS completed FROM water_refills WHERE order_status = 'delivered'");
$completedOrders = $completedStmt->fetch(PDO::FETCH_ASSOC)['completed'];

// Pending orders
$pendingStmt = $db->query("SELECT COUNT(refill_id) AS pending FROM water_refills WHERE order_status = 'pending'");
$pendingOrders = $pendingStmt->fetch(PDO::FETCH_ASSOC)['pending'];

// Orders this month
$thisMonthStmt = $db->query("SELECT COUNT(refill_id) AS this_month FROM water_refills WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
$thisMonthOrders = $thisMonthStmt->fetch(PDO::FETCH_ASSOC)['this_month'];

$stats = [
    'total_orders' => $totalOrders,
    'completed' => $completedOrders,
    'pending' => $pendingOrders,
    'this_month' => $thisMonthOrders
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RCH Water - Customer Orders</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .page-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .content-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .table-scroll-container {
            overflow-y: auto;
            overflow-x: auto;
            flex: 1;
        }

        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
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

        tbody tr {
            transition: all 0.2s ease;
        }

        tbody tr:hover {
            background-color: #eff6ff !important;
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

                <!-- Content Wrapper -->
                <div class="content-wrapper p-4">
                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4 flex-shrink-0">
                        <div class="bg-white border-l-4 border-blue-500 rounded-lg shadow-md p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-700 text-sm font-medium mb-1">Total Orders</p>
                                    <h3 class="text-3xl font-bold text-blue-600"><?= $stats['total_orders'] ?></h3>
                                </div>
                                <div class="bg-blue-100 rounded-full p-4">
                                    <i class="fas fa-shopping-cart text-2xl text-blue-500"></i>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white border-l-4 border-green-500 rounded-lg shadow-md p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-700 text-sm font-medium mb-1">Completed</p>
                                    <h3 class="text-3xl font-bold text-green-600"><?= $stats['completed'] ?></h3>
                                </div>
                                <div class="bg-green-100 rounded-full p-4">
                                    <i class="fas fa-check-circle text-2xl text-green-500"></i>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white border-l-4 border-yellow-500 rounded-lg shadow-md p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-700 text-sm font-medium mb-1">Pending</p>
                                    <h3 class="text-3xl font-bold text-yellow-600"><?= $stats['pending'] ?></h3>
                                </div>
                                <div class="bg-yellow-100 rounded-full p-4">
                                    <i class="fas fa-clock text-2xl text-yellow-500"></i>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white border-l-4 border-blue-500 rounded-lg shadow-md p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-700 text-sm font-medium mb-1">This Month</p>
                                    <h3 class="text-3xl font-bold text-blue-600"><?= $stats['this_month'] ?></h3>
                                </div>
                                <div class="bg-blue-100 rounded-full p-4">
                                    <i class="fas fa-calendar-alt text-2xl text-blue-500"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Table Container -->
                    <div class="bg-white rounded-lg shadow-lg flex flex-col overflow-hidden flex-1">
                        <!-- Search and Filter Section - Fixed -->
                        <div class="p-6 border-b border-gray-200 flex-shrink-0">
                            <h2 class="text-xl font-semibold text-blue-600 mb-4 flex items-center">
                                <i class="fas fa-list mr-2"></i>Customer Orders
                            </h2>

                            <div class="flex flex-col lg:flex-row lg:flex-wrap gap-4">
                                <!-- Search Bar -->
                                <div class="flex-1 min-w-full lg:min-w-[300px]">
                                    <label class="text-sm font-medium text-gray-700 mb-2 block">
                                        <i class="fas fa-search mr-2 text-blue-500"></i>Search Orders
                                    </label>
                                    <div class="relative">
                                        <i class="fas fa-search absolute left-4 top-3.5 text-gray-400"></i>
                                        <input
                                            type="text"
                                            id="searchInput"
                                            placeholder="Search by Order Number, Customer Name..."
                                            class="w-full pl-10 pr-10 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                                        <button onclick="document.getElementById('searchInput').value = ''; filterOrders();"
                                            class="absolute right-3 top-3.5 text-gray-400 hover:text-gray-700 transition">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Status Filter -->
                                <div class="w-full sm:w-auto lg:w-[200px]">
                                    <label class="text-sm font-medium text-gray-700 mb-2 block">
                                        <i class="fas fa-filter mr-2 text-blue-500"></i>Status
                                    </label>
                                    <select 
                                        id="statusFilter" 
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-white">
                                        <option value="all">All Status</option>
                                        <option value="pending" selected>Pending</option>
                                        <option value="assigned">Assigned</option>
                                        <option value="pickup_in_progress">Pickup In Progress</option>
                                        <option value="pickup_delayed">Pickup Delayed</option>
                                        <option value="picked_up">Picked Up</option>
                                        <option value="on_delivery">On Delivery</option>
                                        <option value="delivery_delayed">Delivery Delayed</option>
                                        <option value="delivered">Delivered</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>

                                <!-- Date Range Filter -->
                                <div class="flex-1 lg:w-[380px]">
                                    <label class="text-sm font-medium text-gray-700 mb-2 block">
                                        <i class="fas fa-calendar mr-2 text-blue-500"></i>Date Range
                                    </label>
                                    <div class="flex flex-col sm:flex-row gap-2">
                                        <input
                                            type="date"
                                            id="fromDate"
                                            class="flex-1 px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 transition-all">
                                        <span class="flex items-center justify-center text-gray-500 font-medium sm:px-2">to</span>
                                        <input
                                            type="date"
                                            id="toDate"
                                            class="flex-1 px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 transition-all">
                                    </div>
                                </div>

                                <!-- Apply Filter Button -->
                                <div class="w-full sm:w-auto lg:w-32 flex items-end">
                                    <button
                                        id="applyDateRange"
                                        class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 rounded-lg transition-all shadow-md hover:shadow-lg">
                                        <i class="fas fa-filter mr-2"></i>Apply
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Table - Scrollable -->
                        <div class="table-scroll-container">
                            <table id="ordersTable" class="min-w-full text-sm text-left text-gray-700">
                                <thead class="bg-blue-50 border-b border-gray-200 sticky top-0 z-10">
                                    <tr>
                                        <th class="px-6 py-4 text-center font-semibold cursor-pointer min-w-[80px]" onclick="sortTable(0)">#</th>
                                        <th class="px-6 py-4 text-center font-semibold cursor-pointer min-w-[150px]" onclick="sortTable(1)">Order Number ⬍</th>
                                        <th class="px-6 py-4 text-center font-semibold cursor-pointer min-w-[150px]" onclick="sortTable(2)">Customer Name ⬍</th>
                                        <th class="px-6 py-4 text-center font-semibold min-w-[200px]">Address</th>
                                        <th class="px-6 py-4 text-center font-semibold cursor-pointer min-w-[130px]" onclick="sortTable(4)">Date ⬍</th>
                                        <th class="px-6 py-4 text-center font-semibold cursor-pointer min-w-[120px]" onclick="sortTable(5)">Amount ⬍</th>
                                        <th class="px-6 py-4 text-center font-semibold min-w-[150px]">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT 
                                        r.refill_id, 
                                        r.order_number, 
                                        r.pickup_datetime, 
                                        r.delivery_datetime, 
                                        r.order_status, 
                                        r.total_amount,
                                        r.user_id,
                                        u.name as customer_name,
                                        u.address as customer_address
                                    FROM water_refills r
                                    LEFT JOIN users u ON r.user_id = u.user_id
                                    ORDER BY r.created_at DESC";
                                    $stmt = $db->prepare($query);
                                    $stmt->execute();
                                    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                    if (!empty($orders)):
                                        foreach ($orders as $index => $order):
                                    ?>
                                            <tr class="border-b divide-x divide-gray-200 hover:bg-gray-50 order-row" 
                                                data-order-number="<?= htmlspecialchars($order['order_number']) ?>"
                                                data-status="<?= htmlspecialchars($order['order_status']) ?>"
                                                data-date="<?= htmlspecialchars($order['pickup_datetime']) ?>"
                                                data-customer="<?= htmlspecialchars($order['customer_name'] ?? 'Unknown') ?>"
                                                data-amount="<?= htmlspecialchars($order['total_amount']) ?>">
                                                <td class="px-6 py-4 text-center"><?= $index + 1 ?></td>
                                                <td class="px-6 py-4 text-center font-semibold text-gray-700"><?= htmlspecialchars($order['order_number']) ?></td>
                                                <td class="px-6 py-4 text-center text-gray-700">
                                                    <?= htmlspecialchars($order['customer_name'] ?? 'Unknown') ?>
                                                </td>
                                                <td class="px-6 py-4 text-gray-700">
                                                    <?= htmlspecialchars($order['customer_address'] ?? 'No address') ?>
                                                </td>
                                                <td class="px-6 py-4 text-center text-gray-700">
                                                    <?= date('M d, Y', strtotime($order['pickup_datetime'])) ?>
                                                </td>
                                                <td class="px-6 py-4 text-center font-semibold text-green-600">
                                                    ₱<?= number_format($order['total_amount'], 2) ?>
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <a href="../Order/order_details.php?order_number=<?= urlencode($order['order_number']) ?>"
                                                        class="inline-flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition hover:scale-105">
                                                        <i class="bi bi-eye"></i>
                                                        View Details
                                                    </a>
                                                </td>
                                            </tr>
                                    <?php
                                        endforeach;
                                    else:
                                    ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-12 text-gray-500">
                                                <i class="fas fa-inbox text-6xl mb-4 block"></i>
                                                <div class="text-lg font-medium">No Orders Found</div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Empty State (Hidden by default, shown when no results) -->
                        <div id="emptyState" class="hidden text-center py-16">
                            <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                            <h3 class="text-xl font-semibold text-gray-700 mb-2">No Orders Found</h3>
                            <p class="text-gray-500">Try adjusting your search or filter criteria</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="..Admin/includes/admin_toast.js"></script>

    <script>
        // Status color mapping (keeping for reference if needed elsewhere)
        const statusColors = {
            'pending': { bg: 'bg-yellow-100', text: 'text-yellow-700', icon: 'fa-clock' },
            'assigned': { bg: 'bg-blue-100', text: 'text-blue-700', icon: 'fa-user-check' },
            'pickup_in_progress': { bg: 'bg-purple-100', text: 'text-purple-700', icon: 'fa-truck-pickup' },
            'pickup_delayed': { bg: 'bg-orange-100', text: 'text-orange-700', icon: 'fa-exclamation-triangle' },
            'picked_up': { bg: 'bg-indigo-100', text: 'text-indigo-700', icon: 'fa-box' },
            'on_delivery': { bg: 'bg-cyan-100', text: 'text-cyan-700', icon: 'fa-shipping-fast' },
            'delivery_delayed': { bg: 'bg-red-100', text: 'text-red-700', icon: 'fa-exclamation-circle' },
            'delivered': { bg: 'bg-green-100', text: 'text-green-700', icon: 'fa-check-circle' },
            'cancelled': { bg: 'bg-gray-100', text: 'text-gray-700', icon: 'fa-times-circle' }
        };

        // Apply initial filter on page load
        document.addEventListener("DOMContentLoaded", function() {
            filterOrders(); // Apply pending filter by default
        });

        // Dynamic search and filter
        function filterOrders() {
            const searchValue = document.getElementById('searchInput').value.toLowerCase().trim();
            const statusFilter = document.getElementById('statusFilter').value;
            const fromDate = document.getElementById('fromDate').value;
            const toDate = document.getElementById('toDate').value;
            
            const rows = document.querySelectorAll('.order-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const orderNumber = row.dataset.orderNumber.toLowerCase();
                const status = row.dataset.status.toLowerCase();
                const customer = row.dataset.customer.toLowerCase();
                const orderDate = new Date(row.dataset.date);
                
                let matchesSearch = true;
                let matchesStatus = true;
                let matchesDate = true;

                // Search filter - customer name and order number
                if (searchValue) {
                    matchesSearch = orderNumber.includes(searchValue) || 
                                  customer.includes(searchValue);
                }

                // Status filter
                if (statusFilter !== 'all') {
                    matchesStatus = status === statusFilter;
                }

                // Date range filter
                if (fromDate && toDate) {
                    const from = new Date(fromDate);
                    const to = new Date(toDate);
                    to.setHours(23, 59, 59, 999);
                    matchesDate = orderDate >= from && orderDate <= to;
                } else if (fromDate) {
                    const from = new Date(fromDate);
                    matchesDate = orderDate >= from;
                } else if (toDate) {
                    const to = new Date(toDate);
                    to.setHours(23, 59, 59, 999);
                    matchesDate = orderDate <= to;
                }

                if (matchesSearch && matchesStatus && matchesDate) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Show/hide empty state
            const emptyState = document.getElementById('emptyState');
            const tbody = document.querySelector('#ordersTable tbody');
            
            if (visibleCount === 0) {
                tbody.style.display = 'none';
                emptyState.classList.remove('hidden');
            } else {
                tbody.style.display = '';
                emptyState.classList.add('hidden');
            }
        }

        // Real-time search
        document.getElementById('searchInput').addEventListener('input', filterOrders);
        
        // Status filter change
        document.getElementById('statusFilter').addEventListener('change', filterOrders);

        // Apply date range filter
        document.getElementById('applyDateRange').addEventListener('click', function() {
            const fromDate = document.getElementById('fromDate').value;
            const toDate = document.getElementById('toDate').value;

            if (!fromDate && !toDate) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Date Selected',
                    text: 'Please select a date range to filter',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }

            if (fromDate && toDate && new Date(fromDate) > new Date(toDate)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Date Range',
                    text: 'Start date must be before end date',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }

            filterOrders();
            
            Swal.fire({
                icon: 'success',
                title: 'Filter Applied',
                text: 'Orders filtered by date range',
                timer: 1500,
                showConfirmButton: false
            });
        });

        // Sorting functionality
        let sortDirection = {};

        function sortTable(columnIndex) {
            const table = document.getElementById("ordersTable");
            const tbody = table.querySelector("tbody");
            const rows = Array.from(tbody.querySelectorAll("tr.order-row"));

            if (!sortDirection[columnIndex]) {
                sortDirection[columnIndex] = 'asc';
            } else {
                sortDirection[columnIndex] = sortDirection[columnIndex] === 'asc' ? 'desc' : 'asc';
            }

            const direction = sortDirection[columnIndex];

            rows.sort((a, b) => {
                let aValue, bValue;

                // Column 0: Row number (index)
                if (columnIndex === 0) {
                    aValue = parseInt(a.cells[columnIndex].textContent.trim());
                    bValue = parseInt(b.cells[columnIndex].textContent.trim());
                    return direction === 'asc' ? aValue - bValue : bValue - aValue;
                }

                // Column 1: Order Number (string)
                if (columnIndex === 1) {
                    aValue = a.cells[columnIndex].textContent.trim();
                    bValue = b.cells[columnIndex].textContent.trim();
                    return direction === 'asc' ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue);
                }

                // Column 2: Customer Name (string)
                if (columnIndex === 2) {
                    aValue = a.dataset.customer;
                    bValue = b.dataset.customer;
                    return direction === 'asc' ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue);
                }

                // Column 4: Date
                if (columnIndex === 4) {
                    aValue = new Date(a.dataset.date);
                    bValue = new Date(b.dataset.date);
                    return direction === 'asc' ? aValue - bValue : bValue - aValue;
                }

                // Column 5: Amount (numeric)
                if (columnIndex === 5) {
                    aValue = parseFloat(a.dataset.amount);
                    bValue = parseFloat(b.dataset.amount);
                    return direction === 'asc' ? aValue - bValue : bValue - aValue;
                }

                // Default string comparison
                aValue = a.cells[columnIndex].textContent.trim();
                bValue = b.cells[columnIndex].textContent.trim();
                return direction === 'asc' ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue);
            });

            rows.forEach(row => tbody.appendChild(row));
            updateSortIndicators(columnIndex, direction);
        }

        function updateSortIndicators(activeColumn, direction) {
            const headers = document.querySelectorAll('#ordersTable thead th[onclick]');
            headers.forEach((header, index) => {
                const text = header.textContent.replace(/[⬍▲▼]/g, '').trim();
                if (index === activeColumn) {
                    header.textContent = text + (direction === 'asc' ? ' ▲' : ' ▼');
                } else {
                    header.textContent = text + ' ⬍';
                }
            });
        }
    </script>
</body>

</html>