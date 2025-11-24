<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rider Dashboard | RCH Water</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: #f5f5f5;
        }

        /* Updated header styling to match design - brighter blue gradient */
        .header {
            background: linear-gradient(135deg, #2B6FD9 0%, #1E5FBD 100%);
            color: white;
            padding: 1.25rem;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
        }

        .header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .header p {
            font-size: 0.875rem;
            opacity: 0.95;
            font-weight: 500;
        }

        /* Updated tabs styling - pill-shaped buttons with light blue inactive state */
        .tabs {
            background-color: white;
            position: sticky;
            top: 70px;
            z-index: 40;
            padding: 1rem;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .tabs button {
            background-color: #E3F2FD;
            color: #1E5FBD;
            font-weight: 600;
            padding: 0.625rem 1.25rem;
            border: none;
            border-radius: 2rem;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .tabs button.active {
            background-color: #2B6FD9;
            color: white;
        }

        .tabs button:hover:not(.active) {
            background-color: #BBDEFB;
        }

        .section-content {
            padding: 1.5rem;
            padding-bottom: 2rem;
            background-color: #f5f5f5;
            min-height: calc(100vh - 150px);
        }

        /* Updated heading styling to match design - blue color */
        .section-content h3 {
            font-size: 1.125rem;
            font-weight: 700;
            color: #1E5FBD;
            margin-bottom: 1.25rem;
        }

        .hidden {
            display: none !important;
        }

        /* Updated map container styling with dashed border like design */
        #active-map {
            background-color: #E8E8E8;
            border-radius: 0.75rem;
            height: 300px;
            margin-bottom: 1.5rem;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 2px dashed #BDBDBD;
            z-index: 10;
            overflow: hidden;
        }

        .delivery-list {
            background-color: white;
            padding: 0.75rem;
            border-radius: 0.75rem;
            height: 400px;
            overflow-y: auto;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .delivery-card {
            background-color: white;
            border: 1px solid #EEEEEE;
            border-radius: 0.75rem;
            padding: 1.25rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            margin-bottom: 1rem;
            transition: box-shadow 0.2s ease;
        }

        .delivery-card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
        }

        .delivery-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .delivery-name {
            font-weight: 700;
            color: #212121;
            font-size: 1rem;
        }

        .delivery-order {
            font-size: 0.75rem;
            color: #757575;
            margin-top: 0.25rem;
        }

        .delivery-status {
            font-size: 0.75rem;
            background-color: #DCFCE7;
            color: #166534;
            padding: 0.5rem 0.75rem;
            border-radius: 9999px;
            font-weight: 600;
            white-space: nowrap;
        }

        .delivery-details {
            font-size: 0.875rem;
            color: #616161;
            margin-bottom: 1rem;
        }

        .delivery-detail-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 0.625rem;
        }

        .address-icon {
            color: #2B6FD9;
            font-size: 0.95rem;
            margin-top: 0.125rem;
        }

        .time-icon {
            color: #F57C00;
            font-size: 0.95rem;
            margin-top: 0.125rem;
        }

        .delivery-btn {
            width: 100%;
            padding: 0.75rem;
            font-size: 0.875rem;
            font-weight: 600;
            border: none;
            border-radius: 0.5rem;
            background-color: #E3F2FD;
            color: #1E5FBD;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .delivery-btn:hover {
            background-color: #BBDEFB;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f5f5f5;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #BDBDBD;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #9E9E9E;
        }

        .no-deliveries {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            padding: 2rem;
        }

        .no-deliveries p {
            text-align: center;
            color: #9E9E9E;
            font-size: 0.875rem;
        }

        .pending-tabs {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        /* Updated pending tab styling - pill-shaped buttons with better spacing */
        .pending-tab {
            padding: 0.625rem 1.5rem;
            background-color: #E3F2FD;
            color: #1E5FBD;
            border: none;
            border-radius: 2rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .pending-tab.active {
            background-color: #2B6FD9;
            color: white;
        }

        .pending-tab:hover:not(.active) {
            background-color: #BBDEFB;
        }

        .pending-content {
            display: none;
        }

        .pending-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Updated header with dynamic date/time display -->
    <div class="header">
        <h1>Welcome, Rider</h1>
        <p id="header-date-time"></p>
    </div>

    <!-- Navigation Tabs -->
    <div class="tabs">
        <button class="section-btn active" onclick="showSection('active-deliveries', this)">Active</button>
        <button class="section-btn" onclick="showSection('pending-deliveries', this)">Pending</button>
        <button class="section-btn" onclick="showSection('completed-deliveries', this)">Completed</button>
    </div>

    <!-- ACTIVE DELIVERIES SECTION -->
    <div id="active-deliveries" class="section-content">
        <h3>Active Deliveries</h3>
        
        <!-- Map View Card - displays all active delivery customer locations as pins -->
        <div id="active-map"></div>

        <div class="delivery-list custom-scrollbar" id="active-list"></div>
    </div>

    <!-- PENDING DELIVERIES SECTION -->
    <div id="pending-deliveries" class="section-content hidden">
        <h3>Pending Deliveries</h3>

        <!-- Pickup Pending Tabs -->
        <div class="pending-tabs" id="pickup-tabs"></div>
        <div id="pickup-content" class="pending-content active"></div>

        <!-- Delivery Pending Tabs -->
        <div class="pending-tabs" id="delivery-tabs"></div>
        <div id="delivery-content" class="pending-content active"></div>
    </div>

    <!-- COMPLETED DELIVERIES SECTION -->
    <div id="completed-deliveries" class="section-content hidden">
        <h3>Order History</h3>

        <!-- Delayed Tabs -->
        <div class="pending-tabs" id="delayed-tabs"></div>
        <div id="delayed-content" class="pending-content active"></div>

        <!-- Completed Tabs -->
        <div class="pending-tabs" id="completed-tabs"></div>
        <div id="completed-content" class="pending-content active"></div>
    </div>

    <!-- Added Leaflet JS library for map functionality -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <script>
        let deliveryData = {};
        let map = null;
        let markers = [];

        function updateDateTime() {
            const now = new Date();
            const options = { month: 'long', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' };
            const dateTimeString = now.toLocaleDateString('en-US', options);
            document.getElementById('header-date-time').textContent = dateTimeString;
        }

        function showSection(sectionId, button) {
            const sections = ['active-deliveries', 'pending-deliveries', 'completed-deliveries'];
            sections.forEach(id => {
                document.getElementById(id).classList.add('hidden');
            });
            document.getElementById(sectionId).classList.remove('hidden');

            document.querySelectorAll('.section-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            button.classList.add('active');

            if (sectionId === 'active-deliveries') {
                setTimeout(() => {
                    if (!map) {
                        initializeMap();
                    } else {
                        map.invalidateSize();
                        updateMapMarkers();
                    }
                }, 100);
            }
        }

        function initializeMap() {
            const mapContainer = document.getElementById('active-map');
            if (!mapContainer) return;

            const defaultLat = 14.5994;
            const defaultLng = 120.9842;

            if (!map) {
                map = L.map('active-map').setView([defaultLat, defaultLng], 13);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                    maxZoom: 19
                }).addTo(map);
            }

            updateMapMarkers();
        }

        function updateMapMarkers() {
            markers.forEach(marker => map.removeLayer(marker));
            markers = [];

            const today = new Date().toISOString().split('T')[0];
            const activeOrders = (deliveryData.active || []).filter(order =>
                order.pickup_datetime.startsWith(today) || order.delivery_datetime.startsWith(today)
            );

            if (activeOrders.length === 0) {
                map.setView([14.5994, 120.9842], 13);
                return;
            }

            activeOrders.forEach((order) => {
                if (order.user.latitude && order.user.longitude) {
                    const marker = L.marker([order.user.latitude, order.user.longitude], {
                        title: order.user.name
                    }).addTo(map);

                    const popupContent = `
                        <div style="font-size: 12px; width: 200px;">
                            <strong>${order.user.name}</strong><br>
                            Order #${order.order_number}<br>
                            <strong>Address:</strong> ${order.user.address}<br>
                            <strong>Time:</strong> ${new Date(order.pickup_datetime || order.delivery_datetime).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                        </div>
                    `;
                    marker.bindPopup(popupContent);
                    markers.push(marker);
                }
            });

            if (markers.length > 0) {
                const group = new L.featureGroup(markers);
                map.fitBounds(group.getBounds().pad(0.1));
            }
        }

        function fetchDeliveries() {
            fetch('dashboard_backend.php')
                .then(res => res.json())
                .then(data => {
                    deliveryData = data;
                    const activeList = document.getElementById('active-list');
                    const today = new Date().toISOString().split('T')[0];

                    const activeOrders = data.active.filter(order =>
                        order.pickup_datetime.startsWith(today) || order.delivery_datetime.startsWith(today)
                    );

                    if (!activeOrders.length) {
                        activeList.innerHTML = `
                            <div class="no-deliveries">
                                <p>No active deliveries for today</p>
                            </div>
                        `;
                    } else {
                        activeList.innerHTML = activeOrders.map(order => `
                            <div class="delivery-card">
                                <div class="delivery-header">
                                    <div>
                                        <div class="delivery-name">${order.user.name}</div>
                                        <div class="delivery-order">Order #${order.order_number}</div>
                                    </div>
                                    <span class="delivery-status">
                                        <i class="bi bi-truck"></i> On the Way
                                    </span>
                                </div>

                                <div class="delivery-details">
                                    <div class="delivery-detail-item">
                                        <i class="bi bi-geo-alt-fill address-icon"></i>
                                        <span>${order.user.address}</span>
                                    </div>
                                    <div class="delivery-detail-item">
                                        <i class="bi bi-clock-fill time-icon"></i>
                                        <span><strong>${new Date(order.pickup_datetime || order.delivery_datetime).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</strong></span>
                                    </div>
                                </div>

                                <button onclick="window.location.href='order_details.php?refill_id=${order.refill_id}'" class="delivery-btn">
                                    View Details
                                </button>
                            </div>
                        `).join('');
                    }

                    if (document.getElementById('active-deliveries').classList.contains('hidden') === false) {
                        setTimeout(() => {
                            if (!map) {
                                initializeMap();
                            } else {
                                updateMapMarkers();
                            }
                        }, 100);
                    }

                    renderPickupList();
                    renderDeliveryList();
                    renderDelayedList();
                    renderCompletedList();
                })
                .catch(console.error);
        }

        function renderPickupList() {
            const pickupTabs = document.getElementById('pickup-tabs');
            const pickupContent = document.getElementById('pickup-content');
            
            const pickupData = deliveryData.pending?.pickup || {};
            const periods = Object.keys(pickupData);

            pickupTabs.innerHTML = periods.map(period => `
                <button class="pending-tab ${period === 'morning' ? 'active' : ''}" onclick="switchPendingTab(this, '${period}', 'pickup')">
                    ${period.charAt(0).toUpperCase() + period.slice(1)}
                </button>
            `).join('');

            const firstPeriod = periods[0] || 'morning';
            const ordersToShow = pickupData[firstPeriod] || [];

            pickupContent.innerHTML = ordersToShow.length ? ordersToShow.map(order => `
                <div class="delivery-card">
                    <div class="delivery-header">
                        <div>
                            <div class="delivery-name">${order.user.name}</div>
                            <div class="delivery-order">Order #${order.order_number}</div>
                        </div>
                        <span class="delivery-status" style="background-color: #fef3c7; color: #92400e;">
                            <i class="bi bi-hourglass-split"></i> Pickup
                        </span>
                    </div>

                    <div class="delivery-details">
                        <div class="delivery-detail-item">
                            <i class="bi bi-geo-alt-fill address-icon"></i>
                            <span>${order.user.address}</span>
                        </div>
                        <div class="delivery-detail-item">
                            <i class="bi bi-clock-fill time-icon"></i>
                            <span><strong>${new Date(order.pickup_datetime).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</strong></span>
                        </div>
                    </div>

                    <button onclick="window.location.href='order_details.php?refill_id=${order.refill_id}'" class="delivery-btn">
                        View Details
                    </button>
                </div>
            `).join('') : '<div class="no-deliveries"><p>No pickups scheduled</p></div>';
        }

        function renderDeliveryList() {
            const deliveryTabs = document.getElementById('delivery-tabs');
            const deliveryContent = document.getElementById('delivery-content');
            
            const deliveryData_ = deliveryData.pending?.delivery || {};
            const periods = Object.keys(deliveryData_);

            deliveryTabs.innerHTML = periods.map(period => `
                <button class="pending-tab ${period === 'morning' ? 'active' : ''}" onclick="switchPendingTab(this, '${period}', 'delivery')">
                    ${period.charAt(0).toUpperCase() + period.slice(1)}
                </button>
            `).join('');

            const firstPeriod = periods[0] || 'morning';
            const ordersToShow = deliveryData_[firstPeriod] || [];

            deliveryContent.innerHTML = ordersToShow.length ? ordersToShow.map(order => `
                <div class="delivery-card">
                    <div class="delivery-header">
                        <div>
                            <div class="delivery-name">${order.user.name}</div>
                            <div class="delivery-order">Order #${order.order_number}</div>
                        </div>
                        <span class="delivery-status" style="background-color: #dbeafe; color: #1e40af;">
                            <i class="bi bi-hourglass-split"></i> Ready
                        </span>
                    </div>

                    <div class="delivery-details">
                        <div class="delivery-detail-item">
                            <i class="bi bi-geo-alt-fill address-icon"></i>
                            <span>${order.user.address}</span>
                        </div>
                        <div class="delivery-detail-item">
                            <i class="bi bi-clock-fill time-icon"></i>
                            <span><strong>${new Date(order.delivery_datetime).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</strong></span>
                        </div>
                    </div>

                    <button onclick="window.location.href='order_details.php?refill_id=${order.refill_id}'" class="delivery-btn">
                        View Details
                    </button>
                </div>
            `).join('') : '<div class="no-deliveries"><p>No deliveries ready</p></div>';
        }

        function renderDelayedList() {
            const delayedTabs = document.getElementById('delayed-tabs');
            const delayedContent = document.getElementById('delayed-content');
            
            const delayedData = deliveryData.history?.delayed || {};
            const periods = Object.keys(delayedData);

            delayedTabs.innerHTML = periods.map(period => `
                <button class="pending-tab ${period === 'morning' ? 'active' : ''}" onclick="switchPendingTab(this, '${period}', 'delayed')">
                    ${period.charAt(0).toUpperCase() + period.slice(1)}
                </button>
            `).join('');

            const firstPeriod = periods[0] || 'morning';
            const ordersToShow = delayedData[firstPeriod] || [];

            delayedContent.innerHTML = ordersToShow.length ? ordersToShow.map(order => `
                <div class="delivery-card">
                    <div class="delivery-header">
                        <div>
                            <div class="delivery-name">${order.user.name}</div>
                            <div class="delivery-order">Order #${order.order_number}</div>
                        </div>
                        <span class="delivery-status" style="background-color: #fee2e2; color: #991b1b;">
                            <i class="bi bi-exclamation-triangle"></i> Delayed
                        </span>
                    </div>

                    <div class="delivery-details">
                        <div class="delivery-detail-item">
                            <i class="bi bi-geo-alt-fill address-icon"></i>
                            <span>${order.user.address}</span>
                        </div>
                        <div class="delivery-detail-item">
                            <i class="bi bi-clock-fill time-icon"></i>
                            <span><strong>${new Date(order.delivery_datetime).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</strong></span>
                        </div>
                    </div>

                    <button onclick="window.location.href='order_details.php?refill_id=${order.refill_id}'" class="delivery-btn">
                        View Details
                    </button>
                </div>
            `).join('') : '<div class="no-deliveries"><p>No delayed deliveries</p></div>';
        }

        function renderCompletedList() {
            const completedTabs = document.getElementById('completed-tabs');
            const completedContent = document.getElementById('completed-content');
            
            const completedData = deliveryData.history?.completed || {};
            const periods = Object.keys(completedData);

            completedTabs.innerHTML = periods.map(period => `
                <button class="pending-tab ${period === 'morning' ? 'active' : ''}" onclick="switchPendingTab(this, '${period}', 'completed')">
                    ${period.charAt(0).toUpperCase() + period.slice(1)}
                </button>
            `).join('');

            const firstPeriod = periods[0] || 'morning';
            const ordersToShow = completedData[firstPeriod] || [];

            completedContent.innerHTML = ordersToShow.length ? ordersToShow.map(order => `
                <div class="delivery-card">
                    <div class="delivery-header">
                        <div>
                            <div class="delivery-name">${order.user.name}</div>
                            <div class="delivery-order">Order #${order.order_number}</div>
                        </div>
                        <span class="delivery-status" style="background-color: #dcfce7; color: #166534;">
                            <i class="bi bi-check-circle"></i> Completed
                        </span>
                    </div>

                    <div class="delivery-details">
                        <div class="delivery-detail-item">
                            <i class="bi bi-geo-alt-fill address-icon"></i>
                            <span>${order.user.address}</span>
                        </div>
                        <div class="delivery-detail-item">
                            <i class="bi bi-clock-fill time-icon"></i>
                            <span><strong>${new Date(order.delivery_datetime).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</strong></span>
                        </div>
                    </div>

                    <button onclick="window.location.href='order_details.php?refill_id=${order.refill_id}'" class="delivery-btn">
                        View Details
                    </button>
                </div>
            `).join('') : '<div class="no-deliveries"><p>No completed deliveries</p></div>';
        }

        function switchPendingTab(button, period, type) {
            const parentId = type === 'pickup' ? 'pickup-tabs' : type === 'delivery' ? 'delivery-tabs' : type === 'delayed' ? 'delayed-tabs' : 'completed-tabs';
            const contentId = type === 'pickup' ? 'pickup-content' : type === 'delivery' ? 'delivery-content' : type === 'delayed' ? 'delayed-content' : 'completed-content';
            
            document.querySelectorAll(`#${parentId} .pending-tab`).forEach(btn => {
                btn.classList.remove('active');
            });
            button.classList.add('active');

            const dataType = type === 'pickup' ? deliveryData.pending.pickup : type === 'delivery' ? deliveryData.pending.delivery : type === 'delayed' ? deliveryData.history.delayed : deliveryData.history.completed;
            const orders = dataType[period] || [];

            const contentElement = document.getElementById(contentId);
            contentElement.innerHTML = orders.length ? orders.map(order => `
                <div class="delivery-card">
                    <div class="delivery-header">
                        <div>
                            <div class="delivery-name">${order.user.name}</div>
                            <div class="delivery-order">Order #${order.order_number}</div>
                        </div>
                        <span class="delivery-status" style="background-color: ${type === 'completed' ? '#dcfce7' : type === 'delayed' ? '#fee2e2' : '#fef3c7'}; color: ${type === 'completed' ? '#166534' : type === 'delayed' ? '#991b1b' : '#92400e'};">
                            <i class="bi bi-${type === 'completed' ? 'check-circle' : type === 'delayed' ? 'exclamation-triangle' : 'hourglass-split'}"></i> ${type === 'completed' ? 'Completed' : type === 'delayed' ? 'Delayed' : 'Pickup'}
                        </span>
                    </div>

                    <div class="delivery-details">
                        <div class="delivery-detail-item">
                            <i class="bi bi-geo-alt-fill address-icon"></i>
                            <span>${order.user.address}</span>
                        </div>
                        <div class="delivery-detail-item">
                            <i class="bi bi-clock-fill time-icon"></i>
                            <span><strong>${new Date(type === 'pickup' ? order.pickup_datetime : order.delivery_datetime).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</strong></span>
                        </div>
                    </div>

                    <button onclick="window.location.href='order_details.php?refill_id=${order.refill_id}'" class="delivery-btn">
                        View Details
                    </button>
                </div>
            `).join('') : `<div class="no-deliveries"><p>No deliveries for this period</p></div>`;
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            updateDateTime();
            fetchDeliveries();
            setInterval(fetchDeliveries, 30000);
        });
    </script>
</body>
</html>
