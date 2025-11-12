<?php
session_start();
require_once 'Database/db_connection.php';

$database = new Database();
$db = $database->getConnect(); // PDO connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $house_number = $_POST['house_number'];
    $street_name = $_POST['street_name'];
    $barangay = $_POST['barangay'];
    $city = $_POST['city'];
    $province = $_POST['province'];
    $postal_code = $_POST['postal_code'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    $address = "$house_number, $street_name, $barangay, $city, $province, $postal_code";

    // Password match check
    if ($password !== $confirmPassword) {
        echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
        exit;
    }

    // Email duplicate check
    $checkEmail = $db->prepare("SELECT email FROM users WHERE email = :email");
    $checkEmail->bindParam(':email', $email, PDO::PARAM_STR);
    $checkEmail->execute();

    if ($checkEmail->rowCount() > 0) {
        echo "<script>alert('Email already registered!'); window.history.back();</script>";
        exit;
    }

    // ✅ Generate OTP
    $otp = rand(100000, 999999);

    // Store registration data in session temporarily
    $_SESSION['reg_data'] = [
        'fullName' => $fullName,
        'email' => $email,
        'password_hash' => hash('sha256', $password),
        'phone' => $phone,
        'address' => $address,
        'otp' => $otp,
        'otp_sent_at' => time(),
        'otp_tries' => 0 // resend counter
    ];

    // ✅ Send OTP using iProg SMS API
    $api_token = "ba9958e785ac42c22bda17b617158dac68c24165";

    $data = [
        'api_token'    => $api_token,
        'phone_number' => $phone,
        'message'      => "Your RCH Water verification code is: $otp. It will expire in 5 minutes."
    ];

    $ch = curl_init("https://sms.iprogtech.com/api/v1/otp/send_otp");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo "<script>alert('Failed to send OTP. $error'); window.history.back();</script>";
        exit;
    }

    $result = json_decode($response, true);
    if (isset($result['status']) && $result['status'] === 'success') {
        echo "<script>alert('OTP sent successfully! Please verify your phone number.'); window.location='verify_otp.php';</script>";
    } else {
        echo "<script>alert('Failed to send OTP. Please try again later.'); window.history.back();</script>";
    }
}
?>
