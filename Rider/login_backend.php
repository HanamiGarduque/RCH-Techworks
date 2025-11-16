<?php
session_start();
require_once '../Database/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['username']; // your login field
    $password = $_POST['password'];

    // Hash password using SHA256
    $password_sha256 = hash("sha256", $password);

    $db = new Database();
    $conn = $db->getConnect();

    // PDO prepared statement
    $sql = "SELECT * FROM users WHERE email = :email AND role = 'rider' LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['login_error'] = "Invalid email or password.";
        header("Location: login.php");
        exit();
    }

    // Check password
    if ($user['password_hash'] !== $password_sha256) {
        $_SESSION['login_error'] = "Incorrect password.";
        header("Location: login.php");
        exit();
    }

    // Set session
    $_SESSION['rider_id'] = $user['user_id'];
    $_SESSION['rider_name'] = $user['name'];
    $_SESSION['role'] = $user['role'];

    header("Location: dashboard.php");
    exit();
}
?>
