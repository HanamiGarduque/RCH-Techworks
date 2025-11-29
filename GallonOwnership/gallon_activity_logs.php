<?php
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('admin'); // only admins can access
require_once '../Database/db_connection.php';

$database = new Database();
$db = $database->getConnect();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RCH Water - Gallon Activity Logs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
            scrollbar-gutter: stable;
        }
        .page-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .content-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .table-scroll-container {
            overflow-y: auto;
            overflow-x: auto;
            flex: 1;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <script src="../Admin/includes/sidebar.js"></script>
        
        <!-- Main Content -->
        <div class="flex-1 ml-20 group-hover:ml-64 transition-all duration-300 ease-in-out overflow-hidden">
            <div class="page-container">
                <!-- Header -->
                <?php
                $pageTitle = "Gallon Activity Logs";
                $pageSubtitle = "Track all gallon movements and status changes";
                include '../Admin/includes/header.php';
                ?>
                
                <!-- Content Wrapper -->
                <div class="content-wrapper p-3">
                    <!-- Search and Filters -->
                    <div class="flex items-center gap-2 mb-4 flex-shrink-0 flex-wrap">
                        <div class="flex-1 relative min-w-[200px]">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            <input type="text" id="searchInput" 
                                placeholder="Search by Item ID, Code, Action..." 
                                class="w-full pl-10 pr-4 py-2 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                oninput="searchTable()">
                        </div>
                        <select id="actionFilter" onchange="filterByAction()" 
                            class="px-4 py-2 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="all">All Actions</option>
                            <option value="returned">Returned</option>
                            <option value="assigned">Assigned</option>
                            <option value="status_change">Status Change</option>
                        </select>
                        <input type="date" id="dateFilter" onchange="filterByDate()" 
                            class="px-4 py-2 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <!-- Content Area -->
                    <div class="p-6 bg-white rounded-lg shadow-lg ring-3 ring-gray-200 flex flex-col flex-1 overflow-hidden">
                        <!-- Summary Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 flex-shrink-0">
                            <?php
                            // Get today's statistics
                            $todayQuery = "SELECT 
                                COUNT(*) as total_today,
                                SUM(CASE WHEN action_type = 'returned' THEN 1 ELSE 0 END) as returned_today,
                                SUM(CASE WHEN action_type = 'assigned' THEN 1 ELSE 0 END) as assigned_today,
                                SUM(CASE WHEN action_type = 'status_change' THEN 1 ELSE 0 END) as status_changes_today
                                FROM gallon_activity_logs 
                                WHERE DATE(created_at) = CURDATE()";
                            $todayStmt = $db->prepare($todayQuery);
                            $todayStmt->execute();
                            $stats = $todayStmt->fetch(PDO::FETCH_ASSOC);
                            ?>
                            
                            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-blue-600 font-semibold">Today's Activities</p>
                                        <p class="text-2xl font-bold text-blue-700"><?php echo $stats['total_today']; ?></p>
                                    </div>
                                    <i class="fas fa-chart-line text-3xl text-blue-600"></i>
                                </div>
                            </div>
                            
                            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-green-600 font-semibold">Returns Today</p>
                                        <p class="text-2xl font-bold text-green-700"><?php echo $stats['returned_today']; ?></p>
                                    </div>
                                    <i class="fas fa-undo text-3xl text-green-600"></i>
                                </div>
                            </div>
                            
                            <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-purple-600 font-semibold">Assigned Today</p>
                                        <p class="text-2xl font-bold text-purple-700"><?php echo $stats['assigned_today']; ?></p>
                                    </div>
                                    <i class="fas fa-user-check text-3xl text-purple-600"></i>
                                </div>
                            </div>
                            
                            <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-orange-600 font-semibold">Status Changes</p>
                                        <p class="text-2xl font-bold text-orange-700"><?php echo $stats['status_changes_today']; ?></p>
                                    </div>
                                    <i class="fas fa-exchange-alt text-3xl text-orange-600"></i>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Table with Scrollable Body -->
                        <div class="pb-6 flex flex-col overflow-hidden flex-1">
                            <div class="bg-white rounded-lg shadow-md overflow-hidden flex flex-col flex-1">
                                <div class="table-scroll-container">
                                    <table id="logsTable" class="min-w-full text-sm text-left text-gray-700">
                                        <thead class="bg-blue-50 border-b border-gray-200 sticky top-0 z-10">
                                            <tr>
                                                <th class="px-4 py-3 font-semibold text-center min-w-[80px]">Log ID</th>
                                                <th class="px-4 py-3 font-semibold text-center min-w-[100px]">Item ID</th>
                                                <th class="px-4 py-3 font-semibold text-center min-w-[120px]">Gallon Code</th>
                                                <th class="px-4 py-3 font-semibold text-center min-w-[100px]">Type</th>
                                                <th class="px-4 py-3 font-semibold text-center min-w-[120px]">Action</th>
                                                <th class="px-4 py-3 font-semibold text-center min-w-[120px]">Previous Owner</th>
                                                <th class="px-4 py-3 font-semibold text-center min-w-[120px]">New Owner</th>
                                                <th class="px-4 py-3 font-semibold text-center min-w-[120px]">Status Change</th>
                                                <th class="px-4 py-3 font-semibold text-center min-w-[130px]">Performed By</th>
                                                <th class="px-4 py-3 font-semibold text-center min-w-[200px]">Notes</th>
                                                <th class="px-4 py-3 font-semibold text-center min-w-[150px]">Date & Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $query = "SELECT 
                                                log.log_id,
                                                log.action_type,
                                                log.previous_status,
                                                log.new_status,
                                                log.notes,
                                                log.created_at,
                                                gal.item_id,
                                                gal.gallon_type,
                                                gal.code_value,
                                                prev_user.name as prev_owner_name,
                                                new_user.name as new_owner_name,
                                                performer.name as performed_by_name
                                            FROM gallon_activity_logs log
                                            JOIN gallon_ownership gal ON log.ownership_id = gal.ownership_id
                                            LEFT JOIN users prev_user ON log.previous_owner_id = prev_user.user_id
                                            LEFT JOIN users new_user ON log.new_owner_id = new_user.user_id
                                            LEFT JOIN users performer ON log.performed_by = performer.user_id
                                            ORDER BY log.created_at DESC
                                            LIMIT 500";
                                            
                                            $stmt = $db->prepare($query);
                                            $stmt->execute();
                                            
                                            if ($stmt->rowCount() > 0) {
                                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                    $actionClass = [
                                                        'returned' => 'bg-green-100 text-green-700',
                                                        'assigned' => 'bg-blue-100 text-blue-700',
                                                        'status_change' => 'bg-orange-100 text-orange-700'
                                                    ][$row['action_type']] ?? 'bg-gray-100 text-gray-700';
                                                    
                                                    echo "<tr class='border-b hover:bg-gray-50' data-action='{$row['action_type']}' data-date='" . date('Y-m-d', strtotime($row['created_at'])) . "'>";
                                                    echo "<td class='px-4 py-3 text-center'>" . htmlspecialchars($row['log_id']) . "</td>";
                                                    echo "<td class='px-4 py-3 text-center font-medium'>" . htmlspecialchars($row['item_id']) . "</td>";
                                                    echo "<td class='px-4 py-3 text-center'><code class='bg-gray-100 px-2 py-1 rounded text-xs'>" . htmlspecialchars($row['code_value']) . "</code></td>";
                                                    echo "<td class='px-4 py-3 text-center'>" . htmlspecialchars($row['gallon_type']) . "</td>";
                                                    echo "<td class='px-4 py-3 text-center'><span class='inline-block px-3 py-1 rounded-full text-xs font-semibold {$actionClass}'>" . ucfirst(str_replace('_', ' ', $row['action_type'])) . "</span></td>";
                                                    echo "<td class='px-4 py-3 text-center'>" . ($row['prev_owner_name'] ?? 'N/A') . "</td>";
                                                    echo "<td class='px-4 py-3 text-center'>" . ($row['new_owner_name'] ?? 'N/A') . "</td>";
                                                    echo "<td class='px-4 py-3 text-center text-xs'>" . htmlspecialchars($row['previous_status']) . " → " . htmlspecialchars($row['new_status']) . "</td>";
                                                    echo "<td class='px-4 py-3 text-center'>" . ($row['performed_by_name'] ?? 'System') . "</td>";
                                                    echo "<td class='px-4 py-3'>" . htmlspecialchars($row['notes']) . "</td>";
                                                    echo "<td class='px-4 py-3 text-center text-xs'>" . date('M d, Y h:i A', strtotime($row['created_at'])) . "</td>";
                                                    echo "</tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='11' class='px-6 py-4 text-center text-gray-500'>No activity logs found</td></tr>";
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
    </div>

    <script>
        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const rows = document.querySelectorAll('#logsTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        }
        
        function filterByAction() {
            const action = document.getElementById('actionFilter').value;
            const rows = document.querySelectorAll('#logsTable tbody tr');
            
            rows.forEach(row => {
                if (action === 'all' || row.dataset.action === action) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        function filterByDate() {
            const date = document.getElementById('dateFilter').value;
            const rows = document.querySelectorAll('#logsTable tbody tr');
            
            if (!date) {
                rows.forEach(row => row.style.display = '');
                return;
            }
            
            rows.forEach(row => {
                if (row.dataset.date === date) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>