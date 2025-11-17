<?php
// =====================================
//  MAIN BREVO SENDER FUNCTION (DO NOT MODIFY)
// =====================================
function sendEmail($toEmail, $toName, $subject, $htmlContent, $attachmentPath = null) {

    $apiKey = "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";  // <<==== PUT API KEY HERE

    $url = "https://api.brevo.com/v3/smtp/email";

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

    // Add attachment if exists
    if (!empty($attachmentPath) && file_exists($attachmentPath)) {
        $payload["attachment"] = [[
            "content" => base64_encode(file_get_contents($attachmentPath)),
            "name" => basename($attachmentPath)
        ]];
    }

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json',
        'api-key: ' . $apiKey
    ]);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($httpCode == 201) {
        return true;
    } else {
        echo "Email send error: HTTP $httpCode<br>";
        echo $response;
        return false;
    }
}



// =====================================
//  CUSTOMER NOTIFICATIONS
// =====================================

// Customer: Order Placed
function sendCustomerOrderPlaced($email, $name, $orderId) {
    $html = "
        <h2>Order Placed Successfully</h2>
        <p>Hi $name,</p>
        <p>Your order <strong>#$orderId</strong> has been successfully placed.</p>
    ";
    return sendEmail($email, $name, "Your Order Has Been Placed", $html);
}

// Customer: Order Delivered + Receipt Attached
function sendCustomerOrderDelivered($email, $name, $orderId, $receiptPath) {
    $html = "
        <h2>Your Order Has Been Delivered</h2>
        <p>Hi $name,</p>
        <p>Your order <strong>#$orderId</strong> has been delivered.</p>
        <p>Your e-receipt is attached.</p>
    ";
    return sendEmail($email, $name, "Order Delivered — Receipt Attached", $html, $receiptPath);
}

// =====================================
//  ADMIN NOTIFICATIONS
// =====================================

$adminEmail = "admin@rchwater.com";  // change if needed
$adminName = "Admin";

// Admin: New Order Received
function notifyAdminNewOrder($orderId, $customerName) {
    global $adminEmail, $adminName;

    $html = "
        <h2>New Order Received</h2>
        <p>A new order has been placed.</p>
        <p><strong>Order ID:</strong> $orderId</p>
        <p><strong>Customer:</strong> $customerName</p>
    ";
    return sendEmail($adminEmail, $adminName, "New Order Received — #$orderId", $html);
}

// Admin: Pending Orders Reaching Deadline
function notifyAdminPendingDeadlines($pendingCount) {
    global $adminEmail, $adminName;

    $html = "
        <h2>Pending Orders Near Deadline</h2>
        <p>There are <strong>$pendingCount</strong> orders nearing the deadline.</p>
    ";
    return sendEmail($adminEmail, $adminName, "Pending Orders Deadline Alert", $html);
}

// Admin: Order Cancelled / Issue Reported
function notifyAdminOrderIssue($orderId, $customerName, $issue) {
    global $adminEmail, $adminName;

    $html = "
        <h2>Order Cancelled / Issue Reported</h2>
        <p>Order <strong>#$orderId</strong> has a reported issue.</p>
        <p><strong>Customer:</strong> $customerName</p>
        <p><strong>Issue:</strong> $issue</p>
    ";
    return sendEmail($adminEmail, $adminName, "Order Issue Reported — #$orderId", $html);
}

// Admin: Low Stock Alerts
function notifyAdminLowStock($itemName, $qtyLeft) {
    global $adminEmail, $adminName;

    $html = "
        <h2>Low Stock Alert</h2>
        <p><strong>$itemName</strong> is running low.</p>
        <p>Remaining quantity: <strong>$qtyLeft</strong></p>
    ";
    return sendEmail($adminEmail, $adminName, "Low Stock: $itemName", $html);
}

?>
