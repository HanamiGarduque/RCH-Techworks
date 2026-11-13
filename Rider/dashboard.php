<?php


    require_once __DIR__ . '/../session.php';
    redirectIfRoleMismatch('rider'); // only customers can access
    require_once '../Database/db_connection.php';

    $database = new Database();
    $db = $database->getConnect();


    $user_id = $_SESSION['id'];

    $rider_name = $_SESSION['name'] ?? "Rider";
    ?>
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

         /* Custom scrollbar */
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

         /* ===== MAP MARKER STYLES ===== */
         .rider-pulse {
             position: relative;
             width: 36px;
             height: 36px;
         }

         .rider-pulse::before {
             content: '';
             position: absolute;
             inset: 0;
             border-radius: 50%;
             background: rgba(16, 185, 129, 0.35);
             animation: riderPulse 1.8s ease-out infinite;
         }

         .rider-pulse .dot {
             position: absolute;
             inset: 0;
             border-radius: 50%;
             background: #10b981;
             border: 3px solid #fff;
             box-shadow: 0 2px 8px rgba(0, 0, 0, .35);
             display: flex;
             align-items: center;
             justify-content: center;
             color: #fff;
             font-size: 15px;
         }

         @keyframes riderPulse {
             0% {
                 transform: scale(1);
                 opacity: .9;
             }

             100% {
                 transform: scale(2.2);
                 opacity: 0;
             }
         }

         .customer-pin {
             width: 30px;
             height: 30px;
             border-radius: 50%;
             background: #2563eb;
             border: 3px solid #fff;
             box-shadow: 0 2px 8px rgba(0, 0, 0, .3);
             display: flex;
             align-items: center;
             justify-content: center;
             color: #fff;
             font-size: 13px;
         }
     </style>
 </head>

 <body class="bg-white min-h-screen flex justify-center items-center relative">

     <!-- Fixed-width container wrapper -->
     <div class="bg-white w-[375px] h-[750px] mx-auto relative rounded-3xl overflow-hidden border shadow-lg">

         <!-- Header -->
         <div class="bg-gradient-to-br from-blue-400 to-blue-600 text-white sticky top-0 z-50 w-full px-4 py-3">
             <div class="flex justify-between items-center">
                 <div>
                     <h2 class="text-lg font-bold tracking-tight">Welcome, Rider</h2>
                     <div id="datetime" class="text-xs text-blue-100 font-medium"></div>
                 </div>
                 <div class="flex items-center gap-3">
                     <a href="../Notification/rider_notifications.php"
                         class="relative text-white text-lg hover:scale-110 transition-transform duration-200">
                         <i class="bi bi-bell"></i>
                         <span class="absolute top-0 start-100 translate-x-1/2 -translate-y-1/2 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white animate-pulse"></span>
                     </a>
                     <a href="../Messaging/rider_messages.php"
                         class="relative text-white text-lg hover:scale-110 transition-transform duration-200">
                         <i class="bi bi-chat-dots"></i>
                         <span class="absolute top-0 start-100 translate-x-1/2 -translate-y-1/2 w-2.5 h-2.5 bg-green-500 rounded-full border-2 border-white animate-pulse"></span>
                     </a>
                     <a href="edit_profile.php"
                         class="text-white text-lg hover:scale-110 transition-transform duration-200">
                         <i class="bi bi-person-circle"></i>
                     </a>
                 </div>
             </div>
         </div>

         <!-- Dashboard Buttons Container -->
         <div class="bg-white sticky top-16 z-40 w-full px-5 py-4">
             <div class="grid grid-cols-3 gap-3">
                 <button onclick="showSection('active-deliveries', this)" class="section-btn bg-blue-100 text-blue-600 font-semibold py-2 px-4 rounded-3xl text-xs shadow-sm transition-all duration-200">Active</button>
                 <button onclick="showSection('pending-deliveries', this)" class="section-btn bg-blue-100 text-blue-600 font-semibold py-2 px-4 rounded-3xl text-xs shadow-sm transition-all duration-200">Pending</button>
                 <button onclick="showSection('completed-deliveries', this)" class="section-btn bg-blue-100 text-blue-600 font-semibold py-2 px-4 rounded-3xl text-xs shadow-sm transition-all duration-200">Completed</button>
             </div>
         </div>

         <!-- ACTIVE DELIVERIES -->
         <div class="px-5 pb-5" id="active-deliveries">
             <h3 class="text-md font-semibold text-blue-600 mb-3">Active Deliveries</h3>

             <!-- Map Integration (live rider GPS + customer destinations) -->
             <div class="relative mb-4">
                 <div id="active-map"
                     class="rounded-xl h-40 border border-gray-300 overflow-hidden">
                 </div>

                 <!-- GPS status chip -->
                 <div id="gps-status"
                     class="absolute top-2 left-2 z-[1000] max-w-[210px] bg-white/95 text-gray-700 text-[10px] font-semibold px-2.5 py-1 rounded-full shadow flex items-center gap-1.5">
                     <i class="bi bi-hourglass-split text-amber-500"></i>
                     <span>Locating you...</span>
                 </div>

                 <!-- Map buttons -->
                 <div class="absolute bottom-2 right-2 z-[1000] flex flex-col gap-2">
                     <button onclick="recenterOnRider()" title="Go to my location"
                         class="w-8 h-8 rounded-full bg-white shadow flex items-center justify-center text-blue-600 hover:bg-blue-50">
                         <i class="bi bi-crosshair"></i>
                     </button>
                     <button onclick="openFullMap()" title="Open full map"
                         class="w-8 h-8 rounded-full bg-blue-600 shadow flex items-center justify-center text-white hover:bg-blue-700">
                         <i class="bi bi-arrows-fullscreen"></i>
                     </button>
                 </div>
             </div>



             <div id="active-list" class="bg-gray-100 p-2 rounded-md shadow-inner space-y-4 overflow-y-auto custom-scrollbar h-[330px] pb-5 pr-2"></div>
         </div>

         <!-- PENDING DELIVERIES -->
         <div class="px-5 pb-20 hidden relative" id="pending-deliveries">
             <h3 class="text-md font-semibold text-blue-600 mb-3">Pending Deliveries</h3>

             <!-- TABS - No gap between buttons -->
             <div class="flex mb-4 bg-gray-200 rounded-lg overflow-hidden">
                 <button id="pending-tab-pickup"
                     class="flex-1 py-2 text-xs font-semibold bg-blue-500 text-white transition-colors duration-200"
                     onclick="showPendingTab('pickup')">
                     For Pickup
                 </button>

                 <button id="pending-tab-delivery"
                     class="flex-1 py-2 text-xs font-semibold bg-gray-200 text-gray-700 transition-colors duration-200"
                     onclick="showPendingTab('delivery')">
                     For Delivery
                 </button>
             </div>

             <!-- PICKUP LIST -->
             <div id="pickup-list" class="bg-gray-100 p-2 rounded-md shadow-inner space-y-4 overflow-y-auto custom-scrollbar h-[450px] pb-5 pr-2"></div>

             <!-- DELIVERY LIST -->
             <div id="delivery-list" class="bg-gray-100 p-2 rounded-md shadow-inner space-y-4 overflow-y-auto custom-scrollbar h-[450px] pb-5 pr-2 hidden"></div>

             <!-- Floating Period Switcher for Pickup -->
             <div id="time-switcher-pickup"
                 class="absolute bottom-7 left-1/2 -translate-x-1/2
                       bg-blue-600 text-white shadow-lg rounded-full px-2 py-1
                       flex items-center space-x-6 z-50 border">
                 <button id="prev-period-pickup"
                     class="w-7 h-7 flex items-center justify-center
                           rounded-full bg-white shadow text-blue-600 font-bold hover:bg-gray-100 transition">
                     ←
                 </button>
                 <span id="current-period-pickup" class="text-xs text-white font-semibold">
                     Morning
                 </span>
                 <button id="next-period-pickup"
                     class="w-7 h-7 flex items-center justify-center
                           rounded-full bg-white shadow text-blue-600 font-bold hover:bg-gray-100 transition">
                     →
                 </button>
             </div>

             <!-- Floating Period Switcher for Delivery -->
             <div id="time-switcher-delivery"
                 class="absolute bottom-7 left-1/2 -translate-x-1/2
                       bg-blue-600 text-white shadow-lg rounded-full px-2 py-1
                       flex items-center space-x-6 z-50 border hidden">
                 <button id="prev-period-delivery"
                     class="w-7 h-7 flex items-center justify-center
                           rounded-full bg-white shadow text-blue-600 font-bold hover:bg-gray-100 transition">
                     ←
                 </button>
                 <span id="current-period-delivery" class="text-xs text-white font-semibold">
                     Morning
                 </span>
                 <button id="next-period-delivery"
                     class="w-7 h-7 flex items-center justify-center
                           rounded-full bg-white shadow text-blue-600 font-bold hover:bg-gray-100 transition">
                     →
                 </button>
             </div>
         </div>

         <!-- HISTORY SECTION (previously COMPLETED DELIVERIES) -->
         <div class="px-5 pb-20 hidden relative" id="completed-deliveries">
             <h3 class="text-md font-semibold text-blue-600 mb-3">Order History</h3>

             <!-- TABS - Delayed and Completed -->
             <div class="flex mb-4 bg-gray-200 rounded-lg overflow-hidden">
                 <button id="history-tab-delayed"
                     class="flex-1 py-2 text-xs font-semibold bg-blue-500 text-white transition-colors duration-200"
                     onclick="showHistoryTab('delayed')">
                     Delayed
                 </button>

                 <button id="history-tab-completed"
                     class="flex-1 py-2 text-xs font-semibold bg-gray-200 text-gray-700 transition-colors duration-200"
                     onclick="showHistoryTab('completed')">
                     Completed
                 </button>
             </div>

             <!-- DELAYED LIST -->
             <div id="delayed-list" class="bg-gray-100 p-2 rounded-md shadow-inner space-y-4 overflow-y-auto custom-scrollbar h-[450px] pb-5 pr-2"></div>

             <!-- COMPLETED LIST -->
             <div id="completed-list" class="bg-gray-100 p-2 rounded-md shadow-inner space-y-4 overflow-y-auto custom-scrollbar h-[450px] pb-5 pr-2 hidden"></div>

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

                        <button onclick="window.location.href='order_details.php?order_number=${order.order_number}'"
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

               <button onclick="window.location.href='order_details.php?order_number=${order.order_number}'"
                    class="w-full py-2.5 text-sm font-semibold rounded-lg bg-blue-500 text-white hover:bg-blue-600 transition">
                    Start D
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
                        <button onclick="window.location.href='order_details.php?order_number=${order.order_number}'"
                    class="w-full py-2.5 text-sm font-semibold rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200 transition">
                <i class="bi bi-check-circle mr-1"></i> Delivery Completed
            </button>
        </div>
    `;
             });
         }

         // =====================================================================
         // MAP INTEGRATION (OpenStreetMap using Leaflet) + LIVE RIDER GPS
         // =====================================================================
         let map = null;
         let markers = [];
         let riderMarker = null;
         let accuracyCircle = null;
         let gpsWatchId = null;
         let firstFix = true; // first GPS fix centers the map on the rider
         let followRider = true; // map follows the rider until the map is dragged
         let trackUserId = null; // customer user_id used by the "open full map" button

         const riderIcon = L.divIcon({
             html: '<div class="rider-pulse"><div class="dot"><i class="bi bi-truck"></i></div></div>',
             className: '',
             iconSize: [36, 36],
             iconAnchor: [18, 18],
             popupAnchor: [0, -18]
         });

         const customerIcon = L.divIcon({
             html: '<div class="customer-pin"><i class="bi bi-house-fill"></i></div>',
             className: '',
             iconSize: [30, 30],
             iconAnchor: [15, 15],
             popupAnchor: [0, -15]
         });

         function initMap() {
             // Temporary view (Lipa City) until the first GPS fix arrives
             map = L.map('active-map').setView([13.9411, 121.1635], 13);
             L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                 maxZoom: 19,
                 attribution: '&copy; OpenStreetMap contributors'
             }).addTo(map);

             // Stop auto-following once the rider drags the map
             map.on('dragstart', () => {
                 followRider = false;
             });

             // Keep the map sized correctly when the Active tab is hidden/shown
             if ('ResizeObserver' in window) {
                 new ResizeObserver(() => map.invalidateSize()).observe(document.getElementById('active-map'));
             }
         }

         function setGpsStatus(text, type) {
             const el = document.getElementById('gps-status');
             const icons = {
                 ok: '<i class="bi bi-broadcast text-green-500"></i>',
                 wait: '<i class="bi bi-hourglass-split text-amber-500"></i>',
                 warn: '<i class="bi bi-exclamation-triangle-fill text-amber-500"></i>',
                 error: '<i class="bi bi-x-circle-fill text-red-500"></i>'
             };
             el.innerHTML = (icons[type] || icons.wait) + '<span>' + text + '</span>';
         }

         function handlePosition(pos) {
             const lat = pos.coords.latitude;
             const lng = pos.coords.longitude;
             const acc = pos.coords.accuracy || 0;

             if (!riderMarker) {
                 riderMarker = L.marker([lat, lng], {
                         icon: riderIcon,
                         zIndexOffset: 1000
                     })
                     .addTo(map)
                     .bindPopup('<b>You are here</b>');
                 accuracyCircle = L.circle([lat, lng], {
                     radius: acc,
                     color: '#10b981',
                     weight: 1,
                     fillColor: '#10b981',
                     fillOpacity: 0.12
                 }).addTo(map);
             } else {
                 riderMarker.setLatLng([lat, lng]);
                 accuracyCircle.setLatLng([lat, lng]);
                 accuracyCircle.setRadius(acc);
             }

             riderMarker.setPopupContent('<b>You are here</b><br>Accuracy: ±' + Math.round(acc) + ' m');

             if (firstFix) {
                 map.setView([lat, lng], acc > 1000 ? 13 : 16);
                 firstFix = false;
             } else if (followRider) {
                 map.panTo([lat, lng]);
             }

             if (acc > 500) {
                 setGpsStatus('Weak GPS (±' + Math.round(acc) + ' m) - approximate only', 'warn');
             } else {
                 setGpsStatus('Live • ±' + Math.round(acc) + ' m', 'ok');
             }
         }

         function handleGeoError(err) {
             console.warn('Map geolocation error', err);
             if (err.code === 1) {
                 setGpsStatus('Location blocked - allow it in browser settings', 'error');
             } else if (err.code === 2) {
                 setGpsStatus('Position unavailable - check GPS / signal', 'warn');
             } else {
                 setGpsStatus('Waiting for GPS signal...', 'wait');
             }
         }

         function startLiveTracking() {
             if (!('geolocation' in navigator)) {
                 setGpsStatus('GPS not supported on this device', 'error');
                 return;
             }

             // Browsers only allow location on HTTPS or on "localhost"
             if (!window.isSecureContext) {
                 setGpsStatus('Location needs HTTPS (or localhost)', 'error');
                 console.error('Geolocation blocked: not a secure context. Use HTTPS or http://localhost');
                 return;
             }

             const options = {
                 enableHighAccuracy: true, // use the real GPS chip when available
                 maximumAge: 0, // never use a cached position
                 timeout: 20000
             };

             // Quick first fix, then continuous live updates
             navigator.geolocation.getCurrentPosition(handlePosition, handleGeoError, options);
             gpsWatchId = navigator.geolocation.watchPosition(handlePosition, handleGeoError, options);
         }

         function recenterOnRider() {
             followRider = true;
             if (riderMarker) {
                 map.setView(riderMarker.getLatLng(), 16);
                 riderMarker.openPopup();
             } else {
                 setGpsStatus('Still locating you...', 'wait');
             }
         }

         function openFullMap() {
             if (trackUserId === null) {
                 alert('No active delivery to track yet.');
                 return;
             }
             window.location.href = 'full_map.php?user_id=' + encodeURIComponent(trackUserId);
         }

         function updateMapMarkers(orders) {
             if (!map) return;

             // Clear old markers
             markers.forEach(marker => map.removeLayer(marker));
             markers = [];
             trackUserId = null;

             orders.forEach(order => {
                 // remember the first customer's id for the full map button
                 if (trackUserId === null) {
                     const u = order.user || {};
                     const uid = u.user_id ?? u.id ?? order.user_id ?? order.customer_id ?? null;
                     if (uid !== null) trackUserId = uid;
                 }

                 if (order.user.latitude && order.user.longitude) {
                     const lat = parseFloat(order.user.latitude);
                     const lng = parseFloat(order.user.longitude);
                     const marker = L.marker([lat, lng], {
                             icon: customerIcon
                         }).addTo(map)
                         .bindPopup(`<b>${order.user.name}</b><br>${order.user.address}`);
                     markers.push(marker);
                 }
             });

             // Only fit to the customers while the rider has no GPS fix yet
             if (markers.length > 0 && !riderMarker) {
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

                        <button onclick="window.location.href='order_details.php?order_number=${order.order_number}'"
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
             startLiveTracking(); // rider's device GPS on the map
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
                     headers: {
                         'Content-Type': 'application/json'
                     },
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