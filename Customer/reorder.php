<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>RCH Water - Order Water Refill</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        #mapContainer {
            z-index: 1;
        }
    </style>
</head>

<body class="bg-gray-100 overflow-hidden">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <script src="includes/sidebar.js"></script>

        <!-- Main Content -->
        <main class="flex-1 ml-20 bg-gray-50 rounded-xl shadow overflow-y-auto">
            <div class="main-header">
                <?php
                $pageTitle = "Order Water Refill";
                $pageSubtitle = "Hydrate made easy—order and we'll handle the rest!";
                include 'includes/header.php';
                ?>
            </div>

            <!-- YOUR INSERTED CONTENT -->
            <div class="p-6">

                <!-- CHECKOUT VIEW -->
                <div id="checkoutView"
                    class="p-6 bg-white rounded-lg shadow-lg ring-3 ring-gray-200 flex flex-col flex-1 overflow-auto">
                    <h3 class="text-2xl font-semibold mb-4">Checkout</h3>

                    <!-- Delivery address block -->
                    <div class="p-4 border rounded-lg mb-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="text-blue-600 font-semibold mb-1">Delivery Address</h4>
                                <div id="deliveryDisplay">
                                    <p id="deliverName" class="font-semibold">User Name</p>
                                    <p id="deliverPhone" class="text-sm text-gray-600">Phone</p>
                                    <p id="deliverAddress" class="text-sm text-gray-600">Address</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <button id="changeAddressBtn" class="text-sm text-blue-600 hover:underline">Change</button>
                            </div>
                        </div>

                        <div id="deliveryEdit" class="hidden mt-4 flex gap-3">
                            <!-- Form section -->
                            <div class="flex-1 flex flex-col space-y-2">
                                <div id="addressForm" class="flex-1">
                                    <div class="grid grid-cols-1 gap-2">
                                        <input id="addrHouse" placeholder="House/Unit No."
                                            class="border rounded px-2 py-2" />
                                        <input id="addrStreet" placeholder="Street Name"
                                            class="border rounded px-2 py-2" />
                                        <input id="addrBarangay" placeholder="Barangay"
                                            class="border rounded px-2 py-2" />
                                        <input id="addrCity" placeholder="City" value="Lipa City"
                                            class="border rounded px-2 py-2" />
                                        <input id="addrProvince" placeholder="Province" value="Batangas"
                                            class="border rounded px-2 py-2" />
                                        <input id="addrPostal" placeholder="Postal Code"
                                            class="border rounded px-2 py-2" />
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2">We only deliver in Lipa City, Batangas.</p>
                                    <div class="flex space-x-3 mt-3">
                                        <button id="saveAddressBtn"
                                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save</button>
                                        <button id="cancelAddressBtn"
                                            class="bg-white border px-4 py-2 rounded hover:bg-blue-600 hover:text-white">Cancel</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Map section -->
                            <div id="mapContainer" class="flex-1 border rounded-lg bg-blue-100"
                                style="height: 300px;">
                            </div>
                        </div>

                    </div>

                    <!-- Products ordered -->
                    <div class="p-4 border rounded-lg mb-4">
                        <h4 class="text-blue-600 font-semibold mb-3">Products Ordered</h4>
                        <div id="checkoutProducts" class="text-sm"></div>
                    </div>

                    <!-- costs -->
                    <div class="p-4 border rounded-lg mb-4">
                        <div class="flex justify-between mb-2">
                            <div>Subtotal</div>
                            <div id="coSubtotal">₱0.00</div>
                        </div>
                        <div class="flex justify-between mb-2">
                            <div>Tax (8%)</div>
                            <div id="coTax">₱0.00</div>
                        </div>
                        <div class="flex justify-between mb-2">
                            <div>Delivery Fee</div>
                            <div id="coDelivery">₱40.00</div>
                        </div>
                        <hr class="my-3" />
                        <div class="flex justify-between text-lg font-bold">
                            <div>Total Cost</div>
                            <div id="coTotal">₱0.00</div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button id="cancelOrderBtn" class="px-4 py-2 rounded-lg border hover:bg-gray-50">Cancel</button>
                        <button id="placeOrderBtn" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Place Order</button>
                    </div>
                </div>

            </div>
            <!-- END INSERTED CONTENT -->

        </main>
    </div>

