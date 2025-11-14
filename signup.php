<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - RCH Water Refilling System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        .wave-pattern {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .wave {
            position: absolute;
            border-radius: 50%;
            opacity: 0.6;
        }

        .wave-1 {
            width: 500px;
            height: 500px;
            background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
            top: -150px;
            left: -100px;
            animation: float 8s ease-in-out infinite;
        }

        .wave-2 {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, #93c5fd 0%, #60a5fa 100%);
            bottom: -100px;
            right: -80px;
            animation: float 10s ease-in-out infinite reverse;
        }

        .wave-3 {
            width: 300px;
            height: 300px;
            background: linear-gradient(135deg, #dbeafe 0%, #93c5fd 100%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation: float 12s ease-in-out infinite;
        }

        .form-scroll-container {
            max-height: 410px;
            overflow-y: auto;
            padding-right: 6px;
        }

        /* Hide scrollbar on WebKit browsers */
        .form-scroll-container::-webkit-scrollbar {
            width: 3px;
        }

        .input-field:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-scroll-container::-webkit-scrollbar-thumb {
            background: #6fa8edff;
            opacity: 70%;
            border-radius: 4px;
        }

        .form-scroll-container::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .btn-primary {
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }
    </style>
</head>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirmPassword');
    const passwordReqText = document.getElementById('passwordReq');
    const confirmReqText = document.getElementById('confirmPasswordReq');

    const requirements = [
        { regex: /.{8,}/, text: '8+' },
        { regex: /[A-Z]/, text: 'A-Z' },
        { regex: /[a-z]/, text: 'a-z' },
        { regex: /[0-9]/, text: '0-9' },
        { regex: /[!@#$%^&*(),.?":{}|<>]/, text: '!@#$' },
    ];

    function updatePasswordReqText() {
        const value = passwordInput.value;
        let html = '';
        let allMet = true;

        requirements.forEach((req, index) => {
            if (req.regex.test(value)) {
                html += `<span class="text-green-600 font-semibold">${req.text}</span>`;
            } else {
                html += `<span class="text-red-500 font-semibold">${req.text}</span>`;
                allMet = false;
            }
            if (index !== requirements.length - 1) html += ' , ';
        });

        passwordReqText.innerHTML = html;

        // Change input border based on all requirements
        if (allMet) {
            passwordInput.classList.remove("border-red-500");
            passwordInput.classList.add("border-green-600");
        } else {
            passwordInput.classList.remove("border-green-600");
            passwordInput.classList.add("border-red-500");
        }

        return allMet;
    }

    function updateConfirmPasswordText() {
        const password = passwordInput.value;
        const confirm = confirmInput.value;

        if (confirm === "") {
            confirmReqText.textContent = "";
            confirmInput.classList.remove("border-green-600", "border-red-500");
        } else if (password === confirm) {
            confirmReqText.textContent = "Great! Your passwords match.";
            confirmReqText.classList.remove("text-red-500");
            confirmReqText.classList.add("text-green-600");
            confirmInput.classList.remove("border-red-500");
            confirmInput.classList.add("border-green-600");
        } else {
            confirmReqText.textContent = "Oops! Your passwords don’t match.";
            confirmReqText.classList.remove("text-green-600");
            confirmReqText.classList.add("text-red-500");
            confirmInput.classList.remove("border-green-600");
            confirmInput.classList.add("border-red-500");
        }
    }

    passwordInput.addEventListener('input', function() {
        updatePasswordReqText();
        updateConfirmPasswordText();
    });

    confirmInput.addEventListener('input', updateConfirmPasswordText);

    // Form submission validation
    const form = document.querySelector("form");
    form.addEventListener("submit", function (e) {
        const value = passwordInput.value;
        const confirm = confirmInput.value;

        const allMet = updatePasswordReqText();
        if (!allMet) {
            e.preventDefault();
            alert("Password does not meet all required criteria.");
            return;
        }

        if (value !== confirm) {
            e.preventDefault();
            alert("Passwords do not match.");
        }
    });

    // Initialize on load
    updatePasswordReqText();
});
</script>


<body class="bg-gray-50 min-h-screen flex items-center justify-center p-10">
    <div class="flex h-[600px] bg-white rounded-2xl shadow-lg overflow-hidden max-w-5xl w-full">
        <!-- Left Panel -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-blue-400 via-blue-500 to-blue-600 relative overflow-hidden">
            <div class="wave-pattern">
                <div class="wave wave-1"></div>
                <div class="wave wave-2"></div>
                <div class="wave wave-3"></div>
            </div>

            <div class="relative z-10 flex flex-col justify-between p-12 text-white w-full">
                <div class="flex items-center gap-3">
                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                        <path d="M20 2C20 2 8 14 8 22C8 28.6274 13.3726 34 20 34C26.6274 34 32 28.6274 32 22C32 14 20 2 20 2Z" fill="white" stroke="white" stroke-width="2" />
                        <circle cx="16" cy="20" r="2" fill="#3b82f6" opacity="0.5" />
                    </svg>
                    <span class="text-xl font-bold">RCH Water</span>
                </div>

                <div class="space-y-6">
                    <h1 class="text-3xl font-bold leading-tight">Welcome to<br />RCH Water Refilling</h1>
                    <p class="text-lg text-blue-100 max-w-md">Join us today and enjoy fresh, clean water delivered right to your doorstep.</p>
                </div>

                <div class="text-blue-100 text-sm">© 2025 RCH Water Refilling System</div>
            </div>
        </div>

        <!-- Right Panel -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
            <div class="h-[500px] max-w-5xl w-full">
                <div class="lg:hidden flex items-center gap-3 mb-8">
                    <svg width="32" height="32" viewBox="0 0 40 40" fill="none">
                        <path d="M20 2C20 2 8 14 8 22C8 28.6274 13.3726 34 20 34C26.6274 34 32 28.6274 32 22C32 14 20 2 20 2Z" fill="#3b82f6" stroke="#3b82f6" stroke-width="2" />
                        <circle cx="16" cy="20" r="2" fill="white" opacity="0.5" />
                    </svg>
                    <span class="text-2xl font-bold text-gray-800">RCH Water</span>
                </div>

                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-blue-400 mb-2">Create Account</h2>
                    <p class="text-gray-600">Welcome! Please enter your details to get started.</p>
                </div>

                <!-- Scrollable form container -->
                <div class="form-scroll-container">
                    <form action="signup_backend.php" method="POST" class="space-y-5 pb-6 m-2">

                        <div>
                            <label for="fullName" class="block text-sm font-medium text-gray-700 mb-2">Full Name<span class="text-red-500">*</span></label>
                            <input type="text" id="fullName" name="fullName" placeholder="Enter your full name"
                                class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-300 transition-all"
                                required>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address<span class="text-red-500">*</span></label>
                            <input type="email" id="email" name="email" placeholder="Enter your email address"
                                class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-300 transition-all"
                                required>
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number<span class="text-red-500">*</span></label>
                            <input type="tel" id="phone" name="phone" placeholder="Enter your phone number"
                                class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-300 transition-all"
                                required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Address<span class="text-red-500">*</span></label>

                            <input type="text" name="house_number" placeholder="House No." class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg mb-2" required>

                            <input type="text" name="street_name" placeholder="Street Name" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg mb-2" required>

                            <input type="text" name="barangay" placeholder="Barangay" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg mb-2" required>

                            <input type="text" name="city" placeholder="City / Municipality" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg mb-2" required>

                            <input type="text" name="province" placeholder="Province" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg mb-2" required>

                            <input type="text" name="postal_code" placeholder="Postal Code" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg" required>
                        </div>


                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password<span class="text-red-500">*</span></label>
                            <input type="password" id="password" name="password" placeholder="Enter your password"
                                class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-300 transition-all"
                                required>

                            <p id="passwordReq" class="text-sm text-red-500 mt-2">
                                Minimum 8 characters, 1 uppercase, 1 lowercase, 1 number, 1 special character (!@#$%^&*(),.?)
                            </p>

                        </div>

                        <div>
                            <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password<span class="text-red-500">*</span></label>
                            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Re-enter your password"
                                class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-300 transition-all"
                                required>
                                <p id="confirmPasswordReq" class="text-sm mt-2"></p>
                        </div>

                        <div class="flex items-start gap-2">
                            <input type="checkbox" id="terms" name="terms"
                                class="mt-1 w-4 h-4 text-blue-500 border-gray-300 rounded focus:ring-blue-500" required>
                            <label for="terms" class="text-sm text-gray-600">
                                I agree to the <a href="javascript:void(0);" onclick="openModal('termsModal')" class="text-blue-500 hover:text-blue-600 font-medium">Terms and Conditions</a> and <a href="javascript:void(0);" onclick="openModal('privacyModal')" class="text-blue-500 hover:text-blue-600 font-medium">Privacy Policy<span class="text-red-500">*</span></a>
                            </label>
                        </div>

                        <button type="submit"
                            class="btn-primary w-full bg-blue-500 text-white py-3 rounded-lg font-medium hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Create Account
                        </button>

                        <div class="text-center text-sm text-gray-600">
                            Already have an account?
                            <a href="index.php" class="text-blue-500 hover:text-blue-600 font-medium">Log in</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Terms and Conditions Modal -->
    <div id="termsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full overflow-y-auto max-h-[80vh]">
            <div class="px-6 py-4 flex justify-between items-center border-b">
                <h2 class="text-2xl font-bold">Terms and Conditions</h2>
                <button onclick="closeModal('termsModal')" class="text-gray-500 hover:text-gray-700 p-2 rounded-full transition">&times;</button>
            </div>
            <div class="p-6 text-gray-700 space-y-4">
                <p>Welcome to RCH Water Refilling System! By using our services, you agree to the following terms and conditions.</p>
                <ul class="list-disc pl-5 space-y-2">
                    <li>Users must provide accurate and complete information during signup.</li>
                    <li>Account sharing is strictly prohibited.</li>
                    <li>All orders are subject to availability and confirmation.</li>
                    <li>RCH Water is not responsible for delays due to unforeseen circumstances.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Privacy Policy Modal -->
    <div id="privacyModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full overflow-y-auto max-h-[80vh]">
            <div class="px-6 py-4 flex justify-between items-center border-b">
                <h2 class="text-2xl font-bold">Privacy Policy</h2>
                <button onclick="closeModal('privacyModal')" class="text-gray-500 hover:text-gray-700 p-2 rounded-full transition">&times;</button>
            </div>
            <div class="p-6 text-gray-700 space-y-4">
                <p>RCH Water Refilling System respects your privacy and is committed to protecting your personal information.</p>
                <ul class="list-disc pl-5 space-y-2">
                    <li>We collect only necessary information for service provision.</li>
                    <li>Personal data will not be shared with third parties without consent.</li>
                    <li>Data is stored securely and handled according to local privacy laws.</li>
                    <li>Users may request to view or delete their personal information anytime.</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }
    </script>

    <?php if (isset($_SESSION['alert'])): ?>
<script>
Swal.fire({
    icon: "<?= $_SESSION['alert']['icon'] ?>",
    title: "<?= $_SESSION['alert']['title'] ?>",
    text: "<?= $_SESSION['alert']['text'] ?>",
}).then(() => {
    <?php if ($_SESSION['alert']['action'] == "redirect"): ?>
        window.location = "<?= $_SESSION['alert']['url'] ?>";
    <?php else: ?>
        window.history.back();
    <?php endif; ?>
});
</script>
<?php unset($_SESSION['alert']); endif; ?>

</body>
</html>
