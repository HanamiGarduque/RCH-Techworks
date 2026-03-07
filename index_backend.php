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
                // REDIRECT WITH STATUS ERROR
                header("Location: index.php?error=deactivated");
                exit;
            }

            // START SESSION
            session_regenerate_id(true); 
            $_SESSION['id'] = $user['user_id']; 
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            // Remember Me logic
            if ($remember) {
                setcookie('remember_email', $email, time() + 86400, "/");
            } else {
                if (isset($_COOKIE['remember_email'])) {
                    setcookie('remember_email', '', time() - 3600, "/");
                }
            }

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: admin/dashboard.php");
            } else if ($user['role'] === 'rider') {
                header("Location: rider/dashboard.php");
            } else {
                header("Location: Customer/order_water_refill.php");
            }
            exit;
        } else {
            // REDIRECT WITH PASSWORD ERROR
            header("Location: index.php?error=invalid_password");
            exit;
        }
    } else {
        // REDIRECT WITH EMAIL ERROR
        header("Location: index.php?error=email_not_found");
        exit;
    }
}