<script>
    // Lipa City only - map part
    const LIPA_CENTER = [13.9410, 121.1640];
    const LIPA_BOUNDS = L.latLngBounds(
        L.latLng(13.8500, 121.0500),
        L.latLng(14.0300, 121.3000)
    );
    let reorderMap = null;
    let reorderMarker = null;
    let lastValidLatLng = L.latLng(LIPA_CENTER[0], LIPA_CENTER[1]);

    function isInLipa(lat, lng) {
        return LIPA_BOUNDS.contains(L.latLng(lat, lng));
    }

    function showLipaMessage() {
        if (window.Swal) {
            Swal.fire('Outside delivery area', 'We only deliver in Lipa City.', 'warning');
        } else {
            alert('We only deliver in Lipa City.');
        }
    }

    function isLipaAddress() {
        const city = (document.getElementById('addrCity') ? document.getElementById('addrCity').value : '').trim().toLowerCase();
        const province = (document.getElementById('addrProvince') ? document.getElementById('addrProvince').value : '').trim().toLowerCase();
        const cityOk = (city === 'lipa city' || city === 'lipa');
        const provinceOk = (province === '' || province === 'batangas');
        return cityOk && provinceOk;
    }

    function initReorderMap() {
        if (reorderMap) {
            setTimeout(function() { reorderMap.invalidateSize(); }, 100);
            return;
        }
        reorderMap = L.map('mapContainer', {
            center: LIPA_CENTER,
            zoom: 13,
            maxBounds: LIPA_BOUNDS,
            maxBoundsViscosity: 1.0,
            minZoom: 12,
            maxZoom: 18
        });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(reorderMap);

        reorderMarker = L.marker(LIPA_CENTER, { draggable: true }).addTo(reorderMap);

        reorderMarker.on('dragend', function() {
            const pos = reorderMarker.getLatLng();
            if (!isInLipa(pos.lat, pos.lng)) {
                reorderMarker.setLatLng(lastValidLatLng);
                showLipaMessage();
            } else {
                lastValidLatLng = pos;
            }
        });

        reorderMap.on('click', function(e) {
            if (!isInLipa(e.latlng.lat, e.latlng.lng)) {
                showLipaMessage();
                return;
            }
            lastValidLatLng = e.latlng;
            reorderMarker.setLatLng(e.latlng);
        });

        setTimeout(function() { reorderMap.invalidateSize(); }, 100);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const changeBtn = document.getElementById('changeAddressBtn');
        const editBox = document.getElementById('deliveryEdit');
        const saveBtn = document.getElementById('saveAddressBtn');
        const cancelBtn = document.getElementById('cancelAddressBtn');
        const placeBtn = document.getElementById('placeOrderBtn');

        if (changeBtn && editBox) {
            changeBtn.addEventListener('click', function() {
                editBox.classList.remove('hidden');
                initReorderMap();
            });
        }

        if (cancelBtn && editBox) {
            cancelBtn.addEventListener('click', function() {
                editBox.classList.add('hidden');
            });
        }

        if (saveBtn) {
            saveBtn.addEventListener('click', function() {
                if (!isLipaAddress()) {
                    showLipaMessage();
                    return;
                }
                if (reorderMarker) {
                    const pos = reorderMarker.getLatLng();
                    if (!isInLipa(pos.lat, pos.lng)) {
                        showLipaMessage();
                        return;
                    }
                }
                if (editBox) editBox.classList.add('hidden');
                const house = document.getElementById('addrHouse').value.trim();
                const street = document.getElementById('addrStreet').value.trim();
                const barangay = document.getElementById('addrBarangay').value.trim();
                const city = document.getElementById('addrCity').value.trim();
                const province = document.getElementById('addrProvince').value.trim();
                const addrEl = document.getElementById('deliverAddress');
                if (addrEl) addrEl.textContent = [house, street, barangay, city, province].filter(Boolean).join(', ');
            });
        }

        if (placeBtn) {
            placeBtn.addEventListener('click', function(e) {
                if (!isLipaAddress()) {
                    e.preventDefault();
                    e.stopPropagation();
                    showLipaMessage();
                    return;
                }
                if (reorderMarker) {
                    const pos = reorderMarker.getLatLng();
                    if (!isInLipa(pos.lat, pos.lng)) {
                        e.preventDefault();
                        e.stopPropagation();
                        showLipaMessage();
                        return;
                    }
                }
            });
        }
    });
</script>
</body>

</html>