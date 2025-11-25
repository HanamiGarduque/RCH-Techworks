<?php 
session_start();

// Get parameters from URL
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 9;
$customer_name = isset($_GET['customer']) ? $_GET['customer'] : 'Customer';
$lat = isset($_GET['lat']) ? floatval($_GET['lat']) : 0;
$lng = isset($_GET['lng']) ? floatval($_GET['lng']) : 0;

if ($user_id <= 0) {
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

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<style>
body { 
    margin: 0; 
    font-family: 'Poppins', sans-serif; 
    background: #f4f6fa; 
    color: #333; 
    overflow: hidden; 
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}

.phone-container {
    background: white;
    width: 375px;
    height: 750px;
    position: relative;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
}

/* Header */
.header {
    background: linear-gradient(135deg, #60a5fa 0%, #2563eb 100%);
    color: white;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative;
    z-index: 1000;
}

.back-btn {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 20px;
    transition: all 0.2s;
}

.back-btn:hover {
    background: rgba(255,255,255,0.3);
}

.header-title {
    flex: 1;
    text-align: center;
    margin: 0 12px;
}

.header-title h2 {
    font-size: 16px;
    font-weight: 600;
    margin: 0;
    margin-bottom: 2px;
}

.header-title p {
    font-size: 12px;
    opacity: 0.9;
    margin: 0;
}

.header-actions {
    width: 36px;
}

/* Content Area */
.content {
    padding: 20px;
    height: calc(100% - 60px);
    overflow-y: auto;
}

/* Customer Info Card */
.customer-card {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border: 2px solid #bae6fd;
    border-radius: 16px;
    padding: 16px;
    margin-bottom: 20px;
}

.customer-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.customer-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #60a5fa 0%, #2563eb 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
}

.customer-details h3 {
    font-size: 16px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
    margin-bottom: 4px;
}

.customer-details p {
    font-size: 12px;
    color: #64748b;
    margin: 0;
}

.customer-address {
    display: flex;
    align-items: start;
    gap: 8px;
    padding: 12px;
    background: white;
    border-radius: 10px;
    margin-top: 12px;
}

.customer-address i {
    color: #2563eb;
    font-size: 16px;
    margin-top: 2px;
}

.customer-address span {
    font-size: 13px;
    color: #475569;
    line-height: 1.5;
}

/* Scanner Section */
.scanner-section {
    margin-bottom: 20px;
}

.scanner-title {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 16px;
}

.scanner-title i {
    color: #2563eb;
    font-size: 20px;
}

.scanner-title h3 {
    font-size: 15px;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}

.scanner-container {
    border-radius: 16px;
    overflow: hidden;
    background: #000;
    position: relative;
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
}

#qr-reader {
    border-radius: 16px;
    overflow: hidden;
}

#qr-reader__dashboard_section {
    display: none !important;
}

.scanner-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
    z-index: 10;
}

.scanner-frame {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 200px;
    height: 200px;
    border: 3px solid #60a5fa;
    border-radius: 16px;
    box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.5);
}

.scanner-corner {
    position: absolute;
    width: 30px;
    height: 30px;
    border: 4px solid #60a5fa;
}

.corner-tl { top: -2px; left: -2px; border-right: none; border-bottom: none; border-top-left-radius: 16px; }
.corner-tr { top: -2px; right: -2px; border-left: none; border-bottom: none; border-top-right-radius: 16px; }
.corner-bl { bottom: -2px; left: -2px; border-right: none; border-top: none; border-bottom-left-radius: 16px; }
.corner-br { bottom: -2px; right: -2px; border-left: none; border-top: none; border-bottom-right-radius: 16px; }

/* Instructions */
.instructions {
    background: #f8fafc;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 20px;
}

