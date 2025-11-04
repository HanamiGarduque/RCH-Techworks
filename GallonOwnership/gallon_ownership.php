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
    <title>RCH Water - Gallon Ownership</title>
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


<body class="bg-gray-50">
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
                        <a href="../Admin/dashboard.php" class="flex items-center px-4 py-3 font-medium  hover:bg-blue-500 rounded-lg">
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
                        <a href="../Order/orders_list.php" class="flex items-center px-4 py-3 text-white rounded-lg font-medium hover:bg-blue-500">
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
                        <a href="../GallonOwnership/gallon_ownership.php" class="flex items-center px-4 py-3 bg-blue-50 text-blue-600 rounded-lg font-medium">
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
                        <a href="../Inventory/inventory_list.php" class="flex items-center px-4 py-3 text-white rounded-lg font-medium hover:bg-blue-500">
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
                        <a href="../Customer/customers_list.php" class="flex items-center px-4 py-3 text-white rounded-lg font-medium hover:bg-blue-500">
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
                        <a href="../Delivery/deliveries_list.php" class="flex items-center px-4 py-3 text-white rounded-lg font-medium hover:bg-blue-500">
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

        <div id="page-content"
            class="main-content fixed w-full top-0 right-0 overflow-auto h-full transition-all duration-300 ease-in-out">
            <div
                class="flex-1 transition-all duration-300 ease-in-out group-hover:ml-64 ml-20 flex flex-col overflow-hidden">
                <div class="bg-white border-b border-gray-100 shadow-sm p-4 transition-all duration-300 ease-in-out">
                    <div class="flex items-center justify-between">
                        <h1 class="text-2xl font-semibold text-blue-500">Gallon Ownership</h1>

                        <div class="flex items-center space-x-4">
                            <a href="../Notification/notifications.php" title="Notifications" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-blue-100 cursor-pointer transition">
                                <i class="fas fa-bell text-blue-500 text-xl"></i>
                            </a>
                            <a href="../Messaging/messages.php" title="Messages" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-blue-100 cursor-pointer transition">
                                <i class="fas fa-envelope text-blue-500 text-xl"></i>
                            </a>
                            <a href="../Admin/profile.php" title="Profile" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-blue-100 cursor-pointer transition">
                                <i class="fas fa-user text-blue-500 text-xl"></i>
                            </a>
                        </div>
                    </div>
                </div>


                <div class="m-3">
                    <!-- Search and Buttons -->
                    <div class="flex items-center gap-2 mb-4">
                        <div class="flex-1 relative">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            <input
                                type="text"
                                id="searchInput"
                                placeholder="Search by Gallon Owner, Gallon Type, Gallon ID..."
                                class="w-full pl-10 pr-4 py-2 font-light border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder:text-sm"
                                style="font-style: italic;"
                                oninput="this.style.fontStyle = this.value ? 'normal' : 'italic';"
                                onkeyup="searchTable()">
                        </div>
                        <button
                            onclick="searchTable()"
                            class="bg-gradient-to-br from-blue-400 to-blue-600 hover:from-blue-500 hover:to-blue-700 text-white px-6 py-2 rounded-lg font-semibold shadow-md transition">
                            Search
                        </button>

                        <a href="qr_scanner.php" id="scanBtn"
                            class="inline-flex items-center bg-gradient-to-br from-blue-400 to-blue-600 hover:from-blue-500 hover:to-blue-700 text-white px-6 py-2 rounded-lg font-semibold shadow-md transition">
                            <i class="fas fa-qrcode mr-2"></i>
                            Scan QR Code
                        </a>


                        <!-- QR Reader -->
                        <div id="reader"></div>
                        <div id="result"></div>
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
                                <i class="fas fa-building mr-2"></i>Station-Owned
                            </button>
                            <button
                                class="px-4 py-2 rounded-full border-none cursor-pointer font-semibold transition-all duration-300 bg-blue-50 text-blue-500 hover:bg-gray-200"
                                onclick="filterTab(this)">
                                <i class="fas fa-user mr-2"></i>Customer-Owned
                            </button>
                            <button
                                class="px-4 py-2 rounded-full border-none cursor-pointer font-semibold transition-all duration-300 bg-blue-50 text-blue-500 hover:bg-gray-200"
                                onclick="filterTab(this)">
                                <i class="fas fa-question-circle mr-2"></i>Untracked
                            </button>
                        </div>

                        <!-- Table -->
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <!-- Scroll wrapper -->
                            <div class="overflow-x-auto max-h-[400px]">

                                <table id="gallonTable" class="min-w-full text-sm text-left text-gray-700">
                                    <thead class="bg-blue-50 border-b border-gray-200 sticky top-0 z-10">
                                        <tr>
                                            <th class="px-6 py-4 font-semibold text-center cursor-pointer min-w-[80px]" onclick="sortTable(0)" title="Gallon ID">Gallon ID ⬍</th>
                                            <th class="px-6 py-4 font-semibold text-center cursor-pointer min-w-[110px]" onclick="sortTable(1)" title="Gallon Type">Gallon Type ⬍</th>
                                            <th class="px-6 py-4 font-semibold text-center cursor-pointer min-w-[220px]" onclick="sortTable(2)" title="Owner">Owner ⬍</th>
                                            <th class="px-6 py-4 font-semibold text-center min-w-[120px]" title="Code Value">Code Value</th>
                                            <th class="px-6 py-4 font-semibold text-center min-w-[100px]" title="Status">Status</th>
                                            <th class="px-6 py-4 font-semibold text-center min-w-[120px]" title="Actions">Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        $query = "SELECT 
                                            go.ownership_id AS `Gallon ID`,
                                            go.gallon_type AS `Gallon Type`,
                                            u.name AS `Owner`,
                                            go.code_value AS `Code Value`,
                                            go.status AS `Status`,
                                            go.qr_image AS `QR Image`
                                        FROM gallon_ownership AS go
                                        LEFT JOIN users AS u ON go.owner_id = u.user_id";

                                        $stmt = $db->prepare($query);
                                        $stmt->execute();

                                        $num = $stmt->rowCount();


                                        if ($num > 0) {
                                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                echo "<tr class='border-b divide-x divide-gray-200 hover:bg-gray-50'>";
                                                echo "<td class='px-6 py-4'>" . htmlspecialchars($row['Gallon ID']) . "</td>";
                                                echo "<td class='px-6 py-4'>" . htmlspecialchars($row['Gallon Type']) . "</td>";
                                                echo "<td class='px-6 py-4 owner-cell'>" . htmlspecialchars($row['Owner']) . "</td>";
                                                echo "<td class='px-6 py-4 text-center code-cell'>";
                                                if (empty($row['Code Value'])) {
                                                    echo '<button onclick="generateQR(' . htmlspecialchars($row['Gallon ID']) . ')" class="bg-blue-100 font-semibold text-blue-500 px-3 py-1 rounded-md text-xs hover:scale-105 transition">Generate QR</button>';
                                                } else {
                                                    echo htmlspecialchars($row['Code Value']);
                                                }
                                                echo '</td>';
                                                echo "<td class='px-6 py-4 text-sm text-center status-cell'>
                                                <span class='status-text'>" . htmlspecialchars($row['Status']) . "</span>
                                            </td>";

                                                // Store qr_image and code_value for JS use
                                                $qrImage = htmlspecialchars($row['QR Image'] ?? '');
                                                $codeValue = htmlspecialchars($row['Code Value'] ?? '');
                                                $gallonID = htmlspecialchars($row['Gallon ID']);
                                                $ownerName = htmlspecialchars($row['Owner'] ?? 'N/A');

                                                echo '<td class="px-6 py-4 text-center">';
                                                echo "<i class=\"fas fa-eye mx-1 text-blue-600 hover:scale-125 transition-transform\" title=\"View\" style=\"cursor:pointer;\" onclick=\"viewQR('{$ownerName}', '{$codeValue}', '{$qrImage}')\"></i>";
                                                echo "<i class=\"fas fa-edit mx-1 text-orange-500 hover:scale-125 transition-transform\" title=\"Edit\" style=\"cursor:pointer;\" onclick=\"editGallon({$gallonID})\"></i>";
                                                echo "<i class=\"fas fa-trash mx-1 text-red-600 hover:scale-125 transition-transform cursor-pointer\" title=\"Delete\" onclick=\"deleteGallon({$gallonID})\"></i>";
                                                echo '</td>';

                                                echo "</tr>";
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            //for smooth page transitio
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

            function sortTable(columnIndex) {
                const table = document.getElementById("gallonTable");
                const tbody = table.querySelector("tbody");
                const rows = Array.from(tbody.querySelectorAll("tr"));
                const isAscending = table.dataset.sortOrder !== "asc";

                rows.sort((a, b) => {
                    const aText = a.children[columnIndex].innerText.toLowerCase();
                    const bText = b.children[columnIndex].innerText.toLowerCase();
                    return isAscending ? aText.localeCompare(bText) : bText.localeCompare(aText);
                });

                tbody.innerHTML = "";
                rows.forEach(row => tbody.appendChild(row));
                table.dataset.sortOrder = isAscending ? "asc" : "desc";
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
                const rows = document.querySelectorAll('#gallonTable tbody tr');

                rows.forEach(row => {
                    const owner = row.querySelector('.owner-cell')?.textContent.trim();
                    const codeValue = row.querySelector('.code-cell')?.textContent.trim();
                    let show = false;

                    if (filter === "All") {
                        show = true;
                    } else if (filter === "Station-Owned" && owner === "RCH Water") {
                        show = true;
                    } else if (filter === "Customer-Owned" && owner !== "RCH Water") {
                        show = true;
                    } else if (filter === "Untracked" && (codeValue === "Generate QR")) {
                        show = true;
                    }

                    row.style.display = show ? "" : "none";
                });
            }

            // Automatically trigger "All" on load
            window.addEventListener("DOMContentLoaded", () => {
                document.getElementById("allTab").click();
            });


            // Status styling
            document.addEventListener("DOMContentLoaded", function() {
                const statusCells = document.querySelectorAll(".status-text");

                statusCells.forEach(cell => {
                    const text = cell.textContent.trim().toLowerCase();

                    if (text === "available") {
                        cell.className = "bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold";
                    } else if (text === "in-use") {
                        cell.className = "bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-semibold";
                    } else if (text === "damaged") {
                        cell.className = "bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold";
                    } else {
                        cell.className = "bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-xs font-semibold"; // fallback for unknown statuses
                    }
                });
            });
            // Search Function
            function searchTable() {
                const input = document.getElementById("searchInput");
                const filter = input.value.toLowerCase();
                const table = document.getElementById("gallonTable");
                const rows = table.getElementsByTagName("tr");

                for (let i = 1; i < rows.length; i++) { // skip header
                    const cells = rows[i].getElementsByTagName("td");
                    let match = false;

                    // If search is empty, show all rows
                    if (filter === "") {
                        rows[i].style.display = "";
                        continue;
                    }

                    // Otherwise, search the first 3 columns
                    for (let j = 0; j < 3; j++) {
                        if (cells[j] && cells[j].textContent.toLowerCase().includes(filter)) {
                            match = true;
                            break;
                        }
                    }

                    rows[i].style.display = match ? "" : "none";
                }
            }
            // Generate QR
            function generateQR(gallonID) {
                Swal.fire({
                    title: 'Generate QR Code?',
                    text: "Are you sure you want to generate a QR code for this gallon?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#9ca3af',
                    confirmButtonText: 'Yes, generate it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('generate_qr.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    'gallonID': gallonID
                                })
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        title: 'QR Code Generated!',
                                        html: `
                                                <div style="display:flex; flex-direction:column; align-items:center; gap:8px;">
                                                    <p style="margin:0;"><strong>Code Value:</strong> ${data.code_value}</p>
                                                    <img src="${data.image}"
                                                        alt="QR Code"
                                                        style="width:200px; height:200px; border-radius:8px;">
                                                </div>
                                            `,
                                        icon: 'success',
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        location.reload();
                                    });


                                } else {
                                    Swal.fire('Error', data.message || 'Unknown error', 'error');
                                }
                            })
                            .catch(err => {
                                console.error(err);
                                Swal.fire('Error', 'Request failed. Check your connection or console.', 'error');
                            });
                    }
                });
            }
            // VIEW FUNCTION
            function viewQR(ownerName, codeValue, qrImage) {
                if (!qrImage || qrImage === 'null') {
                    Swal.fire({
                        icon: 'info',
                        title: 'No QR Code',
                        html: `
                <p><strong>Owner:</strong> ${ownerName}</p>
                <p>No QR code has been generated yet for this gallon.</p>
            `,
                        confirmButtonColor: '#2563eb'
                    });
                } else {
                    const fileName = qrImage.split('/').pop();
                    Swal.fire({
                        title: 'QR Code Details',
                        html: `
                <div style="text-align:center;">
                    <p><strong>Owner:</strong> ${ownerName}</p>
                    <p><strong>Code Value:</strong> ${codeValue}</p>
                    <p><strong>File Name:</strong> ${fileName}</p>
                    <img src="${qrImage}" 
                         alt="QR Code" 
                         style="display:block; margin:10px auto; width:200px; height:200px; border-radius:8px;">
                </div>
            `,
                        icon: 'success',
                        confirmButtonColor: '#2563eb'
                    });
                }
            }


            function editGallon(id) {
                Swal.fire({
                    title: 'Edit Gallon Owner',
                    html: `
            <input type="text" id="ownerName" class="swal2-input" placeholder="Enter new owner name">
        `,
                    confirmButtonText: 'Save Changes',
                    showCancelButton: true,
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#9ca3af',
                    preConfirm: () => {
                        const ownerName = document.getElementById('ownerName').value.trim();
                        if (!ownerName) {
                            Swal.showValidationMessage('Owner name cannot be empty');
                            return false;
                        }
                        return {
                            id,
                            ownerName
                        };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const {
                            id,
                            ownerName
                        } = result.value;

                        // Send update request to backend
                        fetch('update_owner.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    id,
                                    ownerName
                                })
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Updated!',
                                        text: 'Owner name has been updated successfully.',
                                        confirmButtonColor: '#2563eb'
                                    }).then(() => location.reload());
                                } else {
                                    Swal.fire('Error', data.message || 'Failed to update owner name.', 'error');
                                }
                            })
                            .catch(err => {
                                console.error(err);
                                Swal.fire('Error', 'Request failed. Please check your connection.', 'error');
                            });
                    }
                });
            }



            // DELETE FUNCTION (with password confirmation)
            function deleteGallon(id) {
                Swal.fire({
                    title: 'Confirm Deletion',
                    html: `
            <p>Please enter admin password to confirm:</p>
            <input type="password" id="adminPass" class="swal2-input" placeholder="Enter password">
        `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#9ca3af',
                    confirmButtonText: 'Delete',
                    preConfirm: () => {
                        const pass = document.getElementById('adminPass').value;
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
                        fetch('delete_gallon.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(result.value)
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire('Deleted!', 'Gallon record has been removed.', 'success')
                                        .then(() => location.reload());
                                } else {
                                    Swal.fire('Error', data.message || 'Failed to delete record.', 'error');
                                }
                            })
                            .catch(() => Swal.fire('Error', 'Request failed.', 'error'));
                    }
                });
            }
        </script>
</body>

</html>