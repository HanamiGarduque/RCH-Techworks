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


<body class="bg-gray-50 overflow-hidden">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <script src="../Admin/includes/sidebar.js"></script>

        <!-- Main Content -->

        <div class="flex-1 ml-20 group-hover:ml-64 transition-all duration-300 ease-in-out">
            <div class="flex flex-col h-screen">
                <!-- Header -->
                 <?php
                $pageTitle = "Order Management";
                $pageSubtitle = "View and manage customer orders and details.";
                include '../Admin/includes/header.php';
                ?>

                <div class="m-3 flex flex-col flex-1 overflow-hidden">
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