.instructions h4 {
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 12px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.instructions h4 i {
    color: #f59e0b;
}

.instructions ul {
    margin: 0;
    padding-left: 20px;
}

.instructions li {
    font-size: 13px;
    color: #64748b;
    line-height: 1.6;
    margin-bottom: 8px;
}

/* Status Messages */
.status-message {
    padding: 12px 16px;
    border-radius: 12px;
    margin-bottom: 16px;
    display: none;
    align-items: center;
    gap: 10px;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.status-message.success {
    background: #dcfce7;
    border: 2px solid #86efac;
    color: #166534;
}

.status-message.error {
    background: #fee2e2;
    border: 2px solid #fca5a5;
    color: #991b1b;
}

.status-message i {
    font-size: 20px;
}

.status-message span {
    font-size: 13px;
    font-weight: 500;
    flex: 1;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 12px;
    margin-top: 20px;
}

.btn {
    flex: 1;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    padding: 14px 20px;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-family: 'Poppins', sans-serif;
}

.btn-primary { 
    background: linear-gradient(135deg, #60a5fa 0%, #2563eb 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
}

.btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-secondary { 
    background: #f1f5f9;
    color: #64748b;
    border: 2px solid #e2e8f0;
}

.btn-secondary:hover {
    background: #e2e8f0;
    border-color: #cbd5e1;
}

/* Success Modal */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    z-index: 2000;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 20px;
    padding: 32px;
    max-width: 320px;
    text-align: center;
    animation: modalPop 0.3s ease;
}

@keyframes modalPop {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.success-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #86efac 0%, #22c55e 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    color: white;
    font-size: 40px;
}

.modal-content h3 {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 12px 0;
}

.modal-content p {
    font-size: 14px;
    color: #64748b;
    margin: 0 0 24px 0;
    line-height: 1.5;
}

@media (max-width: 480px) {
    .phone-container {
        width: 100vw;
        height: 100vh;
        border-radius: 0;
    }
}
</style>
</head>
<body>

<div class="phone-container">
    <!-- Header -->
    <div class="header">
        <button class="back-btn" onclick="history.back()">
            <i class="bi bi-arrow-left"></i>
        </button>
        <div class="header-title">
            <h2>Scan QR Code</h2>
            <p>Complete delivery</p>
        </div>
        <div class="header-actions"></div>
    </div>

    <!-- Content -->
    <div class="content">
        <!-- Customer Info Card -->
        <div class="customer-card">
            <div class="customer-header">
                <div class="customer-icon">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div class="customer-details">
                    <h3><?= htmlspecialchars($customer_name) ?></h3>
                    <p>Delivery Destination</p>
                </div>
            </div>
            <div class="customer-address">
                <i class="bi bi-geo-alt-fill"></i>
                <span>Lat: <?= $lat ?>, Lng: <?= $lng ?></span>
            </div>
        </div>

        <!-- Status Messages -->
        <div class="status-message success" id="success-message">
            <i class="bi bi-check-circle-fill"></i>
            <span>QR Code scanned successfully!</span>
        </div>

        <div class="status-message error" id="error-message">
            <i class="bi bi-x-circle-fill"></i>
            <span id="error-text">Invalid QR code. Please try again.</span>
        </div>

        <!-- Scanner Section -->
        <div class="scanner-section">
            <div class="scanner-title">
                <i class="bi bi-qr-code-scan"></i>
                <h3>Point camera at gallon QR code</h3>
            </div>
            
            <div class="scanner-container">
                <div id="qr-reader"></div>
                <div class="scanner-overlay">
                    <div class="scanner-frame">
                        <div class="scanner-corner corner-tl"></div>
                        <div class="scanner-corner corner-tr"></div>
                        <div class="scanner-corner corner-bl"></div>
                        <div class="scanner-corner corner-br"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instructions -->
        <div class="instructions">
            <h4>
                <i class="bi bi-info-circle-fill"></i>
                Instructions
            </h4>
            <ul>
                <li>Hold your device steady</li>
                <li>Ensure good lighting conditions</li>
                <li>Center the QR code within the frame</li>
                <li>Wait for automatic scan</li>
            </ul>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <button class="btn btn-secondary" onclick="manualEntry()">
                <i class="bi bi-keyboard"></i>
                Manual Entry
            </button>
            <button class="btn btn-primary" id="complete-btn" disabled onclick="completeDelivery()">
                <i class="bi bi-check-circle"></i>
                Complete
            </button>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal" id="success-modal">
    <div class="modal-content">
        <div class="success-icon">
            <i class="bi bi-check-lg"></i>
        </div>
        <h3>Delivery Complete!</h3>
        <p>The delivery has been successfully completed. Thank you!</p>
        <button class="btn btn-primary" onclick="returnToDashboard()" style="width: 100%;">
            <i class="bi bi-house-door"></i>
            Back to Dashboard
        </button>
    </div>
</div>

<script>
let scannedData = null;
let html5QrCode = null;

// Initialize QR Scanner
function initScanner() {
    html5QrCode = new Html5Qrcode("qr-reader");
    
    const config = {
        fps: 10,
        qrbox: { width: 200, height: 200 },
        aspectRatio: 1.0,
        videoConstraints: {
            facingMode: "environment"
        }
    };
    
    html5QrCode.start(
        { facingMode: "environment" },
        config,
        onScanSuccess,
        onScanError
    ).catch(err => {
        console.error("Unable to start scanner", err);
        showError("Camera access denied or not available");
    });
}

function onScanSuccess(decodedText, decodedResult) {
    // Stop scanning
    html5QrCode.stop();
    
    scannedData = decodedText;
    
    // Show success message
    document.getElementById('success-message').style.display = 'flex';
    document.getElementById('error-message').style.display = 'none';
    document.getElementById('complete-btn').disabled = false;
    
    // Haptic feedback if available
    if (navigator.vibrate) {
        navigator.vibrate(200);
    }
    
    console.log("QR Code scanned:", decodedText);
}

function onScanError(errorMessage) {
    // Ignore scanning errors (they happen continuously while scanning)
}

function showError(message) {
    document.getElementById('error-text').innerText = message;
    document.getElementById('error-message').style.display = 'flex';
    document.getElementById('success-message').style.display = 'none';
    
    setTimeout(() => {
        document.getElementById('error-message').style.display = 'none';
    }, 3000);
}

function manualEntry() {
    const code = prompt("Enter gallon QR code manually:");
    if (code && code.trim()) {
        scannedData = code.trim();
        document.getElementById('success-message').style.display = 'flex';
        document.getElementById('complete-btn').disabled = false;
        
        if (html5QrCode && html5QrCode.isScanning) {
            html5QrCode.stop();
        }
    }
}

function completeDelivery() {
    if (!scannedData) {
        showError("Please scan a QR code first");
        return;
    }
    
    // Here you would send the data to your backend
    // For now, we'll simulate a successful delivery
    
    document.getElementById('complete-btn').disabled = true;
    document.getElementById('complete-btn').innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';
    
    // Simulate API call
    setTimeout(() => {
        // Show success modal
        document.getElementById('success-modal').style.display = 'flex';
        
        // Send data to backend (uncomment and modify as needed)
        /*
        fetch('complete_delivery.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                user_id: <?= $user_id ?>,
                qr_code: scannedData,
                latitude: <?= $lat ?>,
                longitude: <?= $lng ?>
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('success-modal').style.display = 'flex';
            } else {
                showError(data.message || 'Failed to complete delivery');
            }
        })
        .catch(error => {
            showError('Network error. Please try again.');
        });
        */
    }, 1500);
}

function returnToDashboard() {
    window.location.href = 'rider_dashboard.php';
}

// Initialize scanner on page load
window.addEventListener('load', () => {
    initScanner();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (html5QrCode && html5QrCode.isScanning) {
        html5QrCode.stop();
    }
});
</script>

</body>
</html>