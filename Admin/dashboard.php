<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RCH Water - Performance Metrics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>
<body class="bg-gray-50 font-sans" style="font-family: 'Poppins', sans-serif;">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="sidebar w-64 bg-gradient-to-br from-blue-600 to-blue-800 text-white flex flex-col fixed h-screen md:relative md:left-0 transition-all duration-300 z-40">
            <!-- Logo -->
            <div class="p-6 border-b border-blue-400">
                <div class="flex items-center gap-2">
                    <i class="fas fa-droplet text-2xl"></i>
                    <span class="text-xl font-bold">RCH Water</span>
                </div>
            </div>
            
            <!-- Navigation Items -->
            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                <div class="flex items-center gap-3 px-4 py-3 cursor-pointer rounded-lg transition-all duration-300 hover:bg-white hover:bg-opacity-10 bg-white bg-opacity-20 border-l-4 border-white">
                    <div class="w-10 h-10 rounded-full bg-white bg-opacity-20 flex items-center justify-center text-white text-lg">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <span class="font-medium">Performance Metrics</span>
                </div>
                
                <div class="flex items-center gap-3 px-4 py-3 cursor-pointer rounded-lg transition-all duration-300 hover:bg-white hover:bg-opacity-10">
                    <div class="w-10 h-10 rounded-full bg-white bg-opacity-20 flex items-center justify-center text-white text-lg">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <span class="font-medium">Orders</span>
                </div>
                
                <div class="flex items-center gap-3 px-4 py-3 cursor-pointer rounded-lg transition-all duration-300 hover:bg-white hover:bg-opacity-10">
                    <div class="w-10 h-10 rounded-full bg-white bg-opacity-20 flex items-center justify-center text-white text-lg">
                        <i class="fas fa-jug"></i>
                    </div>
                    <span class="font-medium">Gallon Ownership</span>
                </div>
                
                <div class="flex items-center gap-3 px-4 py-3 cursor-pointer rounded-lg transition-all duration-300 hover:bg-white hover:bg-opacity-10">
                    <div class="w-10 h-10 rounded-full bg-white bg-opacity-20 flex items-center justify-center text-white text-lg">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <span class="font-medium">Inventory</span>
                </div>
                
                <div class="flex items-center gap-3 px-4 py-3 cursor-pointer rounded-lg transition-all duration-300 hover:bg-white hover:bg-opacity-10">
                    <div class="w-10 h-10 rounded-full bg-white bg-opacity-20 flex items-center justify-center text-white text-lg">
                        <i class="fas fa-users"></i>
                    </div>
                    <span class="font-medium">Users</span>
                </div>
                
                <div class="flex items-center gap-3 px-4 py-3 cursor-pointer rounded-lg transition-all duration-300 hover:bg-white hover:bg-opacity-10">
                    <div class="w-10 h-10 rounded-full bg-white bg-opacity-20 flex items-center justify-center text-white text-lg">
                        <i class="fas fa-truck"></i>
                    </div>
                    <span class="font-medium">Deliveries</span>
                </div>
                
                <div class="flex items-center gap-3 px-4 py-3 cursor-pointer rounded-lg transition-all duration-300 hover:bg-white hover:bg-opacity-10">
                    <div class="w-10 h-10 rounded-full bg-white bg-opacity-20 flex items-center justify-center text-white text-lg">
                        <i class="fas fa-bell"></i>
                    </div>
                    <span class="font-medium">Notifications</span>
                </div>
            </nav>
            
            <!-- Logout -->
            <div class="p-4 border-t border-blue-400">
                <div class="flex items-center gap-3 px-4 py-3 cursor-pointer rounded-lg transition-all duration-300 hover:bg-white hover:bg-opacity-10">
                    <div class="w-10 h-10 rounded-full bg-white bg-opacity-20 flex items-center justify-center text-white text-lg">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                    <span class="font-medium">Logout</span>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content flex-1 md:ml-0 ml-0 overflow-auto">
            <!-- Header -->
            <div class="bg-white border-b border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <h1 class="text-3xl font-bold text-gray-800">Performance Metrics</h1>
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center text-white cursor-pointer">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
            </div>
            
            <!-- Content Area -->
            <div class="p-6 space-y-6">
                <!-- Key Metrics Cards Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <!-- Orders This Month Card -->
                    <div class="bg-white rounded-lg shadow-md p-6 flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center text-white text-3xl">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Orders This Month</p>
                            <p class="text-3xl font-bold text-gray-800">50</p>
                        </div>
                    </div>
                    
                    <!-- Total Remaining Gallons Card -->
                    <div class="bg-white rounded-lg shadow-md p-6 flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full bg-white border-4 border-gray-200 flex items-center justify-center text-gray-600 text-3xl">
                            <i class="fas fa-jug"></i>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Total Remaining Gallons</p>
                            <p class="text-3xl font-bold text-gray-800">50</p>
                        </div>
                    </div>
                    
                    <!-- Active Customers Card -->
                    <div class="bg-white rounded-lg shadow-md p-6 flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center text-white text-3xl">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Active Customers</p>
                            <p class="text-3xl font-bold text-gray-800">50</p>
                        </div>
                    </div>
                    
                    <!-- Average Delivery Time Card -->
                    <div class="bg-white rounded-lg shadow-md p-6 flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full bg-white border-4 border-gray-200 flex items-center justify-center text-gray-600 text-3xl">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Average Delivery Time</p>
                            <p class="text-3xl font-bold text-gray-800">50</p>
                        </div>
                    </div>

                    <!-- QR Codes Generated Card -->
                    <div class="bg-white rounded-lg shadow-md p-6 flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center text-white text-3xl">
                            <i class="fas fa-qrcode"></i>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm font-medium">QR Codes Generated</p>
                            <p class="text-3xl font-bold text-gray-800">150</p>
                        </div>
                    </div>
                </div>

                <!-- Order & Delivery Analytics Section -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-chart-bar text-blue-600"></i>
                        Order & Delivery Analytics
                    </h2>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Orders Over Time Chart -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Orders Over Time</h3>
                            <canvas id="ordersChart" height="80"></canvas>
                        </div>

                        <!-- Delivery Completion Rate Chart -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Delivery Completion Rate</h3>
                            <canvas id="completionChart" height="80"></canvas>
                        </div>

                        <!-- Average Delivery Time Chart -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Average Delivery Time (Hours)</h3>
                            <canvas id="deliveryTimeChart" height="80"></canvas>
                        </div>

                        <!-- Order Type Breakdown Chart -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Order Type Breakdown</h3>
                            <canvas id="orderTypeChart" height="80"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Customer Behavior Insights Section -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-users text-blue-600"></i>
                        Customer Behavior Insights
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Most Frequent Customers -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h3 class="text-sm font-semibold text-gray-600 mb-3 flex items-center gap-2">
                                <i class="fas fa-star text-yellow-500"></i>
                                Most Frequent Customers
                            </h3>
                            <div class="space-y-2">
                                <p class="text-sm text-gray-700"><span class="font-semibold">1.</span> John Smith</p>
                                <p class="text-sm text-gray-700"><span class="font-semibold">2.</span> Sarah Johnson</p>
                                <p class="text-sm text-gray-700"><span class="font-semibold">3.</span> Mike Davis</p>
                            </div>
                        </div>

                        <!-- Average Orders per Customer -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h3 class="text-sm font-semibold text-gray-600 mb-3 flex items-center gap-2">
                                <i class="fas fa-chart-pie text-green-500"></i>
                                Average Orders per Customer
                            </h3>
                            <p class="text-4xl font-bold text-gray-800">12.5</p>
                            <p class="text-xs text-gray-500 mt-2">Orders per month</p>
                        </div>

                        <!-- Peak Ordering Times -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h3 class="text-sm font-semibold text-gray-600 mb-3 flex items-center gap-2">
                                <i class="fas fa-clock text-purple-500"></i>
                                Peak Ordering Times
                            </h3>
                            <div class="space-y-2">
                                <p class="text-sm text-gray-700"><span class="font-semibold">Morning:</span> 8-10 AM</p>
                                <p class="text-sm text-gray-700"><span class="font-semibold">Afternoon:</span> 2-4 PM</p>
                                <p class="text-sm text-gray-700"><span class="font-semibold">Evening:</span> 6-8 PM</p>
                            </div>
                        </div>

                        <!-- Common Delivery Areas -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h3 class="text-sm font-semibold text-gray-600 mb-3 flex items-center gap-2">
                                <i class="fas fa-map-marker-alt text-red-500"></i>
                                Common Delivery Areas
                            </h3>
                            <div class="space-y-2">
                                <p class="text-sm text-gray-700"><span class="font-semibold">1.</span> Barangay A</p>
                                <p class="text-sm text-gray-700"><span class="font-semibold">2.</span> Barangay B</p>
                                <p class="text-sm text-gray-700"><span class="font-semibold">3.</span> Barangay C</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gallon Tracking Insights Section -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-qrcode text-blue-600"></i>
                        Gallon Tracking Insights
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Gallons by Owner Type -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Gallons Owned by Station vs Customer</h3>
                            <canvas id="gallonOwnershipChart" height="80"></canvas>
                        </div>

                        <!-- QR Code Performance -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm font-semibold text-gray-600 mb-2">Total QR Codes Generated</p>
                                    <p class="text-4xl font-bold text-blue-600">5,234</p>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-600 mb-2">QR Codes Scanned</p>
                                    <p class="text-4xl font-bold text-green-600">4,150</p>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-600 mb-2">Scan Rate</p>
                                    <p class="text-4xl font-bold text-purple-600">79.3%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Floating Inbox Button -->
    <div class="fixed bottom-7 right-7 w-15 h-15 rounded-full bg-gradient-to-br from-blue-600 to-blue-800 text-white flex items-center justify-center cursor-pointer shadow-lg z-50 transition-transform duration-300 hover:scale-110" title="Inbox">
        <i class="fas fa-envelope text-2xl"></i>
    </div>
    
    <script>
        // Chart.js initialization for all charts
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        font: {
                            family: "'Poppins', sans-serif",
                            size: 12,
                            weight: '500'
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: {
                            family: "'Poppins', sans-serif"
                        }
                    }
                },
                x: {
                    ticks: {
                        font: {
                            family: "'Poppins', sans-serif"
                        }
                    }
                }
            }
        };

        // Orders Over Time (Line Chart)
        const ordersCtx = document.getElementById('ordersChart').getContext('2d');
        new Chart(ordersCtx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Orders',
                    data: [12, 19, 8, 15, 22, 18, 14],
                    borderColor: '#1E90FF',
                    backgroundColor: 'rgba(30, 144, 255, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: chartOptions
        });

        // Delivery Completion Rate (Doughnut Chart)
        const completionCtx = document.getElementById('completionChart').getContext('2d');
        new Chart(completionCtx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'Pending', 'Cancelled'],
                datasets: [{
                    data: [75, 15, 10],
                    backgroundColor: ['#10B981', '#F59E0B', '#EF4444']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                family: "'Poppins', sans-serif",
                                size: 12
                            }
                        }
                    }
                }
            }
        });

        // Average Delivery Time (Bar Chart)
        const deliveryTimeCtx = document.getElementById('deliveryTimeChart').getContext('2d');
        new Chart(deliveryTimeCtx, {
            type: 'bar',
            data: {
                labels: ['Driver A', 'Driver B', 'Driver C', 'Driver D', 'Driver E'],
                datasets: [{
                    label: 'Delivery Time (Hours)',
                    data: [2.5, 3.0, 2.2, 3.5, 2.8],
                    backgroundColor: '#1E90FF'
                }]
            },
            options: chartOptions
        });

        // Order Type Breakdown (Pie Chart)
        const orderTypeCtx = document.getElementById('orderTypeChart').getContext('2d');
        new Chart(orderTypeCtx, {
            type: 'pie',
            data: {
                labels: ['Refill Orders', 'New Orders'],
                datasets: [{
                    data: [65, 35],
                    backgroundColor: ['#1E90FF', '#8B5CF6']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                family: "'Poppins', sans-serif",
                                size: 12
                            }
                        }
                    }
                }
            }
        });

        // Gallons Owned (Bar Chart)
        const gallonCtx = document.getElementById('gallonOwnershipChart').getContext('2d');
        new Chart(gallonCtx, {
            type: 'bar',
            data: {
                labels: ['Station-Owned', 'Customer-Owned'],
                datasets: [{
                    label: 'Number of Gallons',
                    data: [450, 750],
                    backgroundColor: ['#1E90FF', '#10B981']
                }]
            },
            options: chartOptions
        });

        // Floating button click
        document.querySelector('[title="Inbox"]').addEventListener('click', function() {
            alert('Inbox clicked!');
        });
    </script>
</body>
</html>
