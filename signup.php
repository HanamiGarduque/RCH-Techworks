<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - RCH Water Refilling System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

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

        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-20px) scale(1.05); }
        }

        .form-scroll-container {
            max-height: 410px;
            overflow-y: auto;
            padding-right: 6px;
        }

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

        .address-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        @media (max-width: 640px) {
            .address-grid {
                grid-template-columns: 1fr;
            }
        }

        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none;
        }
    </style>
</head>

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
                    <form action="signup_backend.php" method="POST" class="space-y-5 pb-6 m-2" id="signupForm">
                        <!-- CSRF Token (Add this in your PHP) -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

                        <div>
                            <label for="fullName" class="block text-sm font-medium text-gray-700 mb-2">Full Name<span class="text-red-500">*</span></label>
                            <input type="text" id="fullName" name="fullName" placeholder="Enter your full name" maxlength="100"
                                class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-300 transition-all"
                                required>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address<span class="text-red-500">*</span></label>
                            <input type="email" id="email" name="email" placeholder="Enter your email address" maxlength="100"
                                class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-300 transition-all"
                                required>
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number<span class="text-red-500">*</span></label>
                            <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" 
                                pattern="[0-9]{10,11}" maxlength="11"
                                class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-300 transition-all"
                                required>
                            <p class="text-xs text-gray-500 mt-1">Format: 10-11 digits</p>
                        </div>

                        <!-- Improved Address Section -->
                        <div class="address-section">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Delivery Address<span class="text-red-500">*</span>
                            </label>

                            <!-- House & Street in one row -->
                            <div class="address-grid mb-3">
                                <input type="text" name="house_number" placeholder="House/Unit No." 
                                    class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg" 
                                    maxlength="20" required>
                                
                                <input type="text" name="street_name" placeholder="Street Name" 
                                    class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg" 
                                    maxlength="100" required>
                            </div>

                            <!-- Barangay full width -->
                            <input type="text" name="barangay" placeholder="Barangay" 
                                class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg mb-3" 
                                maxlength="100" required>

                            <!-- City & Province in one row -->
                            <div class="address-grid mb-3">
                                <input type="text" name="city" placeholder="City/Municipality" 
                                    class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg" 
                                    maxlength="100" required>
                                
                                <input type="text" name="province" placeholder="Province" 
                                    class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg" 
                                    maxlength="100" required>
                            </div>

                            <!-- Postal Code -->
                            <input type="text" name="postal_code" placeholder="Postal Code" 
                                pattern="[0-9]{4}" maxlength="4"
                                class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg" 
                                required>
                            <p class="text-xs text-gray-500 mt-1">4-digit postal code</p>
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                Password <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <input type="password" id="password" name="password"
                                    placeholder="Enter your password" maxlength="128"
                                    class="input-field w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-300 transition-all"
                                    required>
                                <button type="button" onclick="togglePassword('password', this)"
                                    class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-gray-700">
                                    <i class="bi bi-eye text-lg"></i>
                                </button>
                            </div>

                            <p id="passwordReq" class="text-sm text-red-500 mt-2">
                                Minimum 8 characters, 1 uppercase, 1 lowercase, 1 number, 1 special character
                            </p>
                        </div>

                        <div>
                            <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-2">
                                Confirm Password <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <input type="password" id="confirmPassword" name="confirmPassword"
                                    placeholder="Re-enter your password" maxlength="128"
                                    class="input-field w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-300 transition-all"
                                    required>
                                <button type="button" onclick="togglePassword('confirmPassword', this)"
                                    class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-gray-700">
                                    <i class="bi bi-eye text-lg"></i>
                                </button>
                            </div>

                            <p id="confirmPasswordReq" class="text-sm mt-2"></p>
                        </div>

                        <div class="flex items-start gap-2">
                            <input type="checkbox" id="terms" name="terms"
                                class="mt-1 w-4 h-4 text-blue-500 border-gray-300 rounded focus:ring-blue-500" required>
                            <label for="terms" class="text-sm text-gray-600">
                                I agree to the <a href="javascript:void(0);" onclick="openModal('termsModal')" class="text-blue-500 hover:text-blue-600 font-medium">Terms and Conditions and Privacy Policy</a><span class="text-red-500">*</span>
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
    <div id="termsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full overflow-y-auto max-h-[80vh]">
        <!-- Modal Header -->
        <div class="px-6 py-4 flex justify-between items-center border-b">
            <h2 class="text-2xl font-bold">RCH Policies</h2>
            <button onclick="closeModal('termsModal')" class="text-gray-500 hover:text-gray-700 text-3xl p-2 rounded-full transition">&times;</button>
        </div>

        <!-- Modal Tabs -->
        <div class="px-6 py-2 border-b flex space-x-4">
            <button onclick="showTab('termsContent')" id="termsTab" class="text-blue-600 font-semibold border-b-2 border-blue-600 pb-1">Terms & Conditions</button>
            <button onclick="showTab('privacyContent')" id="privacyTab" class="text-gray-500 font-semibold hover:text-blue-600 pb-1">Privacy Policy</button>
        </div>

        <!-- Modal Content -->
        <div class="p-6 text-gray-700 space-y-4">
            <!-- Terms & Conditions -->
            <div id="termsContent">
                <p>Welcome to <strong>RCH: QR-Code Integrated Web-Based Water Ordering, Refilling, and Delivery Management Platform</strong>. By using our platform, you agree to comply with the following terms and conditions:</p>
                <ul class="list-disc pl-5 space-y-2">
                    <li>Users must provide accurate and complete information during registration.</li>
                    <li>Account sharing is strictly prohibited.</li>
                    <li>All orders are subject to availability and confirmation.</li>
                    <li>RCH will make reasonable efforts to ensure timely delivery but is not responsible for delays caused by circumstances beyond our control.</li>
                    <li>Users are responsible for maintaining the confidentiality of their account credentials.</li>
                    <li>Payments are processed securely via cash on delivery (COD).</li>
                    <li>All content, branding, and software are protected under intellectual property laws and may not be copied or distributed without permission.</li>
                    <li>RCH’s liability is limited to the total amount paid by the user for the service.</li>
                    <li>Governing law: Republic of the Philippines, including <strong>RA 8792 (E-Commerce Act)</strong> and <strong>RA 7394 (Consumer Act)</strong>.</li>
                    <li>RCH may update these Terms and Conditions at any time. Continued use constitutes acceptance of any changes.</li>
                </ul>
            </div>

            <!-- Privacy Policy -->
            <div id="privacyContent" class="hidden">
                <p>RCH values your privacy and is committed to protecting your personal information. By using our services, you agree to the collection and use of information as outlined below:</p>
                <ul class="list-disc pl-5 space-y-2">
                    <li><strong>Information Collected:</strong> Personal data (name, email, contact, delivery address), account credentials, order history, payment details, and device/browser information.</li>
                    <li><strong>Use of Information:</strong> To provide services, process orders/payments, communicate updates/promotions, and comply with legal obligations.</li>
                    <li><strong>Data Sharing:</strong> With trusted service providers for order fulfillment or payment processing; as required by law.</li>
                    <li><strong>Data Security:</strong> Industry-standard measures are implemented; however, no method is 100% secure.</li>
                    <li><strong>Your Rights:</strong> Access, correct, or delete your personal data, subject to legal obligations.</li>
                    <li><strong>Compliance:</strong> <strong>RA 10173 (Data Privacy Act of 2012)</strong>, <strong>RA 8792 (E-Commerce Act).</li>
                    <li>RCH may update this Privacy Policy. Major changes will be communicated via platform or email.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Tab switch function
