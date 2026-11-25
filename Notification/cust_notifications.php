<?php
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('customer'); // only customers can access
require_once '../Database/db_connection.php';

$database = new Database();
$db = $database->getConnect();

// Generate dynamic base URL (project root)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST']; // e.g., localhost
$projectRoot = dirname(dirname($_SERVER['SCRIPT_NAME'])); // go up 2 levels from Notification
$baseUrl = $protocol . "://" . $host . $projectRoot . '/';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RCH Water - Notifications</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
        }

        html {
            scrollbar-gutter: stable;
        }

        /* Unread notification pulse animation */
        @keyframes pulse-border {
            0%, 100% {
                border-color: #3b82f6;
            }
            50% {
                border-color: #60a5fa;
            }
        }

        .notification-unread {
            animation: pulse-border 2s ease-in-out infinite;
        }

        /* Custom scrollbar */
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

        /* Preview popup - match your page style */
        .notif-preview-icon {
            width: 64px; height: 64px; border-radius: 9999px;
            background: #3b82f6; color: #fff;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 12px auto; font-size: 26px;
        }
        .notif-preview-time { font-size: 12px; color: #94a3b8; margin-top: 10px; }
        .notif-preview-msg { font-size: 14px; color: #334155; line-height: 1.6; text-align: left; white-space: pre-line; }
    </style>
</head>

<body class="bg-gray-50 overflow-hidden">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <script src="../Customer/includes/sidebar.js"></script>

        <!-- Main Content -->
        <div class="flex-1 ml-20 group-hover:ml-64 transition-all duration-300 ease-in-out">
            <div class="flex flex-col h-screen">
                <!-- Header -->
                <?php
                $pageTitle = "Notifications";
                $pageSubtitle = "Stay updated with the latest alerts and messages.";
                include '../Customer/includes/header.php';
                ?>

                <!-- Filter Tabs -->
                <div class="px-6 py-3 bg-white border-b">
                    <div class="flex gap-3">
                        <button id="allTab" class="filter-tab px-4 py-2 rounded-lg font-medium transition-all bg-blue-600 text-white" data-filter="all">
                            <i class="fas fa-list mr-2"></i>All
                        </button>
                        <button class="filter-tab px-4 py-2 rounded-lg font-medium transition-all bg-gray-100 text-gray-600 hover:bg-gray-200" data-filter="unread">
                            <i class="fas fa-envelope mr-2"></i>Unread
                        </button>
                        <button class="filter-tab px-4 py-2 rounded-lg font-medium transition-all bg-gray-100 text-gray-600 hover:bg-gray-200" data-filter="read">
                            <i class="fas fa-envelope-open mr-2"></i>Read
                        </button>
                        <button id="markAllRead" class="ml-auto px-4 py-2 rounded-lg font-medium transition-all bg-green-500 text-white hover:bg-green-600">
                            <i class="fas fa-check-double mr-2"></i>Mark All as Read
                        </button>
                    </div>
                </div>

                <div class="m-6 flex flex-col flex-1 overflow-hidden">
                    <div class="w-full overflow-y-auto space-y-3" id="notifications-list">
                        <!-- Notification items will be added here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const baseUrl = "<?= $baseUrl ?>"; // PHP-generated base URL
        let currentFilter = 'all';

        // Escape HTML so message shows as text, not as broken HTML
        function escapeHtml(s) {
            return String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        }

        // Instantly turn ONE item to the READ look (white, gray, no NEW, opacity-70)
        function markItemAsReadUI(item) {
            if (!item) return;
            item.dataset.status = 'read';
            item.classList.remove('border-blue-600', 'bg-blue-50', 'hover:bg-blue-100', 'shadow-md', 'notification-unread');
            item.classList.add('border-gray-300', 'bg-white', 'hover:bg-gray-50', 'shadow-sm', 'opacity-70');
            const iconBox = item.querySelector('.notif-icon');
            if (iconBox) { iconBox.classList.remove('bg-blue-500'); iconBox.classList.add('bg-gray-400'); }
            const title = item.querySelector('.notif-title');
            if (title) { title.classList.remove('text-blue-700'); title.classList.add('text-gray-600'); }
            const msg = item.querySelector('.notif-msg');
            if (msg) { msg.classList.remove('text-gray-800', 'font-medium'); msg.classList.add('text-gray-600'); }
            const time = item.querySelector('.notif-time');
            if (time) { time.classList.remove('text-gray-600'); time.classList.add('text-gray-400'); }
            const badge = item.querySelector('.notif-new-badge');
            if (badge) badge.remove();
        }

        function markAllAsReadUI() {
            document.querySelectorAll('.notification-item').forEach(markItemAsReadUI);
        }

        // NEW: Preview popup - shows full details before going to the order
        function showNotifPreview(notif, fullLink) {
            const title = escapeHtml(notif.title || 'Notification');
            const msg = escapeHtml(notif.message || '');
            const time = escapeHtml(notif.created_at || '');

            // If it has a link: [View Order] + [Close]
            // If no link: [Close] only
            Swal.fire({
                title: title,
                html: `
                    <div class="notif-preview-icon"><i class="fas fa-bell"></i></div>
                    <div class="notif-preview-msg">${msg}</div>
                    <div class="notif-preview-time"><i class="fas fa-clock"></i> ${time}</div>
                `,
                showCancelButton: !!fullLink,
                confirmButtonText: fullLink ? 'View Order' : 'Close',
                cancelButtonText: 'Close',
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#6b7280',
                reverseButtons: true,
                allowOutsideClick: true
            }).then((result) => {
                if (result.isConfirmed && fullLink) {
                    window.location.href = fullLink;
                } else if (result.isConfirmed && !fullLink) {
                    // Just closing - if on Unread tab, refresh so read item disappears
                    if (currentFilter === 'unread') fetchNotifications();
                } else {
                    // Closed via Close / X / outside - same: refresh only on Unread tab
                    if (currentFilter === 'unread') fetchNotifications();
                }
            });
        }

        function fetchNotifications() {
            $.ajax({
                url: 'get_notifications.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('Response:', response);
                    if (response.error) {
                        console.error('Error from server:', response.error);
                        console.log('Session keys:', response.session_keys);
                        $('#notifications-list').html(`
                            <div class="text-center py-8">
                                <p class="text-red-500 font-semibold">Debug Info:</p>
                                <p class="text-gray-600">${response.error}</p>
                                <p class="text-gray-500 text-sm mt-2">Available session keys: ${response.session_keys ? response.session_keys.join(', ') : 'none'}</p>
                            </div>
                        `);
                        return;
                    }
                    renderNotifications(response.notifications || response);
                },
                error: function(err) {
                    console.error('Error fetching notifications:', err);
                    console.log('Full error:', err.responseText);
                    $('#notifications-list').html(`
                        <div class="text-center py-8">
                            <p class="text-red-500 font-semibold">Failed to load notifications</p>
                            <p class="text-gray-500 text-sm mt-2">Check console for details</p>
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
                    <div class="text-center py-12">
                        <i class="fas fa-bell-slash text-6xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 text-lg">No notifications</p>
                    </div>
                `;
                return;
            }

            let filteredNotifications = notifications;
            if (currentFilter === 'unread') {
                filteredNotifications = notifications.filter(n => n.status === 'unread');
            } else if (currentFilter === 'read') {
                filteredNotifications = notifications.filter(n => n.status === 'read');
            }

            if (filteredNotifications.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-12">
                        <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 text-lg">No ${currentFilter} notifications</p>
                    </div>
                `;
                return;
            }

            filteredNotifications.forEach(notif => {
                const isUnread = notif.status === 'unread';

                const item = document.createElement('div');
                item.className = `
                    notification-item relative border-l-4 p-5 rounded-lg transition-all duration-300 cursor-pointer
                    ${isUnread
                        ? 'border-blue-600 bg-blue-50 hover:bg-blue-100 shadow-md notification-unread'
                        : 'border-gray-300 bg-white hover:bg-gray-50 shadow-sm opacity-70'
                    }
                `;
                item.dataset.id = notif.notification_id;
                item.dataset.status = notif.status;

                item.innerHTML = `
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4 flex-1">
                            <div class="flex-shrink-0">
                                <div class="notif-icon ${isUnread ? 'bg-blue-500' : 'bg-gray-400'} w-12 h-12 rounded-full flex items-center justify-center">
                                    <i class="fas fa-bell text-white text-lg"></i>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-1">
                                    <h3 class="notif-title font-bold text-lg ${isUnread ? 'text-blue-700' : 'text-gray-600'}">
                                        ${notif.title}
                                    </h3>
                                    ${isUnread ? '<span class="notif-new-badge bg-blue-500 text-white text-xs font-bold px-2 py-1 rounded-full">NEW</span>' : ''}
                                </div>
                                <p class="notif-msg ${isUnread ? 'text-gray-800 font-medium' : 'text-gray-600'} text-sm mb-2">
                                    ${notif.message}
                                </p>
                                <div class="notif-time flex items-center gap-2 text-xs ${isUnread ? 'text-gray-600' : 'text-gray-400'}">
                                    <i class="fas fa-clock"></i>
                                    <span>${notif.created_at}</span>
                                </div>
                            </div>
                        </div>
                        <button class="delete-btn ml-4 text-gray-400 hover:text-red-600 transition-colors p-2" title="Delete">
                            <i class="fas fa-trash text-lg"></i>
                        </button>
                    </div>
                `;

                // Click: mark as read + show PREVIEW, do NOT redirect immediately
                item.addEventListener('click', (e) => {
                    if (e.target.closest('.delete-btn')) return;

                    const fullLink = notif.link ? baseUrl + notif.link.replace(/^\/+/, '') : null;

                    if (isUnread) {
                        markItemAsReadUI(item);
                        $.post('mark_notification.php', { notification_id: notif.notification_id })
                            .done((res) => {
                                if (res && res.success === false) { fetchNotifications(); return; }
                                showNotifPreview(notif, fullLink);
                            })
                            .fail(err => { console.error('Error marking notification as read:', err); fetchNotifications(); });
                    } else {
                        // Already read: still show preview
                        showNotifPreview(notif, fullLink);
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
                        confirmButtonText: 'Yes, delete it',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.post('delete_notification.php', { notification_id: notif.notification_id })
                                .done(() => {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted!',
                                        text: 'Notification has been deleted.',
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                    fetchNotifications();
                                })
                                .fail(err => {
                                    console.error('Error deleting notification:', err);
                                    Swal.fire('Error', 'Failed to delete notification', 'error');
                                });
                        }
                    });
                });

                container.appendChild(item);
            });
        }

        // Filter tabs
        $('.filter-tab').on('click', function() {
            $('.filter-tab').removeClass('bg-blue-600 text-white').addClass('bg-gray-100 text-gray-600');
            $(this).removeClass('bg-gray-100 text-gray-600').addClass('bg-blue-600 text-white');
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
                    markAllAsReadUI();
                    $.post('mark_all_read.php')
                        .done((res) => {
                            if (res && res.success === false) { fetchNotifications(); Swal.fire('Error', res.message || 'Failed to mark all as read', 'error'); return; }
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
                            console.error('Error marking all as read:', err);
                            Swal.fire('Error', 'Failed to mark all as read', 'error');
                            fetchNotifications();
                        });
                }
            });
        });

        $(document).ready(function() {
            fetchNotifications();
        });

        // Prevent layout shift when SweetAlert opens
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