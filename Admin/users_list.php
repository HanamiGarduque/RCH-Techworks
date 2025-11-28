<?php
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('admin'); // only admins can access
require_once '../Database/db_connection.php';

$database = new Database();
$db = $database->getConnect();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    if ($_POST['action'] === 'ban') {
        $userId = intval($_POST['user_id']);
        $newStatus = $_POST['status'] === 'active' ? 'banned' : 'active';

        $query = "UPDATE users SET status = :status WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':status', $newStatus);
        $stmt->bindParam(':user_id', $userId);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'newStatus' => $newStatus]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update status']);
        }
        exit();
    }
    if ($_POST['action'] === 'change_role') {
        $userId = intval($_POST['user_id']);
        $newRole = $_POST['role'];

        // Validate allowed roles
        $allowedRoles = ['customer', 'admin', 'rider'];
        if (!in_array($newRole, $allowedRoles)) {
            echo json_encode(['success' => false, 'error' => 'Invalid role']);
            exit();
        }

        $query = "UPDATE users SET role = :role WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':role', $newRole);
        $stmt->bindParam(':user_id', $userId);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'newRole' => $newRole]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update role']);
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
    <title>RCH Water - Users List</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
        }

        body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown) {
            overflow: hidden !important;
            padding-right: 0 !important;
        }

        /* Prevent height changes */
        body.swal2-height-auto {
            height: auto !important;
        }

        /* Use scrollbar-gutter to reserve space without showing scrollbar */
        body {
            scrollbar-gutter: stable;
        }


        /* Ensure the page layout uses full viewport height */
        .page-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Main content area that will scroll */
        .content-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Table container with scroll */
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
        <<div class="flex-1 ml-20 group-hover:ml-64 transition-all duration-300 ease-in-out overflow-hidden">
            <div class="page-container">
                <!-- Header -->
                <?php
                $pageTitle = "Manage System Users";
                $pageSubtitle = "Manage all registered users in the system";
                include '../Admin/includes/header.php';
                ?>

                <!-- Content Wrapper -->
                <div class="content-wrapper p-3">
                    <!-- Search and Buttons - Fixed -->
                    <div class="flex items-center gap-2 mb-4 flex-shrink-0">
                        <div class="flex-1 relative">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            <input
                                type="text"
                                id="searchInput"
                                placeholder="Search by Name, Email, Phone, Address..."
                                class="w-full pl-10 pr-4 py-2 font-light border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                oninput="searchTable()">
                        </div>

                    </div>
                    <!-- Content Area -->
                    <div class="p-6 bg-white rounded-lg shadow-lg ring-3 ring-gray-200 flex flex-col flex-1 overflow-hidden">
                        <!-- Updated filter tabs for users: All, Customers, Riders, Banned -->
                        <div class="flex gap-3 mb-6 flex-wrap flex-shrink-0">
                            <button class="filter-btn px-4 py-2 rounded-full border-none cursor-pointer font-semibold transition-all duration-300 bg-blue-600 text-white"
                                onclick="filterTab(this)">
                                <i class="fas fa-list mr-2"></i>All
                            </button>
                            <button class="filter-btn px-4 py-2 rounded-full border-none cursor-pointer font-semibold transition-all duration-300 bg-blue-50 text-blue-500 hover:bg-gray-200"
                                onclick="filterTab(this)">
                                <i class="fas fa-shopping-cart mr-2"></i>Customers
                            </button>
                            <button class="filter-btn px-4 py-2 rounded-full border-none cursor-pointer font-semibold transition-all duration-300 bg-blue-50 text-blue-500 hover:bg-gray-200"
                                onclick="filterTab(this)">
                                <i class="fas fa-motorcycle mr-2"></i>Riders
                            </button>
                            <button class="filter-btn px-4 py-2 rounded-full border-none cursor-pointer font-semibold transition-all duration-300 bg-blue-50 text-blue-500 hover:bg-gray-200"
                                onclick="filterTab(this)">
                                <i class="fas fa-ban mr-2"></i>Banned
                            </button>
                        </div>


                        <!-- Table with Scrollable Body -->
                        <div class="pb-6 flex flex-col overflow-hidden flex-1">
                            <div class="bg-white rounded-lg shadow-md overflow-hidden flex flex-col flex-1">
                                <!-- Scrollable table container -->
                                <div class="table-scroll-container">

                                    <table id="usersTable" class="min-w-full text-sm text-left text-gray-700">
                                        <thead class="bg-blue-50 border-b border-gray-200 sticky top-0 z-10">
                                            <tr>
                                                <th class="px-4 py-3 font-semibold text-center cursor-pointer min-w-[100px]" onclick="sortTable(0)" title="User ID">User ID ⬍</th>
                                                <th class="px-4 py-3 font-semibold text-center cursor-pointer min-w-[180px]" onclick="sortTable(1)" title="Name">Name ⬍</th>
                                                <th class="px-4 py-3 font-semibold text-center cursor-pointer min-w-[160px]" onclick="sortTable(2)" title="Email">Email ⬍</th>
                                                <th class="px-4 py-3 font-semibold text-center min-w-[130px]" title="Phone Number">Phone Number</th>
                                                <th class="px-4 py-3 font-semibold text-center min-w-[160px]" title="Address">Address</th>
                                                <th class="px-4 py-3 font-semibold text-center min-w-[80px]" title="User Type">User Type</th>
                                                <th class="px-4 py-3 font-semibold text-center min-w-[80px]" title="Status">Status</th>
                                                <th class="px-4 py-3 font-semibold text-center min-w-[160px]" title="Signup Date">Signup Date</th>
                                                <th class="px-4 py-3 font-semibold text-center min-w-[80px]" title="Actions">Actions</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php
                                            $query = "SELECT 
                                            user_id,
                                            name,
                                            email,
                                            phone_number,
                                            address,
                                            role AS user_type,
                                            status,
                                            created_at
                                        FROM users  
                                        ORDER BY created_at DESC";

                                            $stmt = $db->prepare($query);
                                            $stmt->execute();

                                            if ($stmt->rowCount() > 0) {
                                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                    $statusClass = $row['status'] === 'banned' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700';
                                                    $statusText = ucfirst($row['status']);

                                                    echo "<tr class='border-b divide-x divide-gray-200 hover:bg-gray-50'>";
                                                    echo "<td class='px-6 py-4 text-center'>" . htmlspecialchars($row['user_id']) . "</td>";
                                                    echo "<td class='px-6 py-4 font-medium'>" . htmlspecialchars($row['name']) . "</td>";
                                                    echo "<td class='px-6 py-4'>" . htmlspecialchars($row['email']) . "</td>";
                                                    echo "<td class='px-6 py-4 text-center'>" . htmlspecialchars($row['phone_number']) . "</td>";
                                                    echo "<td class='px-6 py-4'>" . htmlspecialchars($row['address']) . "</td>";
                                                    echo "<td class='px-6 py-4 text-center usertype-cell'><span class='inline-block px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700'>" . ucfirst(htmlspecialchars($row['user_type'])) . "</span></td>";
                                                    echo "<td class='px-6 py-4 text-center userstatus-cell'><span class='inline-block px-3 py-1 rounded-full text-xs font-semibold {$statusClass}'>{$statusText}</span></td>";
                                                    echo "<td class='px-6 py-4 text-center text-sm'>" . date('M d, Y', strtotime($row['created_at'])) . "</td>";

                                                    $userId = htmlspecialchars($row['user_id']);
                                                    $userName = htmlspecialchars($row['name']);
                                                    $userStatus = htmlspecialchars($row['status']);


                                                    echo '<td class="px-6 py-4 text-center">';

                                                    echo "<div class='flex items-center justify-center gap-3'>";

                                                    $banBtnClass = $userStatus === 'banned' ? 'text-green-600' : 'text-red-600';
                                                    $banBtnTitle = $userStatus === 'banned' ? 'Unban Account' : 'Ban Account';

                                                    // Change Role button
                                                    echo "<i class='fas fa-user-cog text-blue-600 hover:scale-125 transition-transform cursor-pointer'
                                                        title='Change Role'
                                                        onclick=\"openRoleModal({$userId}, '{$row['user_type']}')\"></i>";

                                                    // Ban / Unban button
                                                    echo "<i class=\"fas fa-lock {$banBtnClass} hover:scale-125 transition-transform cursor-pointer\" 
                                                        title=\"{$banBtnTitle}\" 
                                                        onclick=\"toggleBanUser({$userId}, '{$userName}', '{$userStatus}')\"></i>";

                                                    echo "</div>";

                                                    echo '</td>';



                                                    echo "</tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='9' class='px-6 py-4 text-center text-gray-500'>No users found</td></tr>";
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
    </div>
    <script src="..Admin/includes/admin_toast.js"></script>

    <script>
        // Smooth page transition
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.addEventListener('click', e => {
                e.preventDefault();
                const target = e.currentTarget.getAttribute('href');
                const content = document.getElementById('page-content');
                content.classList.add('opacity-0', 'translate-x-2');
                setTimeout(() => {
                    window.location.href = target;
                }, 300);
            });
        });

        const content = document.getElementById('page-content');
        if (content) {
            content.classList.add('opacity-0');
            setTimeout(() => content.classList.remove('opacity-0', 'translate-x-2'), 50);
        }

        function filterTab(button) {
            // Update tab styles
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white', 'no-hover');
                btn.classList.add('bg-blue-50', 'text-blue-500', 'hover:bg-gray-200');
            });
            button.classList.remove('bg-blue-50', 'text-blue-500', 'hover:bg-gray-200');
            button.classList.add('bg-blue-600', 'text-white', 'no-hover');

            // Filter table rows
            const filter = button.textContent.trim().toLowerCase(); // all, customers, riders, banned
            const rows = document.querySelectorAll('#usersTable tbody tr');

            rows.forEach(row => {
                const userType = row.querySelector('.usertype-cell')?.textContent.trim().toLowerCase();
                const userStatus = row.querySelector('.userstatus-cell')?.textContent.trim().toLowerCase();

                let show = false;

                if (filter === "all") {
                    show = true;
                } else if (filter === "customers" && userType === "customer") {
                    show = true;
                } else if (filter === "riders" && userType === "rider") {
                    show = true;
                } else if (filter === "banned" && userStatus === "banned") {
                    show = true;
                }

                row.style.display = show ? "" : "none";
            });
        }


        // Fixed searchTable function
        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const rows = Array.from(document.querySelectorAll('#usersTable tbody tr'));

            rows.forEach(row => {
                const cells = row.getElementsByTagName("td");
                let match = false;

                if (filter === "") {
                    row.style.display = "";
                    return;
                }

                for (let j = 0; j < cells.length; j++) { // include all cells
                    if (cells[j] && cells[j].textContent.toLowerCase().includes(filter)) {
                        match = true;
                        break;
                    }
                }

                row.style.display = match ? "" : "none";
            });
        }


        // Ban/Unban User
        function toggleBanUser(userId, userName, currentStatus) {
            const action = currentStatus === 'banned' ? 'unban' : 'ban';
            const title = action === 'ban' ? 'Ban Account' : 'Unban Account';
            const message = action === 'ban' ?
                `Are you sure you want to ban <strong>${userName}</strong>? They will no longer be able to access their account.` :
                `Are you sure you want to unban <strong>${userName}</strong>? They will regain access to their account.`;

            Swal.fire({
                title: title,
                html: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: action === 'ban' ? '#dc2626' : '#16a34a',
                cancelButtonColor: '#6b7280',
                confirmButtonText: action === 'ban' ? 'Yes, Ban User' : 'Yes, Unban User',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('action', 'ban');
                    formData.append('user_id', userId);
                    formData.append('status', currentStatus);

                    fetch(window.location.href, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: 'Success',
                                    text: `User has been ${action}ned successfully`,
                                    icon: 'success',
                                    confirmButtonColor: '#3b82f6'
                                }).then(() => location.reload());
                            } else {
                                Swal.fire('Error', data.error || 'Failed to update user status', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Error', 'An error occurred while updating user status', 'error');
                        });
                }
            });
        }

        // Simple column sorting
        function sortTable(columnIndex) {
            const table = document.getElementById('usersTable');
            const rows = Array.from(table.querySelectorAll('tbody tr'));

            rows.sort((a, b) => {
                const aValue = a.cells[columnIndex].textContent.trim();
                const bValue = b.cells[columnIndex].textContent.trim();
                return aValue.localeCompare(bValue);
            });

            const tbody = table.querySelector('tbody');
            rows.forEach(row => tbody.appendChild(row));
        }

        function openRoleModal(userId, currentRole) {
            Swal.fire({
                title: "Change User Role",
                input: "select",
                inputOptions: {
                    customer: "Customer",
                    admin: "Admin",
                    rider: "Rider"
                },
                inputValue: currentRole.toLowerCase(),
                showCancelButton: true,
                confirmButtonText: "Update Role",
                confirmButtonColor: "#2563eb"
            }).then(result => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append("action", "change_role");
                    formData.append("user_id", userId);
                    formData.append("role", result.value);

                    fetch(window.location.href, {
                            method: "POST",
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: "Success",
                                    text: "User role updated successfully!",
                                    icon: "success",
                                    confirmButtonColor: "#2563eb"
                                }).then(() => location.reload());
                            } else {
                                Swal.fire("Error", data.error, "error");
                            }
                        })
                        .catch(err => Swal.fire("Error", "Something went wrong", "error"));
                }
            });
        }
        (function() {
            const originalSwalFire = Swal.fire;
            let scrollPosition = 0;

            Swal.fire = function(...args) {
                scrollPosition = window.pageYOffset;
                const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;

                document.body.style.overflow = 'hidden';
                document.body.style.position = 'fixed';
                document.body.style.top = `-${scrollPosition}px`;
                document.body.style.width = '100%';
                document.body.style.paddingRight = scrollbarWidth + 'px';

                const result = originalSwalFire.apply(this, args);

                result.finally(() => {
                    document.body.style.overflow = '';
                    document.body.style.position = '';
                    document.body.style.top = '';
                    document.body.style.width = '';
                    document.body.style.paddingRight = '';
                    window.scrollTo(0, scrollPosition);
                });

                return result;
            };
        })();
    </script>

</body>

</html>