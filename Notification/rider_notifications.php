<?php
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('rider');
require_once '../Database/db_connection.php';

$database = new Database();
$db = $database->getConnect();

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$projectRoot = dirname(dirname($_SERVER['SCRIPT_NAME']));
$baseUrl = $protocol . "://" . $host . $projectRoot . '/';

$rider_name = $_SESSION['name'] ?? "Rider";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | RCH Water</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        /* Custom scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Unread notification pulse */
        @keyframes pulse-glow {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4);
            }

            50% {
                box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            }
        }

        .notification-unread {
            animation: pulse-glow 2s ease-in-out infinite;
        }
    </style>
</head>

<body class="bg-white min-h-screen flex justify-center items-center">

    <!-- Fixed-width container wrapper -->
    <div class="bg-white w-[375px] h-[750px] mx-auto relative rounded-3xl overflow-hidden border shadow-lg">

        <!-- Header -->
        <div class="bg-gradient-to-br from-blue-400 to-blue-600 text-white sticky top-0 z-50 w-full px-4 py-3">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-bold tracking-tight">Notifications</h2>
                    <div id="datetime" class="text-xs text-blue-100 font-medium"></div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="../Rider/dashboard.php"
                        class="text-white text-lg hover:scale-110 transition-transform duration-200">
                        <i class="bi bi-arrow-left-circle"></i>
                    </a>
                    <a href="../Messaging/rider_messages.php" class="relative text-white text-lg hover:scale-110 transition-transform duration-200">
                        <i class="bi bi-chat-dots"></i>
                    </a>
                    <a href="../Rider/edit_profile.php"
                        class="text-white text-lg hover:scale-110 transition-transform duration-200">
                        <i class="bi bi-person-circle"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="bg-white sticky top-16 z-40 w-full px-5 py-4">
            <div class="grid grid-cols-3 gap-3 mb-3">
                <button class="filter-tab bg-blue-500 text-white font-semibold py-2 px-4 rounded-3xl text-xs shadow-sm transition-all duration-200" data-filter="all">
                    All
                </button>
                <button class="filter-tab bg-blue-100 text-blue-600 font-semibold py-2 px-4 rounded-3xl text-xs shadow-sm transition-all duration-200" data-filter="unread">
                    Unread
                </button>
                <button class="filter-tab bg-blue-100 text-blue-600 font-semibold py-2 px-4 rounded-3xl text-xs shadow-sm transition-all duration-200" data-filter="read">
                    Read
                </button>
            </div>
            <button id="markAllRead" class="w-full py-2 rounded-3xl font-semibold text-xs transition-all bg-green-500 text-white hover:bg-green-600 shadow-sm">
                <i class="bi bi-check-double mr-1"></i>Mark All as Read
            </button>
        </div>

        <!-- Notifications List -->
        <div class="px-5 pb-5">
            <div id="notifications-list" class="bg-gray-100 p-2 rounded-md shadow-inner space-y-3 overflow-y-auto custom-scrollbar h-[500px] pb-5 pr-2">
                <!-- Notification items will be added here -->
            </div>
        </div>

    </div>

    <script>
        const baseUrl = "<?= $baseUrl ?>";
        let currentFilter = 'all';

        // Month names
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        function updateDateTime() {
            const now = new Date();
            const month = monthNames[now.getMonth()];
            const day = String(now.getDate()).padStart(2, '0');
            const year = now.getFullYear();
            let hours = now.getHours();
            const minutes = String(now.getMinutes()).padStart(2, '0');

            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12;
            const hoursFormatted = String(hours).padStart(2, '0');

            document.getElementById('datetime').innerText = `${month} ${day}, ${year} • ${hoursFormatted}:${minutes} ${ampm}`;
        }

        updateDateTime();
        setInterval(updateDateTime, 60000);

        function fetchNotifications() {
            $.ajax({
                url: 'get_notifications.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('Response:', response);

                    if (response.error) {
                        console.error('Error from server:', response.error);
                        $('#notifications-list').html(`
                            <div class="text-center py-8">
                                <i class="bi bi-exclamation-triangle text-6xl text-red-300 mb-4"></i>
                                <p class="text-red-500 font-semibold text-sm">Debug Info:</p>
                                <p class="text-gray-600 text-xs">${response.error}</p>
                            </div>
                        `);
                        return;
                    }

                    renderNotifications(response.notifications || response);
                },
                error: function(err) {
                    console.error('Error fetching notifications:', err);
                    $('#notifications-list').html(`
                        <div class="text-center py-8">
                            <i class="bi bi-wifi-off text-6xl text-red-300 mb-4"></i>
                            <p class="text-red-500 font-semibold text-sm">Failed to load notifications</p>
                            <p class="text-gray-500 text-xs mt-2">Check your connection</p>
                        </div>
                    `);
                }
            });
        }

        function renderNotifications(notifications) {
            const container = document.getElementById('notifications-list');
            container.innerHTML = '';

            if (notifications.length === 0) {
                container.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-full py-12">
                        <i class="bi bi-bell-slash text-6xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 text-sm">No notifications</p>
                    </div>
                `;
                return;
            }

            // Filter notifications
            let filteredNotifications = notifications;
            if (currentFilter === 'unread') {
                filteredNotifications = notifications.filter(n => n.status === 'unread');
            } else if (currentFilter === 'read') {
                filteredNotifications = notifications.filter(n => n.status === 'read');
            }

            if (filteredNotifications.length === 0) {
                container.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-full py-12">
                        <i class="bi bi-inbox text-6xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 text-sm">No ${currentFilter} notifications</p>
                    </div>
                `;
                return;
            }

            filteredNotifications.forEach(notif => {
                const isUnread = notif.status === 'unread';

                const item = document.createElement('div');
                item.className = `
                    notification-item bg-white border rounded-xl p-4 shadow-sm transition-all duration-300 cursor-pointer relative
                    ${isUnread ? 'border-blue-500 notification-unread' : 'border-gray-200 opacity-70'}
                `;
                item.dataset.id = notif.notification_id;
                item.dataset.status = notif.status;

                item.innerHTML = `
                    <div class="flex items-start space-x-3">
                        <!-- Icon -->
                        <div class="flex-shrink-0">
                            <div class="${isUnread ? 'bg-blue-500' : 'bg-gray-400'} w-10 h-10 rounded-full flex items-center justify-center">
                                <i class="bi bi-bell text-white"></i>
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between mb-1">
                                <h3 class="font-bold text-sm ${isUnread ? 'text-blue-700' : 'text-gray-600'} truncate">
                                    ${notif.title}
                                </h3>
                                ${isUnread ? '<span class="bg-blue-500 text-white text-xs font-bold px-2 py-0.5 rounded-full ml-2 flex-shrink-0">NEW</span>' : ''}
                            </div>
                            <p class="${isUnread ? 'text-gray-800 font-medium' : 'text-gray-600'} text-xs mb-2 line-clamp-2">
                                ${notif.message}
                            </p>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1 text-xs ${isUnread ? 'text-gray-600' : 'text-gray-400'}">
                                    <i class="bi bi-clock"></i>
                                    <span>${notif.created_at}</span>
                                </div>
                                <button class="delete-btn text-gray-400 hover:text-red-600 transition-colors p-1" title="Delete">
                                    <i class="bi bi-trash text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;

                // Click: mark as read then redirect
                item.addEventListener('click', (e) => {
                    if (e.target.closest('.delete-btn')) return;

                    const fullLink = notif.link ? baseUrl + notif.link.replace(/^\/+/, '') : null;

                    if (isUnread) {
                        $.post('mark_notification.php', {
                                notification_id: notif.notification_id
                            })
                            .done(() => {
                                if (fullLink) {
                                    window.location.href = fullLink;
                                } else {
                                    fetchNotifications();
                                }
                            })
                            .fail(err => console.error('Error marking notification:', err));
                    } else {
                        if (fullLink) window.location.href = fullLink;
                    }
                });

                // Delete button
                item.querySelector('.delete-btn').addEventListener('click', e => {
                    e.stopPropagation();

                    Swal.fire({
                        title: 'Delete Notification?',
                        text: 'This action cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Yes, delete',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.post('delete_notification.php', {
                                    notification_id: notif.notification_id
                                })
                                .done(() => {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted!',
                                        text: 'Notification removed.',
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                    fetchNotifications();
                                })
                                .fail(err => {
                                    console.error('Error deleting:', err);
                                    Swal.fire('Error', 'Failed to delete', 'error');
                                });
                        }
                    });
                });

                container.appendChild(item);
            });
        }

        // Filter tabs
        $('.filter-tab').on('click', function() {
            $('.filter-tab').removeClass('bg-blue-500 text-white').addClass('bg-blue-100 text-blue-600');
            $(this).removeClass('bg-blue-100 text-blue-600').addClass('bg-blue-500 text-white');
            currentFilter = $(this).data('filter');
            fetchNotifications();
        });

        // Mark all as read
        $('#markAllRead').on('click', function() {
            Swal.fire({
                title: 'Mark All as Read?',
                text: 'This will mark all unread notifications as read.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, mark all',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('mark_all_read.php')
                        .done(() => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Done!',
                                text: 'All notifications marked as read.',
                                timer: 1500,
                                showConfirmButton: false
                            });
                            fetchNotifications();
                        })
                        .fail(err => {
                            console.error('Error:', err);
                            Swal.fire('Error', 'Failed to mark all as read', 'error');
                        });
                }
            });
        });

        $(document).ready(function() {
            fetchNotifications();
            setInterval(fetchNotifications, 30000); // Refresh every 30 seconds
        });
    </script>
</body>

</html>