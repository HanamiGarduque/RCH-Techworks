<?php
// =====================================
//  MAIN BREVO SENDER FUNCTION
// =====================================
function sendEmail($toEmail, $toName, $subject, $htmlContent, $attachmentPath = null)
{

    $apiKey = "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";

    // $apiKey = "key"; 

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
function sendCustomerRegistration($email, $fullName)
{
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
function sendCustomerOrderPlaced($email, $fullName, $orderId, $totalAmount, $pickupDatetime, $deliveryDatetime)
{
    // Optional: Format datetime strings for better readability in the email
    $formattedPickup = date("M d, Y h:i A", strtotime($pickupDatetime));
    $formattedDelivery = date("M d, Y h:i A", strtotime($deliveryDatetime));

    $html = "
    <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #1F2937; max-width: 600px; margin: auto; border: 1px solid #E5E7EB; padding: 20px; border-radius: 8px;'>
        <h2 style='color: #2563EB; border-bottom: 2px solid #EFF6FF; padding-bottom: 10px;'>✅ Your Order Has Been Placed!</h2>
        <p>Hi $fullName,</p>
        <p>Thank you for choosing <strong>RCH Water</strong>! We have successfully received your refill order and are now preparing it for scheduling.</p>

        <div style='background-color: #F9FAFB; padding: 15px; border-radius: 6px; margin: 20px 0;'>
            <h3 style='margin-top: 0; color: #374151; font-size: 18px;'>Order Summary</h3>
            <ul style='list-style: none; padding: 0; margin: 0;'>
                <li style='margin-bottom: 8px;'><strong>Order ID:</strong> <span style='float: right; font-weight: bold;'>#$orderId</span></li>
                <li style='margin-bottom: 8px; padding-top: 8px; border-top: 1px dashed #D1D5DB;'><strong>Total Amount Due:</strong> <span style='float: right; color: #10B981; font-weight: bold;'>PHP " . number_format($totalAmount, 2) . "</span></li>
                <li style='margin-bottom: 8px;'><strong>Expected Pickup:</strong> <span style='float: right;'>$formattedPickup</span></li>
                <li><strong>Expected Delivery:</strong> <span style='float: right;'>$formattedDelivery</span></li>
            </ul>
        </div>
        
        <p>You can track the progress of your order and view the complete details below:</p>

        <p style='text-align:center; margin: 30px 0;'>
            <a href='http://localhost/Tin_RCH-Techworks/customer/order_details.php' 
               style='background-color: #2563EB; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 8px; display:inline-block; font-weight: bold; font-size: 16px;'>
               View My Order Status
            </a>
        </p>

        <p style='font-size: 14px;'>We will notify you again once your order is out for delivery. Thank you!</p>

        <p style='margin-top:20px;'>Best regards,<br>RCH Water Team</p>
    </div>
    ";

    return sendEmail($email, $fullName, "Your Order Has Been Placed — #$orderId", $html);
}

// Customer: Order Delivered (with receipt)
function sendCustomerOrderDelivered($email, $fullName, $orderId, $receiptPath, $totalAmountPaid)
{
    $html = "
    <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #1F2937; max-width: 600px; margin: auto; border: 1px solid #E5E7EB; padding: 20px; border-radius: 8px;'>
        <h2 style='color: #2563EB; border-bottom: 2px solid #EFF6FF; padding-bottom: 10px;'>📦 Your Order Has Been Delivered!</h2>
        <p>Hi $fullName,</p>
        <p>We are pleased to confirm that your water refill order, **#$orderId**, has been successfully delivered and completed.</p>

        <div style='background-color: #F9FAFB; padding: 15px; border-radius: 6px; margin: 20px 0;'>
            <h3 style='margin-top: 0; color: #374151; font-size: 18px;'>Transaction Summary</h3>
            <ul style='list-style: none; padding: 0; margin: 0;'>
                <li style='margin-bottom: 8px;'><strong>Order ID:</strong> <span style='float: right; font-weight: bold;'>#$orderId</span></li>
                <li style='margin-bottom: 8px; padding-top: 8px; border-top: 1px dashed #D1D5DB;'>
                    <strong>Total Amount Paid:</strong> 
                    <span style='float: right; color: #10B981; font-weight: bold; font-size: 18px;'>PHP " . number_format($totalAmountPaid, 2) . "</span>
                </li>
            </ul>
        </div>
        
        <p>Your official **payment receipt is attached** to this email for your records.</p>
        <p>If you need to review the details of the products delivered, please click the button below:</p>

        <p style='text-align:center; margin: 30px 0;'>
            <a href='http://localhost/Tin_RCH-Techworks/customer/orders.php' 
               style='background-color: #2563EB; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 8px; display:inline-block; font-weight: bold; font-size: 16px;'>
               View Order Details
            </a>
        </p>

        <p>Thank you once again for choosing **RCH Water**! We appreciate your trust.</p>

        <p style='margin-top:20px;'>Warm regards,<br>RCH Water Team</p>
    </div>
    ";

    return sendEmail($email, $fullName, "Your Order #$orderId Has Been Delivered", $html, $receiptPath);
}

function emailPickupDelayedCustomer($email, $fullName, $orderNumber)
{
    $html = "
    <div style='font-family: Arial, sans-serif; line-height: 1.6; color:#1F2937; max-width: 600px; margin: auto; border: 1px solid #FEE2E2; padding: 20px; border-radius: 8px;'>
        <h2 style='color:#DC2626; border-bottom: 2px solid #FEE2E2; padding-bottom: 10px;'>Pickup Delayed for Order #$orderNumber</h2>
        <p>Hi $fullName,</p>
        <p>We are writing to inform you that the **scheduled pickup** for your order, **#$orderNumber**, has been slightly delayed due to unforeseen circumstances or route adjustments.</p>

        <div style='background-color: #FEF2F2; padding: 15px; border-radius: 6px; margin: 20px 0; border: 1px solid #FCA5A5;'>
            <p style='margin-top: 0; font-weight: bold; color: #B91C1C;'>What to Expect Next:</p>
            <ul style='padding-left: 20px; margin: 10px 0;'>
                <li>The rider has been notified, and we are working to complete your pickup as soon as possible.</li>
                <li>Your order status has been updated to **'Pickup Delayed'**.</li>
            </ul>
        </div>
        
        <p>You may view the updated status and estimated timeline by clicking the button below:</p>

        <p style='text-align:center; margin: 30px 0;'>
            <a href='http://localhost/Tin_RCH-Techworks/Order/order_details.php?order_number=$orderNumber'
               style='background-color:#2563EB; color:white; padding:12px 28px; text-decoration:none; border-radius:8px; font-weight:bold; display:inline-block; font-size: 16px;'>
               View Order Details
            </a>
        </p>

        <p>We sincerely apologize for the inconvenience this may cause and appreciate your understanding and patience.</p>
        <p style='margin-top:20px;'>Warm regards,<br>RCH Water Team</p>
    </div>";

    return sendEmail($email, $fullName, "⚠️ Pickup Delayed — Order #$orderNumber", $html);
}
function emailDeliveryDelayedCustomer($email, $fullName, $orderNumber)
{
    $html = "
    <div style='font-family: Arial, sans-serif; line-height: 1.6; color:#1F2937; max-width: 600px; margin: auto; border: 1px solid #FEE2E2; padding: 20px; border-radius: 8px;'>
        <h2 style='color:#DC2626; border-bottom: 2px solid #FEE2E2; padding-bottom: 10px;'> Important: Delivery Delayed for Order #$orderNumber</h2>
        <p>Hi $fullName,</p>
        <p>We wanted to let you know that the scheduled delivery time for your order, **#$orderNumber**, may be extended. This is typically due to heavy traffic, weather conditions, or unforeseen complications on the rider's current route.</p>

        <div style='background-color: #FEF2F2; padding: 15px; border-radius: 6px; margin: 20px 0; border: 1px solid #FCA5A5;'>
            <p style='margin-top: 0; font-weight: bold; color: #B91C1C;'>Our Priority:</p>
            <p style='margin-bottom: 0;'>Your order is currently marked as **'Delivery Delayed'**. We are actively monitoring the situation and your rider is working hard to reach you safely and as quickly as possible.</p>
        </div>

        <p>For the most current information and to track your order's status, please click below:</p>

        <p style='text-align:center; margin: 30px 0;'>
            <a href='http://localhost/Tin_RCH-Techworks/Order/order_details.php?order_number=$orderNumber'
            style='background:#2563EB; color:white; padding:12px 28px; text-decoration:none; border-radius:8px; font-weight:bold; display:inline-block; font-size: 16px;'>
            View Real-Time Delivery Status
            </a>
        </p>

        <p>We apologize for the inconvenience and sincerely appreciate your patience and understanding.</p>
        <p style='margin-top:20px;'>Warm regards,<br>RCH Water Team</p>
    </div>";

    return sendEmail($email, $fullName, "Delivery Delayed — Order #$orderNumber", $html);
}
function emailPickupReminderCustomer($email, $fullName, $orderNumber, $scheduledPickupDatetime)
{
    // Format the date/time for easy readability in the email
    $formattedPickup = date("l, M d, Y \a\\t h:i A", strtotime($scheduledPickupDatetime));

    $html = "
    <div style='font-family: Arial, sans-serif; line-height: 1.6; color:#1F2937; max-width: 600px; margin: auto; border: 1px solid #DBEAFE; padding: 20px; border-radius: 8px;'>
        <h2 style='color:#2563EB; border-bottom: 2px solid #DBEAFE; padding-bottom: 10px;'>Your Water Refill Pickup Reminder</h2>
        <p>Hi $fullName,</p>
        <p>This is a friendly reminder regarding your water refill order **#$orderNumber**.</p>

        <div style='background-color: #EFF6FF; padding: 15px; border-radius: 6px; margin: 20px 0;'>
            <h3 style='margin-top: 0; color: #1E40AF; font-size: 18px;'>Scheduled Pickup Time</h3>
            <p style='font-size: 16px; font-weight: bold; color: #2563EB;'>$formattedPickup</p>
        </div>

        <p style='font-weight: bold;'>To ensure a smooth and quick process, please:</p>
        <ul style='padding-left: 20px; margin: 10px 0;'>
            <li>Ensure your empty gallon/s are ready and clean.</li>
            <li>Place them in a secure yet **easily accessible area** for the rider.</li>
            <li>Be available to confirm the pickup when the rider arrives.</li>
        </ul>

        <p>If you need to make any changes or verify details, please use the link below:</p>

        <p style='text-align:center; margin: 30px 0;'>
            <a href='http://localhost/Tin_RCH-Techworks/Order/order_details.php?order_number=$orderNumber'
               style='background:#2563EB; color:white; padding:12px 28px; border-radius:8px; text-decoration:none; font-weight:bold; display:inline-block; font-size: 16px;'>
               View Order Details
            </a>
        </p>

        <p>Thank you for choosing RCH Water! We look forward to refilling your order.</p>
        <p style='margin-top:20px;'>Warm regards,<br>RCH Water Team</p>
    </div>";

    return sendEmail($email, $fullName, "Pickup Reminder — Order #$orderNumber", $html);
}
function emailDeliveryReminderCustomer($email, $fullName, $orderNumber, $scheduledDeliveryDatetime)
{
    // Format the date/time for easy readability in the email
    $formattedDelivery = date("l, M d, Y \a\\t h:i A", strtotime($scheduledDeliveryDatetime));

    $html = "
    <div style='font-family: Arial, sans-serif; color:#1F2937; line-height:1.6; max-width: 600px; margin: auto; border: 1px solid #DBEAFE; padding: 20px; border-radius: 8px;'>
        <h2 style='color:#2563EB; border-bottom: 2px solid #DBEAFE; padding-bottom: 10px;'>Delivery Reminder for Your Order</h2>
        <p>Hi $fullName,</p>
        <p>We're sending a quick reminder that your order, **#$orderNumber**, is scheduled for delivery **tomorrow**.</p>

        <div style='background-color: #EFF6FF; padding: 15px; border-radius: 6px; margin: 20px 0;'>
            <h3 style='margin-top: 0; color: #1E40AF; font-size: 18px;'>Scheduled Delivery Time</h3>
            <p style='font-size: 16px; font-weight: bold; color: #2563EB;'>$formattedDelivery</p>
        </div>

        <p style='font-weight: bold;'>Please ensure the following for a smooth delivery:</p>
        <ul style='padding-left: 20px; margin: 10px 0;'>
            <li>You or an authorized person is available to receive the delivery.</li>
            <li>If you chose **Cash on Delivery (COD)**, please prepare the exact amount for payment.</li>
            <li>Clear the delivery area for the quick placement of your refill gallons.</li>
        </ul>

        <p>You can check the latest status of your rider's journey here:</p>

        <p style='text-align:center; margin: 30px 0;'>
            <a href='http://localhost/Tin_RCH-Techworks/Order/order_details.php?order_number=$orderNumber'
               style='background:#2563EB; color:white; padding:12px 28px; text-decoration:none; border-radius:8px; font-weight:bold; display:inline-block; font-size: 16px;'>
               Track Delivery
            </a>
        </p>

        <p>Thank you for choosing RCH Water!</p>
        <p style='margin-top:20px;'>Warm regards,<br>RCH Water Team</p>
    </div>";

    return sendEmail($email, $fullName, "Delivery Reminder — Order #$orderNumber", $html);
}
function notifyCustomerDeliveryCancelled($email, $fullName, $orderNumber, $reason = null)
{
    // Set a default reason if none is provided
    $cancellationReason = $reason ?
        "The reason for this cancellation is: **$reason**." :
        "The reason for the cancellation will be communicated to you shortly by our support team.";

    $html = "
        <div style='font-family: Arial, sans-serif; color:#1F2937; line-height:1.6; max-width: 600px; margin: auto; border: 1px solid #FEE2E2; padding: 20px; border-radius: 8px;'>
            <h2 style='color:#EF4444; border-bottom: 2px solid #FEE2E2; padding-bottom: 10px;'>🚨 Order Delivery Cancelled</h2>
            <p>Hi $fullName,</p>
            <p>We regret to inform you that the delivery for your order **#$orderNumber** has been **cancelled**.</p>

            <div style='background-color: #FEF2F2; padding: 15px; border-radius: 6px; margin: 20px 0; border: 1px solid #FCA5A5;'>
                <h3 style='margin-top: 0; color: #B91C1C; font-size: 18px;'>Cancellation Details</h3>
                <p>Order Number: **#$orderNumber**</p>
                <p>$cancellationReason</p>
            </div>

            <p>If you did not initiate this cancellation, please contact our support team immediately.</p>

            <p style='text-align:center; margin: 30px 0;'>
                <a href='http://localhost/Tin_RCH-Techworks/Order/order_details.php?order_number=$orderNumber'
                style='background:#EF4444; color:white; padding:12px 28px; text-decoration:none; border-radius:8px; font-weight:bold; display:inline-block; font-size: 16px;'>
                View Order Details
                </a>
            </p>

            <p>We apologize for any inconvenience this may cause.</p>
            <p style='margin-top:20px;'>Warm regards,<br>RCH Water Team</p>
        </div>";

    return sendEmail($email, $fullName, "Cancellation Notice — Order #$orderNumber", $html);
}
function notifyCustomerRescheduled($email, $fullName, $orderNumber, $rescheduleType, $oldDatetime, $newDatetime, $delayReason = null)
{
    // Format the date/time for readability
    $formattedOld = date("l, M d, Y \a\t h:i A", strtotime($oldDatetime));
    $formattedNew = date("l, M d, Y \a\t h:i A", strtotime($newDatetime));


    // Capitalize first letter of type
    $typeLabel = ucfirst($rescheduleType);

    // Determine message regarding the delay reason
    $reasonMessage = $delayReason ?
        "<p>Reason for Reschedule: <strong>$delayReason</strong></p>" :
        "<p>We apologize for the inconvenience and are working to ensure your order is updated accordingly.</p>";

    $html = "
    <div style='font-family: Arial, sans-serif; color:#1F2937; line-height:1.6; max-width: 600px; margin: auto; border: 1px solid #FFF3B3; padding: 20px; border-radius: 8px;'>
        <h2 style='color:#F59E0B; border-bottom: 2px solid #FFF3B3; padding-bottom: 10px;'>🕒 {$typeLabel} Reschedule for Order #$orderNumber</h2>
        <p>Hi $fullName,</p>
        <p>Please be advised that the <strong>$rescheduleType</strong> for your order <strong>#$orderNumber</strong> has been <strong>rescheduled</strong>.</p>

        <div style='background-color: #FFFBEB; padding: 15px; border-radius: 6px; margin: 20px 0; border: 1px solid #FCD34D;'>
            <h3 style='margin-top: 0; color: #D97706; font-size: 18px;'>Updated {$typeLabel} Schedule</h3>
            <p>Previous Scheduled Time: <span style='float: right; text-decoration: line-through;'>$formattedOld</span></p>
            <p style='padding-top: 8px; border-top: 1px dashed #FDE68A;'><strong>New Scheduled Time:</strong> <span style='float: right; font-weight: bold; color: #F59E0B;'>$formattedNew</span></p>
            $reasonMessage
        </div>

        <p>Please adjust your schedule accordingly. You can view the order details below:</p>

        <p style='text-align:center; margin: 30px 0;'>
            <a href='http://localhost/Tin_RCH-Techworks/Order/order_details.php?order_number=$orderNumber'
            style='background:#F59E0B; color:white; padding:12px 28px; text-decoration:none; border-radius:8px; font-weight:bold; display:inline-block; font-size: 16px;'>
            View Updated Details
            </a>
        </p>

        <p>Thank you for your understanding.</p>
        <p style='margin-top:20px;'>Warm regards,<br>RCH Water Team</p>
    </div>";

    return sendEmail($email, $fullName, "{$typeLabel} Rescheduled — Order #$orderNumber", $html);
}


// =====================================
//  ADMIN NOTIFICATIONS
// =====================================

$adminEmail = "admin@rchwater.com";
$adminName  = "Admin";


// Admin: New Order
function notifyAdminNewOrder($orderId, $customerName, $totalAmount, $pickupDatetime, $deliveryDatetime)
{
    global $adminEmail, $adminName;

    // Optional: Format datetime strings for better readability in the email
    $formattedPickup = date("M d, Y h:i A", strtotime($pickupDatetime));
    $formattedDelivery = date("M d, Y h:i A", strtotime($deliveryDatetime));

    $html = "
    <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #1F2937; max-width: 600px; margin: auto; border: 1px solid #ddd; padding: 20px; border-radius: 8px;'>
        <h2 style='color: #DC2626; border-bottom: 2px solid #eee; padding-bottom: 10px;'>New Water Refill Order Received</h2>
        <p>Hello Admin,</p>
        <p>A new water refill order has been successfully submitted by a customer and requires your attention.</p>

        <div style='background-color: #f9f9f9; padding: 15px; border-radius: 6px; margin: 20px 0;'>
            <h3 style='margin-top: 0; color: #333; font-size: 18px;'>Order Summary</h3>
            <ul style='list-style: none; padding: 0; margin: 0;'>
                <li style='margin-bottom: 8px;'><strong>Order ID:</strong> <span style='float: right;'>#$orderId</span></li>
                <li style='margin-bottom: 8px;'><strong>Customer:</strong> <span style='float: right;'>$customerName</span></li>
                <li style='margin-bottom: 8px;'><strong>Total Amount:</strong> <span style='float: right; color: #10B981; font-weight: bold;'>PHP " . number_format($totalAmount, 2) . "</span></li>
                <li style='margin-bottom: 8px; padding-top: 8px; border-top: 1px dashed #eee;'><strong>Requested Pickup:</strong> <span style='float: right;'>$formattedPickup</span></li>
                <li><strong>Requested Delivery:</strong> <span style='float: right;'>$formattedDelivery</span></li>
            </ul>
        </div>

        <p style='margin-top: 20px;'>Please review the order details and assign a rider immediately.</p>

        <p style='text-align:center; margin: 30px 0;'>
            <a href='http://localhost/Tin_RCH-Techworks/order/order_details.php'
               style='background-color: #DC2626; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 8px; display:inline-block; font-weight: bold; font-size: 16px;'>
               View Order Details & Assign
            </a>
        </p>

        <p style='font-size: 12px; color: #777;'>Action is required to move this order forward.</p>
    </div>
    ";

    return sendEmail($adminEmail, $adminName, "NEW ORDER ALERT — #$orderId", $html);
}
// Admin: Pending deadlines
function notifyAdminPendingDeadlines($pendingCount, $pendingSummary = null)
{
    global $adminEmail, $adminName;

    $summaryHtml = "";
    if ($pendingSummary) {
        // Assuming $pendingSummary is an HTML list or string detailing the critical orders
        $summaryHtml = "
        <div style='background-color: #FFFBEB; padding: 15px; border-radius: 6px; border: 1px solid #FCD34D; margin: 20px 0;'>
            <h3 style='margin-top: 0; color: #B45309; font-size: 16px;'>Critical Orders Summary:</h3>
            {$pendingSummary}
            <p style='margin: 0; font-size: 14px; font-style: italic;'>Please check the full list on the admin dashboard.</p>
        </div>";
    }

    $html = "
    <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #1F2937; max-width: 600px; margin: auto; border: 1px solid #FDE68A; padding: 20px; border-radius: 8px;'>
        <h2 style='color: #F59E0B; border-bottom: 2px solid #FDE68A; padding-bottom: 10px;'>Urgent: {$pendingCount} Orders Approaching Deadline</h2>
        <p>Hello Admin,</p>
        <p>This automated alert indicates that **{$pendingCount} order(s)** are currently in a pending state and are nearing their scheduled pickup or delivery deadlines. </p>

        <div style='margin: 20px 0;'>
            <ul style='list-style: none; padding: 0; margin: 0;'>
                <li style='margin-bottom: 8px;'><strong>Total Critical Pending Orders:</strong> <span style='float: right; font-weight: bold; color: #F59E0B;'>{$pendingCount}</span></li>
                <li><strong>Required Action:</strong> <span style='float: right;'>Immediate Review & Assignment</span></li>
            </ul>
        </div>
        
        {$summaryHtml}

        <p>To prevent late fulfillment and maintain customer satisfaction, please prioritize the review and processing of these orders.</p>

        <p style='text-align:center; margin: 30px 0;'>
            <a href='http://localhost/Tin_RCH-Techworks/admin/orders.php'
               style='background-color: #F59E0B; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 8px; display:inline-block; font-weight: bold; font-size: 16px;'>
               Go to Order Management
            </a>
        </p>

        <p style='font-size: 12px; color: #777;'>This is an automated notification. Please do not reply.</p>
    </div>
    ";

    return sendEmail($adminEmail, $adminName, "🚨 URGENT: {$pendingCount} Orders Near Deadline", $html);
}

function notifyAdminOrderIssue($orderId, $customerName, $issue)
{
    global $adminEmail, $adminName;

    $html = "
    <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #1F2937;'>
        <h2 style='color: #DC2626;'>Customer Reported an Issue</h2>

        <p>A customer has reported a problem regarding their order.</p>

        <ul style='margin-left:20px;'>
            <li><strong>Order ID:</strong> #$orderId</li>
            <li><strong>Customer:</strong> $customerName</li>
            <li><strong>Issue:</strong> $issue</li>
        </ul>

        <p style='text-align:center; margin: 25px 0;'>
            <a href='http://localhost/Tin_RCH-Techworks/admin/issues.php'
               style='background-color: #DC2626; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 8px; display:inline-block; font-weight: bold;'>
               Review Issue
            </a>
        </p>

        <p>Admin action is needed.</p>
    </div>
    ";

    return sendEmail($adminEmail, $adminName, "Issue Reported — Order #$orderId", $html);
}

function notifyAdminOrderCancelled($refillId, $orderNumber, $customerName, $cancelReason = "No reason provided")
{
    global $adminEmail, $adminName;

    $html = "
    <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #1F2937;'>
        <h2 style='color: #DC2626;'>Order Cancelled by Customer</h2>

        <p>A customer has cancelled their water refill order. Below are the details:</p>

        <ul style='margin-left:20px;'>
            <li><strong>Refill ID:</strong> #$refillId</li>
            <li><strong>Order Number:</strong> $orderNumber</li>
            <li><strong>Customer:</strong> $customerName</li>
            <li><strong>Cancellation Reason:</strong> $cancelReason</li>
        </ul>

        <p style='text-align:center; margin: 25px 0;'>
            <a href='http://localhost/Tin_RCH-Techworks/admin/orders.php?filter=cancelled'
               style='background-color: #DC2626; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 8px; display:inline-block; font-weight: bold;'>
               Review Cancelled Order
            </a>
        </p>

        <p>Please review the details and take the necessary action if needed.</p>
    </div>
    ";

    return sendEmail(
        $adminEmail,
        $adminName,
        "Order Cancelled — #$orderNumber",
        $html
    );
}


// =====================================
//  RIDER NOTIFICATIONS
// =====================================
function notifyRiderAssignedOrder($email, $fullName, $orderNumber, $scheduledDeliveryDatetime, $deliveryAddress)
{
    // Format the delivery date/time for email
    $formattedDelivery = date("l, M d, Y \a\t h:i A", strtotime($scheduledDeliveryDatetime));

    $html = "
        <div style='font-family: Arial, sans-serif; line-height:1.6; color:#1F2937; max-width: 600px; margin: auto; border: 1px solid #DBEAFE; padding: 20px; border-radius: 8px;'>
            <h2 style='color:#2563EB; border-bottom: 2px solid #DBEAFE; padding-bottom: 10px;'>Delivery Task Assigned</h2>
            <p>Hello $fullName,</p>
            <p>You have been assigned to deliver order <strong>#$orderNumber</strong>.</p>

            <div style='background-color: #EFF6FF; padding: 15px; border-radius: 6px; margin: 20px 0;'>
                <h3 style='margin-top: 0; color: #1E40AF; font-size: 18px;'>Delivery Details</h3>
                <ul style='list-style: none; padding: 0; margin: 0;'>
                    <li style='margin-bottom: 8px;'><strong>Order Number:</strong> <span style='float: right; font-weight: bold;'>#$orderNumber</span></li>
                    <li style='margin-bottom: 8px; padding-top: 8px; border-top: 1px dashed #D1D5DB;'><strong>Scheduled Time:</strong> <span style='float: right; font-weight: bold; color: #2563EB;'>$formattedDelivery</span></li>
                    <li><strong>Delivery Address:</strong> <span style='float: right; max-width: 70%; text-align: right;'>$deliveryAddress</span></li>
                </ul>
            </div>

            <p>Please review the delivery location and ensure the order is ready for transport.</p>

            <p style='text-align:center; margin: 30px 0;'>
                <a href='http://localhost/Tin_RCH-Techworks/Rider/order_details.php?order_number=$orderNumber'
                style='background:#2563EB; color:white; padding:12px 28px; border-radius:8px; text-decoration:none; font-weight:bold; display:inline-block; font-size: 16px;'>
                View Delivery Task
                </a>
            </p>

            <p>Thank you for your service and safe travels.</p>
            <p style='margin-top:20px;'>RCH Water Admin Team</p>
        </div>";

    return sendEmail($email, $fullName, "Delivery Task Assigned — Order #$orderNumber", $html);
}

function emailPickupDelayedRider($email, $fullName, $orderNumber, $originalPickupDatetime)
{
    // Format the original time for clear reference
    $formattedOriginalTime = date("h:i A \o\\n M d, Y", strtotime($originalPickupDatetime));

    $html = "
    <div style='font-family: Arial, sans-serif; line-height: 1.6; color:#1F2937; max-width: 600px; margin: auto; border: 1px solid #DC2626; padding: 20px; border-radius: 8px;'>
        <h2 style='color:#DC2626; border-bottom: 2px solid #FCA5A5; padding-bottom: 10px;'>Pickup Delay Notice: Order #$orderNumber</h2>
        <p>Hi $fullName,</p>
        <p>The system has marked the pickup for your assigned order **#$orderNumber** as **DELAYED**.</p>

        <div style='background-color: #FEF2F2; padding: 15px; border-radius: 6px; margin: 20px 0; border: 1px solid #FCA5A5;'>
            <h3 style='margin-top: 0; color: #B91C1C; font-size: 16px;'>Important Details</h3>
            <ul style='list-style: none; padding: 0; margin: 0;'>
                <li style='margin-bottom: 8px;'><strong>Order Status:</strong> <span style='float: right; font-weight: bold;'>Pickup Delayed</span></li>
                <li style='margin-bottom: 8px;'><strong>Original Time:</strong> <span style='float: right;'>$formattedOriginalTime</span></li>
                <li><strong>Action:</strong> <span style='float: right; color: #DC2626; font-weight: bold;'>DO NOT proceed with pickup until confirmation.</span></li>
            </ul>
        </div>
        
        <p>Please check your rider dashboard immediately. If you have already left for the location, **contact the dispatcher/admin** for new instructions or an updated schedule.</p>

        <p style='text-align:center; margin: 30px 0;'>
            <a href='http://localhost/Tin_RCH-Techworks/Rider/order_details.php?order_number=$orderNumber'
               style='background-color:#2563EB; color:white; padding:12px 28px; text-decoration:none; border-radius:8px; font-weight:bold; display:inline-block; font-size: 16px;'>
               View Full Order Details
            </a>
        </p>

        <p>Adjust your task list accordingly.</p>
        <p style='margin-top:20px;'>RCH Water Admin</p>
    </div>";

    return sendEmail($email, $fullName, "Pickup Delayed — Order #$orderNumber", $html);
}
function emailDeliveryDelayedRider($email, $fullName, $orderNumber, $originalDeliveryDatetime)
{
    // Format the original time for clear reference
    $formattedOriginalTime = date("h:i A \o\\n M d, Y", strtotime($originalDeliveryDatetime));

    $html = "
    <div style='font-family: Arial, sans-serif; color:#1F2937; line-height:1.6; max-width: 600px; margin: auto; border: 1px solid #DC2626; padding: 20px; border-radius: 8px;'>
        <h2 style='color:#DC2626; border-bottom: 2px solid #FCA5A5; padding-bottom: 10px;'>Delivery Delay Notice: Order #$orderNumber</h2>
        <p>Hi $fullName,</p>
        <p>The delivery for your assigned order **#$orderNumber** is now flagged as **DELAYED** by the system.</p>

        <div style='background-color: #FEF2F2; padding: 15px; border-radius: 6px; margin: 20px 0; border: 1px solid #FCA5A5;'>
            <h3 style='margin-top: 0; color: #B91C1C; font-size: 16px;'>Operational Check</h3>
            <ul style='list-style: none; padding: 0; margin: 0;'>
                <li style='margin-bottom: 8px;'><strong>Order Status:</strong> <span style='float: right; font-weight: bold;'>Delivery Delayed</span></li>
                <li style='margin-bottom: 8px;'><strong>Original Delivery Time:</strong> <span style='float: right;'>$formattedOriginalTime</span></li>
                <li><strong>Current Action:</strong> <span style='float: right; color: #DC2626; font-weight: bold;'>Update the status as soon as possible.</span></li>
            </ul>
        </div>
        
        <p>If you encounter unexpected obstacles (e.g., severe traffic, vehicle trouble), please ensure you **contact the Admin/Dispatch** immediately for assistance and to communicate any estimated new delivery time to the customer.</p>

        <p style='text-align:center; margin: 30px 0;'>
            <a href='http://localhost/Tin_RCH-Techworks/Rider/order_details.php?order_number=$orderNumber'
               style='background:#2563EB; color:white; padding:12px 28px; text-decoration:none; border-radius:8px; font-weight:bold; display:inline-block; font-size: 16px;'>
               View Full Order Details
            </a>
        </p>

        <p>Thank you for your quick response.</p>
        <p style='margin-top:20px;'>RCH Water Admin</p>
    </div>";

    return sendEmail($email, $fullName, "Delivery Delayed — Order #$orderNumber", $html);
}
function emailPickupReminderRider($email, $fullName, $orderNumber, $scheduledPickupDatetime, $pickupAddress)
{
    // Format the date/time for easy readability in the email
    $formattedPickup = date("l, M d, Y \a\\t h:i A", strtotime($scheduledPickupDatetime));

    $html = "
    <div style='font-family: Arial, sans-serif; line-height:1.6; color:#1F2937; max-width: 600px; margin: auto; border: 1px solid #DBEAFE; padding: 20px; border-radius: 8px;'>
        <h2 style='color:#2563EB; border-bottom: 2px solid #DBEAFE; padding-bottom: 10px;'>Tomorrow's Pickup Task Reminder</h2>
        <p>Hello $fullName,</p>
        <p>This is a reminder for your assigned water refill pickup task scheduled for tomorrow.</p>

        <div style='background-color: #EFF6FF; padding: 15px; border-radius: 6px; margin: 20px 0;'>
                <h3 style='margin-top: 0; color: #1E40AF; font-size: 18px;'>Task Details</h3>
                <ul style='list-style: none; padding: 0; margin: 0;'>
                    <li style='margin-bottom: 8px;'><strong>Order Number:</strong> <span style='float: right; font-weight: bold;'>#$orderNumber</span></li>
                    <li style='margin-bottom: 8px; padding-top: 8px; border-top: 1px dashed #D1D5DB;'><strong>Scheduled Time:</strong> <span style='float: right; font-weight: bold; color: #2563EB;'>$formattedPickup</span></li>
                    <li><strong>Pickup Location:</strong> <span style='float: right; max-width: 70%; text-align: right;'>$pickupAddress</span></li>
                </ul>
            </div>
            
            <p>Please review the details and confirm the route for efficient service delivery.</p>

            <p style='text-align:center; margin: 30px 0;'>
                <a href='http://localhost/Tin_RCH-Techworks/Rider/order_details.php?order_number=$orderNumber'
                style='background:#2563EB; color:white; padding:12px 28px; border-radius:8px; text-decoration:none; font-weight:bold; display:inline-block; font-size: 16px;'>
                View Full Task Details
                </a>
            </p>

            <p>Thank you for your service and drive safely.</p>
            <p style='margin-top:20px;'>RCH Water Admin Team</p>
        </div>";

    return sendEmail($email, $fullName, "Pickup Task Reminder — Order #$orderNumber", $html);
}

function emailDeliveryReminderRider($email, $fullName, $orderNumber, $scheduledDeliveryDatetime, $deliveryAddress)
{
    // Format the date/time for easy readability in the email
    $formattedDelivery = date("l, M d, Y \a\\t h:i A", strtotime($scheduledDeliveryDatetime));

    $html = "
    <div style='font-family: Arial, sans-serif; line-height:1.6; color:#1F2937; max-width: 600px; margin: auto; border: 1px solid #DBEAFE; padding: 20px; border-radius: 8px;'>
        <h2 style='color:#2563EB; border-bottom: 2px solid #DBEAFE; padding-bottom: 10px;'>Tomorrow's Delivery Task Reminder</h2>
        <p>Hello $fullName,</p>
        <p>This is a reminder that you are scheduled for the delivery of order **#$orderNumber** tomorrow.</p>

        <div style='background-color: #EFF6FF; padding: 15px; border-radius: 6px; margin: 20px 0;'>
            <h3 style='margin-top: 0; color: #1E40AF; font-size: 18px;'>Delivery Details</h3>
            <ul style='list-style: none; padding: 0; margin: 0;'>
                <li style='margin-bottom: 8px;'><strong>Order Number:</strong> <span style='float: right; font-weight: bold;'>#$orderNumber</span></li>
                <li style='margin-bottom: 8px; padding-top: 8px; border-top: 1px dashed #D1D5DB;'><strong>Scheduled Time:</strong> <span style='float: right; font-weight: bold; color: #2563EB;'>$formattedDelivery</span></li>
                <li><strong>Delivery Address:</strong> <span style='float: right; max-width: 70%; text-align: right;'>$deliveryAddress</span></li>
            </ul>
        </div>
        
        <p>Please review the delivery location and ensure the order is fully prepared for transport.</p>

        <p style='text-align:center; margin: 30px 0;'>
            <a href='http://localhost/Tin_RCH-Techworks/Rider/order_details.php?order_number=$orderNumber'
               style='background:#2563EB; color:white; padding:12px 28px; border-radius:8px; text-decoration:none; font-weight:bold; display:inline-block; font-size: 16px;'>
               View Delivery Task
            </a>
        </p>

        <p>Thank you for your service and safe travels.</p>
        <p style='margin-top:20px;'>RCH Water Admin Team</p>
    </div>";

    return sendEmail($email, $fullName, "Delivery Task Reminder — Order #$orderNumber", $html);
}
function notifyRiderRescheduled($email, $riderName, $orderNumber, $rescheduleType, $oldDatetime, $newDatetime, $delayReason = null)
{
    // Format the date/time for readability
    $formattedOld = date("l, M d, Y \a\t h:i A", strtotime($oldDatetime));
    $formattedNew = date("l, M d, Y \a\t h:i A", strtotime($newDatetime));

    // Capitalize first letter of type
    $typeLabel = ucfirst($rescheduleType);

    // Determine message regarding the delay reason
    $reasonMessage = $delayReason ?
        "<p>Reason for Reschedule: <strong>$delayReason</strong></p>" :
        "<p>Please be aware and adjust your schedule accordingly.</p>";

    $html = "
    <div style='font-family: Arial, sans-serif; color:#1F2937; line-height:1.6; max-width: 600px; margin: auto; border: 1px solid #FFF3B3; padding: 20px; border-radius: 8px;'>
        <h2 style='color:#F59E0B; border-bottom: 2px solid #FFF3B3; padding-bottom: 10px;'>🕒 {$typeLabel} Reschedule for Order #$orderNumber</h2>
        <p>Hi $riderName,</p>
        <p>The <strong>$rescheduleType</strong> for order <strong>#$orderNumber</strong> has been <strong>rescheduled</strong>. Please see the updated schedule below:</p>

        <div style='background-color: #FFFBEB; padding: 15px; border-radius: 6px; margin: 20px 0; border: 1px solid #FCD34D;'>
            <h3 style='margin-top: 0; color: #D97706; font-size: 18px;'>Updated {$typeLabel} Schedule</h3>
            <p>Previous Scheduled Time: <span style='float: right; text-decoration: line-through;'>$formattedOld</span></p>
            <p style='padding-top: 8px; border-top: 1px dashed #FDE68A;'><strong>New Scheduled Time:</strong> <span style='float: right; font-weight: bold; color: #F59E0B;'>$formattedNew</span></p>
            $reasonMessage
        </div>

        <p>Make sure to update your route and plan accordingly. You can view the order details below:</p>

        <p style='text-align:center; margin: 30px 0;'>
            <a href='http://localhost/Tin_RCH-Techworks/Order/order_details.php?order_number=$orderNumber'
            style='background:#F59E0B; color:white; padding:12px 28px; text-decoration:none; border-radius:8px; font-weight:bold; display:inline-block; font-size: 16px;'>
            View Updated Details
            </a>
        </p>

        <p>Thank you for your cooperation.</p>
        <p style='margin-top:20px;'>Warm regards,<br>RCH Water Team</p>
    </div>";

    return sendEmail($email, $riderName, "{$typeLabel} Rescheduled — Order #$orderNumber", $html);
}

function notifyRiderDeliveryCancelled($email, $fullName, $orderNumber, $reason = null)
{
    // Set a default reason if none is provided
    $cancellationReason = $reason ?
        "The reason for this cancellation is: **$reason**." :
        "The reason for the cancellation will be communicated to you shortly by our support team.";

    $html = "
        <div style='font-family: Arial, sans-serif; color:#1F2937; line-height:1.6; max-width: 600px; margin: auto; border: 1px solid #DBEAFE; padding: 20px; border-radius: 8px;'>
            <h2 style='color:#3B82F6; border-bottom: 2px solid #DBEAFE; padding-bottom: 10px;'>⚠️ Assigned Order Cancelled</h2>
            <p>Hi $fullName,</p>
            <p>We wanted to inform you that the order **#$orderNumber** assigned to you has been **cancelled**.</p>

            <div style='background-color: #DBEAFE; padding: 15px; border-radius: 6px; margin: 20px 0; border: 1px solid #93C5FD;'>
                <h3 style='margin-top: 0; color: #1E40AF; font-size: 18px;'>Cancellation Details</h3>
                <p>Order Number: **#$orderNumber**</p>
                <p>$cancellationReason</p>
            </div>

            <p>If you have already prepared for this order, please disregard or notify your supervisor if necessary.</p>

            <p style='text-align:center; margin: 30px 0;'>
                <a href='http://localhost/Tin_RCH-Techworks/Rider/order_details.php?order_number=$orderNumber'
                style='background:#3B82F6; color:white; padding:12px 28px; text-decoration:none; border-radius:8px; font-weight:bold; display:inline-block; font-size: 16px;'>
                View Order Details
                </a>
            </p>

            <p>Thank you for your attention.</p>
            <p style='margin-top:20px;'>Warm regards,<br>RCH Water Team</p>
        </div>";

    return sendEmail($email, $fullName, "Assigned Order Cancelled — Order #$orderNumber", $html);
}
?>
