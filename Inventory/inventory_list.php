<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Refilling Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>


<body class="bg-gray-50 font-sans">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-gradient-to-br from-blue-400 via-blue-500 to-blue-600 shadow-sm border-r border-gray-200 flex-shrink-0 fixed top-0 left-0 h-full overflow-y-auto flex flex-col">
            <!-- Logo -->
            <div class="flex items-center gap-3 m-4">
                <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                    <path d="M20 2C20 2 8 14 8 22C8 28.6274 13.3726 34 20 34C26.6274 34 32 28.6274 32 22C32 14 20 2 20 2Z" fill="white" stroke="white" stroke-width="2" />
                    <circle cx="16" cy="20" r="2" fill="#3b82f6" opacity="0.5" />
                </svg>
                <span class="text-2xl font-bold text-white">RCH Water</span>
            </div>

            <nav class="mt-6 flex-1">
                <ul class="space-y-1 px-4">
                    <li>
                        <a href="#" class="flex items-center px-4 py-3 text-blue-600 bg-blue-50 rounded-lg font-medium">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                            </svg>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center px-4 py-3 font-medium text-white hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2L3 7v11a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V7l-7-5z" clip-rule="evenodd"></path>
                            </svg>
                            Orders
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center px-4 py-3 font-medium text-white hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                            </svg>
                            Customers
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center px-4 py-3 font-medium text-white hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Gallons
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center px-4 py-3 font-medium text-white hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                            </svg>
                            Delivery Slots
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center px-4 py-3 font-medium text-white hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
                            </svg>
                            Reports
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center px-4 py-3 font-medium text-white hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path>
                            </svg>
                            Settings
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="border-t border-blue-400 p-4">
                <a href="#" class="flex items-center px-4 py-3 font-medium text-white hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"></path>
                    </svg>
                    Logout
                </a>
            </div>
        </aside>
        <div class="flex-1 flex flex-col overflow-hidden">
            <main class="flex-1 ml-64 p-6 overflow-y-auto">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-blue-4   00">Inventory Management</h1>
                    <button class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        + Add Item
                    </button>
                </div>

                <!-- Inventory Table -->
                <h2 class="text-lg font-semibold mb-3">Inventory List</h2>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead class="bg-blue-100">
                            <tr>
                                <th class="p-3 text-left">Item Name</th>
                                <th class="p-3 text-left">Category</th>
                                <th class="p-3 text-left">Quantity</th>
                                <th class="p-3 text-left">Status</th>
                                <th class="p-3 text-left">QR Code</th>
                                <th class="p-3 text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-3">5-Gallon Water</td>
                                <td class="p-3">Gallons</td>
                                <td class="p-3">50</td>
                                <td class="p-3 text-green-600 font-medium">Available</td>
                                <td class="p-3">
                                    <button class="text-blue-600 hover:underline">View</button>
                                </td>
                                <td class="p-3 flex gap-2">
                                    <button class="text-yellow-500">Edit</button>
                                    <button class="text-red-500">Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    </div>
</body>

</html>