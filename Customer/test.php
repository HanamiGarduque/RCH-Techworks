<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>RCH Water - Order Water Refill</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        /* Step indicator styling */
        .step-dots {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
            gap: 10px;
        }

        .step-dot {
            width: 2.5rem;
            height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            transition: all .3s;
            font-weight: 600;
        }

        .step-line {
            flex-grow: 1;
            height: 6px;
            background-color: #E5E7EB;
            border-radius: 4px;
            transition: background-color .3s;
        }

        .step-dot.completed,
        .step-dot.active {
            background-color: #2563eb;
            color: white;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
        }

        .step-dot.pending {
            background-color: #E5E7EB;
            color: #4B5563;
        }

        .step-line.completed {
            background-color: #2563eb;
        }

        /* Card hover effects */
        .gallon-card {
            transition: all 0.3s ease;
        }

        .gallon-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.3);
        }

        /* Disabled time slot styling */
        button[disabled] {
            cursor: not-allowed !important;
            opacity: 0.5 !important;
            background-color: #f3f4f6 !important;
            color: #9ca3af !important;
        }

        /* Header gradient */
        .header-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        /* Animated water droplet */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .float-animation {
            animation: float 3s ease-in-out infinite;
        }
    </style>
</head>

<body class="overflow-hidden">
    <div class="flex h-screen">
        <!-- Enhanced Sidebar -->
        <aside class="group bg-gradient-to-br from-blue-500 to-blue-700 shadow-2xl
            flex-shrink-0 fixed top-0 left-0 h-full overflow-hidden flex flex-col text-white 
            transition-all duration-500 ease-in-out w-20 hover:w-64 z-50">

            <!-- Logo Section -->
            <div class="px-6 py-6 border-b border-blue-400/30 flex items-center gap-3">
                <i class="fas fa-tint text-4xl float-animation"></i>
                <div class="overflow-hidden">
                    <span class="text-2xl font-bold block whitespace-nowrap transition-all duration-500 ease-in-out 
                        opacity-0 group-hover:opacity-100 translate-x-[-20px] group-hover:translate-x-0">
                        RCH Water
                    </span>
                    <span class="text-xs opacity-75 block whitespace-nowrap transition-all duration-500 ease-in-out 
                        opacity-0 group-hover:opacity-100 translate-x-[-20px] group-hover:translate-x-0">
                        Pure & Fresh
                    </span>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="mt-8 flex-1">
                <ul class="space-y-2 px-3">
                    <li>
                        <a href="#" class="flex items-center px-4 py-3 rounded-xl font-medium transition-all duration-200 bg-white/20 text-white">
                            <i class="fas fa-tint text-lg"></i>
                            <span class="ml-3 block whitespace-nowrap transition-all duration-500 ease-in-out 
                                opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">
                                Order Water Refill
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center px-4 py-3 rounded-xl font-medium transition-all duration-200 text-white hover:bg-white/20">
                            <i class="fas fa-box text-lg"></i>
                            <span class="ml-3 block whitespace-nowrap transition-all duration-500 ease-in-out 
                                opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">
                                My Orders
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center px-4 py-3 rounded-xl font-medium transition-all duration-200 text-white hover:bg-white/20">
                            <i class="fas fa-user text-lg"></i>
                            <span class="ml-3 block whitespace-nowrap transition-all duration-500 ease-in-out 
                                opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">
                                Profile
                            </span>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Logout Section -->
            <div class="border-t border-blue-400/30 px-3 py-5">
                <a href="#" class="flex items-center px-4 py-3 text-white rounded-xl font-medium hover:bg-red-500/20 transition-all">
                    <i class="fas fa-sign-out-alt text-lg"></i>
                    <span class="ml-3 block whitespace-nowrap transition-all duration-500 ease-in-out 
                        opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">
                        Logout
                    </span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 ml-20 overflow-y-auto">
            <!-- Enhanced Header -->
            <div class="header-gradient text-white p-8 shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-4xl font-bold mb-2">Order Water Refill</h1>
                        <p class="text-blue-100 text-lg">Hydrate made easy—order and we'll handle the rest! 💧</p>
                    </div>
                    <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6 text-center">
                        <i class="fas fa-tint text-5xl mb-2"></i>
                        <p class="text-sm font-semibold">24/7 Service</p>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="p-8">
                <!-- Steps Card with Enhanced Design -->
                <div class="bg-white rounded-2xl shadow-2xl p-8 mb-6">
                    <!-- Step Indicator -->
                    <div class="step-dots mb-8">
                        <div id="step1Dot" class="step-dot active">1</div>
                        <div class="step-line"></div>
                        <div id="step2Dot" class="step-dot pending">2</div>
                        <div class="step-line"></div>
                        <div id="step3Dot" class="step-dot pending">3</div>
                        <div class="step-line"></div>
                        <div id="step4Dot" class="step-dot pending">4</div>
                        <div class="step-line"></div>
                        <div id="step5Dot" class="step-dot pending">5</div>
                        <div class="step-line"></div>
                        <div id="step6Dot" class="step-dot pending">6</div>
                    </div>

                    <h2 id="stepTitle" class="text-2xl font-bold mb-6 text-gray-800 text-center">
                        <i class="fas fa-shopping-cart text-blue-600 mr-2"></i>
                        Step 1: Choose Gallon Type
                    </h2>

                    <!-- Step 1: Gallon Type Selection -->
                    <div id="set1" class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                        <div id="slimCard" class="gallon-card border-3 border-gray-300 p-8 rounded-2xl cursor-pointer text-center bg-gradient-to-br from-blue-50 to-white hover:from-blue-100 hover:to-blue-50 transition-all">
                            <div class="bg-white rounded-xl p-6 mb-4 shadow-lg">
                                <img src="img/20L_box_type_gallon.jpg" class="mx-auto w-48 h-48 object-contain" />
                            </div>
                            <h3 class="font-bold text-xl text-gray-800 mb-2">Slim Gallon</h3>
                            <p class="text-gray-600">Perfect for compact spaces</p>
                        </div>

                        <div id="roundCard" class="gallon-card border-3 border-gray-300 p-8 rounded-2xl cursor-pointer text-center bg-gradient-to-br from-green-50 to-white hover:from-green-100 hover:to-green-50 transition-all">
                            <div class="bg-white rounded-xl p-6 mb-4 shadow-lg">
                                <img src="img/18.9L_round_gallon.jpg" class="mx-auto w-48 h-48 object-contain" />
                            </div>
                            <h3 class="font-bold text-xl text-gray-800 mb-2">Round Gallon</h3>
                            <p class="text-gray-600">Classic 18.9L capacity</p>
                        </div>
                    </div>

                    <!-- Other steps remain hidden initially -->
                    <div id="set2" class="hidden"><!-- Size selection content --></div>
                    <div id="set3" class="hidden"><!-- Quantity selection content --></div>
                    <div id="set4" class="hidden"><!-- Pickup selection content --></div>
                    <div id="set5" class="hidden"><!-- Delivery selection content --></div>
                    <div id="set6" class="hidden"><!-- Review content --></div>
                </div>

                <!-- Controls -->
                <div id="controls" class="flex justify-between items-center max-w-4xl mx-auto"></div>
            </div>
        </main>
    </div>

    <script>
        // Your existing JavaScript code here
        console.log('Customer order page loaded');
    </script>
</body>
</html>