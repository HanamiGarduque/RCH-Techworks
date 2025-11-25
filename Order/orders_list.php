<?php
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('admin'); // only admins can access

require_once '../Database/db_connection.php';


$database = new Database();
$db = $database->getConnect();
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

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #3b82f6;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #2563eb;
        }

        /* Table hover effect */
        tbody tr {
            transition: all 0.2s ease;
        }

        tbody tr:hover {
            background-color: #eff6ff !important;
        }
    </style>
</head>

<body class="bg-gray-50 overflow-auto">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <script src="../Admin/includes/sidebar.js"></script>

        <!-- Main Content -->
        <div class="flex-1 ml-20 group-hover:ml-64 transition-all duration-300 ease-in-out">
            <div class="flex flex-col h-screen">
                <!-- Header -->
                <?php
                $pageTitle = "Order Management";
                $pageSubtitle = "View and manage customer orders and details.";
                include '../Admin/includes/header.php';
                ?>

                <div class="m-3 flex flex-col flex-1 overflow-auto">
                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-white border-l-4 border-blue-500 rounded-lg shadow-md p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-600 text-sm font-medium mb-1">Total Orders</p>
                                    <h3 class="text-3xl font-bold text-blue-600">4</h3>
                                </div>
                                <div class="bg-blue-100 rounded-full p-4">
                                    <i class="fas fa-shopping-cart text-2xl text-blue-500"></i>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white border-l-4 border-green-500 rounded-lg shadow-md p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-600 text-sm font-medium mb-1">Completed</p>
                                    <h3 class="text-3xl font-bold text-green-600">0</h3>
                                </div>
                                <div class="bg-green-100 rounded-full p-4">
                                    <i class="fas fa-check-circle text-2xl text-green-500"></i>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white border-l-4 border-yellow-500 rounded-lg shadow-md p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-600 text-sm font-medium mb-1">Pending</p>
                                    <h3 class="text-3xl font-bold text-yellow-600">4</h3>
                                </div>
                                <div class="bg-yellow-100 rounded-full p-4">
                                    <i class="fas fa-clock text-2xl text-yellow-500"></i>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white border-l-4 border-blue-500 rounded-lg shadow-md p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-600 text-sm font-medium mb-1">This Month</p>
                                    <h3 class="text-3xl font-bold text-blue-600">4</h3>
                                </div>
                                <div class="bg-blue-100 rounded-full p-4">
                                    <i class="fas fa-calendar-alt text-2xl text-blue-500"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Search and Filter Section -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <div class="flex flex-col lg:flex-row lg:flex-wrap gap-4">

                            <!-- Search Bar -->
                            <div class="flex-1 min-w-full lg:min-w-[350px]">
                                <label class="text-sm font-medium text-gray-700 mb-2 block">
                                    <i class="fas fa-search mr-2 text-blue-500"></i>Search Orders
                                </label>
                                <div class="relative">
                                    <i class="fas fa-search absolute left-4 top-3.5 text-gray-400"></i>
                                    <input
                                        type="text"
                                        id="searchInput"
                                        placeholder="Search by Order Number, Refill ID, Status..."
                                        class="w-full pl-12 pr-10 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 transition-all"
                                        onkeyup="searchTable()">
                                    <button onclick="document.getElementById('searchInput').value = ''; searchTable();"
                                        class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 transition">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
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


                    <!-- Orders Table -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden flex flex-col flex-1">
                        <!-- Scroll wrapper -->
                        <div class="overflow-x-auto overflow-y-auto flex-1">
                            <table id="ordersTable" class="min-w-full text-sm text-left text-gray-700">
                                <thead class="bg-blue-50 border-b border-gray-200 sticky top-0 z-10">
                                    <tr>
                                        <th class="px-6 py-4 font-semibold text-center cursor-pointer min-w-[100px]" onclick="sortTable(0)" title="Refill ID">
                                            Refill ID ⬍
                                        </th>
                                        <th class="px-6 py-4 font-semibold text-center cursor-pointer min-w-[180px]" onclick="sortTable(1)" title="Order Number">
                                            Order Number ⬍
                                        </th>
                                        <th class="px-6 py-4 font-semibold text-center cursor-pointer min-w-[120px]" onclick="sortTable(2)" title="Date">
                                            Date ⬍
                                        </th>
                                        <th class="px-6 py-4 font-semibold text-center min-w-[100px]" title="Status">
                                            Status
                                        </th>
                                        <th class="px-6 py-4 font-semibold text-center min-w-[120px]" title="Actions">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <!-- ROW 1 -->
                                    <tr class="border-b divide-x divide-gray-200 hover:bg-gray-50 order-row">
                                        <td class="px-6 py-4 text-center">1</td>
                                        <td class="px-6 py-4 text-center font-semibold text-gray-800">ORD-110325001</td>
                                        <td class="px-6 py-4 text-center text-gray-600">Nov 3, 2025</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                                                <i class="fas fa-clock"></i>
                                                Pending
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <a href="../Order/order_details.php?order_id=ORD-110325001&status=pending"
                                                class="inline-flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition hover:scale-105">
                                                <i class="bi bi-eye"></i>
                                                View Details
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- ROW 2 -->
                                    <tr class="border-b divide-x divide-gray-200 hover:bg-gray-50 order-row">
                                        <td class="px-6 py-4 text-center">2</td>
                                        <td class="px-6 py-4 text-center font-semibold text-gray-800">ORD-110325002</td>
                                        <td class="px-6 py-4 text-center text-gray-600">Nov 3, 2025</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                                                <i class="fas fa-clock"></i>
                                                Pending
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <a href="../Order/order_details.php?order_id=ORD-110325002&status=pending"
                                                class="inline-flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition hover:scale-105">
                                                <i class="bi bi-eye"></i>
                                                View Details
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- ROW 3 -->
                                    <tr class="border-b divide-x divide-gray-200 hover:bg-gray-50 order-row">
                                        <td class="px-6 py-4 text-center">3</td>
                                        <td class="px-6 py-4 text-center font-semibold text-gray-800">ORD-110325003</td>
                                        <td class="px-6 py-4 text-center text-gray-600">Nov 3, 2025</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                                                <i class="fas fa-clock"></i>
                                                Pending
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <a href="../Order/../Order/order_details.php?order_id=ORD-110325003&status=pending"
                                                class="inline-flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition hover:scale-105">
                                                <i class="bi bi-eye"></i>
                                                View Details
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- ROW 4 -->
                                    <tr class="border-b divide-x divide-gray-200 hover:bg-gray-50 order-row">
                                        <td class="px-6 py-4 text-center">4</td>
                                        <td class="px-6 py-4 text-center font-semibold text-gray-800">ORD-110325004</td>
                                        <td class="px-6 py-4 text-center text-gray-600">Nov 3, 2025</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                                                <i class="fas fa-clock"></i>
                                                Pending
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <a href="../Order/order_details.php?order_id=ORD-110325004&status=pending"
                                                class="inline-flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition hover:scale-105">
                                                <i class="bi bi-eye"></i>
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Empty State -->
                            <div id="emptyState" class="hidden text-center py-16">
                                <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                                <h3 class="text-xl font-semibold text-gray-600 mb-2">No Orders Found</h3>
                                <p class="text-gray-500">Try adjusting your search or filter criteria</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Enhanced search functionality
        function searchTable() {
            const input = document.getElementById("searchInput");
            const filter = input.value.toLowerCase().trim();
            const rows = document.querySelectorAll(".order-row");
            let visibleCount = 0;

            rows.forEach(row => {
                const cells = row.querySelectorAll("td");
                let match = false;

                if (filter === "") {
                    row.style.display = "";
                    visibleCount++;
                    return;
                }

                cells.forEach(cell => {
                    if (cell.textContent.toLowerCase().includes(filter)) {
                        match = true;
                    }
                });

                if (match) {
                    row.style.display = "";
                    visibleCount++;
                } else {
                    row.style.display = "none";
                }
            });

            // Show/hide empty state
            const emptyState = document.getElementById('emptyState');
            const table = document.querySelector('#ordersTable tbody');

            if (visibleCount === 0) {
                emptyState.classList.remove('hidden');
                table.style.display = 'none';
            } else {
                emptyState.classList.add('hidden');
                table.style.display = '';
            }
        }

        // Sorting functionality
        let sortDirection = {};

        function sortTable(columnIndex) {
            const table = document.getElementById("ordersTable");
            const tbody = table.querySelector("tbody");
            const rows = Array.from(tbody.querySelectorAll("tr.order-row"));

            // Initialize sort direction for this column
            if (!sortDirection[columnIndex]) {
                sortDirection[columnIndex] = 'asc';
            } else {
                sortDirection[columnIndex] = sortDirection[columnIndex] === 'asc' ? 'desc' : 'asc';
            }

            const direction = sortDirection[columnIndex];

            rows.sort((a, b) => {
                const aValue = a.cells[columnIndex].textContent.trim();
                const bValue = b.cells[columnIndex].textContent.trim();

                // Handle numeric comparison for Refill ID
                if (columnIndex === 0) {
                    return direction === 'asc' ?
                        parseInt(aValue) - parseInt(bValue) :
                        parseInt(bValue) - parseInt(aValue);
                }

                // String comparison for other columns
                return direction === 'asc' ?
                    aValue.localeCompare(bValue) :
                    bValue.localeCompare(aValue);
            });

            // Re-append sorted rows
            rows.forEach(row => tbody.appendChild(row));

            // Update sort indicators
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

        // Date range filtering with validation
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

            Swal.fire({
                icon: 'success',
                title: 'Filter Applied',
                text: 'Orders filtered by date range',
                timer: 1500,
                showConfirmButton: false
            });

            // Add your date filtering logic here
            console.log('Filtering from:', fromDate, 'to:', toDate);
        });

        // Clear search button functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            if (this.value === '') {
                searchTable();
            }
        });

        // Add loading state to view details buttons
        document.querySelectorAll('a[href^="../Order/order_details.php"]').forEach(link => {
            link.addEventListener('click', function(e) {
                const btn = this;
                const originalContent = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Loading...';
                btn.style.pointerEvents = 'none';
            });
        });
    </script>
</body>

</html>