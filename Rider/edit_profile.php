<?php
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('rider');
date_default_timezone_set('Asia/Manila');

require_once '../Database/db_connection.php';

$database = new Database();
$db = $database->getConnect();

$user_id = $_SESSION['id'] ?? null;
$rider_name = $_SESSION['name'] ?? 'Rider';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | RCH Water</title>
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

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stat-card {
            animation: slideIn 0.3s ease-out forwards;
        }

        .progress-ring {
            transform: rotate(-90deg);
        }

        .progress-ring-circle {
            transition: stroke-dashoffset 0.5s ease;
        }
    </style>
</head>

<body class="bg-white min-h-screen flex justify-center items-center relative">

    <!-- Fixed-width container wrapper -->
    <div class="bg-white w-[375px] h-[750px] mx-auto relative rounded-3xl overflow-hidden border shadow-lg">

        <!-- Header -->
        <div class="bg-gradient-to-br from-blue-400 to-blue-600 text-white sticky top-0 z-50 w-full px-4 py-3">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-bold tracking-tight">My Profile</h2>
                    <div id="datetime" class="text-xs text-blue-100 font-medium"></div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="../Notification/rider_notifications.php" class="relative text-white text-lg hover:scale-110 transition-transform duration-200">
                        <i class="bi bi-bell"></i>
                    </a>
                     <a href="../Messaging/rider_messages.php"
                        class="text-white text-lg hover:scale-110 transition-transform duration-200">
                         <i class="bi bi-chat-dots"></i>
                    </a>
                    <a href="dashboard.php"
                        class="text-white text-lg hover:scale-110 transition-transform duration-200">
                        <i class="bi bi-arrow-left-circle"></i>
                        </a>
                </div>
            </div>
        </div>

        <!-- Profile Section Tabs -->
        <div class="bg-white sticky top-16 z-40 w-full px-5 py-4">
            <div class="grid grid-cols-2 gap-3">
                <button onclick="showSection('profile-info', this)" class="section-btn bg-blue-600 text-white font-semibold py-2 px-4 rounded-3xl text-xs shadow-sm transition-all duration-200">Profile</button>
                <button onclick="showSection('stats-info', this)" class="section-btn bg-blue-100 text-blue-600 font-semibold py-2 px-4 rounded-3xl text-xs shadow-sm transition-all duration-200">Statistics</button>
            </div>
        </div>

        <!-- PROFILE INFO SECTION -->
        <div class="px-5 pb-5 overflow-y-auto custom-scrollbar h-[570px]" id="profile-info">
            <!-- Profile Card -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-5 mb-4 border border-blue-200 shadow-sm">
                <div class="flex flex-col items-center">
                    <div class="w-24 h-24 bg-gradient-to-br from-blue-500 to-blue-700 rounded-full flex items-center justify-center text-white font-bold text-3xl mb-3 shadow-lg border-4 border-white">
                        <span id="profile-initials">R</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800" id="profile-name">Loading...</h3>
                    <p class="text-sm text-blue-600 font-medium mb-1" id="profile-email">rider@example.com</p>
                    <span class="text-xs bg-green-100 text-green-700 px-3 py-1 rounded-full font-semibold flex items-center gap-1">
                        <i class="bi bi-circle-fill text-green-500" style="font-size: 0.4rem;"></i>
                        Active Rider
                    </span>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-white rounded-2xl p-4 mb-4 border border-gray-200 shadow-sm">
                <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <i class="bi bi-info-circle text-blue-500"></i>
                    Contact Information
                </h4>
                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <i class="bi bi-telephone text-blue-500 mt-1"></i>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500">Phone Number</p>
                            <p class="text-sm font-semibold text-gray-800" id="profile-phone">Loading...</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <i class="bi bi-geo-alt text-blue-500 mt-1"></i>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500">Address</p>
                            <p class="text-sm font-semibold text-gray-800" id="profile-address">Loading...</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <i class="bi bi-calendar text-blue-500 mt-1"></i>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500">Member Since</p>
                            <p class="text-sm font-semibold text-gray-800" id="profile-joined">Loading...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vehicle Information -->
            <div class="bg-white rounded-2xl p-4 mb-4 border border-gray-200 shadow-sm">
                <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <i class="bi bi-motorcycle text-orange-500"></i>
                    Vehicle Information
                </h4>
                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <i class="bi bi-card-text text-orange-500 mt-1"></i>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500">License Plate</p>
                            <p class="text-sm font-semibold text-gray-800" id="vehicle-plate">ABC-1234</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <i class="bi bi-tools text-orange-500 mt-1"></i>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500">Vehicle Type</p>
                            <p class="text-sm font-semibold text-gray-800" id="vehicle-type">Motorcycle</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-sm">
                <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <i class="bi bi-gear text-gray-600"></i>
                    Quick Actions
                </h4>
                <div class="space-y-2">
                    <button class="w-full flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition">
                        <span class="text-sm font-medium text-gray-700 flex items-center gap-2">
                            <i class="bi bi-pencil-square text-blue-500"></i>
                            Edit Profile
                        </span>
                        <i class="bi bi-chevron-right text-gray-400"></i>
                    </button>
                    <button class="w-full flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition">
                        <span class="text-sm font-medium text-gray-700 flex items-center gap-2">
                            <i class="bi bi-shield-lock text-green-500"></i>
                            Change Password
                        </span>
                        <i class="bi bi-chevron-right text-gray-400"></i>
                    </button>
                    <button onclick="logout()" class="w-full flex items-center justify-between p-3 bg-red-50 hover:bg-red-100 rounded-lg transition">
                        <span class="text-sm font-medium text-red-600 flex items-center gap-2">
                            <i class="bi bi-box-arrow-right"></i>
                            Logout
                        </span>
                        <i class="bi bi-chevron-right text-red-400"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- STATISTICS SECTION -->
        <div class="px-5 pb-5 overflow-y-auto custom-scrollbar h-[570px] hidden" id="stats-info">
            <!-- Summary Cards -->
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="stat-card bg-gradient-to-br from-green-400 to-green-600 rounded-2xl p-4 text-white shadow-md" style="animation-delay: 0.1s">
                    <div class="flex items-center justify-between mb-2">
                        <i class="bi bi-check-circle text-2xl"></i>
                        <span class="text-xs bg-white bg-opacity-20 px-2 py-1 rounded-full">Total</span>
                    </div>
                    <p class="text-2xl font-bold" id="total-completed">0</p>
                    <p class="text-xs font-medium opacity-90">Completed</p>
                </div>

                <div class="stat-card bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl p-4 text-white shadow-md" style="animation-delay: 0.2s">
                    <div class="flex items-center justify-between mb-2">
                        <i class="bi bi-truck text-2xl"></i>
                        <span class="text-xs bg-white bg-opacity-20 px-2 py-1 rounded-full">Active</span>
                    </div>
                    <p class="text-2xl font-bold" id="total-active">0</p>
                    <p class="text-xs font-medium opacity-90">In Progress</p>
                </div>

                <div class="stat-card bg-gradient-to-br from-orange-400 to-orange-600 rounded-2xl p-4 text-white shadow-md" style="animation-delay: 0.3s">
                    <div class="flex items-center justify-between mb-2">
                        <i class="bi bi-clock-history text-2xl"></i>
                        <span class="text-xs bg-white bg-opacity-20 px-2 py-1 rounded-full">Pending</span>
                    </div>
                    <p class="text-2xl font-bold" id="total-pending">0</p>
                    <p class="text-xs font-medium opacity-90">Waiting</p>
                </div>

                <div class="stat-card bg-gradient-to-br from-purple-400 to-purple-600 rounded-2xl p-4 text-white shadow-md" style="animation-delay: 0.4s">
                    <div class="flex items-center justify-between mb-2">
                        <i class="bi bi-star text-2xl"></i>
                        <span class="text-xs bg-white bg-opacity-20 px-2 py-1 rounded-full">Rating</span>
                    </div>
                    <p class="text-2xl font-bold" id="rating-score">5.0</p>
                    <p class="text-xs font-medium opacity-90">Average</p>
                </div>
            </div>

            <!-- Performance Chart -->
            <div class="stat-card bg-white rounded-2xl p-4 mb-4 border border-gray-200 shadow-sm" style="animation-delay: 0.5s">
                <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <i class="bi bi-graph-up text-blue-500"></i>
                    Weekly Performance
                </h4>
                <div class="flex items-end justify-between gap-2 h-32">
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-full bg-gray-200 rounded-t-lg overflow-hidden flex flex-col-reverse" style="height: 100%;">
                            <div class="bg-gradient-to-t from-blue-500 to-blue-400 rounded-t-lg" id="bar-mon" style="height: 40%;"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2 font-medium">Mon</p>
                    </div>
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-full bg-gray-200 rounded-t-lg overflow-hidden flex flex-col-reverse" style="height: 100%;">
                            <div class="bg-gradient-to-t from-blue-500 to-blue-400 rounded-t-lg" id="bar-tue" style="height: 60%;"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2 font-medium">Tue</p>
                    </div>
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-full bg-gray-200 rounded-t-lg overflow-hidden flex flex-col-reverse" style="height: 100%;">
                            <div class="bg-gradient-to-t from-blue-500 to-blue-400 rounded-t-lg" id="bar-wed" style="height: 80%;"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2 font-medium">Wed</p>
                    </div>
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-full bg-gray-200 rounded-t-lg overflow-hidden flex flex-col-reverse" style="height: 100%;">
                            <div class="bg-gradient-to-t from-blue-500 to-blue-400 rounded-t-lg" id="bar-thu" style="height: 50%;"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2 font-medium">Thu</p>
                    </div>
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-full bg-gray-200 rounded-t-lg overflow-hidden flex flex-col-reverse" style="height: 100%;">
                            <div class="bg-gradient-to-t from-blue-500 to-blue-400 rounded-t-lg" id="bar-fri" style="height: 90%;"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2 font-medium">Fri</p>
                    </div>
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-full bg-gray-200 rounded-t-lg overflow-hidden flex flex-col-reverse" style="height: 100%;">
                            <div class="bg-gradient-to-t from-blue-500 to-blue-400 rounded-t-lg" id="bar-sat" style="height: 70%;"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2 font-medium">Sat</p>
                    </div>
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-full bg-gray-200 rounded-t-lg overflow-hidden flex flex-col-reverse" style="height: 100%;">
                            <div class="bg-gradient-to-t from-blue-500 to-blue-400 rounded-t-lg" id="bar-sun" style="height: 30%;"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2 font-medium">Sun</p>
                    </div>
                </div>
            </div>

            <!-- Completion Rate -->
            <div class="stat-card bg-white rounded-2xl p-4 mb-4 border border-gray-200 shadow-sm" style="animation-delay: 0.6s">
                <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="bi bi-pie-chart text-green-500"></i>
                    Completion Rate
                </h4>
                <div class="flex items-center justify-center">
                    <div class="relative w-32 h-32">
                        <svg class="w-32 h-32 progress-ring">
                            <circle cx="64" cy="64" r="56" stroke="#e5e7eb" stroke-width="8" fill="none" />
                            <circle id="progress-circle" cx="64" cy="64" r="56" stroke="#10b981" stroke-width="8" fill="none"
                                stroke-dasharray="351.858" stroke-dashoffset="0" class="progress-ring-circle" />
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <p class="text-3xl font-bold text-gray-800" id="completion-rate">95%</p>
                            <p class="text-xs text-gray-500">Success</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Achievements -->
            <div class="stat-card bg-white rounded-2xl p-4 border border-gray-200 shadow-sm" style="animation-delay: 0.7s">
                <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <i class="bi bi-trophy text-yellow-500"></i>
                    Recent Achievements
                </h4>
                <div class="space-y-2">
                    <div class="flex items-center gap-3 p-3 bg-yellow-50 rounded-lg">
                        <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                            <i class="bi bi-star-fill text-yellow-500"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-800">100 Deliveries</p>
                            <p class="text-xs text-gray-500">Completed milestone</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-blue-50 rounded-lg">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="bi bi-lightning-fill text-blue-500"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-800">Speed Demon</p>
                            <p class="text-xs text-gray-500">Fast delivery streak</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-green-50 rounded-lg">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="bi bi-hand-thumbs-up-fill text-green-500"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-800">Customer Favorite</p>
                            <p class="text-xs text-gray-500">5-star rating streak</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
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

        function showSection(sectionId, button) {
            const sections = ['profile-info', 'stats-info'];
            sections.forEach(id => {
                document.getElementById(id).classList.add('hidden');
            });
            document.getElementById(sectionId).classList.remove('hidden');

            document.querySelectorAll('.section-btn').forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('bg-blue-100', 'text-blue-600');
            });
            button.classList.remove('bg-blue-100', 'text-blue-600');
            button.classList.add('bg-blue-600', 'text-white');
        }

        function loadProfileData() {
            // Simulated data - replace with actual API call
            const profileData = {
                name: '<?php echo $rider_name; ?>',
                email: 'rider@rchwater.com',
                phone: '+63 912 345 6789',
                address: 'Lipa City, Batangas',
                joined: 'January 15, 2024',
                initials: '<?php echo strtoupper(substr($rider_name, 0, 1)); ?>'
            };

            document.getElementById('profile-name').textContent = profileData.name;
            document.getElementById('profile-email').textContent = profileData.email;
            document.getElementById('profile-phone').textContent = profileData.phone;
            document.getElementById('profile-address').textContent = profileData.address;
            document.getElementById('profile-joined').textContent = profileData.joined;
            document.getElementById('profile-initials').textContent = profileData.initials;
        }

        function loadStatistics() {
            // Simulated data - replace with actual API call
            fetch('get_rider_stats.php')
                .then(res => res.json())
                .then(data => {
                    document.getElementById('total-completed').textContent = data.completed || '156';
                    document.getElementById('total-active').textContent = data.active || '3';
                    document.getElementById('total-pending').textContent = data.pending || '8';
                    document.getElementById('rating-score').textContent = data.rating || '4.9';

                    // Update completion rate circle
                    const completionRate = data.completion_rate || 95;
                    document.getElementById('completion-rate').textContent = completionRate + '%';

                    const circle = document.getElementById('progress-circle');
                    const circumference = 2 * Math.PI * 56; // 2πr
                    const offset = circumference - (completionRate / 100) * circumference;
                    circle.style.strokeDashoffset = offset;

                    // Update weekly chart (if data available)
                    if (data.weekly) {
                        const days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
                        days.forEach((day, index) => {
                            const percentage = data.weekly[index] || Math.random() * 100;
                            document.getElementById(`bar-${day}`).style.height = percentage + '%';
                        });
                    }
                })
                .catch(err => {
                    console.error('Error loading stats:', err);
                    // Use fallback data
                    document.getElementById('total-completed').textContent = '156';
                    document.getElementById('total-active').textContent = '3';
                    document.getElementById('total-pending').textContent = '8';
                    document.getElementById('rating-score').textContent = '4.9';
                });
        }

        function logout() {
            Swal.fire({
                title: 'Logout?',
                text: 'Are you sure you want to logout?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, logout',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../logout.php';
                }
            });
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            loadProfileData();
            loadStatistics();
        });
    </script>

</body>

</html>