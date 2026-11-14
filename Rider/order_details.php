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
     <title>Order Details | Rider</title>

     <script src="https://cdn.tailwindcss.com"></script>
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
     <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
     <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

     <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

     <style>
         body {
             font-family: "Inter", sans-serif;
         }

         h1,
         h2,
         h3,
         .font-display {
             font-family: "Poppins", sans-serif;
             font-weight: 700;
         }

         .map-container {
             height: 300px;
             border-radius: 12px;
             overflow: hidden;
         }

         .timeline-item {
             position: relative;
             padding-left: 40px;
             margin-bottom: 24px;
         }

         .timeline-item::before {
             content: '';
             position: absolute;
             left: 8px;
             top: 6px;
             width: 16px;
             height: 16px;
             border-radius: 50%;
             background-color: #e5e7eb;
             border: 2px solid white;
         }

         .timeline-item.active::before {
             background-color: #2563eb;
             box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
         }

         .timeline-item.completed::before {
             background-color: #10b981;
         }

         .timeline-connector {
             position: absolute;
             left: 15px;
             top: 24px;
             width: 2px;
             height: calc(100% + 24px);
             background-color: #e5e7eb;
         }

         .timeline-item.completed .timeline-connector {
             background-color: #10b981;
         }

         .modal-overlay {
             display: none;
             position: fixed;
             top: 0;
             left: 0;
             width: 100%;
             height: 100%;
             background: rgba(0, 0, 0, 0.5);
             z-index: 50;
             align-items: center;
             justify-content: center;
         }

         .modal-overlay.active {
             display: flex;
         }

         .loading-spinner {
             display: inline-block;
             width: 16px;
             height: 16px;
             border: 2px solid rgba(255, 255, 255, 0.3);
             border-top-color: white;
             border-radius: 50%;
             animation: spin 0.8s linear infinite;
         }

         @keyframes spin {
             to {
                 transform: rotate(360deg);
             }
         }

         .btn-disabled {
             opacity: 0.5;
             cursor: not-allowed;
         }

         .status-badge {
             display: inline-flex;
             align-items: center;
             gap: 6px;
             padding: 6px 12px;
             border-radius: 20px;
             font-size: 12px;
             font-weight: 600;
         }

         .phone-frame {
             display: flex;
             flex-direction: column;
             height: 100vh;
             max-height: 900px;
         }

         .scrollable-content {
             flex: 1;
             overflow-y: auto;
             overflow-x: hidden;
         }
     </style>
 </head>

 <body class="bg-white min-h-screen flex justify-center items-center relative">

     <!-- PHONE FRAME -->
     <div class="phone-frame bg-white w-[375px] max-h-[750px] rounded-2xl shadow-2xl">

         <!-- HEADER -->
         <div class="bg-gradient-to-br from-blue-400 to-blue-600 text-white px-4 py-3 rounded-t-3xl flex-shrink-0">
             <div class="flex items-center justify-between mb-1">
                 <button onclick="history.back()" class="text-xl hover:bg-blue-500 rounded-lg transition">
                     <i class="bi bi-chevron-left"></i>
                 </button>
                 <span id="status-badge" class="status-badge bg-white text-blue-600">
                     <i class="bi bi-hourglass-split"></i>
                     <span id="status-text">Loading</span>
                 </span>
             </div>

             <div>
                 <h1 id="order-id" class="text-lg font-bold">Loading...</h1>
                 <div class="flex items-center gap-2 text-blue-100">
                     <i class="bi bi-person-circle text-xl"></i>
                     <p id="customer-name" class="text-sm font-medium">Loading...</p>
                 </div>
             </div>
         </div>

         <!-- SCROLLABLE CONTENT -->
         <div class="scrollable-content">

             <!-- MAP SECTION -->
             <div class="px-6 pt-3 pb-4">
                 <div class="flex items-center gap-2 mb-2">
                     <i class="bi bi-geo-alt-fill text-blue-600 text-lg"></i>
                     <h2 class="text-md font-semibold text-blue-600">Live Location</h2>
                 </div>
                 <div class="map-container bg-gray-200 border-2 border-blue-100" id="map"></div>
             </div>

             <!-- DELIVERY INFO CARD -->
             <div class="px-6 pb-4">
                 <div class="bg-blue-50 border-l-4 border-blue-600 rounded-lg p-4">
                     <div class="grid grid-cols-2 gap-4">
                         <div>
                             <p class="text-xs text-gray-600 font-semibold mb-1">PICKUP</p>
                             <p class="text-xs font-bold text-gray-900" id="pickup-date">--/--/----</p>
                             <p class="text-sm font-bold text-blue-600" id="pickup-time">--:--</p>
                         </div>
                         <div>
                             <p class="text-xs text-gray-600 font-semibold mb-1">DELIVERY</p>
                             <p class="text-xs font-bold text-gray-900" id="delivery-date">--/--/----</p>
                             <p class="text-sm font-bold text-blue-600" id="delivery-time">--:--</p>
                         </div>
                     </div>
                 </div>
             </div>

             <!-- ADDRESS SECTION -->
             <div class="px-6 pb-4">
                 <div class="bg-white border border-gray-200 rounded-xl p-4 hover:border-blue-300 transition">
                     <div class="flex items-start gap-3">
                         <div class="text-blue-600 text-xl mt-1">
                             <i class="bi bi-house-door"></i>
                         </div>
                         <div class="flex-1">
                             <p class="text-xs font-semibold text-gray-500 mb-1">DELIVERY ADDRESS</p>
                             <p id="customer-address" class="text-sm font-medium text-gray-900">Loading...</p>
                             <p id="customer-phone" class="text-xs text-gray-600 mt-2 flex items-center gap-1">
                                 <i class="bi bi-telephone"></i> <span id="phone-text">Loading...</span>
                             </p>
                         </div>
                     </div>
                 </div>
             </div>

             <!-- PROGRESS TIMELINE -->
             <div class="px-6 pb-4">
                 <h3 class="text-md font-semibold text-blue-600 mb-4 flex items-center gap-2">
                     <i class="bi bi-arrow-repeat text-blue-600"></i> Delivery Progress
                 </h3>
                 <div class="relative">
                     <div id="timeline-container"></div>
                 </div>
             </div>

             <!-- ITEMS CARD -->
             <div class="px-6 pb-4">
                 <h3 class="text-md font-semibold text-blue-600 mb-3 flex items-center gap-2">
                     <i class="bi bi-box2 text-blue-600"></i> Order Items
                 </h3>
                 <div id="items-list" class="space-y-3"></div>

                 <!-- SUBTOTAL -->
                 <div class="mt-4 pt-4 border-t border-gray-200">
                     <div class="flex justify-between items-center">
                         <span class="text-sm font-semibold text-gray-700">Total Amount:</span>
                         <span class="text-lg font-bold text-blue-600" id="total-amount">₱0.00</span>
                     </div>
                 </div>
             </div>

             <div class="pb-32"></div>

         </div>

         <!-- FIXED ACTION BUTTONS -->
         <div class="bg-white border-t border-gray-200 px-6 py-4 flex-shrink-0 shadow-2xl">
             <div id="action-buttons" class="space-y-3"></div>
         </div>

     </div>

     <!-- DELAY NOTE MODAL -->
     <div id="delay-modal" class="modal-overlay">
         <div class="bg-white rounded-2xl p-6 w-11/12 max-w-sm max-h-96 flex flex-col">
             <div class="flex items-center justify-between mb-4">
                 <h3 class="text-xl font-bold text-gray-900">Reason for Delay</h3>
                 <button onclick="closeDelayModal()" class="text-gray-400 hover:text-gray-600">
                     <i class="bi bi-x-lg text-xl"></i>
                 </button>
             </div>

             <textarea id="delay-reason" placeholder="Please enter the reason for delay..." class="flex-1 w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 resize-none mb-4"></textarea>

             <div class="flex gap-3">
                 <button onclick="closeDelayModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium">
                     Cancel
                 </button>
                 <button id="submit-delay-btn" onclick="submitDelayReason()" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium flex items-center justify-center gap-2">
                     <span>Submit</span>
                 </button>
             </div>
         </div>
     </div>

     <script>
         const urlParams = new URLSearchParams(window.location.search);
         const orderNumber = urlParams.get("order_number");

         if (!orderNumber) {
             alert("Missing order_number in URL");
             throw new Error("Missing order_number");
         }
         let currentOrder = null;
         let refillId = null; // Add this
         let map = null;
         let riderMarker = null;
         let userMarker = null;
         let delayAction = null;

         const STATUS_CONFIG = {
             pending: {
                 step: 1,
                 color: "bg-gray-500",
                 text: "Pending",
                 icon: "bi-clock"
             },
             assigned: {
                 step: 1,
                 color: "bg-yellow-500",
                 text: "Assigned",
                 icon: "bi-person-check"
             },
             pickup_in_progress: {
                 step: 2,
                 color: "bg-blue-600",
                 text: "Pickup In Progress",
                 icon: "bi-box-seam"
             },
             pickup_delayed: {
                 step: 2,
                 color: "bg-orange-500",
                 text: "Pickup Delayed",
                 icon: "bi-exclamation-triangle"
             },
             picked_up: {
                 step: 3,
                 color: "bg-green-500",
                 text: "Picked Up",
                 icon: "bi-check-circle"
             },
             on_delivery: {
                 step: 4,
                 color: "bg-blue-700",
                 text: "On Delivery",
                 icon: "bi-truck"
             },
             delivery_delayed: {
                 step: 4,
                 color: "bg-red-500",
                 text: "Delivery Delayed",
                 icon: "bi-exclamation-circle"
             },
             delivered: {
                 step: 5,
                 color: "bg-green-600",
                 text: "Delivered",
                 icon: "bi-check-lg"
             },
             cancelled: {
                 step: -1,
                 color: "bg-gray-700",
                 text: "Cancelled",
                 icon: "bi-x-circle"
             }
         };

         // Initialize map
         function initMap() {
             if (map) map.remove();
             const defaultLat = 14.5995;
             const defaultLng = 120.9842;

             map = L.map('map').setView([defaultLat, defaultLng], 15);
             L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                 attribution: '© OpenStreetMap contributors',
                 maxZoom: 19
             }).addTo(map);

             riderMarker = L.circleMarker([defaultLat, defaultLng], {
                 radius: 8,
                 fillColor: '#2563eb',
                 color: '#1e40af',
                 weight: 2,
                 opacity: 1,
                 fillOpacity: 0.8
             }).addTo(map).bindPopup('Your Location');

             userMarker = L.circleMarker([defaultLat + 0.01, defaultLng + 0.01], {
                 radius: 8,
                 fillColor: '#10b981',
                 color: '#059669',
                 weight: 2,
                 opacity: 1,
                 fillOpacity: 0.8
             }).addTo(map).bindPopup('Delivery Location');
         }

         function fetchOrderData() {
             fetch(`order_details_backend.php?order_number=${orderNumber}`) // Fix filename
                 .then(res => res.json())
                 .then(data => {
                     if (!data.success) {
                         alert(data.error || "Order not found");
                         return;
                     }
                     currentOrder = data.order;
                     refillId = data.order.refill_id; // Add this line
                     const items = data.items;

                     populateHeader();
                     populateAddressInfo();
                     populateTimings();
                     populateTimeline();
                     populateItems(items, data.total_subtotal);
                     populateActionButtons();
                     initMap();
                 })
                 .catch(err => {
                     console.error("Fetch error:", err);
                     alert("Failed to load order details");
                 });
         }

         function populateHeader() {
             document.getElementById("order-id").textContent = `#${currentOrder.order_number}`;
             document.getElementById("customer-name").textContent = currentOrder.customer_name;

             const config = STATUS_CONFIG[currentOrder.order_status];
             const badgeEl = document.getElementById("status-badge");
             badgeEl.className = `status-badge ${config.color} text-white`;
             badgeEl.innerHTML = `<i class="bi ${config.icon}"></i><span id="status-text">${config.text}</span>`;
         }

         function populateAddressInfo() {
             document.getElementById("customer-address").textContent = currentOrder.customer_address;
             document.getElementById("phone-text").textContent = currentOrder.customer_phone;
         }

         function populateTimings() {
             const pickupDateTime = new Date(currentOrder.pickup_datetime);
             const deliveryDateTime = new Date(currentOrder.delivery_datetime);

             // Format date as MM/DD/YYYY
             const pickupDate = pickupDateTime.toLocaleDateString('en-US', {
                 month: '2-digit',
                 day: '2-digit',
                 year: 'numeric'
             });
             const deliveryDate = deliveryDateTime.toLocaleDateString('en-US', {
                 month: '2-digit',
                 day: '2-digit',
                 year: 'numeric'
             });

             // Format time
             const pickupTime = pickupDateTime.toLocaleTimeString('en-US', {
                 hour: '2-digit',
                 minute: '2-digit',
                 hour12: true
             });
             const deliveryTime = deliveryDateTime.toLocaleTimeString('en-US', {
                 hour: '2-digit',
                 minute: '2-digit',
                 hour12: true
             });

             document.getElementById("pickup-date").textContent = pickupDate;
             document.getElementById("pickup-time").textContent = pickupTime;
             document.getElementById("delivery-date").textContent = deliveryDate;
             document.getElementById("delivery-time").textContent = deliveryTime;
         }



         function populateItems(items, totalSubtotal) {
             const itemsList = document.getElementById("items-list");
             const totalAmountEl = document.getElementById("total-amount");

             itemsList.innerHTML = '';

             if (items.length === 0) {
                 itemsList.innerHTML = '<p class="text-gray-500 text-sm">No items found</p>';
                 return;
             }

             items.forEach(item => {
                 // Use item_name from inventory if available, otherwise fall back to gallon_type
                 const itemName = item.item_name || item.gallon_type;
                 const itemSize = item.gallon_size;

                 itemsList.innerHTML += `
                    <div class="bg-white border border-gray-200 rounded-lg p-3 flex items-center justify-between hover:border-blue-300 transition">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900">${itemName}</p>
                            <p class="text-xs text-gray-600">${itemSize} • Qty: ${item.quantity}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-600">₱${parseFloat(item.unit_price).toFixed(2)} each</p>
                            <p class="text-sm font-bold text-blue-600">₱${parseFloat(item.subtotal).toFixed(2)}</p>
                        </div>
                    </div>
                `;
             });

             // Display total amount
             totalAmountEl.textContent = `₱${parseFloat(totalSubtotal || currentOrder.total_amount).toFixed(2)}`;
         }

         function populateActionButtons() {
             const buttonsEl = document.getElementById("action-buttons");
             const status = currentOrder.order_status;

             let html = '';

             if (status === 'pending' || status === 'assigned') {
                 html = `
            <button onclick="startPickup()" class="w-full py-3 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition flex items-center justify-center gap-2 shadow-lg">
                <i class="bi bi-box-seam"></i> Start Pickup
            </button>
        `;
             } else if (status === 'pickup_in_progress') {
                 html = `
            <button onclick="markPickedUp()" class="w-full py-3 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 transition flex items-center justify-center gap-2 shadow-lg mb-2">
                <i class="bi bi-check-circle"></i> Mark as Picked Up
            </button>
            <button onclick="openDelayModal('pickup')" class="w-full py-3 bg-orange-500 text-white rounded-xl font-bold hover:bg-orange-600 transition flex items-center justify-center gap-2 shadow-lg">
                <i class="bi bi-exclamation-triangle"></i> Delay
            </button>
        `;
             } else if (status === 'pickup_delayed') {
                 // ADD THIS SECTION
                 html = `
            <button onclick="requestReschedule('pickup')" class="w-full py-3 bg-purple-600 text-white rounded-xl font-bold hover:bg-purple-700 transition flex items-center justify-center gap-2 shadow-lg">
                <i class="bi bi-calendar-event"></i> Request Reschedule from Admin
            </button>
        `;
             } else if (status === 'picked_up') {
                 const canStartDelivery = canShowStartDeliveryButton();
                 html = `
            <button onclick="startDelivery()" ${!canStartDelivery ? 'disabled' : ''} class="w-full py-3 ${canStartDelivery ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed'} text-white rounded-xl font-bold transition flex items-center justify-center gap-2 shadow-lg">
                <i class="bi bi-truck"></i> Start Delivery
            </button>
            ${!canStartDelivery ? '<p class="text-xs text-gray-600 text-center mt-2">Available 30 minutes before delivery time</p>' : ''}
        `;
             } else if (status === 'on_delivery') {
                 html = `
            <button onclick="goToQRScanner()" class="w-full py-3 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 transition flex items-center justify-center gap-2 shadow-lg mb-2">
        <i class="bi bi-check-lg"></i> Complete Delivery
        </button>

            <button onclick="openDelayModal('delivery')" class="w-full py-3 bg-red-500 text-white rounded-xl font-bold hover:bg-red-600 transition flex items-center justify-center gap-2 shadow-lg">
                <i class="bi bi-exclamation-circle"></i> Delay
            </button>
        `;
             } else if (status === 'delivery_delayed') {
                 // ADD THIS SECTION
                 html = `
            
            <button onclick="requestReschedule('delivery')" class="w-full py-3 bg-purple-600 text-white rounded-xl font-bold hover:bg-purple-700 transition flex items-center justify-center gap-2 shadow-lg">
                <i class="bi bi-calendar-event"></i> Request Reschedule from Admin
            </button>
        `;
             } else if (status === 'delivered' || status === 'cancelled') {
                 html = `
            <button disabled class="w-full py-3 bg-gray-400 text-white rounded-xl font-bold cursor-not-allowed flex items-center justify-center gap-2 shadow-lg">
                <i class="bi bi-check-lg"></i> ${status === 'delivered' ? 'Order Completed' : 'Order Cancelled'}
            </button>
        `;
             }

             buttonsEl.innerHTML = html;
         }

         function requestReschedule(type = 'pickup') {
             const actionType = type === 'delivery' ? 'delivery' : 'pickup';
             const actionText = type === 'delivery' ? 'delivery delay' : 'pickup delay';

             if (!confirm(`Send reschedule request to admin? This will notify the admin about the ${actionText}.`)) {
                 return;
             }

             const btn = event.target.closest('button');
             if (btn) {
                 btn.disabled = true;
                 btn.innerHTML = '<div class="loading-spinner"></div> Sending...';
             }

             const formData = new FormData();
             formData.append('order_number', currentOrder.order_number);
             formData.append('refill_id', currentOrder.refill_id);
             formData.append('delay_reason', currentOrder.delay_reason || `${actionType.charAt(0).toUpperCase() + actionType.slice(1)} delayed - reschedule requested`);
             formData.append('delay_type', actionType);

             fetch('request_reschedule.php', {
                     method: 'POST',
                     body: formData
                 })
                 .then(res => res.json())
                 .then(data => {
                     if (data.success) {
                         alert('Reschedule request sent to admin successfully!');
                     } else {
                         alert(data.error || 'Failed to send reschedule request');
                     }
                     populateActionButtons();
                 })
                 .catch(err => {
                     console.error("Reschedule request error:", err);
                     alert('Error sending reschedule request');
                     populateActionButtons();
                 });
         }

         function goToQRScanner() {
             const refillId = CURRENT_REFILL_ID; // You already have this
             const customer = CURRENT_CUSTOMER_NAME; // You already have this
             const lat = CURRENT_LAT;
             const lng = CURRENT_LNG;

             window.location.href = `scan_qr.php?refill_id=${refillId}&customer=${encodeURIComponent(customer)}&lat=${lat}&lng=${lng}`;
         }

         function populateTimeline() {
             const currentStatus = currentOrder.order_status;

             const steps = [{
                     step: 1,
                     label: "Order Placed",
                     statuses: ["pending", "assigned"]
                 },
                 {
                     step: 2,
                     label: currentStatus === "pickup_delayed" ? "Pickup Delayed" : "Pickup In Progress",
                     statuses: ["pickup_in_progress", "pickup_delayed"]
                 },
                 {
                     step: 3,
                     label: "Picked Up",
                     statuses: ["picked_up"]
                 },
                 {
                     step: 4,
                     label: currentStatus === "delivery_delayed" ? "Delivery Delayed" : "On Delivery",
                     statuses: ["on_delivery", "delivery_delayed"]
                 },
                 {
                     step: 5,
                     label: "Delivered",
                     statuses: ["delivered"]
                 }
             ];

             const currentStep = STATUS_CONFIG[currentStatus].step;
             let html = '';

             steps.forEach((item, index) => {
                 const isActive = item.step === currentStep;
                 const isCompleted = item.step < currentStep;
                 const hasConnector = index < steps.length - 1;

                 html += `
            <div class="timeline-item ${isActive ? 'active' : ''} ${isCompleted ? 'completed' : ''}">
                ${hasConnector ? '<div class="timeline-connector"></div>' : ''}
                <div class="flex flex-col">
                    <p class="text-sm font-semibold ${isActive ? 'text-blue-600' : isCompleted ? 'text-green-600' : 'text-gray-900'}">${item.label}</p>
                    <p class="text-xs text-gray-600 mt-1">Step ${item.step} of 5</p>
                </div>
            </div>
        `;
             });

             document.getElementById("timeline-container").innerHTML = html;
         }

         function canShowStartDeliveryButton() {
             const now = new Date();
             const deliveryTime = new Date(currentOrder.delivery_datetime);
             const thirtyMinutesBefore = new Date(deliveryTime.getTime() - 30 * 60000);
             return now >= thirtyMinutesBefore;
         }

         function updateOrderStatus(newStatus, delayNote = null) {
             console.log('Updating status to:', newStatus); // Debug log
             console.log('Refill ID:', refillId); // Debug log

             if (!refillId) {
                 alert('Error: Refill ID not found');
                 return;
             }

             const submitBtn = event?.target?.closest('button');
             if (submitBtn) {
                 submitBtn.disabled = true;
                 submitBtn.innerHTML = '<div class="loading-spinner"></div> Processing...';
             }

             const formData = new FormData();
             formData.append('refill_id', refillId);
             formData.append('new_status', newStatus);
             if (delayNote) formData.append('delay_note', delayNote);

             // FIXED: Correct filename
             fetch('order_status_update.php', {
                     method: 'POST',
                     body: formData
                 })
                 .then(res => res.json())
                 .then(data => {
                     console.log('Response:', data); // Debug log
                     if (data.success) {
                         currentOrder.order_status = newStatus;
                         populateHeader();
                         populateTimeline();
                         populateActionButtons();
                         alert('Order status updated successfully!');
                     } else {
                         alert(data.error || 'Failed to update order status');
                         populateActionButtons();
                     }
                 })
                 .catch(err => {
                     console.error("Update error:", err);
                     alert('Error updating order status');
                     populateActionButtons();
                 });
         }
         // Action button handlers
         function startPickup() {
             if (confirm('Start pickup for this order?')) {
                 updateOrderStatus('pickup_in_progress');
             }
         }

         function markPickedUp() {
             if (confirm('Mark order as picked up?')) {
                 updateOrderStatus('picked_up');
             }
         }

         function startDelivery() {
             if (!canShowStartDeliveryButton()) {
                 alert('Cannot start delivery yet. Available 30 minutes before delivery time.');
                 return;
             }
             if (confirm('Start delivery for this order?')) {
                 updateOrderStatus('on_delivery');
             }
         }

         function completeDelivery() {
             if (confirm('Mark order as delivered?')) {
                 updateOrderStatus('delivered');
             }
         }

         function openDelayModal(action) {
             delayAction = action;
             document.getElementById("delay-modal").classList.add("active");
             document.getElementById("delay-reason").value = '';
         }

         function closeDelayModal() {
             document.getElementById("delay-modal").classList.remove("active");
             delayAction = null;
         }

         function submitDelayReason() {
             const reason = document.getElementById("delay-reason").value.trim();
             if (!reason) {
                 alert('Please enter a reason for delay');
                 return;
             }

             const newStatus = delayAction === 'pickup' ? 'pickup_delayed' : 'delivery_delayed';
             closeDelayModal();
             updateOrderStatus(newStatus, reason);
         }

         // Initialize on page load
         document.addEventListener('DOMContentLoaded', () => {
             fetchOrderData();
         });

         // Close modal on escape
         document.addEventListener('keydown', (e) => {
             if (e.key === 'Escape') closeDelayModal();
         });
     </script>
<script>
// RIDER ONLY: send GPS every 5s to update_rider_location.php
(function(){
    const orderId = <?= (int)($order['refill_id'] ?? $_GET['order_id'] ?? 0) ?>;
    const status = "<?= strtolower($order['order_status'] ?? '') ?>";
    const active = ['assigned','pickup_in_progress','picked_up','on_delivery'].includes(status);
    if (!orderId || !active) return;
    if (!('geolocation' in navigator)) return;

    function send(lat, lng, accuracy){
        fetch('../Location/update_rider_location.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, latitude: lat, longitude: lng, accuracy: accuracy || 0 })
        }).catch(e=>console.error(e));
    }

    const opts = { enableHighAccuracy: true, maximumAge: 3000, timeout: 10000 };

    navigator.geolocation.watchPosition(pos => {
        send(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy);
    }, err => console.error(err), opts);

    setInterval(() => {
        navigator.geolocation.getCurrentPosition(pos => {
            send(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy);
        }, ()=>{}, opts);
    }, 5000);
})();
</script>
     
 </body>

 </html>