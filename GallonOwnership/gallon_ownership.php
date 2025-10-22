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
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>


<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-gradient-to-br from-blue-400 to-blue-600 shadow-lg border-r border-gray-200 flex-shrink-0 fixed top-0 left-0 h-full overflow-y-auto flex flex-col text-white">
            <!-- Logo -->
            <div class="p-6 border-b border-blue-400">
                <div class="flex items-center gap-2">
                    <i class="fas fa-droplet text-2xl"></i>
                    <span class="text-2xl font-semibold">RCH Water</span>
                </div>
            </div>

            <nav class="mt-6 flex-1">
                <ul class="space-y-1 px-4">
                    <li>
                        <a href="#" class="flex items-center px-4 py-3 text-blue-600 bg-blue-50 rounded-lg font-medium">
                            <div class="w-5 h-5 mr-3 rounded-full bg-opacity-20 flex items-center justify-center text-lg">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <span class="font-medium">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center px-4 py-3 font-medium text-white hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                            <div class="w-5 h-5 mr-3 rounded-full bg-opacity-20 flex items-center justify-center text-lg">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <span class="font-medium">Orders</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center px-4 py-3 font-medium text-white hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                            <div class="w-5 h-5 mr-3 rounded-full bg-opacity-20 flex items-center justify-center text-lg">
                                <i class="fas fa-jug"></i>
                            </div>
                            <span class="font-medium">Gallon Ownership</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center px-4 py-3 font-medium text-white hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                            <div class="w-5 h-5 mr-3 rounded-full bg-opacity-20 flex items-center justify-center text-lg">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <span class="font-medium">Inventory</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center px-4 py-3 font-medium text-white hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                            <div class="w-5 h-5 mr-3 rounded-full bg-opacity-20 flex items-center justify-center text-lg">
                                <i class="fas fa-users"></i>
                            </div>
                            <span class="font-medium">Users</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center px-4 py-3 font-medium text-white hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                            <div class="w-5 h-5 mr-3 rounded-full bg-opacity-20 flex items-center justify-center text-lg">
                                <i class="fas fa-truck"></i>
                            </div>
                            <span class="font-medium">Deliveries</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center px-4 py-3 font-medium text-white hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                            <div class="w-5 h-5 mr-3 rounded-full bg-opacity-20 flex items-center justify-center text-lg">
                                <i class="fas fa-bell"></i>
                            </div>
                            <span class="font-medium">Notifications</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="border-t border-blue-400 p-4">
                <a href="#" class="flex items-center px-4 py-3 font-medium text-white hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                    <div class="w-5 h-5 mr-3 rounded-full bg-opacity-20 flex items-center justify-center text-lg">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                    <span class="font-medium">Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="main-content fixed w-full top-0 right-0 overflow-auto h-full">

            <div class="flex-1 ml-64 flex flex-col overflow-hidden">
                <div class="bg-white border-b border-gray-100 shadow-b shadow-sm p-4">
                    <div class="flex items-center justify-between">
                        <h1 class="text-2xl font-semibold text-blue-400">Gallon Ownership</h1>
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white cursor-pointer">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>

                </div>
                <div class="m-3">
                    <!-- Search and Buttons -->
                    <div class="flex flex-col md:flex-row gap-4 items-center mb-3">
                        <div class="flex-1 relative">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            <input type="text" id="searchInput" placeholder="Search by Gallon Owner, Gallon Type, Gallon ID..."
                                class="w-full pl-10 pr-4 py-2 font-light border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                style="font-style: italic;"
                                oninput="this.style.fontStyle = this.value ? 'normal' : 'italic';">
                        </div>
                        <button class="bg-gradient-to-br bg-blue-400 text-white px-6 py-2 rounded-lg font-semibold hover:shadow-lg transition">
                            Search
                        </button>
                        <button class="bg-gradient-to-br from-blue-400 to-blue-600 text-white px-6 py-2 rounded-lg font-semibold hover:shadow-lg transition flex items-center gap-2">
                            <i class="fas fa-qrcode"></i>
                            Scan QR Code
                        </button>
                    </div>

                    <!-- Content Area -->
                    <div class="p-6 bg-white rounded-lg shadow-lg ring-3 ring-gray-200 rounded-lg">
                        <!-- Filter Tabs -->
                        <!--  Converted .tab-button styles to Tailwind classes -->
                        <div class="flex gap-3 mb-6 flex-wrap">
                            <button class="px-4 py-2 rounded-full border-none cursor-pointer font-semibold transition-all duration-300 bg-blue-50 text-blue-500 hover:bg-gray-200 active:bg-blue-600 active:text-white" onclick="filterTab(this)">
                                <i class="fas fa-building mr-2"></i>Station-Owned
                            </button>
                            <button class="px-4 py-2 rounded-full border-none cursor-pointer font-semibold transition-all duration-300 bg-blue-50 text-blue-500 hover:bg-gray-200" onclick="filterTab(this)">
                                <i class="fas fa-user mr-2"></i>Customer-Owned
                            </button>
                            <button class="px-4 py-2 rounded-full border-none cursor-pointer font-semibold transition-all duration-300 bg-blue-50 text-blue-500 hover:bg-gray-200" onclick="filterTab(this)">
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
                                            <th class="px-6 py-4 font-semibold cursor-pointer" onclick="sortTable(0)">Gallon ID ⬍</th>
                                            <th class="px-6 py-4 font-semibold cursor-pointer" onclick="sortTable(1)">Gallon Type ⬍</th>
                                            <th class="px-6 py-4 font-semibold cursor-pointer" onclick="sortTable(2)">Current Owner ⬍</th>
                                            <th class="px-6 py-4 font-semibold">Status</th>
                                            <th class="px-6 py-4 font-semibold">Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="px-6 py-4">GAL-001</td>
                                            <td class="px-6 py-4">5 Gallon</td>
                                            <td class="px-6 py-4">John Smith</td>
                                            <td class="px-6 py-4">
                                                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">Active</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <i class="fas fa-eye mx-1 text-blue-600 hover:scale-125 transition-transform" title="View"></i>
                                                <i class="fas fa-edit mx-1 text-orange-500 hover:scale-125 transition-transform" title="Edit"></i>
                                                <i class="fas fa-trash mx-1 text-red-600 hover:scale-125 transition-transform" title="Delete"></i>
                                            </td>
                                        </tr>
                                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                                            <td class="px-6 py-4 text-sm text-gray-700">GAL-002</td>
                                            <td class="px-6 py-4 text-sm text-gray-700">10 Gallon</td>
                                            <td class="px-6 py-4 text-sm text-gray-700">Sarah Johnson</td>
                                            <td class="px-6 py-4 text-sm"><span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">In Transit</span></td>
                                            <td class="px-6 py-4 text-sm">
                                                <i class="fas fa-eye cursor-pointer transition-transform duration-200 hover:scale-125 mx-1 text-blue-600" title="View"></i>
                                                <i class="fas fa-edit cursor-pointer transition-transform duration-200 hover:scale-125 mx-1 text-orange-500" title="Edit"></i>
                                                <i class="fas fa-trash cursor-pointer transition-transform duration-200 hover:scale-125 mx-1 text-red-600" title="Delete"></i>
                                            </td>
                                        </tr>
                                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                                            <td class="px-6 py-4 text-sm text-gray-700">GAL-003</td>
                                            <td class="px-6 py-4 text-sm text-gray-700">5 Gallon</td>
                                            <td class="px-6 py-4 text-sm text-gray-700">Mike Davis</td>
                                            <td class="px-6 py-4 text-sm"><span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-semibold">Pending</span></td>
                                            <td class="px-6 py-4 text-sm">
                                                <i class="fas fa-eye cursor-pointer transition-transform duration-200 hover:scale-125 mx-1 text-blue-600" title="View"></i>
                                                <i class="fas fa-edit cursor-pointer transition-transform duration-200 hover:scale-125 mx-1 text-orange-500" title="Edit"></i>
                                                <i class="fas fa-trash cursor-pointer transition-transform duration-200 hover:scale-125 mx-1 text-red-600" title="Delete"></i>
                                            </td>
                                        </tr>
                                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                                            <td class="px-6 py-4 text-sm text-gray-700">GAL-004</td>
                                            <td class="px-6 py-4 text-sm text-gray-700">20 Gallon</td>
                                            <td class="px-6 py-4 text-sm text-gray-700">Emily Wilson</td>
                                            <td class="px-6 py-4 text-sm"><span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">Active</span></td>
                                            <td class="px-6 py-4 text-sm">
                                                <i class="fas fa-eye cursor-pointer transition-transform duration-200 hover:scale-125 mx-1 text-blue-600" title="View"></i>
                                                <i class="fas fa-edit cursor-pointer transition-transform duration-200 hover:scale-125 mx-1 text-orange-500" title="Edit"></i>
                                                <i class="fas fa-trash cursor-pointer transition-transform duration-200 hover:scale-125 mx-1 text-red-600" title="Delete"></i>
                                            </td>
                                        </tr>
                                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                                            <td class="px-6 py-4 text-sm text-gray-700">GAL-005</td>
                                            <td class="px-6 py-4 text-sm text-gray-700">5 Gallon</td>
                                            <td class="px-6 py-4 text-sm text-gray-700">Robert Brown</td>
                                            <td class="px-6 py-4 text-sm"><span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold">Inactive</span></td>
                                            <td class="px-6 py-4 text-sm">
                                                <i class="fas fa-eye cursor-pointer transition-transform duration-200 hover:scale-125 mx-1 text-blue-600" title="View"></i>
                                                <i class="fas fa-edit cursor-pointer transition-transform duration-200 hover:scale-125 mx-1 text-orange-500" title="Edit"></i>
                                                <i class="fas fa-trash cursor-pointer transition-transform duration-200 hover:scale-125 mx-1 text-red-600" title="Delete"></i>
                                            </td>
                                        </tr>
                                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                                            <td class="px-6 py-4 text-sm text-gray-700">GAL-006</td>
                                            <td class="px-6 py-4 text-sm text-gray-700">10 Gallon</td>
                                            <td class="px-6 py-4 text-sm text-gray-700">Lisa Anderson</td>
                                            <td class="px-6 py-4 text-sm"><span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">Active</span></td>
                                            <td class="px-6 py-4 text-sm">
                                                <i class="fas fa-eye cursor-pointer transition-transform duration-200 hover:scale-125 mx-1 text-blue-600" title="View"></i>
                                                <i class="fas fa-edit cursor-pointer transition-transform duration-200 hover:scale-125 mx-1 text-orange-500" title="Edit"></i>
                                                <i class="fas fa-trash cursor-pointer transition-transform duration-200 hover:scale-125 mx-1 text-red-600" title="Delete"></i>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Floating Inbox Button -->
                <div class="fixed bottom-7 right-7 w-16 h-16 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 text-white flex items-center justify-center cursor-pointer shadow-lg z-50 transition-transform duration-300 hover:scale-110" title="Inbox">
                    <i class="fas fa-envelope text-2xl"></i>
                </div>
            </div>
            <script>
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

                // Filter function
                function filterTab(button) {
                    // Update active tab
                    document.querySelectorAll('button[onclick="filterTab(this)"]').forEach(btn => {
                        btn.classList.remove('bg-blue-600', 'text-white');
                        btn.classList.add('bg-gray-100', 'text-gray-600');
                    });
                    button.classList.remove('bg-gray-100', 'text-gray-600');
                    button.classList.add('bg-blue-600', 'text-white');

                    // Filter logic would go here
                    console.log('Filtering by:', button.textContent.trim());
                }
            </script>

</body>
wwwe