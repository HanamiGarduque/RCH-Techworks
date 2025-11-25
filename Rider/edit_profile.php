<?php
session_start();
require_once '../Database/db_connection.php';
date_default_timezone_set('Asia/Manila');

// TEMPORARY RIDER ID FOR TESTING
$rider_id = $_SESSION['rider_id'] ?? 11;

if (!$rider_id) {
    die("Rider not logged in");
}

$db = new Database();
$conn = $db->getConnect();

// Fetch rider info
$query = "SELECT user_id, name, email, phone_number, address FROM users WHERE user_id = :id LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bindParam(":id", $rider_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found");
}

// Update profile
if (isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone_number'];
    $address = $_POST['address'];

    // Update query
    $update = " UPDATE users 
        SET name = :name, email = :email, phone_number = :phone, address = :address
        WHERE user_id = :id
    ";

    $stmt = $conn->prepare($update);
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':phone' => $phone,
        ':address' => $address,
        ':id' => $rider_id
    ]);

    header("Location: dashboard.php?updated=1");
    exit;
}


// Change password
if (isset($_POST['change_password'])) {
    $new_pass = $_POST['new_password'];

    if (!empty($new_pass)) {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);

        $updatePass = "UPDATE users SET password_hash = :hash WHERE user_id = :id";
        $stmt = $conn->prepare($updatePass);
        $stmt->execute([
            ':hash' => $hashed,
            ':id' => $rider_id
        ]);

        header("Location: edit_profile.php?password_changed=1");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile | RCH Water</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">

    <div class="max-w-xl mx-auto mt-10 bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-4 text-blue-700">Edit Profile</h2>

        <!-- Update Profile Form -->
        <form method="POST" class="space-y-4">
            <div>
                <label class="block font-medium">Full Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" class="w-full p-2 border rounded-lg">
            </div>

            <div>
                <label class="block font-medium">Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="w-full p-2 border rounded-lg">
            </div>

            <div>
                <label class="block font-medium">Phone Number</label>
                <input type="text" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>" class="w-full p-2 border rounded-lg">
            </div>

            <div>
                <label class="block font-medium">Address</label>
                <textarea name="address" class="w-full p-2 border rounded-lg"><?php echo htmlspecialchars($user['address']); ?></textarea>
            </div>

            <button type="submit" name="update_profile" class="w-full bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700">
                Save Changes
            </button>
        </form>

        <hr class="my-6">

        <!-- Password Change -->
        <h3 class="text-xl font-bold mb-2">Change Password</h3>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block font-medium">New Password</label>
                <input type="password" name="new_password" class="w-full p-2 border rounded-lg">
            </div>

            <button type="submit" name="change_password" class="w-full bg-green-600 text-white p-3 rounded-lg hover:bg-green-700">
                Update Password
            </button>
        </form>

        <a href="dashboard.php" class="block mt-6 text-center text-blue-600 hover:underline">Back to Dashboard</a>
    </div>

</body>
</html>
