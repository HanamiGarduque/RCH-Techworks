<?php
// order_details.php

// Get order ID from URL
$orderID = isset($_GET['order_id']) ? $_GET['order_id'] : "Unknown Order";

$pageTitle = "Order Details";
$pageSubtitle = "View full breakdown of your order.";

// For demo: static data (replace with DB query)
$customerName = "Christine May Padua";
$customerPhone = "09168195195";
$deliveryAddress = "Camella Homes, Block 26 Lot 33 Alexia Street, Phase 1, Tibig, Lipa City, Batangas 4217";

$products = [
    ["name" => "20L Slim Container Gallon", "qty" => 2, "price" => 170],
    ["name" => "Slim Gallon Faucet - Press", "qty" => 10, "price" => 25],
];

$subtotal = 590;
$tax = 47.20;
$deliveryFee = 40;
$total = 677.20;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>RCH Water - Order Water Refill</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Bootstrap Icons Only -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Font: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- SweetAlert -->
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
                // Change this for page title/subtitle per page
                $pageTitle = "Order Water Refill";
                $pageSubtitle = "Hydrate made easy—order and we'll handle the rest!";
                include 'includes/header.php';
                ?>
            </div>

            <!-- PAGE CONTENT START -->
            <div class="p-6">

                <!-- Address Section -->
                <div class="bg-white p-5 rounded-xl shadow mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <h2 class="text-lg font-semibold"><i class="bi bi-geo-alt-fill text-blue-600 mr-2"></i> Delivery Address</h2>

                        <!-- BLUE CHANGE BUTTON -->
                        <button onclick="openEditAddress()"
                            class="text-blue-600 font-medium hover:underline">
                            Change
                        </button>
                    </div>

                    <p class="text-gray-700">
                        <?php echo htmlspecialchars($user_address ?? "No address found"); ?>
                    </p>
                </div>

                <!-- Insert Your Page Content Below -->
                <div>
                    <!-- Your content here -->
                </div>

            </div>
            <!-- PAGE CONTENT END -->

        </main>
    </div>

    <script>
        // Example function for edit address (SweetAlert)
        function openEditAddress() {
            Swal.fire({
                title: "Edit Address",
                html: `
                    <input id="edit-address" class="swal2-input" placeholder="Enter new address">
                `,
                confirmButtonText: "Save",
                confirmButtonColor: "#1d4ed8",
                showCancelButton: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    let newAddress = document.getElementById("edit-address").value;

                    if (newAddress.trim() === "") {
                        return Swal.fire("Error", "Address cannot be empty.", "error");
                    }

                    // TODO: AJAX request to update address
                    Swal.fire("Updated!", "Your address has been updated.", "success");
                }
            });
        }
    </script>

</body>
</html>

