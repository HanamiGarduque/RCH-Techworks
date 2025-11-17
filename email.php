<?php
// =====================================
//  MAIN BREVO SENDER FUNCTION
// =====================================
function sendEmail($toEmail, $toName, $subject, $htmlContent, $attachmentPath = null) {

    $apiKey = "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";  

    // Brevo endpoint
    $url = "https://api.brevo.com/v3/smtp/email";

    // Email payload
    $payload = [
        "sender" => [
            "name"  => "RCH Water",
            "email" => "rchwater1@gmail.com"
        ],
        "to" => [
            ["email" => $toEmail, "name" => $toName]
        ],
        "subject" => $subject,
        "htmlContent" => $htmlContent
    ];

    // Add attachment if provided
    if (!empty($attachmentPath) && file_exists($attachmentPath)) {
        $payload["attachment"] = [[
            "content" => base64_encode(file_get_contents($attachmentPath)),
            "name" => basename($attachmentPath)
        ]];
    }

    // cURL Setup
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Accept: application/json",
        "Content-Type: application/json",
        "api-key: $apiKey"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Brevo success code = 201
    if ($httpCode == 201) {
        return true;
    } else {
        echo "<b>Email send error: HTTP $httpCode</b><br>";
        echo $response;  // shows actual Brevo error
        return false;
    }
}



// =====================================
//  CUSTOMER NOTIFICATIONS
// =====================================

// Customer: Account Created
function sendCustomerRegistration($email, $fullName) {
    $html = "
    <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #1F2937;'>
        <h2 style='color: #2563EB;'>Welcome to RCH Water, $fullName!</h2>
        <p>Hi $fullName,</p>
        <p>We’re thrilled to have you join the RCH Water family! Your account has been successfully created, and you now have full access to our services.</p>
        <p>With your account, you can easily:</p>
        <ul style='margin-left: 20px;'>
            <li>Place and track your water refill orders</li>
            <li>Manage your delivery preferences and account details</li>
            <li>Receive updates and notifications directly to your email</li>
        </ul>
        <p>To get started, simply click the button below to log in to your account:</p>
        <p style='text-align:center; margin: 25px 0;'>
            <a href='http://localhost/Tin_RCH-Techworks/index.php' 
               style='background-color: #2563EB; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 8px; display:inline-block; font-weight: bold;'>
               Log In to Your Account
            </a>
        </p>
        <p>If you did not create this account, you can safely ignore this email.</p>
        <p>Thank you for choosing <strong>RCH Water</strong>! We look forward to serving you.</p>
        <p style='margin-top:20px;'>Warm regards,<br>RCH Water Team</p>
    </div>
    ";

    return sendEmail($email, $fullName, "Your RCH Water Account is Ready!", $html);
}


// Customer: Order Placed
function sendCustomerOrderPlaced($email, $fullName, $orderId) {
    $html = "
        <h2>Order Placed Successfully</h2>
        <p>Hi $fullName, your order <b>#$orderId</b> has been placed.</p>
    ";
    return sendEmail($email, $fullName, "Your Order Has Been Placed", $html);
}


// Customer: Order Delivered (with receipt)
function sendCustomerOrderDelivered($email, $fullName, $orderId, $receiptPath) {
    $html = "
        <h2>Order Delivered</h2>
        <p>Your order <b>#$orderId</b> has been delivered. Receipt attached.</p>
    ";
    return sendEmail($email, $fullName, "Order Delivered — Receipt Attached", $html, $receiptPath);
}



// =====================================
//  ADMIN NOTIFICATIONS
// =====================================

$adminEmail = "admin@rchwater.com";  
$adminName  = "Admin";


// Admin: New Order
function notifyAdminNewOrder($orderId, $customerName) {
    global $adminEmail, $adminName;

    $html = "
        <h2>New Order Received</h2>
        <p>Order ID: <b>$orderId</b></p>
        <p>Customer: <b>$customerName</b></p>
    ";
    return sendEmail($adminEmail, $adminName, "New Order Received — #$orderId", $html);
}


// Admin: Pending deadlines
function notifyAdminPendingDeadlines($pendingCount) {
    global $adminEmail, $adminName;

    $html = "
        <h2>Pending Orders Near Deadline</h2>
        <p>Total pending: <b>$pendingCount</b></p>
    ";
    return sendEmail($adminEmail, $adminName, "Pending Orders Deadline Alert", $html);
}


// Admin: Order Cancelled / Issue
function notifyAdminOrderIssue($orderId, $customerName, $issue) {
    global $adminEmail, $adminName;

    $html = "
        <h2>Order Issue Reported</h2>
        <p><b>$customerName</b> reported an issue on order <b>#$orderId</b>.</p>
        <p>Issue: <b>$issue</b></p>
    ";
    return sendEmail($adminEmail, $adminName, "Order Issue Reported — #$orderId", $html);
}


// Admin: Low Stock
function notifyAdminLowStock($itemName, $qtyLeft) {
    global $adminEmail, $adminName;

    $html = "
        <h2>Low Stock Alert</h2>
        <p>Item: <b>$itemName</b></p>
        <p>Remaining Qty: <b>$qtyLeft</b></p>
    ";
    return sendEmail($adminEmail, $adminName, "Low Stock: $itemName", $html);
}

?>
