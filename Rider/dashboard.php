<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rider Dashboard | RCH Water</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>

<body class="bg-white min-h-screen flex justify-center items-center relative">

    <div class="bg-white w-[375px] h-[750px] mx-auto relative rounded-3xl overflow-hidden border shadow-lg">

        <!-- Header -->
        <div class="bg-gradient-to-br from-blue-400 to-blue-600 text-white sticky top-0 z-50 w-full px-4 py-3">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-bold tracking-tight">Welcome, Rider</h2>
                    <div id="datetime" class="text-sm text-blue-100 font-medium"></div>
                </div>
                <div class="flex items-center gap-3">
                    <button class="relative text-white text-2xl hover:scale-110 transition-transform duration-200">
                        <i class="bi bi-bell"></i>
                        <span class="absolute top-0 start-100 translate-x-1/2 -translate-y-1/2 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white animate-pulse"></span>
                    </button>
                    <button class="relative text-white text-2xl hover:scale-110 transition-transform duration-200">
                        <i class="bi bi-chat-dots"></i>
                        <span class="absolute top-0 start-100 translate-x-1/2 -translate-y-1/2 w-2.5 h-2.5 bg-green-500 rounded-full border-2 border-white animate-pulse"></span>
                    </button>
                    <button class="text-white text-2xl hover:scale-110 transition-transform duration-200">
                        <i class="bi bi-person-circle"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Dashboard Buttons -->
        <div class="bg-white sticky top-16 z-40 w-full px-5 py-4">
            <div class="grid grid-cols-3 gap-3">
                <button onclick="showSection('active-deliveries', this)" class="section-btn bg-blue-600 text-white font-semibold py-2 px-4 rounded-3xl text-xs shadow-sm transition-all duration-200">Active</button>
                <button onclick="showSection('pending-deliveries', this)" class="section-btn bg-blue-100 text-blue-600 font-semibold py-2 px-4 rounded-3xl text-xs shadow-sm transition-all duration-200">Pending</button>
                <button onclick="showSection('completed-deliveries', this)" class="section-btn bg-blue-100 text-blue-600 font-semibold py-2 px-4 rounded-3xl text-xs shadow-sm transition-all duration-200">Completed</button>
            </div>
        </div>

        <!-- ACTIVE DELIVERIES -->
        <div class="px-5 pb-5" id="active-deliveries">
            <h3 class="text-md font-semibold text-blue-600 mb-3">Active Deliveries</h3>

            <div id="active-map" class="rounded-xl h-40 mb-4 border border-gray-300 overflow-hidden"></div>

            <div id="active-list" class="bg-gray-100 p-2 rounded-md shadow-inner space-y-4 overflow-y-auto custom-scrollbar h-[330px] pb-5 pr-2">
                <!-- Active Delivery Card 1 -->
                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h4 class="font-bold text-gray-900 text-base">Maria Santos</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Order #ORD-112501</p>
                        </div>
                        <span class="text-xs bg-green-100 text-green-700 px-2.5 py-1 rounded-full font-semibold flex items-center">
                            <i class="bi bi-truck mr-1"></i> On the Way
                        </span>
                    </div>
                    <div class="space-y-2 mb-3">
                        <div class="flex items-start text-sm text-gray-600">
                            <i class="bi bi-geo-alt-fill text-blue-500 mr-2 mt-0.5"></i>
                            <span class="flex-1">123 San Jose St., Batangas City</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="bi bi-clock-fill text-orange-500 mr-2"></i>
                            <span class="font-medium">09:30 AM</span>
                        </div>
                    </div>
                    <button class="w-full py-2.5 text-sm font-semibold rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200 transition">
                        View Details
                    </button>
                </div>

                <!-- Active Delivery Card 2 -->
                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h4 class="font-bold text-gray-900 text-base">Juan Dela Cruz</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Order #ORD-112502</p>
                        </div>
                        <span class="text-xs bg-green-100 text-green-700 px-2.5 py-1 rounded-full font-semibold flex items-center">
                            <i class="bi bi-truck mr-1"></i> On the Way
                        </span>
                    </div>
                    <div class="space-y-2 mb-3">
                        <div class="flex items-start text-sm text-gray-600">
                            <i class="bi bi-geo-alt-fill text-blue-500 mr-2 mt-0.5"></i>
                            <span class="flex-1">456 Rizal Ave., Lipa City</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="bi bi-clock-fill text-orange-500 mr-2"></i>
                            <span class="font-medium">10:15 AM</span>
                        </div>
                    </div>
                    <button class="w-full py-2.5 text-sm font-semibold rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200 transition">
                        View Details
                    </button>
                </div>

                <!-- Active Delivery Card 3 -->
                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h4 class="font-bold text-gray-900 text-base">Ana Reyes</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Order #ORD-112503</p>
                        </div>
                        <span class="text-xs bg-green-100 text-green-700 px-2.5 py-1 rounded-full font-semibold flex items-center">
                            <i class="bi bi-truck mr-1"></i> On the Way
                        </span>
                    </div>
                    <div class="space-y-2 mb-3">
                        <div class="flex items-start text-sm text-gray-600">
                            <i class="bi bi-geo-alt-fill text-blue-500 mr-2 mt-0.5"></i>
                            <span class="flex-1">789 Mabini St., Tanauan City</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="bi bi-clock-fill text-orange-500 mr-2"></i>
                            <span class="font-medium">11:00 AM</span>
                        </div>
                    </div>
                    <button class="w-full py-2.5 text-sm font-semibold rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200 transition">
                        View Details
                    </button>
                </div>
            </div>
        </div>

        <!-- PENDING DELIVERIES -->
        <div class="px-5 pb-20 hidden relative" id="pending-deliveries">
            <h3 class="text-md font-semibold text-blue-600 mb-3">Pending Deliveries</h3>

            <div class="flex mb-4 bg-gray-200 rounded-lg overflow-hidden">
                <button id="pending-tab-pickup" class="flex-1 py-2 text-xs font-semibold bg-blue-500 text-white transition-colors duration-200" onclick="showPendingTab('pickup')">
                    For Pickup
                </button>
                <button id="pending-tab-delivery" class="flex-1 py-2 text-xs font-semibold bg-gray-200 text-gray-700 transition-colors duration-200" onclick="showPendingTab('delivery')">
                    For Delivery
                </button>
            </div>

            <!-- PICKUP LIST -->
            <div id="pickup-list" class="bg-gray-100 p-2 rounded-md shadow-inner space-y-4 overflow-y-auto custom-scrollbar h-[450px] pb-5 pr-2">
                <!-- Pickup Card 1 -->
                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h4 class="font-bold text-gray-900 text-base">Pedro Garcia</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Order #ORD-112504</p>
                        </div>
                        <span class="text-xs bg-red-100 text-red-700 px-2.5 py-1 rounded-full font-semibold">Pickup</span>
                    </div>
                    <div class="space-y-2 mb-3">
                        <div class="flex items-start text-sm text-gray-600">
                            <i class="bi bi-geo-alt-fill text-blue-500 mr-2 mt-0.5"></i>
                            <span class="flex-1">321 Luna St., Batangas City</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="bi bi-clock-fill text-orange-500 mr-2"></i>
                            <span class="font-medium">08:00 AM</span>
                        </div>
                    </div>
                    <button class="w-full py-2.5 text-sm font-semibold rounded-lg bg-blue-500 text-white hover:bg-blue-600 transition">
                        Start Delivery
                    </button>
                </div>

                <!-- Pickup Card 2 -->
                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h4 class="font-bold text-gray-900 text-base">Sofia Mendoza</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Order #ORD-112505</p>
                        </div>
                        <span class="text-xs bg-red-100 text-red-700 px-2.5 py-1 rounded-full font-semibold">Pickup</span>
                    </div>
                    <div class="space-y-2 mb-3">
                        <div class="flex items-start text-sm text-gray-600">
                            <i class="bi bi-geo-alt-fill text-blue-500 mr-2 mt-0.5"></i>
                            <span class="flex-1">654 Aguinaldo Blvd., Lipa City</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="bi bi-clock-fill text-orange-500 mr-2"></i>
                            <span class="font-medium">08:45 AM</span>
                        </div>
                    </div>
                    <button class="w-full py-2.5 text-sm font-semibold rounded-lg bg-blue-500 text-white hover:bg-blue-600 transition">
                        Start Delivery
                    </button>
                </div>

                <!-- Pickup Card 3 -->
                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h4 class="font-bold text-gray-900 text-base">Carlos Ramos</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Order #ORD-112506</p>
                        </div>
                        <span class="text-xs bg-red-100 text-red-700 px-2.5 py-1 rounded-full font-semibold">Pickup</span>
                    </div>
                    <div class="space-y-2 mb-3">
                        <div class="flex items-start text-sm text-gray-600">
                            <i class="bi bi-geo-alt-fill text-blue-500 mr-2 mt-0.5"></i>
                            <span class="flex-1">987 Bonifacio St., Tanauan City</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="bi bi-clock-fill text-orange-500 mr-2"></i>
                            <span class="font-medium">09:30 AM</span>
                        </div>
                    </div>
                    <button class="w-full py-2.5 text-sm font-semibold rounded-lg bg-blue-500 text-white hover:bg-blue-600 transition">
                        Start Delivery
                    </button>
                </div>

                <!-- Pickup Card 4 -->
                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h4 class="font-bold text-gray-900 text-base">Elena Cruz</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Order #ORD-112507</p>
                        </div>
                        <span class="text-xs bg-red-100 text-red-700 px-2.5 py-1 rounded-full font-semibold">Pickup</span>
                    </div>
                    <div class="space-y-2 mb-3">
                        <div class="flex items-start text-sm text-gray-600">
                            <i class="bi bi-geo-alt-fill text-blue-500 mr-2 mt-0.5"></i>
                            <span class="flex-1">147 Del Pilar Ave., Batangas City</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="bi bi-clock-fill text-orange-500 mr-2"></i>
                            <span class="font-medium">10:15 AM</span>
                        </div>
                    </div>
                    <button class="w-full py-2.5 text-sm font-semibold rounded-lg bg-blue-500 text-white hover:bg-blue-600 transition">
                        Start Delivery
                    </button>
                </div>
            </div>

            <!-- DELIVERY LIST -->
            <div id="delivery-list" class="bg-gray-100 p-2 rounded-md shadow-inner space-y-4 overflow-y-auto custom-scrollbar h-[450px] pb-5 pr-2 hidden">
                <!-- Delivery Card 1 -->
                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h4 class="font-bold text-gray-900 text-base">Rosa Fernandez</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Order #ORD-112508</p>
                        </div>
                        <span class="text-xs bg-orange-100 text-orange-700 px-2.5 py-1 rounded-full font-semibold">Delivery</span>
                    </div>
                    <div class="space-y-2 mb-3">
                        <div class="flex items-start text-sm text-gray-600">
                            <i class="bi bi-geo-alt-fill text-blue-500 mr-2 mt-0.5"></i>
                            <span class="flex-1">258 Marasigan St., Lipa City</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="bi bi-clock-fill text-orange-500 mr-2"></i>
                            <span class="font-medium">02:00 PM</span>
                        </div>
                    </div>
                    <button class="w-full py-2.5 text-sm font-semibold rounded-lg bg-blue-500 text-white hover:bg-blue-600 transition">
                        Start Delivery
                    </button>
                </div>

                <!-- Delivery Card 2 -->
                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h4 class="font-bold text-gray-900 text-base">Miguel Torres</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Order #ORD-112509</p>
                        </div>
                        <span class="text-xs bg-orange-100 text-orange-700 px-2.5 py-1 rounded-full font-semibold">Delivery</span>
                    </div>
                    <div class="space-y-2 mb-3">
                        <div class="flex items-start text-sm text-gray-600">
                            <i class="bi bi-geo-alt-fill text-blue-500 mr-2 mt-0.5"></i>
                            <span class="flex-1">369 Santos Ave., Tanauan City</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="bi bi-clock-fill text-orange-500 mr-2"></i>
                            <span class="font-medium">03:30 PM</span>
                        </div>
                    </div>
                    <button class="w-full py-2.5 text-sm font-semibold rounded-lg bg-blue-500 text-white hover:bg-blue-600 transition">
                        Start Delivery
                    </button>
                </div>

                <!-- Delivery Card 3 -->
                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h4 class="font-bold text-gray-900 text-base">Isabel Gomez</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Order #ORD-112510</p>
                        </div>
                        <span class="text-xs bg-orange-100 text-orange-700 px-2.5 py-1 rounded-full font-semibold">Delivery</span>
                    </div>
                    <div class="space-y-2 mb-3">
                        <div class="flex items-start text-sm text-gray-600">
                            <i class="bi bi-geo-alt-fill text-blue-500 mr-2 mt-0.5"></i>
                            <span class="flex-1">741 Laurel St., Batangas City</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="bi bi-clock-fill text-orange-500 mr-2"></i>
                            <span class="font-medium">04:00 PM</span>
                        </div>
                    </div>
                    <button class="w-full py-2.5 text-sm font-semibold rounded-lg bg-blue-500 text-white hover:bg-blue-600 transition">
                        Start Delivery
                    </button>
                </div>
            </div>

            <!-- Time Switcher for Pickup -->
            <div id="time-switcher-pickup" class="absolute bottom-7 left-1/2 -translate-x-1/2 bg-blue-600 text-white shadow-lg rounded-full px-2 py-1 flex items-center space-x-6 z-50 border">
                <button id="prev-period-pickup" class="w-7 h-7 flex items-center justify-center rounded-full bg-white shadow text-blue-600 font-bold hover:bg-gray-100 transition">←</button>
                <span id="current-period-pickup" class="text-xs text-white font-semibold">Morning</span>
                <button id="next-period-pickup" class="w-7 h-7 flex items-center justify-center rounded-full bg-white shadow text-blue-600 font-bold hover:bg-gray-100 transition">→</button>
            </div>

            <!-- Time Switcher for Delivery -->
            <div id="time-switcher-delivery" class="absolute bottom-7 left-1/2 -translate-x-1/2 bg-blue-600 text-white shadow-lg rounded-full px-2 py-1 flex items-center space-x-6 z-50 border hidden">
                <button id="prev-period-delivery" class="w-7 h-7 flex items-center justify-center rounded-full bg-white shadow text-blue-600 font-bold hover:bg-gray-100 transition">←</button>
                <span id="current-period-delivery" class="text-xs text-white font-semibold">Morning</span>
                <button id="next-period-delivery" class="w-7 h-7 flex items-center justify-center rounded-full bg-white shadow text-blue-600 font-bold hover:bg-gray-100 transition">→</button>
            </div>
        </div>

        <!-- COMPLETED DELIVERIES -->
        <div class="px-5 pb-20 hidden relative" id="completed-deliveries">
            <h3 class="text-md font-semibold text-blue-600 mb-3">Order History</h3>

            <div class="flex mb-4 bg-gray-200 rounded-lg overflow-hidden">
                <button id="history-tab-delayed" class="flex-1 py-2 text-xs font-semibold bg-blue-500 text-white transition-colors duration-200" onclick="showHistoryTab('delayed')">Delayed</button>
                <button id="history-tab-completed" class="flex-1 py-2 text-xs font-semibold bg-gray-200 text-gray-700 transition-colors duration-200" onclick="showHistoryTab('completed')">Completed</button>
            </div>

            <!-- DELAYED LIST -->
            <div id="delayed-list" class="bg-gray-100 p-2 rounded-md shadow-inner space-y-4 overflow-y-auto custom-scrollbar h-[450px] pb-5 pr-2">
                <!-- Delayed Card 1 -->
                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h4 class="font-bold text-gray-900 text-base">Roberto Sanchez</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Order #ORD-112401</p>
                        </div>
                        <span class="text-xs bg-yellow-100 text-yellow-700 px-2.5 py-1 rounded-full font-semibold flex items-center">
                            <i class="bi bi-clock-history mr-1"></i> Delayed
                        </span>
                    </div>
                    <div class="space-y-2 mb-3">
                        <div class="flex items-start text-sm text-gray-600">
                            <i class="bi bi-geo-alt-fill text-blue-500 mr-2 mt-0.5"></i>
                            <span class="flex-1">852 Gomez St., Batangas City</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="bi bi-clock-fill text-yellow-500 mr-2"></i>
                            <span class="font-medium">Nov 24, 03:00 PM</span>
                        </div>
                    </div>
                    <button class="w-full py-2.5 text-sm font-semibold rounded-lg bg-yellow-50 text-yellow-700 cursor-default">
                        <i class="bi bi-exclamation-triangle mr-1"></i> Delivery Delayed
                    </button>
                </div>

                <!-- Delayed Card 2 -->
                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h4 class="font-bold text-gray-900 text-base">Linda Pascual</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Order #ORD-112402</p>
                        </div>
                        <span class="text-xs bg-yellow-100 text-yellow-700 px-2.5 py-1 rounded-full font-semibold flex items-center">
                            <i class="bi bi-clock-history mr-1"></i> Delayed
                        </span>
                    </div>
                    <div class="space-y-2 mb-3">
                        <div class="flex items-start text-sm text-gray-600">
                            <i class="bi bi-geo-alt-fill text-blue-500 mr-2 mt-0.5"></i>
                            <span class="flex-1">963 Evangelista Ave., Lipa City</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="bi bi-clock-fill text-yellow-500 mr-2"></i>
                            <span class="font-medium">Nov 24, 04:30 PM</span>
                        </div>
                    </div>
                    <button class="w-full py-2.5 text-sm font-semibold rounded-lg bg-yellow-50 text-yellow-700 cursor-default">
                        <i class="bi bi-exclamation-triangle mr-1"></i> Delivery Delayed
                    </button>
                </div>
            </div>

            <!-- COMPLETED LIST -->
            <div id="completed-list" class="bg-gray-100 p-2 rounded-md shadow-inner space-y-4 overflow-y-auto custom-scrollbar h-[450px] pb-5 pr-2 hidden">
                <!-- Completed Card 1 -->
                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h4 class="font-bold text-gray-900 text-base">Teresa Diaz</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Order #ORD-112301</p>
                        </div>
                        <span class="text-xs bg-green-100 text-green-700 px-2.5 py-1 rounded-full font-semibold flex items-center">
                            <i class="bi bi-check-circle-fill mr-1"></i> Completed
                        </span>
                    </div>
            <!-- Floating Period Switcher for Delayed -->
            <div id="time-switcher-delayed"
                class="absolute bottom-7 left-1/2 -translate-x-1/2 
               bg-blue-600 text-white shadow-lg rounded-full px-2 py-1 
               flex items-center space-x-6 z-50 border">
                <button id="prev-period-delayed"
                    class="w-7 h-7 flex items-center justify-center 
                   rounded-full bg-white shadow text-blue-600 font-bold hover:bg-gray-100 transition">
                    ←
                </button>
                <span id="current-period-delayed" class="text-xs text-white font-semibold">
                    Morning
                </span>
                <button id="next-period-delayed"
                    class="w-7 h-7 flex items-center justify-center 
                   rounded-full bg-white shadow text-blue-600 font-bold hover:bg-gray-100 transition">
                    →
                </button>
            </div>

            <!-- Floating Period Switcher for Completed -->
            <div id="time-switcher-completed"
                class="absolute bottom-7 left-1/2 -translate-x-1/2 
               bg-blue-600 text-white shadow-lg rounded-full px-2 py-1 
               flex items-center space-x-6 z-50 border hidden">
                <button id="prev-period-completed"
                    class="w-7 h-7 flex items-center justify-center 
                   rounded-full bg-white shadow text-blue-600 font-bold hover:bg-gray-100 transition">
                    ←
                </button>
                <span id="current-period-completed" class="text-xs text-white font-semibold">
                    Morning
                </span>
                <button id="next-period-completed"
                    class="w-7 h-7 flex items-center justify-center 
                   rounded-full bg-white shadow text-blue-600 font-bold hover:bg-gray-100 transition">
                    →
                </button>
            </div>
        </div>

    </div>
    <script>
        // Global variables
        let currentPeriodPickup = "morning";
        let currentPeriodDelivery = "morning";
        let currentPeriodDelayed = "morning";
        let currentPeriodCompleted = "morning";
        let deliveryData = null;

        // Month names
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        // Initialize periods based on current time
        function initializePeriods() {
            const now = new Date();
            const hour = now.getHours();

            // If it's 12 PM (noon) or later, set to afternoon
            if (hour >= 12) {
                currentPeriodPickup = "afternoon";
                currentPeriodDelivery = "afternoon";
                currentPeriodDelayed = "afternoon";
                currentPeriodCompleted = "afternoon";
            } else {
                currentPeriodPickup = "morning";
                currentPeriodDelivery = "morning";
                currentPeriodDelayed = "morning";
                currentPeriodCompleted = "morning";
            }
        }

        function updateDateTime() {
            const now = new Date();
            const month = monthNames[now.getMonth()];
            const day = String(now.getDate()).padStart(2, '0');
            const year = now.getFullYear();
            let hours = now.getHours();
            const minutes = String(now.getMinutes()).padStart(2, '0');

            // Convert to 12-hour format with AM/PM
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12; // Convert 0 to 12
            const hoursFormatted = String(hours).padStart(2, '0');

            document.getElementById('datetime').innerText = `${month} ${day}, ${year} • ${hoursFormatted}:${minutes} ${ampm}`;
        }

        updateDateTime();
        setInterval(updateDateTime, 60000);

        function showSection(sectionId, button) {
            const sections = ['active-deliveries', 'pending-deliveries', 'completed-deliveries'];
            sections.forEach(id => {
                document.getElementById(id).classList.add('hidden');
            });
            document.getElementById(sectionId).classList.remove('hidden');

            document.querySelectorAll('.section-btn').forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('bg-blue-100', 'text-blue-600');
            });
            button.classList.remove('bg-blue-100', 'text-blue-600');
            button.classList.add('bg-blue-600', 'text-white');
        }

        showSection('active-deliveries', document.querySelector('.section-btn'));

        function showPendingTab(tab) {
            const pickupTab = document.getElementById('pending-tab-pickup');
            const deliveryTab = document.getElementById('pending-tab-delivery');
            const pickupList = document.getElementById('pickup-list');
            const deliveryList = document.getElementById('delivery-list');
            const timeSwitcherPickup = document.getElementById('time-switcher-pickup');
            const timeSwitcherDelivery = document.getElementById('time-switcher-delivery');

            if (tab === 'pickup') {
                pickupTab.classList.add("bg-blue-500", "text-white");
                pickupTab.classList.remove("bg-gray-200", "text-gray-700");
                deliveryTab.classList.add("bg-gray-200", "text-gray-700");
                deliveryTab.classList.remove("bg-blue-500", "text-white");

                pickupList.classList.remove("hidden");
                deliveryList.classList.add("hidden");
                timeSwitcherPickup.classList.remove("hidden");
                timeSwitcherDelivery.classList.add("hidden");
            } else {
                deliveryTab.classList.add("bg-blue-500", "text-white");
                deliveryTab.classList.remove("bg-gray-200", "text-gray-700");
                pickupTab.classList.add("bg-gray-200", "text-gray-700");
                pickupTab.classList.remove("bg-blue-500", "text-white");

                deliveryList.classList.remove("hidden");
                pickupList.classList.add("hidden");
                timeSwitcherDelivery.classList.remove("hidden");
                timeSwitcherPickup.classList.add("hidden");
            }
        }

        function showHistoryTab(tab) {
            const delayedTab = document.getElementById('history-tab-delayed');
            const completedTab = document.getElementById('history-tab-completed');
            const delayedList = document.getElementById('delayed-list');
            const completedList = document.getElementById('completed-list');
            const timeSwitcherDelayed = document.getElementById('time-switcher-delayed');
            const timeSwitcherCompleted = document.getElementById('time-switcher-completed');

            if (tab === 'delayed') {
                delayedTab.classList.add("bg-blue-500", "text-white");
                delayedTab.classList.remove("bg-gray-200", "text-gray-700");
                completedTab.classList.add("bg-gray-200", "text-gray-700");
                completedTab.classList.remove("bg-blue-500", "text-white");

                delayedList.classList.remove("hidden");
                completedList.classList.add("hidden");
                timeSwitcherDelayed.classList.remove("hidden");
                timeSwitcherCompleted.classList.add("hidden");
            } else {
                completedTab.classList.add("bg-blue-500", "text-white");
                completedTab.classList.remove("bg-gray-200", "text-gray-700");
                delayedTab.classList.add("bg-gray-200", "text-gray-700");
                delayedTab.classList.remove("bg-blue-500", "text-white");

                completedList.classList.remove("hidden");
                delayedList.classList.add("hidden");
                timeSwitcherCompleted.classList.remove("hidden");
                timeSwitcherDelayed.classList.add("hidden");
            }
        }

        function updatePickupPeriodDisplay() {
            document.getElementById("current-period-pickup").textContent =
                currentPeriodPickup.charAt(0).toUpperCase() + currentPeriodPickup.slice(1);
            renderPickupList();
        }

        function updateDeliveryPeriodDisplay() {
            document.getElementById("current-period-delivery").textContent =
                currentPeriodDelivery.charAt(0).toUpperCase() + currentPeriodDelivery.slice(1);
            renderDeliveryList();
        }

        function updateDelayedPeriodDisplay() {
            document.getElementById("current-period-delayed").textContent =
                currentPeriodDelayed.charAt(0).toUpperCase() + currentPeriodDelayed.slice(1);
            renderDelayedList();
        }

        function updateCompletedPeriodDisplay() {
            document.getElementById("current-period-completed").textContent =
                currentPeriodCompleted.charAt(0).toUpperCase() + currentPeriodCompleted.slice(1);
            renderCompletedList();
        }

        // Period switchers for pickup/delivery
        document.getElementById("prev-period-pickup").addEventListener("click", () => {
            currentPeriodPickup = currentPeriodPickup === "morning" ? "afternoon" : "morning";
            updatePickupPeriodDisplay();
        });

        document.getElementById("next-period-pickup").addEventListener("click", () => {
            currentPeriodPickup = currentPeriodPickup === "morning" ? "afternoon" : "morning";
            updatePickupPeriodDisplay();
        });

        document.getElementById("prev-period-delivery").addEventListener("click", () => {
            currentPeriodDelivery = currentPeriodDelivery === "morning" ? "afternoon" : "morning";
            updateDeliveryPeriodDisplay();
        });

        document.getElementById("next-period-delivery").addEventListener("click", () => {
            currentPeriodDelivery = currentPeriodDelivery === "morning" ? "afternoon" : "morning";
            updateDeliveryPeriodDisplay();
        });

        // Period switchers for delayed/completed
        document.getElementById("prev-period-delayed").addEventListener("click", () => {
            currentPeriodDelayed = currentPeriodDelayed === "morning" ? "afternoon" : "morning";
            updateDelayedPeriodDisplay();
        });

        document.getElementById("next-period-delayed").addEventListener("click", () => {
            currentPeriodDelayed = currentPeriodDelayed === "morning" ? "afternoon" : "morning";
            updateDelayedPeriodDisplay();
        });

        document.getElementById("prev-period-completed").addEventListener("click", () => {
            currentPeriodCompleted = currentPeriodCompleted === "morning" ? "afternoon" : "morning";
            updateCompletedPeriodDisplay();
        });

        document.getElementById("next-period-completed").addEventListener("click", () => {
            currentPeriodCompleted = currentPeriodCompleted === "morning" ? "afternoon" : "morning";
            updateCompletedPeriodDisplay();
        });

        function renderPickupList() {
            if (!deliveryData) return;

            const pickupList = document.getElementById('pickup-list');
            const today = new Date().toISOString().split('T')[0];

            pickupList.innerHTML = '';

            const orders = deliveryData.pending.pickup[currentPeriodPickup].filter(order =>
                order.pickup_datetime.startsWith(today)
            );

            if (!orders.length) {
                pickupList.innerHTML = `
            <div class="flex items-center justify-center h-full">
                <p class="text-center text-gray-400 text-sm">No ${currentPeriodPickup} pickups for today</p>
            </div>
        `;
                return;
            }

            orders.forEach(order => {
                pickupList.innerHTML += `
            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h4 class="font-bold text-gray-900 text-base">${order.user.name}</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Order #${order.order_number}</p>
                    </div>
                    <span class="text-xs bg-red-100 text-red-700 px-2.5 py-1 rounded-full font-semibold">Pickup</span>
                </div>

                <div class="space-y-2 mb-3">
                    <div class="flex items-start text-sm text-gray-600">
                        <i class="bi bi-geo-alt-fill text-blue-500 mr-2 mt-0.5"></i>
                        <span class="flex-1">${order.user.address}</span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="bi bi-clock-fill text-orange-500 mr-2"></i>
                        <span class="font-medium">${new Date(order.pickup_datetime).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
                    </div>
                </div>

                <button onclick="startDelivery(${order.refill_id})"
                    class="w-full py-2.5 text-sm font-semibold rounded-lg bg-blue-500 text-white hover:bg-blue-600 transition">
                    Start Delivery
                </button>
            </div>
        `;
            });
        }

        function startDelivery(refillId) {
            window.location.href = `order_details.php?refill_id=${refill_id}`;
        }



        function renderDeliveryList() {
            if (!deliveryData) return;

            const deliveryList = document.getElementById('delivery-list');
            const today = new Date().toISOString().split('T')[0];

            deliveryList.innerHTML = '';

            const orders = deliveryData.pending.delivery[currentPeriodDelivery].filter(order =>
                order.delivery_datetime.startsWith(today)
            );

            if (!orders.length) {
                deliveryList.innerHTML = `
            <div class="flex items-center justify-center h-full">
                <p class="text-center text-gray-400 text-sm">No ${currentPeriodDelivery} deliveries for today</p>
            </div>
        `;
                return;
            }

            orders.forEach(order => {
                deliveryList.innerHTML += `
            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h4 class="font-bold text-gray-900 text-base">${order.user.name}</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Order #${order.order_number}</p>
                    </div>
                    <span class="text-xs bg-orange-100 text-orange-700 px-2.5 py-1 rounded-full font-semibold">Delivery</span>
                </div>

                <div class="space-y-2 mb-3">
                    <div class="flex items-start text-sm text-gray-600">
                        <i class="bi bi-geo-alt-fill text-blue-500 mr-2 mt-0.5"></i>
                        <span class="flex-1">${order.user.address}</span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="bi bi-clock-fill text-orange-500 mr-2"></i>
                        <span class="font-medium">${new Date(order.delivery_datetime).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
                    </div>
                </div>

                <button onclick="startDelivery(${order.refill_id})"
                    class="w-full py-2.5 text-sm font-semibold rounded-lg bg-blue-500 text-white hover:bg-blue-600 transition">
                    Start Delivery
                </button>
            </div>
        `;
            });
        }

        function renderDelayedList() {
            if (!deliveryData) return;

            const delayedList = document.getElementById('delayed-list');
            delayedList.innerHTML = '';

            // Removed date filtering
            const orders = deliveryData.history.delayed[currentPeriodDelayed];

            if (!orders.length) {
                delayedList.innerHTML = `
        <div class="flex items-center justify-center h-full">
            <p class="text-center text-gray-400 text-sm">No ${currentPeriodDelayed} delayed orders</p>
        </div>
    `;
                return;
            }

            orders.forEach(order => {
                delayedList.innerHTML += `
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h4 class="font-bold text-gray-900 text-base">${order.user.name}</h4>
                    <p class="text-xs text-gray-500 mt-0.5">Order #${order.order_number}</p>
                </div>
                <span class="text-xs bg-yellow-100 text-yellow-700 px-2.5 py-1 rounded-full font-semibold flex items-center">
                    <i class="bi bi-clock-history mr-1"></i> Delayed
                </span>
            </div>

            <div class="space-y-2 mb-3">
                <div class="flex items-start text-sm text-gray-600">
                    <i class="bi bi-geo-alt-fill text-blue-500 mr-2 mt-0.5"></i>
                    <span class="flex-1">${order.user.address}</span>
                </div>
                <div class="flex items-center text-sm text-gray-600">
                    <i class="bi bi-clock-fill text-yellow-500 mr-2"></i>
                    <span class="font-medium">${new Date(order.delivery_datetime).toLocaleString([], {month: 'short', day: 'numeric', hour: '2-digit', minute:'2-digit'})}</span>
                </div>
            </div>

            <button class="w-full py-2.5 text-sm font-semibold rounded-lg bg-yellow-50 text-yellow-700 cursor-default">
                <i class="bi bi-exclamation-triangle mr-1"></i> Delivery Delayed
            </button>
        </div>
    `;
            });
        }

        function renderCompletedList() {
            if (!deliveryData) return;

            const completedList = document.getElementById('completed-list');
            completedList.innerHTML = '';

            // Removed date filtering
            const orders = deliveryData.history.completed[currentPeriodCompleted];

            if (!orders.length) {
                completedList.innerHTML = `
        <div class="flex items-center justify-center h-full">
            <p class="text-center text-gray-400 text-sm">No ${currentPeriodCompleted} completed orders</p>
        </div>
    `;
                return;
            }

            orders.forEach(order => {
                completedList.innerHTML += `
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h4 class="font-bold text-gray-900 text-base">${order.user.name}</h4>
                    <p class="text-xs text-gray-500 mt-0.5">Order #${order.order_number}</p>
                </div>
                <span class="text-xs bg-green-100 text-green-700 px-2.5 py-1 rounded-full font-semibold flex items-center">
                    <i class="bi bi-check-circle-fill mr-1"></i> Completed
                </span>
            </div>

            <div class="space-y-2 mb-3">
                <div class="flex items-start text-sm text-gray-600">
                    <i class="bi bi-geo-alt-fill text-blue-500 mr-2 mt-0.5"></i>
                    <span class="flex-1">${order.user.address}</span>
                </div>
                <div class="flex items-center text-sm text-gray-600">
                    <i class="bi bi-clock-fill text-gray-500 mr-2"></i>
                    <span class="font-medium">${new Date(order.delivery_datetime).toLocaleString([], {month: 'short', day: 'numeric', hour: '2-digit', minute:'2-digit'})}</span>
                </div>
            </div>

            <button class="w-full py-2.5 text-sm font-semibold rounded-lg bg-green-50 text-green-700 cursor-default">
                <i class="bi bi-check-circle mr-1"></i> Delivery Completed
            </button>
        </div>
    `;
            });
        }
        // ===== MAP INTEGRATION (OpenStreetMap using Leaflet) =====
