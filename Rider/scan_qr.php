<?php 
session_start();

// Get parameters from URL
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 9;
$refill_id = isset($_GET['refill_id']) ? intval($_GET['refill_id']) : 0;
$order_number = isset($_GET['order_number']) ? $_GET['order_number'] : '';
$customer_name = isset($_GET['customer']) ? $_GET['customer'] : 'Customer';
$lat = isset($_GET['lat']) ? floatval($_GET['lat']) : 0;
$lng = isset($_GET['lng']) ? floatval($_GET['lng']) : 0;

if ($user_id <= 0 && $refill_id <= 0) {
    echo "Invalid parameters.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan QR Code | <?= htmlspecialchars($customer_name) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .phone-shell { width: 340px; height: 680px; max-width: calc(100vw - 16px); max-height: calc(100vh - 16px); display: flex; flex-direction: column; overflow: hidden; position: relative; z-index: 1; }
        .scroll-inside { flex: 1 1 auto; min-height: 0; overflow-y: auto; overflow-x: hidden; -webkit-overflow-scrolling: touch; overscroll-behavior: contain; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); z-index: 9999; align-items: center; justify-content: center; padding: 16px; }
        .modal-overlay.active { display: flex; }
        .modal-box { position: relative; z-index: 10000; width: 100%; }
        .loading-spinner { display: inline-block; width: 16px; height: 16px; border: 2px solid rgba(255, 255, 255, 0.3); border-top-color: white; border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .status-message { animation: slideIn 0.3s ease; }
        #qr-reader { border-radius: 16px; overflow: hidden; }
        #qr-reader__dashboard_section { display: none !important; }
        .scanner-frame { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 180px; height: 200px; border: 3px solid #60a5fa; border-radius: 16px; box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.5); pointer-events: none; }
        .scanner-corner { position: absolute; width: 30px; height: 30px; border: 4px solid #60a5fa; }
        .corner-tl { top: -2px; left: -2px; border-right: none; border-bottom: none; border-top-left-radius: 16px; }
        .corner-tr { top: -2px; right: -2px; border-left: none; border-bottom: none; border-top-right-radius: 16px; }
        .corner-bl { bottom: -2px; left: -2px; border-right: none; border-top: none; border-bottom-left-radius: 16px; }
        .corner-br { bottom: -2px; right: -2px; border-left: none; border-top: none; border-bottom-right-radius: 16px; }
    </style>
</head>
<body class="bg-white min-h-screen flex justify-center items-center relative">
    <div class="phone-shell bg-white mx-auto relative rounded- overflow-hidden border shadow-lg">
        <div class="bg-gradient-to-br from-blue-400 to-blue-600 text-white sticky top-0 z-50 w-full px-4 py-3 shrink-0">
            <div class="flex items-center justify-between mb-1">
                <button onclick="history.back()" class="text-xl hover:bg-blue-500 rounded-lg transition"><i class="bi bi-chevron-left"></i></button>
                <span class="status-badge bg-white text-blue-600"><i class="bi bi-qr-code-scan"></i><span>Complete Delivery</span></span>
            </div>
            <div>
                <h1 class="text-lg font-bold tracking-tight">Scan QR Code</h1>
                <div class="flex items-center gap-2 text-blue-100"><i class="bi bi-person-circle text-xl"></i><p class="text-sm font-medium"><?= htmlspecialchars($customer_name) ?></p></div>
            </div>
        </div>
        <div class="scroll-inside custom-scrollbar">
            <div class="px-5 pt-4 pb-5">
                <div class="bg-blue-50 border-l-4 border-blue-600 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <div class="w-12 h-12 shrink-0 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center text-white text-2xl"><i class="bi bi-person-fill"></i></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-base font-bold text-gray-900 leading-tight"><?= htmlspecialchars($customer_name) ?></p>
                            <p class="text-xs text-gray-500 mt-0.5">Delivery Destination</p>
                            <p class="text-xs text-gray-600 mt-2 flex items-start gap-1.5"><i class="bi bi-geo-alt-fill text-blue-600 mt-0.5"></i><span class="leading-relaxed">Lat: <?= $lat ?>, Lng: <?= $lng ?></span></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-5 pb-5">
                <div class="status-message hidden items-center gap-2.5 bg-green-50 border-2 border-green-200 text-green-800 rounded-xl px-4 py-3" id="success-message">
                    <i class="bi bi-check-circle-fill text-xl"></i>
                    <span class="text- font-medium flex-1">QR Code scanned successfully!</span>
                </div>
                <div class="status-message hidden items-center gap-2.5 bg-red-50 border-2 border-red-200 text-red-800 rounded-xl px-4 py-3" id="error-message">
                    <i class="bi bi-x-circle-fill text-xl"></i>
                    <span class="text- font-medium flex-1" id="error-text">Invalid QR code. Please try again.</span>
                </div>
            </div>
            <div class="px-5 pb-5">
                <h3 class="text-md font-semibold text-blue-600 mb-3 flex items-center gap-2"><i class="bi bi-qr-code-scan text-blue-600"></i> Point camera at gallon QR code</h3>
                <div class="relative">
                    <div class="rounded-2xl overflow-hidden bg-black relative shadow-md">
                        <div id="qr-reader"></div>
                        <div class="absolute inset-0 pointer-events-none z-10">
                            <div class="scanner-frame">
                                <div class="scanner-corner corner-tl"></div>
                                <div class="scanner-corner corner-tr"></div>
                                <div class="scanner-corner corner-bl"></div>
                                <div class="scanner-corner corner-br"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-5 pb-5">
                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2"><i class="bi bi-info-circle-fill text-amber-500"></i> Instructions</h3>
                    <ul class="list-disc pl-5 space-y-2">
                        <li class="text- text-gray-500 leading-relaxed">Hold your device steady</li>
                        <li class="text- text-gray-500 leading-relaxed">Ensure good lighting conditions</li>
                        <li class="text- text-gray-500 leading-relaxed">Center the QR code within the frame</li>
                        <li class="text- text-gray-500 leading-relaxed">Wait for automatic scan</li>
                    </ul>
                </div>
            </div>
            <div class="pb-5"></div>
        </div>
        <div class="bg-white border-t border-gray-200 px-5 py-4 shrink-0 shadow-2xl">
            <div class="flex gap-3">
                <button onclick="manualEntry()" class="flex-1 py-3 bg-gray-100 text-gray-500 border-2 border-gray-200 rounded-xl font-bold hover:bg-gray-200 transition flex items-center justify-center gap-2 text-sm"><i class="bi bi-keyboard"></i> Manual Entry</button>
                <button id="complete-btn" disabled onclick="completeDelivery()" class="flex-1 py-3 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition flex items-center justify-center gap-2 shadow-lg text-sm disabled:opacity-50 disabled:cursor-not-allowed"><i class="bi bi-check-circle"></i> Complete</button>
            </div>
        </div>
    </div>
    <div id="success-modal" class="modal-overlay">
        <div class="modal-box bg-white rounded-2xl p-6 w-11/12 max-w-sm flex flex-col items-center text-center">
            <div class="w- h- bg-gradient-to-br from-green-300 to-green-500 rounded-full flex items-center justify-center text-white text- mb-5"><i class="bi bi-check-lg"></i></div>
            <h3 class="text-xl font-bold text-gray-900 mb-3">Delivery Complete!</h3>
            <p class="text-sm text-gray-500 leading-relaxed mb-6">The delivery has been successfully completed. Thank you!</p>
            <button onclick="returnToDashboard()" class="w-full py-3 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition flex items-center justify-center gap-2"><i class="bi bi-house-door"></i> Back to Dashboard</button>
        </div>
    </div>
<script>
let scannedData = null;
let html5QrCode = null;
const REFILL_ID = <?= $refill_id ?>;
const ORDER_NUMBER = <?= json_encode($order_number) ?>;

function initScanner() {
    html5QrCode = new Html5Qrcode("qr-reader");
    const config = { fps: 10, qrbox: { width: 200, height: 200 }, aspectRatio: 1.0, videoConstraints: { facingMode: "environment" } };
    html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess, onScanError)
    .catch(err => { console.error("Unable to start scanner", err); showError("Camera access denied or not available"); });
}
function onScanSuccess(decodedText, decodedResult) {
    if (html5QrCode && html5QrCode.isScanning) html5QrCode.stop();
    scannedData = decodedText;
    document.getElementById('success-message').style.display = 'flex';
    document.getElementById('error-message').style.display = 'none';
    document.getElementById('complete-btn').disabled = false;
    if (navigator.vibrate) navigator.vibrate(200);
    console.log("QR Code scanned:", decodedText);
}
function onScanError(errorMessage) {}
function showError(message) {
    document.getElementById('error-text').innerText = message;
    document.getElementById('error-message').style.display = 'flex';
    document.getElementById('success-message').style.display = 'none';
    setTimeout(() => { document.getElementById('error-message').style.display = 'none'; }, 3000);
}
function manualEntry() {
    const code = prompt("Enter gallon QR code manually:");
    if (code && code.trim()) {
        scannedData = code.trim();
        document.getElementById('success-message').style.display = 'flex';
        document.getElementById('complete-btn').disabled = false;
        if (html5QrCode && html5QrCode.isScanning) html5QrCode.stop();
    }
}

// ===== FIXED: NOW REALLY SAVES TO DB =====
function completeDelivery() {
    if (!scannedData) { showError("Please scan a QR code first"); return; }
    const btn = document.getElementById('complete-btn');
    btn.disabled = true;
    btn.innerHTML = '<div class="loading-spinner"></div> Processing...';

    const formData = new FormData();
    formData.append('refill_id', REFILL_ID); // from ?refill_id= in URL
    formData.append('order_number', ORDER_NUMBER);
    formData.append('new_status', 'delivered');
    formData.append('qr_code', scannedData);

    fetch('order_status_update.php', { method: 'POST', body: formData })
    .then(async res => {
        const text = await res.text();
        console.log('completeDelivery raw:', text);
        try { return JSON.parse(text); }
        catch(e) { throw new Error('Server returned non-JSON: ' + text.substring(0, 300)); }
    })
    .then(data => {
        if (data.success) {
            document.getElementById('success-modal').classList.add('active');
        } else {
            showError(data.error || 'Failed to complete delivery');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle"></i> Complete';
        }
    })
    .catch(err => {
        showError('Network error: ' + err.message);
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle"></i> Complete';
    });
}
function returnToDashboard() { window.location.href = 'dashboard.php'; }
window.addEventListener('load', () => { initScanner(); });
window.addEventListener('beforeunload', () => { if (html5QrCode && html5QrCode.isScanning) html5QrCode.stop(); });
</script>
</body>
</html>