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
    <title>RCH Water - Notifications </title>
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
                $pageTitle = "Notifications";
                $pageSubtitle = "Stay updated with the latest alerts and messages.";
                include '../Admin/includes/header.php';
                ?>

                <div class="m-3 flex flex-col flex-1 overflow-hidden">
                    <div class="w-full mx-auto space-y-4" id="notifications-list">
                        <!-- Notification items will be added here -->
                    </div>

                </div>
            </div>

        </div>

    </div>
    </div>
    <script>
        // Sample notifications data
        function fetchNotifications() {
            $.ajax({
                url: 'get_notifications.php', // we'll create this
                method: 'GET',
                dataType: 'json',
                success: function(notifications) {
                    renderNotifications(notifications);
                },
                error: function(err) {
                    console.error('Error fetching notifications:', err);
                    $('#notifications-list').html('<p class="text-center text-gray-500 py-8">Failed to load notifications.</p>');
                }
            });
        }


        // Render notifications
        function renderNotifications(notifications) {
            const container = document.getElementById('notifications-list');
            container.innerHTML = '';

            if (notifications.length === 0) {
                container.innerHTML = '<p class="text-center text-gray-500 py-8">No notifications</p>';
                return;
            }

            notifications.forEach(notif => {

                const uniformCard = "bg-white border-gray-200 hover:bg-gray-100 shadow-sm";
                const uniformIcon = "text-blue-500";

                const item = document.createElement('div');
                item.className = `
            border-l-8 p-4 rounded border-blue-600 
            ${uniformCard} 
            ${notif.status === 'read' ? 'opacity-75' : ''} 
            cursor-pointer hover:shadow-md transition notification-item
        `;
                item.dataset.id = notif.notification_id;

                item.innerHTML = `
            <div class="flex items-start justify-between">
                <div class="flex items-start space-x-3 flex-1">
                    <i class="fas fa-bell ${uniformIcon} mt-1"></i>
                    <div class="flex-1">
                        <h3 class="font-semibold text-lg text-blue-500">${notif.title}</h3>
                        <p class="text-gray-700 text-sm mt-1">${notif.message}</p>
                        <p class="text-gray-600 text-xs mt-2">${notif.created_at}</p>
                    </div>
                </div>
                <button class="ml-4 text-gray-400 hover:text-gray-600 delete-btn" title="Delete">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

                // Mark as read + redirect
                item.addEventListener('click', () => {
                    markAsRead(notif.notification_id, notif.link);
                });

                // Delete
                item.querySelector('.delete-btn').addEventListener('click', (e) => {
                    e.stopPropagation();
                    deleteNotification(notif.notification_id);
                });

                container.appendChild(item);
            });
        }



        function markAsRead(id, link) {
            $.ajax({
                url: 'mark_notification_read.php',
                method: 'POST',
                data: {
                    notification_id: id
                },
                success: function() {
                    // refresh notifications
                    fetchNotifications();
                    if (link) {
                        window.location.href = link; // redirect if link exists
                    }
                },
                error: function(err) {
                    console.error('Error marking notification as read:', err);
                }
            });
        }


        function deleteNotification(id) {
            $.ajax({
                url: 'delete_notification.php',
                method: 'POST',
                data: {
                    notification_id: id
                },
                success: function() {
                    fetchNotifications();
                },
                error: function(err) {
                    console.error('Error deleting notification:', err);
                }
            });
        }
        $(document).ready(function() {
            fetchNotifications();
        });
    </script>
</body>

</html>