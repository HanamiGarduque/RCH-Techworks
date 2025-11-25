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

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            padding-left: 5rem;
            transition: padding-left 0.5s ease-in-out;
        }

        .tab-button.active {
            background-color: #2563eb;
            color: white;
            border-radius: 0.375rem;
        }
    </style>
</head>

<body class="bg-gray-100 overflow-hidden">
    <script src="includes/sidebar.js"></script>


    <!-- MAIN CONTENT -->
    <div class="flex h-screen">
        <div class="flex-1">
            <main class="bg-white rounded-xl shadow-lg overflow-y-auto h-[calc(100vh-2rem)]">
                <div class="main-header">
                    <?php
                    $pageTitle = "My Orders";
                    $pageSubtitle = "Hydrate made easy—order and we'll handle the rest!";
                    include 'includes/header.php';
                    ?>
                </div>
                <div class="p-6">

                    <!-- TABS -->
                    <div class="flex space-x-2 p-1 bg-gray-100 rounded-lg w-full mb-6">
                        <button class="tab-button flex-1 text-center py-2 text-sm font-medium hover:bg-blue-600 hover:text-white" data-tab="topay">To Pay</button>
                        <button class="tab-button flex-1 text-center py-2 text-sm font-medium hover:bg-blue-600 hover:text-white" data-tab="toreceive">To Receive</button>
                        <button class="tab-button flex-1 text-center py-2 text-sm font-medium hover:bg-blue-600 hover:text-white" data-tab="completed">Completed</button>
                        <button class="tab-button flex-1 text-center py-2 text-sm font-medium hover:bg-blue-600 hover:text-white" data-tab="cancelled">Cancelled</button>
                    </div>

                    <!-- PLACEHOLDER CARDS -->

                    <!-- TO PAY -->
                    <div id="topay" class="tab-content space-y-6">
                        <?php for ($i = 1; $i <= 2; $i++): ?>
                            <div class="p-4 border rounded-lg shadow-sm bg-white">
                                <div class="flex justify-between mb-2">
                                    <span class="font-semibold">ORDER-000<?= $i ?></span>
                                    <span class="text-yellow-500 font-medium">Pending</span>
                                </div>

                                <hr class="my-2">

                                <p class="text-sm text-gray-600 mb-2">2 items</p>
                                <p class="text-lg font-bold">₱150.00</p>

                                <div class="flex justify-end mt-3 space-x-2">
                                    <button class="bg-red-500 text-white px-4 py-2 rounded">Cancel</button>
                                    <a href="order_details.php" class="bg-blue-600 text-white px-4 py-2 rounded inline-block">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <!-- TO RECEIVE -->
                    <div id="toreceive" class="tab-content space-y-6 hidden">
                        <?php for ($i = 1; $i <= 2; $i++): ?>
                            <div class="p-4 border rounded-lg shadow-sm bg-white">
                                <div class="flex justify-between mb-2">
                                    <span class="font-semibold">ORDER-100<?= $i ?></span>
                                    <span class="text-blue-600 font-medium">On Delivery</span>
                                </div>

                                <hr class="my-2">

                                <p class="text-sm text-gray-600 mb-2">1 item</p>
                                <p class="text-lg font-bold">₱80.00</p>

                                <div class="flex justify-end">
                                    <button class="bg-blue-600 text-white px-4 py-2 rounded">Track Delivery</button>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <!-- COMPLETED -->
                    <div id="completed" class="tab-content space-y-6 hidden">
                        <?php for ($i = 1; $i <= 2; $i++): ?>
                            <div class="p-4 border rounded-lg shadow-sm bg-white">
                                <div class="flex justify-between mb-2">
                                    <span class="font-semibold">ORDER-200<?= $i ?></span>
                                    <span class="text-green-600 font-medium">Delivered</span>
                                </div>

                                <hr class="my-2">

                                <p class="text-sm text-gray-600 mb-2">3 items</p>
                                <p class="text-lg font-bold">₱230.00</p>

                                <div class="flex justify-end space-x-2">
                                    <button class="bg-blue-600 text-white px-4 py-2 rounded">Reorder</button>
                                    <a href="order_details.php" class="bg-blue-600 text-white px-4 py-2 rounded inline-block">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <!-- CANCELLED -->
                    <div id="cancelled" class="tab-content space-y-6 hidden">
                        <?php for ($i = 1; $i <= 2; $i++): ?>
                            <div class="p-4 border rounded-lg shadow-sm bg-white">
                                <div class="flex justify-between mb-2">
                                    <span class="font-semibold">ORDER-300<?= $i ?></span>
                                    <span class="text-red-600 font-medium">Cancelled</span>
                                </div>

                                <hr class="my-2">

                                <p class="text-sm text-gray-600 mb-2">1 item</p>
                                <p class="text-lg font-bold">₱60.00</p>

                                <div class="flex justify-end">
                                    <a href="order_details.php" class="bg-blue-600 text-white px-4 py-2 rounded inline-block">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <script>
        // Tab switching
        document.addEventListener('DOMContentLoaded', () => {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');

            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const tab = button.getAttribute('data-tab');

                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.add('hidden'));

                    button.classList.add('active');
                    document.getElementById(tab).classList.remove('hidden');
                });
            });

            // default open tab
            document.querySelector('[data-tab="topay"]').click();
        });
    </script>

</body>

</html>