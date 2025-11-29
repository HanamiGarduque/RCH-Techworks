<?php
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('admin'); // only admins can access
require_once '../Database/db_connection.php';

$database = new Database();
$db = $database->getConnect();

// Handle gallon return/scan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'scan_return') {
        $codeValue = trim($_POST['code_value']);
        
        try {
            // Find the gallon by code_value
            $query = "SELECT ownership_id, item_id, gallon_type, current_owner_id, status, code_value
                      FROM gallon_ownership 
                      WHERE code_value = :code_value";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':code_value', $codeValue);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                echo json_encode(['success' => false, 'error' => 'Gallon not found']);
                exit();
            }
            
            $gallon = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if already available/returned
            if ($gallon['status'] === 'Available') {
                echo json_encode(['success' => false, 'error' => 'Gallon is already in the station']);
                exit();
            }
            
            // Get admin user ID
            $adminQuery = "SELECT user_id FROM users WHERE role = 'admin' LIMIT 1";
            $adminStmt = $db->prepare($adminQuery);
            $adminStmt->execute();
            $admin = $adminStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$admin) {
                echo json_encode(['success' => false, 'error' => 'No admin user found']);
                exit();
            }
            
            $previousOwnerId = $gallon['current_owner_id'];
            $adminId = $admin['user_id'];
            
            // Update gallon ownership
            $updateQuery = "UPDATE gallon_ownership 
                           SET current_owner_id = :admin_id, 
                               status = 'Available' 
                           WHERE ownership_id = :ownership_id";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->bindParam(':admin_id', $adminId);
            $updateStmt->bindParam(':ownership_id', $gallon['ownership_id']);
            
            if ($updateStmt->execute()) {
                // Log the activity
                $logQuery = "INSERT INTO gallon_activity_logs 
                            (ownership_id, action_type, performed_by, previous_owner_id, new_owner_id, previous_status, new_status, notes) 
                            VALUES 
                            (:ownership_id, 'returned', :performed_by, :previous_owner, :new_owner, :prev_status, 'Available', 'Gallon scanned and returned to station')";
                $logStmt = $db->prepare($logQuery);
                $logStmt->bindParam(':ownership_id', $gallon['ownership_id']);
                $logStmt->bindParam(':performed_by', $_SESSION['user_id']);
                $logStmt->bindParam(':previous_owner', $previousOwnerId);
                $logStmt->bindParam(':new_owner', $adminId);
                $logStmt->bindParam(':prev_status', $gallon['status']);
                $logStmt->execute();
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Gallon returned successfully',
                    'gallon_info' => [
                        'item_id' => $gallon['item_id'],
                        'type' => $gallon['gallon_type'],
                        'code' => $gallon['code_value'],
                        'time' => date('h:i A'),
                        'previous_owner' => $previousOwnerId
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to update gallon status']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RCH Water - Scan Gallon Return</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            scrollbar-gutter: stable;
        }
        #reader {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
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
        .new-row {
            animation: slideIn 0.3s ease-out;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <script src="../Admin/includes/sidebar.js"></script>
        
        <!-- Main Content -->
        <div class="flex-1 ml-20 group-hover:ml-64 transition-all duration-300 ease-in-out overflow-hidden">
            <div class="h-screen flex flex-col">
                <!-- Header -->
                <?php
                $pageTitle = "Scan Gallon Return";
                $pageSubtitle = "Scan returned gallons to update inventory";
                include '../Admin/includes/header.php';
                ?>
                
                <!-- Content -->
                <div class="flex-1 overflow-y-auto p-6">
                    <div class="mx-auto">
                        <!-- Scanner Card -->
                        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                            <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-qrcode mr-3 text-blue-600"></i>
                                QR Code Scanner
                            </h2>
                            
                            <!-- Scanner Toggle -->
                            <div class="mb-4">
                                <button id="startScanBtn" onclick="startScanner()" 
                                    class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-camera mr-2"></i>Start Camera Scanner
                                </button>
                                <button id="stopScanBtn" onclick="stopScanner()" 
                                    class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition-colors hidden">
                                    <i class="fas fa-stop mr-2"></i>Stop Scanner
                                </button>
                            </div>
                            
                            <!-- QR Reader -->
                            <div id="reader" class="mb-4 hidden"></div>
                            
                            <!-- Manual Input -->
                            <div class="mt-6">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Or Enter Code Manually:
                                </label>
                                <div class="flex gap-2">
                                    <input type="text" id="manualCode" 
                                        placeholder="Enter gallon code..." 
                                        class="flex-1 px-4 py-2 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <button onclick="processManualCode()" 
                                        class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors">
                                        <i class="fas fa-check mr-2"></i>Submit
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Recent Scans -->
                        <div class="bg-white rounded-lg shadow-lg p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">
                                <i class="fas fa-history mr-2 text-blue-600"></i>
                                Recent Returns (Today)
                            </h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-blue-50 border-b">
                                        <tr>
                                            <th class="px-4 py-3 text-left font-semibold">Item ID</th>
                                            <th class="px-4 py-3 text-left font-semibold">Type</th>
                                            <th class="px-4 py-3 text-left font-semibold">Code</th>
                                            <th class="px-4 py-3 text-left font-semibold">Return Time</th>
                                            <th class="px-4 py-3 text-left font-semibold">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recentScans">
                                        <?php
                                        $recentQuery = "SELECT gal.item_id, gal.gallon_type, gal.code_value, 
                                                       gal.status, log.created_at
                                                FROM gallon_ownership gal
                                                JOIN gallon_activity_logs log ON gal.ownership_id = log.ownership_id
                                                WHERE log.action_type = 'returned' 
                                                AND DATE(log.created_at) = CURDATE()
                                                ORDER BY log.created_at DESC
                                                LIMIT 10";
                                        $recentStmt = $db->prepare($recentQuery);
                                        $recentStmt->execute();
                                        
                                        if ($recentStmt->rowCount() > 0) {
                                            while ($row = $recentStmt->fetch(PDO::FETCH_ASSOC)) {
                                                echo "<tr class='border-b hover:bg-gray-50'>";
                                                echo "<td class='px-4 py-3'>" . htmlspecialchars($row['item_id']) . "</td>";
                                                echo "<td class='px-4 py-3'>" . htmlspecialchars($row['gallon_type']) . "</td>";
                                                echo "<td class='px-4 py-3'><code class='bg-gray-100 px-2 py-1 rounded'>" . htmlspecialchars($row['code_value']) . "</code></td>";
                                                echo "<td class='px-4 py-3'>" . date('h:i A', strtotime($row['created_at'])) . "</td>";
                                                echo "<td class='px-4 py-3'><span class='bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold'>" . htmlspecialchars($row['status']) . "</span></td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr id='noDataRow'><td colspan='5' class='px-4 py-3 text-center text-gray-500'>No returns today</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let html5QrCode;
        let isScanning = false;
        let processingCode = false;
        
        function startScanner() {
            document.getElementById('reader').classList.remove('hidden');
            document.getElementById('startScanBtn').classList.add('hidden');
            document.getElementById('stopScanBtn').classList.remove('hidden');
            
            html5QrCode = new Html5Qrcode("reader");
            html5QrCode.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: 250 },
                onScanSuccess,
                onScanFailure
            ).then(() => {
                isScanning = true;
            }).catch(err => {
                Swal.fire('Error', 'Unable to start camera: ' + err, 'error');
                resetScannerUI();
            });
        }
        
        function stopScanner() {
            if (html5QrCode && isScanning) {
                html5QrCode.stop().then(() => {
                    isScanning = false;
                    html5QrCode.clear();
                    resetScannerUI();
                }).catch(err => {
                    console.error('Error stopping scanner:', err);
                    resetScannerUI();
                });
            }
        }
        
        function resetScannerUI() {
            document.getElementById('reader').classList.add('hidden');
            document.getElementById('startScanBtn').classList.remove('hidden');
            document.getElementById('stopScanBtn').classList.add('hidden');
        }
        
        function onScanSuccess(decodedText, decodedResult) {
            if (!processingCode) {
                processingCode = true;
                processCode(decodedText);
            }
        }
        
        function onScanFailure(error) {
            // Silent - scanning errors are normal
        }
        
        function processManualCode() {
            const code = document.getElementById('manualCode').value.trim();
            if (!code) {
                Swal.fire('Error', 'Please enter a code', 'error');
                return;
            }
            processCode(code);
        }
        
        function processCode(code) {
            const formData = new FormData();
            formData.append('action', 'scan_return');
            formData.append('code_value', code);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Add new row to table
                    addNewScanRow(data.gallon_info);
                    
                    // Show subtle success notification
                    showToast('Success', 'Gallon returned successfully', 'success');
                    
                    // Clear manual input
                    document.getElementById('manualCode').value = '';
                } else {
                    Swal.fire('Error', data.error, 'error');
                }
                
                // Reset processing flag after a short delay
                setTimeout(() => {
                    processingCode = false;
                }, 1000);
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'An error occurred', 'error');
                processingCode = false;
            });
        }
        
        function addNewScanRow(gallonInfo) {
            const tbody = document.getElementById('recentScans');
            const noDataRow = document.getElementById('noDataRow');
            
            // Remove "no data" row if exists
            if (noDataRow) {
                noDataRow.remove();
            }
            
            // Create new row
            const newRow = document.createElement('tr');
            newRow.className = 'border-b hover:bg-gray-50 new-row';
            newRow.innerHTML = `
                <td class='px-4 py-3'>${gallonInfo.item_id}</td>
                <td class='px-4 py-3'>${gallonInfo.type}</td>
                <td class='px-4 py-3'><code class='bg-gray-100 px-2 py-1 rounded'>${gallonInfo.code}</code></td>
                <td class='px-4 py-3'>${gallonInfo.time}</td>
                <td class='px-4 py-3'><span class='bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold'>Available</span></td>
            `;
            
            // Insert at the top
            tbody.insertBefore(newRow, tbody.firstChild);
            
            // Keep only last 10 rows
            while (tbody.children.length > 10) {
                tbody.removeChild(tbody.lastChild);
            }
        }
        
        function showToast(title, message, icon) {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
            
            Toast.fire({
                icon: icon,
                title: message
            });
        }
        
        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (html5QrCode && isScanning) {
                html5QrCode.stop();
            }
        });
    </script>
</body>
</html>