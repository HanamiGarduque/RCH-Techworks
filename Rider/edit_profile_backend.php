<?php
session_start();
require_once '../database/Database.php';

// Redirect if not logged in
if (!isset($_SESSION['rider_username'])) {
    header("Location: login.php");
    exit();
}

$db = new Database();
$conn = $db->getConnect();

// Fetch current profile info
$stmt = $conn->prepare("SELECT name, email, phone_number, address FROM users WHERE username = :username LIMIT 1");
$stmt->bindParam(':username', $_SESSION['rider_username']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$name = htmlspecialchars($user['name']);
$email = htmlspecialchars($user['email']);
$phone = htmlspecialchars($user['phone_number']);
$address = htmlspecialchars($user['address']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Profile</title>
<style>
body { font-family: Arial; padding: 20px; background: #f2f2f2; }
.container {
    background: white;
    padding: 20px;
    border-radius: 10px;
    max-width: 400px;
    margin: auto;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
input {
    width: 100%;
    padding: 10px;
    margin-top: 8px;
    border-radius: 8px;
    border: 1px solid #ccc;
}
button {
    background: #1E90FF;
    color: white;
    border: none;
    padding: 12px;
    width: 100%;
    margin-top: 15px;
    border-radius: 8px;
    cursor: pointer;
}
button:hover {
    background: #0b7dda;
}
</style>
</head>
<body>

<div class="container">
    <h2>Edit Profile</h2>

    <form action="edit_profile_backend.php" method="post">

        <label>Full Name</label>
        <input type="text" name="name" value="<?= $name ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= $email ?>" required>

        <label>Phone Number</label>
        <input type="text" name="phone_number" value="<?= $phone ?>" required>

        <label>Address</label>
        <input type="text" name="address" value="<?= $address ?>" required>

        <button type="submit">Save Changes</button>
    </form>
</div>

</body>
</html>
