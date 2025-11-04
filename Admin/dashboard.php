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
    <title>RCH Water - Gallon Ownership</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="group bg-gradient-to-br from-blue-400 to-blue-600 shadow-lg border-r border-gray-200 
        flex-shrink-0 fixed top-0 left-0 h-full overflow-hidden flex flex-col text-white 
        transition-all duration-500 ease-in-out w-20 hover:w-64 z-50">

            <!-- Logo -->
            <div class="p-7 border-b border-blue-400 flex items-center gap-2">
                <i class="fas fa-droplet text-3xl"></i>
                <div class="overflow-hidden">
                    <span class="text-2xl font-semibold block whitespace-nowrap transition-all duration-500 ease-in-out 
                 opacity-0 group-hover:opacity-100 translate-x-[-20px] group-hover:translate-x-0">
                        RCH Water
                    </span>
                </div>
            </div>

            <nav class="mt-6 flex-1">
                <ul class="space-y-1 px-4">
                    <li>
                        <a href="../Admin/dashboard.php" class="flex items-center px-4 py-3 bg-blue-50 text-blue-600 rounded-lg font-medium">
                            <i class="fas fa-chart-line text-lg"></i>
                            <div class="overflow-hidden ml-3">
                                <span class="block whitespace-nowrap transition-all duration-500 ease-in-out 
                                     opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">
                                    Dashboard
                                </span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="../Order/orders_list.php" class="flex items-center px-4 py-3 text-white rounded-lg font-medium hover:bg-blue-500">
                            <i class="fas fa-shopping-cart text-lg"></i>
                            <div class="overflow-hidden ml-3">
                                <span class="block whitespace-nowrap transition-all duration-500 ease-in-out 
                                     opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">
                                    Orders
                                </span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="../GallonOwnership/gallon_ownership.php" class="flex items-center px-4 py-3 text-white rounded-lg font-medium hover:bg-blue-500">
                            <i class="fas fa-tint text-lg"></i>
                            <div class="overflow-hidden ml-3">
                                <span class="block whitespace-nowrap transition-all duration-500 ease-in-out 
                 opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">
                                    Gallon Ownership
                                </span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="../Inventory/inventory_list.php" class="flex items-center px-4 py-3 text-white rounded-lg font-medium hover:bg-blue-500">
                            <i class="fas fa-boxes text-lg"></i>
                            <div class="overflow-hidden ml-3">
                                <span class="block whitespace-nowrap transition-all duration-500 ease-in-out 
                 opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">
                                    Inventory
                                </span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="../Customer/customers_list.php" class="flex items-center px-4 py-3 text-white rounded-lg font-medium hover:bg-blue-500">
                            <i class="fas fa-users text-lg"></i>
                            <div class="overflow-hidden ml-3">
                                <span class="block whitespace-nowrap transition-all duration-500 ease-in-out 
                 opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">
                                    Customers
                                </span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="../Delivery/deliveries_list.php" class="flex items-center px-4 py-3 text-white rounded-lg font-medium hover:bg-blue-500">
                            <i class="fas fa-truck text-lg"></i>
                            <div class="overflow-hidden ml-3">
                                <span class="block whitespace-nowrap transition-all duration-500 ease-in-out 
                 opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">
                                    Deliveries
                                </span>
                            </div>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="border-t border-blue-400 p-4">
                <a href="../logout.php" class="flex items-center px-4 py-3 font-medium text-white hover:bg-blue-500 rounded-lg transition-colors">
                    <i class="fas fa-sign-out-alt text-lg"></i>
                    <span class="ml-3 hidden group-hover:inline">Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <div id="page-content"
            class="main-content fixed w-full top-0 right-0 overflow-auto h-full transition-all duration-300 ease-in-out">
            <div
                class="flex-1 transition-all duration-300 ease-in-out group-hover:ml-64 ml-20 flex flex-col overflow-hidden">
                <div class="bg-white border-b border-gray-100 shadow-sm p-4 transition-all duration-300 ease-in-out">
                    <div class="flex items-center justify-between">
                        <h1 class="text-2xl font-semibold text-blue-500">Performance Metrics    </h1>
                        
                        <div class="flex items-center space-x-4">                            
                            <a href="../Notification/notifications.php" title="Notifications" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-blue-100 cursor-pointer transition">
                                <i class="fas fa-bell text-blue-500 text-xl"></i>
                            </a>    
                            <a href="../Messaging/messages.php" title="Messages" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-blue-100 cursor-pointer transition">
                                <i class="fas fa-envelope text-blue-500 text-xl"></i>
                            </a>                           
                            <a href="../Admin/profile.php" title="Profile" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-blue-100 cursor-pointer transition">
                                <i class="fas fa-user text-blue-500 text-xl"></i>
                            </a>
                        </div>
                    </div>
                </div>

            <!-- Content Area -->
            <div class="p-8 space-y-8">
                <!-- Metric Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Added icons to metric cards -->
                    <div class="bg-white rounded-lg p-6 shadow-sm flex items-center gap-4 hover:shadow-md transition">
                        <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-shopping-cart text-2xl text-white"></i>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Orders This Month</p>
                            <p class="text-2xl font-bold text-gray-700">50</p>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg p-6 shadow-sm flex items-center gap-4 text-white hover:shadow-md transition">
                        <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-tint text-2xl"></i>
                        </div>
                        <div>
                            <p class="text-white text-sm">Total Handouts-Gallons</p>
                            <p class="text-2xl font-bold">50</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm flex items-center gap-4 hover:shadow-md transition">
                        <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-users text-2xl text-white"></i>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Active Customers</p>
                            <p class="text-2xl font-bold text-gray-700">50</p>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg p-6 shadow-sm flex items-center gap-4 text-white hover:shadow-md transition">
                        <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-clock text-2xl"></i>
                        </div>
                        <div>
                            <p class="text-white     text-sm">Average Delivery Time</p>
                            <p class="text-2xl font-bold">2.5 hrs</p>
                        </div>
                    </div>
                </div>

                <!-- Order and Delivery Analytics Section -->
                <div class="bg-blue-50 rounded-lg p-6 border border-blue-200 shadow-sm">
                    <h2 class="text-lg font-bold text-blue-600 mb-6 flex items-center gap-2">
                        <i class="fas fa-chart-column text-blue-600 text-lg"></i>
                        Order & Delivery Analytics
                    </h2>

                    <!-- Charts Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        <!-- Orders Over Time -->
                        <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="font-semibold text-gray-700 flex items-center gap-2">
                                    <i class="fas fa-arrow-trend-up text-blue-500"></i>
                                    Orders Over Time
                                </h3>
                            </div>
                            <div class="flex gap-2 mb-4">
                                <button class="px-4 py-2 bg-blue-600 text-white rounded-full text-sm font-medium hover:bg-blue-700">Daily</button>
                                <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-gray-300">Weekly</button>
                                <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-gray-300">Yearly</button>
                            </div>
                            <div class="h-64">
                                <canvas id="ordersChart"></canvas>
                            </div>
                        </div>

                        <!-- Order Type Breakdown -->
                        <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                            <h3 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                                <i class="fas fa-chart-pie text-blue-500"></i>
                                Order Type Breakdown
                            </h3>
                            <div class="h-64 flex items-center justify-center">
                                <canvas id="orderTypeChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Delivery Completion Rate -->
                        <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                            <h3 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                                <i class="fas fa-check-circle text-green-500"></i>
                                Delivery Completion Rate
                            </h3>
                            <div class="h-64 flex items-center justify-center">
                                <canvas id="completionChart"></canvas>
                            </div>
                        </div>

                        <!-- Average Delivery Hours -->
                        <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="font-semibold text-gray-700 flex items-center gap-2">
                                    <i class="fas fa-hourglass-end text-orange-500"></i>
                                    Average Delivery Hours
                                </h3>
                            </div>
                            <div class="flex gap-2 mb-4">
                                <button class="px-4 py-2 bg-blue-600 text-white rounded-full text-sm font-medium hover:bg-blue-700">Daily</button>
                                <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-gray-300">Weekly</button>
                                <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-full text-sm font-medium hover:bg-gray-300">Monthly</button>
                            </div>
                            <div class="h-64">
                                <canvas id="deliveryHoursChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Behaviour Insights Section -->
                <div class="bg-blue-50 rounded-lg p-6 border border-blue-200 shadow-sm">
                    <h2 class="text-lg font-bold text-blue-600 mb-6 flex items-center gap-2">
                        <i class="fas fa-user-group text-blue-600 text-lg"></i>
                        Customer Behaviour Insights
                    </h2>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Most Frequent Customers Chart -->
                        <!-- Replaced placeholder with actual chart -->
                        <div class="lg:col-span-2 bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                            <h3 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                                <i class="fas fa-star text-yellow-500"></i>
                                Most Frequent Customers
                            </h3>
                            <div class="h-64">
                                <canvas id="frequentCustomersChart"></canvas>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-4">
                            <!-- Average Orders Per Customer -->
                            <div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-lg p-6 text-white shadow-md">
                                <h3 class="font-semibold mb-4 text-sm flex items-center gap-2">
                                    <i class="fas fa-chart-line text-lg"></i>
                                    Average Orders/Customer
                                </h3>
                                <div class="flex items-center justify-center gap-4">
                                    <div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center flex-shrink-0">
                                        <span class="text-3xl font-bold">8</span>
                                    </div>
                                    <p class="text-blue-100 text-sm">Orders This Month</p>
                                </div>
                            </div>

                            <!-- Peak Ordering Times -->
                            <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                                <h3 class="font-semibold text-gray-700 mb-4 text-sm flex items-center gap-2">
                                    <i class="fas fa-clock text-blue-500"></i>
                                    Peak Ordering Times
                                </h3>
                                <ol class="space-y-2 text-sm text-gray-700">
                                    <li class="flex items-center justify-between p-2 bg-blue-50 rounded">
                                        <span><i class="fas fa-sun text-yellow-500 mr-2"></i>Morning (8-11AM)</span>
                                        <span class="font-bold text-blue-600 text-xs bg-blue-100 px-2 py-1 rounded">45%</span>
                                    </li>
                                    <li class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                        <span><i class="fas fa-cloud-sun text-orange-400 mr-2"></i>Afternoon (12-4PM)</span>
                                        <span class="font-bold text-blue-600 text-xs bg-blue-100 px-2 py-1 rounded">35%</span>
                                    </li>
                                    <li class="flex items-center justify-between p-2 bg-blue-50 rounded">
                                        <span><i class="fas fa-moon text-indigo-600 mr-2"></i>Evening (5-8PM)</span>
                                        <span class="font-bold text-blue-600 text-xs bg-blue-100 px-2 py-1 rounded">20%</span>
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gallon Tracking Insights Section -->
                <div class="bg-blue-50 rounded-lg p-6 border border-blue-200 shadow-sm mb-8">
                    <h2 class="text-lg font-bold text-blue-600 mb-6 flex items-center gap-2">
                        <i class="fas fa-flask-vial text-blue-600 text-lg"></i>
                        Gallon Tracking Insights
                    </h2>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        <!-- Gallon Status Overview -->
                        <!-- Replaced placeholder with bar chart -->
                        <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                            <h3 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                                <i class="fas fa-chart-bar text-blue-500"></i>
                                Gallon Status Overview
                            </h3>
                            <div class="h-64">
                                <canvas id="gallonStatusChart"></canvas>
                            </div>
                        </div>

                        <!-- Gallons Owned By Station vs by Customers -->
                        <!-- Replaced placeholder with comparison chart -->
                        <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                            <h3 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                                <i class="fas fa-code-compare text-purple-500"></i>
                                Station vs Customer Gallons
                            </h3>
                            <div class="h-64">
                                <canvas id="stationCustomerChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- QR Code Stats -->
                    <!-- Added QR code statistics section -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-600 text-sm flex items-center gap-2">
                                        <i class="fas fa-qrcode text-blue-500"></i>
                                        Total QR Codes
                                    </p>
                                    <p class="text-2xl font-bold text-gray-700">1,245</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-600 text-sm flex items-center gap-2">
                                        <i class="fas fa-check-circle text-green-500"></i>
                                        Active QR Codes
                                    </p>
                                    <p class="text-2xl font-bold text-green-600">892</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-600 text-sm flex items-center gap-2">
                                        <i class="fas fa-times-circle text-red-500"></i>
                                        Inactive QR Codes
                                    </p>
                                    <p class="text-2xl font-bold text-red-600">353</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders Section -->
                <!-- Added recent orders table with icons -->
                <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                    <h2 class="text-lg font-bold text-blue-600 mb-6 flex items-center gap-2">
                        <i class="fas fa-history text-blue-600"></i>
                        Recent Orders
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 bg-gray-50">
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Order ID</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Customer</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Type</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Amount</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Status</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                                    <td class="py-3 px-4"><span class="font-medium text-blue-600">#ORD-001</span></td>
                                    <td class="py-3 px-4">Leon Dala Cruz</td>
                                    <td class="py-3 px-4"><span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">Refill</span></td>
                                    <td class="py-3 px-4">₱500</td>
                                    <td class="py-3 px-4"><span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium flex items-center gap-1 w-fit"><i class="fas fa-check-circle text-xs"></i>Delivered</span></td>
                                    <td class="py-3 px-4 text-gray-500">2 hrs ago</td>
                                </tr>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                                    <td class="py-3 px-4"><span class="font-medium text-blue-600">#ORD-002</span></td>
                                    <td class="py-3 px-4">Felicity M Varrua</td>
                                    <td class="py-3 px-4"><span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium">New Order</span></td>
                                    <td class="py-3 px-4">₱800</td>
                                    <td class="py-3 px-4"><span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-medium flex items-center gap-1 w-fit"><i class="fas fa-truck text-xs"></i>In Transit</span></td>
                                    <td class="py-3 px-4 text-gray-500">30 mins ago</td>
                                </tr>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                                    <td class="py-3 px-4"><span class="font-medium text-blue-600">#ORD-003</span></td>
                                    <td class="py-3 px-4">Jenalyn Gentilique</td>
                                    <td class="py-3 px-4"><span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">Refill</span></td>
                                    <td class="py-3 px-4">₱450</td>
                                    <td class="py-3 px-4"><span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium flex items-center gap-1 w-fit"><i class="fas fa-clock text-xs"></i>Pending</span></td>
                                    <td class="py-3 px-4 text-gray-500">Just now</td>
                                </tr>
                            </tbody>
                        </table>
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

        // Orders Over Time Chart
        const ordersCtx = document.getElementById('ordersChart').getContext('2d');
        new Chart(ordersCtx, {
            type: 'bar',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Orders',
                    data: [12, 19, 8, 15, 22, 16, 9],
                    backgroundColor: '#1E90FF',
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 5
                        }
                    }
                }
            }
        });

        // Order Type Breakdown Chart
        const orderTypeCtx = document.getElementById('orderTypeChart').getContext('2d');
        new Chart(orderTypeCtx, {
            type: 'doughnut',
            data: {
                labels: ['Refill', 'New Order'],
                datasets: [{
                    data: [60, 40],
                    backgroundColor: ['#1E90FF', '#046CD2'],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Delivery Completion Rate Chart
        const completionCtx = document.getElementById('completionChart').getContext('2d');
        new Chart(completionCtx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'Pending', 'Cancelled'],
                datasets: [{
                    data: [70, 20, 10],
                    backgroundColor: ['#10B981', '#F59E0B', '#EF4444'],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Average Delivery Hours Chart
        const deliveryHoursCtx = document.getElementById('deliveryHoursChart').getContext('2d');
        new Chart(deliveryHoursCtx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Hours',
                    data: [2, 3, 2.5, 4, 3.5, 2, 3],
                    backgroundColor: 'rgba(30, 144, 255, 0.1)',
                    borderColor: '#1E90FF',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#1E90FF',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Most Frequent Customers Chart
        const frequentCustomersCtx = document.getElementById('frequentCustomersChart').getContext('2d');
        new Chart(frequentCustomersCtx, {
            type: 'bar',
            data: {
                labels: ['Leon Dala Cruz', 'Felicity M Varrua', 'Jenalyn Gentilique', 'Bryson Forte', 'Christine May'],
                datasets: [{
                    label: 'Number of Orders',
                    data: [45, 38, 32, 28, 25],
                    backgroundColor: ['#1E90FF', '#046CD2', '#1873CC', '#1460B8', '#1050A4'],
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Gallon Status Overview Chart
        const gallonStatusCtx = document.getElementById('gallonStatusChart').getContext('2d');
        new Chart(gallonStatusCtx, {
            type: 'bar',
            data: {
                labels: ['In Stock', 'In Transit', 'Damaged', 'Lost'],
                datasets: [{
                    label: 'Gallons',
                    data: [450, 320, 45, 15],
                    backgroundColor: ['#10B981', '#3B82F6', '#EF4444', '#F59E0B'],
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Station vs Customer Gallons Chart
        const stationCustomerCtx = document.getElementById('stationCustomerChart').getContext('2d');
        new Chart(stationCustomerCtx, {
            type: 'bar',
            data: {
                labels: ['Station A', 'Station B', 'Station C', 'Station D'],
                datasets: [{
                        label: 'Station Owned',
                        data: [120, 150, 100, 130],
                        backgroundColor: '#1E90FF',
                        borderRadius: 6
                    },
                    {
                        label: 'Customer Owned',
                        data: [80, 95, 110, 75],
                        backgroundColor: '#046CD2',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>