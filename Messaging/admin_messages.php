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
    <title>RCH Water - Admin Messages</title>
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

        .chat-messages {
            scroll-behavior: smooth;
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
                $pageTitle = "Admin Messages";
                $pageSubtitle = "Chat with riders and customers in real-time.";
                include '../Admin/includes/header.php';
                ?>
                <!-- Chat Container -->
                <div class="flex-1 flex gap-4 p-4 overflow-hidden">

                    <!-- Chat list panel on the left -->
                    <div class="w-80 flex flex-col bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">

                        <!-- Search Bar -->
                        <div class="p-4 border-b border-gray-100">
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                                <input type="text" id="search-input" placeholder="Search chats..." class="w-full pl-10 pr-4 py-2 bg-gray-50 rounded-lg border border-gray-200 focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400 text-sm">
                            </div>
                        </div>

                        <!-- Chat List -->
                        <div class="flex-1 overflow-y-auto" id="chat-list-container">
                            <!-- Customer Chats Section -->
                        </div>
                    </div>

                    <!-- Chat main area on the right -->
                    <div class="flex-1 flex flex-col bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <!-- Chat Header -->
                        <div class="p-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-blue-100 flex items-center justify-between" id="chat-header">

                        </div>


                        <!-- Chat Messages -->
                        <div class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50 chat-messages" id="messages-container">
                            <div class="flex items-center justify-center h-full">
                                <p class="text-gray-400 text-center">Select a contact to view messages</p>
                            </div>
                        </div>

                        <!-- Chat Input -->
                        <div class="p-4 border-t border-gray-100 bg-white">
                            <div class="flex items-end gap-3">
                                <button class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition text-gray-600">
                                    <i class="fas fa-plus text-lg"></i>
                                </button>
                                <div class="flex-1 flex items-center gap-2 bg-gray-50 rounded-full px-4 py-2 border border-gray-200 focus-within:border-blue-400 focus-within:ring-1 focus-within:ring-blue-400 transition">
                                    <input type="text" placeholder="Type your message..." class="flex-1 bg-transparent text-sm outline-none text-gray-800 placeholder-gray-400">
                                    <button class="flex-shrink-0 text-gray-400 hover:text-blue-600 transition">
                                        <i class="fas fa-face-smile text-lg"></i>
                                    </button>
                                </div>
                                <button class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center rounded-full hover:shadow-lg transition text-white">
                                    <i class="fas fa-paper-plane text-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <input type="file" id="image-input" accept="image/*" style="display: none;">
        <!-- Image Preview Modal -->
        <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-70 hidden items-center justify-center z-50">
            <img id="modalImage" src="" class="max-w-[90%] max-h-[90%] rounded-lg shadow-lg" alt="Preview">
        </div>


    </div>

    <script>
        const currentUserId = 3;
        const currentUserType = 'rider';
        let activeReceiverId = null;
        let activeReceiverName = '';
        let allContacts = [];

        function formatMessageTime(timestamp) {
            if (!timestamp) return "";
            const date = new Date(timestamp.replace(" ", "T"));
            if (isNaN(date)) return ""; // fallback
            return date.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });
        }

        function formatLastMessageTime(timestamp) {
            if (!timestamp) return "";
            const date = new Date(timestamp.replace(" ", "T"));
            if (isNaN(date)) return "—";
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);

            if (diffMins < 1) return 'just now';
            if (diffMins < 60) return `${diffMins}m`;
            if (diffHours < 24) return `${diffHours}h`;
            if (diffDays < 7) return `${diffDays}d`;
            return date.toLocaleDateString();
        }


        function loadContacts(searchQuery = '') {
            const params = new URLSearchParams({
                user_type: currentUserType,
                search: searchQuery
            });

            fetch(`get_contacts.php?${params}`)
                .then(res => res.json())
                .then(data => {
                    console.log("[v0] Contacts loaded:", data);
                    allContacts = data;
                    const container = document.getElementById('chat-list-container');
                    container.innerHTML = '';

                    // Organize contacts by type
                    const customers = data.filter(c => c.type === 'customer');
                    const riders = data.filter(c => c.type === 'rider');
                    const admins = data.filter(c => c.type === 'admin');

                    // Render customers section
                    if (customers.length > 0) {
                        container.innerHTML += `
                            <div class="px-3 py-2">
                                <p class="text-xs font-semibold text-gray-600 px-2 mb-2 uppercase tracking-wide">
                                    <i class="fas fa-user-circle text-blue-500 mr-1"></i>
                                    Customers
                                </p>
                        `;
                        customers.forEach(customer => {
                            const bgClass = customer.avatar_color || 'from-blue-400 to-blue-600';
                            const isActive = activeReceiverId == customer.user_id;
                            container.innerHTML += `
                                <div class="chat-item p-3 rounded-lg mb-2 cursor-pointer transition ${isActive ? 'bg-blue-50 border-l-4 border-blue-500' : 'hover:bg-gray-50'}" data-receiver-id="${customer.user_id}" data-receiver-name="${customer.name}">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-start gap-2 flex-1">
                                            <div class="w-10 h-10 bg-gradient-to-br ${bgClass} rounded-full flex items-center justify-center flex-shrink-0 text-white font-bold text-sm">
                                                ${customer.initials}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-semibold text-gray-800 text-sm">${customer.name}</p>
                                                <p class="text-xs text-gray-600 truncate">${customer.last_message}</p>
                                            </div>
                                        </div>
                                        <span class="text-xs text-gray-500 ml-2 flex-shrink-0">${formatLastMessageTime(customer.last_message_time)}</span>
                                    </div>
                                    ${customer.unread_count > 0 ? `<span class="inline-block px-2 py-1 bg-blue-500 text-white text-xs rounded-full mt-2">${customer.unread_count}</span>` : ''}
                                </div>
                            `;
                        });
                        container.innerHTML += `</div>`;
                    }

                    // Render riders section
                    if (riders.length > 0) {
                        container.innerHTML += `
                            <div class="px-3 py-2 border-t border-gray-200">
                                <p class="text-xs font-semibold text-gray-600 px-2 mb-2 uppercase tracking-wide">
                                    <i class="fas fa-motorcycle text-orange-500 mr-1"></i>
                                    Riders
                                </p>
                        `;
                        riders.forEach(rider => {
                            const bgClass = rider.avatar_color || 'from-orange-400 to-orange-600';
                            const isActive = activeReceiverId == rider.user_id;
                            container.innerHTML += `
                                <div class="chat-item p-3 rounded-lg mb-2 cursor-pointer transition ${isActive ? 'bg-blue-50 border-l-4 border-blue-500' : 'hover:bg-gray-50'}" data-receiver-id="${rider.user_id}" data-receiver-name="${rider.name}">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-start gap-2 flex-1">
                                            <div class="w-10 h-10 bg-gradient-to-br ${bgClass} rounded-full flex items-center justify-center flex-shrink-0 text-white font-bold text-sm">
                                                ${rider.initials}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-semibold text-gray-800 text-sm">${rider.name}</p>
                                                <p class="text-xs text-gray-600 truncate">${rider.last_message}</p>
                                            </div>
                                        </div>
                                        <span class="text-xs text-gray-500 ml-2 flex-shrink-0">${formatLastMessageTime(rider.last_message_time)}</span>
                                    </div>
                                    ${rider.unread_count > 0 ? `<span class="inline-block px-2 py-1 bg-blue-500 text-white text-xs rounded-full mt-2">${rider.unread_count}</span>` : ''}
                                </div>
                            `;
                        });
                        container.innerHTML += `</div>`;
                    }

                    // Render admins section (if visible)
                    if (admins.length > 0) {
                        container.innerHTML += `
                            <div class="px-3 py-2 border-t border-gray-200">
                                <p class="text-xs font-semibold text-gray-600 px-2 mb-2 uppercase tracking-wide">
                                    <i class="fas fa-shield-alt text-purple-500 mr-1"></i>
                                    Admins
                                </p>
                        `;
                        admins.forEach(admin => {
                            const bgClass = admin.avatar_color || 'from-purple-400 to-purple-600';
                            const isActive = activeReceiverId == admin.user_id;
                            container.innerHTML += `
                                <div class="chat-item p-3 rounded-lg mb-2 cursor-pointer transition ${isActive ? 'bg-blue-50 border-l-4 border-blue-500' : 'hover:bg-gray-50'}" data-receiver-id="${admin.user_id}" data-receiver-name="${admin.name}">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-start gap-2 flex-1">
                                            <div class="w-10 h-10 bg-gradient-to-br ${bgClass} rounded-full flex items-center justify-center flex-shrink-0 text-white font-bold text-sm">
                                                ${admin.initials}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-semibold text-gray-800 text-sm">${admin.name}</p>
                                                <p class="text-xs text-gray-600 truncate">${admin.last_message}</p>
                                            </div>
                                        </div>
                                        <span class="text-xs text-gray-500 ml-2 flex-shrink-0">${formatLastMessageTime(admin.last_message_time)}</span>
                                    </div>
                                    ${admin.unread_count > 0 ? `<span class="inline-block px-2 py-1 bg-blue-500 text-white text-xs rounded-full mt-2">${admin.unread_count}</span>` : ''}
                                </div>
                            `;
                        });
                        container.innerHTML += `</div>`;
                    }

                    // Attach click handlers and keep active state
                    attachContactListeners();
                    restoreActiveContact();
                })
                .catch(err => console.log("[v0] Error loading contacts:", err));
        }

        function restoreActiveContact() {
            if (activeReceiverId) {
                const activeItem = document.querySelector(`.chat-item[data-receiver-id="${activeReceiverId}"]`);
                if (activeItem) {
                    activeItem.classList.remove('hover:bg-gray-50');
                    activeItem.classList.add('bg-blue-50', 'border-l-4', 'border-blue-500');
                }
            }
        }

        function attachContactListeners() {
            document.querySelectorAll('.chat-item').forEach(item => {
                item.addEventListener('click', function() {
                    const receiverId = this.getAttribute('data-receiver-id');
                    const receiverName = this.getAttribute('data-receiver-name');

                    // Update active state
                    document.querySelectorAll('.chat-item').forEach(el => {
                        el.classList.remove('bg-blue-50', 'border-l-4', 'border-blue-500');
                        el.classList.add('hover:bg-gray-50');
                    });
                    this.classList.add('bg-blue-50', 'border-l-4', 'border-blue-500');
                    this.classList.remove('hover:bg-gray-50');

                    // Update active receiver
                    activeReceiverId = receiverId;
                    activeReceiverName = receiverName;

                    // Update chat header
                    updateChatHeader(receiverName, this.querySelector('.w-10').textContent);

                    // Load messages for this contact
                    loadMessages();
                });
            });
        }

        function updateChatHeader(name, initials) {
            const header = document.getElementById('chat-header');
            if (header) {
                header.innerHTML = `
                    <div class="flex items-center gap-3 w-full">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                            ${initials}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">${name}</p>
                            <p class="text-xs text-green-600 flex items-center gap-1">
                                <i class="fas fa-circle text-green-500" style="font-size: 0.4rem;"></i>
                                Online now
                            </p>
                        </div>
                    </div>
                `;
            }
        }

        function loadMessages() {
            if (!activeReceiverId) return;

            fetch(`get_messages.php?sender_id=${currentUserId}&receiver_id=${activeReceiverId}`)
                .then(res => res.json())
                .then(data => {
                    console.log("[ Messages loaded:", data);
                    const container = document.getElementById('messages-container');
                    container.innerHTML = '';

                    const sortedData = data.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

                    if (sortedData.length === 0) {
                        container.innerHTML = '<div class="flex items-center justify-center h-full"><p class="text-gray-400">No messages yet. Start the conversation!</p></div>';
                        return;
                    }

                    sortedData.forEach(msg => {
                        const isSelf = msg.sender_id == currentUserId;

                        // Determine message content (text or image)
                        let bubbleContent = '';
                        if (msg.image_path && msg.image_path.trim() !== '') {
                            bubbleContent = `<img src="${msg.image_path}" alt="Sent image" class="w-40 rounded-lg cursor-pointer hover:opacity-90 transition">`;
                        } else if (msg.message && msg.message.trim() !== '') {
                            bubbleContent = `<p class="text-sm">${msg.message}</p>`;
                        }

                        // Create the message bubble wrapper
                        const messageHTML = `
                    <div class="flex ${isSelf ? 'justify-end' : 'justify-start'} mb-2">
                        <div class="max-w-xs lg:max-w-md">
                            <div class="flex items-end gap-2">
                                ${!isSelf ? `<div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                    ${msg.sender_initials || '?'}
                                </div>` : ''}
                                <div class="${isSelf ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-3xl rounded-tr-sm px-4 py-2 shadow-sm'
                                                : 'bg-white border border-gray-200 text-gray-800 rounded-3xl rounded-tl-sm px-4 py-2 shadow-sm'}">
                                    ${bubbleContent}
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1 ${isSelf ? 'text-right' : 'ml-10'}">
                                ${formatMessageTime(msg.created_at)}
                            </p>
                        </div>
                    </div>
                `;

                        // Append the message bubble
                        container.insertAdjacentHTML('beforeend', messageHTML);

                        // Attach click handler to image for modal
                        const lastImg = container.querySelector('img:last-of-type');
                        if (lastImg) {
                            lastImg.addEventListener('click', () => {
                                const modal = document.getElementById('imageModal');
                                const modalImage = document.getElementById('modalImage');
                                modalImage.src = lastImg.src;
                                modal.classList.remove('hidden');
                                modal.classList.add('flex');
                            });
                        }
                    });

                    // Scroll to bottom
                    container.scrollTop = container.scrollHeight;
                })
                .catch(err => console.log("[v0] Error loading messages:", err));
        }


        function sendMessage(formData) {
            fetch('send_message.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(() => {
                    document.querySelector('input[placeholder="Type your message..."]').value = '';
                    document.querySelector('#image-input').value = '';
                    loadMessages();
                })
                .catch(err => console.log("[v0] Error sending message:", err));
        }

        // Send text when clicking send icon
        document.querySelector('.fa-paper-plane').parentElement.addEventListener('click', () => {
            if (!activeReceiverId) {
                alert('Please select a contact first');
                return;
            }

            const input = document.querySelector('input[placeholder="Type your message..."]');
            const message = input.value.trim();

            if (message === '') return;

            const formData = new FormData();
            formData.append('sender_id', currentUserId);
            formData.append('receiver_id', activeReceiverId);
            formData.append('message', message);

            sendMessage(formData);
        });

        // Handle image upload button
        document.querySelector('.fa-plus').parentElement.addEventListener('click', () => {
            if (!activeReceiverId) {
                alert('Please select a contact first');
                return;
            }
            document.querySelector('#image-input').click();
        });

        // When an image is selected, send it automatically
        document.querySelector('#image-input').addEventListener('change', function() {
            if (!activeReceiverId) return;

            const file = this.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('sender_id', currentUserId);
            formData.append('receiver_id', activeReceiverId);
            formData.append('image', file);

            sendMessage(formData);
        });

        let searchTimeout;
        document.getElementById('search-input').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const searchQuery = this.value.trim();
            searchTimeout = setTimeout(() => {
                loadContacts(searchQuery);
            }, 300);
        });

        loadContacts();
        setInterval(loadContacts, 5000); // Refresh contacts every 5 seconds
        setInterval(loadMessages, 3000); // Refresh messages every 3 seconds
        function openImageModal(src) {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            modalImage.src = src;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        document.getElementById('imageModal').addEventListener('click', () => {
            const modal = document.getElementById('imageModal');
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }); 
    </script>

</body>

</html>