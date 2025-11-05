<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RCH Water - QR Code Scanner & Upload</title>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>


<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="group bg-gradient-to-br from-blue-400 to-blue-600 shadow-lg border-r border-gray-200 
        flex-shrink-0 fixed top-0 left-0 h-full overflow-hidden flex flex-col text-white 
        transition-all duration-500 ease-in-out w-20 hover:w-64 z-50">

            <!-- Logo -->
            <div class="p-7 border-b border-blue-400 flex items-center gap-2">
                <i class="fas fa-droplet text-3xl"></i>
                <div class="overflow-hidden">
                    <span class="text-2xl font-semibold block whitespace-nowrap transition-all duration-500 ease-in-out 
                 opacity-0 group-hover:opacity-100 translate-x-[-20px] group-hover:translate-x-0">
                        RCH Water
                    </span>
                </div>
            </div>

            <nav class="mt-6 flex-1">
                <ul class="space-y-1 px-4">
                    <li>
                        <a href="../Admin/dashboard.php" class="flex items-center px-4 py-3 text-white rounded-lg font-medium hover:bg-white/20">
                            <i class="fas fa-chart-line text-lg"></i>
                            <div class="overflow-hidden ml-3">
                                <span class="block whitespace-nowrap transition-all duration-500 ease-in-out 
                                     opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">
                                    Dashboard
                                </span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="../Order/orders_list.php" class="flex items-center px-4 py-3 text-white rounded-lg font-medium hover:bg-white/20">
                            <i class="fas fa-shopping-cart text-lg"></i>
                            <div class="overflow-hidden ml-3">
                                <span class="block whitespace-nowrap transition-all duration-500 ease-in-out 
                                     opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">
                                    Orders
                                </span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="../GallonOwnership/gallon_ownership.php" class="flex items-center px-4 py-3 text-white rounded-lg font-medium hover:bg-white/20">
                            <i class="fas fa-tint text-lg"></i>
                            <div class="overflow-hidden ml-3">
                                <span class="block whitespace-nowrap transition-all duration-500 ease-in-out 
                 opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">
                                    Gallon Ownership
                                </span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="../Inventory/inventory_list.php" class="flex items-center px-4 py-3 text-white rounded-lg font-medium hover:bg-white/20">
                            <i class="fas fa-boxes text-lg"></i>
                            <div class="overflow-hidden ml-3">
                                <span class="block whitespace-nowrap transition-all duration-500 ease-in-out 
                 opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">
                                    Inventory
                                </span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="../Customer/customers_list.php" class="flex items-center px-4 py-3 text-white rounded-lg font-medium hover:bg-white/20">
                            <i class="fas fa-users text-lg"></i>
                            <div class="overflow-hidden ml-3">
                                <span class="block whitespace-nowrap transition-all duration-500 ease-in-out 
                 opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">
                                    Customers
                                </span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="../Delivery/deliveries_list.php" class="flex items-center px-4 py-3 bg-white/20 text-white rounded-lg font-medium">
                            <i class="fas fa-truck text-lg"></i>
                            <div class="overflow-hidden ml-3">
                                <span class="block whitespace-nowrap transition-all duration-500 ease-in-out 
                 opacity-0 group-hover:opacity-100 translate-x-[-10px] group-hover:translate-x-0">
                                    Deliveries
                                </span>
                            </div>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="border-t border-blue-400 p-4">
                <a href="../logout.php" class="flex items-center px-4 py-3 font-medium text-white hover:bg-blue-500 rounded-lg transition-colors">
                    <i class="fas fa-sign-out-alt text-lg"></i>
                    <span class="ml-3 hidden group-hover:inline">Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->

        <div id="page-content"
            class="main-content fixed w-full top-0 right-0 overflow-auto h-full transition-all duration-300 ease-in-out">
            <div
                class="flex-1 transition-all duration-300 ease-in-out group-hover:ml-64 ml-20 flex flex-col overflow-hidden">
                <!-- Header -->
                <div class="bg-white border-b border-gray-100 shadow-sm p-4 transition-all duration-300 ease-in-out">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col">
                            <h1 class="text-3xl font-semibold text-blue-500">Gallon Ownership</h1>
                            <p class="text-sm text-gray-700 mt-1">Manage and track ownership of gallons between customers and the station.</p>
                        </div>

                        <div class="flex items-center space-x-4">
                            <a href="../Notification/notifications.php" title="Notifications" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-blue-100 cursor-pointer transition">
                                <i class="fas fa-bell text-blue-500 text-xl"></i>
                            </a>
                            <a href="../Messaging/messages.php" title="Messages" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-blue-100 cursor-pointer transition">
                                <i class="fas fa-envelope text-blue-500 text-xl"></i>
                            </a>
                            <a href="../Admin/profile.php" title="Profile" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-blue-100 cursor-pointer transition">
                                <i class="fas fa-user text-blue-500 text-xl"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="m-3">
                    <!-- insert main content here -->
                   

                    <!-- MAIN BODY -->
                    <div class="flex flex-1 gap-6">

                        <!-- LEFT SECTION (2/3) -->
                        <div class="w-2/3 bg-white p-4 rounded-2xl shadow-md overflow-y-auto">
                            <h2 class="text-lg font-semibold mb-4 text-gray-700">QR Code Gallery</h2>
                            <div class="grid grid-cols-3 gap-4">
                                <!-- Example QR Items -->
                                <div class="p-3 border rounded-xl flex flex-col items-center shadow-sm hover:shadow-md transition">
                                    <img src="assets/sample-qr1.png" alt="QR Code 1" class="w-32 h-32 object-contain">
                                    <p class="text-sm mt-2 text-gray-600">Gallon #1</p>
                                </div>
                                <div class="p-3 border rounded-xl flex flex-col items-center shadow-sm hover:shadow-md transition">
                                    <img src="assets/sample-qr2.png" alt="QR Code 2" class="w-32 h-32 object-contain">
                                    <p class="text-sm mt-2 text-gray-600">Gallon #2</p>
                                </div>
                                <!-- Add more QR codes dynamically -->
                            </div>
                        </div>

                        <!-- RIGHT SECTION (1/3) -->
                        <div class="w-1/3 bg-white p-4 rounded-2xl shadow-md flex flex-col justify-between">
                            <div>
                                <h2 class="text-lg font-semibold mb-3 text-gray-700">Scan QR Code</h2>
                                <div id="reader" class="rounded-lg overflow-hidden border border-gray-300"></div>
                            </div>

                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Or Upload a QR Code Image:</label>
                                <input type="file" id="qrFileInput" accept="image/*" class="block w-full text-sm text-gray-700 border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                            </div>

                            <div id="result" class="mt-4 text-sm text-gray-700 bg-gray-50 p-3 rounded-lg overflow-auto max-h-40"></div>
                        </div>

                    </div>
                    </main>
                </div>


            </div>

        </div>
    </div>
    <script>
        //for smooth page transition
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.addEventListener('click', e => {
                e.preventDefault();
                const target = e.currentTarget.getAttribute('href');
                const content = document.getElementById('page-content');

                // Fade out
                content.classList.add('opacity-0', 'translate-x-2');

                setTimeout(() => {
                    window.location.href = target;
                }, 300); // same as duration-300
            });
        });

        const content = document.getElementById('page-content');
        content.classList.add('opacity-0');
        setTimeout(() => content.classList.remove('opacity-0', 'translate-x-2'), 50);

        let html5QrcodeScanner;
        const resultDiv = document.getElementById("result");
        const fileInput = document.getElementById("qrFileInput");

        // Start camera on load
        window.addEventListener("load", () => {
            startScanner();
        });

        function startScanner() {
            html5QrcodeScanner = new Html5Qrcode("reader");
            html5QrcodeScanner.start({
                    facingMode: "environment"
                }, {
                    fps: 10,
                    qrbox: 250
                },
                qrCodeMessage => handleDecodedCode(qrCodeMessage),
                errorMessage => console.log(errorMessage)
            );
        }

        // Handle uploaded image QR decoding
        fileInput.addEventListener("change", async () => {
            const file = fileInput.files[0];
            if (!file) return;

            resultDiv.innerHTML = "Decoding uploaded image...";
            const formData = new FormData();
            formData.append("file", file);

            try {
                const response = await fetch("https://api.qrserver.com/v1/read-qr-code/", {
                    method: "POST",
                    body: formData
                });
                const json = await response.json();
                const decodedText = json[0].symbol[0].data;

                if (decodedText) {
                    handleDecodedCode(decodedText);
                } else {
                    resultDiv.innerHTML = "No QR code found in image.";
                }
            } catch (err) {
                resultDiv.innerHTML = "Error decoding image.";
                console.error(err);
            }
        });

        // Display decoded QR code data
        function handleDecodedCode(codeValue) {
            fileInput.value = "";
            resultDiv.innerHTML = `<p class="text-green-700 font-semibold">Scanned Code: ${codeValue}</p>`;

            // Fetch related details
            fetch("fetch_qr.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "code=" + encodeURIComponent(codeValue)
                })
                .then(res => res.text())
                .then(data => {
                    resultDiv.innerHTML += `<div class="mt-3 text-gray-700"><strong>Details:</strong><br>${data}</div>`;
                });
        }
    </script>
</body>