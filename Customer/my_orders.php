<?php
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('customer'); // only admins can access

require_once '../Database/db_connection.php';



$database = new Database();
$db = $database->getConnect();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>RCH Water - My Orders</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            padding-left: 5rem;
            transition: padding-left 0.5s ease-in-out;
        }

        .group:hover+.content-area {
            padding-left: 16rem;
        }

        .tab-button.active {
            background-color: #2563eb;
            color: white;
            border-radius: 0.375rem;
        }

        .content-area {
            min-height: 100vh;
        }
    </style>
</head>

<body class="bg-gray-100 overflow-hidden">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <script src="includes/sidebar.js"></script>

        <div class="flex-1 group-hover:ml-64 transition-all duration-300 ease-in-out">
            <div class="flex flex-col h-screen">

                <div class="content-area">
                    <main class="bg-white rounded-xl shadow-lg overflow-y-auto h-[calc(100vh-2rem)]">

                        <?php
                        $pageTitle = "My Orders";
                        $pageSubtitle = "Track your order status and history";
                        include 'includes/header.php';
                        ?>

                        <div class="p-6">

                            <div class="flex space-x-2 p-1 bg-gray-100 rounded-lg w-full mb-6">
                                <button class="tab-button flex-1 text-center py-2 text-gray-700 text-sm font-medium hover:bg-blue-600 hover:text-white rounded-md transition-colors duration-200" data-tab="topay">To Pay</button>
                                <button class="tab-button flex-1 text-center py-2 text-gray-700 text-sm font-medium hover:bg-blue-600 hover:text-white rounded-md transition-colors duration-200" data-tab="toreceive">To Receive</button>
                                <button class="tab-button flex-1 text-center py-2 text-gray-700 text-sm font-medium hover:bg-blue-600 hover:text-white rounded-md transition-colors duration-200" data-tab="completed">Completed</button>
                                <button class="tab-button flex-1 text-center py-2 text-gray-700 text-sm font-medium hover:bg-blue-600 hover:text-white rounded-md transition-colors duration-200" data-tab="cancelled">Cancelled</button>
                            </div>

                            <!-- TO PAY TAB (Pending Orders) -->
                            <div id="topay" class="tab-content space-y-6">
                                <div class="text-center py-8 text-gray-500">
                                    <i class="fas fa-spinner fa-spin text-3xl mb-2"></i>
                                    <p>Loading orders...</p>
                                </div>
                            </div>

                            <!-- TO RECEIVE TAB (On Delivery Orders) -->
                            <div id="toreceive" class="tab-content space-y-6 hidden">
                                <div class="text-center py-8 text-gray-500">
                                    <i class="fas fa-spinner fa-spin text-3xl mb-2"></i>
                                    <p>Loading orders...</p>
                                </div>
                            </div>

                            <!-- COMPLETED TAB (Delivered Orders) -->
                            <div id="completed" class="tab-content space-y-6 hidden">
                                <div class="text-center py-8 text-gray-500">
                                    <i class="fas fa-spinner fa-spin text-3xl mb-2"></i>
                                    <p>Loading orders...</p>
                                </div>
                            </div>

                            <!-- CANCELLED TAB -->
                            <div id="cancelled" class="tab-content space-y-6 hidden">
                                <div class="text-center py-8 text-gray-500">
                                    <i class="fas fa-spinner fa-spin text-3xl mb-2"></i>
                                    <p>Loading orders...</p>
                                </div>
                            </div>

                        </div>

                    </main>
                </div>
            </div>
        </div>
    </div>

    <script>
        const userId = <?= json_encode($userId) ?>;

        // Tab switching functionality
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

            // Load all tabs
            loadOrders('Pending', 'topay');
            loadOrders('On Delivery', 'toreceive');
            loadOrders('Delivered', 'completed');
            loadOrders('Cancelled', 'cancelled');
        });

        // Function to load orders dynamically
        function loadOrders(status, containerId) {
            const container = document.getElementById(containerId);

            fetch(`../Order/get_orders.php?status=${encodeURIComponent(status)}&user_id=${userId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        container.innerHTML = `
                            <div class="text-center py-8 text-red-500">
                                <i class="fas fa-exclamation-circle text-3xl mb-2"></i>
                                <p>${data.error}</p>
                            </div>
                        `;
                        return;
                    }

                    if (data.length === 0) {
                        container.innerHTML = `
                            <div class="text-center py-12 text-gray-500">
                                <i class="fas fa-box-open text-5xl mb-3 opacity-50"></i>
                                <p class="text-lg font-medium">No orders found</p>
                                <p class="text-sm">You don't have any ${status.toLowerCase()} orders yet.</p>
                            </div>
                        `;
                        return;
                    }

                    container.innerHTML = "";

                    data.forEach(entry => {
                        const order = entry.order;
                        const items = entry.items;

                        let totalQty = 0;
                        items.forEach(item => {
                            totalQty += parseInt(item.quantity);
                        });

                        const totalAmount = parseFloat(order.total_amount);

                        // Generate items HTML
                        let itemsHtml = "";
                        items.forEach(item => {
                            itemsHtml += `
                                <div class="col-span-2">${item.item_name}</div>
                                <div class="text-center">${item.quantity}</div>
                                <div></div>
                            `;
                        });

                        // Status styling
                        let statusClass = '';
                        switch (status) {
                            case 'Pending':
                                statusClass = 'text-yellow-500';
                                break;
                            case 'On Delivery':
                                statusClass = 'text-blue-600';
                                break;
                            case 'Delivered':
                                statusClass = 'text-green-600';
                                break;
                            case 'Cancelled':
                                statusClass = 'text-red-600';
                                break;
                        }

                        // Action buttons based on status
                        let actionButtons = '';
                        if (status === 'Pending') {
                            actionButtons = `
                                <button onclick="cancelOrder('${order.order_number}')" class="cancel-order bg-red-500 text-white font-medium py-2 px-4 rounded-md shadow hover:bg-red-600">
                                    Cancel Order
                                </button>
                                <a href="../Customer/order_details.php?order_number=${order.order_number}"
                                    class="bg-white text-blue-600 border border-blue-600 font-medium py-2 px-4 rounded-md shadow hover:bg-blue-600 hover:text-white">
                                    View Details
                                </a>
                            `;
                        } else if (status === 'On Delivery') {
                            actionButtons = `
                                <a href="../Customer/order_details.php?order_number=${order.order_number}"
                                    class="bg-blue-600 text-white font-medium py-2 px-4 rounded-md shadow hover:bg-blue-700">
                                    Track Delivery
                                </a>
                            `;
                        } else if (status === 'Delivered') {
                            actionButtons = `
                                <button onclick="reorder('${order.refill_id}')" class="bg-blue-600 text-white font-medium py-2 px-4 rounded-md shadow hover:bg-blue-700 transition-colors duration-200 flex items-center space-x-2">
                                    <i class="bi bi-arrow-repeat text-white"></i>
                                    <span>Reorder</span>
                                </button>
                                <a href="../Customer/order_details.php?order_number=${order.order_number}"
                                    class="bg-white text-blue-600 border border-blue-600 font-medium py-2 px-4 rounded-md shadow hover:bg-blue-600 hover:text-white">
                                    View Details
                                </a>
                            `;
                        } else if (status === 'Cancelled') {
                            actionButtons = `
                                <a href="../Customer/order_details.php?order_number=${order.order_number}"
                                    class="bg-white text-blue-600 border border-blue-600 font-medium py-2 px-4 rounded-md shadow hover:bg-blue-600 hover:text-white">
                                    View Details
                                </a>
                            `;
                        }

                        const orderHtml = `
                            <div class="p-4 border border-gray-200 rounded-lg shadow-sm mb-4">
                                <div class="flex justify-between items-center mb-3">
                                    <span class="font-semibold text-gray-800">${order.order_number}</span>
                                    <span class="${statusClass} font-medium">${order.order_status}</span>
                                </div>

                                <hr class="mb-3">

                                <div class="grid grid-cols-4 gap-4 text-sm font-semibold text-gray-700 mb-1">
                                    <div class="col-span-2">Product</div>
                                    <div class="text-center">Quantity</div>
                                    <div></div>
                                </div>

                                <div class="grid grid-cols-4 gap-2 text-sm text-gray-600 mb-4">
                                    ${itemsHtml}
                                </div>

                                <div class="flex justify-end items-center mb-4">
                                    <span class="text-sm text-gray-600 mr-2">Total of ${totalQty} Item${totalQty > 1 ? 's' : ''}:</span>
                                    <span class="text-lg font-bold text-gray-800">₱${totalAmount.toFixed(2)}</span>
                                </div>

                                <div class="flex justify-end space-x-3">
                                    ${actionButtons}
                                </div>
                            </div>
                        `;

                        container.innerHTML += orderHtml;
                    });
                })
                .catch(err => {
                    console.error("Fetch error:", err);
                    container.innerHTML = `
                        <div class="text-center py-8 text-red-500">
                            <i class="fas fa-exclamation-triangle text-3xl mb-2"></i>
                            <p>Failed to load orders. Please try again.</p>
                        </div>
                    `;
                });
        }

        // Cancel order function
        function cancelOrder(orderNumber) {
            Swal.fire({
                title: "Are you sure you want to cancel this order?",
                text: "Once cancelled, you won't be able to restore it.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#dc2626",
                cancelButtonColor: "#6b7280",
                confirmButtonText: "Yes, cancel it"
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../Order/cancel_order.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                order_number: orderNumber
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: "Cancelled!",
                                    text: "Order cancelled successfully.",
                                    icon: "success",
                                    confirmButtonColor: "#2563eb"
                                }).then(() => {
                                    // Reload orders
                                    loadOrders('Pending', 'topay');
                                    loadOrders('Cancelled', 'cancelled');
                                });
                            } else {
                                Swal.fire({
                                    title: "Error!",
                                    text: data.error || "Failed to cancel order.",
                                    icon: "error",
                                    confirmButtonColor: "#dc2626"
                                });
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire({
                                title: "Error!",
                                text: "An error occurred. Please try again.",
                                icon: "error",
                                confirmButtonColor: "#dc2626"
                            });
                        });
                }
            });
        }

        // Reorder function
        function reorder(refillId) {
            Swal.fire({
                title: "Reorder this item?",
                text: "This will add the items to your cart.",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#2563eb",
                cancelButtonColor: "#6b7280",
                confirmButtonText: "Yes, reorder"
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../Order/reorder.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                refill_id: refillId
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: "Success!",
                                    text: "Items added to cart.",
                                    icon: "success",
                                    confirmButtonColor: "#2563eb"
                                }).then(() => {
                                    window.location.href = '../Customer/order_water_refill.php';
                                });
                            } else {
                                Swal.fire({
                                    title: "Error!",
                                    text: data.error || "Failed to reorder.",
                                    icon: "error",
                                    confirmButtonColor: "#dc2626"
                                });
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire({
                                title: "Error!",
                                text: "An error occurred. Please try again.",
                                icon: "error",
                                confirmButtonColor: "#dc2626"
                            });
                        });
                }
            });
        }
    </script>

</body>

</html>