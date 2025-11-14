<?php
session_start();
require_once 'Database/db_connection.php';

$database = new Database();
$db = $database->getConnect();

if (!isset($_SESSION['reg_data'])) {
    header("Location: signup.php");
    exit;
}

$reg = &$_SESSION['reg_data']; // Shortcut

// Handle OTP verification
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verify'])) {
    $entered_otp = trim($_POST['otp']);
    $otp_validity = 300; // 5 minutes

    if (time() - $reg['otp_sent_at'] > $otp_validity) {
        echo "<script>alert('OTP expired! Please request a new one.');</script>";
    } elseif ($entered_otp == $reg['otp']) {
        // ✅ OTP correct → save to database
        $stmt = $db->prepare("INSERT INTO users (name, email, password_hash, phone_number, address, role, status, created_at, updated_at) 
                              VALUES (:name, :email, :password_hash, :phone, :address, 'customer', 'active', NOW(), NOW())");

        $stmt->bindParam(':name', $reg['fullName']);
        $stmt->bindParam(':email', $reg['email']);
        $stmt->bindParam(':password_hash', $reg['password_hash']);
        $stmt->bindParam(':phone', $reg['phone']);
        $stmt->bindParam(':address', $reg['address']);

        if ($stmt->execute()) {
            unset($_SESSION['reg_data']);
            echo "<script>alert('Phone number verified! Account created successfully.'); window.location='../index.php';</script>";
            exit;
        } else {
            echo "<script>alert('Error creating account. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('Invalid OTP. Please try again.');</script>";
    }
}

// Handle resend OTP
if (isset($_POST['resend'])) {
    if ($reg['otp_tries'] >= 3) {
        echo "<script>alert('You have reached the maximum resend attempts. Please try again later.');</script>";
    } else {
        $reg['otp'] = rand(100000, 999999);
        $reg['otp_sent_at'] = time();
        $reg['otp_tries']++;

        // Send new OTP
        $api_token = "ba9958e785ac42c22bda17b617158dac68c24165";
        $data = [
            'api_token'    => $api_token,
            'phone_number' => $reg['phone'],
            'message'      => "Your new RCH Water verification code is: {$reg['otp']}. It will expire in 5 minutes."
        ];

        $ch = curl_init("https://sms.iprogtech.com/api/v1/otp/send_otp");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $response = curl_exec($ch);
        curl_close($ch);

        echo "<script>alert('A new OTP has been sent to your phone.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Verify OTP</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script>
let countdown = 300; // 5 minutes
function updateTimer() {
    const timer = document.getElementById('timer');
    if (countdown <= 0) {
        timer.innerText = "Expired!";
        document.getElementById('verifyBtn').disabled = true;
    } else {
        const mins = Math.floor(countdown / 60);
        const secs = countdown % 60;
        timer.innerText = `${mins}:${secs.toString().padStart(2,'0')}`;
        countdown--;
        setTimeout(updateTimer, 1000);
    }
}
window.onload = updateTimer;
</script>
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="card p-4 shadow" style="max-width:400px;margin:auto;">
    <h4 class="text-center mb-3">Verify Your Phone Number</h4>
    <p class="text-center text-muted">We’ve sent a 6-digit code to <strong><?php echo htmlspecialchars($reg['phone']); ?></strong></p>
    <p class="text-center text-danger">Expires in: <span id="timer">5:00</span></p>
    
    <form method="POST">
      <div class="mb-3">
        <input type="text" name="otp" class="form-control text-center" placeholder="Enter 6-digit code" maxlength="6" required>
      </div>
      <button type="submit" name="verify" id="verifyBtn" class="btn btn-primary w-100 mb-2">Verify</button>
      <button type="submit" name="resend" class="btn btn-outline-secondary w-100">Resend OTP</button>
      <p class="text-center mt-2 small text-muted">Resend attempts left: <?php echo 3 - $reg['otp_tries']; ?></p>
    </form>
  </div>
</div>
</body>
</html>
