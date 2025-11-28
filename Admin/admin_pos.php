<?php
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('admin'); // only admins can access

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Point of Sale - RCH Water</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Add html2canvas library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
        }

        body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown) {
            overflow: hidden !important;
            padding-right: 0 !important;
        }

        /* Prevent height changes */
        body.swal2-height-auto {
            height: auto !important;
        }

        /* Use scrollbar-gutter to reserve space without showing scrollbar */
        body {
            scrollbar-gutter: stable;
        }


        .product-card {
            transition: all 0.2s ease;
        }

        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .cart-item {
            transition: background-color 0.2s;
        }

        .cart-item:hover {
            background-color: #f3f4f6;
        }

        .receipt-print {
            display: none;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            .receipt-print,
            .receipt-print * {
                visibility: visible;
            }

            .receipt-print {
                position: absolute;
                left: 0;
                top: 0;
                width: 80mm;
            }
        }
    </style>
</head>

<body class="bg-gray-100">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <script src="includes/sidebar.js"></script>

        <!-- Main Content -->
        <div class="flex-1 ml-20 flex flex-col">
            <!-- Header -->
            <div class="bg-white border-b border-gray-100 shadow-sm p-4 transition-all duration-300 ease-in-out">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-semibold text-blue-500">Point of Sale</h1>
                        <p class="text-sm text-gray-700 mt-1">Process walk-in customer transactions</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Current Date</p>
                            <p id="currentDate" class="font-semibold text-gray-800"></p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Admin</p>
                            <p id="adminName" class="font-semibold text-gray-800">Loading...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- POS Content -->
            <div class="flex flex-1 overflow-hidden">
                <!-- Products Section (Left) -->
                <div class="flex-1 p-4 overflow-hidden flex flex-col">
                    <!-- Search & Category Filter -->
                    <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
                        <div class="flex gap-3 mb-3">
                            <div class="flex-1 relative">
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                                <input type="text" id="productSearch"
                                    placeholder="Search products..."
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <div class="flex gap-3 flex-wrap">

                            <button id="allTab"
                                class="px-4 py-2 rounded-full border-none cursor-pointer font-semibold transition-all duration-300 bg-blue-600 text-white"
                                data-category="all">
                                <i class="fas fa-th mr-2"></i> All Products
                            </button>

                            <button
                                class="px-4 py-2 rounded-full border-none cursor-pointer font-semibold transition-all duration-300 bg-blue-50 text-blue-500 hover:bg-gray-200"
                                data-category="services">
                                <i class="fas fa-concierge-bell mr-2"></i> Services
                            </button>

                            <button
                                class="px-4 py-2 rounded-full border-none cursor-pointer font-semibold transition-all duration-300 bg-blue-50 text-blue-500 hover:bg-gray-200"
                                data-category="gallons">
                                <i class="fas fa-water mr-2"></i> Gallons
                            </button>

                            <button
                                class="px-4 py-2 rounded-full border-none cursor-pointer font-semibold transition-all duration-300 bg-blue-50 text-blue-500 hover:bg-gray-200"
                                data-category="accessories">
                                <i class="fas fa-cube mr-2"></i> Accessories
                            </button>

                            <button
                                class="px-4 py-2 rounded-full border-none cursor-pointer font-semibold transition-all duration-300 bg-blue-50 text-blue-500 hover:bg-gray-200"
                                data-category="others">
                                <i class="fas fa-ellipsis-h mr-2"></i> Others
                            </button>

                        </div>


                    </div>

                    <!-- Products Grid -->
                    <div class="bg-white rounded-lg shadow-sm flex-1 overflow-hidden">
                        <div id="productsGrid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 p-4 overflow-y-auto h-full">
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
                                WHERE i.for_sale = 1
                                ORDER BY 
                                    CASE WHEN i.category = 'services' THEN 0 ELSE 1 END,
                                    i.item_name ASC
                            ";

                            $stmt = $db->prepare($query);
                            $stmt->execute();

                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $item_id = htmlspecialchars($row['item_id']);
                                $item_name = htmlspecialchars($row['item_name']);
                                $quantity = (int)$row['quantity'];
                                $price = number_format((float)$row['price'], 2);
                                $category = htmlspecialchars($row['category']);
                                $item_image = htmlspecialchars($row['item_image']);

                                $isService = ($category === 'services');
                                $classes = (!$isService && $quantity <= 0) ?
                                    'opacity-50 cursor-not-allowed' :
                                    'cursor-pointer';

                                echo "
                                <div 
                                    id='card-{$item_id}'
                                    class='product-card bg-white border rounded-lg p-3 {$classes}'
                                    data-id='{$item_id}'
                                    data-name=\"{$item_name}\"
                                    data-price='{$row['price']}'
                                    data-stock='{$quantity}'
                                    data-category='{$category}'
                                    onclick=\"addToCart({$item_id}, '{$category}')\"
                                >
                                    <div class='aspect-square mb-2 flex items-center justify-center'>
                                        <img src='../Assets/{$item_image}' alt='{$item_name}' class='w-full h-full object-cover rounded'>
                                    </div>
                                    <h3 class='font-semibold text-sm text-gray-800 mb-1 truncate'>{$item_name}</h3>
                                    <p class='text-blue-600 font-bold text-lg'>₱{$price}</p>
                                    <p class='text-xs text-gray-500'>Stock: {$quantity}</p>
                                </div>";
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Cart Section (Right) -->
                <div class="w-96 bg-white border-l flex flex-col">
                    <!-- Cart Header -->
                    <div class="p-4 border-b">
                        <h2 class="text-xl font-bold text-gray-800">Current Sale</h2>
                        <p class="text-sm text-gray-500">Transaction #<span id="transactionNum">000000</span></p>
                    </div>

                    <!-- Customer Info -->
                    <div class="p-4 border-b bg-gray-50">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Customer Name (Optional)</label>
                        <input type="text" id="customerName"
                            placeholder="Walk-in Customer"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Cart Items -->
                    <div class="flex-1 overflow-y-auto p-4">
                        <div id="cartItems" class="space-y-2">
                            <div class="text-center text-gray-400 py-8">
                                <i class="fas fa-shopping-cart text-4xl mb-2"></i>
                                <p>No items in cart</p>
                            </div>
                        </div>
                    </div>

                    <!-- Cart Summary -->
                    <div class="border-t p-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span>Subtotal:</span>
                            <span id="subtotal" class="font-semibold">₱0.00</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span>Tax (8%):</span>
                            <span id="tax" class="font-semibold">₱0.00</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold pt-2 border-t">
                            <span>Total:</span>
                            <span id="total" class="text-blue-600">₱0.00</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="p-4 border-t space-y-2">
                        <button onclick="processPayment()"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition flex items-center justify-center gap-2">
                            <i class="fas fa-credit-card"></i>
                            Process Payment
                        </button>
                        <button onclick="clearCart()"
                            class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 rounded-lg transition">
                            Clear Cart
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="..Admin/includes/admin_toast.js"></script>


    <!-- Receipt Template (Hidden, for printing) -->
    <div class="receipt-print" id="receiptPrint"></div>

    <script>
        // State Management
        const state = {
            cart: [],
            adminId: null,
            adminName: '',
            transactionNumber: generateTransactionNumber()
        };

        let inventoryUpdateInterval;

        // Initialize
        document.addEventListener('DOMContentLoaded', async () => {
            updateDateTime();
            setInterval(updateDateTime, 60000);

            // Load admin info
            try {
                const res = await fetch('../Order/get_user.php');
                const user = await res.json();
                if (user) {
                    state.adminId = user.user_id;
                    state.adminName = user.name || 'Admin';
                    document.getElementById('adminName').textContent = state.adminName;
                }
            } catch (err) {
                console.error('Failed to load admin info:', err);
            }

            document.getElementById('transactionNum').textContent = state.transactionNumber;
            setupEventListeners();
            startInventoryPolling();
        });

        function updateDateTime() {
            const now = new Date();
            const options = {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            document.getElementById('currentDate').textContent = now.toLocaleDateString('en-US', options);
        }

        function generateTransactionNumber() {
            return 'POS' + Date.now();
        }

        function setupEventListeners() {
            const searchInput = document.getElementById('productSearch');
            searchInput.addEventListener('input', filterProducts);

            const categoryButtons = document.querySelectorAll('.flex.gap-3 button');

            categoryButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    // Remove active styles from all buttons
                    categoryButtons.forEach(b => {
                        b.classList.remove('bg-blue-600', 'text-white');
                        b.classList.add('bg-blue-50', 'text-blue-500');
                    });

                    // Add active styles to clicked button
                    this.classList.remove('bg-blue-50', 'text-blue-500');
                    this.classList.add('bg-blue-600', 'text-white');

                    filterProducts();
                });
            });
        }

        function filterProducts() {
            const searchTerm = document.getElementById('productSearch').value.toLowerCase();
            const activeCategoryBtn = document.querySelector('.flex.gap-3 button.bg-blue-600');
            const activeCategory = activeCategoryBtn ? activeCategoryBtn.dataset.category : 'all';

            document.querySelectorAll('.product-card').forEach(card => {
                const name = card.dataset.name.toLowerCase();
                const category = card.dataset.category.toLowerCase();

                const matchesSearch = name.includes(searchTerm);
                const matchesCategory = activeCategory === 'all' || category === activeCategory;

                card.style.display = (matchesSearch && matchesCategory) ? 'block' : 'none';
            });
        }

        // Initialize event listeners
        setupEventListeners();


        function addToCart(itemId, category) {
            const card = document.getElementById("card-" + itemId);
            const stock = parseInt(card.dataset.stock);
            const existingItem = state.cart.find(item => item.id === itemId);
            const isService = (category === "services");

            if (!isService) {
                if (stock <= 0) {
                    Swal.fire('Out of Stock', 'This product is out of stock', 'error');
                    return;
                }

                if (existingItem && existingItem.quantity >= stock) {
                    Swal.fire('Stock Limit', 'Cannot add more than available stock', 'warning');
                    return;
                }
            }

            if (existingItem) {
                existingItem.quantity++;
            } else {
                state.cart.push({
                    id: itemId,
                    name: card.dataset.name,
                    price: parseFloat(card.dataset.price),
                    quantity: 1,
                    stock: stock
                });
            }

            renderCart();
        }

        function removeFromCart(itemId) {
            state.cart = state.cart.filter(item => item.id !== itemId);
            renderCart();
        }

        function updateQuantity(itemId, delta) {
            const item = state.cart.find(i => i.id === itemId);
            if (!item) return;

            const newQty = item.quantity + delta;
            if (newQty < 1) return;

            const card = document.getElementById("card-" + itemId);
            const category = card ? card.dataset.category : "";
            const stock = card ? parseInt(card.dataset.stock) : 0;

            if (category !== "services" && newQty > stock) {
                Swal.fire('Stock Limit', 'Cannot exceed available stock', 'warning');
                return;
            }

            item.quantity = newQty;
            renderCart();
        }

        function updateQuantityManual(itemId, value) {
            const item = state.cart.find(i => i.id === itemId);
            if (!item) return;

            let qty = parseInt(value);
            if (isNaN(qty) || qty < 1) qty = 1;

            const card = document.getElementById("card-" + itemId);
            const category = card ? card.dataset.category : "";
            const stock = card ? parseInt(card.dataset.stock) : 0;

            if (category !== "services" && qty > stock) {
                qty = stock;
                Swal.fire('Stock Limit', 'Cannot exceed available stock', 'warning');
            }

            item.quantity = qty;
            renderCart();
        }

        function renderCart() {
            const cartContainer = document.getElementById('cartItems');

            if (state.cart.length === 0) {
                cartContainer.innerHTML = `
            <div class="text-center text-gray-400 py-8">
                <i class="fas fa-shopping-cart text-4xl mb-2"></i>
                <p>No items in cart</p>
            </div>
        `;
                updateTotals();
                return;
            }

            cartContainer.innerHTML = state.cart.map(item => `
        <div class="cart-item bg-gray-50 rounded-lg p-3 border">
            <div class="flex justify-between items-start mb-2">
                <div class="flex-1">
                    <h4 class="font-semibold text-sm text-gray-800">${escapeHtml(item.name)}</h4>
                    <p class="text-xs text-gray-500">₱${item.price.toFixed(2)} each</p>
                </div>
                <button onclick="removeFromCart(${item.id})" 
                    class="text-red-500 hover:text-red-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <button onclick="updateQuantity(${item.id}, -1)" 
                        class="w-8 h-8 bg-white border rounded hover:bg-gray-100">
                        <i class="fas fa-minus text-xs"></i>
                    </button>
                    <input type="number" min="1" class="border rounded px-2 w-16 text-center"
                        value="${item.quantity}"
                        onchange="updateQuantityManual(${item.id}, this.value)">
                    <button onclick="updateQuantity(${item.id}, 1)" 
                        class="w-8 h-8 bg-white border rounded hover:bg-gray-100">
                        <i class="fas fa-plus text-xs"></i>
                    </button>
                </div>
                <div class="font-bold text-blue-600">
                    ₱${(item.price * item.quantity).toFixed(2)}
                </div>
            </div>
        </div>
    `).join('');

            updateTotals();
        }

        function updateTotals() {
            const subtotal = state.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const tax = subtotal * 0.08;
            const total = subtotal + tax;

            document.getElementById('subtotal').textContent = `₱${subtotal.toFixed(2)}`;
            document.getElementById('tax').textContent = `₱${tax.toFixed(2)}`;
            document.getElementById('total').textContent = `₱${total.toFixed(2)}`;
        }

        function clearCart() {
            if (state.cart.length === 0) return;

            Swal.fire({
                title: 'Clear Cart?',
                text: 'All items will be removed from the cart',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, clear it'
            }).then((result) => {
                if (result.isConfirmed) {
                    state.cart = [];
                    renderCart();
                }
            });
        }

        async function processPayment() {
            if (state.cart.length === 0) {
                Swal.fire('Empty Cart', 'Please add items to cart first', 'error');
                return;
            }

            const subtotal = state.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const tax = subtotal * 0.08;
            const total = subtotal + tax;

            const result = await Swal.fire({
                title: 'Process Payment',
                html: `
            <div class="text-left space-y-3">
                <p class="text-sm text-gray-600">Total Amount:</p>
                <p class="font-bold text-2xl text-blue-600 mb-3">₱${total.toFixed(2)}</p>
                <label class="block text-sm font-medium text-gray-700 mb-2">Amount Paid:</label>
                <input id="amountPaid" type="number" step="0.01" min="${total}" 
                    class="swal2-input" 
                    placeholder="Enter amount paid"
                    style="width: 90%; max-width: 300px; font-size: 16px; padding: 10px;">
            </div>
        `,
                showCancelButton: true,
                confirmButtonText: 'Complete Sale',
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#6b7280',
                width: '400px',
                preConfirm: () => {
                    const paid = parseFloat(document.getElementById('amountPaid').value);
                    if (!paid || paid < total) {
                        Swal.showValidationMessage('Insufficient payment amount');
                        return false;
                    }
                    return paid;
                }
            });

            if (result.isConfirmed) {
                const amountPaid = result.value;
                const change = amountPaid - total;
                await completeSale(amountPaid, change, total);
            }
        }

        async function completeSale(amountPaid, change, total) {
            Swal.fire({
                title: 'Processing...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            const customerName = document.getElementById('customerName').value.trim() || 'Walk-in Customer';

            const payload = {
                transaction_type: 'walk_in',
                customer_name: customerName,
                admin_id: state.adminId,
                transaction_number: state.transactionNumber,
                cart: state.cart.map(item => ({
                    product_id: item.id,
                    quantity: item.quantity,
                    unit_price: item.price
                })),
                subtotal: total - (total * 0.08),
                tax: total * 0.08,
                total_amount: total,
                amount_paid: amountPaid,
                change: change
            };

            try {
                const res = await fetch('../Order/pos_backend.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const text = await res.text();
                console.log("RAW RESPONSE:", text);

                let result;
                try {
                    result = JSON.parse(text);
                } catch (e) {
                    Swal.fire("Error", "Backend returned invalid JSON. Check console.", "error");
                    return;
                }

                if (result.success) {
                    // Automatically save receipt
                    await saveReceiptAutomatically(payload, result.order_id, change);
                } else {
                    throw new Error(result.message || 'Failed to process sale');
                }
            } catch (err) {
                Swal.fire('Error', err.message, 'error');
            }
        }

        async function saveReceiptAutomatically(data, orderId, change) {
            try {
                // Create a temporary element for receipt
                const tempDiv = document.createElement('div');
                tempDiv.style.position = 'fixed';
                tempDiv.style.left = '-9999px';
                tempDiv.innerHTML = `
            <div id="tempReceiptPreview" style="background: white; padding: 20px; width: 400px;">
                ${generateReceiptHTML(data, orderId)}
            </div>
        `;
                document.body.appendChild(tempDiv);

                const receiptElement = document.getElementById('tempReceiptPreview');

                // Convert to canvas
                const canvas = await html2canvas(receiptElement, {
                    backgroundColor: '#ffffff',
                    scale: 2,
                    logging: false
                });

                // Remove temp element
                document.body.removeChild(tempDiv);

                // Convert to blob and save
                canvas.toBlob(async (blob) => {
                    const formData = new FormData();
                    formData.append('receipt_image', blob, `receipt_${data.transaction_number}.png`);
                    formData.append('order_id', orderId);
                    formData.append('transaction_number', data.transaction_number);
                    formData.append('customer_name', data.customer_name);
                    formData.append('total_amount', data.total_amount);

                    try {
                        const res = await fetch('../Order/save_receipt_photo.php', {
                            method: 'POST',
                            body: formData
                        });

                        const result = await res.json();

                        if (result.success) {
                            // Show success with receipt preview
                            showReceiptModal(data, orderId, change);
                        } else {
                            throw new Error(result.message);
                        }
                    } catch (err) {
                        console.error('Failed to save receipt:', err);
                        // Still show success modal even if save fails
                        showReceiptModal(data, orderId, change);
                    }
                }, 'image/png');

            } catch (err) {
                console.error('Failed to generate receipt:', err);
                // Show success modal anyway
                showReceiptModal(data, orderId, change);
            }
        }

        function showReceiptModal(data, orderId, change) {
            const receiptHTML = generateReceiptHTML(data, orderId);

            Swal.fire({
                title: 'Payment Complete!',
                html: `
            <div class="text-center mb-4">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-3">
                    <i class="fas fa-check text-3xl text-green-600"></i>
                </div>
                <p class="text-lg mb-2">Change: <span class="text-2xl font-bold text-green-600">₱${change.toFixed(2)}</span></p>
                <p class="text-sm text-gray-600 mb-2">Transaction #${data.transaction_number}</p>
                <p class="text-xs text-green-600"><i class="fas fa-check-circle mr-1"></i>Receipt saved automatically</p>
            </div>
            
            <div class="border-t border-b py-3 my-4">
                <h3 class="font-bold text-lg mb-3 text-gray-800">Receipt Preview</h3>
                <div id="receiptPreview" class="bg-white p-6 rounded-lg max-h-96 overflow-y-auto text-left mx-auto" style="font-family: 'Courier New', monospace; font-size: 13px; max-width: 400px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    ${receiptHTML}
                </div>
            </div>
        `,
                width: '700px',
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonText: '<i class="fas fa-receipt mr-2"></i>View All Receipts',
                denyButtonText: '<i class="fas fa-print mr-2"></i>Print Receipt',
                cancelButtonText: '<i class="fas fa-shopping-cart mr-2"></i>New Sale',
                confirmButtonColor: '#10b981',
                denyButtonColor: '#3b82f6',
                cancelButtonColor: '#6b7280',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open('../Receipts/receipts_manager.php', '_blank');
                    resetPOS();
                } else if (result.isDenied) {
                    printReceipt(data, orderId);
                    resetPOS();
                } else {
                    resetPOS();
                }
            });
        }

        function generateReceiptHTML(data, orderId) {
            return `
        <div style="max-width: 400px; margin: 0 auto; background: white; padding: 20px;">
            <div style="text-align: center; margin-bottom: 20px; border-bottom: 3px double #333; padding-bottom: 15px;">
                <h2 style="margin: 0; font-size: 24px; font-weight: bold; color: #1e40af;">RCH WATER</h2>
                <p style="margin: 8px 0; font-size: 14px; color: #666;">Official Receipt</p>
            </div>
            
            <div style="margin-bottom: 20px; font-size: 12px; line-height: 1.6; color: #333;">
                <div style="display: grid; grid-template-columns: 120px 1fr; gap: 8px;">
                    <div><strong>Date:</strong></div>
                    <div>${new Date().toLocaleString('en-US', { 
                        year: 'numeric', 
                        month: 'short', 
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    })}</div>
                    <div><strong>Transaction:</strong></div>
                    <div>${data.transaction_number}</div>
                    <div><strong>Order ID:</strong></div>
                    <div>${orderId}</div>
                    <div><strong>Customer:</strong></div>
                    <div>${data.customer_name}</div>
                    <div><strong>Cashier:</strong></div>
                    <div>${state.adminName}</div>
                </div>
            </div>
            
            <div style="border-top: 2px dashed #666; border-bottom: 2px dashed #666; padding: 15px 0; margin: 20px 0;">
                <table style="width: 100%; font-size: 12px; color: #333;">
                    ${data.cart.map(item => {
                        const itemName = state.cart.find(i => i.id === item.product_id)?.name || 'Item';
                        return `
                            <tr>
                                <td colspan="3" style="padding: 8px 0 2px 0; font-weight: 600;">${escapeHtml(itemName)}</td>
                            </tr>
                            <tr>
                                <td style="padding: 0 0 12px 0; width: 60px;">${item.quantity} x</td>
                                <td style="padding: 0 0 12px 0; text-align: left;">₱${item.unit_price.toFixed(2)}</td>
                                <td style="padding: 0 0 12px 0; text-align: right; font-weight: 600;">₱${(item.quantity * item.unit_price).toFixed(2)}</td>
                            </tr>
                        `;
                    }).join('')}
                </table>
            </div>
            
            <div style="font-size: 13px; margin-top: 20px; color: #333;">
                <table style="width: 100%;">
                    <tr>
                        <td style="padding: 5px 0;">Subtotal:</td>
                        <td style="text-align: right; padding: 5px 0;">₱${data.subtotal.toFixed(2)}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;">Tax (8%):</td>
                        <td style="text-align: right; padding: 5px 0;">₱${data.tax.toFixed(2)}</td>
                    </tr>
                    <tr style="border-top: 2px solid #333;">
                        <td style="padding: 12px 0 8px 0; font-size: 18px; font-weight: bold;">TOTAL:</td>
                        <td style="text-align: right; padding: 12px 0 8px 0; font-size: 18px; font-weight: bold; color: #1e40af;">₱${data.total_amount.toFixed(2)}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;">Cash:</td>
                        <td style="text-align: right; padding: 5px 0;">₱${data.amount_paid.toFixed(2)}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0; font-weight: bold; color: #059669;">Change:</td>
                        <td style="text-align: right; padding: 5px 0; font-weight: bold; color: #059669;">₱${data.change.toFixed(2)}</td>
                    </tr>
                </table>
            </div>
            
            <div style="text-align: center; margin-top: 25px; padding-top: 20px; border-top: 3px double #333; font-size: 12px; color: #666;">
                <p style="margin: 8px 0; font-weight: 600; color: #333;">Thank you for your purchase!</p>
                <p style="margin: 8px 0;">Please come again</p>
            </div>
        </div>
    `;
        }

        function printReceipt(data, orderId) {
            const receipt = document.getElementById('receiptPrint');
            receipt.innerHTML = `
        <div style="width: 80mm; padding: 10px; font-family: monospace; font-size: 12px;">
            <div style="text-align: center; margin-bottom: 10px;">
                <h2 style="margin: 0;">RCH WATER</h2>
                <p style="margin: 2px 0;">Official Receipt</p>
                <p style="margin: 2px 0;">================================</p>
            </div>
            
            <p>Date: ${new Date().toLocaleString()}</p>
            <p>Transaction: ${data.transaction_number}</p>
            <p>Order ID: ${orderId}</p>
            <p>Customer: ${data.customer_name}</p>
            <p>Cashier: ${state.adminName}</p>
            <p>================================</p>
            
            ${data.cart.map(item => {
                const itemName = state.cart.find(i => i.id === item.product_id)?.name || 'Item';
                return `
                    <div style="margin: 5px 0;">
                        <p style="margin: 0;">${escapeHtml(itemName)}</p>
                        <p style="margin: 0;">${item.quantity} x ₱${item.unit_price.toFixed(2)} = ₱${(item.quantity * item.unit_price).toFixed(2)}</p>
                    </div>
                `;
            }).join('')}
            
            <p>================================</p>
            <p>Subtotal: ₱${data.subtotal.toFixed(2)}</p>
            <p>Tax (8%): ₱${data.tax.toFixed(2)}</p>
            <p style="font-size: 14px; font-weight: bold;">TOTAL: ₱${data.total_amount.toFixed(2)}</p>
            <p>Cash: ₱${data.amount_paid.toFixed(2)}</p>
            <p>Change: ₱${data.change.toFixed(2)}</p>
            <p>================================</p>
            
            <div style="text-align: center; margin-top: 10px;">
                <p>Thank you for your purchase!</p>
                <p>Please come again</p>
            </div>
        </div>
    `;
            window.print();
        }

        function resetPOS() {
            state.cart = [];
            state.transactionNumber = generateTransactionNumber();
            document.getElementById('transactionNum').textContent = state.transactionNumber;
            document.getElementById('customerName').value = '';
            renderCart();
        }

        function startInventoryPolling() {
            inventoryUpdateInterval = setInterval(async () => {
                try {
                    const res = await fetch('../Order/get_inventory_stock.php');
                    const data = await res.json();

                    if (data.success) {
                        data.inventory.forEach(item => {
                            const card = document.getElementById(`card-${item.item_id}`);
                            if (card) {
                                card.dataset.stock = item.quantity;
                                const stockText = card.querySelector('.text-xs.text-gray-500');
                                if (stockText) {
                                    stockText.textContent = `Stock: ${item.quantity}`;
                                }

                                if (item.category !== 'services' && item.quantity <= 0) {
                                    card.classList.add('opacity-50', 'cursor-not-allowed');
                                } else {
                                    card.classList.remove('opacity-50', 'cursor-not-allowed');
                                }
                            }
                        });
                    }
                } catch (err) {
                    console.error('Failed to update inventory:', err);
                }
            }, 5000);
        }

        window.addEventListener('beforeunload', () => {
            if (inventoryUpdateInterval) {
                clearInterval(inventoryUpdateInterval);
            }
        });

        function escapeHtml(str) {
            return String(str || '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;');
        }


        (function() {
            const originalSwalFire = Swal.fire;
            let scrollPosition = 0;

            Swal.fire = function(...args) {
                scrollPosition = window.pageYOffset;
                const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;

                document.body.style.overflow = 'hidden';
                document.body.style.position = 'fixed';
                document.body.style.top = `-${scrollPosition}px`;
                document.body.style.width = '100%';
                document.body.style.paddingRight = scrollbarWidth + 'px';

                const result = originalSwalFire.apply(this, args);

                result.finally(() => {
                    document.body.style.overflow = '';
                    document.body.style.position = '';
                    document.body.style.top = '';
                    document.body.style.width = '';
                    document.body.style.paddingRight = '';
                    window.scrollTo(0, scrollPosition);
                });

                return result;
            };
        })();
    </script>


</body>

</html>