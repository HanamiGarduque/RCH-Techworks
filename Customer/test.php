<!-- Real-Time Tracking - Always Show Container -->

<div class="bg-white rounded-xl shadow-sm border p-6">
    <div class="flex items-center gap-2 text-blue-600 font-semibold mb-4">
        <i class="fas fa-location-arrow"></i>
        <span>Delivery Tracking</span>
    </div>

```
<!-- Map Container Always Exists -->
<div id="rider-map" class="w-full h-80 rounded-lg mb-4 border border-gray-200 relative">
    <?php if (!($orderStatus === 'On delivery' && $orderData['rider_id'])): ?>
        <div class="absolute inset-0 bg-gray-50 flex flex-col items-center justify-center text-center p-6 z-10 rounded-lg">
            <div class="text-gray-400 mb-3">
                <i class="fas fa-map-marked-alt text-5xl"></i>
            </div>
            <p class="text-gray-600 font-medium">Real-time tracking not available</p>
            <p class="text-gray-500 text-sm mt-1">
                <?php if (!$orderData['rider_id']): ?>
                    Waiting for rider assignment
                <?php else: ?>
                    Tracking will be available when order is out for delivery
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<!-- Rider Info -->
<?php if ($orderData['rider_id']): ?>
    <div class="bg-blue-50 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 mb-1">Rider</p>
                <p class="font-semibold text-gray-800"><?= htmlspecialchars($riderName) ?></p>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-500 mb-1">Contact</p>
                <p class="font-semibold text-gray-800">
                    <i class="fas fa-phone text-blue-600 mr-1"></i><?= htmlspecialchars($riderPhone) ?>
                </p>
            </div>
        </div>
    </div>
<?php endif; ?>
```

</div>

<script>
    const refillId = <?= json_encode($refill_id) ?>;
    const customerLat = <?= json_encode($customerLat) ?>;
    const customerLng = <?= json_encode($customerLng) ?>;
    const hasRider = <?= json_encode($orderData['rider_id'] !== null) ?>;
    const orderStatus = <?= json_encode($orderStatus) ?>;

    let map, riderMarker, customerMarker, routeLine;

    function initMap() {
        const mapElement = document.getElementById('rider-map');
        if (!mapElement) return;

        map = L.map('rider-map', { zoomControl: true }).setView([customerLat, customerLng], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);

        // Customer marker
        const customerIcon = L.divIcon({
            html: '<i class="fas fa-home text-2xl text-red-600"></i>',
            className: 'custom-marker',
            iconSize: [30, 30],
            iconAnchor: [15, 30]
        });
        customerMarker = L.marker([customerLat, customerLng], { icon: customerIcon, title: 'Delivery Destination' }).addTo(map);

        // Only add rider marker if rider is assigned
        if (orderStatus === 'On delivery' && hasRider) {
            const riderIcon = L.divIcon({
                html: '<i class="fas fa-motorcycle text-2xl text-blue-600"></i>',
                className: 'custom-marker',
                iconSize: [30, 30],
                iconAnchor: [15, 30]
            });

            riderMarker = L.marker([customerLat, customerLng], { icon: riderIcon, title: 'Rider Location' }).addTo(map);
            fetchRiderLocation();
            setInterval(fetchRiderLocation, 5000);
        }
    }

    async function fetchRiderLocation() {
        if (!refillId || !hasRider) return;

        try {
            const resp = await fetch(`order_details.php?refill_id=${refillId}&action=rider_location`, { cache: "no-store" });
            const data = await resp.json();

            if (data.latitude && data.longitude) {
                const lat = parseFloat(data.latitude);
                const lng = parseFloat(data.longitude);

                riderMarker.setLatLng([lat, lng]);
                riderMarker.getPopup().setContent(`
                    <div class="p-2">
                        <b class="text-blue-600">Rider Location</b><br>
                        <span class="text-xs text-gray-600">Last updated: ${new Date(data.timestamp).toLocaleTimeString()}</span>
                    </div>
                `);

                // Route line
                if (routeLine) map.removeLayer(routeLine);
                routeLine = L.polyline([[lat, lng], [customerLat, customerLng]], {
                    color: '#3B82F6', weight: 3, opacity: 0.7, dashArray: '10,10'
                }).addTo(map);

                const bounds = L.latLngBounds([[lat, lng], [customerLat, customerLng]]);
                map.fitBounds(bounds, { padding: [50, 50] });
            }
        } catch (err) {
            console.error("Rider location fetch error:", err);
        }
    }

    document.addEventListener('DOMContentLoaded', initMap);
</script>
