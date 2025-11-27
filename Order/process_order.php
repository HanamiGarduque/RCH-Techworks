<?php
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('admin');

require_once '../Database/db_connection.php';

$database = new Database();
$db = $database->getConnect();

$orderNumber = isset($_GET['order_number']) ? $_GET['order_number'] : '';

if (empty($orderNumber)) {
    header('Location: customer_orders.php');
    exit;
}

// Fetch order info
$orderQuery = "SELECT 
    r.refill_id,
    r.order_number,
    r.user_id,
    r.order_status,
    u.name as customer_name
FROM water_refills r
LEFT JOIN users u ON r.user_id = u.user_id
WHERE r.order_number = :order_number
LIMIT 1";

$stmt = $db->prepare($orderQuery);
$stmt->bindParam(':order_number', $orderNumber);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: customer_orders.php');
    exit;
}

// Fetch station-owned items
$stationQuery = "SELECT 
    ri.item_id,
    ri.product_id,
    ri.gallon_type,
    ri.gallon_size,
    ri.quantity,
    ri.gallon_owner,
    ri.processed_quantity,
    i.item_name
FROM water_refill_items ri
LEFT JOIN inventory i ON ri.product_id = i.item_id
WHERE ri.refill_id = :refill_id AND ri.gallon_owner = 'station'";

$stationStmt = $db->prepare($stationQuery);
$stationStmt->bindParam(':refill_id', $order['refill_id']);
$stationStmt->execute();
$stationItems = $stationStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch customer-owned items
$customerQuery = "SELECT 
    ri.item_id,
    ri.product_id,
    ri.gallon_type,
    ri.gallon_size,
    ri.quantity,
    ri.gallon_owner,
    ri.processed_quantity,
    i.item_name
FROM water_refill_items ri
LEFT JOIN inventory i ON ri.product_id = i.item_id
WHERE ri.refill_id = :refill_id AND ri.gallon_owner = 'customer'";

$custStmt = $db->prepare($customerQuery);
$custStmt->bindParam(':refill_id', $order['refill_id']);
$custStmt->execute();
$customerItems = $custStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate initial processed count
$totalProcessed = 0;
foreach ($stationItems as $item) {
    $totalProcessed += ($item['processed_quantity'] ?? 0);
}
foreach ($customerItems as $item) {
    $totalProcessed += ($item['processed_quantity'] ?? 0);
}

// Total gallons
$totalGallons =
    array_sum(array_column($stationItems, 'quantity')) +
    array_sum(array_column($customerItems, 'quantity'));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Order - RCH Water</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .page-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .content-wrapper {
            flex: 1;
            overflow-y: auto;
            padding: 2rem;
        }

        .full-height-grid {
            height: 100%;
            min-height: 0;
        }
    </style>

