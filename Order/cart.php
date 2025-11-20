<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>My Cart - RCH Water</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .main-scroll::-webkit-scrollbar {
            width: 10px;
        }

        .main-scroll::-webkit-scrollbar-thumb {
            background: #cfd7df;
            border-radius: 10px;
        }

        .btn-blue {
            background-color: #2563eb;
            color: white;
        }

        .btn-blue:hover {
            background-color: #1d4ed8;
        }

        .card-rounded {
            border-radius: 0.75rem;
        }
    </style>
</head>

<body class="bg-gray-100">
    <div class="flex h-screen overflow-hidden">

        <!-- Sidebar -->
        <script src="../Customer/includes/sidebar.js"></script>

        <div class="flex-1 ml-20 group-hover:ml-64 transition-all duration-300 ease-in-out">
            <div class="flex flex-col h-screen">

                <!-- Header -->
                <?php
                $pageTitle = "My Cart";
                $pageSubtitle = "Review your selected items before checkout";
                include '../Customer/includes/header.php';
                ?>

                <div class="m-3 flex flex-col flex-1 overflow-hidden">

                    <!-- Main Content -->
                    <div id="browseView"
                        class="p-6 bg-white rounded-lg shadow-lg ring-3 ring-gray-200 flex flex-col flex-1 overflow-auto">

                        <!-- Cards -->
                        <div class="space-y-4">

                            <?php
                            $userId = 4; // replace with session user_id
                            require_once '../Database/db_connection.php';

                            $database = new Database();
                            $db = $database->getConnect();

                            // FIXED: correct query preparation
                            $query = $db->prepare("
                                SELECT 
                                    c.cart_id,
                                    c.product_id,
                                    c.quantity,
                                    c.unit_price,
                                    c.subtotal,
                                    p.item_image,
                                    p.item_name AS product_name
                                FROM cart c
                                JOIN inventory p ON p.item_id = c.product_id
                                WHERE c.user_id = :uid
                            ");

                            $query->execute(['uid' => $userId]);
                            $cartItems = $query->fetchAll(PDO::FETCH_ASSOC);
                            ?>

                            <?php foreach ($cartItems as $item): ?>
                                <div class="w-full bg-white shadow rounded-lg p-4 border flex gap-4 items-center" id="cart-item-<?= $item['cart_id'] ?>">

                                    <!-- Product Image -->
                                    <div class="flex-shrink-0">
                                        <img src="../Assets/<?= $item['item_image'] ?>"
                                            alt="<?= htmlspecialchars($item['product_name']) ?>"
                                            class="w-36 h-36 object-cover rounded ring-4 ring-blue-500">
                                    </div>


                                    <!-- Product Info & Controls -->
                                    <div class="flex-1 flex flex-col">

                                        <!-- Header: Product Name + Delete -->
                                        <div class="flex items-start justify-between">
                                            <h2 class="text-lg font-semibold flex-1">
                                                <?= htmlspecialchars($item['product_name']) ?>
                                            </h2>
                                            <button
                                                type="button"
                                                class="text-red-500 rounded hover:text-red-700 transition ml-2 deleteItemBtn"
                                                data-id="<?= $item['cart_id'] ?>"
                                                title="Remove item">
                                                <i class="fa-solid fa-trash text-lg"></i>
                                            </button>
                                        </div>

                                        <!-- Price Information -->
                                        <div class="mb-4 space-y-2">
                                            <p class="text-sm text-gray-600">
                                                Unit Price: <span class="font-medium">₱<?= number_format($item['unit_price'], 2) ?></span>
                                            </p>
                                            <p class="text-sm text-gray-800 font-semibold">
                                                Subtotal: <span class="subtotal text-blue-600">₱<?= number_format($item['subtotal'], 2) ?></span>
                                            </p>

                                        </div>

                                        <!-- Controls Row: Quantity + Buy -->
                                        <div class="flex items-center justify-between gap-4">
                                            <form action="update_cart.php" method="POST" class="flex items-center gap-2">
                                                <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">

                                                <button type="submit" name="action" value="decrement"
                                                    class="px-3 py-2 bg-white border hover:bg-gray-100 rounded text-sm font-medium transition">
                                                    −
                                                </button>

                                                <input type="number" name="quantity"
                                                    value="<?= $item['quantity'] ?>"
                                                    class="w-16 border rounded p-2 text-center text-sm" min="1">

                                                <button type="submit" name="action" value="increment"
                                                    class="px-3 py-2 bg-white border hover:bg-gray-100 rounded text-sm font-medium transition">
                                                    +
                                                </button>
                                            </form>

                                            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium shadow transition"
                                                onclick="buyNow(<?= $item['cart_id'] ?>)">
                                                Buy Now
                                            </button>
                                        </div>

                                    </div>
                                </div>


                            <?php endforeach; ?>
                        </div>

                    </div>

                    <!-- Checkout View -->
                    <div id="checkoutView"
                        class="hidden p-6 bg-white rounded-lg shadow-lg ring-3 ring-gray-200 flex flex-col flex-1 overflow-auto">
                        <h3 class="text-2xl font-semibold mb-4">Checkout</h3>

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
                                    <button id="changeAddressBtn"
                                        class="text-sm text-blue-600 hover:underline">Change</button>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert DELETE HANDLER -->
    <script>
        document.querySelectorAll(".deleteItemBtn").forEach(btn => {
            btn.addEventListener("click", function() {
                let cartId = this.dataset.id;
                let itemDiv = document.getElementById("cart-item-" + cartId);

                Swal.fire({
                    title: "Remove this item?",
                    text: "This item will be deleted from your cart.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Yes, remove"
                }).then(result => {
                    if (result.isConfirmed) {
                        fetch("remove_cart.php", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/x-www-form-urlencoded"
                                },
                                body: "cart_id=" + cartId
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    // ✨ Smooth fade-out delete animation
                                    itemDiv.style.transition = "opacity 0.4s ease";
                                    itemDiv.style.opacity = "0";

                                    setTimeout(() => {
                                        itemDiv.remove();
                                    }, 400);

                                    Swal.fire({
                                        icon: "success",
                                        title: "Removed!",
                                        text: "Item deleted.",
                                        timer: 1200,
                                        showConfirmButton: false
                                    });
                                }
                            });
                    }
                });
            });
        });
        document.querySelectorAll("form[action='update_cart.php']").forEach(form => {
            form.addEventListener("submit", function(e) {
                e.preventDefault();

                const formData = new FormData(this);

                // Ensure action value is captured
                const clickedButton = document.activeElement;
                if (clickedButton && clickedButton.name === "action") {
                    formData.set("action", clickedButton.value);
                }

                fetch("update_cart.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) return;

                        // update quantity input
                        this.querySelector("input[name='quantity']").value = data.quantity;

                        // update subtotal text
                        const subtotalEl = this.closest(".flex-1").querySelector(".subtotal");
                        subtotalEl.textContent = "₱" + data.subtotal;
                    });
            });
        });
    </script>



</body>

</html>