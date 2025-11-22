<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>RCH Water - <?= htmlspecialchars($pageTitle) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            /* Add padding to the body to account for the fixed, collapsed sidebar (w-20) */
            padding-left: 5rem;
            /* 5rem = 80px (w-20 in tailwind) */
            transition: padding-left 0.5s ease-in-out;
        }

        /* Adjust padding when the sidebar is hovered/expanded (w-64) */
        .group:hover+.content-area {
            padding-left: 16rem;
            /* 16rem = 256px (w-64 in tailwind) */
        }

        /* Custom style for the active tab (To Pay) */
        .tab-button.active {
            background-color: #2563eb;
            /* Blue 600 */
            color: white;
            border-radius: 0.375rem;
        }

        /* Ensures the sidebar is at the beginning of the body and main content pushes right */
        .content-area {
            min-height: 100vh;
        }
    </style>
</head>

<body class="bg-gray-100 overflow-hidden">
    <div class="content-area flex h-screen">
        <main class="bg-white rounded-xl shadow-lg overflow-y-auto">
            <!-- Sidebar -->
            <script src="includes/sidebar.js"></script>


            <div class="flex flex-col h-screen p-6">
              <?php
                $pageTitle = "Browse Products";
                $pageSubtitle = "Shop for gallon containers and accessories!";
                include 'includes/header.php';
                ?>

                <div class="flex space-x-2 p-1 bg-gray-100 rounded-lg w-full mb-6">
                    <button class="tab-button flex-1 text-center py-2 text-gray-700 text-sm font-medium hover:bg-blue-600 hover:text-white rounded-md transition-colors duration-200" data-tab="topay">To Pay</button>
                    <button class="tab-button flex-1 text-center py-2 text-gray-700 text-sm font-medium hover:bg-blue-600 hover:text-white rounded-md transition-colors duration-200" data-tab="toreceive">To Recieve</button>
                    <button class="tab-button flex-1 text-center py-2 text-gray-700 text-sm font-medium hover:bg-blue-600 hover:text-white rounded-md transition-colors duration-200" data-tab="completed">Completed</button>
                    <button class="tab-button flex-1 text-center py-2 text-gray-700 text-sm font-medium hover:bg-blue-600 hover:text-white rounded-md transition-colors duration-200" data-tab="cancelled">Cancelled</button>

                </div>


                <!-- --------------------------------------------- -->
                <!-- TO PAY TAB (Pending Orders) -->
                <!-- --------------------------------------------- -->
                <div id="topay" class="tab-content space-y-6 overflow-x-auto">

                    <div class="p-4 border border-gray-200 rounded-lg shadow-sm">
                        <div class="flex justify-between items-center mb-3">
                            <span class="font-semibold text-gray-800">ORD-110325001</span>
                            <span class="text-red-500 font-medium">Pending</span>
                        </div>

                        <hr class="mb-3">

                        <!-- COLUMN LABELS -->
                        <div class="grid grid-cols-4 gap-4 text-sm font-semibold text-gray-700 mb-1">
                            <div class="col-span-2">Product</div>
                            <div class="text-center">Quantity</div>
                            <div></div>
                        </div>

                        <!-- PRODUCT LIST -->
                        <div class="grid grid-cols-4 gap-2 text-sm text-gray-600 mb-4">
                            <div class="col-span-2">20L Slim Container Gallon</div>
                            <div class="text-center">2</div>
                            <div></div>

                            <div class="col-span-2">Slim Gallon Faucet - Press</div>
                            <div class="text-center">10</div>
                            <div></div>
                        </div>

                        <div class="flex justify-end items-center mb-4">
                            <span class="text-sm text-gray-600 mr-2">Total 12 Item:</span>
                            <span class="text-lg font-bold text-gray-800">₱677.20</span>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <button class="cancel-order bg-red-500 text-white font-medium py-2 px-4 rounded-md shadow hover:bg-red-600">
                                Cancel Order
                            </button>
                            <a href="order_details.php?order=ORD-110325001&status=pending"
                                class="bg-white text-blue-600 border border-blue-600 font-medium py-2 px-4 rounded-md shadow hover:bg-blue-600 hover:text-white">
                                View Details
                            </a>
                        </div>
                    </div>

                </div>


                <!-- --------------------------------------------- -->
                <!-- TO RECEIVE TAB (On Delivery Orders) -->
                <!-- --------------------------------------------- -->
                <div id="toreceive" class="tab-content space-y-6 hidden overflow-x-auto">

                    <div class="p-4 border border-gray-200 rounded-lg shadow-sm">
                        <div class="flex justify-between items-center mb-3">
                            <span class="font-semibold text-gray-800">ORD-110325001</span>
                            <span class="text-blue-500 font-medium">On Delivery</span>
                        </div>
                        <hr class="mb-3">

                        <!-- COLUMN LABELS -->
                        <div class="grid grid-cols-4 gap-4 text-sm font-semibold text-gray-700 mb-1">
                            <div class="col-span-2">Product</div>
                            <div class="text-center">Quantity</div>
                            <div></div>
                        </div>

                        <div class="grid grid-cols-4 gap-2 text-sm text-gray-600 mb-4">
                            <div class="col-span-2">20L Slim Container Gallon</div>
                            <div class="col-span-1 text-center">2</div>

                            <div class="col-span-2">Slim Gallon Faucet - Press</div>
                            <div class="col-span-1 text-center">10</div>
                        </div>

                        <div class="flex justify-end items-center mb-4">
                            <span class="text-sm text-gray-600 mr-2">Total 12 Item:</span>
                            <span class="text-lg font-bold text-gray-800">₱677.20</span>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="order_details.php?order=ORD-110325001&status=on_delivery"
                                class="bg-white text-blue-600 border border-blue-600 font-medium py-2 px-4 rounded-md shadow hover:bg-blue-600 hover:text-white">
                                View Details
                            </a>
                        </div>
                    </div>


                    <div class="p-4 border border-gray-200 rounded-lg shadow-sm">
                        <div class="flex justify-between items-center mb-3">
                            <span class="font-semibold text-gray-800">ORD-110325002</span>
                            <span class="text-blue-500 font-medium">On Delivery</span>
                        </div>
                        <hr class="mb-3">

                        <!-- COLUMN LABELS -->
                        <div class="grid grid-cols-4 gap-4 text-sm font-semibold text-gray-700 mb-1">
                            <div class="col-span-2">Product</div>
                            <div class="text-center">Quantity</div>
                            <div></div>
                        </div>

                        <div class="grid grid-cols-4 gap-2 text-sm text-gray-600 mb-4">
                            <div class="col-span-2">Refill - Slim Gallon 20L</div>
                            <div class="col-span-1 text-center">3</div>

                            <div class="col-span-2">Refill - Round Gallon 18.9L</div>
                            <div class="col-span-1 text-center">2</div>
                        </div>

                        <div class="flex justify-end items-center mb-4">
                            <span class="text-sm text-gray-600 mr-2">Total 5 Item:</span>
                            <span class="text-lg font-bold text-gray-800">₱150.00</span>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="order_details.php?order=ORD-110325002&status=on_delivery"
                                class="bg-white text-blue-600 border border-blue-600 font-medium py-2 px-4 rounded-md shadow hover:bg-blue-600 hover:text-white">
                                View Details
                            </a>
                        </div>
                    </div>

                </div>




                <!-- --------------------------------------------- -->
                <!-- COMPLETED TAB (Delivered Orders) -->
                <!-- --------------------------------------------- -->
                <div id="completed" class="tab-content space-y-6 hidden overflow-x-auto">

                    <div class="p-4 border border-gray-200 rounded-lg shadow-sm">
                        <div class="flex justify-between items-center mb-3">
                            <span class="font-semibold text-gray-800">ORD-110325001</span>
                            <span class="text-green-600 font-medium">Delivered</span>
                        </div>
                        <hr class="mb-3">

                        <!-- COLUMN LABELS -->
                        <div class="grid grid-cols-4 gap-4 text-sm font-semibold text-gray-700 mb-1">
                            <div class="col-span-2">Product</div>
                            <div class="text-center">Quantity</div>
                            <div></div>
                        </div>

                        <div class="grid grid-cols-4 gap-2 text-sm text-gray-600 mb-4">
                            <div class="col-span-2">20L Slim Container Gallon</div>
                            <div class="col-span-1 text-center">2</div>

                            <div class="col-span-2">Slim Gallon Faucet - Press</div>
                            <div class="col-span-1 text-center">10</div>
                        </div>

                        <div class="flex justify-end items-center mb-4">
                            <span class="text-sm text-gray-600 mr-2">Total 12 Item:</span>
                            <span class="text-lg font-bold text-gray-800">₱677.20</span>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <button class="bg-blue-600 text-white font-medium py-2 px-4 rounded-md shadow hover:bg-blue-700 transition-colors duration-200 flex items-center space-x-2">
                                <i class="bi bi-arrow-repeat text-white"></i>
                                <span>Reorder</span>
                            </button>
                            <a href="order_details.php?order=ORD-110325001&status=completed"
                                class="bg-white text-blue-600 border border-blue-600 font-medium py-2 px-4 rounded-md shadow hover:bg-blue-600 hover:text-white">
                                View Details
                            </a>
                        </div>
                    </div>


                    <div class="p-4 border border-gray-200 rounded-lg shadow-sm">
                        <div class="flex justify-between items-center mb-3">
                            <span class="font-semibold text-gray-800">ORD-110325002</span>
                            <span class="text-green-600 font-medium">Delivered</span>
                        </div>
                        <hr class="mb-3">

                        <!-- COLUMN LABELS -->
                        <div class="grid grid-cols-4 gap-4 text-sm font-semibold text-gray-700 mb-1">
                            <div class="col-span-2">Product</div>
                            <div class="text-center">Quantity</div>
                            <div></div>
                        </div>

                        <div class="grid grid-cols-4 gap-2 text-sm text-gray-600 mb-4">
                            <div class="col-span-2">Refill - Slim Gallon 20L</div>
                            <div class="col-span-1 text-center">3</div>

                            <div class="col-span-2">Refill - Round Gallon 18.9L</div>
                            <div class="col-span-1 text-center">2</div>
                        </div>

                        <div class="flex justify-end items-center mb-4">
                            <span class="text-sm text-gray-600 mr-2">Total 5 Item:</span>
                            <span class="text-lg font-bold text-gray-800">₱150.00</span>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <button class="bg-blue-600 text-white font-medium py-2 px-4 rounded-md shadow hover:bg-blue-700 transition-colors duration-200 flex items-center space-x-2">
                                <i class="bi bi-arrow-repeat text-white"></i>
                                <span>Reorder</span>
                            </button>
                            <a href="order_details.php?order=ORD-110325002&status=completed"
                                class="bg-white text-blue-600 border border-blue-600 font-medium py-2 px-4 rounded-md shadow hover:bg-blue-600 hover:text-white">
                                View Details
                            </a>
                        </div>
                    </div>

                </div>

                <!-- --------------------------------------------- -->
                <!-- CANCELED TAB (Delivered Orders) -->
                <!-- --------------------------------------------- -->
                <div id="cancelled" class="tab-content space-y-6 hidden overflow-x-auto">

                    <div class="p-4 border border-gray-200 rounded-lg shadow-sm">
                        <div class="flex justify-between items-center mb-3">
                            <span class="font-semibold text-gray-800">ORD-110325001</span>
                            <span class="text-red-600 font-medium">Cancelled</span>
                        </div>
                        <hr class="mb-3">

                        <!-- COLUMN LABELS -->
                        <div class="grid grid-cols-4 gap-4 text-sm font-semibold text-gray-700 mb-1">
                            <div class="col-span-2">Product</div>
                            <div class="text-center">Quantity</div>
                            <div></div>
                        </div>

                        <div class="grid grid-cols-4 gap-2 text-sm text-gray-600 mb-4">
                            <div class="col-span-2">20L Slim Container Gallon</div>
                            <div class="col-span-1 text-center">2</div>

                            <div class="col-span-2">Slim Gallon Faucet - Press</div>
                            <div class="col-span-1 text-center">10</div>
                        </div>

                        <div class="flex justify-end items-center mb-4">
                            <span class="text-sm text-gray-600 mr-2">Total 12 Item:</span>
                            <span class="text-lg font-bold text-gray-800">₱677.20</span>
                        </div>

                    </div>


                    <div class="p-4 border border-gray-200 rounded-lg shadow-sm">
                        <div class="flex justify-between items-center mb-3">
                            <span class="font-semibold text-gray-800">ORD-110325002</span>
                            <span class="text-red-600 font-medium">Cancelled</span>
                        </div>
                        <hr class="mb-3">

                        <!-- COLUMN LABELS -->
                        <div class="grid grid-cols-4 gap-4 text-sm font-semibold text-gray-700 mb-1">
                            <div class="col-span-2">Product</div>
                            <div class="text-center">Quantity</div>
                            <div></div>
                        </div>

                        <div class="grid grid-cols-4 gap-2 text-sm text-gray-600 mb-4">
                            <div class="col-span-2">Refill - Slim Gallon 20L</div>
                            <div class="col-span-1 text-center">3</div>

                            <div class="col-span-2">Refill - Round Gallon 18.9L</div>
                            <div class="col-span-1 text-center">2</div>
                        </div>

                        <div class="flex justify-end items-center mb-4">
                            <span class="text-sm text-gray-600 mr-2">Total 5 Item:</span>
                            <span class="text-lg font-bold text-gray-800">₱150.00</span>
                        </div>

                    </div>

                </div>


            </div>

        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');

            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const targetTab = button.getAttribute('data-tab');

                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.add('hidden'));

                    button.classList.add('active');
                    document.getElementById(targetTab).classList.remove('hidden');
                });
            });

            // Set the initial active tab to 'To Pay'
            const initialTabButton = document.querySelector('[data-tab="topay"]');
            if (initialTabButton) {
                initialTabButton.click();
            }
        });

        document.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll(".cancel-order").forEach(button => {
                button.addEventListener("click", function() {

                    Swal.fire({
                        title: "Are you sure you want to cancel this order?",
                        text: "Once cancelled, you won’t be able to restore it.",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#dc2626", // red-600
                        cancelButtonColor: "#6b7280", // gray-500
                        confirmButtonText: "Yes, cancel it"
                    }).then((result) => {
                        if (result.isConfirmed) {

                            // OPTIONAL: Call your backend PHP cancel API here
                            // fetch('cancel_order.php?id=ORDER_ID')

                            Swal.fire({
                                title: "Cancelled!",
                                text: "Order cancelled successfully.",
                                icon: "success",
                                confirmButtonColor: "#2563eb"
                            });
                        }
                    });

                });
            });
        });
        document.addEventListener("DOMContentLoaded", function() {
            const topayContainer = document.getElementById("topay");

            fetch("../Order/pending_deliveries.php")
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        console.error(data.error);
                        return;
                    }

                    topayContainer.innerHTML = ""; // clear existing content

                    data.forEach(entry => {
                        const order = entry.order;
                        const items = entry.items;

                        // Calculate total items and total amount
                        let totalQty = 0;
                        let totalAmount = 0;
                        items.forEach(item => {
                            totalQty += parseInt(item.quantity);
                            totalAmount += parseFloat(item.quantity) * parseFloat(item.unit_price);
                        });

                        // Generate HTML for order
                        let itemsHtml = "";
                        items.forEach(item => {
                            itemsHtml += `
                        <div class="col-span-2">${item.item_name}</div>
                        <div class="text-center">${item.quantity}</div>
                        <div></div>
                    `;
                        });

                        const orderHtml = `
                    <div class="p-4 border border-gray-200 rounded-lg shadow-sm mb-4">
                        <div class="flex justify-between items-center mb-3">
                            <span class="font-semibold text-gray-800">${order.order_number}</span>
                            <span class="text-red-500 font-medium">${order.order_status.charAt(0).toUpperCase() + order.order_status.slice(1)}</span>
                        </div>

                        <hr class="mb-3">

                        <!-- COLUMN LABELS -->
                        <div class="grid grid-cols-4 gap-4 text-sm font-semibold text-gray-700 mb-1">
                            <div class="col-span-2">Product</div>
                            <div class="text-center">Quantity</div>
                            <div></div>
                        </div>

                        <!-- PRODUCT LIST -->
                        <div class="grid grid-cols-4 gap-2 text-sm text-gray-600 mb-4">
                            ${itemsHtml}
                        </div>

                        <div class="flex justify-end items-center mb-4">
                            <span class="text-sm text-gray-600 mr-2">Total ${totalQty} Item${totalQty > 1 ? 's' : ''}:</span>
                            <span class="text-lg font-bold text-gray-800">₱${totalAmount.toFixed(2)}</span>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <button class="cancel-order bg-red-500 text-white font-medium py-2 px-4 rounded-md shadow hover:bg-red-600">
                                Cancel Order
                            </button>
                            <a href="order_details.php?order=${order.order_number}&status=${order.order_status}"
                                class="bg-white text-blue-600 border border-blue-600 font-medium py-2 px-4 rounded-md shadow hover:bg-blue-600 hover:text-white">
                                View Details
                            </a>
                        </div>
                    </div>
                `;

                        topayContainer.innerHTML += orderHtml;
                    });
                })
                .catch(err => console.error("Fetch error:", err));
        });
    </script>


</body>

</html>