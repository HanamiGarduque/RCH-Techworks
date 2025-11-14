<?php
session_start();
header('Content-Type: application/json');

// Show PHP errors during development
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/Database/db_connection.php';

$database = new Database();
$db = $database->getConnect();

$API_TOKEN = "ba9958e785ac42c22bda17b617158dac68c24165";

// Function to send SMS via iProg API (same as signup)
function sendSMS($phone, $message, $api_token)
{
    $url = "https://sms.iprogtech.com/api/v1/otp/send_otp";
    $data = [
        'api_token'    => $api_token,
        'phone_number' => $phone,
        'message'      => $message
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['success' => false, 'error' => $error];
    }

    return json_decode($response, true);
}

$action = $_POST['action'] ?? '';

try {
    // SEND OTP
    if ($action === 'send_otp') {
        $phone = trim($_POST['phone'] ?? '');

        if (empty($phone)) {
            echo json_encode(['success' => false, 'message' => 'Please enter your phone number.']);
            exit;
        }

        // Check if phone number exists
        $stmt = $db->prepare("SELECT * FROM users WHERE phone_number = ?");
        $stmt->execute([$phone]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Phone number not found in our records.']);
            exit;
        }

        // Generate OTP
        $otp = rand(100000, 999999);
        $_SESSION['reset_otp'] = $otp;
        $_SESSION['reset_phone'] = $phone;
        $_SESSION['otp_expiry'] = time() + 300;

        $message = "Your RCH Water OTP code is $otp. It expires in 5 minutes.";
        $smsResponse = sendSMS($phone, $message, $API_TOKEN);

        echo json_encode(['success' => true, 'message' => 'OTP sent successfully.']);
        exit;
    }

    // VERIFY OTP
    if ($action === 'verify_otp') {
        $otp = $_POST['otp'] ?? '';

        if (!isset($_SESSION['reset_otp']) || !isset($_SESSION['otp_expiry'])) {
            echo json_encode(['success' => false, 'message' => 'No OTP found. Please request again.']);
            exit;
        }

        if (time() > $_SESSION['otp_expiry']) {
            echo json_encode(['success' => false, 'message' => 'OTP expired. Please request a new one.']);
            exit;
        }

        if ($otp != $_SESSION['reset_otp']) {
            echo json_encode(['success' => false, 'message' => 'Incorrect OTP.']);
            exit;
        }

        $_SESSION['otp_verified'] = true;
        echo json_encode(['success' => true, 'message' => 'OTP verified successfully.']);
        exit;
    }

    // RESET PASSWORD
if ($action === 'reset_password') {
    if (empty($_SESSION['otp_verified']) || !$_SESSION['otp_verified']) {
        echo json_encode(['success' => false, 'message' => 'OTP verification required before resetting password.']);
        exit;
    }

    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    if (empty($password) || empty($confirmPassword)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all password fields.']);
        exit;
    }

    if ($password !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
        exit;
    }

    $hashedPassword = hash('sha256', $password);
    $phone = $_SESSION['reset_phone'];

    $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE phone_number = ?");
    $stmt->execute([$hashedPassword, $phone]);

    // Clear session
    unset($_SESSION['reset_otp'], $_SESSION['reset_phone'], $_SESSION['otp_verified'], $_SESSION['otp_expiry']);

    echo json_encode(['success' => true, 'message' => 'Password reset successful. You can now log in.']);
    exit;
}

    // Default invalid action
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    exit;

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
}
?>
