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


<body class="bg-gray-50 overflow-hidden">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <script src="../Admin/includes/sidebar.js"></script>
        <!-- Main Content -->
        <div class="flex-1 ml-20 group-hover:ml-64 transition-all duration-300 ease-in-out">
            <div class="flex flex-col h-screen">
                <!-- Header -->
                <?php
                $pageTitle = "Gallon Ownership";
                $pageSubtitle = "Manage and track ownership of gallons between customers and the station.";
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
                                placeholder="Search by Gallon Owner, Gallon Type, Gallon ID, Status..."
                                class="w-full pl-10 pr-4 py-2 font-light border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder:text-sm"
                                style="font-style: italic;"
                                oninput="this.style.fontStyle = this.value ? 'normal' : 'italic';"
                                onkeyup="searchTable()">
                        </div>
                        <button
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

                    <!-- Content Area -->
                    <div class="p-6 bg-white rounded-lg shadow-lg ring-3 ring-gray-200 flex flex-col flex-1 overflow-hidden">
                        <!-- Filter Tabs -->
                        <div class="flex gap-3 mb-6 flex-wrap flex-shrink-0">

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
                        <div class="bg-white rounded-lg shadow-md overflow-hidden flex flex-col flex-1">
                            <!-- Scroll wrapper -->
                            <div class="overflow-x-auto overflow-y-auto flex-1">

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
                    } else if (text === "lost/damaged") {
                        cell.className = "bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold";
                    } else {
                        cell.className = "bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-xs font-semibold"; // fallback for unknown statuses
                    }
                });
            });
            // Add Gallon button
            document.querySelector('button').addEventListener('click', addGallon);

            function addGallon() {
                Swal.fire({
                    title: 'Add New Gallon',
                    html: `
            <label style="font-size: 14px; margin-bottom: 8px; display:block;">Select Gallon Type:</label>
            <select id="gallonTypeDropdown" class="swal2-select"
                style="width: 300px; padding:8px 10px; border-radius:6px; border:1px solid #e5e7eb; box-shadow: 0 0 0 4px rgba(156,163,175,0.25); transition: box-shadow .15s ease, border-color .15s ease; outline: none;"
                onfocus="this.style.boxShadow='0 0 0 4px rgba(59,130,246,0.25)'; this.style.borderColor='#2563eb';"
                onblur="this.style.boxShadow='0 0 0 4px rgba(156,163,175,0.25)'; this.style.borderColor='#e5e7eb';">
                <option value="1" data-type="18.9L Round Gallon">18.9L Round Gallon</option>
                <option value="2" data-type="20L Slim Gallon">20L Slim Gallon</option>
                <option value="11" data-type="10L Slim Gallon">10L Slim Gallon</option>
            </select>
        `,
                    confirmButtonText: 'Add Gallon',
                    showCancelButton: true,
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#9ca3af',
                    preConfirm: () => {
                        const dropdown = document.getElementById('gallonTypeDropdown');
                        const item_id = dropdown.value;
                        const gallon_type = dropdown.options[dropdown.selectedIndex].dataset.type;

                        return {
                            item_id,
                            gallon_type
                        };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const {
                            item_id,
                            gallon_type
                        } = result.value;

                        fetch('add_gallon.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    item_id,
                                    gallon_type
                                })
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Added!',
                                        text: 'New gallon added successfully.',
                                        confirmButtonColor: '#2563eb'
                                    }).then(() => location.reload());
                                } else {
                                    Swal.fire('Error', data.message || 'Failed to add gallon.', 'error');
                                }
                            })
                            .catch(err => {
                                console.error(err);
                                Swal.fire('Error', 'Request failed. Please check your connection.', 'error');
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
                // create modal overlay
                const modal = document.createElement('div');
                modal.className = "fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50";

                modal.innerHTML = `
                    <div class="bg-white rounded-xl shadow-lg w-96 p-6">
                        <h2 class="text-2xl font-semibold text-blue-600 mb-4 text-center">Edit Gallon Owner / Status</h2>
                        <form id="editGallonForm" class="flex flex-col items-center">
                            <label class="self-start text-left font-medium text-blue-500 mr-2 w-full">Owner Name:</label>
                            <input type="text" id="ownerName" class="w-full mx-auto border border-gray-300 rounded-md px-3 py-2 mb-4 focus:ring-2 focus:ring-blue-500 font-medium" placeholder="Enter new owner name" />

                            <div class="flex items-center gap-3 mb-6 w-full">
                                <label class="flex items-center gap-2 text-gray-700">
                                    <input type="checkbox" id="markDamaged" class="h-4 w-4" />
                                    <span class="text-sm">Mark as Damaged</span>
                                </label>
                            </div>

                            <div class="flex justify-center gap-3 w-full">
                                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md shadow text-sm font-medium">
                                    <i class="fas fa-save mr-1"></i>Save
                                </button>
                                <button type="button" id="cancelEditBtn" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded-md shadow text-sm font-medium">
                                    <i class="fas fa-times mr-1"></i>Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                `;

                document.body.appendChild(modal);
                modal.querySelector('#ownerName').focus();

                // Cancel handler - remove modal
                modal.querySelector('#cancelEditBtn').addEventListener('click', () => {
                    modal.remove();
                });

                // Submit handler
                modal.querySelector('#editGallonForm').addEventListener('submit', function(e) {
                    e.preventDefault();

                    const ownerName = modal.querySelector('#ownerName').value.trim();
                    const isDamaged = modal.querySelector('#markDamaged').checked;

                    if (!ownerName && !isDamaged) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Validation',
                            text: 'Please enter an owner name or mark as damaged.',
                            confirmButtonColor: '#2563eb'
                        });
                        return;
                    }

                    // Build payload
                    const payload = {
                        id
                    };
                    if (ownerName) payload.ownerName = ownerName;
                    if (isDamaged) payload.status = 'Lost/Damaged';

                    // Send update
                    fetch('update_changes.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Updated!',
                                    text: isDamaged ? 'Gallon marked as damaged successfully.' : 'Owner updated successfully.',
                                    confirmButtonColor: '#2563eb'
                                }).then(() => location.reload());
                            } else {
                                Swal.fire('Error', data.message || 'Failed to update gallon.', 'error');
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire('Error', 'Request failed. Please check your connection.', 'error');
                        })
                        .finally(() => {
                            modal.remove();
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
        </script>
</body>

</html>