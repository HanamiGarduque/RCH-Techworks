<?php
require_once 'Database/db_connection.php';

$database = new Database();
$db = $database->getConnect(); // This is a PDO connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    // Address fields
    $house_number = $_POST['house_number'];
    $street_name = $_POST['street_name'];
    $barangay = $_POST['barangay'];
    $city = $_POST['city'];
    $province = $_POST['province'];
    $postal_code = $_POST['postal_code'];

    // Combine into single address
    $address = "$house_number, $street_name, $barangay, $city, $province, $postal_code";

    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // Check if passwords match
    if ($password !== $confirmPassword) {
        echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
        exit;
    }

    // Encrypt password using SHA-256
    $password_hash = hash('sha256', $password);

    // Check if email already exists
    $checkEmail = $db->prepare("SELECT email FROM users WHERE email = :email");
    $checkEmail->bindParam(':email', $email, PDO::PARAM_STR);
    $checkEmail->execute();

    if ($checkEmail->rowCount() > 0) {
        echo "<script>alert('Email already registered!'); window.history.back();</script>";
        exit;
    }

    // Insert user
    $stmt = $db->prepare("INSERT INTO users (name, email, password_hash, phone_number, address, role, status, created_at, updated_at) 
                          VALUES (:name, :email, :password_hash, :phone, :address, 'customer', 'active', NOW(), NOW())");

    $stmt->bindParam(':name', $fullName);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password_hash', $password_hash);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':address', $address);

    if ($stmt->execute()) {
        echo "<script>alert('Account successfully created! You can now log in.'); window.location='../index.php';</script>";
    } else {
        echo "<script>alert('Error creating account. Please try again.'); window.history.back();</script>";
    }
}
?>
