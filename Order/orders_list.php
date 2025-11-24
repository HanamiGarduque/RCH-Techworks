<?php
// require_once '../check_session.php';
require_once '../Database/db_connection.php';

// if (!isAdmin()) {
//     header("Location: " . dirname($_SERVER['PHP_SELF']) . "/index.php");
//     exit();
// }

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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
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
                    <!-- insert main content here -->

                    <!DOCTYPE html>
                    <html lang="en">

                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>Orders Page</title>
                        <script src="https://cdn.tailwindcss.com"></script>
                        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
                    </head>

                    <body class="bg-gray-100">
                        <div class="w-full mx-auto p-8">
                            <!-- Header -->
                            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                                <!-- Search Bar -->
                                <div class="mb-6">
                                    <div class="flex-1 relative">
                                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                                        <input
                                            type="text"
                                            id="searchInput"
                                            placeholder="Search by Gallon Owner, Gallon Type, Gallon ID, Status..."
                                            class="w-full pl-10 pr-4 py-2 font-light border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder:text-sm"
                                            style="font-style: italic;"
                                            oninput="this.style.fontStyle = this.value ? 'normal' : 'italic';"
                                            onkeyup="searchTable()">
                                    </div>
                                </div>

                                <!-- Date Range -->
                                <div class="bg-white rounded-lg border border-gray-200 p-6">
                                    <h3 class="text-gray-600 font-semibold mb-4">Date Range</h3>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-gray-600 text-sm block mb-2">From:</label>
                                            <input
                                                type="date"
                                                id="fromDate"
                                                placeholder="DD/MM/YYYY"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="text-gray-600 text-sm block mb-2">To:</label>
                                            <input
                                                type="date"
                                                id="toDate"
                                                placeholder="DD/MM/YYYY"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                    </div>
                                    <button
                                        id="applyDateRange"
                                        class="mt-6 w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 rounded-lg transition">
                                        Done
                                    </button>
                                </div>
                            </div>

                            <!-- Orders Table -->
                            <div class="bg-white rounded-lg shadow-md p-6">
                                <table class="w-full" id="ordersTableWrapper">
                                    <thead>
                                        <tr class="border-b-2 border-gray-200">
                                            <th class="py-4 px-6 font-semibold text-gray-600 text-center w-32">
                                                Refill ID
                                            </th>
                                            <th class="py-4 px-6 font-semibold text-gray-600 text-center w-64">
                                                Order Number
                                            </th>
                                            <th class="py-4 px-6 font-semibold text-gray-800 text-right-aligned w-40">
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        <!-- ROW 1 -->
                                        <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                                            <td class="py-4 px-6 text-center text-gray-800">
                                                1
                                            </td>
                                            <td class="py-4 px-6 text-center text-gray-800 font-semibold">
                                                ORD-110325001
                                            </td>
                                            <td class="py-4 px-6 text-right">
                                                <a href="order_details.php?order_id=ORD-110325001&status=pending"
                                                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium inline-flex items-center gap-2">
                                                    <i class="bi bi-eye"></i>
                                                    View Details
                                                </a>

                                            </td>
                                        </tr>

                                        <!-- ROW 2 -->
                                        <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                                            <td class="py-4 px-6 text-center text-center text-gray-800">
                                                2
                                            </td>
                                            <td class="py-4 px-6 text-center text-gray-800 font-semibold">
                                                ORD-110325002
                                            </td>
                                            <td class="py-4 px-6 text-right">
                                                <a href="order_details.php?order_id=ORD-110325001&status=pending"
                                                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium inline-flex items-center gap-2">
                                                    <i class="bi bi-eye"></i>
                                                    View Details
                                                </a>
                                            </td>
                                        </tr>

                                        <!-- ROW 3 -->
                                        <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                                            <td class="py-4 px-6 text-center text-gray-800">
                                                3
                                            </td>
                                            <td class="py-4 px-6 text-center text-gray-800 font-semibold">
                                                ORD-110325003
                                            </td>
                                            <td class="py-4 px-6 text-right">
                                                <a href="order_details.php?order_id=ORD-110325001&status=pending"
                                                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium inline-flex items-center gap-2">
                                                    <i class="bi bi-eye"></i>
                                                    View Details
                                                </a>
                                            </td>
                                        </tr>

                                        <!-- ROW 4 -->
                                        <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                                            <td class="py-4 px-6 text-center text-gray-800">
                                                4
                                            </td>
                                            <td class="py-4 px-6 text-center text-gray-800 font-semibold">
                                                ORD-110325004
                                            </td>
                                            <td class="py-4 px-6 text-right">
                                                <a href="order_details.php?order_id=ORD-110325001&status=pending"
                                                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium inline-flex items-center gap-2">
                                                    <i class="bi bi-eye"></i>
                                                    View Details
                                                </a>
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>


                            <!-- Modal for Order Details -->
                            <div id="detailsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                                <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full">
                                    <div class="flex justify-between items-center mb-4">
                                        <h2 class="text-2xl font-semi-bold text-gray-600">Order Details</h2>
                                        <button id="closeModal" class="text-gray-500 hover:text-gray-800">
                                            <i class="bi bi-x-lg text-2xl"></i>
                                        </button>
                                    </div>
                                    <p class="text-gray-600 mb-6">Order Number: <span id="modalOrderNumber" class="font-semibold text-gray-800"></span></p>
                                    <div class="flex gap-4">
                                        <button class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg font-medium transition">Cancel</button>
                                        <button class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition">Confirm</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                            // Search functionality
                            function searchTable() {
                                const input = document.getElementById("searchInput");
                                const filter = input.value.toLowerCase();
                                const rows = document.querySelectorAll("#ordersTable .order-row");

                                rows.forEach(row => {
                                    const cells = row.querySelectorAll("td");
                                    let match = false;

                                    // if search is empty, show all rows
                                    if (filter === "") {
                                        row.style.display = "";
                                        return;
                                    }

                                    cells.forEach(cell => {
                                        if (cell.textContent.toLowerCase().includes(filter)) {
                                            match = true;
                                        }
                                    });

                                    row.style.display = match ? "" : "none";
                                });
                            }


                            // View Details button functionality
                            const viewDetailsButtons = document.querySelectorAll('.view-details-btn');
                            const detailsModal = document.getElementById('detailsModal');
                            const closeModalBtn = document.getElementById('closeModal');
                            const modalOrderNumber = document.getElementById('modalOrderNumber');

                            viewDetailsButtons.forEach(btn => {
                                btn.addEventListener('click', () => {
                                    const orderNumber = btn.getAttribute('data-order');
                                    modalOrderNumber.textContent = orderNumber;
                                    detailsModal.classList.remove('hidden');
                                });
                            });

                            closeModalBtn.addEventListener('click', () => {
                                detailsModal.classList.add('hidden');
                            });

                            // Close modal when clicking outside
                            detailsModal.addEventListener('click', (e) => {
                                if (e.target === detailsModal) {
                                    detailsModal.classList.add('hidden');
                                }
                            });

                            // Date range filtering (placeholder for future functionality)
                            const fromDate = document.getElementById('fromDate');
                            const toDate = document.getElementById('toDate');

                            fromDate.addEventListener('change', () => {
                                console.log('Date range filter applied');
                            });

                            toDate.addEventListener('change', () => {
                                console.log('Date range filter applied');
                            });
                        </script>
                    </body>

                    </html>

                </div>
            </div>

        </div>

    </div>
    </div>
    <script>
        //for smooth page transition
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.addEventListener('click', e => {
                e.preventDefault();
                const target = e.currentTarget.getAttribute('href');
                const content = document.getElementById('page-content');

                // Fade out
                content.classList.add('opacity-0', 'translate-x-2');

                setTimeout(() => {
                    window.location.href = target;
                }, 300); // same as duration-300
            });
        });

        const content = document.getElementById('page-content');
        content.classList.add('opacity-0');
        setTimeout(() => content.classList.remove('opacity-0', 'translate-x-2'), 50);
    </script>
</body>