<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>RCH Water - Order Water Refill</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100 overflow-hidden">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <script src="includes/sidebar.js"></script>

        <!-- Main Content -->
        <main class="flex-1 ml-20 bg-gray-50 rounded-xl shadow overflow-y-auto">
            <div class="main-header">
                <?php //ibahin mo lang tong header na to sa bawat page para magiba title at subtitle
                $pageTitle = "My Profile";
                $pageSubtitle = "Manage your personal information, contact details, and preferences to keep your account up to date.";
                include 'includes/header.php';
                ?>
            </div>
            <!-- open -->

            <!-- PROFILE PAGE CONTENT -->
            <div class="p-6">

                <!-- PROFILE VIEW -->
                <div id="profileView">

                    <div class="flex flex-col items-center text-center">
                        <!-- Profile Icon -->
                        <div class="w-28 h-28 bg-gray-200 rounded-full flex items-center justify-center shadow">
                            <i class="bi bi-person text-gray-600 text-6xl"></i>
                        </div>

                        <h2 class="text-2xl font-semibold mt-4">Jane Doe</h2>
                        <p class="text-gray-500 text-sm">jane.doe@email.com</p>
                    </div>

                    <!-- Profile Information -->
                    <div class="mt-8 space-y-4">

                        <div>
                            <p class="text-gray-500 text-sm">Full Name</p>
                            <p class="font-medium text-gray-800">Jane Doe</p>
                        </div>

                        <div>
                            <p class="text-gray-500 text-sm">Phone Number</p>
                            <p class="font-medium text-gray-800">09123456789</p>
                        </div>

                        <div>
                            <p class="text-gray-500 text-sm">Email</p>
                            <p class="font-medium text-gray-800">jane.doe@email.com</p>
                        </div>

                        <div>
                            <p class="text-gray-500 text-sm">Address</p>
                            <p class="font-medium text-gray-800 leading-tight">
                                123-A Sample House<br>
                                Mabuhay Street, Brgy. Poblacion<br>
                                Quezon City, Metro Manila, 1100
                            </p>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="mt-6 flex gap-3">
                        <button onclick="toggleEditProfile()"
                            class="flex-1 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700">
                            Edit Profile
                        </button>

                        <button onclick="openPasswordModal()"
                            class="flex-1 py-2 bg-gray-700 text-white rounded-lg font-medium hover:bg-gray-800">
                            Change Password
                        </button>
                    </div>
                </div>

                <!-- EDIT PROFILE VIEW -->
                <div id="editProfileView" class="hidden mt-4">

                    <h2 class="text-2xl font-semibold mb-4">Edit Profile</h2>

                    <form id="editProfileForm" class="space-y-4">

                        <!-- Full Name -->
                        <div>
                            <label class="text-sm font-medium">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" required
                                class="w-full mt-1 px-3 py-2 border rounded-lg focus:ring-blue-400 focus:ring-1">
                        </div>

                        <!-- Phone -->
                        <div>
                            <label class="text-sm font-medium">Phone Number <span class="text-red-500">*</span></label>
                            <input type="text" required
                                class="w-full mt-1 px-3 py-2 border rounded-lg focus:ring-blue-400 focus:ring-1">
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="text-sm font-medium">Email <span class="text-red-500">*</span></label>
                            <input type="email" required
                                class="w-full mt-1 px-3 py-2 border rounded-lg focus:ring-blue-400 focus:ring-1">
                        </div>

                        <!-- Address -->
                        <div>
                            <label class="text-sm font-medium">Address <span class="text-red-500">*</span></label>
                            <div class="space-y-3 mt-2">

                                <input type="text" placeholder="House No." required
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-blue-400 focus:ring-1">

                                <input type="text" placeholder="Street Name" required
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-blue-400 focus:ring-1">

                                <input type="text" placeholder="Barangay" required
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-blue-400 focus:ring-1">

                                <input type="text" placeholder="City / Municipality" required
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-blue-400 focus:ring-1">

                                <input type="text" placeholder="Province" required
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-blue-400 focus:ring-1">

                                <input type="text" placeholder="Postal Code" required
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-blue-400 focus:ring-1">
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex gap-3 pt-4">
                            <button type="button" onclick="toggleEditProfile()"
                                class="flex-1 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                                Cancel
                            </button>

                            <button type="submit"
                                class="flex-1 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>


                <!-- CHANGE PASSWORD MODAL -->
                <div id="passwordModal"
                    class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">

                    <div class="bg-white w-full max-w-md rounded-xl p-6 shadow-xl">
                        <h3 class="text-xl font-semibold mb-4">Verify OTP to Change Password</h3>

                        <!-- Step 1: Enter OTP -->
                        <div id="otpStep">
                            <p class="text-gray-600 mb-3 text-sm">An OTP has been sent to your phone number.</p>

                            <input type="text" placeholder="Enter OTP"
                                class="w-full px-3 py-2 border rounded-lg">

                            <button onclick="goToPasswordStep()"
                                class="w-full mt-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                Verify OTP
                            </button>
                        </div>

                        <!-- Step 2: New Password -->
                        <div id="passwordStep" class="hidden">
                            <label class="text-sm font-medium">New Password <span class="text-red-500">*</span></label>
                            <input id="newPassword" type="password"
                                class="w-full mt-1 px-3 py-2 border rounded-lg">

                            <p class="text-xs text-gray-500 mt-1">
                                8+ characters, uppercase, lowercase, numbers, symbols.
                            </p>

                            <label class="text-sm font-medium mt-3 block">Confirm Password <span class="text-red-500">*</span></label>
                            <input id="confirmPassword" type="password"
                                class="w-full mt-1 px-3 py-2 border rounded-lg">

                            <button onclick="saveNewPassword()"
                                class="w-full mt-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                Save Password
                            </button>
                        </div>

                        <!-- Close -->
                        <button onclick="closePasswordModal()"
                            class="absolute top-3 right-3 text-gray-500 hover:text-black">
                            <i class="bi bi-x-lg text-lg"></i>
                        </button>
                    </div>

                </div>
            </div>

            <!-- JS FUNCTIONS -->
            <script>
                function toggleEditProfile() {
                    document.getElementById("profileView").classList.toggle("hidden");
                    document.getElementById("editProfileView").classList.toggle("hidden");
                }

                function openPasswordModal() {
                    document.getElementById("passwordModal").classList.remove("hidden");
                }

                function closePasswordModal() {
                    document.getElementById("passwordModal").classList.add("hidden");
                }

                function goToPasswordStep() {
                    document.getElementById("otpStep").classList.add("hidden");
                    document.getElementById("passwordStep").classList.remove("hidden");
                }

                function saveNewPassword() {
                    let pass = document.getElementById("newPassword").value;
                    let confirm = document.getElementById("confirmPassword").value;

                    if (pass !== confirm) {
                        Swal.fire("Error", "Passwords do not match!", "error");
                        return;
                    }

                    // SHA-256 Hashing
                    async function sha256(message) {
                        const msgBuffer = new TextEncoder().encode(message);
                        const hashBuffer = await crypto.subtle.digest("SHA-256", msgBuffer);
                        const hashArray = Array.from(new Uint8Array(hashBuffer));
                        return hashArray.map(b => b.toString(16).padStart(2, "0")).join("");
                    }

                    sha256(pass).then(hash => {
                        console.log("Password Hash:", hash);

                        // Success alert
                        Swal.fire({
                            icon: "success",
                            title: "Password Updated!",
                            text: "Your password has been changed successfully.",
                            confirmButtonColor: "#2563eb"
                        });

                        closePasswordModal();
                    });
                }
            </script>


            <!-- close -->
        </main>
    </div>
</body>