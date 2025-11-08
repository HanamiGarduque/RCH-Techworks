<?php
// require_once '../check_session.php';
require_once '../Database/db_connection.php';

// if (!isAdmin()) {
//     header("Location: " . dirname($_SERVER['PHP_SELF']) . "/index.php");
//     exit();
// }

$database = new Database();
$db = $database->getConnect();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RCH Water - Product Inventory</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>


<body class="bg-gray-50 overflow-hidden">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="group bg-gradient-to-br from-blue-400 to-blue-600 shadow-lg border-r border-gray-200 
        flex-shrink-0 fixed top-0 left-0 h-full overflow-hidden flex flex-col text-white 
        transition-all duration-500 ease-in-out w-20 hover:w-64 z-50">

            <!-- Logo -->
            <div class="p-7 border-b border-blue-400 flex items-center gap-2">
                <i class="fas fa-droplet text-3xl"></i>
                <div class="overflow-hidden">
                    <span class="text-2xl font-semibold block whitespace-nowrap transition-all duration-500 ease-in-out 
                 opacity-0 group-hover:opacity-100 translate-x-[-20px] group-hover:translate-x-0">
                        RCH Water
                    </span>
                </div>
            </div>

            <nav class="mt-6 flex-1">
                <ul class="space-y-1 px-4">
                    <li>
                        <a href="../Admin/dashboard.php" class="flex items-center px-4 py-3 text-white rounded-lg font-medium hover:bg-white/20">
                            <i class="fas fa-chart-line text-lg"></i>
                            <div class="overflow-hidden ml-3">
                                <span class="block whitespace-nowrap transition-all duration-500 ease-in-out 
                                     opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">
                                    Dashboard
                                </span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="../Order/orders_list.php" class="flex items-center px-4 py-3 text-white rounded-lg font-medium hover:bg-white/20">
                            <i class="fas fa-shopping-cart text-lg"></i>
                            <div class="overflow-hidden ml-3">
                                <span class="block whitespace-nowrap transition-all duration-500 ease-in-out 
                                     opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">
                                    Orders
                                </span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="../GallonOwnership/gallon_ownership.php" class="flex items-center px-4 py-3 text-white rounded-lg font-medium hover:bg-white/20">
                            <i class="fas fa-tint text-lg"></i>
                            <div class="overflow-hidden ml-3">
                                <span class="block whitespace-nowrap transition-all duration-500 ease-in-out 
                 opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">
                                    Gallon Ownership
                                </span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="../Inventory/inventory_list.php" class="flex items-center px-4 py-3 bg-white/20 text-white rounded-lg font-medium">
                            <i class="fas fa-boxes text-lg"></i>
                            <div class="overflow-hidden ml-3">
                                <span class="block whitespace-nowrap transition-all duration-500 ease-in-out 
                 opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">
                                    Inventory
                                </span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="../Customer/customers_list.php" class="flex items-center px-4 py-3 text-white rounded-lg font-medium hover:bg-white/20">
                            <i class="fas fa-users text-lg"></i>
                            <div class="overflow-hidden ml-3">
                                <span class="block whitespace-nowrap transition-all duration-500 ease-in-out 
                 opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">
                                    Customers
                                </span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="../Delivery/deliveries_list.php" class="flex items-center px-4 py-3 text-white rounded-lg font-medium hover:bg-white/20">
                            <i class="fas fa-truck text-lg"></i>
                            <div class="overflow-hidden ml-3">
                                <span class="block whitespace-nowrap transition-all duration-500 ease-in-out 
                 opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">
                                    Deliveries
                                </span>
                            </div>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="border-t border-blue-400 p-4">
                <a href="../logout.php" class="flex items-center px-4 py-3 font-medium text-white hover:bg-blue-500 rounded-lg transition-colors">
                    <i class="fas fa-sign-out-alt text-lg"></i>
                    <span class="ml-3 hidden group-hover:inline">Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->

        <div class="flex-1 ml-20 group-hover:ml-64 transition-all duration-300 ease-in-out">
            <div class="flex flex-col h-screen">
                <!-- Header -->
                <div class="bg-white border-b border-gray-100 shadow-sm p-4 transition-all duration-300 ease-in-out">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col">
                            <h1 class="text-3xl font-semibold text-blue-500">Inventory</h1>
                            <p class="text-sm text-gray-700 mt-1">View and manage inventory levels and product details.</p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <a href="../Notification/notifications.php" title="Notifications" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-blue-100 cursor-pointer transition">
                                <i class="fas fa-bell text-blue-500 text-xl"></i>
                            </a>
                            <a href="../Messaging/admin_messages.php" title="Messages" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-blue-100 cursor-pointer transition">
                                <i class="fas fa-envelope text-blue-500 text-xl"></i>
                            </a>
                            <a href="../Admin/profile.php" title="Profile" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-blue-100 cursor-pointer transition">
                                <i class="fas fa-user text-blue-500 text-xl"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="m-3 flex flex-col flex-1 overflow-hidden">
                    <!-- Search and Buttons -->
                    <div class="flex items-center gap-2 mb-4">
                        <div class="flex-1 relative">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            <input
                                type="text"
                                id="searchInput"
                                placeholder="Search by Item, Quantity, or Price..."
                                class="w-full pl-10 pr-4 py-2 font-light border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder:text-sm"
                                style="font-style: italic;"
                                oninput="this.style.fontStyle = this.value ? 'normal' : 'italic';"
                                onkeyup="searchCards()">

                        </div>
                        <button
                            class="bg-gradient-to-br from-blue-400 to-blue-600 hover:from-blue-500 hover:to-blue-700 text-white px-6 py-2 rounded-lg font-semibold shadow-md transition">
                            <i class="fas fa-plus mr-2"></i> Add Item
                        </button>

                    </div>

                    <!-- Content Area -->
                    <div class="p-6 bg-white rounded-lg shadow-lg ring-3 ring-gray-200 rounded-lg">
                        <!-- Filter Tabs -->
                        <div class="flex gap-3 mb-6 flex-wrap">
                            <button id="allTab"
                                class="px-4 py-2 rounded-full border-none cursor-pointer font-semibold transition-all duration-300 bg-blue-600 text-white"
                                onclick="filterTab(this)">
                                <i class="fas fa-list mr-2"></i>All
                            </button>
                            <button
                                class="px-4 py-2 rounded-full border-none cursor-pointer font-semibold transition-all duration-300 bg-blue-50 text-blue-500 hover:bg-gray-200"
                                onclick="filterTab(this)">
                                <i class="fas fa-building mr-2"></i>Gallons
                            </button>
                            <button
                                class="px-4 py-2 rounded-full border-none cursor-pointer font-semibold transition-all duration-300 bg-blue-50 text-blue-500 hover:bg-gray-200"
                                onclick="filterTab(this)">
                                <i class="fas fa-user mr-2"></i>Accessories
                            </button>
                            <button
                                class="px-4 py-2 rounded-full border-none cursor-pointer font-semibold transition-all duration-300 bg-blue-50 text-blue-500 hover:bg-gray-200"
                                onclick="filterTab(this)">
                                <i class="fas fa-question-circle mr-2"></i>Others
                            </button>
                        </div>

                        <!-- Cards -->
                        <div class="bg-white rounded-lg overflow-hidden">
                            <!-- Scroll wrapper -->
                            <div class="overflow-x-auto max-h-[500px]">

                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
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
                                        WHERE i.item_name NOT IN ('Refill (18.9L RG)', 'Refill (20L SG)', 'Refill (10L SG)');
                                    ";

                                    $stmt = $db->prepare($query);
                                    $stmt->execute();

                                    if ($stmt->rowCount() > 0) {
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            $item_id = htmlspecialchars($row['item_id']);
                                            $item_name = htmlspecialchars($row['item_name']);
                                            $quantity = (int)$row['quantity'];
                                            $category = htmlspecialchars($row['category']);
                                            $price = number_format((float)$row['price'], 2);
                                            $item_image = htmlspecialchars($row['item_image']);

                                            // Stock status logic
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
                                                <div class="inventory-card bg-white border border-gray-200 rounded-xl shadow-md hover:shadow-lg transition p-4 flex flex-col items-center" data-category="' . strtolower($category) . '">
                                                <img src="../Assets/' . $item_image . '" alt="' . $item_name . '" class="w-24 h-24 object-cover rounded-lg mb-3">
                                                <h2 class="text-lg font-semibold text-gray-800 text-center mb-1">' . $item_name . '</h2>
                                                <p class="text-sm text-gray-600 mb-1">Quantity: <span class="font-medium">' . $quantity . '</span></p>
                                                <p class="text-sm text-gray-600 mb-1">Price: <span class="font-medium text-blue-600">₱' . $price . '</span></p>
                                                <p class="text-sm font-semibold ' . $statusColor . ' px-3 py-1 rounded-full mb-3">' . $status . '</p>
                                                <div class="flex gap-3">
                                                    <button 
                                                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-1 rounded-md text-sm font-medium shadow" 
 onclick="editItem(' . $item_id . ')">                                                        <i class="fas fa-edit mr-1"></i>Edit
                                                        </button>
                                                    <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-1 rounded-md text-sm font-medium shadow" onclick="deleteItem(' . $item_id . ')">
                                                        <i class="fas fa-trash mr-1"></i>Delete
                                                    </button>
                                                </div>
                                            </div>';
                                        }
                                    } else {
                                        echo '<p class="text-gray-500 text-center col-span-full">No items found in inventory.</p>';
                                    }
                                    ?>
                                </div>

                            </div>
                        </div>


                        <!-- Edit Modal -->
                        <div id="editModal" class="fixed inset-0 hidden bg-black bg-opacity-50 flex items-center justify-center z-50">
                            <div class="bg-white rounded-lg shadow-lg w-96 p-6 relative">
                                <h2 class="text-lg font-semibold text-gray-800 mb-4">Edit Item</h2>

                                <form id="editForm">
                                    <input type="hidden" id="editItemId" name="item_id">

                                    <label class="block text-sm font-medium text-gray-700 mb-1">Item Name</label>
                                    <input type="text" id="editItemName" name="item_name" class="w-full border rounded px-3 py-1 mb-3">

                                    <div id="quantitySection">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity Adjustment</label>
                                        <div class="flex items-center space-x-2">
                                            <button type="button" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded" onclick="adjustQuantity(-1)">−</button>
                                            <input type="number" id="editQuantity" name="quantity" value="0" class="border rounded px-3 py-1 w-20 text-center">
                                            <button type="button" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded" onclick="adjustQuantity(1)">+</button>
                                        </div>
                                    </div>

                                    <label class="block text-sm font-medium text-gray-700 mt-3 mb-1">Price</label>
                                    <input type="number" id="editPrice" name="price" step="0.01" class="w-full border rounded px-3 py-1 mb-3">

                                    <div class="flex justify-end space-x-2 mt-4">
                                        <button type="button" onclick="cancelEdit()" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-1 rounded">Cancel</button>
                                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1 rounded">Update</button>
                                    </div>
                                </form>

                                <script>
                                    // Show the modal and populate the hidden id field.
                                    // Make sure your item edit buttons call editItem(<id>) (no nested PHP tags).
                                    function editItem(itemId) {
                                        document.getElementById('editItemId').value = itemId;
                                        // Optionally populate other fields if you have DOM values or fetch from server
                                        document.getElementById('editModal').classList.remove('hidden');
                                    }

                                    function cancelEdit() {
                                        document.getElementById('editModal').classList.add('hidden');
                                    }

                                    function adjustQuantity(delta) {
                                        const el = document.getElementById('editQuantity');
                                        let val = parseInt(el.value, 10) || 0;
                                        val += delta;
                                        if (val < 0) val = 0;
                                        el.value = val;
                                    }

                                    // Simple submit handler — replace with AJAX to your update endpoint as needed
                                    document.getElementById('editForm').addEventListener('submit', function(e) {
                                        e.preventDefault();
                                        // Example: close modal and show success
                                        document.getElementById('editModal').classList.add('hidden');
                                        const success = document.getElementById('successModal');
                                        success.classList.remove('hidden');
                                        setTimeout(() => success.classList.add('hidden'), 2000);
                                    });
                                </script>
                            </div>
                        </div>

                        <!-- Success Modal -->
                        <div id="successModal" class="fixed inset-0 hidden bg-black bg-opacity-50 flex items-center justify-center z-50">
                            <div class="bg-white rounded-lg shadow-lg w-80 p-6 text-center">
                                <h3 class="text-lg font-semibold text-green-600 mb-2">✔ Item Updated Successfully!</h3>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

    </div>
    </div>
    <script>
        //for smooth page transition
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.addEventListener('click', e => {
                e.preventDefault();
                const target = e.currentTarget.getAttribute('href');
                const content = document.getElementById('page-content');

                // Fade out
                content.classList.add('opacity-0', 'translate-x-2');

                setTimeout(() => {
                    window.location.href = target;
                }, 300); // same as duration-300
            });
        });

        const content = document.getElementById('page-content');
        content.classList.add('opacity-0');
        setTimeout(() => content.classList.remove('opacity-0', 'translate-x-2'), 50);

        function searchCards() {
            const input = document.getElementById("searchInput");
            const filter = input.value.toLowerCase();
            const cards = document.querySelectorAll(".inventory-card");
            let matchFound = false;

            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                if (filter === "" || text.includes(filter)) {
                    card.style.display = "";
                    matchFound = true;
                } else {
                    card.style.display = "none";
                }
            });

            // Check if message element already exists
            let message = document.getElementById("noMatchMessage");
            if (!message) {
                message = document.createElement("p");
                message.id = "noMatchMessage";
                message.className = "text-gray-500 text-center col-span-full mt-4";
                const grid = document.querySelector(".grid");
                grid.appendChild(message);
            }

            // Update message visibility
            if (matchFound) {
                message.textContent = "";
                message.style.display = "none";
            } else {
                message.textContent = "No matching items found.";
                message.style.display = "block";
            }
        }
    </script>
</body>