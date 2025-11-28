<?php
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('rider');
date_default_timezone_set('Asia/Manila');

require_once '../Database/db_connection.php';

$database = new Database();
$db = $database->getConnect();

$user_id = $_SESSION['id'] ?? null;
$user_role = $_SESSION['role'] ?? null;
$user_name = $_SESSION['name'] ?? 'Rider';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | RCH Water</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

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

        .chat-messages {
            scroll-behavior: smooth;
        }

        .typing-indicator {
            display: inline-flex;
            align-items: center;
            gap: 2px;
        }

        .typing-indicator span {
            width: 6px;
            height: 6px;
            background: #94a3b8;
            border-radius: 50%;
            animation: typing 1.4s infinite;
        }

        .typing-indicator span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-indicator span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes typing {

            0%,
            60%,
            100% {
                transform: translateY(0);
            }

            30% {
                transform: translateY(-8px);
            }
        }

        .message-fade-in {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .contact-badge {
            position: absolute;
            top: 4px;
            right: 4px;
            min-width: 16px;
            height: 16px;
            background: #ef4444;
            border-radius: 8px;
            border: 2px solid white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            font-weight: bold;
            color: white;
            padding: 0 3px;
        }
    </style>
</head>

<body class="bg-white min-h-screen flex justify-center items-center">

    <!-- Fixed-width container wrapper -->
    <div class="bg-white w-[375px] h-[750px] mx-auto relative rounded-3xl overflow-hidden border shadow-lg flex flex-col">

        <!-- Header -->
        <div class="bg-gradient-to-br from-blue-400 to-blue-600 text-white sticky top-0 z-50 w-full px-4 py-3">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-bold tracking-tight">Messages</h2>
                    <div id="datetime" class="text-xs text-blue-100 font-medium"></div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="../Notification/rider_notifications.php" class="relative text-white text-lg hover:scale-110 transition-transform duration-200">
                        <i class="bi bi-bell"></i>
                    </a>
                    <a href="../Rider/dashboard.php"
                        class="text-white text-lg hover:scale-110 transition-transform duration-200">
                        <i class="bi bi-arrow-left-circle"></i>
                    </a>
                    <a href="../Rider/edit_profile.php"
                        class="text-white text-lg hover:scale-110 transition-transform duration-200">
                        <i class="bi bi-person-circle"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Chat View Toggle (when chat is active) -->
        <div id="chat-back-header" class="hidden bg-gradient-to-br from-blue-400 to-blue-600 text-white px-4 py-3 border-t border-blue-300">
            <div class="flex items-center gap-3">
                <button onclick="backToContacts()" class="text-white text-xl hover:scale-110 transition-transform duration-200">
                    <i class="bi bi-arrow-left"></i>
                </button>
                <div class="flex items-center gap-2 flex-1">
                    <div id="chat-avatar" class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center text-white font-bold text-sm"></div>
                    <div>
                        <p id="chat-name" class="font-semibold text-sm"></p>
                        <p class="text-xs text-blue-100">Active now</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contacts List View -->
        <div id="contacts-view" class="flex-1 flex flex-col overflow-hidden">
            <!-- Search Bar -->
            <div class="px-4 py-3 bg-white border-b">
                <div class="relative">
                    <i class="bi bi-search absolute left-3 top-2.5 text-gray-400"></i>
                    <input type="text" id="search-input" placeholder="Search chats..."
                        class="w-full pl-9 pr-4 py-2 bg-gray-50 rounded-full border border-gray-200 focus:outline-none focus:border-blue-400 text-sm transition">
                </div>
            </div>

            <!-- Contacts List -->
            <div class="flex-1 overflow-y-auto custom-scrollbar px-4 py-2" id="chat-list-container">
                <div class="flex items-center justify-center h-full">
                    <div class="typing-indicator">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Messages View -->
        <div id="messages-view" class="hidden flex-1 flex flex-col overflow-hidden">
            <!-- Messages Container -->
            <div class="flex-1 overflow-y-auto custom-scrollbar px-4 py-3 bg-gray-50 chat-messages" id="messages-container">
                <div class="flex items-center justify-center h-full">
                    <div class="text-center">
                        <i class="bi bi-chat-dots text-gray-300 text-5xl mb-3"></i>
                        <p class="text-gray-400 text-sm">Select a contact to start</p>
                    </div>
                </div>
            </div>

            <!-- Image Preview Area -->
            <div id="image-preview-area" class="hidden px-4 py-2 border-t bg-gray-50">
                <div class="relative inline-block">
                    <img id="preview-image" src="" alt="Preview" class="max-w-[120px] max-h-[120px] rounded-lg">
                    <button onclick="clearImagePreview()" class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-xs">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>

            <!-- Chat Input -->
            <div class="px-4 py-3 border-t bg-white" id="chat-input-area">
                <div class="flex items-end gap-2">
                    <button id="attach-btn" class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition text-gray-600 disabled:opacity-50" disabled>
                        <i class="bi bi-paperclip text-lg"></i>
                    </button>
                    <div class="flex-1 flex items-center gap-2 bg-gray-50 rounded-full px-3 py-2 border border-gray-200">
                        <input type="text" id="message-input" placeholder="Select a contact..."
                            class="flex-1 bg-transparent text-sm outline-none" disabled>
                    </div>
                    <button id="send-btn" class="flex-shrink-0 w-9 h-9 bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center rounded-full hover:shadow-lg transition text-white disabled:opacity-50" disabled>
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- Hidden file input -->
    <input type="file" id="image-input" accept="image/*" style="display: none;">

    <!-- Image Preview Modal -->
    <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-70 hidden items-center justify-center z-50" onclick="closeImageModal()">
        <div class="relative max-w-[90%] max-h-[90%]">
            <img id="modalImage" src="" class="max-w-full max-h-[90vh] rounded-lg shadow-lg" alt="Preview">
            <button class="absolute top-4 right-4 w-10 h-10 bg-white rounded-full flex items-center justify-center text-gray-800 hover:bg-gray-100 transition">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>

    <script>
        const currentUserId = <?php echo json_encode($user_id); ?>;
        const currentUserType = <?php echo json_encode($user_role); ?>;
        const currentUserName = <?php echo json_encode($user_name); ?>;

        let activeReceiverId = null;
        let activeReceiverName = '';
        let activeReceiverInitials = '';
        let allContacts = [];
        let isSearching = false;
        let searchTimeout = null;
        let contactsRefreshInterval = null;
        let messagesRefreshInterval = null;
        let selectedImageFile = null;

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

        if (!currentUserId) {
            Swal.fire({
                icon: 'error',
                title: 'Session Error',
                text: 'Please log in again.',
                confirmButtonColor: '#3b82f6'
            }).then(() => {
                window.location.href = '../login.php';
            });
        }

        function formatMessageDate(timestamp) {
            const date = new Date(timestamp.replace(" ", "T"));
            return date.toLocaleDateString('en-US', {
                month: 'long',
                day: 'numeric',
                year: 'numeric'
            });
        }

        function formatMessageTime(timestamp) {
            if (!timestamp) return "";
            try {
                const date = new Date(timestamp.replace(" ", "T"));
                if (isNaN(date)) return "";
                return date.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
            } catch (e) {
                return "";
            }
        }

        function formatLastMessageTime(timestamp) {
            if (!timestamp) return "";
            try {
                const date = new Date(timestamp.replace(" ", "T").split(".")[0]);
                if (isNaN(date.getTime())) return "—";

                const now = new Date();
                const diffMs = now - date;
                const diffSecs = Math.floor(diffMs / 1000);
                const diffMins = Math.floor(diffMs / 60000);
                const diffHours = Math.floor(diffMs / 3600000);
                const diffDays = Math.floor(diffMs / 86400000);

                if (diffSecs < 60) return 'now';
                if (diffMins < 60) return `${diffMins}m`;
                if (diffHours < 24) return `${diffHours}h`;
                if (diffDays < 7) return `${diffDays}d`;
                return date.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric'
                });
            } catch (e) {
                return "—";
            }
        }

        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message,
                confirmButtonColor: '#3b82f6',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        }

        function backToContacts() {
            document.getElementById('contacts-view').classList.remove('hidden');
            document.getElementById('messages-view').classList.add('hidden');
            document.getElementById('chat-back-header').classList.add('hidden');

            activeReceiverId = null;
            document.getElementById('message-input').disabled = true;
            document.getElementById('send-btn').disabled = true;
            document.getElementById('attach-btn').disabled = true;
        }

        function showChatView(receiverId, receiverName, receiverInitials) {
            // Hide contacts, show messages
            document.getElementById('contacts-view').classList.add('hidden');
            document.getElementById('messages-view').classList.remove('hidden');
            document.getElementById('chat-back-header').classList.remove('hidden');

            // Update header
            document.getElementById('chat-avatar').textContent = receiverInitials;
            document.getElementById('chat-name').textContent = receiverName;

            // Enable input
            document.getElementById('message-input').disabled = false;
            document.getElementById('message-input').placeholder = 'Type your message...';
            document.getElementById('send-btn').disabled = false;
            document.getElementById('attach-btn').disabled = false;

            // Set active receiver
            activeReceiverId = receiverId;
            activeReceiverName = receiverName;
            activeReceiverInitials = receiverInitials;

            // Mark as read and load messages
            markAsRead(receiverId);
            loadMessages(true);
        }

        function loadContacts(searchQuery = '') {
            const params = new URLSearchParams({
                search: searchQuery
            });

            fetch(`get_contacts.php?${params}`)
                .then(res => {
                    if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                    return res.json();
                })
                .then(response => {
                    let data = Array.isArray(response) ? response : (response.data || []);
                    if (response.success === false) {
                        showError(response.message || 'Failed to load contacts');
                        return;
                    }
                    allContacts = data;
                    renderContacts(data);
                })
                .catch(err => {
                    console.error("Error loading contacts:", err);
                    const container = document.getElementById('chat-list-container');
                    container.innerHTML = `
                        <div class="text-center py-8">
                            <i class="bi bi-wifi-off text-red-300 text-5xl mb-3"></i>
                            <p class="text-gray-600 font-medium text-sm">Failed to load</p>
                        </div>
                    `;
                });
        }

        function renderContacts(data) {
            const container = document.getElementById('chat-list-container');

            if (data.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-12">
                        <i class="bi bi-people text-gray-300 text-5xl mb-3"></i>
                        <p class="text-gray-400 text-sm">No contacts found</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = '';

            data.forEach(contact => {
                const unreadClass = contact.has_unread ? 'font-bold text-gray-900' : 'text-gray-700';
                const lastMsgClass = contact.has_unread ? 'font-semibold text-gray-900' : 'text-gray-500';
                const timeDisplay = formatLastMessageTime(contact.last_message_time);

                const contactHTML = `
                    <div class="contact-item relative bg-white border border-gray-200 rounded-xl p-3 mb-2 cursor-pointer hover:shadow-sm transition-all"
                         data-receiver-id="${contact.user_id}" 
                         data-receiver-name="${contact.name}"
                         data-receiver-initials="${contact.initials}">
                        ${contact.unread_count > 0 ? `<div class="contact-badge">${contact.unread_count}</div>` : ''}
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 flex-1 min-w-0">
                                <div class="w-11 h-11 bg-gradient-to-br ${contact.avatar_color} rounded-full flex items-center justify-center flex-shrink-0 text-white font-bold text-sm">
                                    ${contact.initials}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="${unreadClass} text-sm truncate">${contact.name}</p>
                                    <p class="text-xs truncate ${lastMsgClass}">${contact.last_message || 'No messages yet'}</p>
                                </div>
                            </div>
                            <span class="text-xs text-gray-400 ml-2 flex-shrink-0">${timeDisplay}</span>
                        </div>
                    </div>
                `;

                container.insertAdjacentHTML('beforeend', contactHTML);
            });

            attachContactListeners();
        }

        function attachContactListeners() {
            document.querySelectorAll('.contact-item').forEach(item => {
                item.addEventListener('click', function() {
                    const receiverId = this.getAttribute('data-receiver-id');
                    const receiverName = this.getAttribute('data-receiver-name');
                    const receiverInitials = this.getAttribute('data-receiver-initials');

                    showChatView(receiverId, receiverName, receiverInitials);
                });
            });
        }

        function markAsRead(contactId) {
            fetch('mark_read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        contact_id: contactId
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) loadContacts();
                })
                .catch(console.error);
        }

        function loadMessages(forceScrollToBottom = false) {
            if (!activeReceiverId) return;

            fetch(`get_messages.php?sender_id=${currentUserId}&receiver_id=${activeReceiverId}`)
                .then(res => {
                    if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                    return res.json();
                })
                .then(response => {
                    let data = Array.isArray(response) ? response : (response.data || []);
                    if (response.success === false) {
                        showError(response.message || 'Failed to load messages');
                        return;
                    }
                    renderMessages(data, forceScrollToBottom);
                })
                .catch(err => {
                    console.error("Error loading messages:", err);
                });
        }

        function renderMessages(data, forceScrollToBottom = false) {
            const container = document.getElementById('messages-container');
            const wasScrolledToBottom = container.scrollHeight - container.scrollTop <= container.clientHeight + 100;

            container.innerHTML = '';

            if (data.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-12">
                        <i class="bi bi-chat-dots text-gray-300 text-5xl mb-3"></i>
                        <p class="text-gray-400 text-sm">No messages yet</p>
                    </div>
                `;
                return;
            }

            const sortedData = data.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
            let lastDate = null;

            sortedData.forEach(msg => {
                const msgDate = formatMessageDate(msg.created_at);
                const isSelf = msg.sender_id == currentUserId;

                if (msgDate !== lastDate) {
                    container.insertAdjacentHTML('beforeend', `
                        <div class="flex justify-center my-3">
                            <div class="text-gray-500 text-xs font-medium px-3 py-1 bg-white rounded-full shadow-sm border">
                                ${msgDate}
                            </div>
                        </div>
                    `);
                    lastDate = msgDate;
                }

                let bubbleContent = '';
                if (msg.image_path && msg.image_path.trim() !== '') {
                    bubbleContent = `<img src="${msg.image_path}" 
                        alt="Sent image" 
                        class="max-w-[200px] rounded-lg cursor-pointer hover:opacity-90 transition"
                        onclick="openImageModal('${msg.image_path}')"
                        onerror="this.style.display='none'; this.parentElement.innerHTML='<p class=\\'text-xs italic\\'>Image unavailable</p>';">`;
                } else if (msg.message && msg.message.trim() !== '') {
                    const escapedMessage = msg.message
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;')
                        .replace(/\n/g, '<br>');
                    bubbleContent = `<p class="text-sm break-words">${escapedMessage}</p>`;
                }

                const timeOnly = new Date(msg.created_at.replace(" ", "T"))
                    .toLocaleTimeString('en-US', {
                        hour: 'numeric',
                        minute: '2-digit'
                    });

                const messageHTML = `
                    <div class="flex ${isSelf ? 'justify-end' : 'justify-start'} mb-2">
                        <div class="max-w-[75%]">
                            <div class="flex items-end gap-2">
                                ${!isSelf ? `
                                    <div class="w-7 h-7 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full 
                                    flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                        ${activeReceiverInitials}
                                    </div>` 
                                : ''}
                                <div class="${isSelf
                                    ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-2xl rounded-tr-sm px-3 py-2'
                                    : 'bg-white border border-gray-200 text-gray-800 rounded-2xl rounded-tl-sm px-3 py-2'}">
                                    ${bubbleContent}
                                </div>
                            </div>
                            <p class="text-[10px] text-gray-400 mt-1 ${isSelf ? 'text-right' : 'ml-9'}">
                                ${timeOnly}
                            </p>
                        </div>
                    </div>
                `;

                container.insertAdjacentHTML('beforeend', messageHTML);
            });

            if (forceScrollToBottom || wasScrolledToBottom) {
                setTimeout(() => {
                    container.scrollTop = container.scrollHeight;
                }, 100);
            }
        }

        function sendMessage(formData) {
            fetch('send_message.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => {
                    if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        document.getElementById('message-input').value = '';
                        document.getElementById('image-input').value = '';
                        clearImagePreview();
                        loadMessages(true);
                        loadContacts();
                    } else {
                        showError(data.message || 'Failed to send message');
                    }
                })
                .catch(err => {
                    console.error("Error sending message:", err);
                    showError('Failed to send message');
                });
        }

        function clearImagePreview() {
            selectedImageFile = null;
            document.getElementById('image-preview-area').classList.add('hidden');
            document.getElementById('preview-image').src = '';
            document.getElementById('image-input').value = '';
        }

        function openImageModal(src) {
            const modal = document.getElementById('imageModal');
            document.getElementById('modalImage').src = src;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeImageModal() {
            const modal = document.getElementById('imageModal');
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }

        // Event Listeners
        document.getElementById('send-btn').addEventListener('click', () => {
            if (!activeReceiverId) {
                showError('Please select a contact first');
                return;
            }

            const input = document.getElementById('message-input');
            const message = input.value.trim();

            if (message === '' && !selectedImageFile) {
                showError('Please enter a message or select an image');
                return;
            }

            const formData = new FormData();
            formData.append('sender_id', currentUserId);
            formData.append('receiver_id', activeReceiverId);
            formData.append('message', message);

            if (selectedImageFile) {
                formData.append('image', selectedImageFile);
            }

            sendMessage(formData);
        });

        document.getElementById('message-input').addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                document.getElementById('send-btn').click();
            }
        });

        document.getElementById('attach-btn').addEventListener('click', () => {
            if (!activeReceiverId) {
                showError('Please select a contact first');
                return;
            }
            document.getElementById('image-input').click();
        });

        document.getElementById('image-input').addEventListener('change', function() {
            if (!activeReceiverId) return;

            const file = this.files[0];
            if (!file) return;

            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                showError('Please select a valid image file');
                this.value = '';
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                showError('Image must be less than 5MB');
                this.value = '';
                return;
            }

            selectedImageFile = file;

            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-image').src = e.target.result;
                document.getElementById('image-preview-area').classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        });

        document.getElementById('search-input').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const searchQuery = this.value.trim();
            isSearching = searchQuery.length > 0;

            searchTimeout = setTimeout(() => {
                loadContacts(searchQuery);
                isSearching = false;
            }, 500);
        });

        // Initialize
        loadContacts();

        contactsRefreshInterval = setInterval(() => {
            if (!isSearching && !activeReceiverId) {
                loadContacts();
            }
        }, 5000);

        messagesRefreshInterval = setInterval(() => {
            if (activeReceiverId) {
                loadMessages();
            }
        }, 3000);

        window.addEventListener('beforeunload', () => {
            clearInterval(contactsRefreshInterval);
            clearInterval(messagesRefreshInterval);
        });
    </script>

</body>

</html>