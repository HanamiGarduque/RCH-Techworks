<?php
<<<<<<< HEAD
<<<<<<< HEAD
require_once '../Database/db_connection.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];  // email input from the form
    $password = $_POST['password'];

    $db = new Database();
    $conn = $db->getConnect();

    // Check if user exists and is a rider
    $query = "SELECT * FROM users WHERE email = :email AND role = 'rider' LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify hashed password
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['rider_id'] = $user['user_id'];
            $_SESSION['rider_name'] = $user['name'];
            $_SESSION['rider_email'] = $user['email'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid email or password";
        }
    } else {
        $error = "Invalid email or password";
    }
=======
=======
>>>>>>> e70e61c6d9b34d520c5eb233fb632ee3492ace3b
session_start();
require_once '../Database/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "SELECT user_id, name, password_hash, role, status FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user['password_hash'])) {
        if ($user['role'] === 'rider' && $user['status'] === 'active') {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            header("Location: rider_account.php");
            exit;
        } else {
            $_SESSION['login_error'] = "You are not authorized to log in as a rider.";
        }
    } else {
        $_SESSION['login_error'] = "Invalid email or password.";
    }

    header("Location: login.php");
    exit;
<<<<<<< HEAD
>>>>>>> e70e61c (rider)
=======
>>>>>>> e70e61c6d9b34d520c5eb233fb632ee3492ace3b
}
?>
