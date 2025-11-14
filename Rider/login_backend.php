<?php
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
}
?>
