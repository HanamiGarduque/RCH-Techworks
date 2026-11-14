<?php
    require_once __DIR__ . '/../session.php';
    redirectIfRoleMismatch('rider');
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.min.js"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; }

        /* SAME LENGTH AS dashboard.php - NEVER GROWS */
        .phone-shell {
            width: 375px;
            height: 750px;
            max-width: calc(100vw - 16px);
            max-height: calc(100vh - 16px);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* SCROLL AREA - THIS IS WHAT SCROLLS INSIDE */
        .scroll-inside {
            flex: 1 1 auto;
            min-height: 0; /* <-- key: lets flex child actually scroll instead of stretching */
            overflow-y: auto;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior: contain;
        }

        /* Custom scrollbar - same as dashboard.php */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        .timeline-item { position: relative; padding-left: 40px; margin-bottom: 24px; }
        .timeline-item::before { content: ''; position: absolute; left: 8px; top: 6px; width: 16px; height: 16px; border-radius: 50%; background-color: #e5e7eb; border: 2px solid white; }
        .timeline-item.active::before { background-color: #2563eb; box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); }
        .timeline-item.completed::before { background-color: #10b981; }
        .timeline-connector { position: absolute; left: 15px; top: 24px; width: 2px; height: calc(100% + 24px); background-color: #e5e7eb; }
        .timeline-item.completed .timeline-connector { background-color: #10b981; }
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 50; align-items: center; justify-content: center; }
        .modal-overlay.active { display: flex; }
        .loading-spinner { display: inline-block; width: 16px; height: 16px; border: 2px solid rgba(255, 255, 255, 0.3); border-top-color: white; border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }

        /* ===== MAP MARKER STYLES - same as dashboard.php ===== */
        .leaflet-routing-container { display: none !important; }
        .rider-pulse { position: relative; width: 36px; height: 36px; }
        .rider-pulse::before { content: ''; position: absolute; inset: 0; border-radius: 50%; background: rgba(16, 185, 129, 0.35); animation: riderPulse 1.8s ease-out infinite; }
        .rider-pulse .dot { position: absolute; inset: 0; border-radius: 50%; background: #10b981; border: 3px solid #fff; box-shadow: 0 2px 8px rgba(0, 0, 0, .35); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 15px; }
        @keyframes riderPulse { 0% { transform: scale(1); opacity: .9; } 100% { transform: scale(2.2); opacity: 0; } }
        .customer-pin { width: 30px; height: 30px; border-radius: 50%; background: #2563eb; border: 3px solid #fff; box-shadow: 0 2px 8px rgba(0, 0, 0, .3); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 13px; }
        .station-pin { background: #f59e0b; border: 3px solid #fff; border-radius: 50%; width: 18px; height: 18px; box-shadow: 0 0 0 6px rgba(245,158,11,.25); }
    </style>
</head>
<body class="bg-white min-h-screen flex justify-center items-center relative">

    <!-- Fixed-width container wrapper - SAME AS dashboard.php: 375 x 750, never grows -->
    <div class="phone-shell bg-white mx-auto relative rounded-3xl overflow-hidden border shadow-lg">

        <!-- Header - SAME FORMAT AS dashboard.php - stays fixed -->
        <div class="bg-gradient-to-br from-blue-400 to-blue-600 text-white sticky top-0 z-50 w-full px-4 py-3 shrink-0">
            <div class="flex items-center justify-between mb-1">
                <button onclick="history.back()" class="text-xl hover:bg-blue-500 rounded-lg transition"><i class="bi bi-chevron-left"></i></button>
                <span id="status-badge" class="status-badge bg-white text-blue-600"><i class="bi bi-hourglass-split"></i><span id="status-text">Loading</span></span>
            </div>
            <div>
                <h1 id="order-id" class="text-lg font-bold tracking-tight">Loading...</h1>
                <div class="flex items-center gap-2 text-blue-100"><i class="bi bi-person-circle text-xl"></i><p id="customer-name" class="text-sm font-medium">Loading...</p></div>
            </div>
        </div>

        <!-- SCROLLABLE CONTENT - only this part scrolls inside the 750px frame -->
        <div class="scroll-inside custom-scrollbar">

            <!-- MAP SECTION - SAME FORMAT AS dashboard.php -->
            <div class="px-5 pt-4 pb-5">
                <h3 class="text-md font-semibold text-blue-600 mb-3 flex items-center gap-2"><i class="bi bi-geo-alt-fill text-blue-600"></i> Live Location</h3>
                <div class="relative mb-4">
                    <div id="map" class="rounded-xl h-40 border border-gray-300 overflow-hidden"></div>
                </div>
            </div>

            <!-- DELIVERY INFO CARD -->
            <div class="px-5 pb-5">
                <div class="bg-blue-50 border-l-4 border-blue-600 rounded-lg p-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div><p class="text-xs text-gray-600 font-semibold mb-1">PICKUP</p><p class="text-xs font-bold text-gray-900" id="pickup-date">--/--/----</p><p class="text-sm font-bold text-blue-600" id="pickup-time">--:--</p></div>
                        <div><p class="text-xs text-gray-600 font-semibold mb-1">DELIVERY</p><p class="text-xs font-bold text-gray-900" id="delivery-date">--/--/----</p><p class="text-sm font-bold text-blue-600" id="delivery-time">--:--</p></div>
                    </div>
                </div>
            </div>

            <!-- ADDRESS SECTION -->
            <div class="px-5 pb-5">
                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-start gap-3">
                        <div class="text-blue-600 text-xl mt-1"><i class="bi bi-house-door"></i></div>
                        <div class="flex-1">
                            <p class="text-xs font-semibold text-gray-500 mb-1">DELIVERY ADDRESS</p>
                            <p id="customer-address" class="text-sm font-medium text-gray-900">Loading...</p>
                            <p id="customer-phone" class="text-xs text-gray-600 mt-2 flex items-center gap-1"><i class="bi bi-telephone"></i> <span id="phone-text">Loading...</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PROGRESS TIMELINE -->
            <div class="px-5 pb-5">
                <h3 class="text-md font-semibold text-blue-600 mb-4 flex items-center gap-2"><i class="bi bi-arrow-repeat text-blue-600"></i> Delivery Progress</h3>
                <div class="relative"><div id="timeline-container"></div></div>
            </div>

            <!-- ITEMS CARD -->
            <div class="px-5 pb-5">
                <h3 class="text-md font-semibold text-blue-600 mb-3 flex items-center gap-2"><i class="bi bi-box2 text-blue-600"></i> Order Items</h3>
                <div id="items-list" class="space-y-4"></div>
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex justify-between items-center"><span class="text-sm font-semibold text-gray-700">Total Amount:</span><span class="text-lg font-bold text-blue-600" id="total-amount">₱0.00</span></div>
                </div>
            </div>
            <div class="pb-5"></div>
        </div>

        <!-- FIXED ACTION BUTTONS - stays fixed at bottom of the 750px frame -->
        <div class="bg-white border-t border-gray-200 px-5 py-4 shrink-0 shadow-2xl"><div id="action-buttons" class="space-y-3"></div></div>
    </div>

    <!-- DELAY NOTE MODAL -->
    <div id="delay-modal" class="modal-overlay">
        <div class="bg-white rounded-2xl p-6 w-11/12 max-w-sm max-h-96 flex flex-col">
            <div class="flex items-center justify-between mb-4"><h3 class="text-xl font-bold text-gray-900">Reason for Delay</h3><button onclick="closeDelayModal()" class="text-gray-400 hover:text-gray-600"><i class="bi bi-x-lg text-xl"></i></button></div>
            <textarea id="delay-reason" placeholder="Please enter the reason for delay..." class="flex-1 w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100 resize-none mb-4"></textarea>
            <div class="flex gap-3">
                <button onclick="closeDelayModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium">Cancel</button>
                <button id="submit-delay-btn" onclick="submitDelayReason()" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium flex items-center justify-center gap-2"><span>Submit</span></button>
            </div>
        </div>
    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const orderNumber = urlParams.get("order_number");
        if (!orderNumber) { alert("Missing order_number in URL"); throw new Error("Missing order_number"); }

        let currentOrder = null;
        let refillId = null;
        let delayAction = null;

        // ---- MAP STATE ----
        let map = null, riderMarker = null, customerMarker = null, routeControl = null;
        let riderPos = null, riderPosValid = false, customerPos = null, customerResolved = false, firstFitDone = false;
        const STATION = { lat: 13.9411, lng: 121.1628 }; // RCH Lipa

        const STATUS_CONFIG = {
            pending: { step: 1, color: "bg-gray-500", text: "Pending", icon: "bi-clock" },
            assigned: { step: 1, color: "bg-yellow-500", text: "Assigned", icon: "bi-person-check" },
            pickup_in_progress: { step: 2, color: "bg-blue-600", text: "Pickup In Progress", icon: "bi-box-seam" },
            pickup_delayed: { step: 2, color: "bg-orange-500", text: "Pickup Delayed", icon: "bi-exclamation-triangle" },
            picked_up: { step: 3, color: "bg-green-500", text: "Picked Up", icon: "bi-check-circle" },
            on_delivery: { step: 4, color: "bg-blue-700", text: "On Delivery", icon: "bi-truck" },
            delivery_delayed: { step: 4, color: "bg-red-500", text: "Delivery Delayed", icon: "bi-exclamation-circle" },
            delivered: { step: 5, color: "bg-green-600", text: "Delivered", icon: "bi-check-lg" },
            cancelled: { step: -1, color: "bg-gray-700", text: "Cancelled", icon: "bi-x-circle" }
        };

        // ---------- MAP - dashboard.php icons ----------
        const riderIcon = L.divIcon({ html: '<div class="rider-pulse"><div class="dot"><i class="bi bi-truck"></i></div></div>', className: '', iconSize: [36, 36], iconAnchor: [18, 18], popupAnchor: [0, -18] });
        const customerIcon = L.divIcon({ html: '<div class="customer-pin"><i class="bi bi-house-fill"></i></div>', className: '', iconSize: [30, 30], iconAnchor: [15, 15], popupAnchor: [0, -15] });

        function initMap() {
            if (map) return;
            map = L.map('map').setView([STATION.lat, STATION.lng], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap contributors', maxZoom: 19 }).addTo(map);
            const stationIcon = L.divIcon({ className: '', html: '<div class="station-pin"></div>', iconSize: [18, 18], iconAnchor: [9, 9] });
            L.marker([STATION.lat, STATION.lng], { icon: stationIcon }).addTo(map).bindPopup('<b>RCH Water Station</b>');
            setTimeout(() => map.invalidateSize(), 400);
        }

        function samePoint(a, b) { if (!a || !b) return false; return Math.abs(a.lat - b.lat) < 0.0001 && Math.abs(a.lng - b.lng) < 0.0001; }

        function distanceKm(a, b) {
            const R = 6371, dLat = (b.lat - a.lat) * Math.PI / 180, dLng = (b.lng - a.lng) * Math.PI / 180;
            const s = Math.sin(dLat/2)**2 + Math.cos(a.lat*Math.PI/180)*Math.cos(b.lat*Math.PI/180)*Math.sin(dLng/2)**2;
            return 2 * R * Math.asin(Math.sqrt(s));
        }

        function isPlausibleRiderPos(pos) {
            if (!pos) return false;
            if (pos.lat < 4.5 || pos.lat > 21.5 || pos.lng < 116.0 || pos.lng > 127.5) return false;
            if (customerPos && distanceKm(pos, customerPos) > 100) return false;
            if (distanceKm(pos, STATION) > 100) return false;
            return true;
        }

        function drawRoute() {
            if (!map || !riderPosValid || !riderPos || !customerPos || samePoint(riderPos, customerPos)) return;
            if (distanceKm(riderPos, customerPos) > 100) return;
            if (routeControl) { map.removeControl(routeControl); routeControl = null; }
            routeControl = L.Routing.control({
                waypoints: [L.latLng(riderPos.lat, riderPos.lng), L.latLng(customerPos.lat, customerPos.lng)],
                lineOptions: { styles: [{ color: '#f59e0b', weight: 5, opacity: 0.9 }] },
                createMarker: function() { return null; },
                addWaypoints: false, routeWhileDragging: false, show: false, fitSelectedRoutes: false
            }).addTo(map);
        }

        function fitBoth() {
            if (!map || firstFitDone) return;
            if (riderPosValid && riderPos && customerPos && distanceKm(riderPos, customerPos) <= 100) {
                map.fitBounds(L.latLngBounds([L.latLng(riderPos.lat, riderPos.lng), L.latLng(customerPos.lat, customerPos.lng)]).pad(0.3));
                firstFitDone = true;
            } else if (customerPos) {
                map.setView([customerPos.lat, customerPos.lng], 15);
                firstFitDone = true;
            }
        }

        function applyCustomerPos(pos, label) {
            if (!pos) return;
            if (samePoint(pos, STATION)) { console.warn('Customer pos == station, ignoring'); return; }
            customerPos = pos; customerResolved = true;
            if (!customerMarker) {
                customerMarker = L.marker([pos.lat, pos.lng], { icon: customerIcon }).addTo(map)
                    .bindPopup(`<div style="min-width:160px;text-align:center;"><b style="color:#2563eb;">📍 Customer</b><br><small>${currentOrder ? currentOrder.customer_name : ''}</small><br><small style="color:#6b7280;">${currentOrder ? currentOrder.customer_address : ''}</small>${label ? `<br><small style="color:#f59e0b;">~ ${label}</small>` : ''}</div>`);
            } else customerMarker.setLatLng([pos.lat, pos.lng]);
            drawRoute(); fitBoth();
        }

        async function geocodeCustomerAddress() {
            if (!currentOrder || !currentOrder.customer_address) return;
            const full = currentOrder.customer_address.trim();
            const attempts = [];
            attempts.push(full + ', Philippines');
            const noHouseNo = full.replace(/^\s*\d+\s*,?\s*/, '');
            if (noHouseNo && noHouseNo !== full) attempts.push(noHouseNo + ', Philippines');
            const parts = full.split(',').map(s => s.trim()).filter(Boolean);
            if (parts.length >= 3) {
                const barangayCity = parts.slice(-3).join(', ');
                if (!attempts.includes(barangayCity + ', Philippines')) attempts.push(barangayCity + ', Philippines');
            }
            if (parts.length >= 2) {
                const cityOnly = parts.slice(-2).join(', ');
                if (!attempts.includes(cityOnly + ', Philippines')) attempts.push(cityOnly + ', Philippines');
            }
            attempts.push('Lipa City, Batangas, Philippines');
            for (const q of attempts) {
                try {
                    const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(q)}`, { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();
                    if (data && data[0]) {
                        const isFallback = q !== attempts[0];
                        applyCustomerPos({ lat: parseFloat(data[0].lat), lng: parseFloat(data[0].lon) }, isFallback ? data[0].display_name : null);
                        return;
                    }
                } catch (e) { console.warn('Geocode failed for', q, e); }
                await new Promise(r => setTimeout(r, 300));
            }
        }

        function applyRiderPos(lat, lng, accuracy) {
            const pos = { lat, lng };
            if (accuracy && accuracy > 2000) { console.warn('GPS accuracy too low:', accuracy); return; }
            if (!isPlausibleRiderPos(pos)) {
                console.warn('Implausible rider GPS (VPN?):', pos);
                riderPosValid = false;
                if (customerPos) map.setView([customerPos.lat, customerPos.lng], 15);
                return;
            }
            riderPos = pos; riderPosValid = true;
            if (!riderMarker) {
                riderMarker = L.marker([lat, lng], { icon: riderIcon, zIndexOffset: 1000 }).addTo(map).bindPopup('<b>You (Rider)</b>');
            } else {
                const from = riderMarker.getLatLng(), to = L.latLng(lat, lng);
                let i = 0; const anim = setInterval(() => { i++; riderMarker.setLatLng([from.lat + (to.lat - from.lat) * i / 20, from.lng + (to.lng - from.lng) * i / 20]); if (i >= 20) clearInterval(anim); }, 40);
            }
            drawRoute(); fitBoth();
        }

        // ---------- RIDER GPS ----------
        function startRiderGPS() {
            if (!('geolocation' in navigator)) { return; }
            const active = ['assigned','pickup_in_progress','picked_up','on_delivery'].includes((currentOrder.order_status || '').toLowerCase());
            const opts = { enableHighAccuracy: true, maximumAge: 3000, timeout: 10000 };

            function send(lat, lng, accuracy) {
                if (!refillId || !active) return;
                if (!isPlausibleRiderPos({ lat, lng })) { console.warn('Not sending implausible GPS to server'); return; }
                fetch('../Location/update_rider_location.php', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order_id: refillId, latitude: lat, longitude: lng, accuracy: accuracy || 0 })
                }).catch(e => console.error(e));
            }

            navigator.geolocation.watchPosition(pos => {
                applyRiderPos(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy);
                send(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy);
            }, err => { console.error(err); }, opts);

            setInterval(() => {
                navigator.geolocation.getCurrentPosition(pos => {
                    applyRiderPos(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy);
                    send(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy);
                }, () => {}, opts);
            }, 5000);
        }

        // ---------- ORDER DATA ----------
        function fetchOrderData() {
            fetch(`order_details_backend.php?order_number=${orderNumber}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success) { alert(data.error || "Order not found"); return; }
                    currentOrder = data.order;
                    refillId = data.order.refill_id;
                    const items = data.items;
                    populateHeader(); populateAddressInfo(); populateTimings(); populateTimeline(); populateItems(items, data.total_subtotal); populateActionButtons();
                    initMap();
                    if (data.order.customer_lat !== null && data.order.customer_lat !== undefined && data.order.customer_lng !== null) {
                        applyCustomerPos({ lat: parseFloat(data.order.customer_lat), lng: parseFloat(data.order.customer_lng) });
                    } else {
                        geocodeCustomerAddress();
                    }
                    startRiderGPS();
                })
                .catch(err => { console.error("Fetch error:", err); alert("Failed to load order details"); });
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
            const pickupDate = pickupDateTime.toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: 'numeric' });
            const deliveryDate = deliveryDateTime.toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: 'numeric' });
            const pickupTime = pickupDateTime.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
            const deliveryTime = deliveryDateTime.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
            document.getElementById("pickup-date").textContent = pickupDate;
            document.getElementById("pickup-time").textContent = pickupTime;
            document.getElementById("delivery-date").textContent = deliveryDate;
            document.getElementById("delivery-time").textContent = deliveryTime;
        }
        function populateItems(items, totalSubtotal) {
            const itemsList = document.getElementById("items-list");
            const totalAmountEl = document.getElementById("total-amount");
            itemsList.innerHTML = '';
            if (items.length === 0) { itemsList.innerHTML = '<p class="text-gray-500 text-sm">No items found</p>'; return; }
            items.forEach(item => {
                const itemName = item.item_name || item.gallon_type;
                const itemSize = item.gallon_size;
                itemsList.innerHTML += `
                    <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow flex items-center justify-between">
                        <div class="flex-1"><p class="text-sm font-semibold text-gray-900">${itemName}</p><p class="text-xs text-gray-600">${itemSize} • Qty: ${item.quantity}</p></div>
                        <div class="text-right"><p class="text-xs text-gray-600">₱${parseFloat(item.unit_price).toFixed(2)} each</p><p class="text-sm font-bold text-blue-600">₱${parseFloat(item.subtotal).toFixed(2)}</p></div>
                    </div>`;
            });
            totalAmountEl.textContent = `₱${parseFloat(totalSubtotal || currentOrder.total_amount).toFixed(2)}`;
        }
        function populateActionButtons() {
            const buttonsEl = document.getElementById("action-buttons");
            const status = currentOrder.order_status;
            let html = '';
            if (status === 'pending' || status === 'assigned') {
                html = `<button onclick="startPickup()" class="w-full py-3 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition flex items-center justify-center gap-2 shadow-lg"><i class="bi bi-box-seam"></i> Start Pickup</button>`;
            } else if (status === 'pickup_in_progress') {
                html = `<button onclick="markPickedUp()" class="w-full py-3 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 transition flex items-center justify-center gap-2 shadow-lg mb-2"><i class="bi bi-check-circle"></i> Mark as Picked Up</button><button onclick="openDelayModal('pickup')" class="w-full py-3 bg-orange-500 text-white rounded-xl font-bold hover:bg-orange-600 transition flex items-center justify-center gap-2 shadow-lg"><i class="bi bi-exclamation-triangle"></i> Delay</button>`;
            } else if (status === 'pickup_delayed') {
                html = `<button onclick="requestReschedule('pickup')" class="w-full py-3 bg-purple-600 text-white rounded-xl font-bold hover:bg-purple-700 transition flex items-center justify-center gap-2 shadow-lg"><i class="bi bi-calendar-event"></i> Request Reschedule from Admin</button>`;
            } else if (status === 'picked_up') {
                const canStartDelivery = canShowStartDeliveryButton();
                html = `<button onclick="startDelivery()" ${!canStartDelivery ? 'disabled' : ''} class="w-full py-3 ${canStartDelivery ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed'} text-white rounded-xl font-bold transition flex items-center justify-center gap-2 shadow-lg"><i class="bi bi-truck"></i> Start Delivery</button>${!canStartDelivery ? '<p class="text-xs text-gray-600 text-center mt-2">Available 30 minutes before delivery time</p>' : ''}`;
            } else if (status === 'on_delivery') {
                html = `<button onclick="goToQRScanner()" class="w-full py-3 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 transition flex items-center justify-center gap-2 shadow-lg mb-2"><i class="bi bi-check-lg"></i> Complete Delivery</button><button onclick="openDelayModal('delivery')" class="w-full py-3 bg-red-500 text-white rounded-xl font-bold hover:bg-red-600 transition flex items-center justify-center gap-2 shadow-lg"><i class="bi bi-exclamation-circle"></i> Delay</button>`;
            } else if (status === 'delivery_delayed') {
                html = `<button onclick="requestReschedule('delivery')" class="w-full py-3 bg-purple-600 text-white rounded-xl font-bold hover:bg-purple-700 transition flex items-center justify-center gap-2 shadow-lg"><i class="bi bi-calendar-event"></i> Request Reschedule from Admin</button>`;
            } else if (status === 'delivered' || status === 'cancelled') {
                html = `<button disabled class="w-full py-3 bg-gray-400 text-white rounded-xl font-bold cursor-not-allowed flex items-center justify-center gap-2 shadow-lg"><i class="bi bi-check-lg"></i> ${status === 'delivered' ? 'Order Completed' : 'Order Cancelled'}</button>`;
            }
            buttonsEl.innerHTML = html;
        }
        function requestReschedule(type = 'pickup') {
            const actionType = type === 'delivery' ? 'delivery' : 'pickup';
            const actionText = type === 'delivery' ? 'delivery delay' : 'pickup delay';
            if (!confirm(`Send reschedule request to admin? This will notify the admin about the ${actionText}.`)) return;
            const btn = event.target.closest('button');
            if (btn) { btn.disabled = true; btn.innerHTML = '<div class="loading-spinner"></div> Sending...'; }
            const formData = new FormData();
            formData.append('order_number', currentOrder.order_number);
            formData.append('refill_id', currentOrder.refill_id);
            formData.append('delay_reason', currentOrder.delay_reason || `${actionType.charAt(0).toUpperCase() + actionType.slice(1)} delayed - reschedule requested`);
            formData.append('delay_type', actionType);
            fetch('request_reschedule.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => { if (data.success) alert('Reschedule request sent to admin successfully!'); else alert(data.error || 'Failed to send reschedule request'); populateActionButtons(); })
                .catch(err => { console.error("Reschedule request error:", err); alert('Error sending reschedule request'); populateActionButtons(); });
        }
        function goToQRScanner() {
            const lat = customerPos ? customerPos.lat : (currentOrder.customer_lat ?? '');
            const lng = customerPos ? customerPos.lng : (currentOrder.customer_lng ?? '');
            window.location.href = `scan_qr.php?refill_id=${refillId}&customer=${encodeURIComponent(currentOrder.customer_name)}&lat=${lat}&lng=${lng}`;
        }
        function populateTimeline() {
            const currentStatus = currentOrder.order_status;
            const steps = [
                { step: 1, label: "Order Placed", statuses: ["pending", "assigned"] },
                { step: 2, label: currentStatus === "pickup_delayed" ? "Pickup Delayed" : "Pickup In Progress", statuses: ["pickup_in_progress", "pickup_delayed"] },
                { step: 3, label: "Picked Up", statuses: ["picked_up"] },
                { step: 4, label: currentStatus === "delivery_delayed" ? "Delivery Delayed" : "On Delivery", statuses: ["on_delivery", "delivery_delayed"] },
                { step: 5, label: "Delivered", statuses: ["delivered"] }
            ];
            const currentStep = STATUS_CONFIG[currentStatus].step;
            let html = '';
            steps.forEach((item, index) => {
                const isActive = item.step === currentStep;
                const isCompleted = item.step < currentStep;
                const hasConnector = index < steps.length - 1;
                html += `<div class="timeline-item ${isActive ? 'active' : ''} ${isCompleted ? 'completed' : ''}">${hasConnector ? '<div class="timeline-connector"></div>' : ''}<div class="flex flex-col"><p class="text-sm font-semibold ${isActive ? 'text-blue-600' : isCompleted ? 'text-green-600' : 'text-gray-900'}">${item.label}</p><p class="text-xs text-gray-600 mt-1">Step ${item.step} of 5</p></div></div>`;
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
            if (!refillId) { alert('Error: Refill ID not found'); return; }
            const submitBtn = event?.target?.closest('button');
            if (submitBtn) { submitBtn.disabled = true; submitBtn.innerHTML = '<div class="loading-spinner"></div> Processing...'; }
            const formData = new FormData();
            formData.append('refill_id', refillId);
            formData.append('new_status', newStatus);
            if (delayNote) formData.append('delay_note', delayNote);
            fetch('order_status_update.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) { currentOrder.order_status = newStatus; populateHeader(); populateTimeline(); populateActionButtons(); alert('Order status updated successfully!'); }
                    else { alert(data.error || 'Failed to update order status'); populateActionButtons(); }
                })
                .catch(err => { console.error("Update error:", err); alert('Error updating order status'); populateActionButtons(); });
        }
        function startPickup() { if (confirm('Start pickup for this order?')) updateOrderStatus('pickup_in_progress'); }
        function markPickedUp() { if (confirm('Mark order as picked up?')) updateOrderStatus('picked_up'); }
        function startDelivery() {
            if (!canShowStartDeliveryButton()) { alert('Cannot start delivery yet. Available 30 minutes before delivery time.'); return; }
            if (confirm('Start delivery for this order?')) updateOrderStatus('on_delivery');
        }
        function completeDelivery() { if (confirm('Mark order as delivered?')) updateOrderStatus('delivered'); }
        function openDelayModal(action) { delayAction = action; document.getElementById("delay-modal").classList.add("active"); document.getElementById("delay-reason").value = ''; }
        function closeDelayModal() { document.getElementById("delay-modal").classList.remove("active"); delayAction = null; }
        function submitDelayReason() {
            const reason = document.getElementById("delay-reason").value.trim();
            if (!reason) { alert('Please enter a reason for delay'); return; }
            const newStatus = delayAction === 'pickup' ? 'pickup_delayed' : 'delivery_delayed';
            closeDelayModal(); updateOrderStatus(newStatus, reason);
        }
        document.addEventListener('DOMContentLoaded', () => { fetchOrderData(); });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeDelayModal(); });
    </script>
</body>
</html>