<?php
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('admin');

require_once '../Database/db_connection.php';

$database = new Database();
$db = $database->getConnect();

// Get session values for JavaScript
$user_id = $_SESSION['id'] ?? null;
$user_role = $_SESSION['role'] ?? null;
$user_name = $_SESSION['name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RCH Water - Admin Messages</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
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
            top: 8px;
            right: 8px;
            width: 12px;
            height: 12px;
            background: #ef4444;
            border-radius: 50%;
            border: 2px solid white;
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

                    <!-- Chat list panel -->
                    <div class="w-80 flex flex-col bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">

                        <!-- Search Bar -->
                        <div class="p-4 border-b border-gray-100">
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                                <input type="text" id="search-input" placeholder="Search chats..."
                                    class="w-full pl-10 pr-4 py-2 bg-gray-50 rounded-lg border border-gray-200 focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400 text-sm transition">
                            </div>
                        </div>

                        <!-- Chat List -->
                        <div class="flex-1 overflow-y-auto" id="chat-list-container">
                            <div class="flex items-center justify-center h-full">
                                <div class="typing-indicator">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chat main area -->
                    <div class="flex-1 flex flex-col bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <!-- Chat Header -->
                        <div class="p-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-blue-100 flex items-center justify-between" id="chat-header">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-gray-300 to-gray-400 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                    <i class="fas fa-comments"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">Select a contact</p>
                                    <p class="text-xs text-gray-500">Choose someone to start chatting</p>
                                </div>
                            </div>
                        </div>

                        <!-- Chat Messages -->
                        <div class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50 chat-messages" id="messages-container">
                            <div class="flex items-center justify-center h-full">
                                <div class="text-center">
                                    <i class="fas fa-comments text-gray-300 text-6xl mb-4"></i>
                                    <p class="text-gray-400 text-lg">Select a contact to view messages</p>
                                </div>
                            </div>
                        </div>

                        <!-- Chat Input -->
                        <div class="p-4 border-t border-gray-100 bg-white" id="chat-input-area">
                            <div class="flex items-end gap-3">
                                <button id="attach-btn" class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition text-gray-600 disabled:opacity-50" disabled>
                                    <i class="fas fa-paperclip text-lg"></i>
                                </button>
                                <div class="flex-1 flex items-center gap-2 bg-gray-50 rounded-full px-4 py-2 border border-gray-200 focus-within:border-blue-400 focus-within:ring-1 focus-within:ring-blue-400 transition">
                                    <input type="text" id="message-input" placeholder="Select a contact to start messaging..."
                                        class="flex-1 bg-transparent text-sm outline-none text-gray-800 placeholder-gray-400" disabled>
                                    <button class="flex-shrink-0 text-gray-400 hover:text-blue-600 transition">
                                        <i class="fas fa-face-smile text-lg"></i>
                                    </button>
                                </div>
                                <button id="send-btn" class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center rounded-full hover:shadow-lg transition text-white disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                    <i class="fas fa-paper-plane text-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden file input -->
        <input type="file" id="image-input" accept="image/*" style="display: none;">

        <!-- Image Preview Modal -->
        <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-70 hidden items-center justify-center z-50 cursor-pointer">
            <div class="relative max-w-[90%] max-h-[90%]">
                <img id="modalImage" src="" class="max-w-full max-h-[90vh] rounded-lg shadow-lg" alt="Preview">
                <button class="absolute top-4 right-4 w-10 h-10 bg-white rounded-full flex items-center justify-center text-gray-800 hover:bg-gray-100 transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

    </div>

    <script>
        // PHP session values passed to JavaScript
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

        // Validate session
        if (!currentUserId) {
            Swal.fire({
                icon: 'error',
                title: 'Session Error',
                text: 'User session not found. Please log in again.',
                confirmButtonColor: '#3b82f6'
            }).then(() => {
                window.location.href = '../login.php';
            });
        }

        // Utility functions
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
                console.error('Error formatting time:', e);
                return "";
            }
        }
            function formatLastMessageTime(timestamp) {
                if (!timestamp) return "";

                try {
                    // Handle both formats: "YYYY-MM-DD HH:MM:SS" and ISO format
                    const date = new Date(timestamp.replace(" ", "T"));

                    if (isNaN(date.getTime())) return "—";

                    const now = new Date();
                    const diffMs = now - date;
                    const diffMins = Math.floor(diffMs / 60000);
                    const diffHours = Math.floor(diffMs / 3600000);
                    const diffDays = Math.floor(diffMs / 86400000);

                    if (diffMins < 1) return 'just now';
                    if (diffMins < 60) return `${diffMins}m`;
                    if (diffHours < 24) return `${diffHours}h`;
                    if (diffDays < 7) return `${diffDays}d`;
                    if (diffDays < 365) {
                        return date.toLocaleDateString('en-US', {
                            month: 'short',
                            day: 'numeric'
                        });
                    }
                    return date.toLocaleDateString('en-US', {
                        month: 'short',
                        day: 'numeric',
                        year: 'numeric'
                    });
                } catch (e) {
                    console.error("Error formatting time:", e, timestamp);
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
                        console.log("Contacts response:", response);

                        // Handle both old format (array) and new format (object with data property)
                        let data = Array.isArray(response) ? response : (response.data || []);

                        if (response.success === false) {
                            console.error("API Error:", response.message);
                            showError(response.message || 'Failed to load contacts');
                            return;
                        }

                        allContacts = data;
                        renderContacts(data);
                    })
                    .catch(err => {
                        console.error("Error loading contacts:", err);
                        showError('Failed to load contacts. Please check console for details.');

                        // Show empty state
                        const container = document.getElementById('chat-list-container');
                        container.innerHTML = `
                        <div class="flex items-center justify-center h-full p-4">
                            <div class="text-center">
                                <i class="fas fa-exclamation-triangle text-red-400 text-4xl mb-3"></i>
                                <p class="text-gray-600 font-medium">Failed to load contacts</p>
                                <p class="text-gray-400 text-sm mt-1">Please refresh the page</p>
                            </div>
                        </div>
                    `;
                    });
            }

            function renderContacts(data) {
                const container = document.getElementById('chat-list-container');

                if (data.length === 0) {
                    container.innerHTML = `
                    <div class="flex items-center justify-center h-full p-4">
                        <p class="text-gray-400 text-center">No contacts found</p>
                    </div>
                `;
                    return;
                }

                container.innerHTML = '';

                // Organize by type
                const customers = data.filter(c => c.type === 'customer');
                const riders = data.filter(c => c.type === 'rider');
                const admins = data.filter(c => c.type === 'admin');

                // Render customers
                if (customers.length > 0) {
                    container.innerHTML += renderContactSection('Customers', customers, 'fa-user-circle', 'blue');
                }

                // Render riders
                if (riders.length > 0) {
                    container.innerHTML += renderContactSection('Riders', riders, 'fa-motorcycle', 'orange');
                }

                // Render admins
                if (admins.length > 0) {
                    container.innerHTML += renderContactSection('Admins', admins, 'fa-shield-alt', 'purple');
                }

                attachContactListeners();
                restoreActiveContact();
            }

            function renderContactSection(title, contacts, icon, color) {
                let html = `
                <div class="px-3 py-2 ${title !== 'Customers' ? 'border-t border-gray-200' : ''}">
                    <p class="text-xs font-semibold text-gray-600 px-2 mb-2 uppercase tracking-wide">
                        <i class="fas ${icon} text-${color}-500 mr-1"></i>
                        ${title}
                    </p>
            `;

                contacts.forEach(contact => {
                    const isActive = activeReceiverId == contact.user_id;
                    const unreadClass = contact.has_unread ? 'font-bold text-gray-900' : 'font-semibold text-gray-800';
                    const lastMsgClass = contact.has_unread ? 'font-bold text-gray-900' : 'text-gray-600';

                    html += `
                    <div class="chat-item relative p-3 rounded-lg mb-2 cursor-pointer transition ${isActive ? 'bg-blue-50 border-l-4 border-blue-500' : 'hover:bg-gray-50'}" 
                         data-receiver-id="${contact.user_id}" 
                         data-receiver-name="${contact.name}"
                         data-receiver-initials="${contact.initials}">
                        ${contact.has_unread ? '<div class="contact-badge"></div>' : ''}
                        <div class="flex items-start justify-between">
                            <div class="flex items-start gap-2 flex-1">
                                <div class="w-10 h-10 bg-gradient-to-br ${contact.avatar_color} rounded-full flex items-center justify-center flex-shrink-0 text-white font-bold text-sm">
                                    ${contact.initials}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="${unreadClass} text-sm truncate">${contact.name}</p>
                                    <p class="text-xs truncate ${lastMsgClass}">${contact.last_message || 'No messages yet'}</p>
                                </div>
                            </div>
                            <span class="text-xs text-gray-500 ml-2 flex-shrink-0">${contact.time_ago || ''}</span>
                        </div>
                    </div>
                `;
                });

                html += `</div>`;
                return html;
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
                        const receiverInitials = this.getAttribute('data-receiver-initials');

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
                        activeReceiverInitials = receiverInitials;

                        // Enable input
                        document.getElementById('message-input').disabled = false;
                        document.getElementById('message-input').placeholder = 'Type your message...';
                        document.getElementById('send-btn').disabled = false;
                        document.getElementById('attach-btn').disabled = false;

                        // Update chat header
                        updateChatHeader(receiverName, receiverInitials);

                        // Mark as read
                        markAsRead(receiverId);

                        // Load messages
                        loadMessages();
                    });
                });
            }

            function updateChatHeader(name, initials) {
                const header = document.getElementById('chat-header');
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

            function markAsRead(contactId) {
                fetch('mark_read.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            contact_id: contactId
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            loadContacts(); // Refresh to remove unread badge
                        }
                    })
                    .catch(console.error);
            }

            function loadMessages() {
                if (!activeReceiverId) return;

                fetch(`get_messages.php?sender_id=${currentUserId}&receiver_id=${activeReceiverId}`)
                    .then(res => {
                        if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                        return res.json();
                    })
                    .then(response => {
                        console.log("Messages response:", response);

                        // Handle both old format (array) and new format (object with data property)
                        let data = Array.isArray(response) ? response : (response.data || []);

                        if (response.success === false) {
                            console.error("API Error:", response.message);
                            showError(response.message || 'Failed to load messages');
                            return;
                        }

                        renderMessages(data);
                    })
                    .catch(err => {
                        console.error("Error loading messages:", err);
                        showError('Failed to load messages. Please check console for details.');
                    });
            }

            function renderMessages(data) {
                const container = document.getElementById('messages-container');
                const wasScrolledToBottom = container.scrollHeight - container.scrollTop <= container.clientHeight + 100;

                container.innerHTML = '';

                if (data.length === 0) {
                    container.innerHTML = `
            <div class="flex items-center justify-center h-full">
                <div class="text-center">
                    <i class="fas fa-comment-slash text-gray-300 text-5xl mb-3"></i>
                    <p class="text-gray-400">No messages yet. Start the conversation!</p>
                </div>
            </div>
        `;
                    return;
                }

                const sortedData = data.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

                sortedData.forEach(msg => {
                    const isSelf = msg.sender_id == currentUserId;

                    let bubbleContent = '';
                    if (msg.image_path && msg.image_path.trim() !== '') {
                        // FIXED: Properly construct image path
                        // The image_path from DB is stored as "uploads/filename"
                        // Since we're in /Admin/admin_messages.php, images are in /Admin/uploads/
                        let imagePath = msg.image_path;

                        console.log('Original image path from DB:', imagePath);

                        // If it doesn't start with http/https and doesn't start with /, it's relative
                        if (!imagePath.startsWith('http') && !imagePath.startsWith('/')) {
                            // Image path is "uploads/filename", which is correct for current location
                            imagePath = imagePath;
                        }

                        console.log('Final image path:', imagePath);

                        bubbleContent = `<img src="${imagePath}" 
                alt="Sent image" 
                class="max-w-xs rounded-lg cursor-pointer hover:opacity-90 transition shadow-sm" 
                onerror="console.error('Failed to load image:', this.src); this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect fill=%22%23ddd%22 width=%22200%22 height=%22200%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%23999%22 font-size=%2214%22%3EImage not found%3C/text%3E%3Ctext x=%2250%25%22 y=%2265%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%23999%22 font-size=%2210%22%3E${imagePath}%3C/text%3E%3C/svg%3E';">`;
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

                    const messageHTML = `
            <div class="flex ${isSelf ? 'justify-end' : 'justify-start'} mb-3">
                <div class="max-w-xs lg:max-w-md">
                    <div class="flex items-end gap-2">
                        ${!isSelf ? `<div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                            ${activeReceiverInitials}
                        </div>` : ''}
                        <div class="${isSelf 
                            ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-3xl rounded-tr-sm px-4 py-2.5 shadow-sm'
                            : 'bg-white border border-gray-200 text-gray-800 rounded-3xl rounded-tl-sm px-4 py-2.5 shadow-sm'}">
                            ${bubbleContent}
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ${isSelf ? 'text-right' : 'ml-10'}">
                        ${formatMessageTime(msg.created_at)}
                    </p>
                </div>
            </div>
        `;

                    container.insertAdjacentHTML('beforeend', messageHTML);
                });

                // Attach image click listeners
                container.querySelectorAll('img').forEach(img => {
                    img.addEventListener('click', () => openImageModal(img.src));
                });

                if (wasScrolledToBottom) {
                    container.scrollTop = container.scrollHeight;
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
                        console.log("Send message response:", data);

                        if (data.success) {
                            document.getElementById('message-input').value = '';
                            document.getElementById('image-input').value = '';
                            loadMessages();
                            loadContacts(); // Update last message in contact list
                        } else {
                            showError(data.message || 'Failed to send message');
                        }
                    })
                    .catch(err => {
                        console.error("Error sending message:", err);
                        showError('Failed to send message. Please try again.');
                    });
            }

            // Event Listeners
            document.getElementById('send-btn').addEventListener('click', () => {
                if (!activeReceiverId) {
                    showError('Please select a contact first');
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

            // Send on Enter key
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

                console.log('File selected:', file.name, file.type, file.size);

                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    showError('Please select a valid image file (JPEG, PNG, GIF, or WebP)');
                    this.value = '';
                    return;
                }

                // Validate file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    showError('Image size must be less than 5MB');
                    this.value = '';
                    return;
                }

                // Show loading indicator
                const messagesContainer = document.getElementById('messages-container');
                const loadingHTML = `
        <div id="upload-indicator" class="flex justify-end mb-3">
            <div class="bg-blue-100 rounded-lg px-4 py-2 flex items-center gap-2">
                <div class="typing-indicator">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <span class="text-xs text-blue-600">Uploading image...</span>
            </div>
        </div>
    `;
                messagesContainer.insertAdjacentHTML('beforeend', loadingHTML);
                messagesContainer.scrollTop = messagesContainer.scrollHeight;

                const formData = new FormData();
                formData.append('sender_id', currentUserId);
                formData.append('receiver_id', activeReceiverId);
                formData.append('image', file);

                console.log('Uploading image...');

                fetch('send_message.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => {
                        console.log('Response status:', res.status);
                        if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                        return res.json();
                    })
                    .then(data => {
                        console.log('Upload response:', data);

                        // Remove loading indicator
                        const indicator = document.getElementById('upload-indicator');
                        if (indicator) indicator.remove();

                        if (data.success) {
                            console.log('Image uploaded successfully:', data.image_path);
                            this.value = '';

                            // Small delay before reloading to ensure DB is updated
                            setTimeout(() => {
                                loadMessages();
                                loadContacts();
                            }, 300);
                        } else {
                            console.error('Upload failed:', data.message);
                            showError(data.message || 'Failed to upload image');
                        }
                    })
                    .catch(err => {
                        // Remove loading indicator
                        const indicator = document.getElementById('upload-indicator');
                        if (indicator) indicator.remove();

                        console.error("Error uploading image:", err);
                        showError('Failed to upload image. Please try again.');
                        this.value = '';
                    });
            });
            // Search handler
            document.getElementById('search-input').addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const searchQuery = this.value.trim();

                isSearching = searchQuery.length > 0;

                searchTimeout = setTimeout(() => {
                    loadContacts(searchQuery);
                    isSearching = false;
                }, 500);
            });

            // Image modal
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

            // Initialize
            loadContacts();

            // Auto-refresh intervals
            contactsRefreshInterval = setInterval(() => {
                if (!isSearching) {
                    loadContacts();
                }
            }, 5000);

            messagesRefreshInterval = setInterval(() => {
                if (activeReceiverId) {
                    loadMessages();
                }
            }, 3000);

            // Cleanup on page unload
            window.addEventListener('beforeunload', () => {
                clearInterval(contactsRefreshInterval);
                clearInterval(messagesRefreshInterval);
            });
    </script>

</body>

</html>