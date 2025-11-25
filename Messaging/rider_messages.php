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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .chat-messages {
            scroll-behavior: smooth;
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
    </style>
</head>

<body class="bg-white min-h-screen flex justify-center items-center">

    <!-- Fixed-width mobile container -->
    <div class="bg-white w-[375px] h-[750px] mx-auto relative rounded-3xl overflow-hidden border shadow-lg flex flex-col">

        <!-- Header -->
        <div class="bg-gradient-to-br from-blue-400 to-blue-600 text-white sticky top-0 z-50 w-full px-4 py-3">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-bold tracking-tight">Messages</h2>
                    <p class="text-sm text-blue-100 font-medium">Chat with riders and customers</p>
                </div>
                <button onclick="showChatList()" id="back-button" class="hidden text-white text-2xl hover:scale-110 transition-transform duration-200">
                    <i class="bi bi-arrow-left"></i>
                </button>
            </div>
        </div>

        <!-- Chat List View -->
        <div id="chat-list-view" class="flex-1 flex flex-col overflow-hidden">
            <!-- Search Bar -->
            <div class="p-4 border-b border-gray-100 bg-white">
                <div class="relative">
                    <i class="bi bi-search absolute left-3 top-3 text-gray-400"></i>
                    <input type="text" id="search-input" placeholder="Search chats..." 
                        class="w-full pl-10 pr-4 py-2 bg-gray-50 rounded-lg border border-gray-200 focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400 text-sm">
                </div>
            </div>

            <!-- Chat List -->
            <div class="flex-1 overflow-y-auto custom-scrollbar bg-gray-50" id="chat-list-container">
                <!-- Contacts will be loaded here -->
            </div>
        </div>

        <!-- Chat View (Hidden by default) -->
        <div id="chat-view" class="flex-1 flex-col overflow-hidden hidden">
            <!-- Chat Header -->
            <div class="p-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-blue-100 flex items-center gap-3" id="chat-header">
                <!-- Dynamic header content -->
            </div>

            <!-- Chat Messages -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50 chat-messages custom-scrollbar" id="messages-container">
                <div class="flex items-center justify-center h-full">
                    <p class="text-gray-400 text-center text-sm">Select a contact to view messages</p>
                </div>
            </div>

            <!-- Chat Input -->
            <div class="p-3 border-t border-gray-100 bg-white">
                <div class="flex items-end gap-2">
                    <button id="attach-button" class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-full hover:bg-gray-100 transition text-gray-600">
                        <i class="bi bi-plus-lg text-lg"></i>
                    </button>
                    <div class="flex-1 flex items-center gap-2 bg-gray-50 rounded-full px-3 py-2 border border-gray-200 focus-within:border-blue-400 focus-within:ring-1 focus-within:ring-blue-400 transition">
                        <input type="text" id="message-input" placeholder="Type your message..." 
                            class="flex-1 bg-transparent text-sm outline-none text-gray-800 placeholder-gray-400">
                        <button class="flex-shrink-0 text-gray-400 hover:text-blue-600 transition">
                            <i class="bi bi-emoji-smile text-lg"></i>
                        </button>
                    </div>
                    <button id="send-button" class="flex-shrink-0 w-9 h-9 bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center rounded-full hover:shadow-lg transition text-white">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Hidden file input -->
        <input type="file" id="image-input" accept="image/*" style="display: none;">

        <!-- Image Preview Modal -->
        <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-70 hidden items-center justify-center z-50">
            <img id="modalImage" src="" class="max-w-[90%] max-h-[90%] rounded-lg shadow-lg" alt="Preview">
        </div>
    </div>

    <script>
        const currentUserId = 1;
        const currentUserType = 'admin';
        let activeReceiverId = null;
        let activeReceiverName = '';
        let allContacts = [];

        function formatMessageTime(timestamp) {
            if (!timestamp) return "";
            const date = new Date(timestamp.replace(" ", "T"));
            if (isNaN(date)) return "";
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

        function showChatList() {
            document.getElementById('chat-list-view').classList.remove('hidden');
            document.getElementById('chat-list-view').classList.add('flex');
            document.getElementById('chat-view').classList.remove('flex');
            document.getElementById('chat-view').classList.add('hidden');
            document.getElementById('back-button').classList.add('hidden');
            activeReceiverId = null;
        }

        function showChatView() {
            document.getElementById('chat-list-view').classList.remove('flex');
            document.getElementById('chat-list-view').classList.add('hidden');
            document.getElementById('chat-view').classList.remove('hidden');
            document.getElementById('chat-view').classList.add('flex');
            document.getElementById('back-button').classList.remove('hidden');
        }

        function loadContacts(searchQuery = '') {
            const params = new URLSearchParams({
                user_type: currentUserType,
                search: searchQuery
            });

            fetch(`get_contacts.php?${params}`)
                .then(res => res.json())
                .then(data => {
                    console.log("[] Contacts loaded:", data);
                    allContacts = data;
                    const container = document.getElementById('chat-list-container');
                    container.innerHTML = '';

                    const customers = data.filter(c => c.type === 'customer');
                    const riders = data.filter(c => c.type === 'rider');
                    const admins = data.filter(c => c.type === 'admin');

                    if (customers.length > 0) {
                        container.innerHTML += `
                            <div class="px-3 py-3">
                                <p class="text-xs font-semibold text-gray-600 px-2 mb-2 uppercase tracking-wide">
                                    <i class="bi bi-person-circle text-blue-500 mr-1"></i>
                                    Customers
                                </p>
                        `;
                        customers.forEach(customer => {
                            const bgClass = customer.avatar_color || 'from-blue-400 to-blue-600';
                            const unreadClass = customer.has_unread ? 'font-bold text-gray-900' : 'font-semibold text-gray-800';
                            const lastMsgClass = customer.has_unread ? 'font-bold text-gray-900' : 'text-gray-600';

                            container.innerHTML += `
                                <div class="chat-item bg-white rounded-xl p-3 mb-2 cursor-pointer transition hover:shadow-md border border-gray-100" 
                                    data-receiver-id="${customer.user_id}" 
                                    data-receiver-name="${customer.name}"
                                    data-receiver-initials="${customer.initials}">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-start gap-2 flex-1">
                                            <div class="w-10 h-10 bg-gradient-to-br ${bgClass} rounded-full flex items-center justify-center flex-shrink-0 text-white font-bold text-sm">
                                                ${customer.initials}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="${unreadClass} text-sm truncate">${customer.name}</p>
                                                <p class="text-xs truncate ${lastMsgClass}">${customer.last_message}</p>
                                            </div>
                                        </div>
                                        <span class="text-xs text-gray-500 ml-2 flex-shrink-0">${formatLastMessageTime(customer.last_message_time)}</span>
                                    </div>
                                </div>
                            `;
                        });
                        container.innerHTML += `</div>`;
                    }

                    if (riders.length > 0) {
                        container.innerHTML += `
                            <div class="px-3 py-3 border-t border-gray-200">
                                <p class="text-xs font-semibold text-gray-600 px-2 mb-2 uppercase tracking-wide">
                                    <i class="bi bi-truck text-orange-500 mr-1"></i>
                                    Riders
                                </p>
                        `;
                        riders.forEach(rider => {
                            const bgClass = rider.avatar_color || 'from-orange-400 to-orange-600';
                            const unreadClass = rider.has_unread ? 'font-bold text-gray-900' : 'font-semibold text-gray-800';
                            const lastMsgClass = rider.has_unread ? 'font-bold text-gray-900' : 'text-gray-600';

                            container.innerHTML += `
                                <div class="chat-item bg-white rounded-xl p-3 mb-2 cursor-pointer transition hover:shadow-md border border-gray-100" 
                                    data-receiver-id="${rider.user_id}" 
                                    data-receiver-name="${rider.name}"
                                    data-receiver-initials="${rider.initials}">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-start gap-2 flex-1">
                                            <div class="w-10 h-10 bg-gradient-to-br ${bgClass} rounded-full flex items-center justify-center flex-shrink-0 text-white font-bold text-sm">
                                                ${rider.initials}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="${unreadClass} text-sm truncate">${rider.name}</p>
                                                <p class="text-xs truncate ${lastMsgClass}">${rider.last_message}</p>
                                            </div>
                                        </div>
                                        <span class="text-xs text-gray-500 ml-2 flex-shrink-0">${formatLastMessageTime(rider.last_message_time)}</span>
                                    </div>
                                </div>
                            `;
                        });
                        container.innerHTML += `</div>`;
                    }

                    if (admins.length > 0) {
                        container.innerHTML += `
                            <div class="px-3 py-3 border-t border-gray-200">
                                <p class="text-xs font-semibold text-gray-600 px-2 mb-2 uppercase tracking-wide">
                                    <i class="bi bi-shield-check text-purple-500 mr-1"></i>
                                    Admins
                                </p>
                        `;
                        admins.forEach(admin => {
                            const bgClass = admin.avatar_color || 'from-purple-400 to-purple-600';
                            const unreadClass = admin.has_unread ? 'font-bold text-gray-900' : 'font-semibold text-gray-800';
                            const lastMsgClass = admin.has_unread ? 'font-bold text-gray-900' : 'text-gray-600';

                            container.innerHTML += `
                                <div class="chat-item bg-white rounded-xl p-3 mb-2 cursor-pointer transition hover:shadow-md border border-gray-100" 
                                    data-receiver-id="${admin.user_id}" 
                                    data-receiver-name="${admin.name}"
                                    data-receiver-initials="${admin.initials}">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-start gap-2 flex-1">
                                            <div class="w-10 h-10 bg-gradient-to-br ${bgClass} rounded-full flex items-center justify-center flex-shrink-0 text-white font-bold text-sm">
                                                ${admin.initials}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="${unreadClass} text-sm truncate">${admin.name}</p>
                                                <p class="text-xs truncate ${lastMsgClass}">${admin.last_message}</p>
                                            </div>
                                        </div>
                                        <span class="text-xs text-gray-500 ml-2 flex-shrink-0">${formatLastMessageTime(admin.last_message_time)}</span>
                                    </div>
                                </div>
                            `;
                        });
                        container.innerHTML += `</div>`;
                    }

                    attachContactListeners();
                })
                .catch(err => console.log("[] Error loading contacts:", err));
        }

        function attachContactListeners() {
            document.querySelectorAll('.chat-item').forEach(item => {
                item.addEventListener('click', function() {
                    const receiverId = this.getAttribute('data-receiver-id');
                    const receiverName = this.getAttribute('data-receiver-name');
                    const receiverInitials = this.getAttribute('data-receiver-initials');

                    activeReceiverId = receiverId;
                    activeReceiverName = receiverName;

                    updateChatHeader(receiverName, receiverInitials);
                    showChatView();

                    fetch('mark_read.php', {
                            method: 'POST',
                            body: new URLSearchParams({
                                contact_id: activeReceiverId
                            })
                        })
                        .then(res => res.json())
                        .then(() => loadContacts())
                        .catch(console.error);

                    loadMessages();
                });
            });
        }

        function updateChatHeader(name, initials) {
            const header = document.getElementById('chat-header');
            if (header) {
                header.innerHTML = `
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        ${initials}
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-800 text-sm">${name}</p>
                        <p class="text-xs text-green-600 flex items-center gap-1">
                            <i class="bi bi-circle-fill text-green-500" style="font-size: 0.4rem;"></i>
                            Online now
                        </p>
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
                        container.innerHTML = '<div class="flex items-center justify-center h-full"><p class="text-gray-400 text-sm">No messages yet. Start the conversation!</p></div>';
                        return;
                    }

                    sortedData.forEach(msg => {
                        const isSelf = msg.sender_id == currentUserId;

                        let bubbleContent = '';
                        if (msg.image_path && msg.image_path.trim() !== '') {
                            bubbleContent = `<img src="${msg.image_path}" alt="Sent image" class="w-40 rounded-lg cursor-pointer hover:opacity-90 transition">`;
                        } else if (msg.message && msg.message.trim() !== '') {
                            bubbleContent = `<p class="text-sm">${msg.message}</p>`;
                        }

                        const messageHTML = `
                    <div class="flex ${isSelf ? 'justify-end' : 'justify-start'} mb-2">
                        <div class="max-w-[280px]">
                            <div class="flex items-end gap-2">
                                ${!isSelf ? `<div class="w-7 h-7 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                    ${msg.sender_initials || '<i class="bi bi-person"></i>'}
                                </div>` : ''}
                                <div class="${isSelf 
                                    ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-2xl rounded-tr-sm px-3 py-2 shadow-sm'
                                    : 'bg-white border border-gray-200 text-gray-800 rounded-2xl rounded-tl-sm px-3 py-2 shadow-sm'}">
                                    ${bubbleContent}
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1 ${isSelf ? 'text-right' : 'ml-9'}">
                                ${formatMessageTime(msg.created_at)}
                            </p>
                        </div>
                    </div>
                `;

                        container.insertAdjacentHTML('beforeend', messageHTML);

                        const lastImg = container.querySelector('img:last-of-type');
                        if (lastImg) {
                            lastImg.addEventListener('click', () => {
                                openImageModal(lastImg.src);
                            });
                        }
                    });

                    container.scrollTop = container.scrollHeight;

                    fetch('mark_read.php', {
                            method: 'POST',
                            body: new URLSearchParams({
                                contact_id: activeReceiverId
                            })
                        })
                        .then(res => res.json())
                        .then(() => loadContacts())
                        .catch(console.error);
                })
                .catch(err => console.log("[] Error loading messages:", err));
        }

        function sendMessage(formData) {
            fetch('send_message.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(() => {
                    document.getElementById('message-input').value = '';
                    document.getElementById('image-input').value = '';
                    loadMessages();
                })
                .catch(err => console.log("[] Error sending message:", err));
        }

        document.getElementById('send-button').addEventListener('click', () => {
            if (!activeReceiverId) {
                alert('Please select a contact first');
                return;
            }

            const input = document.getElementById('message-input');
            const message = input.value.trim();

            if (message === '') return;

            const formData = new FormData();
            formData.append('sender_id', currentUserId);
            formData.append('receiver_id', activeReceiverId);
            formData.append('message', message);

            sendMessage(formData);
        });

        document.getElementById('attach-button').addEventListener('click', () => {
            if (!activeReceiverId) {
                alert('Please select a contact first');
                return;
            }
            document.getElementById('image-input').click();
        });

        document.getElementById('image-input').addEventListener('change', function() {
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
        let isSearching = false;

        document.getElementById('search-input').addEventListener('input', function() {
            clearTimeout(searchTimeout);

            const searchQuery = this.value.trim();

            if (searchQuery.length > 0) {
                isSearching = true;
            }

            searchTimeout = setTimeout(() => {
                loadContacts(searchQuery);
                isSearching = false;
            }, 500);
        });

        setInterval(() => {
            if (!isSearching && !activeReceiverId) {
                loadContacts();
            }
        }, 5000);

        setInterval(() => {
            if (activeReceiverId) {
                loadMessages();
            }
        }, 3000);

        document.getElementById('back-button').addEventListener('click', showChatList);

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

        loadContacts();
    </script>

</body>

</html>