<?php

require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('customer'); // only customers can access
require_once '../Database/db_connection.php';

$database = new Database();
$db = $database->getConnect();

$user_id = $_SESSION['id'];

// ===== LIPA ONLY =====
function isInLipaPHP($lat, $lng) {
    $lat = (float)$lat;
    $lng = (float)$lng;
    return $lat >= 13.85 && $lat <= 14.05 && $lng >= 121.05 && $lng <= 121.28;
}

// Fetch user data
$stmt = $db->prepare("
    SELECT u.*, ml.latitude, ml.longitude 
    FROM users u 
    LEFT JOIN map_location ml ON u.user_id = ml.user_id 
    WHERE u.user_id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'update_profile') {
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone']);
            $house_no = trim($_POST['house_no']);
            $street = trim($_POST['street']);
            $barangay = trim($_POST['barangay']);
            $city = trim($_POST['city']);
            $province = trim($_POST['province']);
            $postal_code = trim($_POST['postal_code']);

            // ===== LIPA ONLY: block address outside Lipa =====
            $cityLower = strtolower($city);
            $provLower = strtolower($province);
            if ($cityLower !== 'lipa city' && $cityLower !== 'lipa') {
                echo json_encode(['success' => false, 'message' => 'We only deliver in Lipa City']);
                exit();
            }
            if ($provLower !== 'batangas') {
                echo json_encode(['success' => false, 'message' => 'We only deliver in Lipa City']);
                exit();
            }
            if ($postal_code !== '' && $postal_code !== '4217') {
                echo json_encode(['success' => false, 'message' => 'We only deliver in Lipa City']);
                exit();
            }

            $address = "$house_no, $street, $barangay, $city, $province $postal_code";

            // Check if email already exists for another user
            $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
            $stmt->execute([$email, $user_id]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Email already exists']);
                exit();
            }

            $stmt = $db->prepare("
                UPDATE users 
                SET name = ?, email = ?, phone_number = ?, address = ?, updated_at = NOW() 
                WHERE user_id = ?
            ");
            $stmt->execute([$name, $email, $phone, $address, $user_id]);

            echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
            exit();
        } elseif ($action === 'change_password') {
            $old_password = $_POST['old_password'];
            $new_password = $_POST['new_password'];

            // Verify old password
            if (!password_verify($old_password, $user['password_hash'])) {
                echo json_encode(['success' => false, 'message' => 'Incorrect old password']);
                exit();
            }

            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);

            $stmt = $db->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE user_id = ?");
            $stmt->execute([$new_hash, $user_id]);

            echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
            exit();
        } elseif ($action === 'update_location') {
            $latitude = floatval($_POST['latitude']);
            $longitude = floatval($_POST['longitude']);

            // ===== LIPA ONLY: block pin outside Lipa =====
            if (!isInLipaPHP($latitude, $longitude)) {
                echo json_encode(['success' => false, 'message' => 'We only deliver in Lipa City']);
                exit();
            }

            // Check if location exists
            $stmt = $db->prepare("SELECT loc_id FROM map_location WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $location = $stmt->fetch();

            if ($location) {
                $stmt = $db->prepare("
                    UPDATE map_location 
                    SET latitude = ?, longitude = ?, updated_at = NOW() 
                    WHERE user_id = ?
                ");
                $stmt->execute([$latitude, $longitude, $user_id]);
            } else {
                $stmt = $db->prepare("
                    INSERT INTO map_location (user_id, latitude, longitude, created_at, updated_at) 
                    VALUES (?, ?, ?, NOW(), NOW())
                ");
                $stmt->execute([$user_id, $latitude, $longitude]);
            }

            echo json_encode(['success' => true, 'message' => 'Location updated successfully']);
            exit();
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        exit();
    }
}

// Parse address
$address_parts = explode(', ', $user['address']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>RCH Water - My Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            overflow: hidden;
        }

        .custom-scroll::-webkit-scrollbar {
            width: 3px;
        }

        .custom-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .custom-scroll::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .requirement {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.875rem;
            padding: 6px 0;
        }

        .requirement-icon {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            color: white;
            transition: all 0.2s ease;
        }

        .requirement.unmet .requirement-icon {
            background-color: #ef4444;
        }

        .requirement.met .requirement-icon {
            background-color: #22c55e;
        }

        .requirement-text {
            transition: color 0.2s ease;
        }

        .requirement.unmet .requirement-text {
            color: #7f1d1d;
        }

        .requirement.met .requirement-text {
            color: #15803d;
        }

        .password-match-message {
            min-height: 24px;
            font-size: 0.875rem;
            font-weight: 500;
            margin-top: 8px;
            padding: 4px 0;
        }

        .match-success {
            color: #22c55e;
        }

        .match-error {
            color: #ef4444;
        }

        #map {
            height: 400px;
            border-radius: 12px;
            z-index: 1;
        }

        #viewMap {
            height: 400px;
            border-radius: 12px;
            z-index: 1;
        }

        .profile-gradient {
            background: linear-gradient(135deg, #57a1f0ff 0%, #273094ff 100%);
        }

        .info-card {
            transition: all 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none;
        }
    </style>
</head>

<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <script src="includes/sidebar.js"></script>

        <!-- Main Content -->
        <main class="flex-1 ml-20 bg-gray-50">
            <?php
            $pageTitle = "Edit Profile";
            $pageSubtitle = "Manage your personal information, contact details, and delivery location";
            include 'includes/header.php';
            ?>

            <!-- PROFILE PAGE CONTENT -->
            <div class="overflow-y-auto p-8 max-w-7xl mx-auto h-[calc(100vh-120px)] custom-scroll">

                <!-- PROFILE VIEW -->

                <div id="profileView" class="overflow-y-auto"> <!-- Profile Header Card -->
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-8">
                        <div class="profile-gradient p-8 text-center">
                            <div class="flex justify-center mb-4">
                                <div class="w-32 h-32 bg-white rounded-full flex items-center justify-center shadow-xl">
                                    <i class="bi bi-person text-blue-600 text-7xl"></i>
                                </div>
                            </div>
                            <h2 class="text-3xl font-bold text-white mb-1"><?= htmlspecialchars($user['name']) ?></h2>
                            <p class="text-blue-100 text-sm mb-6"><?= htmlspecialchars($user['email']) ?></p>

                            <div class="flex gap-3 justify-center flex-wrap">
                                <button onclick="toggleEditProfile()"
                                    class="px-6 py-3 bg-white text-blue-600 rounded-lg font-semibold hover:bg-gray-50 transition flex items-center gap-2 shadow-md">
                                    <i class="bi bi-pencil"></i> Edit Profile
                                </button>

                                <button onclick="openPasswordModal()"
                                    class="px-6 py-3 bg-gray-800 text-white rounded-lg font-semibold hover:bg-gray-900 transition flex items-center gap-2 shadow-md">
                                    <i class="bi bi-key"></i> Change Password
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Information Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                        <!-- Personal Information -->
                        <div class="bg-white rounded-2xl shadow-sm p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <i class="bi bi-person-badge text-blue-600"></i>
                                Personal Information
                            </h3>
                            <div class="space-y-4">
                                <div class="info-card bg-blue-50 rounded-lg p-4 border-l-4 border-blue-600">
                                    <p class="text-gray-600 text-xs font-semibold uppercase mb-1">Full Name</p>
                                    <p class="font-semibold text-gray-800 text-lg"><?= htmlspecialchars($user['name']) ?></p>
                                </div>

                                <div class="info-card bg-green-50 rounded-lg p-4 border-l-4 border-green-600">
                                    <p class="text-gray-600 text-xs font-semibold uppercase mb-1">Phone Number</p>
                                    <p class="font-semibold text-gray-800 text-lg"><?= htmlspecialchars($user['phone_number']) ?></p>
                                </div>

                                <div class="info-card bg-purple-50 rounded-lg p-4 border-l-4 border-purple-600">
                                    <p class="text-gray-600 text-xs font-semibold uppercase mb-1">Email</p>
                                    <p class="font-semibold text-gray-800 text-lg"><?= htmlspecialchars($user['email']) ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Address Information -->
                        <div class="bg-white rounded-2xl shadow-sm p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <i class="bi bi-geo-alt text-red-600"></i>
                                Address Information
                            </h3>
                            <div class="info-card bg-orange-50 rounded-lg p-4 border-l-4 border-orange-600">
                                <p class="text-gray-600 text-xs font-semibold uppercase mb-2">Complete Address</p>
                                <p class="font-semibold text-gray-800 text-base leading-relaxed">
                                    <?= htmlspecialchars($user['address']) ?>
                                </p>
                            </div>
                            <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg p-3 mt-4">We only deliver in Lipa City.</p>
                        </div>
                    </div>

                    <!-- Map Location Section -->
                    <div class="bg-white rounded-2xl shadow-sm p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                                <i class="bi bi-map text-indigo-600"></i>
                                Delivery Location
                            </h3>
                            <button onclick="openMapModal()"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition flex items-center gap-2">
                                <i class="bi bi-pin-map"></i> Update Location
                            </button>
                        </div>

                        <div id="viewMap" class="rounded-xl overflow-hidden"></div>

                        <?php if ($user['latitude'] && $user['longitude']): ?>
                            <div class="mt-4 bg-indigo-50 rounded-lg p-4">
                                <p class="text-sm text-gray-700">
                                    <span class="font-semibold">Coordinates:</span>
                                    <?= htmlspecialchars($user['latitude']) ?>, <?= htmlspecialchars($user['longitude']) ?>
                                </p>
                            </div>
                        <?php else: ?>
                            <div class="mt-4 bg-yellow-50 rounded-lg p-4 border-l-4 border-yellow-500">
                                <p class="text-sm text-yellow-800 flex items-center gap-2">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <span>No delivery location set. Please pin your location for accurate deliveries.</span>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>

                <!-- EDIT PROFILE VIEW -->
                <div id="editProfileView" class="hidden">
                    <div class="bg-white rounded-2xl shadow-lg p-8">
                        <div class="flex items-center gap-3 mb-6 pb-4 border-b">
                            <i class="bi bi-pencil-square text-blue-600 text-3xl"></i>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800">Edit Profile</h2>
                                <p class="text-gray-600 text-sm">Update your personal information</p>
                            </div>
                        </div>

                        <form id="editProfileForm" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Full Name -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Full Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="name" required
                                        value="<?= htmlspecialchars($user['name']) ?>"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                </div>

                                <!-- Phone -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Phone Number <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="phone" required
                                        value="<?= htmlspecialchars($user['phone_number']) ?>"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                </div>
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input type="email" name="email" required
                                    value="<?= htmlspecialchars($user['email']) ?>"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            </div>

                            <!-- Address Section -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3">
                                    <i class="bi bi-house-door"></i> Complete Address <span class="text-red-500">*</span>
                                </label>
                                <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4">We only deliver in Lipa City. City, Province, and Postal Code are locked.</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <input type="text" name="house_no" placeholder="House No." required
                                        value="<?= htmlspecialchars($address_parts[0] ?? '') ?>"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">

                                    <input type="text" name="street" placeholder="Street Name" required
                                        value="<?= htmlspecialchars($address_parts[1] ?? '') ?>"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">

                                    <input type="text" name="barangay" placeholder="Barangay" required
                                        value="<?= htmlspecialchars($address_parts[2] ?? '') ?>"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">

                                    <input type="text" name="city" placeholder="City / Municipality" required readonly
                                        value="Lipa City"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">

                                    <input type="text" name="province" placeholder="Province" required readonly
                                        value="Batangas"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">

                                    <input type="text" name="postal_code" placeholder="Postal Code" required readonly
                                        value="4217"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="flex gap-4 pt-6">
                                <button type="button" onclick="toggleEditProfile()"
                                    class="flex-1 py-3 bg-gray-600 text-white rounded-lg font-semibold hover:bg-gray-700 transition">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </button>

                                <button type="submit"
                                    class="flex-1 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                                    <i class="bi bi-check-circle"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>

            <!-- CHANGE PASSWORD MODAL -->
            <div id="passwordModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                <div class="bg-white w-full max-w-md rounded-2xl p-8 shadow-2xl">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                            <i class="bi bi-shield-lock text-blue-600"></i> Change Password
                        </h3>
                        <button onclick="closePasswordModal()" class="text-gray-400 hover:text-gray-600 text-2xl">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <form id="passwordForm" class="space-y-5">
                        <!-- Old Password -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Old Password <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="password" id="oldPassword" required placeholder="Enter your current password"
                                    class="w-full px-4 py-2.5 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                <button type="button" onclick="togglePasswordVisibility('oldPassword')"
                                    class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-gray-700">
                                    <i class="bi bi-eye text-lg"></i>
                                </button>
                            </div>
                        </div>

                        <!-- New Password -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">New Password <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="password" id="newPassword" required placeholder="Enter new password"
                                    class="w-full px-4 py-2.5 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                    oninput="validatePasswordRequirements()">
                                <button type="button" onclick="togglePasswordVisibility('newPassword')"
                                    class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-gray-700">
                                    <i class="bi bi-eye text-lg"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Password Requirements -->
                        <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                            <div class="requirement unmet" id="req-length">
                                <div class="requirement-icon"><i class="bi bi-check"></i></div>
                                <span class="requirement-text">Min. 8 characters</span>
                            </div>
                            <div class="requirement unmet" id="req-uppercase">
                                <div class="requirement-icon"><i class="bi bi-check"></i></div>
                                <span class="requirement-text">Uppercase letter (A-Z)</span>
                            </div>
                            <div class="requirement unmet" id="req-lowercase">
                                <div class="requirement-icon"><i class="bi bi-check"></i></div>
                                <span class="requirement-text">Lowercase letter (a-z)</span>
                            </div>
                            <div class="requirement unmet" id="req-number">
                                <div class="requirement-icon"><i class="bi bi-check"></i></div>
                                <span class="requirement-text">Number (0-9)</span>
                            </div>
                            <div class="requirement unmet" id="req-special">
                                <div class="requirement-icon"><i class="bi bi-check"></i></div>
                                <span class="requirement-text">Special character (!@#$)</span>
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Confirm Password <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="password" id="confirmPassword" required placeholder="Confirm new password"
                                    class="w-full px-4 py-2.5 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                    oninput="validatePasswordMatch()">
                                <button type="button" onclick="togglePasswordVisibility('confirmPassword')"
                                    class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-gray-700">
                                    <i class="bi bi-eye text-lg"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Password Match Message -->
                        <div class="password-match-message" id="matchMessage"></div>

                        <!-- Buttons -->
                        <div class="flex gap-3 pt-4">
                            <button type="button" onclick="closePasswordModal()"
                                class="flex-1 py-2.5 bg-gray-600 text-white rounded-lg font-medium hover:bg-gray-700 transition flex items-center justify-center gap-2">
                                <i class="bi bi-x"></i> Cancel
                            </button>

                            <button type="submit"
                                class="flex-1 py-2.5 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition flex items-center justify-center gap-2">
                                <i class="bi bi-check"></i> Change Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- MAP LOCATION MODAL -->
            <div id="mapModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                <div class="bg-white w-full max-w-3xl rounded-2xl p-8 shadow-2xl">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                            <i class="bi bi-pin-map text-indigo-600"></i> Pin Your Location
                        </h3>
                        <button onclick="closeMapModal()" class="text-gray-400 hover:text-gray-600 text-2xl">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <p class="text-gray-600 mb-2">Click on the map to pin your exact delivery location</p>
                    <p class="text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4">We only deliver in Lipa City. Please pin inside Lipa City only.</p>

                    <div id="map"></div>

                    <div class="mt-4 bg-blue-50 rounded-lg p-4">
                        <p class="text-sm text-gray-700">
                            <span class="font-semibold">Selected Location:</span>
                            <span id="selectedCoords">Click on the map to select</span>
                        </p>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button onclick="closeMapModal()"
                            class="flex-1 py-3 bg-gray-600 text-white rounded-lg font-semibold hover:bg-gray-700 transition">
                            <i class="bi bi-x-circle"></i> Cancel
                        </button>

                        <button onclick="saveLocation()"
                            class="flex-1 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition">
                            <i class="bi bi-check-circle"></i> Save Location
                        </button>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <script>
        let map, marker;
        let selectedLat = <?= $user['latitude'] ?? 'null' ?>;
        let selectedLng = <?= $user['longitude'] ?? 'null' ?>;
        let viewMap;

        // ===== LIPA ONLY =====
        const LIPA_MIN_LAT = 13.85;
        const LIPA_MAX_LAT = 14.05;
        const LIPA_MIN_LNG = 121.05;
        const LIPA_MAX_LNG = 121.28;
        const LIPA_CENTER_LAT = 13.9411;
        const LIPA_CENTER_LNG = 121.1628;
        function isInLipa(lat, lng) {
            return lat >= LIPA_MIN_LAT && lat <= LIPA_MAX_LAT && lng >= LIPA_MIN_LNG && lng <= LIPA_MAX_LNG;
        }
        // If old saved pin is outside Lipa, ignore it and start in Lipa
        if (selectedLat !== null && selectedLng !== null && !isInLipa(selectedLat, selectedLng)) {
            selectedLat = null;
            selectedLng = null;
        }

        // Initialize view map
        function initViewMap() {
            if (viewMap) {
                viewMap.remove();
            }

            const defaultLat = selectedLat || LIPA_CENTER_LAT;
            const defaultLng = selectedLng || LIPA_CENTER_LNG;

            viewMap = L.map('viewMap', {
                maxBounds: L.latLngBounds([LIPA_MIN_LAT, LIPA_MIN_LNG], [LIPA_MAX_LAT, LIPA_MAX_LNG]),
                maxBoundsViscosity: 1.0,
                minZoom: 11
            }).setView([defaultLat, defaultLng], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(viewMap);

            if (selectedLat && selectedLng) {
                L.marker([selectedLat, selectedLng]).addTo(viewMap)
                    .bindPopup('Your Delivery Location').openPopup();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            initViewMap();
        });

        function toggleEditProfile() {
            document.getElementById("profileView").classList.toggle("hidden");
            document.getElementById("editProfileView").classList.toggle("hidden");

            if (!document.getElementById("profileView").classList.contains("hidden")) {
                setTimeout(() => {
                    initViewMap();
                }, 100);
            }
        }

        // Password Modal Functions
        function openPasswordModal() {
            document.getElementById("passwordModal").classList.remove("hidden");
            resetPasswordForm();
        }

        function closePasswordModal() {
            document.getElementById("passwordModal").classList.add("hidden");
        }

        function resetPasswordForm() {
            document.getElementById("passwordForm").reset();
            document.querySelectorAll(".requirement").forEach(el => {
                el.classList.remove("met");
                el.classList.add("unmet");
            });
            document.getElementById("matchMessage").textContent = "";
        }

        // Map Modal Functions
        function openMapModal() {
            document.getElementById("mapModal").classList.remove("hidden");
            setTimeout(() => {
                initMap();
            }, 100);
        }

        function closeMapModal() {
            document.getElementById("mapModal").classList.add("hidden");
        }

        function initMap() {
            if (map) {
                map.remove();
            }

            const defaultLat = selectedLat || LIPA_CENTER_LAT;
            const defaultLng = selectedLng || LIPA_CENTER_LNG;

            map = L.map('map', {
                maxBounds: L.latLngBounds([LIPA_MIN_LAT, LIPA_MIN_LNG], [LIPA_MAX_LAT, LIPA_MAX_LNG]),
                maxBoundsViscosity: 1.0,
                minZoom: 11
            }).setView([defaultLat, defaultLng], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            if (selectedLat && selectedLng) {
                marker = L.marker([selectedLat, selectedLng]).addTo(map);
                document.getElementById('selectedCoords').textContent =
                    `${selectedLat.toFixed(6)}, ${selectedLng.toFixed(6)}`;
            }

            map.on('click', function(e) {
                // LIPA ONLY: block click outside Lipa
                if (!isInLipa(e.latlng.lat, e.latlng.lng)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Outside Lipa City',
                        text: 'We only deliver in Lipa City',
                        confirmButtonColor: '#6366f1'
                    });
                    return;
                }
                selectedLat = e.latlng.lat;
                selectedLng = e.latlng.lng;

                if (marker) {
                    marker.setLatLng([selectedLat, selectedLng]);
                } else {
                    marker = L.marker([selectedLat, selectedLng]).addTo(map);
                }

                document.getElementById('selectedCoords').textContent =
                    `${selectedLat.toFixed(6)}, ${selectedLng.toFixed(6)}`;
            });
        }

        function saveLocation() {
            if (!selectedLat || !selectedLng) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Location Selected',
                    text: 'Please click on the map to select your location',
                    confirmButtonColor: '#6366f1'
                });
                return;
            }

            // LIPA ONLY: block save outside Lipa
            if (!isInLipa(selectedLat, selectedLng)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Outside Lipa City',
                    text: 'We only deliver in Lipa City',
                    confirmButtonColor: '#6366f1'
                });
                return;
            }

            const formData = new FormData();
            formData.append('action', 'update_location');
            formData.append('latitude', selectedLat);
            formData.append('longitude', selectedLng);

            fetch('profile.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Location Saved!',
                            text: data.message,
                            confirmButtonColor: '#6366f1'
                        }).then(() => {
                            closeMapModal();
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message,
                            confirmButtonColor: '#6366f1'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred. Please try again.',
                        confirmButtonColor: '#6366f1'
                    });
                });
        }

        // Toggle password visibility
        function togglePasswordVisibility(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling.querySelector('i');

            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }

        function validatePasswordRequirements() {
            const password = document.getElementById("newPassword").value;

            const rules = {
                'req-length': /.{8,}/.test(password),
                'req-uppercase': /[A-Z]/.test(password),
                'req-lowercase': /[a-z]/.test(password),
                'req-number': /[0-9]/.test(password),
                'req-special': /[!@#$%^&*(),.?":{}|<>]/.test(password)
            };

            for (const [id, met] of Object.entries(rules)) {
                const el = document.getElementById(id);
                if (met) {
                    el.classList.remove("unmet");
                    el.classList.add("met");
                } else {
                    el.classList.remove("met");
                    el.classList.add("unmet");
                }
            }

            validatePasswordMatch();
        }

        function validatePasswordMatch() {
            const newPassword = document.getElementById("newPassword").value;
            const confirmPassword = document.getElementById("confirmPassword").value;
            const matchMessage = document.getElementById("matchMessage");

            if (confirmPassword === "") {
                matchMessage.textContent = "";
                return;
            }

            if (newPassword === confirmPassword) {
                matchMessage.textContent = "✓ Great! Your passwords match.";
                matchMessage.className = "password-match-message match-success";
            } else {
                matchMessage.textContent = "✗ Oops! Your passwords don't match.";
                matchMessage.className = "password-match-message match-error";
            }
        }

        function allRequirementsMet() {
            const requirements = document.querySelectorAll(".requirement");
            return Array.from(requirements).every(req => req.classList.contains("met"));
        }

        // Edit Profile Form Submit
        document.getElementById("editProfileForm").addEventListener("submit", function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('action', 'update_profile');

            fetch('profile.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Profile Updated!',
                            text: data.message,
                            confirmButtonColor: '#3b82f6'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message,
                            confirmButtonColor: '#3b82f6'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred. Please try again.',
                        confirmButtonColor: '#3b82f6'
                    });
                });
        });

        // Password Form Submit
        document.getElementById("passwordForm").addEventListener("submit", function(e) {
            e.preventDefault();

            const oldPassword = document.getElementById("oldPassword").value;
            const newPassword = document.getElementById("newPassword").value;
            const confirmPassword = document.getElementById("confirmPassword").value;

            if (!oldPassword || !newPassword || !confirmPassword) {
                Swal.fire({
                    icon: 'error',
                    title: 'Missing Fields',
                    text: 'Please fill in all password fields',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }

            if (!allRequirementsMet()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Weak Password',
                    text: 'Password does not meet all requirements',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }

            if (newPassword !== confirmPassword) {
                Swal.fire({
                    icon: 'error',
                    title: 'Password Mismatch',
                    text: 'New passwords do not match',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }

            Swal.fire({
                icon: 'warning',
                title: 'Confirm Password Change',
                text: 'Are you sure you want to change your password?',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#dc2626',
                confirmButtonText: 'Yes, change it',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('action', 'change_password');
                    formData.append('old_password', oldPassword);
                    formData.append('new_password', newPassword);

                    fetch('profile.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Password Changed!',
                                    text: data.message,
                                    confirmButtonColor: '#3b82f6'
                                }).then(() => {
                                    closePasswordModal();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: data.message,
                                    confirmButtonColor: '#3b82f6'
                                });
                            }
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'An error occurred. Please try again.',
                                confirmButtonColor: '#3b82f6'
                            });
                        });
                }
            });
        });
    </script>

</body>

</html>