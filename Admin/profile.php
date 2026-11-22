<?php
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('admin');

require_once '../Database/db_connection.php';

$database = new Database();
$db = $database->getConnect();
$userId = $_SESSION['id'];

// Helper: check if column exists so it never crashes
function colExistsProfile($db, $table, $col) {
    try {
        $s = $db->prepare("SHOW COLUMNS FROM `$table` LIKE :c");
        $s->execute([':c' => $col]);
        return (bool)$s->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) { return false; }
}

$hasPhone = colExistsProfile($db, 'users', 'phone_number');
$hasPhone2 = colExistsProfile($db, 'users', 'phone');
$hasAddress = colExistsProfile($db, 'users', 'address');
$hasCreated = colExistsProfile($db, 'users', 'created_at');
$hasStatus = colExistsProfile($db, 'users', 'status');
$hasRole = colExistsProfile($db, 'users', 'role');

$phoneCol = $hasPhone ? 'phone_number' : ($hasPhone2 ? 'phone' : null);

// Handle update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    header('Content-Type: application/json; charset=utf-8');
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($name === '') {
        echo json_encode(['success' => false, 'message' => 'Name is required.']);
        exit;
    }

    try {
        $sets = ["name = :name"];
        $params = [':name' => $name, ':id' => $userId];
        if ($phoneCol) { $sets[] = "$phoneCol = :phone"; $params[':phone'] = $phone; }
        if ($hasAddress) { $sets[] = "address = :address"; $params[':address'] = $address; }

        $sql = "UPDATE users SET " . implode(', ', $sets) . " WHERE user_id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully.']);
    } catch (Exception $e) {
        error_log('Admin profile update: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to update profile.']);
    }
    exit;
}

// Handle change password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    header('Content-Type: application/json; charset=utf-8');
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($new === '' || strlen($new) < 6) {
        echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters.']);
        exit;
    }
    if ($new !== $confirm) {
        echo json_encode(['success' => false, 'message' => 'New passwords do not match.']);
        exit;
    }

    try {
        $stmt = $db->prepare("SELECT password FROM users WHERE user_id = :id LIMIT 1");
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            echo json_encode(['success' => false, 'message' => 'User not found.']);
            exit;
        }
        // If password is hashed, verify it. If it is plain text from old data, compare directly.
        $stored = $row['password'] ?? '';
        $ok = false;
        if (password_verify($current, $stored)) $ok = true;
        elseif ($current === $stored) $ok = true;

        if (!$ok) {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
            exit;
        }

        $hash = password_hash($new, PASSWORD_DEFAULT);
        $upd = $db->prepare("UPDATE users SET password = :p WHERE user_id = :id");
        $upd->execute([':p' => $hash, ':id' => $userId]);
        echo json_encode(['success' => true, 'message' => 'Password changed successfully.']);
    } catch (Exception $e) {
        error_log('Admin change password: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to change password.']);
    }
    exit;
}

// Load admin profile
try {
    $cols = "user_id, name, email";
    if ($phoneCol) $cols .= ", $phoneCol AS phone";
    if ($hasAddress) $cols .= ", address";
    if ($hasRole) $cols .= ", role";
    if ($hasStatus) $cols .= ", status";
    if ($hasCreated) $cols .= ", created_at";

    $stmt = $db->prepare("SELECT $cols FROM users WHERE user_id = :id LIMIT 1");
    $stmt->execute([':id' => $userId]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$admin) {
        header("Location: ../login.php");
        exit;
    }
} catch (Exception $e) {
    die("Error loading profile: " . $e->getMessage());
}

