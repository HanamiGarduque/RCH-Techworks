<?php
session_start();
require_once 'Database/db_connection.php';

$database = new Database();
$db = $database->getConnect();

if (!isset($_SESSION['reg_data'])) {
    header("Location: signup.php");
    exit;
}

$reg = &$_SESSION['reg_data'];

$swal_script = ''; // Collect SweetAlert scripts

// Handle OTP verification
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verify'])) {
    $entered_otp = trim($_POST['otp']);
    $otp_validity = 300;

    if (time() - $reg['otp_sent_at'] > $otp_validity) {
        $swal_script = "Swal.fire({icon:'error', title:'OTP Expired', text:'Please request a new OTP.'});";
    } elseif ($entered_otp == $reg['otp']) {
        $stmt = $db->prepare("INSERT INTO users (name, email, password_hash, phone_number, address, role, status, created_at, updated_at) 
                              VALUES (:name, :email, :password_hash, :phone, :address, 'customer', 'active', NOW(), NOW())");

        $stmt->bindParam(':name', $reg['fullName']);
        $stmt->bindParam(':email', $reg['email']);
        $stmt->bindParam(':password_hash', $reg['password_hash']);
        $stmt->bindParam(':phone', $reg['phone_number']);
        $stmt->bindParam(':address', $reg['address']);

        if ($stmt->execute()) {
            unset($_SESSION['reg_data']);
            $swal_script = "Swal.fire({icon:'success', title:'Account Created', text:'Phone verified successfully!'}).then(()=>{window.location='./index.php'});";
        } else {
            $swal_script = "Swal.fire({icon:'error', title:'Error', text:'Failed to create account. Try again.'});";
        }
    } else {
        $swal_script = "Swal.fire({icon:'error', title:'Invalid OTP', text:'Please try again.'});";
    }
}

// Handle resend OTP
if (isset($_POST['resend'])) {
    $reg['otp'] = rand(100000, 999999);
    $reg['otp_sent_at'] = time();

    $api_token = "ba9958e785ac42c22bda17b617158dac68c24165";
    $data = [
        'api_token'    => $api_token,
        'phone_number' => $reg['phone_number'],
        'message'      => "Your new RCH Water verification code is: {$reg['otp']}. It will expire in 5 minutes."
    ];

    $ch = curl_init("https://sms.iprogtech.com/api/v1/otp/send_otp");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_exec($ch);
    curl_close($ch);

    $swal_script = "Swal.fire({icon:'success', title:'OTP Sent', text:'A new OTP has been sent to your phone.'});";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verify OTP - RCH Water</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let countdown = 300;
function updateTimer() {
    const timer = document.getElementById('timer');
    const resendText = document.getElementById('resendText');

    if (countdown <= 0) {
        timer.innerText = "Expired!";
        resendText.classList.remove('text-gray-400');
        resendText.classList.add('text-blue-600');
        resendText.classList.remove('cursor-not-allowed');
        resendText.addEventListener('click', resendOTP);
    } else {
        const mins = Math.floor(countdown / 60);
        const secs = countdown % 60;
        timer.innerText = `${mins}:${secs.toString().padStart(2,'0')}`;
        countdown--;
        setTimeout(updateTimer, 1000);
    }
}

function resendOTP() {
    const form = document.getElementById('resendForm');
    form.submit();
}

window.onload = function() {
    updateTimer();
    <?php echo $swal_script; ?>
}
</script>
</head>
<body class="bg-blue-50 min-h-screen flex items-center justify-center p-4">
<div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full">
    <h2 class="text-2xl font-bold text-blue-600 mb-2 text-center">Verify Your Phone Number</h2>
    <p class="text-gray-600 text-center mb-6">
        We’ve sent a 6-digit code to <strong><?php echo htmlspecialchars($reg['phone_number']); ?></strong>
    </p>

    <form method="POST" class="space-y-4">
        <input type="text" name="otp" maxlength="6" required
            class="w-full text-center px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-500"
            placeholder="Enter 6-digit code">
        <button type="submit" name="verify"
            class="w-full bg-blue-500 hover:bg-blue-600 text-white py-3 rounded-lg font-medium transition">
            Verify
        </button>
    </form>

    <!-- Timer + Resend -->
    <div class="flex justify-center items-center gap-2 mt-4 text-sm text-gray-600">
        <span>Expires in: <span id="timer" class="font-semibold text-blue-600">5:00</span></span>
        <form id="resendForm" method="POST">
            <input type="hidden" name="resend" value="1">
            <span id="resendText" class="underline text-gray-400 cursor-not-allowed">Resend</span>
        </form>
    </div>
</div>
</body>
</html>