function showTab(tabId) {
    document.getElementById('termsContent').classList.add('hidden');
    document.getElementById('privacyContent').classList.add('hidden');
    document.getElementById('termsTab').classList.remove('text-blue-600','border-blue-600');
    document.getElementById('privacyTab').classList.remove('text-blue-600','border-blue-600');
    document.getElementById('termsTab').classList.add('text-gray-500');
    document.getElementById('privacyTab').classList.add('text-gray-500');

    document.getElementById(tabId).classList.remove('hidden');

    if(tabId === 'termsContent') {
        document.getElementById('termsTab').classList.add('text-blue-600','border-b-2','border-blue-600');
        document.getElementById('termsTab').classList.remove('text-gray-500');
    } else {
        document.getElementById('privacyTab').classList.add('text-blue-600','border-b-2','border-blue-600');
        document.getElementById('privacyTab').classList.remove('text-gray-500');
    }
}
</script>


    <div id="privacyModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full overflow-y-auto max-h-[80vh]">
            <div class="px-6 py-4 flex justify-between items-center border-b">
                <h2 class="text-2xl font-bold">Privacy Policy</h2>
                <button onclick="closeModal('privacyModal')" class="text-gray-500 hover:text-gray-700 text-3xl p-2 rounded-full transition">&times;</button>
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
                    confirmReqText.textContent = "Oops! Your passwords don't match.";
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

            const form = document.getElementById("signupForm");
            form.addEventListener("submit", function(e) {
                const value = passwordInput.value;
                const confirm = confirmInput.value;

                const allMet = updatePasswordReqText();
                if (!allMet) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Password',
                        text: 'Password does not meet all required criteria.'
                    });
                    return;
                }

                if (value !== confirm) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Password Mismatch',
                        text: 'Passwords do not match.'
                    });
                }
            });

            updatePasswordReqText();
        });


        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }

        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }
    </script>
</body>
</html>