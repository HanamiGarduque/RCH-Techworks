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

    <style>
        body {
            font-family: 'Poppins', sans-serif;
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
                                        <input id="addrCity" placeholder="City"
                                            class="border rounded px-2 py-2" />
                                        <input id="addrProvince" placeholder="Province"
                                            class="border rounded px-2 py-2" />
                                        <input id="addrPostal" placeholder="Postal Code"
                                            class="border rounded px-2 py-2" />
                                    </div>
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
                                <!-- Map placeholder -->
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
</body>

</html>
