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
        <script src="../Admin/includes/sidebar.js"></script>

        <!-- Main Content -->

        <div class="flex-1 ml-20 group-hover:ml-64 transition-all duration-300 ease-in-out">
            <div class="flex flex-col h-screen">
                <!-- Header -->
                 <?php
                $pageTitle = "Inventory";
                $pageSubtitle = "View and manage inventory levels and product details.";
                include '../Admin/includes/header.php';
                ?>


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
                            onclick="addItemModal()"
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
                                                    <button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-1 rounded-md text-sm font-medium shadow" onclick="editItem(' . $item_id . ')">
                                                        <i class="fas fa-edit mr-1"></i>Edit
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


        // Filter Function
        function filterTab(button) {
            document.querySelectorAll('button[onclick="filterTab(this)"]').forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white', 'no-hover');
                btn.classList.add('bg-blue-50', 'text-blue-500', 'hover:bg-gray-200');
            });

            // Set active tab
            button.classList.remove('bg-blue-50', 'text-blue-500', 'hover:bg-gray-200');
            button.classList.add('bg-blue-600', 'text-white', 'no-hover');

            // Get filter type
            const filter = button.textContent.trim();
            const cards = document.querySelectorAll('.inventory-card');

            cards.forEach(card => {
                const category = (card.getAttribute('data-category') || '').toLowerCase();
                let show = false;

                if (filter === "All") {
                    show = true;
                } else if (filter === "Gallons" && category === "gallons") {
                    show = true;
                } else if (filter === "Accessories" && category === "accessories") {
                    show = true;
                } else if (filter === "Others" && category === "others") {
                    show = true;
                }

                card.style.display = show ? "" : "none";
            });
        }

        //edit item modal

        function editItem(itemId) {
            const card = event.target.closest('.inventory-card');
            const name = card.querySelector('h2').innerText;
            const quantity = card.querySelector('p:nth-child(3) span').innerText;
            const price = card.querySelector('p:nth-child(4) span').innerText.replace('₱', '').trim();
            const category = card.getAttribute('data-category');

            // Create modal container
            const modal = document.createElement('div');
            modal.className = "fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50";

            // Determine if quantity input should be disabled for Gallons
            const qtyField = (category === "gallons") ? `
    <div class="flex flex-col items-center mb-4 w-full">
        <span class="self-start font-medium text-blue-500 mr-2 w-full">Quantity:</span>
        <input type="number" id="editQty${itemId}" value="${quantity}" disabled
            class="w-full mx-auto border border-gray-300 rounded-md px-3 py-2 text-center font-medium bg-gray-100 cursor-not-allowed" />
        <p class="text-xs text-gray-500 italic mt-2 w-full text-center">Quantity cannot be edited for Gallon items.</p>
    </div>
    ` : `
        <div class="flex items-center gap-3 mb-4 w-full">
            <span class="text-left font-medium text-blue-500 mr-4">Quantity:</span>
            <div class="flex items-center gap-2">
                <button type="button" onclick="changeQty(${itemId}, -1)"
                    class="bg-gray-200 hover:bg-gray-300 text-lg w-8 h-8 rounded-full font-bold">−</button>
                <input type="number" id="editQty${itemId}" value="${quantity}" min="0"
                    class="w-20 text-center border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" />
                <button type="button" onclick="changeQty(${itemId}, 1)"
                    class="bg-gray-200 hover:bg-gray-300 text-lg w-8 h-8 rounded-full font-bold">+</button>
            </div>
        </div>
    `;

            // Modal content
            modal.innerHTML = `
        <div class="bg-white rounded-xl shadow-lg w-96 p-6">
            <h2 class="text-2xl font-semibold text-blue-600 mb-4 text-center">Edit Item</h2>
            <form onsubmit="saveItem(event, ${itemId}, '${category}')" class="flex flex-col items-center">
            <span class="self-start text-left font-medium text-blue-500 mr-2 w-full">Item Name:</span>
            <input type="text" id="editName${itemId}" value="${name}" 
                class="w-full mx-auto border border-gray-300 rounded-md px-3 py-2 mb-4 focus:ring-2 focus:ring-blue-500 font-medium" />

            ${qtyField}

            <div class="flex items-center gap-3 mb-6 w-full">
                <span class="text-left font-medium text-blue-500 mr-2 w-1/3">Item Price:</span>
                <div class="flex items-center w-2/3">
                <span class="px-3 py-2 bg-gray-100 border border-r-0 border-gray-300 rounded-l-md text-gray-700">Php</span>
                <input type="number" id="editPrice${itemId}" value="${price}" step="0.01"
                    class="w-full border border-gray-300 rounded-r-md px-3 py-2 focus:ring-2 focus:ring-blue-500 font-medium" />
                </div>
            </div>

            <div class="flex justify-center gap-3">
                <button type="submit" 
                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md shadow text-sm font-medium">
                <i class="fas fa-save mr-1"></i>Save
                </button>
                <button type="button" onclick="cancelEdit(this)"
                class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded-md shadow text-sm font-medium">
                <i class="fas fa-times mr-1"></i>Cancel
                </button>
            </div>
            </form>
        </div>
    `;
            document.body.appendChild(modal);
        }

        // Change quantity buttons
        function changeQty(id, delta) {
            const qtyInput = document.getElementById(`editQty${id}`);
            if (!qtyInput.disabled) {
                let newValue = parseInt(qtyInput.value || 0) + delta;
                qtyInput.value = Math.max(newValue, 0);
            }
        }

        // Cancel edit (close modal)
        function cancelEdit(button) {
            const modal = button.closest('.fixed');
            modal.remove();
        }

        // Save item changes
        function saveItem(event, itemId, category) {
            event.preventDefault();
            const newName = document.getElementById(`editName${itemId}`).value;
            const newPrice = document.getElementById(`editPrice${itemId}`).value;
            const newQty = (category === "gallons") ?
                null // skip sending quantity for gallons
                :
                document.getElementById(`editQty${itemId}`).value;

            let bodyData = `item_id=${itemId}&item_name=${encodeURIComponent(newName)}&price=${newPrice}`;
            if (newQty !== null) bodyData += `&quantity=${newQty}`;

            fetch('update_item.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: bodyData
                })
                .then(res => res.text())
                .then(data => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated Successfully',
                        text: 'The item details have been updated.',
                        confirmButtonColor: '#3085d6',
                    }).then(() => {
                        document.querySelector('.fixed.inset-0')?.remove();
                        // Optionally update values in card dynamically
                        const card = document.querySelector(`[onclick="editItem(${itemId})"]`).closest('.inventory-card');
                        card.querySelector('h2').innerText = newName;
                        if (newQty !== null) {
                            card.querySelector('p:nth-child(3) span').innerText = newQty;
                        }
                        card.querySelector('p:nth-child(4) span').innerText = `₱${parseFloat(newPrice).toFixed(2)}`;
                    });
                })
                .catch(err => console.error(err));
        }
        // DELETE FUNCTION 
        function deleteItem(id) {
            Swal.fire({
                title: 'Confirm Deletion',
                html: `
                    <div style="display:flex; align-items:center; gap:10px;">
                        <input type="password" id="adminPass" class="swal2-input" placeholder="Enter password" style="flex:1; margin:10px;" />
                        <button type="button" id="togglePassBtn" title="Show/Hide password" style="background:transparent; border:none; cursor:pointer; color:#374151; font-size:24px;">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#9ca3af',
                confirmButtonText: 'Delete',
                didOpen: () => {
                    const popup = Swal.getPopup();
                    const btn = popup.querySelector('#togglePassBtn');
                    const input = popup.querySelector('#adminPass');

                    // Toggle show/hide password
                    btn.addEventListener('click', () => {
                        if (input.type === 'password') {
                            input.type = 'text';
                            btn.innerHTML = '<i class="fas fa-eye-slash"></i>';
                        } else {
                            input.type = 'password';
                            btn.innerHTML = '<i class="fas fa-eye"></i>';
                        }
                        input.focus();
                    });

                    // allow Enter to confirm
                    input.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter') {
                            Swal.clickConfirm();
                        }
                    });

                    input.focus();
                },
                preConfirm: () => {
                    const passEl = Swal.getPopup().querySelector('#adminPass');
                    const pass = passEl ? passEl.value : '';
                    if (!pass) {
                        Swal.showValidationMessage('Password is required');
                        return false;
                    }
                    return {
                        id,
                        pass
                    };
                }
            }).then(result => {
                if (result.isConfirmed) {
                    fetch('delete_item.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(result.value)
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Deleted!', 'Item has been removed.', 'success')
                                    .then(() => location.reload());
                            } else {
                                Swal.fire('Error', data.message || 'Failed to delete item.', 'error');
                            }
                        })
                        .catch(() => Swal.fire('Error', 'Request failed.', 'error'));
                }
            });
        }

        function addItemModal() {
            // Create modal container
            const modal = document.createElement('div');
            modal.className = "fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50";

            // Modal content
            modal.innerHTML = `
        <div class="bg-white rounded-xl shadow-lg w-96 p-6">
            <h2 class="text-2xl font-semibold text-blue-600 mb-4 text-center">Add New Item</h2>
            <form id="addItemForm" class="flex flex-col items-center" enctype="multipart/form-data">
                
                <!-- Category -->
                <span class="self-start text-left font-medium text-blue-500 mr-2 w-full">Category:</span>
                <select id="addCategory" name="category" class="w-full mx-auto border border-gray-300 rounded-md px-3 py-2 mb-4 focus:ring-2 focus:ring-blue-500">
                    <option value="Gallon">Gallon</option>
                    <option value="Accessories">Accessories</option>
                    <option value="Others">Others</option>
                </select>

                <!-- Item Name -->
                <span class="self-start text-left font-medium text-blue-500 mr-2 w-full">Item Name:</span>
                <input type="text" id="addItemName" name="item_name" 
                    class="w-full mx-auto border border-gray-300 rounded-md px-3 py-2 mb-4 focus:ring-2 focus:ring-blue-500 font-medium" required />

                <!-- Quantity -->
                <div class="flex items-center gap-3 mb-4 w-full">
                    <span class="text-left font-medium text-blue-500 mr-2 w-1/3">Quantity:</span>
                    <div class="flex items-center gap-2 w-2/3">
                        <button type="button" onclick="adjustAddQuantity(-1)"
                            class="bg-gray-200 hover:bg-gray-300 text-lg w-8 h-8 rounded-full font-bold">−</button>
                        <input type="number" id="addQuantity" name="quantity" value="0" min="0"
                            class="w-full text-center border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" />
                        <button type="button" onclick="adjustAddQuantity(1)"
                            class="bg-gray-200 hover:bg-gray-300 text-lg w-8 h-8 rounded-full font-bold">+</button>
                    </div>
                </div>

                <!-- Price -->
                <div class="flex items-center gap-3 mb-4 w-full">
                    <span class="text-left font-medium text-blue-500 mr-2 w-1/3">Item Price:</span>
                    <div class="flex items-center w-2/3">
                        <span class="px-3 py-2 bg-gray-100 border border-r-0 border-gray-300 rounded-l-md text-gray-700">Php</span>
                        <input type="number" id="addPrice" name="price" step="0.01"
                            class="w-full border border-gray-300 rounded-r-md px-3 py-2 focus:ring-2 focus:ring-blue-500 font-medium" required />
                    </div>
                </div>

                <!-- Description -->
                <span class="self-start text-left font-medium text-blue-500 mr-2 w-full">Description:</span>
                <textarea id="addDescription" name="description" rows="2"
                    class="w-full mx-auto border border-gray-300 rounded-md px-3 py-2 mb-4 focus:ring-2 focus:ring-blue-500 font-medium"></textarea>

                <!-- Item Image -->
                <span class="self-start text-left font-medium text-blue-500 mr-2 w-full">Item Image:</span>
                <input type="file" id="addItemImage" name="item_image" class="w-full border border-gray-300 rounded-md px-3 py-2 mb-6" accept="image/*" />

                <!-- Buttons -->
                <div class="flex justify-center gap-3">
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md shadow text-sm font-medium">
                        <i class="fas fa-plus mr-1"></i>Add
                    </button>
                    <button type="button" onclick="cancelAdd(this)"
                        class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded-md shadow text-sm font-medium">
                        <i class="fas fa-times mr-1"></i>Cancel
                    </button>
                </div>
            </form>
        </div>
    `;

            document.body.appendChild(modal);
        }

        // Quantity adjustment function
        function adjustAddQuantity(delta) {
            const el = document.getElementById('addQuantity');
            let val = parseInt(el.value, 10) || 0;
            val += delta;
            if (val < 0) val = 0;
            el.value = val;
        }

        // Cancel function
        function cancelAdd(button) {
            const modal = button.closest('.fixed');
            if (modal) modal.remove();
        }

        // Handle form submission
        document.addEventListener('submit', function(e) {
            if (e.target && e.target.id === 'addItemForm') {
                e.preventDefault();
                const formData = new FormData(e.target);

                fetch('add_item.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Success', 'Item added successfully!', 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message || 'Failed to add item.', 'error');
                        }
                    })
                    .catch(() => Swal.fire('Error', 'Request failed.', 'error'));
            }
        });
    </script>
</body>