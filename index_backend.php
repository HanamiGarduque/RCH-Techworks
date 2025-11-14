<?php
session_start();
require_once 'Database/db_connection.php';

$database = new Database();
$db = $database->getConnect(); // PDO connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST["email"];
    $password = $_POST["password"];
    $remember = isset($_POST['remember']); 

    // Hash input password for comparison
    $password_hash = hash('sha256', $password);

    // Check if user exists
    $stmt = $db->prepare("SELECT user_id, name, email, password_hash, role, status FROM users WHERE email = :email LIMIT 1");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Compare hashed passwords
        if ($password_hash === $user['password_hash']) {

            // Check if user is active
            if ($user['status'] !== 'active') {
                echo "<script>alert('Your account is currently deactivated. Please contact support.'); window.history.back();</script>";
                exit;
            }

            // START SESSION
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            // Remember Me: set simple cookie for email (will expire in 1 day)
            if ($remember) {
                setcookie('remember_email', $email, time() + 86400, "/"); 
            } else {
                // Remove cookie if unchecked
                if (isset($_COOKIE['remember_email'])) {
                    setcookie('remember_email', '', time() - 3600, "/");
                }
            }

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: Customer/landing_page.php");
            }
            exit;

        } else {
            echo "<script>alert('Incorrect password!'); window.history.back();</script>";
            exit;
        }

    } else {
        echo "<script>alert('Email not found!'); window.history.back();</script>";
        exit;
    }
}
?>
