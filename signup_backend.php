<!DOCTYPE html>
<html>
<head>
    <!-- SWEET ALERT NEEDS TO LOAD HERE -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body></body>

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
        echo "
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Password Mismatch',
            text: 'Passwords do not match!',
        }).then(() => { window.history.back(); });
        </script>";
        exit;
    }

    // Email duplicate check
    $checkEmail = $db->prepare("SELECT email FROM users WHERE email = :email");
    $checkEmail->bindParam(':email', $email, PDO::PARAM_STR);
    $checkEmail->execute();

    if ($checkEmail->rowCount() > 0) {
        echo "
        <script>
        Swal.fire({
            icon: 'warning',
            title: 'Email Already Exists',
            text: 'This email is already registered!',
        }).then(() => { window.history.back(); });
        </script>";
        exit;
    }

    if (!preg_match('/^09\d{9}$/', $phone)) {
    echo "
    <script>
    Swal.fire({
        icon: 'error',
        title: 'Invalid Phone Number',
        text: 'Phone number must start with 09 and be 11 digits long!',
    }).then(() => { window.history.back(); });
    </script>";
    exit;
}

    // ✅ CORRECTED PHONE CHECK (phone_number column)
    $checkPhone = $db->prepare("SELECT phone_number FROM users WHERE phone_number = :phone");
    $checkPhone->bindParam(':phone', $phone, PDO::PARAM_STR);
    $checkPhone->execute();

    if ($checkPhone->rowCount() > 0) {
        echo "
        <script>
        Swal.fire({
            icon: 'warning',
            title: 'Phone Number Exists',
            text: 'This phone number is already registered!',
        }).then(() => { window.history.back(); });
        </script>";
        exit;
    }

    // Generate OTP
    $otp = rand(100000, 999999);

    // Store registration data in session temporarily
    $_SESSION['reg_data'] = [
        'fullName' => $fullName,
        'email' => $email,
        'password_hash' => hash('sha256', $password),
        'phone_number' => $phone,   // ← updated
        'address' => $address,
        'otp' => $otp,
        'otp_sent_at' => time(),
        'otp_tries' => 0
    ];

    // Send OTP using iProg SMS API
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
        echo "
        <script>
        Swal.fire({
            icon: 'error',
            title: 'SMS Error',
            text: 'Failed to send OTP: $error',
        }).then(() => { window.history.back(); });
        </script>";
        exit;
    }

    $result = json_decode($response, true);

    if (isset($result['status']) && $result['status'] === 'success') {
        echo "
        <script>
        Swal.fire({
            icon: 'success',
            title: 'OTP Sent',
            text: 'Verification code sent! Please check your SMS.',
        }).then(() => { window.location='verify_otp_backend.php'; });
        </script>";
    } else {
        echo "
        <script>
        Swal.fire({
            icon: 'error',
            title: 'OTP Failed',
            text: 'Unable to send OTP. Please try again later.',
        }).then(() => { window.history.back(); });
        </script>";
    }
}
?>

</html>
