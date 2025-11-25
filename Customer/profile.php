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

        /* Password requirement indicator */
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

            <!-- PROFILE PAGE CONTENT -->
            <div class="p-6 w-full ">

                <!-- PROFILE VIEW -->
                <div id="profileView">

                    <!-- Profile Header Card -->
                    <div class="bg-white rounded-xl shadow-sm p-8 text-center mb-6">
                        <!-- Profile Icon -->
                        <div class="flex justify-center mb-4">
                            <div class="w-32 h-32 bg-gradient-to-br from-blue-100 to-blue-200 rounded-full flex items-center justify-center shadow-lg">
                                <i class="bi bi-person text-blue-600 text-7xl"></i>
                            </div>
                        </div>

                        <h2 class="text-3xl font-bold text-gray-800 mb-1">Jane Doe</h2>
                        <p class="text-gray-500 text-sm mb-6">jane.doe@email.com</p>

                        <!-- Action Buttons -->
                        <div class="flex gap-3 justify-center flex-wrap">
                            <button onclick="toggleEditProfile()"
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition flex items-center gap-2">
                                <i class="bi bi-pencil"></i> Edit Profile
                            </button>

                            <button onclick="openPasswordModal()"
                                class="px-6 py-2 bg-gray-700 text-white rounded-lg font-medium hover:bg-gray-800 transition flex items-center gap-2">
                                <i class="bi bi-key"></i> Change Password
                            </button>
                        </div>
                    </div>

                    <!-- Profile Information Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Full Name Card -->
                        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-blue-600">
                            <p class="text-gray-500 text-xs font-semibold uppercase mb-1">Full Name</p>
                            <p class="font-semibold text-gray-800 text-lg">Jane Doe</p>
                        </div>

                        <!-- Phone Card -->
                        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-blue-600">
                            <p class="text-gray-500 text-xs font-semibold uppercase mb-1">Phone Number</p>
                            <p class="font-semibold text-gray-800 text-lg">09123456789</p>
                        </div>

                        <!-- Email Card -->
                        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-blue-600">
                            <p class="text-gray-500 text-xs font-semibold uppercase mb-1">Email</p>
                            <p class="font-semibold text-gray-800 text-lg">jane.doe@email.com</p>
                        </div>

                        <!-- Address Card -->
                        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-blue-600">
                            <p class="text-gray-500 text-xs font-semibold uppercase mb-1">Address</p>
                            <p class="font-semibold text-gray-800 text-sm leading-relaxed">
                                123-A Sample House<br>
                                Mabuhay Street, Brgy. Poblacion<br>
                                Quezon City, Metro Manila, 1100
                            </p>
                        </div>
                    </div>
                </div>

                <!-- EDIT PROFILE VIEW -->
                <div id="editProfileView" class="hidden">
                    <div class="bg-white rounded-xl shadow-sm p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <i class="bi bi-pencil-square text-blue-600 text-2xl"></i>
                            <h2 class="text-2xl font-bold text-gray-800">Edit Profile</h2>
                        </div>

                        <form id="editProfileForm" class="space-y-5">

                            <!-- Full Name -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Full Name <span class="text-red-500">*</span></label>
                                <input type="text" required placeholder="Enter your full name"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            </div>

                            <!-- Phone -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Phone Number <span class="text-red-500">*</span></label>
                                <input type="text" required placeholder="09123456789"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Email <span class="text-red-500">*</span></label>
                                <input type="email" required placeholder="jane.doe@email.com"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            </div>

                            <!-- Address Section -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3">Address <span class="text-red-500">*</span></label>
                                <div class="space-y-3">
                                    <input type="text" placeholder="House No." required
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">

                                    <input type="text" placeholder="Street Name" required
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">

                                    <input type="text" placeholder="Barangay" required
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">

                                    <input type="text" placeholder="City / Municipality" required
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">

                                    <input type="text" placeholder="Province" required
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">

                                    <input type="text" placeholder="Postal Code" required
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="flex gap-3 pt-6">
                                <button type="button" onclick="toggleEditProfile()"
                                    class="flex-1 py-2.5 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition">
                                    Cancel
                                </button>

                                <button type="submit"
                                    class="flex-1 py-2.5 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition">
                                    Save Changes
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

                    <!-- Password Form -->
                    <form id="passwordForm" class="space-y-5">

                        <!-- Old Password -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Old Password <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="password" id="oldPassword" required placeholder="Enter your current password"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                <button type="button" onclick="togglePasswordVisibility('oldPassword')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- New Password -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">New Password <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="password" id="newPassword" required placeholder="Enter new password"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                    oninput="validatePasswordRequirements()">
                                <button type="button" onclick="togglePasswordVisibility('newPassword')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Password Requirements -->
                        <div class="requirement unmet" id="req-length">
                            <div class="requirement-icon"><i class="bi bi-check"></i></div>
                            <span class="requirement-text">Min. 8 characters (8+)</span>
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


                        <!-- Confirm Password -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Confirm Password <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="password" id="confirmPassword" required placeholder="Confirm new password"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                    oninput="validatePasswordMatch()">
                                <button type="button" onclick="togglePasswordVisibility('confirmPassword')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Password Match Message -->
                        <div class="password-match-message" id="matchMessage"></div>

                        <!-- Buttons -->
                        <div class="flex gap-3 pt-4">
                            <button type="button" onclick="closePasswordModal()"
                                class="flex-1 py-2.5 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition flex items-center justify-center gap-2">
                                <i class="bi bi-x"></i> Cancel
                            </button>

                            <button type="button" onclick="handleDoneClick()"
                                class="flex-1 py-2.5 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition flex items-center justify-center gap-2"
                                id="doneBtn">
                                <i class="bi bi-check"></i> Done
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </main>
    </div>

    <!-- JS FUNCTIONS -->
    <script>
        // Toggle Edit Profile View
        function toggleEditProfile() {
            document.getElementById("profileView").classList.toggle("hidden");
            document.getElementById("editProfileView").classList.toggle("hidden");
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

        // Toggle password visibility
        function togglePasswordVisibility(fieldId) {
            const field = document.getElementById(fieldId);
            field.type = field.type === 'password' ? 'text' : 'password';
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


        // Validate if passwords match
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

        // Validate all requirements before proceeding
        function allRequirementsMet() {
            const requirements = document.querySelectorAll(".requirement");
            return Array.from(requirements).every(req => req.classList.contains("met"));
        }

        // Handle Done button click
        function handleDoneClick() {
            const oldPassword = document.getElementById("oldPassword").value;
            const newPassword = document.getElementById("newPassword").value;
            const confirmPassword = document.getElementById("confirmPassword").value;

            // Validation
            if (!oldPassword) {
                Swal.fire("Error", "Please enter your old password.", "error");
                return;
            }

            if (!newPassword || !confirmPassword) {
                Swal.fire("Error", "Please enter both new password and confirm password.", "error");
                return;
            }

            if (!allRequirementsMet()) {
                Swal.fire("Error", "Password does not meet all requirements.", "error");
                return;
            }

            if (newPassword !== confirmPassword) {
                Swal.fire("Error", "Passwords do not match.", "error");
                return;
            }

            // Show confirmation alert
            Swal.fire({
                icon: "warning",
                title: "Are you sure?",
                text: "Are you sure you want to change your password?",
                showCancelButton: true,
                confirmButtonColor: "#2563eb",
                cancelButtonColor: "#dc2626",
                confirmButtonText: "Yes",
                cancelButtonText: "No"
            }).then((result) => {
                if (result.isConfirmed) {
                    // Hash password and save
                    sha256(newPassword).then(hash => {
                        console.log("Password Hash:", hash);

                        // Success alert
                        Swal.fire({
                            icon: "success",
                            title: "Success!",
                            text: "Your password has been changed successfully.",
                            confirmButtonColor: "#2563eb"
                        }).then(() => {
                            closePasswordModal();
                        });
                    });
                }
            });
        }

        // SHA-256 Hashing
        async function sha256(message) {
            const msgBuffer = new TextEncoder().encode(message);
            const hashBuffer = await crypto.subtle.digest("SHA-256", msgBuffer);
            const hashArray = Array.from(new Uint8Array(hashBuffer));
            return hashArray.map(b => b.toString(16).padStart(2, "0")).join("");
        }

        // Edit Profile Form Submit
        document.getElementById("editProfileForm").addEventListener("submit", function(e) {
            e.preventDefault();

            // Show success alert
            Swal.fire({
                icon: "success",
                title: "Profile Updated!",
                text: "Your profile has been updated successfully.",
                confirmButtonColor: "#2563eb"
            }).then(() => {
                toggleEditProfile();
            });
        });
    </script>

</body>

</html>