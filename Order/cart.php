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
                        class="p-3 bg-white rounded-lg shadow-lg ring-3 ring-gray-200 flex flex-col flex-1 overflow-hidden relative">

                        <!-- Scrollable Cards Container -->
                        <div class="space-y-4 overflow-auto pb-28 flex-1">

                            <?php
                            $userId = 4; // replace with session user_id
                            require_once '../Database/db_connection.php';

                            $database = new Database();
                            $db = $database->getConnect();

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

                            $query->execute([':uid' => $userId]);
                            $cartItems = $query->fetchAll(PDO::FETCH_ASSOC);
                            ?>

                            <!-- CART HEADER -->
                            <div class="w-full bg-gray-100 rounded-lg shadow border p-4 
            flex items-center justify-between gap-4 font-semibold text-gray-700 text-sm">
                                <div class="w-5"></div>
                                <div class="w-1/3">Product</div>
                                <div class="w-24 text-center">Unit Price</div>
                                <div class="w-32 text-center">Quantity</div>
                                <div class="w-24 text-center">Total Price</div>
                                <div class="w-6"></div>
                            </div>

                            <?php foreach ($cartItems as $item): ?>

                                <div class="w-full bg-white rounded-lg shadow border p-4 flex items-center justify-between gap-4"
                                    id="cart-item-<?= htmlspecialchars($item['cart_id']) ?>"
                                    data-subtotal="<?= htmlspecialchars($item['subtotal']) ?>">

                                    <!-- CHECKBOX -->
                                    <div class="flex items-center">
                                        <input type="checkbox"
                                            class="w-5 h-5 accent-blue-600 cursor-pointer selectItem"
                                            data-id="<?= htmlspecialchars($item['cart_id']) ?>"
                                            data-product-id="<?= htmlspecialchars($item['product_id']) ?>"
                                            data-subtotal="<?= htmlspecialchars($item['subtotal']) ?>">


                                    </div>

                                    <!-- PRODUCT IMAGE + NAME -->
                                    <div class="flex items-center gap-4 w-1/3">
                                        <img src="../Assets/<?= htmlspecialchars($item['item_image']) ?>"
                                            alt="<?= htmlspecialchars($item['product_name']) ?>"
                                            class="w-12 h-12 rounded object-cover ring-4 ring-blue-500">
                                        <span class="font-medium text-gray-700 text-sm">
                                            <?= htmlspecialchars($item['product_name']) ?>
                                        </span>
                                    </div>

                                    <!-- UNIT PRICE -->
                                    <div class="text-gray-700 font-medium w-24 text-center">
                                        ₱<?= number_format($item['unit_price'], 2) ?>
                                    </div>

                                    <!-- QUANTITY CONTROLS -->
                                    <form action="update_cart.php"
                                        method="POST"
                                        class="flex items-center gap-2 w-32 justify-center update-cart-form">

                                        <input type="hidden" name="cart_id" value="<?= htmlspecialchars($item['cart_id']) ?>">

                                        <button type="submit" name="action" value="decrement"
                                            class="w-7 h-7 flex items-center justify-center border rounded hover:bg-gray-100">
                                            <i class="fa-solid fa-minus text-xs"></i>
                                        </button>

                                        <input type="number" min="1"
                                            name="quantity"
                                            value="<?= htmlspecialchars($item['quantity']) ?>"
                                            class="w-12 text-center border rounded text-sm">

                                        <button type="submit" name="action" value="increment"
                                            class="w-7 h-7 flex items-center justify-center border rounded hover:bg-gray-100">
                                            <i class="fa-solid fa-plus text-xs"></i>
                                        </button>
                                    </form>

                                    <!-- TOTAL PRICE -->
                                    <div class="text-blue-600 font-semibold w-24 text-center item-subtotal">
                                        ₱<?= number_format($item['subtotal'], 2) ?>
                                    </div>

                                    <!-- DELETE BUTTON -->
                                    <button type="button"
                                        class="text-red-500 hover:text-red-700 deleteItemBtn"
                                        data-id="<?= htmlspecialchars($item['cart_id']) ?>">
                                        <i class="fa-solid fa-trash text-lg"></i>
                                    </button>

                                </div>

                            <?php endforeach; ?>

                        </div>

                        <!-- Checkout Summary Container -->
                        <div class="absolute bottom-0 left-0 right-0 bg-white rounded-lg shadow-lg border-2 border-gray-200 p-4 mx-3 mb-3">
                            <div class="flex items-center justify-between gap-4">

                                <!-- Select All Checkbox -->
                                <div class="flex items-center gap-3">
                                    <input type="checkbox"
                                        id="selectAllCheckbox"
                                        class="w-5 h-5 accent-blue-600 cursor-pointer">
                                    <label for="selectAllCheckbox" class="text-sm font-medium text-gray-700 cursor-pointer">
                                        Select All (<span id="totalItems">0</span>)
                                    </label>
                                </div>

                                <!-- Delete Selected Button -->
                                <button id="deleteSelectedBtn"
                                    class="text-sm text-red-500 hover:text-red-700 font-medium transition-colors">
                                    Delete
                                </button>

                                <!-- Spacer -->
                                <div class="flex-1"></div>

                                <!-- Subtotal Display -->
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-gray-600">Subtotal:</span>
                                    <span id="checkoutSubtotal" class="text-2xl font-bold text-blue-600">₱0.00</span>
                                </div>

                                <!-- Check Out Button -->
                                <button id="checkOutBtn"
                                    class="px-8 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-md">
                                    Check Out
                                </button>
                            </div>
                        </div>

                    </div> <!-- end browseView -->


                    <!-- SINGLE CHECKOUT VIEW (cleaned, no duplicates) -->
                    <div id="checkoutView"
                        class="hidden p-6 bg-white rounded-lg shadow-lg ring-3 ring-gray-200 flex flex-col flex-1 overflow-auto">

                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-2xl font-semibold">Checkout</h3>
                            <button id="backToCartBtn" class="text-blue-600 hover:underline text-sm">← Back to Cart</button>
                        </div>

                        <!-- Delivery Address Block -->
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
                                <button id="changeAddressBtn" class="text-sm text-blue-600 hover:underline">Change</button>
                            </div>

                            <div id="deliveryEdit" class="hidden mt-4 flex gap-3">
                                <!-- Form section -->
                                <div class="flex-1 flex flex-col space-y-2">
                                    <div id="addressForm" class="flex-1">
                                        <div class="grid grid-cols-1 gap-2">
                                            <input id="addrHouse" placeholder="House/Unit No." class="border rounded px-2 py-2" />
                                            <input id="addrStreet" placeholder="Street Name" class="border rounded px-2 py-2" />
                                            <input id="addrBarangay" placeholder="Barangay" class="border rounded px-2 py-2" />
                                            <input id="addrCity" placeholder="City" class="border rounded px-2 py-2" />
                                            <input id="addrProvince" placeholder="Province" class="border rounded px-2 py-2" />
                                            <input id="addrPostal" placeholder="Postal Code" class="border rounded px-2 py-2" />
                                        </div>
                                        <div class="flex space-x-3 mt-3">
                                            <button id="saveAddressBtn" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save</button>
                                            <button id="cancelAddressBtn" class="bg-white border px-4 py-2 rounded hover:bg-blue-600 hover:text-white">Cancel</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Map section (placeholder) -->
                                <div id="mapContainer" class="flex-1 border rounded-lg bg-blue-100" style="height: 300px;">
                                    <!-- Map placeholder -->
                                </div>
                            </div>

                        </div>

                        <!-- Selected Items -->
                        <div id="checkoutItemsList" class="space-y-3 mb-4"></div>

                        <!-- Costs -->
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
                                <div id="checkoutTotalDisplay">₱0.00</div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3">
                            <button id="cancelOrderBtn" class="px-4 py-2 rounded-lg border hover:bg-gray-50">Cancel</button>
                            <button id="placeOrderBtn" class="px-4 py-2 rounded-lg btn-blue">Place Order</button>
                        </div>

                    </div> <!-- end checkoutView -->

                </div> <!-- container inner -->
            </div>
        </div>
    </div>

    <script>
        /* ---------------------------
           Helpers & Initial State
           --------------------------- */
        const $ = (sel) => document.querySelector(sel);
        const $$ = (sel) => Array.from(document.querySelectorAll(sel));
        const formatPHP = n => '₱' + (Number(n) || 0).toFixed(2);

        const DELIVERY_FEE = 40;
        const TAX_RATE = 0.08;

        // App state for user (populated from server)
        const state = {
            userId: null,
            userName: '',
            userContact: '',
            userAddress: {
                house: '',
                street: '',
                barangay: '',
                city: '',
                province: '',
                postal: ''
            },
            // selectedCartItems filled when check out clicked
            cartSelection: []
        };

        /* ---------------------------
           Load logged user details (GET)
           Endpoint: ../Order/get_users.php  (user provided)
           --------------------------- */
        (function loadUser() {
            fetch('../Order/get_user.php')
                .then(res => {
                    if (!res.ok) throw new Error('no user endpoint');
                    return res.json();
                })
                .then(user => {
                    if (!user) return;

                    state.userId = user.user_id || state.userId;
                    state.userName = user.name || state.userName;
                    state.userContact = user.phone_number || state.userContact;

                    const rawAddr = user.address || "";
                    const parts = rawAddr.split(",").map(p => p.trim());

                    let house = parts[0] || "";
                    let street = parts[1] || "";
                    let barangay = parts[2] || "";
                    let city = parts[3] || "";

                    let province = "";
                    let postal = "";

                    if (parts[4]) {
                        const lastParts = parts[4].split(" ");
                        postal = lastParts.pop(); // last value
                        province = lastParts.join(" "); // remaining words
                    }

                    state.userAddress = {
                        house,
                        street,
                        barangay,
                        city,
                        province,
                        postal
                    };

                    // Update UI
                    const dn = $('#deliverName');
                    if (dn) dn.textContent = state.userName || 'User Name';

                    const dp = $('#deliverPhone');
                    if (dp) dp.textContent = state.userContact || 'Phone';

                    const da = $('#deliverAddress');
                    if (da) {
                        const full =
                            `${state.userAddress.house ? state.userAddress.house + ", " : ""}` +
                            `${state.userAddress.street ? state.userAddress.street + ", " : ""}` +
                            `${state.userAddress.barangay ? state.userAddress.barangay + ", " : ""}` +
                            `${state.userAddress.city ? state.userAddress.city + ", " : ""}` +
                            `${state.userAddress.province ? state.userAddress.province + " " : ""}` +
                            `${state.userAddress.postal || ""}`;

                        da.textContent = full || 'Address';
                    }

                    // Prefill delivery edit inputs if present
                    $('#addrHouse') && ($('#addrHouse').value = state.userAddress.house || '');
                    $('#addrStreet') && ($('#addrStreet').value = state.userAddress.street || '');
                    $('#addrBarangay') && ($('#addrBarangay').value = state.userAddress.barangay || '');
                    $('#addrCity') && ($('#addrCity').value = state.userAddress.city || '');
                    $('#addrProvince') && ($('#addrProvince').value = state.userAddress.province || '');
                    $('#addrPostal') && ($('#addrPostal').value = state.userAddress.postal || '');
                })
                .catch(() => {
                    // ignore failures (UI stays default)
                });
        })();

        /* ---------------------------
           Utility: compute totals from selection
           --------------------------- */
        function computeTotalsFromSelectedCheckboxes(selectedCheckboxes) {
            let subtotal = 0;
            selectedCheckboxes.forEach(cb => {
                subtotal += parseFloat(cb.dataset.subtotal || 0);
            });
            const tax = subtotal * TAX_RATE;
            const total = subtotal + tax + DELIVERY_FEE;
            return {
                subtotal,
                tax,
                delivery: DELIVERY_FEE,
                total
            };
        }

        /* ---------------------------
           Summary / Select All logic
           --------------------------- */
        function updateCheckoutSummary() {
            const selectedCheckboxes = document.querySelectorAll(".selectItem:checked");
            const totals = computeTotalsFromSelectedCheckboxes(selectedCheckboxes);

            // small summary in browse view
            const checkoutSubtotalEl = $('#checkoutSubtotal');
            if (checkoutSubtotalEl) checkoutSubtotalEl.textContent = formatPHP(totals.subtotal);

            // item count
            $('#totalItems') && ($('#totalItems').textContent = selectedCheckboxes.length);

            // no need to update co* here unless checkout view visible
            if (!$('#checkoutView').classList.contains('hidden')) {
                // if checkout is visible, update detailed costs
                $('#coSubtotal') && ($('#coSubtotal').textContent = formatPHP(totals.subtotal));
                $('#coTax') && ($('#coTax').textContent = formatPHP(totals.tax));
                $('#coDelivery') && ($('#coDelivery').textContent = formatPHP(totals.delivery));
                $('#checkoutTotalDisplay') && ($('#checkoutTotalDisplay').textContent = formatPHP(totals.total));
            }
        }

        // Select All functionality
        $('#selectAllCheckbox') && $('#selectAllCheckbox').addEventListener("change", function() {
            const checkboxes = document.querySelectorAll(".selectItem");
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateCheckoutSummary();
        });

        // Individual checkbox change (attach to existing checkboxes)
        $$(".selectItem").forEach(checkbox => {
            checkbox.addEventListener("change", function() {
                const allCheckboxes = document.querySelectorAll(".selectItem");
                const checkedBoxes = document.querySelectorAll(".selectItem:checked");
                $('#selectAllCheckbox') && ($('#selectAllCheckbox').checked = allCheckboxes.length === checkedBoxes.length);
                updateCheckoutSummary();
            });
        });

        /* ---------------------------
           Delete Selected Items
           --------------------------- */
        const deleteSelectedBtn = $('#deleteSelectedBtn');
        if (deleteSelectedBtn) {
            deleteSelectedBtn.addEventListener("click", function() {
                const selectedCheckboxes = document.querySelectorAll(".selectItem:checked");

                if (selectedCheckboxes.length === 0) {
                    Swal.fire({
                        icon: "info",
                        title: "No items selected",
                        text: "Please select items to delete.",
                        timer: 1500,
                        showConfirmButton: false
                    });
                    return;
                }

                Swal.fire({
                    title: "Remove selected items?",
                    text: `${selectedCheckboxes.length} item(s) will be deleted from your cart.`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Yes, remove"
                }).then(result => {
                    if (result.isConfirmed) {
                        let deletePromises = [];

                        selectedCheckboxes.forEach(checkbox => {
                            const cartId = checkbox.dataset.id;
                            const itemDiv = document.getElementById("cart-item-" + cartId);

                            const promise = fetch("remove_cart.php", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/x-www-form-urlencoded"
                                },
                                body: "cart_id=" + cartId
                            }).then(res => res.json()).then(data => {
                                if (data.success) {
                                    itemDiv.style.transition = "opacity 0.4s ease";
                                    itemDiv.style.opacity = "0";
                                    setTimeout(() => itemDiv.remove(), 400);
                                }
                            });

                            deletePromises.push(promise);
                        });

                        Promise.all(deletePromises).then(() => {
                            Swal.fire({
                                icon: "success",
                                title: "Removed!",
                                text: "Items deleted.",
                                timer: 1200,
                                showConfirmButton: false
                            });

                            setTimeout(() => {
                                updateCheckoutSummary();
                                $('#selectAllCheckbox') && ($('#selectAllCheckbox').checked = false);
                            }, 500);
                        });
                    }
                });
            });
        }

        /* ---------------------------
           Individual Delete Button
           --------------------------- */
        $$(".deleteItemBtn").forEach(btn => {
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
                                    itemDiv.style.transition = "opacity 0.4s ease";
                                    itemDiv.style.opacity = "0";

                                    setTimeout(() => {
                                        itemDiv.remove();
                                        updateCheckoutSummary();
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

        /* ---------------------------
           Update Cart Quantity (increment/decrement)
           - existing update_cart.php should return JSON { success: true, quantity, subtotal }
           - when quantity changes, update the row subtotal and checkbox data-subtotal
           --------------------------- */
        $$(".update-cart-form").forEach(form => {
            form.addEventListener("submit", function(e) {
                e.preventDefault();

                const formData = new FormData(this);
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

                        // Update quantity input
                        this.querySelector("input[name='quantity']").value = data.quantity;

                        // Update subtotal text
                        const itemRow = this.closest("[id^='cart-item-']");
                        const subtotalEl = itemRow.querySelector(".item-subtotal");
                        subtotalEl.textContent = "₱" + parseFloat(data.subtotal).toFixed(2);

                        // Update data attributes
                        itemRow.dataset.subtotal = data.subtotal;
                        const checkbox = itemRow.querySelector(".selectItem");
                        if (checkbox) checkbox.dataset.subtotal = data.subtotal;

                        // Update summary if this item is selected
                        if (checkbox && checkbox.checked) {
                            updateCheckoutSummary();
                        }
                    });
            });
        });

        /* ---------------------------
           Check Out Button: Collect selected items, populate checkout view and compute totals (Option A)
           --------------------------- */
        $('#checkOutBtn') && $('#checkOutBtn').addEventListener("click", function() {
            const selectedCheckboxes = document.querySelectorAll(".selectItem:checked");

            if (selectedCheckboxes.length === 0) {
                Swal.fire({
                    icon: "info",
                    title: "No items selected",
                    text: "Please select items to checkout.",
                    timer: 1500,
                    showConfirmButton: false
                });
                return;
            }

            // Build selection
            const selectedItems = [];
            let subtotal = 0;
            selectedCheckboxes.forEach(checkbox => {
                const cartItem = checkbox.closest("[id^='cart-item-']");
                const productName = cartItem.querySelector(".font-medium.text-gray-700").textContent.trim();
                const quantity = Number(cartItem.querySelector("input[name='quantity']").value);
                const itemSubtotal = parseFloat(checkbox.dataset.subtotal || 0);

                selectedItems.push({
                    id: checkbox.dataset.id,
                    name: productName,
                    quantity: quantity,
                    subtotal: itemSubtotal
                });

                subtotal += itemSubtotal;
            });

            // Populate checkout items list
            const checkoutList = document.getElementById("checkoutItemsList");
            checkoutList.innerHTML = selectedItems.map(item => `
                <div class="p-3 bg-gray-50 rounded border">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="font-medium">${item.name}</p>
                            <p class="text-sm text-gray-600">Quantity: ${item.quantity}</p>
                        </div>
                        <p class="font-semibold text-blue-600">₱${item.subtotal.toFixed(2)}</p>
                    </div>
                </div>
            `).join('');

            // Compute tax, delivery, total (Option A)
            const tax = subtotal * TAX_RATE;
            const total = subtotal + tax + DELIVERY_FEE;

            // Update visible totals
            $('#coSubtotal') && ($('#coSubtotal').textContent = formatPHP(subtotal));
            $('#coTax') && ($('#coTax').textContent = formatPHP(tax));
            $('#coDelivery') && ($('#coDelivery').textContent = formatPHP(DELIVERY_FEE));
            $('#checkoutTotalDisplay') && ($('#checkoutTotalDisplay').textContent = formatPHP(total));

            // Also update the small browse subtotal to match selection
            $('#checkoutSubtotal') && ($('#checkoutSubtotal').textContent = formatPHP(subtotal));

            // Save selection to state (useful for placing order)
            state.cartSelection = Array.from(
                document.querySelectorAll('.selectItem:checked')
            ).map(cb => ({
                id: cb.dataset.id,
                productId: cb.dataset.productId, // <-- you were missing this
                quantity: cb.closest(".w-full").querySelector("input[name='quantity']").value
            }));

            // Switch views
            $('#browseView') && $('#browseView').classList.add("hidden");
            $('#checkoutView') && $('#checkoutView').classList.remove("hidden");
        });

        // Back to Cart Button
        $('#backToCartBtn') && $('#backToCartBtn').addEventListener("click", function() {
            $('#checkoutView') && $('#checkoutView').classList.add("hidden");
            $('#browseView') && $('#browseView').classList.remove("hidden");
        });

        // Cancel Order (just return to browse)
        $('#cancelOrderBtn') && $('#cancelOrderBtn').addEventListener("click", function() {
            $('#checkoutView') && $('#checkoutView').classList.add("hidden");
            $('#browseView') && $('#browseView').classList.remove("hidden");
        });

        /* ---------------------------
           Place Order Button
           - This makes a POST to ../Order/order_backend.php with a payload.
           - Ensure your backend expects the structure used here or adjust accordingly.
           --------------------------- */
        $('#placeOrderBtn') && $('#placeOrderBtn').addEventListener("click", async function() {
            if (!state.cartSelection || state.cartSelection.length === 0) {
                return Swal.fire('Error', 'No items to place order.', 'error');
            }

            // Prepare cart payload
            const cartPayload = state.cartSelection.map(item => ({
                cart_id: Number(item.id),
                product_id: Number(item.productId),
                quantity: Number(item.quantity),
                unit_price: Number(item.price) || 0, // ADD THIS
                total_price: Number(item.price) * Number(item.quantity) || 0
            }));



            const subtotal = Number($('#coSubtotal').textContent.replace('₱', '')) || 0;
            const tax = Number($('#coTax').textContent.replace('₱', '')) || 0;
            const delivery = Number($('#coDelivery').textContent.replace('₱', '')) || DELIVERY_FEE;
            const total_amount = subtotal + tax + delivery;
            const orderNumber = 'ORD' + Date.now() + (state.userId || '');

            const payload = {
                user_id: state.userId || 1, // fallback
                delivery_datetime: new Date().toISOString().slice(0, 19).replace('T', ' '),
                cart: cartPayload,
                total_amount: total_amount,
                order_number: orderNumber,
                address: {
                    house: state.userAddress.house,
                    street: state.userAddress.street,
                    barangay: state.userAddress.barangay,
                    city: state.userAddress.city,
                    province: state.userAddress.province,
                    postal: state.userAddress.postal
                }
            };

            try {
                Swal.fire({
                    title: 'Placing order...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                const res = await fetch('../Order/order_backend.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const result = await res.json();

                Swal.close();

                if (result.success) {
                    // Remove items from cart table
                    state.cartSelection.forEach(item => {
                        fetch('remove_cart.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: `cart_id=${item.id}`
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    const itemDiv = document.getElementById("cart-item-" + item.id);
                                    if (itemDiv) itemDiv.remove();
                                }
                            });
                    });

                    Swal.fire({
                        title: 'Order Placed!',
                        text: 'Thank you for your order! Please have your payment ready upon delivery.',
                        icon: 'success'
                    }).then(() => {
                        // Reset checkout view totals
                        $('#checkoutView') && $('#checkoutView').classList.add("hidden");
                        $('#browseView') && $('#browseView').classList.remove("hidden");
                        $('#checkoutItemsList') && ($('#checkoutItemsList').innerHTML = '');
                        $('#coSubtotal') && ($('#coSubtotal').textContent = '₱0.00');
                        $('#coTax') && ($('#coTax').textContent = '₱0.00');
                        $('#coDelivery') && ($('#coDelivery').textContent = formatPHP(DELIVERY_FEE));
                        $('#checkoutTotalDisplay') && ($('#checkoutTotalDisplay').textContent = '₱0.00');
                        $('#selectAllCheckbox') && ($('#selectAllCheckbox').checked = false);

                        // Clear selection
                        state.cartSelection = [];
                        updateCheckoutSummary();
                    });
                } else {
                    Swal.fire('Error', result.message || 'Failed to place order', 'error');
                }
            } catch (err) {
                Swal.close();
                Swal.fire('Error', err.message || 'Failed to place order', 'error');
            }
        });

        /* ---------------------------
           Address Edit (show/hide + save)
           - Save will optimistically update UI and attempt to POST to update_address.php if available.
           --------------------------- */
        $('#changeAddressBtn') && $('#changeAddressBtn').addEventListener('click', () => {
            $('#deliveryDisplay') && $('#deliveryDisplay').classList.add('hidden');
            $('#deliveryEdit') && $('#deliveryEdit').classList.remove('hidden');
        });

        $('#cancelAddressBtn') && $('#cancelAddressBtn').addEventListener('click', () => {
            $('#deliveryEdit') && $('#deliveryEdit').classList.add('hidden');
            $('#deliveryDisplay') && $('#deliveryDisplay').classList.remove('hidden');
        });

        $('#saveAddressBtn') && $('#saveAddressBtn').addEventListener('click', async () => {
            const house = ($('#addrHouse')?.value || '').trim();
            const street = ($('#addrStreet')?.value || '').trim();
            const barangay = ($('#addrBarangay')?.value || '').trim();
            const city = ($('#addrCity')?.value || '').trim();
            const province = ($('#addrProvince')?.value || '').trim();
            const postal = ($('#addrPostal')?.value || '').trim();

            if (!house || !street || !barangay || !city || !province || !postal) {
                return Swal.fire('Error', 'Please fill in all fields', 'error');
            }

            // Update local state and UI immediately
            state.userAddress = {
                house,
                street,
                barangay,
                city,
                province,
                postal
            };
            const full = `${house}, ${street}, ${barangay}, ${city}, ${province} ${postal}`;
            $('#deliverAddress') && ($('#deliverAddress').textContent = full);

            // hide edit
            $('#deliveryEdit') && $('#deliveryEdit').classList.add('hidden');
            $('#deliveryDisplay') && $('#deliveryDisplay').classList.remove('hidden');

            // Try to persist server-side (optional endpoint)
            try {
                const res = await fetch('../Order/update_address.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        user_id: state.userId,
                        address: full,
                        phone_number: state.userContact
                    })
                });
                const json = await res.json();
                if (!json.success) {
                    // it's okay if backend isn't present—just inform
                    console.warn('Address save response:', json);
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: 'Address saved',
                        timer: 1200,
                        showConfirmButton: false
                    });
                }
            } catch (err) {
                // backend not present or failed: ignore but inform user it's saved locally
                console.warn('Address save failed (server may be missing).', err);
                Swal.fire({
                    icon: 'success',
                    title: 'Address updated locally',
                    timer: 1200,
                    showConfirmButton: false
                });
            }
        });

        /* ---------------------------
           Initialize small summary on load
           --------------------------- */
        updateCheckoutSummary();
    </script>

</body>

</html>