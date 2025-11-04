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
    <title>RCH Water - Customer Orders</title>
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
                        <a href="../GallonOwnership/gallon_ownership.php" class="flex items-center px-4 py-3 text-white rounded-lg font-medium hover:bg-blue-500">
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
                        <a href="../Customer/customers_list.php" class="flex items-center px-4 py-3 bg-blue-50 text-blue-600 rounded-lg font-medium">
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
                <!-- Header -->
                <div class="bg-white border-b border-gray-100 shadow-sm p-4 transition-all duration-300 ease-in-out">
                    <div class="flex items-center justify-between">
                        <h1 class="text-2xl font-semibold text-blue-500">Manage Customers</h1>

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
                    <!-- insert main content here -->

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
    </script>
</body>