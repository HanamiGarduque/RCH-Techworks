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

                <form class="space-y-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email"
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
                        <a href="#" class="text-sm text-blue-500 hover:text-blue-600 font-medium">Forgot password?</a>
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
</body>

</html>