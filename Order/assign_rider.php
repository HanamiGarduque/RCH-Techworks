<?php
// Order/assign_rider.php - WHOLE REVISED FILE
// Fix: Email / Brevo 401 error was echoing before JSON and breaking Assign Rider.
// Now: all email output is captured and logged, never echoed. Only ONE clean JSON is returned.
ob_start();
error_reporting(0);
ini_set('display_errors', '0');

require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('admin'); // only admins can access

require_once '../Database/db_connection.php';
require_once '../Notification/send_notification.php';
require_once '../Notification/email.php'; // Assuming email functions are here

header('Content-Type: application/json; charset=utf-8');

// Clear anything those includes may have echoed
if (ob_get_length()) ob_clean();

$database = new Database();
$db = $database->getConnect();

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);
$orderNumber = $data['order_number'] ?? null;
$riderId = $data['rider_id'] ?? null;

// Validate inputs
if (!$orderNumber || !$riderId) {
    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => false, 'message' => 'Order number and rider ID are required.']);
    exit;
}

try {
    $db->beginTransaction();

    $checkOrder = $db->prepare("SELECT user_id, order_status, delivery_datetime FROM water_refills WHERE order_number = :order_number LIMIT 1");
    $checkOrder->bindParam(':order_number', $orderNumber);
    $checkOrder->execute();
    
    if ($checkOrder->rowCount() === 0) {
        $db->rollBack();
        if (ob_get_length()) ob_clean();
        echo json_encode(['success' => false, 'message' => 'Order not found.']);
        exit;
    }

    $order = $checkOrder->fetch(PDO::FETCH_ASSOC);
    $customerId = $order['user_id'];
    $scheduledDeliveryDatetime = $order['delivery_datetime'];

    // Check if order is already cancelled or delivered
    if (in_array($order['order_status'], ['cancelled', 'delivered'])) {
        $db->rollBack();
        if (ob_get_length()) ob_clean();
        echo json_encode(['success' => false, 'message' => 'Cannot assign rider to a ' . $order['order_status'] . ' order.']);
        exit;
    }
    
    // 2. Fetch the Customer's Address
    $getAddress = $db->prepare("SELECT address FROM users WHERE user_id = :customer_id AND role = 'customer' LIMIT 1");
    $getAddress->bindParam(':customer_id', $customerId, PDO::PARAM_INT);
    $getAddress->execute();
    
    if ($getAddress->rowCount() === 0) {
        $db->rollBack();
        if (ob_get_length()) ob_clean();
        echo json_encode(['success' => false, 'message' => 'Customer user record not found.']);
        exit;
    }
    
    $customer = $getAddress->fetch(PDO::FETCH_ASSOC);
    $deliveryAddress = $customer['address']; 

    $checkRider = $db->prepare("SELECT user_id, email, name FROM users WHERE user_id = :rider_id AND role = 'rider' AND status = 'active' LIMIT 1");
    $checkRider->bindParam(':rider_id', $riderId, PDO::PARAM_INT);
    $checkRider->execute();
    
    if ($checkRider->rowCount() === 0) {
        $db->rollBack();
        if (ob_get_length()) ob_clean();
        echo json_encode(['success' => false, 'message' => 'Invalid or inactive rider.']);
        exit;
    }
    
    $rider = $checkRider->fetch(PDO::FETCH_ASSOC);
    $riderEmail = $rider['email'];
    $riderFullName = $rider['name'];

    $newStatus = $order['order_status'] === 'pending' ? 'assigned' : $order['order_status'];
    
    $updateQuery = "UPDATE water_refills 
                    SET rider_id = :rider_id, 
                        order_status = :new_status 
                    WHERE order_number = :order_number";
    
    $stmt = $db->prepare($updateQuery);
    $stmt->bindParam(':rider_id', $riderId, PDO::PARAM_INT);
    $stmt->bindParam(':new_status', $newStatus);
    $stmt->bindParam(':order_number', $orderNumber);
    
    if ($stmt->execute()) {
        $db->commit();

        // --- Notification Logic ---
        $notificationSuccess = true;

        // 1. Notify Customer: Rider has been assigned
        $titleCustomer = "Order #$orderNumber Status Update";
        $messageCustomer = "A rider has been assigned to deliver your order. Check the details for updates!";
        $linkCustomer = "/Customer/order_details.php?order_number=$orderNumber"; 
        // Capture any stray echo from createNotification()
        ob_start();
        $customerNotif = createNotification($db, $customerId, $titleCustomer, $messageCustomer, $linkCustomer);
        $stray1 = ob_get_clean();
        if ($stray1 !== '') error_log('assign_rider: createNotification customer echoed: ' . $stray1);
        if (!$customerNotif) {
             $notificationSuccess = false;
        }

        // 2. Notify Rider (In-App): An Order has been assigned
        $titleRider = "New Delivery Assignment";
        $messageRider = "You have been assigned Order #$orderNumber for delivery.";
        $linkRider = "/Rider/order_details.php?order_number=$orderNumber";
        ob_start();
        $riderNotif = createNotification($db, $riderId, $titleRider, $messageRider, $linkRider);
        $stray2 = ob_get_clean();
        if ($stray2 !== '') error_log('assign_rider: createNotification rider echoed: ' . $stray2);
        if (!$riderNotif) {
             $notificationSuccess = false;
        }

        // 3. Email Notification to Rider using the fetched deliveryAddress
        // FIX: notifyRiderAssignedOrder() echoes "<b>Email send error: HTTP 401</b>" + Brevo JSON
        // That echo was breaking the JSON response. Capture it, log it, never echo it.
        $emailSuccess = false;
        try {
            ob_start();
            $emailSuccess = notifyRiderAssignedOrder($riderEmail, $riderFullName, $orderNumber, $scheduledDeliveryDatetime, $deliveryAddress);
            $emailOutput = ob_get_clean();
            if ($emailOutput !== '') {
                // This is the Brevo 401 error - log it for you to fix the API key later, don't show it to the browser
                error_log('assign_rider: email output captured (Brevo 401?): ' . $emailOutput);
            }
            // If function returned null / void but didn't error, treat as failed if it printed an error
            if ($emailOutput !== '' && stripos($emailOutput, 'error') !== false) {
                $emailSuccess = false;
            }
        } catch (Exception $e) {
            if (ob_get_length()) ob_clean();
            error_log('assign_rider: email exception: ' . $e->getMessage());
            $emailSuccess = false;
        } catch (Throwable $e) {
            if (ob_get_length()) ob_clean();
            error_log('assign_rider: email throwable: ' . $e->getMessage());
            $emailSuccess = false;
        }

        $notificationStatus = [
            'in_app' => $notificationSuccess ? 'Sent' : 'Failed',
            'email' => $emailSuccess ? 'Sent' : 'Failed'
        ];

        // Final output - ONLY this JSON, nothing else
        if (ob_get_length()) ob_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Rider assigned successfully.',
            'new_status' => $newStatus,
            'notifications_sent' => $notificationStatus
        ]);
    } else {
        $db->rollBack();
        if (ob_get_length()) ob_clean();
        echo json_encode(['success' => false, 'message' => 'Failed to assign rider.']);
    }

} catch (PDOException $e) {
    if ($db->inTransaction()) $db->rollBack();
    error_log('Database Error in assign_rider.php: ' . $e->getMessage());
    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    error_log('Error in assign_rider.php: ' . $e->getMessage());
    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => false, 'message' => 'An error occurred while assigning rider.']);
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    error_log('Throwable in assign_rider.php: ' . $e->getMessage());
    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => false, 'message' => 'An error occurred while assigning rider.']);
}