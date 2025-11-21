<?php
session_start();
require_once '../Database/db_connection.php';

// Get data from GET parameters
$customer_name = isset($_GET['customer']) ? $_GET['customer'] : "Christine May Padua";
$destLat = isset($_GET['lat']) ? floatval($_GET['lat']) : 13.9434;
$destLng = isset($_GET['lng']) ? floatval($_GET['lng']) : 121.1279;
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 1;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Scanner | RCH Water</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/minified/html5-qrcode.min.js"></script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="w-1/3 bg-white p-4 rounded-2xl shadow-md flex flex-col justify-between h-full">
        <div>
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold text-blue-500 flex-1">
                    <i class="fas fa-camera mr-2"></i> Scan QR Code
                </h2>
                <button id="toggleCameraBtn"
                    class="ml-2 bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-lg text-sm transition">
                    Open Camera
                </button>
            </div>

            <div class="relative mx-auto rounded-lg overflow-hidden border border-gray-300 w-[320px] h-[320px]" id="readerContainer">
                <div id="reader" class="absolute inset-0"></div>

                <!--Position Indicator -->
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="border-4 border-blue-400 rounded-lg w-[220px] h-[220px] opacity-80 animate-pulse"></div>
                </div>
            </div>

            <!--Camera Status Message -->
            <p id="cameraStatus" class="text-center text-gray-500 text-sm mt-2 italic">
                Camera is closed.
            </p>
        </div>

        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-600 mb-1">Or Upload a QR Code Image:</label>
            <input type="file" id="qrFileInput" accept="image/*"
                class="block w-full text-sm text-gray-700 border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>

        <div id="resultTable" class="mt-4 bg-gray-50 rounded-lg p-3 text-sm">
            <h3 class="font-semibold text-blue-500 mb-2">QR Code Information</h3>
            <table class="w-full text-left border-collapse">
                <tbody>
                    <tr class="border-b border-gray-200">
                        <td class="py-1 font-medium text-gray-600">Code Value:</td>
                        <td id="codeValue" class="py-1 text-gray-700">---</td>
                    </tr>
                    <tr class="border-b border-gray-200">
                        <td class="py-1 font-medium text-gray-600">Owner:</td>
                        <td id="codeOwner" class="py-1 text-gray-700">---</td>
                    </tr>
                    <tr class="border-b border-gray-200">
                        <td class="py-1 font-medium text-gray-600">Gallon Type:</td>
                        <td id="codeType" class="py-1 text-gray-700">---</td>
                    </tr>
                    <tr>
                        <td class="py-1 font-medium text-gray-600">Status:</td>
                        <td id="codeStatus" class="py-1 text-gray-700">---</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        let html5QrcodeScanner = null;
        let cameraActive = false;

        const toggleCameraBtn = document.getElementById("toggleCameraBtn");
        const cameraStatus = document.getElementById("cameraStatus");
        const fileInput = document.getElementById("qrFileInput");
        const codeValue = document.getElementById("codeValue");
        const codeOwner = document.getElementById("codeOwner");
        const codeType = document.getElementById("codeType");
        const codeStatus = document.getElementById("codeStatus");

        toggleCameraBtn.addEventListener("click", () => {
            if (!cameraActive) startScanner();
            else stopScanner();
        });

        async function startScanner() {
    try {
        const qrboxSize = 250;

        html5QrcodeScanner = new Html5Qrcode("reader");
        cameraStatus.textContent = "📷 Initializing camera...";

        const devices = await Html5Qrcode.getCameras();
        if (!devices || devices.length === 0) {
            cameraStatus.textContent = "No camera found.";
            return;
        }

        const cameraId = devices.find(d => d.label.toLowerCase().includes('back'))?.id || devices[0].id;

        await html5QrcodeScanner.start(
            { deviceId: { exact: cameraId } },
            { fps: 10, qrbox: { width: qrboxSize, height: qrboxSize }, aspectRatio: 1.0 },
            qrCodeMessage => handleDecodedCode(qrCodeMessage),
            errorMessage => { cameraStatus.textContent = "Fix QR code position..."; }
        );

        cameraActive = true;
        toggleCameraBtn.textContent = "Close Camera";
        cameraStatus.textContent = "Point your camera at a QR code...";
    } catch (err) {
        console.error("Camera start failed:", err);
        cameraStatus.textContent = "Failed to access camera.";
    }
}

async function stopScanner() {
    try {
        if (html5QrcodeScanner) {
            await html5QrcodeScanner.stop();
            await html5QrcodeScanner.clear();
            cameraActive = false;
            toggleCameraBtn.textContent = "Open Camera";
            cameraStatus.textContent = "Camera is closed.";
        }
    } catch (err) {
        console.error("Error stopping camera:", err);
    }
}

// Handle uploaded QR image
fileInput.addEventListener("change", async () => {
    const file = fileInput.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append("file", file);
    try {
        const response = await fetch("https://api.qrserver.com/v1/read-qr-code/", {
            method: "POST",
            body: formData
        });
        const json = await response.json();
        const decodedText = json[0].symbol[0].data;
        if (decodedText) handleDecodedCode(decodedText);
        else codeValue.innerText = "No QR code found.";
    } catch (err) {
        console.error(err);
    }
});

// Handle scanned QR
function handleDecodedCode(qrValue) {
    codeValue.innerText = qrValue;
    cameraStatus.textContent = "✅ QR Code Scanned!";

    fetch("fetch_qr.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "code=" + encodeURIComponent(qrValue)
    })
    .then(res => res.json())
    .then(data => {
        codeOwner.innerText = data.owner || "Unknown";
        codeType.innerText = data.type || "Unknown";
        codeStatus.innerText = data.status || "Unknown";
    })
    .catch(err => console.error(err));

    stopScanner();
}


        async function stopScanner() {
            try {
                if (html5QrcodeScanner) {
                    await html5QrcodeScanner.stop();
                    await html5QrcodeScanner.clear();
                    cameraActive = false;
                    toggleCameraBtn.textContent = "Open Camera";
                    cameraStatus.textContent = "Camera is closed.";
                }
            } catch (err) {
                console.error("Error stopping camera:", err);
            }
        }

        // Handle uploaded QR image
        fileInput.addEventListener("change", async () => {
            const file = fileInput.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append("file", file);
            try {
                const response = await fetch("https://api.qrserver.com/v1/read-qr-code/", {
                    method: "POST",
                    body: formData
                });
                const json = await response.json();
                const decodedText = json[0].symbol[0].data;
                if (decodedText) handleDecodedCode(decodedText);
                else codeValue.innerText = "No QR code found.";
            } catch (err) {
                console.error(err);
            }
        });

        // Handle scanned QR
        function handleDecodedCode(qrValue) {
            codeValue.innerText = qrValue;
            cameraStatus.textContent = "✅ QR Code Scanned!";

            // Fetch details from backend
            fetch("fetch_qr.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "code=" + encodeURIComponent(qrValue)
                })
                .then(res => res.json())
                .then(data => {
                    codeOwner.innerText = data.owner || "Unknown";
                    codeType.innerText = data.type || "Unknown";
                    codeStatus.innerText = data.status || "Unknown";
                })
                .catch(err => console.error(err));

            stopScanner();
        }
    </script>

</body>

</html>