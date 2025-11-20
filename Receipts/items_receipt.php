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
        <script src="../Customer/includes/sidebar.js"></script>

        <!-- Main Content -->
        <main class="flex-1 ml-20 bg-gray-50 rounded-xl shadow overflow-y-auto">
            <div class="main-header">
                <?php //ibahin mo lang tong header na to sa bawat page para magiba title at subtitle
                $pageTitle = "Receipts";
                $pageSubtitle = "Review your order history and download official receipts.";
                include '../Customer/includes/header.php';
                ?>
            </div>
            <!-- open -->
            <div class="bg-white shadow-lg max-w-2xl w-full p-10 rounded-lg">

                <!-- HEADER -->
                <div class="text-center">
                    <h1 class="text-2xl mb-5x` font-bold text-blue-500">RCH: Water Ordering, Refilling, and Delivery</h1>
                    <!-- HEADER -->
                    <div class="text-left mt-6">
                        <h2 class="text-xl font-semibold">Official Receipt</h2>
                        <p class="text-gray-500 font-normal italic">This receipt serves as your official proof of purchase.</p>
                    </div>

                </div>

                <!-- ORDER INFO -->
                <div class="mt-8 text-sm">
                    <p><strong>Receipt No.:</strong> <span id="receiptNo"></span></p>
                    <p><strong>Order No.:</strong> <span id="orderNo"></span></p>
                </div>

                <!-- CUSTOMER INFO -->
                <div class="mt-6 grid grid-cols-2 gap-4 text-sm">
                    <p><strong>Customer:</strong> <span id="custName"></span></p>
                    <p><strong>Phone No.:</strong> <span id="custPhone"></span></p>
                    <p class="col-span-2"><strong>Address:</strong> <span id="custAddress"></span></p>
                    <p class="col-span-2"><strong>Payment Method:</strong> Cash On Delivery (COD)</p>
                </div>

                <!-- ITEMS TABLE -->
                <table class="w-full border mt-8 text-sm">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="border p-2 text-left">Item Description</th>
                            <th class="border p-2 text-center">Qty</th>
                            <th class="border p-2 text-center">Unit Price</th>
                            <th class="border p-2 text-center">Total</th>
                        </tr>
                    </thead>
                    <tbody id="itemsTable"></tbody>
                </table>

                <!-- SUMMARY -->
                <div class="mt-6 text-sm">
                    <p><strong>Total Items:</strong> <span id="totalItems"></span></p>
                    <p><strong>Total Amount:</strong> ₱<span id="totalAmount"></span></p>
                    <p><strong>Payment Status:</strong> Paid</p>
                    <p><strong>Delivery Status:</strong> Delivered</p>
                </div>

                <!-- DELIVERY INFO -->
                <div class="mt-6 text-sm">
                    <p><strong>Delivery Rider:</strong> Ramon Fontelera</p>
                    <p><strong>Delivery Date & Time:</strong> <span id="deliveryDate"></span></p>
                </div>

                <!-- FOOTER -->
                <div class="text-center mt-10">
                    <p class="text-blue-600 font-semibold">Thank you for choosing</p>
                    <p class="text-blue-600 font-bold">RCH: Water Ordering, Refilling, and Delivery Services!</p>
                </div>

            </div>


    </div>




    <!-- close -->
    </main>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const urlParams = new URLSearchParams(window.location.search);
            const orderId = urlParams.get("order_id");

            if (!orderId) {
                console.error("No order_id in URL");
                return;
            }

            fetch(`items_receipts_backend.php?order_id=${orderId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        console.error(data.error);
                        return;
                    }

                    const order = data.order;
                    const items = data.items;

                    // Generate receipt number based on date + order_id
                    const today = new Date();
                    const yy = today.getFullYear().toString().slice(-2); // last 2 digits of year
                    const mm = String(today.getMonth() + 1).padStart(2, '0'); // month with leading 0
                    const dd = String(today.getDate()).padStart(2, '0'); // day with leading 0

                    const receiptNo = `RCPT-${yy}${mm}${dd}${order.order_id}`;
                    document.getElementById("receiptNo").innerText = receiptNo;
                    document.getElementById("orderNo").innerText = order.order_number;
                    document.getElementById("custName").innerText = order.name;
                    document.getElementById("custPhone").innerText = order.phone_number;
                    document.getElementById("custAddress").innerText = order.address;

                    let tbody = document.getElementById("itemsTable");
                    tbody.innerHTML = "";

                    items.forEach(i => {
                        tbody.innerHTML += `
                    <tr>
                        <td class="border p-2">${i.item_name}</td>
                        <td class="border p-2 text-center">${i.quantity}</td>
                        <td class="border p-2 text-center">₱${parseFloat(i.unit_price).toFixed(2)}</td>
                        <td class="border p-2 text-center">₱${parseFloat(i.subtotal).toFixed(2)}</td>
                    </tr>
                `;
                    });

                    document.getElementById("totalItems").innerText = items.length;
                    document.getElementById("totalAmount").innerText =
                        parseFloat(order.total_amount).toFixed(2);

                    // Delivery date
                    // Delivery date
                    const dt = new Date(order.delivery_datetime);

                    // Format date
                    const dateStr = dt.toLocaleDateString(); // e.g., 11/20/2025

                    // Format time in 12-hour format with uppercase AM/PM
                    let hours = dt.getHours();
                    const minutes = String(dt.getMinutes()).padStart(2, "0");
                    const ampm = hours >= 12 ? "PM" : "AM";
                    hours = hours % 12;
                    hours = hours ? hours : 12; // the hour '0' should be '12'

                    const timeStr = `${hours}:${minutes} ${ampm}`;

                    document.getElementById("deliveryDate").innerText = `${dateStr}, ${timeStr}`;

                })
                .catch(err => console.error("AJAX error:", err));
        });
    </script>


</body>