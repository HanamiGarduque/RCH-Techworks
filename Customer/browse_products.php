<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Browse Products - RCH Water</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
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

<body class="bg-gray-100 overflow-hidden">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <script src="includes/sidebar.js"></script>

        <div class="flex-1 ml-20 group-hover:ml-64 transition-all duration-300 ease-in-out">
            <div class="flex flex-col h-screen">
                <!-- Header -->
                <?php
                $pageTitle = "Browse Products";
                $pageSubtitle = "Shop for gallon containers and accessories!";
                include 'includes/header.php';
                ?>

                <div class="m-3 flex flex-col flex-1 overflow-hidden">
                    <!-- Search and Buttons -->
                    <div class="flex items-center gap-2 mb-4">
                        <div class="flex-1 relative">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            <input type="text" id="searchInput" placeholder="Search by Item, Quantity, or Price..."
                                class="w-full pl-10 pr-4 py-2 font-light border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder:text-sm"
                                style="font-style: italic;"
                                oninput="this.style.fontStyle = this.value ? 'normal' : 'italic';"
                                onkeyup="searchCards()">
                        </div>
                    </div>

                    <!-- Main Content Area -->
                    <div id="browseView"
                        class="p-6 bg-white rounded-lg shadow-lg ring-3 ring-gray-200 flex flex-col flex-1 overflow-hidden">
                        <!-- Filter Tabs -->
                        <div class="flex gap-3 mb-6 flex-wrap flex-shrink-0">
                            <button id="allTab"
                                class="px-4 py-2 rounded-full border-none cursor-pointer font-semibold transition-all duration-300 bg-blue-600 text-white"
                                onclick="filterTab(this)">
                                <i class="fas fa-th-large mr-2"></i>All
                            </button>
                            <button
                                class="px-4 py-2 rounded-full border-none cursor-pointer font-semibold transition-all duration-300 bg-blue-50 text-blue-500 hover:bg-gray-200"
                                onclick="filterTab(this)">
                                <i class="fas fa-water mr-2"></i>Gallons
                            </button>
                            <button
                                class="px-4 py-2 rounded-full border-none cursor-pointer font-semibold transition-all duration-300 bg-blue-50 text-blue-500 hover:bg-gray-200"
                                onclick="filterTab(this)">
                                <i class="fas fa-box-open mr-2"></i>Accessories
                            </button>
                            <button
                                class="px-4 py-2 rounded-full border-none cursor-pointer font-semibold transition-all duration-300 bg-blue-50 text-blue-500 hover:bg-gray-200"
                                onclick="filterTab(this)">
                                <i class="fas fa-ellipsis-h mr-2"></i>Others
                            </button>

                        </div>

                        <!-- Cards -->
                        <div class="bg-white rounded-lg shadow-md overflow-hidden flex flex-col flex-1">
                            <div class="overflow-x-auto overflow-y-auto flex-1">
                                <div id="productGrid"
                                    class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 p-4">
                                    <?php
                                    require_once '../Database/db_connection.php';
                                    $database = new Database();
                                    $db = $database->getConnect();

                                    $query = "
                                        SELECT 
                                        i.item_id,
                                        i.item_name,
                                        i.item_image,
                                        i.price,
                                        i.category,
                                        i.description,
                                        CASE 
                                            WHEN i.item_name IN ('18.9L Round Gallon', '20L Slim Gallon', '10L Slim Gallon')
                                            THEN (
                                            SELECT COUNT(*)
                                            FROM gallon_ownership go
                                            WHERE go.gallon_type = i.item_name
                                            AND go.status NOT IN ('Damaged', 'Lost')
                                            )
                                            ELSE i.total_quantity
                                        END AS quantity
                                        FROM inventory i
                                        WHERE i.item_name NOT IN ('Refill (18.9L RG)', 'Refill (20L SG)', 'Refill (10L SG)')
                                        AND i.for_sale = 1
                                    ";

                                    $stmt = $db->prepare($query);
                                    $stmt->execute();

                                    if ($stmt->rowCount() > 0) {
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            $item_id = htmlspecialchars($row['item_id']);
                                            $item_name = htmlspecialchars($row['item_name']);
                                            $quantity = (int) $row['quantity'];
                                            $price = number_format((float) $row['price'], 2);
                                            $category = htmlspecialchars($row['category']);
                                            $item_image = htmlspecialchars($row['item_image']);
                                            $description = htmlspecialchars($row['description']);


                                            if ($quantity > 20) {
                                                $status = "In Stock";
                                                $statusColor = "text-green-600 bg-green-100";
                                            } elseif ($quantity > 0) {
                                                $status = "Low Stock";
                                                $statusColor = "text-yellow-600 bg-yellow-100";
                                            } else {
                                                $status = "Out of Stock";
                                                $statusColor = "text-red-600 bg-red-100";
                                            }

                                            echo '
                                            <div class="inventory-card bg-white border border-gray-200 rounded-xl shadow-md hover:shadow-lg transition p-4 flex flex-col"
                                                data-category="' . strtolower($category) . '"
                                                data-name="' . strtolower($item_name) . '"
                                                data-price="' . $price . '"
                                                data-item-id="' . $item_id . '"
                                                
                                                data-item-name="' . htmlspecialchars($item_name, ENT_QUOTES) . '"
                                                data-item-price="' . $row['price'] . '"
                                                data-stock="' . $quantity . '">
                                            <div class="text-center m-3">
                                                <img src="../Assets/' . $item_image . '" alt="' . $item_name . '" class="w-48 h-48 object-cover rounded-lg mb-3 inline-block">
                                            </div>
                                            <h2 class="text-medium font-semibold text-gray-800 text-center mb-2">' . $item_name . '</h2>
                                            <div class="flex justify-between items-center mb-1">
                                                <p class="text-sm text-gray-600">Price: <span class="font-semibold text-blue-600">₱' . $price . '</span></p>
                                                <p class="text-xs font-semibold text-blue-600">' . $quantity . ' <span class="text-xs font-normal text-gray-600">items on stock</span></p>
                                            </div>
                                            <p class="text-xs text-gray-600 italic mb-3">' . $description . '</p>


                                            <p class="text-sm font-semibold ' . $statusColor . ' px-3 py-1 rounded-full mb-3 text-center">' . $status . '</p>

                                            <div class="flex items-center justify-between gap-2 mb-2">
                                                <p class="text-sm text-gray-600">Quantity:</p>
                                                <div class="flex items-center gap-1">
                                                <button class="qty-minus px-3 py-1 border rounded hover:bg-gray-100" data-id="' . $item_id . '">-</button>
                                                <input class="qty-input w-16 text-center border rounded p-1" data-id="' . $item_id . '" type="number" min="1" max="' . $quantity . '" value="1">
                                                <button class="qty-plus px-3 py-1 border rounded hover:bg-gray-100" data-id="' . $item_id . '">+</button>
                                                </div>
                                            </div>

                                            <div class="flex gap-2 w-full mt-2">
                                                <button class="bg-white border border-blue-600 hover:bg-blue-50 text-blue-600 px-4 py-2 rounded-md text-sm font-medium shadow flex-1 flex items-center justify-center gap-2"
                                                        onclick="addToCart(' . $item_id . ')">
                                                <i class="fas fa-shopping-cart"></i> Add
                                                </button>
                                                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium shadow flex-1 flex items-center justify-center"
                                                        onclick="buyNow(' . $item_id . ')">Buy Now</button>
                                            </div>
                                            </div>
                                        ';
                                        }
                                    } else {
                                        echo '<p class="text-gray-500 text-center col-span-full">No items found in inventory.</p>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CHECKOUT VIEW -->
                    <div id="checkoutView"
                        class="hidden p-6 bg-white rounded-lg shadow-lg ring-3 ring-gray-200 flex flex-col flex-1 overflow-auto">
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
                                    <button id="changeAddressBtn"
                                        class="text-sm text-blue-600 hover:underline">Change</button>
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
                                            <input id="addrCity" placeholder="City" class="border rounded px-2 py-2" />
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
                            <button id="cancelOrderBtn"
                                class="px-4 py-2 rounded-lg border hover:bg-gray-50">Cancel</button>
                            <button id="placeOrderBtn" class="px-4 py-2 rounded-lg btn-blue">Place Order</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        // Full page JS (safe guards & cleaned)
        document.addEventListener('DOMContentLoaded', () => {
            // State
            const state = {
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
                cart: [] // not used here but reserved
            };

            // Safe helper
            const $ = sel => document.querySelector(sel);
            const $$ = sel => Array.from(document.querySelectorAll(sel));
            const formatPHP = n => '₱' + (Number(n) || 0).toFixed(2);

            // Populate user (if endpoint exists); fail silently if not
            fetch('../Order/get_user.php').then(res => {
                if (!res.ok) throw new Error('no user endpoint');
                return res.json();
            }).then(user => {
                if (!user) return;

                state.userId = user.user_id || state.userId;
                state.userName = user.name || state.userName;
                state.userContact = user.phone_number || state.userContact;

                // --------------------------
                // FIXED ADDRESS PARSER
                // --------------------------
                const rawAddr = user.address || "";
                const parts = rawAddr.split(",").map(p => p.trim());

                let house = parts[0] || "";
                let street = parts[1] || "";
                let barangay = parts[2] || "";
                let city = parts[3] || "";

                // province + postal (last part)
                let province = "";
                let postal = "";

                if (parts[4]) {
                    const lastParts = parts[4].split(" ");
                    postal = lastParts.pop(); // last value
                    province = lastParts.join(" "); // remaining words
                }

                state.userAddress = {
                    house: house,
                    street: street,
                    barangay: barangay,
                    city: city,
                    province: province,
                    postal: postal
                };

                // --------------------------
                // UPDATE UI
                // --------------------------
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

            }).catch(() => {
                // ignore failures
            });

            // Quantity buttons: event delegation (works for dynamically generated cards)
            document.body.addEventListener('click', (e) => {
                const minus = e.target.closest('.qty-minus');
                if (minus) {
                    const id = minus.dataset.id;
                    const input = document.querySelector(`.qty-input[data-id="${id}"]`);
                    if (!input) return;
                    const min = Number(input.getAttribute('min') || 1);
                    let val = Number(input.value || min);
                    if (val > min) input.value = --val;
                }

                const plus = e.target.closest('.qty-plus');
                if (plus) {
                    const id = plus.dataset.id;
                    const input = document.querySelector(`.qty-input[data-id="${id}"]`);
                    if (!input) return;
                    const max = Number(input.getAttribute('max') || 999999);
                    let val = Number(input.value || 1);
                    if (val < max) input.value = ++val;
                }
            });

            // Search
            window.searchCards = function() {
                const term = ($('#searchInput')?.value || '').toLowerCase();
                $$('.inventory-card').forEach(card => {
                    const name = (card.dataset.name || '').toLowerCase();
                    const price = (card.dataset.price || '').toLowerCase();
                    if (name.includes(term) || price.includes(term)) card.style.display = 'flex';
                    else card.style.display = 'none';
                });
            };

            // Filter Function
            // Filter Function
            window.filterTab = function(button) {
                document.querySelectorAll('button[onclick="filterTab(this)"]').forEach(btn => {
                    btn.classList.remove('bg-blue-600', 'text-white', 'no-hover');
                    btn.classList.add('bg-blue-50', 'text-blue-500', 'hover:bg-gray-200');
                });

                // Set active tab
                button.classList.remove('bg-blue-50', 'text-blue-500', 'hover:bg-gray-200');
                button.classList.add('bg-blue-600', 'text-white', 'no-hover');

                // Get filter type - extract only the text, not the icon
                const filterText = button.textContent.trim().replace(/\s+/g, ' ');
                const cards = document.querySelectorAll('.inventory-card');

                cards.forEach(card => {
                    const category = (card.getAttribute('data-category') || '').toLowerCase();
                    let show = false;

                    if (filterText.includes("All")) {
                        show = true;
                    } else if (filterText.includes("Gallons") && category === "gallons") {
                        show = true;
                    } else if (filterText.includes("Accessories") && category === "accessories") {
                        show = true;
                    } else if (filterText.includes("Others") && category === "others") {
                        show = true;
                    }

                    card.style.display = show ? "" : "none";
                });
            };

            // Add to cart (simple toast; you can add real cart behavior)
            window.addToCart = async function(itemId) {
                const card = document.querySelector(`.inventory-card[data-item-id="${itemId}"]`);
                if (!card) return;

                const qtyInput = card.querySelector('.qty-input');
                const quantity = Number(qtyInput?.value || 1);
                const itemName = card.dataset.itemName || 'Item';
                const stock = Number(card.dataset.stock || 0);
                const unitPrice = Number(card.dataset.itemPrice || 0);
                const userId = state.userId; // Replace with actual logged-in user ID

                if (quantity > stock) {
                    return Swal.fire('Error', 'Quantity exceeds available stock', 'error');
                }

                // Show animation/toast immediately
                Swal.fire({
                    title: 'Added to Cart!',
                    text: `${quantity}x ${itemName} added to cart`,
                    icon: 'success',
                    showCloseButton: true,
                    showCancelButton: true,
                    confirmButtonText: 'View Cart',
                    cancelButtonText: 'Continue Shopping',
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '../Order/cart.php'; // or open your cart view dynamically
                    }
                });


                // Send to backend
                const payload = {
                    user_id: userId,
                    product_id: itemId,
                    quantity: quantity,
                    unit_price: unitPrice
                };

                try {
                    const res = await fetch('../Order/add_to_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });
                    const result = await res.json();

                    if (!result.success) {
                        // Optional: notify if backend failed
                        Swal.fire('Error', result.message, 'error');
                    }
                } catch (err) {
                    Swal.fire('Error', err.message, 'error');
                }
            };


            // Buy now -> show checkout & populate
            window.buyNow = function(itemId) {
                const card = document.querySelector(`.inventory-card[data-item-id="${itemId}"]`);
                if (!card) return;
                const qtyInput = card.querySelector('.qty-input');
                const quantity = Number(qtyInput?.value || 1);
                const itemName = card.dataset.itemName || 'Item';
                const price = Number(card.dataset.itemPrice || 0);
                const stock = Number(card.dataset.stock || 0);
                if (quantity > stock) return Swal.fire('Error', 'Quantity exceeds available stock', 'error');

                // Hide browse view, show checkout
                const browse = $('#browseView');
                if (browse) browse.classList.add('hidden');
                const checkout = $('#checkoutView');
                if (checkout) checkout.classList.remove('hidden');

                // Fill checkout products section
                const checkoutProducts = $('#checkoutProducts');
                if (checkoutProducts) {
                   checkoutProducts.innerHTML = `
    <div class="checkout-item flex items-center gap-3 mb-2"
         data-product-id="${itemId}"
         data-quantity="${quantity}"
         data-unit-price="${price}">

                        <div class="flex-1">
                        <div class="font-semibold">${escapeHtml(itemName)}</div>
                        <div class="text-xs text-gray-500">x ${quantity}</div>
                        </div>
                        <div class="font-semibold">${formatPHP(price * quantity)}</div>
                    </div>
                    `;
                }

                // Costs
                const subtotal = price * quantity;
                const tax = subtotal * 0.08;
                const delivery = 40;
                const total = subtotal + tax + delivery;
                const coSubtotal = $('#coSubtotal');
                if (coSubtotal) coSubtotal.textContent = formatPHP(subtotal);
                const coTax = $('#coTax');
                if (coTax) coTax.textContent = formatPHP(tax);
                const coDelivery = $('#coDelivery');
                if (coDelivery) coDelivery.textContent = formatPHP(delivery);
                const coTotal = $('#coTotal');
                if (coTotal) coTotal.textContent = formatPHP(total);

                // Pre-fill delivery edit fields with known user data
                const inName = $('#inputName');
                if (inName) inName.value = state.userName || '';
                const inPhone = $('#inputPhone');
                if (inPhone) inPhone.value = state.userContact || '';
                $('#addrHouse').value = state.userAddress.house || '';
                $('#addrStreet').value = state.userAddress.street || '';
                $('#addrBarangay').value = state.userAddress.barangay || '';
                $('#addrCity').value = state.userAddress.city || '';
                $('#addrProvince').value = state.userAddress.province || '';
                $('#addrPostal').value = state.userAddress.postal || '';
            };

            // Escape helper
            function escapeHtml(str) {
                return String(str || '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;');
            }

            // Address edit toggles
            $('#changeAddressBtn')?.addEventListener('click', () => {
                $('#deliveryDisplay')?.classList.add('hidden');
                $('#deliveryEdit')?.classList.remove('hidden');
            });

            $('#cancelAddressBtn')?.addEventListener('click', () => {
                $('#deliveryEdit')?.classList.add('hidden');
                $('#deliveryDisplay')?.classList.remove('hidden');
            });

            // Save address: compose full address from individual fields
            $('#saveAddressBtn')?.addEventListener('click', async () => {
                const house = ($('#addrHouse')?.value || '').trim();
                const street = ($('#addrStreet')?.value || '').trim();
                const barangay = ($('#addrBarangay')?.value || '').trim();
                const city = ($('#addrCity')?.value || '').trim();
                const province = ($('#addrProvince')?.value || '').trim();
                const postal = ($('#addrPostal')?.value || '').trim();

                if (!house || !street || !barangay || !city || !province || !postal) {
                    return Swal.fire('Error', 'Please fill in all fields', 'error');
                }

                // Show loading
                Swal.fire({
                    title: 'Saving...',
                    text: 'Please wait while we update your address',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Prepare payload
                const payload = {
                    user_id: state.userId,
                    address: {
                        house: house,
                        street: street,
                        barangay: barangay,
                        city: city,
                        province: province,
                        postal: postal
                    }
                };

                try {
                    const res = await fetch('update_address.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });

                    const result = await res.json();

                    if (result.success) {
                        // Update local state
                        state.userAddress = {
                            house,
                            street,
                            barangay,
                            city,
                            province,
                            postal
                        };

                        // Update UI
                        const full = `${house}, ${street}, ${barangay}, ${city}, ${province} ${postal}`;
                        $('#deliverAddress') && ($('#deliverAddress').textContent = full);

                        $('#deliveryEdit')?.classList.add('hidden');
                        $('#deliveryDisplay')?.classList.remove('hidden');

                        // Success message
                        Swal.fire({
                            title: 'Success!',
                            text: 'Your delivery address has been updated',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        throw new Error(result.message || 'Failed to update address');
                    }
                } catch (err) {
                    Swal.fire('Error', err.message || 'Failed to update address', 'error');
                }
            });
            // Cancel checkout -> back to browse
            $('#cancelOrderBtn')?.addEventListener('click', () => {
                $('#checkoutView')?.classList.add('hidden');
                $('#browseView')?.classList.remove('hidden');
            });

            // Place order 
            $('#placeOrderBtn')?.addEventListener('click', async () => {
    // Collect all items currently in checkout
    const checkoutProducts = Array.from($('#checkoutProducts').children);
    const cart = checkoutProducts.map((div) => {
        const productId = Number(div.dataset.productId); // set data-product-id on each checkout item
        const quantity = Number(div.dataset.quantity || 1);
        const unitPrice = Number(div.dataset.unitPrice || 0);
        return { product_id: productId, quantity, unit_price: unitPrice };
    });

    if (cart.length === 0) {
        Swal.fire('Error', 'Your cart is empty!', 'error');
        return;
    }

    const total_amount = Number($('#coTotal').textContent.replace('₱', '')) || 0;
    const orderNumber = 'ORD' + Date.now() + state.userId;

    const payload = {
        user_id: state.userId || 4,
        delivery_datetime: new Date().toISOString().slice(0, 19).replace('T', ' '),
        cart: cart,
        total_amount: total_amount,
        order_number: orderNumber
    };

    try {
        const res = await fetch('../Order/order_backend.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const result = await res.json();
        if (result.success) {
            Swal.fire({
                title: 'Order Placed!',
                text: 'Thank you for your order! Please have your payment ready upon delivery.',
                icon: 'success'
            }).then(() => {
                $('#checkoutView')?.classList.add('hidden');
                $('#browseView')?.classList.remove('hidden');
                $('#checkoutProducts').innerHTML = '';
                $('#coSubtotal').textContent = '₱0.00';
                $('#coTax').textContent = '₱0.00';
                $('#coDelivery').textContent = '₱40.00';
                $('#coTotal').textContent = '₱0.00';
            });
        } else {
            Swal.fire('Error', result.message, 'error');
        }
    } catch (err) {
        Swal.fire('Error', err.message, 'error');
    }
});


            // Convenience: if user presses Enter in search, run search
            $('#searchInput')?.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') searchCards();
            });

            // Initialize: ensure browse is visible
            $('#browseView')?.classList.remove('hidden');
            $('#checkoutView')?.classList.add('hidden');
        });
    </script>
</body>

</html>