<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <script src="../Admin/includes/sidebar.js"></script>
        <div class="flex-1 ml-20 group-hover:ml-64 transition-all duration-300 ease-in-out overflow-hidden">
            <div class="page-container">
                <?php
                $pageTitle = "Order Management";
                $pageSubtitle = "View and manage customer orders and details.";
                include '../Admin/includes/header.php';
                ?>

                <div class="flex-1 overflow-y-auto p-6 full-height-grid">
                    <div class="flex items-center gap-3 mb-6">
                        <button onclick="window.location.href='order_details.php?order_number=<?= urlencode($orderNumber) ?>'"
                            class="flex items-center text-blue-600 hover:text-blue-700 text-2xl transition-colors">
                            <i class="bi bi-arrow-left-circle-fill"></i>
                        </button>
                        <h1 class="text-2xl text-gray-700 font-semibold">
                            Process Order - <?= htmlspecialchars($order['order_number']) ?>
                        </h1>
                    </div>

                    <div class="grid grid-cols-4 gap-6 h-[calc(100%-100px)]">
                        <div class="col-span-4 lg:col-span-2 row-span-4 bg-white rounded-xl shadow-sm border border-gray-200 p-8 flex flex-col">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-qrcode text-blue-600 text-xl"></i>
                                </div>
                                <h2 class="text-xl font-bold text-gray-800">QR Scanner</h2>
                            </div>

                            <div class="relative mx-auto rounded-xl overflow-hidden border-4 border-gray-300 mb-6 shadow-inner flex-1 w-full max-w-lg"
                                style="max-height: calc(100% - 150px);">
                                <div id="qr-reader" style="width: 100%; height: 100%;"></div>
                            </div>

                            <div id="scan-status" class="p-4 bg-blue-50 border-2 border-blue-200 rounded-lg text-blue-700 font-medium text-center mt-auto">
                                <i class="fas fa-info-circle mr-2"></i>Ready to scan
                            </div>
                        </div>

                        <div class="col-span-4 lg:col-span-2 row-span-4 flex flex-col gap-6">
                            <div class="col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-chart-line text-blue-600 text-lg"></i>
                                        <span class="font-semibold text-gray-700">Overall Progress</span>
                                    </div>
                                    <span id="progress-text" class="text-lg font-bold text-blue-600"><?= $totalProcessed ?> / <?= $totalGallons ?></span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3">
                                    <div id="progress-bar" class="bg-blue-600 h-3 rounded-full transition-all duration-500"
                                        style="width: <?= ($totalGallons > 0 ? ($totalProcessed / $totalGallons) * 100 : 0) ?>%"></div>
                                </div>
                            </div>

                            <div id="completion-message" class="hidden col-span-2">
                                <div class="bg-green-50 border-2 border-green-300 text-green-800 p-5 rounded-xl text-center font-semibold flex items-center justify-center gap-3">
                                    <i class="fas fa-check-circle text-2xl"></i>
                                    <span>All gallons have been scanned successfully! Redirecting...</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-6 flex-1 min-h-0">
                                <div class="col-span-1 bg-white rounded-xl shadow-sm border border-gray-200 p-8 flex flex-col">
                                    <div class="flex items-center gap-3 mb-6">
                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-warehouse text-blue-600 text-xl"></i>
                                        </div>
                                        <h2 class="text-xl font-bold text-gray-800">Station-Owned Gallons</h2>
                                    </div>

                                    <?php if (count($stationItems) > 0): ?>
                                        <!-- Scrollable container -->
                                        <div class="flex-1 overflow-y-auto max-h-[400px] pr-2">
                                            <div id="station-checklist" class="space-y-4">
                                                <?php foreach ($stationItems as $index => $item): ?>
                                                    <div id="station-item-<?= $index ?>"
                                                        class="p-4 border-2 border-gray-200 rounded-lg bg-gray-50 transition-all hover:shadow-md"
                                                        data-owner="station"
                                                        data-item-id="<?= $item['item_id'] ?>"
                                                        data-quantity="<?= $item['quantity'] ?>"
                                                        data-processed="<?= $item['processed_quantity'] ?? 0 ?>">

                                                        <div class="flex items-start gap-4">
                                                            <i id="station-icon-<?= $index ?>" class="fas fa-clock text-gray-400 text-2xl mt-1"></i>

                                                            <div class="flex-1">
                                                                <div class="font-semibold text-gray-800 text-base mb-2">
                                                                    <?= htmlspecialchars($item['item_name']) ?>
                                                                </div>

                                                                <div class="text-sm text-gray-600 mb-3">
                                                                    <span id="station-progress-<?= $index ?>" class="font-bold text-blue-600">
                                                                        <?= $item['processed_quantity'] ?? 0 ?> / <?= $item['quantity'] ?>
                                                                    </span>
                                                                    <span class="text-gray-500">scanned</span>
                                                                </div>

                                                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                                                    <div id="station-bar-<?= $index ?>"
                                                                        class="bg-blue-600 h-2.5 rounded-full transition-all duration-500"
                                                                        style="width: <?= (($item['processed_quantity'] ?? 0) / $item['quantity']) * 100 ?>%">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-12 text-gray-400">
                                            <i class="fas fa-inbox text-5xl mb-3"></i>
                                            <p class="text-sm">No station-owned gallons</p>
                                        </div>
                                    <?php endif; ?>

                                </div>


                                <?php if (count($customerItems) > 0): ?>
                                    <?php if (count($customerItems) > 0): ?>


                                        <div class="col-span-1 bg-white rounded-xl shadow-sm border border-gray-200 p-8 flex flex-col">
                                            <div class="flex items-center gap-3 mb-6">
                                                <div class="w-10 h-10 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-user text-purple-600 text-xl"></i>
                                                </div>
                                                <h2 class="text-xl font-bold text-gray-800">Customer-Owned Gallons</h2>
                                            </div>

                                            <div class="flex items-center gap-3 mb-4">
                                                <input type="checkbox" id="check-all-customer" class="w-5 h-5">
                                                <label for="check-all-customer" class="font-semibold text-gray-700">Check All Gallons</label>
                                            </div>

                                            <!-- Scrollable container -->
                                            <div class="flex-1 overflow-y-auto max-h-[300px] pr-2">
                                                <div id="customer-checklist" class="space-y-3">
                                                    <?php
                                                    $gallonIndex = 0;
                                                    foreach ($customerItems as $cIndex => $item):
                                                        for ($i = 0; $i < $item['quantity']; $i++):
                                                    ?>
                                                            <div class="flex items-center gap-3 p-3 border-2 border-purple-200 rounded-lg bg-purple-50 hover:shadow-md transition-all customer-gallon-item"
                                                                data-item-name="<?= htmlspecialchars($item['item_name']) ?>"
                                                                data-gallon-index="<?= $gallonIndex ?>">
                                                                <input type="checkbox"
                                                                    class="w-5 h-5 customer-gallon-check flex-shrink-0"
                                                                    id="customer-gallon-<?= $gallonIndex ?>"
                                                                    data-gallon-index="<?= $gallonIndex ?>">
                                                                <label for="customer-gallon-<?= $gallonIndex ?>"
                                                                    class="font-medium text-gray-800 cursor-pointer flex-1">
                                                                    <?= htmlspecialchars($item['item_name']) ?>
                                                                </label>
                                                                <i class="fas fa-circle text-purple-400 text-xs flex-shrink-0 gallon-status-icon"
                                                                    id="gallon-icon-<?= $gallonIndex ?>"></i>
                                                            </div>
                                                    <?php
                                                            $gallonIndex++;
                                                        endfor;
                                                    endforeach;
                                                    ?>
                                                </div>
                                            </div>
                                        </div>


                                    <?php endif; ?>

                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let html5QrcodeScanner;
        const stationItems = <?= json_encode(array_values($stationItems)) ?>;
        const customerItems = <?= json_encode(array_values($customerItems)) ?>;
        const totalGallons = <?= $totalGallons ?>;
        let totalScanned = <?= $totalProcessed ?>;
        const userId = <?= $order['user_id'] ?>;
        const orderNumber = '<?= $orderNumber ?>';

        window.addEventListener('load', () => {
            startQRScanner();

            // Individual gallon checkbox handler
            document.querySelectorAll(".customer-gallon-check").forEach(cb => {
                cb.addEventListener("change", function() {
                    const gallonIndex = parseInt(this.dataset.gallonIndex);
                    const iconEl = document.getElementById(`gallon-icon-${gallonIndex}`);
                    const itemDiv = this.closest('.customer-gallon-item');

                    if (this.checked) {
                        iconEl.className = 'fas fa-check-circle text-green-500 text-sm flex-shrink-0 gallon-status-icon';
                        itemDiv.classList.remove('bg-purple-50', 'border-purple-200');
                        itemDiv.classList.add('bg-green-50', 'border-green-300');
                    } else {
                        iconEl.className = 'fas fa-circle text-purple-400 text-xs flex-shrink-0 gallon-status-icon';
                        itemDiv.classList.remove('bg-green-50', 'border-green-300');
                        itemDiv.classList.add('bg-purple-50', 'border-purple-200');
                    }

                    updateOverallProgressFromCheckboxes();
                });
            });

            // "Check All" button
            document.getElementById("check-all-customer")?.addEventListener("change", function() {
                document.querySelectorAll(".customer-gallon-check").forEach(cb => {
                    cb.checked = this.checked;
                    cb.dispatchEvent(new Event('change'));
                });
            });
        });

        function updateOverallProgressFromCheckboxes() {
            const stationScanned = stationItems.reduce((sum, item) => sum + (item.processed_quantity ?? 0), 0);
            const customerChecked = document.querySelectorAll(".customer-gallon-check:checked").length;
            totalScanned = stationScanned + customerChecked;

            updateOverallProgress();

            if (totalScanned >= totalGallons) {
                completeProcessing();
            }
        }

        function startQRScanner() {
            html5QrcodeScanner = new Html5Qrcode("qr-reader");

            html5QrcodeScanner.start({
                    facingMode: "environment"
                }, {
                    fps: 10,
                    qrbox: {
                        width: 250,
                        height: 250
                    }
                },
                qrCodeMessage => handleQRScan(qrCodeMessage),
                errorMessage => {
                    /* Scanning */
                }
            ).catch(err => {
                console.error("QR Scanner error:", err);
                updateStatus("Camera access denied or unavailable", "error");
            });
        }

        function stopQRScanner() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.stop().catch(err => console.error(err));
            }
        }

        function handleQRScan(qrCode) {
            updateStatus(`Processing: ${qrCode}...`, "info");

            fetch('process_gallon_scan.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        qr_code: qrCode,
                        order_number: orderNumber,
                        user_id: userId
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        let itemIndex = stationItems.findIndex(item => item.item_name === data.item_name);
                        let isStation = itemIndex !== -1;

                        if (itemIndex !== -1) {
                            totalScanned++;
                            updateItemUI(itemIndex, data.processed, data.total, data.item_complete, isStation);
                            updateOverallProgress();
                            updateStatus(`✓ ${data.item_name} (${data.processed}/${data.total})`, "success");

                            if (totalScanned >= totalGallons) {
                                completeProcessing();
                            }
                        } else {
                            updateStatus('⚠ Item not found in this order', "warning");
                        }
                    } else {
                        updateStatus(`✗ ${data.message}`, "error");
                    }
                })
                .catch(err => {
                    console.error(err);
                    updateStatus('✗ Processing failed', "error");
                });
        }

        function updateItemUI(index, processed, total, isComplete, isStation) {
            const prefix = 'station';
            const itemEl = document.getElementById(`${prefix}-item-${index}`);
            const iconEl = document.getElementById(`${prefix}-icon-${index}`);
            const progressEl = document.getElementById(`${prefix}-progress-${index}`);
            const barEl = document.getElementById(`${prefix}-bar-${index}`);

            progressEl.textContent = `${processed} / ${total}`;
            barEl.style.width = ((processed / total) * 100) + '%';

            if (isComplete) {
                iconEl.className = `fas fa-check-circle text-green-500 text-2xl mt-1`;
                itemEl.classList.remove('bg-gray-50');
                itemEl.classList.add('bg-green-50', 'border-green-300');
                barEl.classList.remove('bg-blue-600');
                barEl.classList.add('bg-green-600');
            }
        }

        function updateOverallProgress() {
            const percentage = (totalScanned / totalGallons) * 100;
            document.getElementById('progress-bar').style.width = percentage + '%';
            document.getElementById('progress-text').textContent = `${totalScanned} / ${totalGallons}`;
        }

        function completeProcessing() {
            document.getElementById('completion-message').classList.remove('hidden');
            stopQRScanner();
            setTimeout(() => {
                window.location.href = 'customer_orders.php';
            }, 2500);
        }

        function updateStatus(message, type = 'info') {
            const el = document.getElementById('scan-status');
            el.textContent = message;
            el.className = `p-4 rounded-lg font-medium text-center ${
            type === 'success' ? 'bg-green-50 border-2 border-green-300 text-green-700' :
            type === 'error' ? 'bg-red-50 border-2 border-red-300 text-red-700' :
            type === 'warning' ? 'bg-yellow-50 border-2 border-yellow-300 text-yellow-700' :
            'bg-blue-50 border-2 border-blue-200 text-blue-700'
        }`;
        }
    </script>
</body>

</html>