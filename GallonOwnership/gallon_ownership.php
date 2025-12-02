<?php
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('admin'); // only admins can access

require_once '../Database/db_connection.php';

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

        /* Ensure the page layout uses full viewport height */
        .page-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Main content area that will scroll */
        .content-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Table container with scroll */
        .table-scroll-container {
            overflow-y: auto;
            overflow-x: auto;
            flex: 1;
        }

        /* Custom scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>

<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <script src="../Admin/includes/sidebar.js"></script>

        <div class="flex-1 ml-20 group-hover:ml-64 transition-all duration-300 ease-in-out overflow-hidden">
            <div class="page-container">
                <!-- Header - Fixed -->
                <?php
                $pageTitle = "Gallon Ownership";
                $pageSubtitle = "Manage and track ownership of gallons between customers and the station.";
                include '../Admin/includes/header.php';
                ?>

                <!-- Content Wrapper -->
                <div class="content-wrapper p-3">
                    <!-- Search and Buttons - Fixed -->
                    <div class="flex items-center gap-2 mb-4 flex-shrink-0">
                        <div class="flex-1 relative">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            <input
                                type="text"
                                id="searchInput"
                                placeholder="Search by Gallon Owner, Gallon Type, Gallon ID, Status..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                oninput="searchTable()">


                        </div>
                        <button
                            id="addGallonBtn"
                            class="bg-gradient-to-br from-blue-400 to-blue-600 hover:from-blue-500 hover:to-blue-700 text-white px-6 py-2 rounded-lg font-semibold shadow-md transition">
                            <i class="fas fa-plus mr-2"></i>
                            Add Gallon
                        </button>

                        <a href="qr_scanner.php" id="scanBtn"
                            class="inline-flex items-center bg-gradient-to-br from-blue-400 to-blue-600 hover:from-blue-500 hover:to-blue-700 text-white px-6 py-2 rounded-lg font-semibold shadow-md transition">
                            <i class="fas fa-qrcode mr-2"></i>
                            View QR Codes
                        </a>
                    </div>

                    <!-- Card Container - Flexible -->
                    <div class=" p-6 bg-white rounded-lg shadow-lg ring-3 ring-gray-200 flex flex-col overflow-hidden flex-1">
                        <!-- Filter Tabs -->
                        <div class="flex gap-3 mb-6 flex-wrap">

                            <button id="allTab"
                                class="px-4 py-2 rounded-full border-none cursor-pointer font-semibold transition-all duration-300 bg-blue-600 text-white"
                                onclick="filterTab(this)">
                                <i class="fas fa-list mr-2"></i>All Gallons
                            </button>

                            <button
                                class="px-4 py-2 rounded-full border-none cursor-pointer font-semibold transition-all duration-300 bg-blue-50 text-blue-500 hover:bg-gray-200"
                                onclick="filterTab(this)">
                                <i class="fas fa-warehouse mr-2"></i>Station Inventory
                            </button>

                            <button
                                class="px-4 py-2 rounded-full border-none cursor-pointer font-semibold transition-all duration-300 bg-blue-50 text-blue-500 hover:bg-gray-200"
                                onclick="filterTab(this)">
                                <i class="fas fa-user-check mr-2"></i>With Customer
                            </button>

                            <button
                                class="px-4 py-2 rounded-full border-none cursor-pointer font-semibold transition-all duration-300 bg-blue-50 text-blue-500 hover:bg-gray-200"
                                onclick="filterTab(this)">
                                <i class="fas fa-question-circle mr-2"></i>Untracked
                            </button>

                        </div>


                        <!-- Table with Scrollable Body -->
                        <div class="pb-6 flex flex-col overflow-hidden flex-1">
                            <div class="bg-white rounded-lg shadow-md overflow-hidden flex flex-col flex-1">
                                <!-- Scrollable table container -->
                                <div class="table-scroll-container">
                                    <table id="gallonTable" class="min-w-full text-sm text-left text-gray-700">
                                        <thead class="bg-blue-50 border-b border-gray-200 sticky top-0 z-10">
                                            <tr>
                                                <th class="px-6 py-4 font-semibold text-center cursor-pointer min-w-[80px]" onclick="sortTable(0)" title="Gallon ID">Gallon ID ⬍</th>
                                                <th class="px-6 py-4 font-semibold text-center cursor-pointer min-w-[110px]" onclick="sortTable(1)" title="Gallon Type">Gallon Type ⬍</th>
                                                <th class="px-6 py-4 font-semibold text-center cursor-pointer min-w-[220px]" onclick="sortTable(2)" title="Owner">Current Owner ⬍</th>
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
                                            LEFT JOIN users AS u ON go.current_owner_id = u.user_id
                                            ORDER BY `Gallon ID` DESC";

                                            $stmt = $db->prepare($query);
                                            $stmt->execute();

                                            $num = $stmt->rowCount();

                                            if ($num > 0) {
                                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                    $statusClass = $row['Status'] === 'banned' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700';

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

                                                    $qrImage = htmlspecialchars($row['QR Image'] ?? '');
                                                    $codeValue = htmlspecialchars($row['Code Value'] ?? '');
                                                    $gallonID = htmlspecialchars($row['Gallon ID']);
                                                    $ownerName = htmlspecialchars($row['Owner'] ?? 'N/A');
                                                    $gallonType = htmlspecialchars($row['Gallon Type'] ?? 'N/A');


                                                    echo '<td class="px-6 py-4 text-center">';
                                                    echo "<i class=\"fas fa-eye mx-1 text-blue-600 hover:scale-125 transition-transform\" title=\"View\" style=\"cursor:pointer;\" onclick=\"viewQR('{$ownerName}', '{$codeValue}', '{$qrImage}', '{$gallonType}')\"></i>";
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
            <script src="..Admin/includes/admin_toast.js"></script>

            <script>
                // for sorting table
                function sortTable(columnIndex) {
                    const table = document.getElementById("gallonTable");
                    const tbody = table.querySelector("tbody");
                    const rows = Array.from(tbody.querySelectorAll("tr"));

                    // Determine sort direction: toggle if same column, otherwise default to ascending
                    const prevCol = table.dataset.sortedColumn;
                    const isAscending = (prevCol === String(columnIndex)) ? table.dataset.sortOrder !== "asc" : true;

                    rows.sort((a, b) => {
                        const aText = a.children[columnIndex].innerText.trim();
                        const bText = b.children[columnIndex].innerText.trim();

                        // Numeric sort for Gallon ID (column 0)
                        if (columnIndex === 0) {
                            const aNum = parseFloat(aText.replace(/[^0-9\.\-]/g, ''));
                            const bNum = parseFloat(bText.replace(/[^0-9\.\-]/g, ''));
                            if (!isNaN(aNum) && !isNaN(bNum)) {
                                return isAscending ? aNum - bNum : bNum - aNum;
                            }
                        }

                        // Fallback to string comparison
                        return isAscending ? aText.localeCompare(bText) : bText.localeCompare(aText);
                    });

                    tbody.innerHTML = "";
                    rows.forEach(row => tbody.appendChild(row));

                    table.dataset.sortOrder = isAscending ? "asc" : "desc";
                    table.dataset.sortedColumn = String(columnIndex);
                }
                // Filter Function
                function filterTab(button) {
                    document.querySelectorAll('button[onclick="filterTab(this)"]').forEach(btn => {
                        btn.classList.remove('bg-blue-600', 'text-white', 'no-hover');
                        btn.classList.add('bg-blue-50', 'text-blue-500', 'hover:bg-gray-200');
                    });

                    button.classList.remove('bg-blue-50', 'text-blue-500', 'hover:bg-gray-200');
                    button.classList.add('bg-blue-600', 'text-white', 'no-hover');

                    const filter = button.textContent.trim();
                    const rows = document.querySelectorAll('#gallonTable tbody tr');

                    rows.forEach(row => {
                        const owner = row.querySelector('.owner-cell')?.textContent.trim();
                        const codeValue = row.querySelector('.code-cell')?.textContent.trim();
                        let show = false;

                        if (filter === "All Gallons") {
                            show = true;
                        } else if (filter === "Station Inventory" && owner === "RCH Water") {
                            show = true;
                        } else if (filter === "With Customer" && owner !== "RCH Water") {
                            show = true;
                        } else if (filter === "Untracked" && codeValue === "Generate QR") {
                            show = true;
                        }

                        row.style.display = show ? "" : "none";
                    });
                }

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
                        } else if (text === "lost/damaged") {
                            cell.className = "bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold";
                        } else {
                            cell.className = "bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-xs font-semibold"; // fallback for unknown statuses
                        }
                    });
                });
                // Add Gallon button
                // Add this BEFORE the button click handler
                document.addEventListener("DOMContentLoaded", () => {
                    console.log("Swal available:", typeof Swal !== 'undefined'); // Debug
                    const addBtn = document.getElementById('addGallonBtn');
                    if (addBtn) {
                        addBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            console.log("[Add Gallon clicked"); // Debug
                            if (typeof Swal !== 'undefined') {
                                addGallon();
                            } else {
                                console.error("[v0] Swal is not defined!");
                                alert('Error: SweetAlert2 library not loaded');
                            }
                        });
                    } else {
                        console.error("[ addGallonBtn not found");
                    }
                });


                function addGallon() {
                    Swal.fire({
                        title: 'Add New Gallon',
                        html: `
            <div style="text-align:left; padding: 10px 5px;">
                
                <!-- Gallon Type -->
                <label style="font-size: 14px; font-weight: 500; margin-bottom: 8px; display:block; color: #374151;">
                    Select Gallon Type
                </label>
                <select id="gallonTypeDropdown" 
                    style="width: 100%; padding: 10px 12px; border-radius: 8px; 
                    border: 1px solid #d1d5db; font-size: 14px;
                    background-color: white; color: #1f2937;
                    transition: all 0.2s ease; outline: none; cursor: pointer;
                    appearance: none; background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27currentColor%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3e%3cpolyline points=%276 9 12 15 18 9%27%3e%3c/polyline%3e%3c/svg%3e');
                    background-repeat: no-repeat; background-position: right 10px center; background-size: 20px;
                    padding-right: 40px;"
                    onmouseover="this.style.borderColor='#2563eb'"
                    onmouseout="this.style.borderColor='#d1d5db'"
                    onfocus="this.style.borderColor='#2563eb'; this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.1)'"
                    onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'">
                    <option value="1" data-type="18.9L Round Gallon">18.9L Round Gallon</option>
                    <option value="2" data-type="20L Slim Gallon">20L Slim Gallon</option>
                    <option value="11" data-type="10L Slim Gallon">10L Slim Gallon</option>
                </select>

                <!-- Quantity Section -->
                <label style="font-size: 14px; font-weight: 500; margin-top: 20px; margin-bottom: 8px; display:block; color: #374151;">
                    Quantity
                </label>
                <div style="display: flex; align-items: center; justify-content: center; gap: 12px;">

                    <button id="qtyMinus" type="button"
                        style="width: 44px; height: 44px; border: 1px solid #d1d5db; border-radius: 8px;
                        background: white; font-size: 20px; cursor: pointer; 
                        color: #6b7280; font-weight: 600; transition: all 0.2s ease;
                        display: flex; align-items: center; justify-content: center;"
                        onmouseover="this.style.background='#f3f4f6'; this.style.color='#374151'; this.style.borderColor='#9ca3af'"
                        onmouseout="this.style.background='white'; this.style.color='#6b7280'; this.style.borderColor='#d1d5db'">−</button>

                    <input id="qtyInput" type="number" value="1" min="1"
                        style="width: 80px; text-align: center; padding: 10px; border-radius: 8px;
                        border: 1px solid #d1d5db; font-size: 16px; font-weight: 600;
                        color: #1f2937; transition: all 0.2s ease; outline: none;"
                        onfocus="this.style.borderColor='#2563eb'; this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.1)'"
                        onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'">

                    <button id="qtyPlus" type="button"
                        style="width: 44px; height: 44px; border: 1px solid #d1d5db; border-radius: 8px;
                        background: white; font-size: 20px; cursor: pointer;
                        color: #6b7280; font-weight: 600; transition: all 0.2s ease;
                        display: flex; align-items: center; justify-content: center;"
                        onmouseover="this.style.background='#f3f4f6'; this.style.color='#374151'; this.style.borderColor='#9ca3af'"
                        onmouseout="this.style.background='white'; this.style.color='#6b7280'; this.style.borderColor='#d1d5db'">+</button>
                </div>

            </div>
        `,
                        width: 450,
                        padding: '25px',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-plus" style="margin-right: 8px;"></i>Add Gallon',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#2563eb',
                        cancelButtonColor: '#6b7280',
                        buttonsStyling: true,
                        customClass: {
                            popup: 'swal-no-overflow',
                            htmlContainer: 'swal-html-container',
                            title: 'swal-title-custom'
                        },

                        didOpen: () => {
                            // Add custom styles to remove scrollbar
                            const style = document.createElement('style');
                            style.textContent = `
                .swal-no-overflow {
                    overflow: hidden !important;
                }
                .swal-html-container {
                    overflow: visible !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }
                .swal-title-custom {
                    font-size: 22px !important;
                    font-weight: 600 !important;
                    color: #1f2937 !important;
                    margin-bottom: 20px !important;
                }
                .swal2-confirm, .swal2-cancel {
                    font-weight: 600 !important;
                    padding: 10px 24px !important;
                    border-radius: 8px !important;
                    font-size: 14px !important;
                }
            `;
                            document.head.appendChild(style);
                            const qtyInput = document.getElementById('qtyInput');
                            const plus = document.getElementById('qtyPlus');
                            const minus = document.getElementById('qtyMinus');

                            plus.addEventListener('click', () => {
                                let val = parseInt(qtyInput.value);
                                if (val < 10) qtyInput.value = val + 1; // LIMIT TO 10
                            });

                            minus.addEventListener('click', () => {
                                let val = parseInt(qtyInput.value);
                                if (val > 1) qtyInput.value = val - 1;
                            });

                            // Prevent typing more than 10
                            qtyInput.addEventListener('input', () => {
                                let val = parseInt(qtyInput.value);
                                if (val > 10) qtyInput.value = 10; // FORCE MAX = 10
                                if (val < 1 || isNaN(val)) qtyInput.value = 1;
                            });

                            // Disable mouse scroll
                            qtyInput.addEventListener('wheel', (e) => e.preventDefault());

                        },

                        preConfirm: () => {
                            const dropdown = document.getElementById('gallonTypeDropdown');
                            const item_id = dropdown.value;
                            const gallon_type = dropdown.options[dropdown.selectedIndex].dataset.type;
                            const quantity = parseInt(document.getElementById('qtyInput').value);

                            if (!item_id || quantity < 1 || quantity > 10) {
                                Swal.showValidationMessage('Quantity must be between 1 and 10');
                                return false;
                            }

                            return {
                                item_id,
                                gallon_type,
                                quantity
                            };
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const {
                                item_id,
                                gallon_type,
                                quantity
                            } = result.value;

                            fetch('add_gallon.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        item_id,
                                        gallon_type,
                                        quantity
                                    })
                                })
                                .then(res => res.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Added!',
                                            text: 'New gallon(s) added successfully.',
                                            confirmButtonColor: '#2563eb',
                                            confirmButtonText: 'OK'
                                        }).then(() => location.reload());
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: data.message || 'Failed to add gallon.',
                                            confirmButtonColor: '#dc2626'
                                        });
                                    }
                                })
                                .catch(err => {
                                    console.error(err);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'Request failed. Please check your connection.',
                                        confirmButtonColor: '#dc2626'
                                    });
                                });
                        }
                    });
                }
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

                        for (let j = 0; j < cells.length - 1; j++) {
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
                function viewQR(ownerName, codeValue, qrImage, gallonType) {
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
                    <p><strong>Gallon Type:</strong> ${gallonType}</p>
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
                    // Fetch current gallon status + code_value
                    fetch(`get_gallon_status.php?id=${id}`)
                        .then(res => res.json())
                        .then(gallonData => {
                            const currentStatus = gallonData.data.status;
                            const hasCode = gallonData.data.code_value && gallonData.data.code_value.trim() !== "";

                            // Modal container
                            const modal = document.createElement('div');
                            modal.className = "fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50";
                            modal.style.animation = "fadeIn 0.2s ease-out";

                            modal.innerHTML = `
        <div class="bg-white rounded-xl shadow-2xl w-96 overflow-hidden" style="animation: slideIn 0.3s ease-out;">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                <h2 class="text-xl font-semibold text-white text-center">
                    <i class="fas fa-edit mr-2"></i>Update Gallon Status
                </h2>
            </div>

            <!-- Content -->
            <form id="editGallonForm" class="p-6">

                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Select Gallon Status
                </label>

                <!-- Available -->
                <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                    <input type="radio" name="gallonStatus" value="Available"
                        ${currentStatus === "Available" ? "checked" : ""} class="mt-1">
                    <div>
                        <p class="font-medium text-gray-900">Available</p>
                        <p class="text-xs text-gray-500">The gallon is unused and ready for deployment.</p>
                    </div>
                </label>

                <!-- In-Use -->
                <label class="flex items-start gap-3 p-3 mt-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                    <input type="radio" name="gallonStatus" value="In-Use"
                        ${currentStatus === "In-Use" ? "checked" : ""} class="mt-1">
                    <div>
                        <p class="font-medium text-gray-900">In-Use</p>
                        <p class="text-xs text-gray-500">The gallon is currently with a customer.</p>
                    </div>
                </label>

                <!-- Lost/Damaged -->
                <label class="flex items-start gap-3 p-3 mt-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                    <input type="radio" name="gallonStatus" value="Lost/Damaged"
                        ${currentStatus === "Lost/Damaged" ? "checked" : ""} class="mt-1">
                    <div>
                        <p class="font-medium text-gray-900">Lost/Damaged</p>
                        <p class="text-xs text-gray-500">Mark only if the gallon is lost or unusable.</p>
                    </div>
                </label>

                ${!hasCode ? `
                <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-xs text-red-700">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        ⚠ This gallon does not have a code yet. Generate a code first.
                    </p>
                </div>` : ""}

                <!-- Buttons -->
                <div class="flex gap-3 mt-6">
                    <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg shadow-md font-medium text-sm transition-all">
                        <i class="fas fa-check mr-2"></i>Update Status
                    </button>

                    <button type="button" id="cancelEditBtn"
                        class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2.5 rounded-lg shadow-md font-medium text-sm transition-all">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                </div>

            </form>
        </div>

        <style>
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes slideIn {
                from { transform: scale(0.95) translateY(-20px); opacity: 0; }
                to { transform: scale(1) translateY(0); opacity: 1; }
            }
        </style>
        `;

                            document.body.appendChild(modal);

                            // Cancel button
                            modal.querySelector('#cancelEditBtn').addEventListener('click', () => modal.remove());

                            // Submit handler
                            modal.querySelector('#editGallonForm').addEventListener('submit', function(e) {
                                e.preventDefault();

                                const newStatus = modal.querySelector('input[name="gallonStatus"]:checked').value;

                                // Prevent marking Lost/Damaged without QR code
                                if (newStatus === "Lost/Damaged" && !hasCode) {
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Code Needed',
                                        text: 'Cannot mark as Lost/Damaged without a code.',
                                        confirmButtonColor: '#e63946'
                                    });
                                    return;
                                }

                                // Nothing changed?
                                if (newStatus === currentStatus) {
                                    Swal.fire({
                                        icon: 'info',
                                        title: 'No Changes',
                                        text: 'Status remains the same.',
                                        confirmButtonColor: '#2563eb'
                                    });
                                    modal.remove();
                                    return;
                                }

                                // Send update request
                                fetch("update_changes.php", {
                                        method: "POST",
                                        headers: {
                                            "Content-Type": "application/json"
                                        },
                                        body: JSON.stringify({
                                            id: id,
                                            status: newStatus
                                        })
                                    })
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.success) {
                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Updated!',
                                                text: `Gallon status updated to: ${newStatus}`,
                                                confirmButtonColor: '#2563eb'
                                            }).then(() => location.reload());
                                        } else {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Update Failed',
                                                text: data.message || 'Please try again.',
                                                confirmButtonColor: '#dc2626'
                                            });
                                        }
                                    })
                                    .finally(() => modal.remove());
                            });
                        });
                }


                // DELETE FUNCTION 
                function deleteGallon(id) {
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