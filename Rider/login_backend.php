<?php
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
}
?>