$adminName = $admin['name'] ?? 'Admin';
$adminEmail = $admin['email'] ?? '';
$adminPhone = $admin['phone'] ?? '';
$adminAddress = $admin['address'] ?? '';
$adminRole = $admin['role'] ?? 'admin';
$adminStatus = $admin['status'] ?? 'active';
$memberSince = !empty($admin['created_at']) ? date('F d, Y', strtotime($admin['created_at'])) : 'N/A';
$initials = '';
$parts = explode(' ', trim($adminName));
foreach ($parts as $p) { if ($p !== '') $initials .= strtoupper(substr($p, 0, 1)); }
$initials = substr($initials, 0, 2);
if ($initials === '') $initials = 'A';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RCH Water - Admin Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .page-container { height: 100vh; display: flex; flex-direction: column; overflow: hidden; }
        .content-wrapper { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #9ca3af; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #6b7280; }
        input:disabled { background-color: #f9fafb; color: #374151; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <script src="../Admin/includes/sidebar.js"></script>

        <div class="flex-1 ml-20 group-hover:ml-64 transition-all duration-300 ease-in-out overflow-hidden">
            <div class="page-container">
                <?php
                $pageTitle = "Admin Profile";
                $pageSubtitle = "View and manage your admin account.";
                include '../Admin/includes/header.php';
                ?>

                <div class="content-wrapper p-4">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

                        <!-- Left: Profile Card -->
                        <div class="bg-white rounded-lg shadow-lg p-6 flex flex-col items-center text-center h-fit">
                            <div class="w-24 h-24 rounded-full bg-blue-600 text-white flex items-center justify-center text-3xl font-bold mb-4">
                                <?= htmlspecialchars($initials) ?>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($adminName) ?></h2>
                            <p class="text-sm text-gray-500 mb-3"><?= htmlspecialchars($adminEmail) ?></p>
                            <span class="inline-block bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-semibold uppercase mb-1"><?= htmlspecialchars($adminRole) ?></span>
                            <span class="inline-block bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold uppercase"><?= htmlspecialchars($adminStatus) ?></span>

                            <div class="w-full border-t mt-5 pt-4 text-left space-y-3 text-sm">
                                <div class="flex justify-between"><span class="text-gray-500">Member Since</span><span class="font-semibold text-gray-800"><?= htmlspecialchars($memberSince) ?></span></div>
                                <div class="flex justify-between"><span class="text-gray-500">User ID</span><span class="font-semibold text-gray-800">#<?= htmlspecialchars($userId) ?></span></div>
                                <div class="flex justify-between"><span class="text-gray-500">Phone</span><span class="font-semibold text-gray-800"><?= htmlspecialchars($adminPhone ?: 'Not set') ?></span></div>
                            </div>

                            <button onclick="document.getElementById('passwordCard').scrollIntoView({behavior:'smooth'})" class="mt-6 w-full bg-white border border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white px-4 py-2.5 rounded-lg font-medium transition">
                                <i class="fas fa-key mr-2"></i>Change Password
                            </button>
                        </div>

                        <!-- Right: Details + Password -->
                        <div class="lg:col-span-2 space-y-4">
                            <!-- Profile Details -->
                            <div class="bg-white rounded-lg shadow-lg p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h2 class="text-xl font-semibold text-blue-600 flex items-center"><i class="fas fa-user-circle mr-2"></i>Profile Details</h2>
                                    <button id="editBtn" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition"><i class="fas fa-edit mr-1"></i>Edit Profile</button>
                                </div>

                                <form id="profileForm">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-sm font-medium text-gray-700 mb-2 block">Full Name</label>
                                            <input id="inputName" type="text" value="<?= htmlspecialchars($adminName) ?>" disabled class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-700 mb-2 block">Email Address</label>
                                            <input type="email" value="<?= htmlspecialchars($adminEmail) ?>" disabled class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg">
                                            <p class="text-xs text-gray-400 mt-1">Email cannot be changed here.</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-700 mb-2 block">Phone Number</label>
                                            <input id="inputPhone" type="text" value="<?= htmlspecialchars($adminPhone) ?>" disabled placeholder="09XXXXXXXXX" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-700 mb-2 block">Role</label>
                                            <input type="text" value="<?= htmlspecialchars(ucfirst($adminRole)) ?>" disabled class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="text-sm font-medium text-gray-700 mb-2 block">Address</label>
                                            <textarea id="inputAddress" rows="2" disabled class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($adminAddress) ?></textarea>
                                        </div>
                                    </div>

                                    <div id="editActions" class="hidden flex justify-end gap-3 mt-5">
                                        <button type="button" id="cancelEditBtn" class="px-5 py-2.5 rounded-lg border hover:bg-gray-50 font-medium">Cancel</button>
                                        <button type="submit" class="px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium">Save Changes</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Change Password -->
                            <div id="passwordCard" class="bg-white rounded-lg shadow-lg p-6">
                                <h2 class="text-xl font-semibold text-blue-600 mb-4 flex items-center"><i class="fas fa-lock mr-2"></i>Change Password</h2>
                                <form id="passwordForm">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="text-sm font-medium text-gray-700 mb-2 block">Current Password</label>
                                            <input id="currentPassword" type="password" required placeholder="Current password" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-700 mb-2 block">New Password</label>
                                            <input id="newPassword" type="password" required placeholder="At least 6 characters" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-700 mb-2 block">Confirm New Password</label>
                                            <input id="confirmPassword" type="password" required placeholder="Repeat new password" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                    </div>
                                    <div class="flex justify-end mt-5">
                                        <button type="submit" class="px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium">Update Password</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Quick Actions -->
                            <div class="bg-white rounded-lg shadow-lg p-6">
                                <h2 class="text-lg font-semibold text-gray-800 mb-3">Quick Actions</h2>
                                <div class="flex flex-wrap gap-3">
                                    <a href="../Admin/dashboard.php" class="inline-flex items-center gap-2 bg-blue-50 text-blue-700 px-4 py-2 rounded-lg font-medium hover:bg-blue-100"><i class="fas fa-tachometer-alt"></i>Go to Dashboard</a>
                                    <a href="../Order/orders_list.php" class="inline-flex items-center gap-2 bg-green-50 text-green-700 px-4 py-2 rounded-lg font-medium hover:bg-green-100"><i class="fas fa-list"></i>View Orders</a>
                                    <a href="../logout.php" class="inline-flex items-center gap-2 bg-red-50 text-red-700 px-4 py-2 rounded-lg font-medium hover:bg-red-100"><i class="fas fa-sign-out-alt"></i>Logout</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    const editBtn = document.getElementById('editBtn');
    const cancelBtn = document.getElementById('cancelEditBtn');
    const editActions = document.getElementById('editActions');
    const nameInput = document.getElementById('inputName');
    const phoneInput = document.getElementById('inputPhone');
    const addressInput = document.getElementById('inputAddress');
    const origName = nameInput.value;
    const origPhone = phoneInput.value;
    const origAddress = addressInput.value;

    function setEditing(on) {
        nameInput.disabled = !on;
        phoneInput.disabled = !on;
        addressInput.disabled = !on;
        editActions.classList.toggle('hidden', !on);
        editActions.classList.toggle('flex', on);
        editBtn.textContent = on ? 'Editing...' : 'Edit Profile';
        editBtn.disabled = on;
        if (on) nameInput.focus();
    }
    editBtn.addEventListener('click', () => setEditing(true));
    cancelBtn.addEventListener('click', () => {
        nameInput.value = origName;
        phoneInput.value = origPhone;
        addressInput.value = origAddress;
        setEditing(false);
    });

    document.getElementById('profileForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData();
        formData.append('action', 'update_profile');
        formData.append('name', nameInput.value.trim());
        formData.append('phone', phoneInput.value.trim());
        formData.append('address', addressInput.value.trim());
        const res = await fetch('profile.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Saved', text: data.message, confirmButtonColor: '#2563eb' }).then(() => location.reload());
        } else {
            Swal.fire({ icon: 'error', title: 'Failed', text: data.message, confirmButtonColor: '#2563eb' });
        }
    });

    document.getElementById('passwordForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData();
        formData.append('action', 'change_password');
        formData.append('current_password', document.getElementById('currentPassword').value);
        formData.append('new_password', document.getElementById('newPassword').value);
        formData.append('confirm_password', document.getElementById('confirmPassword').value);
        const res = await fetch('profile.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Password Changed', text: data.message, confirmButtonColor: '#2563eb' }).then(() => { document.getElementById('passwordForm').reset(); });
        } else {
            Swal.fire({ icon: 'error', title: 'Failed', text: data.message, confirmButtonColor: '#2563eb' });
        }
    });
</script>
</body>
</html>