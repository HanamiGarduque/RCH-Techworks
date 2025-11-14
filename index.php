<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - RCH Water Refilling System</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
            right: -100px;
            animation: float 8s ease-in-out infinite;
        }

        .wave-2 {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, #93c5fd 0%, #60a5fa 100%);
            bottom: -100px;
            left: -80px;
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

        .input-field:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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

<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="flex h-[600px] bg-white rounded-2xl shadow-2xl overflow-hidden max-w-5xl w-full">
        <!-- Left Panel - Login Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 lg:p-12">
            <div class="max-w-md w-full">
                <!-- Mobile Logo -->
                <div class="lg:hidden flex items-center gap-3 mb-8">
                    <svg width="32" height="32" viewBox="0 0 40 40" fill="none">
                        <path d="M20 2C20 2 8 14 8 22C8 28.6274 13.3726 34 20 34C26.6274 34 32 28.6274 32 22C32 14 20 2 20 2Z" fill="#3b82f6" stroke="#3b82f6" stroke-width="2" />
                        <circle cx="16" cy="20" r="2" fill="white" opacity="0.5" />
                    </svg>
                    <span class="text-2xl font-bold text-gray-800">RCH Water</span>
                </div>

                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-blue-400 mb-2">Login</h2>
                    <p class="text-gray-600">Welcome! Please enter your details to continue.</p>
                </div>

                <form action="index_backend.php" method="POST" class="space-y-6">

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email"
       value="<?php echo isset($_COOKIE['remember_email']) ? htmlspecialchars($_COOKIE['remember_email']) : ''; ?>"
       class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-300 transition-all"
       required>

                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password"
                            class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-300 transition-all"
                            required>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="remember" name="remember"
                                class="w-4 h-4 text-blue-500 border-gray-300 rounded focus:ring-blue-500">
                            <label for="remember" class="text-sm text-gray-600">Remember me</label>
                        </div>
                        <button type="button" onclick="openForgotPasswordModal()" class="text-blue-600 text-sm">Forgot Password?</button>
                    </div>

                    <button type="submit"
                        class="btn-primary w-full bg-blue-500 text-white py-3 rounded-lg font-medium hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Login
                    </button>

                    <div class="text-center text-sm text-gray-600">
                        Don't have an account?
                        <a href="signup.php" class="text-blue-500 hover:text-blue-600 font-medium">Sign up</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Panel - Design Panel -->
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
                    <h1 class="text-4xl font-bold leading-tight">Fresh Water,<br />Delivered Daily</h1>
                    <p class="text-lg text-blue-100 max-w-md">Experience the convenience of premium water refilling services right at your doorstep.</p>

                    <div class="space-y-4 pt-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="text-blue-50">100% Purified Water</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="text-blue-50">Fast & Reliable Delivery</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="text-blue-50">Affordable Pricing</span>
                        </div>
                    </div>
                </div>

                <div class="text-blue-100 text-sm">© 2025 RCH Water Refilling System</div>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal (embedded) -->
    <div id="forgotPasswordModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
            
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-500 px-6 py-6 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-white">Reset Password</h2>
                <button onclick="closeForgotPasswordModal()" class="text-white hover:bg-white hover:bg-opacity-20 rounded-full p-2 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-6">
                
                <!-- Step 1: Phone Number -->
                <div id="step1" class="space-y-4">
                    <p class="text-gray-600 text-sm mb-4">Enter your registered phone number to receive an OTP code.</p>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Phone Number</label>
                        <input 
                            type="tel" 
                            id="phoneNumber" 
                            placeholder="e.g., 0912 345 6789" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                        >
                        <span id="phoneError" class="text-red-500 text-xs mt-1 hidden"></span>
                    </div>
                    <button 
                        onclick="sendOTP()" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition duration-300 mt-4"
                    >
                        Send OTP
                    </button>
                </div>

                <!-- Step 2: OTP Verification -->
                <div id="step2" class="hidden space-y-4">
                    <p class="text-gray-600 text-sm mb-4">Enter the 6-digit OTP code sent to your phone.</p>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">OTP Code</label>
                        <div class="flex gap-2 justify-between mb-2">
                            <input type="text" maxlength="1" class="otp-input w-12 h-12 border border-gray-300 rounded-lg text-center text-lg font-bold focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                            <input type="text" maxlength="1" class="otp-input w-12 h-12 border border-gray-300 rounded-lg text-center text-lg font-bold focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                            <input type="text" maxlength="1" class="otp-input w-12 h-12 border border-gray-300 rounded-lg text-center text-lg font-bold focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                            <input type="text" maxlength="1" class="otp-input w-12 h-12 border border-gray-300 rounded-lg text-center text-lg font-bold focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                            <input type="text" maxlength="1" class="otp-input w-12 h-12 border border-gray-300 rounded-lg text-center text-lg font-bold focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                            <input type="text" maxlength="1" class="otp-input w-12 h-12 border border-gray-300 rounded-lg text-center text-lg font-bold focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                        </div>
                        <span id="otpError" class="text-red-500 text-xs mt-1 hidden"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Code expires in <span id="timer" class="font-bold text-blue-600">60</span>s</span>
                        <button onclick="resendOTP()" id="resendBtn" class="text-blue-600 hover:text-blue-700 font-medium text-sm transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                            Resend
                        </button>
                    </div>
                    <button 
                        onclick="verifyOTP()" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition duration-300 mt-4"
                    >
                        Verify OTP
                    </button>
                </div>

                <!-- Step 3: New Password -->
                <div id="step3" class="hidden space-y-4">
                    <p class="text-gray-600 text-sm mb-4">Create a new password for your account.</p>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">New Password</label>
                        <input 
                            type="password" 
                            id="newPassword" 
                            placeholder="Enter new password" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                        >
                        <p id="passwordReqReset" class="text-sm text-red-500 mt-1">
                            Minimum 8 characters, 1 uppercase, 1 lowercase, 1 number, 1 special character (!@#$%^&*(),.?)
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Confirm Password</label>
                        <input 
                            type="password" 
                            id="confirmResetPassword" 
                            placeholder="Confirm new password" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                        >
                        <p id="resetPasswordMatch" class="text-sm mt-1"></p>
                        <span id="passwordError" class="text-red-500 text-xs mt-1 hidden"></span>
                    </div>

                    <button 
                        onclick="resetPassword()" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition duration-300 mt-4"
                    >
                        Reset Password
                    </button>
                </div>


                <!-- Success Message -->
                <div id="success" class="hidden text-center space-y-4">
                    <div class="flex justify-center mb-4">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">Password Reset Successful!</h3>
                    <p class="text-gray-600">Your password has been updated. You can now login with your new password.</p>
                    <button 
                        onclick="closeForgotPasswordModal()" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition duration-300"
                    >
                        Back to Login
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Modal Functions
        function openForgotPasswordModal() {
            document.getElementById('forgotPasswordModal').classList.remove('hidden');
            resetModalSteps();
        }

        function closeForgotPasswordModal() {
            document.getElementById('forgotPasswordModal').classList.add('hidden');
            resetModalSteps();
        }

        function resetModalSteps() {
            document.getElementById('step1').classList.remove('hidden');
            document.getElementById('step2').classList.add('hidden');
            document.getElementById('step3').classList.add('hidden');
            document.getElementById('success').classList.add('hidden');
            document.getElementById('phoneNumber').value = '';
            document.getElementById('newPassword').value = '';
            document.getElementById('confirmPassword').value = '';
            document.querySelectorAll('.otp-input').forEach(input => input.value = '');
        }

        // OTP Input Focus Management
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('otp-input')) {
                if (e.target.value) {
                    const next = e.target.nextElementSibling;
                    if (next && next.classList.contains('otp-input')) {
                        next.focus();
                    }
                }
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.target.classList.contains('otp-input') && e.key === 'Backspace') {
                e.target.value = '';
                const prev = e.target.previousElementSibling;
                if (prev && prev.classList.contains('otp-input')) {
                    prev.focus();
                }
            }
        });

        // Send OTP
        function sendOTP() {
            const phone = document.getElementById('phoneNumber').value.trim();
            const phoneError = document.getElementById('phoneError');
            const sendBtn = event.target;

            if (!phone) {
                phoneError.textContent = 'Phone number is required';
                phoneError.classList.remove('hidden');
                return;
            }

            if (!/^[\d\s\-()]{10,}$/.test(phone)) {
                phoneError.textContent = 'Please enter a valid phone number';
                phoneError.classList.remove('hidden');
                return;
            }

            sendBtn.disabled = true;
            sendBtn.textContent = 'Sending...';

            fetch("/Tin's_RCH-Techworks/forgot_pass_otp_backend.php", {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'send_otp',
                    phone: phone
                })
            })
            .then(async response => {
                const contentType = response.headers.get('content-type');
                if (!response.ok) {
                    throw new Error(`Server error: ${response.status}`);
                } else if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    throw new Error('Invalid response (HTML returned): ' + text.slice(0, 50));
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    phoneError.classList.add('hidden');
                    document.getElementById('step1').classList.add('hidden');
                    document.getElementById('step2').classList.remove('hidden');
                    startTimer();
                } else {
                    phoneError.textContent = data.message || 'Failed to send OTP';
                    phoneError.classList.remove('hidden');
                }
            })
            .catch(error => {
                phoneError.textContent = 'Network error: ' + error.message;
                phoneError.classList.remove('hidden');
            })
            .finally(() => {
                sendBtn.disabled = false;
                sendBtn.textContent = 'Send OTP';
            });
        }


        // Timer
        let timerInterval;
        function startTimer() {
            let timeLeft = 60;
            const resendBtn = document.getElementById('resendBtn');
            resendBtn.disabled = true;

            timerInterval = setInterval(() => {
                timeLeft--;
                document.getElementById('timer').textContent = timeLeft;
                if (timeLeft === 0) {
                    clearInterval(timerInterval);
                    resendBtn.disabled = false;
                }
            }, 1000);
        }

        function resendOTP() {
    document.querySelectorAll('.otp-input').forEach(input => input.value = '');
    const resendBtn = document.getElementById('resendBtn');
    const otpError = document.getElementById('otpError');
    
    resendBtn.disabled = true;
    resendBtn.textContent = 'Sending...';

    const phone = document.getElementById('phoneNumber').value.trim();
    if (!phone) return;

    const formData = new FormData();
    formData.append('action', 'send_otp'); 
    formData.append('phone', phone);    

    fetch("/Tin's_RCH-Techworks/forgot_pass_otp_backend.php", {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            otpError.classList.add('hidden');
            startTimer();
            resendBtn.textContent = 'Resend';
        } else {
            otpError.textContent = data.message || 'Failed to resend OTP';
            otpError.classList.remove('hidden');
            resendBtn.disabled = false;
            resendBtn.textContent = 'Resend';
        }
    })
    .catch(error => {
        otpError.textContent = 'Network error: ' + error.message;
        otpError.classList.remove('hidden');
        resendBtn.disabled = false;
        resendBtn.textContent = 'Resend';
    });
}

        // Verify OTP
        function verifyOTP() {
            const otpInputs = document.querySelectorAll('.otp-input');
            const otp = Array.from(otpInputs).map(input => input.value).join('');
            const otpError = document.getElementById('otpError');
            const verifyBtn = event.target;

            if (otp.length !== 6) {
                otpError.textContent = 'Please enter all 6 digits';
                otpError.classList.remove('hidden');
                return;
            }

            // Disable button and show loading state
            verifyBtn.disabled = true;
            verifyBtn.textContent = 'Verifying...';

            // Verify OTP via AJAX
            const formData = new FormData();
            formData.append('action', 'verify_otp');
            formData.append('otp', otp);

            fetch("/Tin's_RCH-Techworks/forgot_pass_otp_backend.php", {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    otpError.classList.add('hidden');
                    clearInterval(timerInterval);
                    document.getElementById('step2').classList.add('hidden');
                    document.getElementById('step3').classList.remove('hidden');
                    verifyBtn.disabled = false;
                    verifyBtn.textContent = 'Verify OTP';
                } else {
                    otpError.textContent = data.message || 'Invalid OTP';
                    otpError.classList.remove('hidden');
                    verifyBtn.disabled = false;
                    verifyBtn.textContent = 'Verify OTP';
                }
            })
            .catch(error => {
                otpError.textContent = 'Network error: ' + error.message;
                otpError.classList.remove('hidden');
                verifyBtn.disabled = false;
                verifyBtn.textContent = 'Verify OTP';
            });
        }

        // Reset Password
        // Password Requirements Elements
        const newPasswordInput = document.getElementById('newPassword');
        const confirmPasswordInput = document.getElementById('confirmResetPassword');
        const passwordReqReset = document.getElementById('passwordReqReset');
        const resetPasswordMatch = document.getElementById('resetPasswordMatch');
        const resetBtn = document.querySelector('#step3 button');

        const requirements = [
            { regex: /.{8,}/, text: '8+' },
            { regex: /[A-Z]/, text: 'A-Z' },
            { regex: /[a-z]/, text: 'a-z' },
            { regex: /[0-9]/, text: '0-9' },
            { regex: /[!@#$%^&*(),.?":{}|<>]/, text: '!@#$...' },
        ];

        // Function to update password requirement indicators (inline) and input border
        function updatePasswordRequirements() {
            const value = newPasswordInput.value;
            let allMet = true;
            let html = '';
            requirements.forEach((req, index) => {
                if (req.regex.test(value)) {
                    html += `<span class="text-green-600 font-semibold">${req.text}</span>`;
                } else {
                    html += `<span class="text-red-500 font-semibold">${req.text}</span>`;
                    allMet = false;
                }
                if (index !== requirements.length - 1) html += ' , '; 
            });
            passwordReqReset.innerHTML = html;

            // Update input border color
            if (value) {
                if (allMet) {
                    newPasswordInput.classList.remove('border-red-500');
                    newPasswordInput.classList.add('border-green-600');
                } else {
                    newPasswordInput.classList.remove('border-green-600');
                    newPasswordInput.classList.add('border-red-500');
                }
            } else {
                newPasswordInput.classList.remove('border-green-600', 'border-red-500');
            }

            return allMet;
        }

        // Function to update password match with professional messages and input border
        function updatePasswordMatch() {
            if (!confirmPasswordInput.value) {
                resetPasswordMatch.textContent = '';
                confirmPasswordInput.classList.remove('border-green-600', 'border-red-500');
                return;
            }

            if (newPasswordInput.value === confirmPasswordInput.value) {
                resetPasswordMatch.textContent = 'Great! Your passwords match.';
                resetPasswordMatch.classList.remove('text-red-500');
                resetPasswordMatch.classList.add('text-green-600');

                confirmPasswordInput.classList.remove('border-red-500');
                confirmPasswordInput.classList.add('border-green-600');
            } else {
                resetPasswordMatch.textContent = 'Oops! Your passwords don’t match.';
                resetPasswordMatch.classList.remove('text-green-600');
                resetPasswordMatch.classList.add('text-red-500');

                confirmPasswordInput.classList.remove('border-green-600');
                confirmPasswordInput.classList.add('border-red-500');
            }
        }

        // Real-time listeners
        newPasswordInput.addEventListener('input', () => {
            updatePasswordRequirements();
            updatePasswordMatch(); // Check match in real-time
        });
        confirmPasswordInput.addEventListener('input', updatePasswordMatch);


        // Reset Password Function
        function resetPassword() {
            const newPassword = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            const passwordError = document.getElementById('passwordError');

            if (!updatePasswordRequirements()) {
                passwordError.textContent = 'Password does not meet all requirements';
                passwordError.classList.remove('hidden');
                return;
            }

            if (newPassword !== confirmPassword) {
                passwordError.textContent = 'Passwords do not match';
                passwordError.classList.remove('hidden');
                return;
            }

            passwordError.classList.add('hidden');

            resetBtn.disabled = true;
            resetBtn.textContent = 'Resetting...';

            const formData = new FormData();
            formData.append('action', 'reset_password');
            formData.append('password', newPassword);
            formData.append('confirmPassword', confirmPassword);

            fetch("/Tin's_RCH-Techworks/forgot_pass_otp_backend.php", {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    passwordError.classList.add('hidden');
                    document.getElementById('step3').classList.add('hidden');
                    document.getElementById('success').classList.remove('hidden');
                } else {
                    passwordError.textContent = data.message || 'Failed to reset password';
                    passwordError.classList.remove('hidden');
                }
            })
            .catch(error => {
                passwordError.textContent = 'Network error: ' + error.message;
                passwordError.classList.remove('hidden');
            })
            .finally(() => {
                resetBtn.disabled = false;
                resetBtn.textContent = 'Reset Password';
            });
        }

    </script>

</body>

</html>