let map = null;
let markers = [];

function initMap() {
    map = L.map('active-map').setView([13.7563, 121.0583], 12); // Default view (Batangas area)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
}

function updateMapMarkers(orders) {
    if (!map) return;

    // Clear old markers
    markers.forEach(marker => map.removeLayer(marker));
    markers = [];

    orders.forEach(order => {
        if (order.user.latitude && order.user.longitude) {
            const lat = parseFloat(order.user.latitude);
            const lng = parseFloat(order.user.longitude);
            const marker = L.marker([lat, lng]).addTo(map)
                .bindPopup(`<b>${order.user.name}</b><br>${order.user.address}`);
            markers.push(marker);
        }
    });

    if (markers.length > 0) {
        const group = new L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.2));
    }
}
// ===== END MAP CODE =====


        function fetchDeliveries() {
            fetch('dashboard_backend.php')
                .then(res => res.json())
                .then(data => {
                    deliveryData = data;
                    const activeList = document.getElementById('active-list');
                    const today = new Date().toISOString().split('T')[0];

                    // --- Active ---
                    const activeOrders = data.active.filter(order =>
                        order.pickup_datetime.startsWith(today) || order.delivery_datetime.startsWith(today)
                    );

                    if (!activeOrders.length) {
                        activeList.innerHTML = `
                    <div class="flex items-center justify-center h-full">
                        <p class="text-center text-gray-400 text-sm">No active deliveries for today</p>
                    </div>
                `;
                    } else {
                        activeList.innerHTML = activeOrders.map(order => `
                    <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h4 class="font-bold text-gray-900 text-base">${order.user.name}</h4>
                                <p class="text-xs text-gray-500 mt-0.5">Order #${order.order_number}</p>
                            </div>
                            <span class="text-xs bg-green-100 text-green-700 px-2.5 py-1 rounded-full font-semibold flex items-center">
                                <i class="bi bi-truck mr-1"></i> On the Way
                            </span>
                        </div>

                        <div class="space-y-2 mb-3">
                            <div class="flex items-start text-sm text-gray-600">
                                <i class="bi bi-geo-alt-fill text-blue-500 mr-2 mt-0.5"></i>
                                <span class="flex-1">${order.user.address}</span>
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="bi bi-clock-fill text-orange-500 mr-2"></i>
                                <span class="font-medium">${order.pickup_datetime ? new Date(order.pickup_datetime).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : new Date(order.delivery_datetime).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
                            </div>
                        </div>

                        <button onclick="window.location.href='order_details.php?refill_id=${order.refill_id}'"
                            class="w-full py-2.5 text-sm font-semibold rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200 transition">
                            View Details
                        </button>
                    </div>
                `).join('');
                updateMapMarkers(activeOrders);

                    }

                    // Render pickup, delivery, delayed, and completed lists
                    renderPickupList();
                    renderDeliveryList();
                    renderDelayedList();
                    renderCompletedList();
                })
                .catch(console.error);
        }

        // Initialize periods based on current time when page loads
        document.addEventListener('DOMContentLoaded', () => {
            initializePeriods();
            // Update the display labels after setting the periods
            updatePickupPeriodDisplay();
            updateDeliveryPeriodDisplay();
            updateDelayedPeriodDisplay();
            updateCompletedPeriodDisplay();
            initMap();
            fetchDeliveries();
            setInterval(fetchDeliveries, 30000);

        });

        function startDelivery(id) {
            window.location.href = "order_details.php?refill_id=" + id;
        }
if ('geolocation' in navigator) {
  // send position every 20-30s (or use watchPosition)
  function sendPosition(pos) {
    const payload = {
      latitude: pos.coords.latitude,
      longitude: pos.coords.longitude,
      // include user/rider id from session if available in JS:
      user_id: <?= json_encode($_SESSION['user_id'] ?? null) ?>
    };
    fetch('update_rider_location.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
      cache: 'no-cache'
    }).catch(err => console.error('sendPosition err', err));
  }

  function errorHandler(err) {
    console.warn('Geolocation error', err);
  }

  // Option A: continuous watching (recommended)
  const watchId = navigator.geolocation.watchPosition(sendPosition, errorHandler, {
    enableHighAccuracy: true,
    maximumAge: 5000,
    timeout: 10000
  });

  // Option B: interval polling (if you prefer)
  // navigator.geolocation.getCurrentPosition(sendPosition, errorHandler, {enableHighAccuracy:true});
  // setInterval(()=> navigator.geolocation.getCurrentPosition(sendPosition, errorHandler, {enableHighAccuracy:true}), 30000);
} else {
  console.error('Geolocation not supported');
}
</script>

    </script>


</body>

</html>
