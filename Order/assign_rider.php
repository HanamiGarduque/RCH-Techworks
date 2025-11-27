<?php
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('admin'); // only admins can access

require_once '../Database/db_connection.php';
require_once '../Notification/send_notification.php';
require_once '../Notification/email.php'; // Assuming email functions are here

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnect();

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);
$orderNumber = $data['order_number'] ?? null;
$riderId = $data['rider_id'] ?? null;

// Validate inputs
if (!$orderNumber || !$riderId) {
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
        echo json_encode(['success' => false, 'message' => 'Order not found.']);
        exit;
    }

    $order = $checkOrder->fetch(PDO::FETCH_ASSOC);
    $customerId = $order['user_id'];
    $scheduledDeliveryDatetime = $order['delivery_datetime'];

    // Check if order is already cancelled or delivered
    if (in_array($order['order_status'], ['cancelled', 'delivered'])) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Cannot assign rider to a ' . $order['order_status'] . ' order.']);
        exit;
    }
    
    // 2. Fetch the Customer's Address
    $getAddress = $db->prepare("SELECT address FROM users WHERE user_id = :customer_id AND role = 'customer' LIMIT 1");
    $getAddress->bindParam(':customer_id', $customerId, PDO::PARAM_INT);
    $getAddress->execute();
    
    if ($getAddress->rowCount() === 0) {
        $db->rollBack();
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
        if (!createNotification($db, $customerId, $titleCustomer, $messageCustomer, $linkCustomer)) {
             $notificationSuccess = false;
        }

        // 2. Notify Rider (In-App): An Order has been assigned
        $titleRider = "New Delivery Assignment";
        $messageRider = "You have been assigned Order #$orderNumber for delivery.";
        $linkRider = "/Rider/order_details.php?order_number=$orderNumber";
        if (!createNotification($db, $riderId, $titleRider, $messageRider, $linkRider)) {
             $notificationSuccess = false;
        }

        // 3. Email Notification to Rider using the fetched deliveryAddress
        $emailSuccess = notifyRiderAssignedOrder($riderEmail, $riderFullName, $orderNumber, $scheduledDeliveryDatetime, $deliveryAddress);

        $notificationStatus = [
            'in_app' => $notificationSuccess ? 'Sent' : 'Failed',
            'email' => $emailSuccess ? 'Sent' : 'Failed'
        ];

        echo json_encode([
            'success' => true,
            'message' => 'Rider assigned successfully.',
            'new_status' => $newStatus,
            'notifications_sent' => $notificationStatus
        ]);
    } else {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to assign rider.']);
    }

} catch (PDOException $e) {
    $db->rollBack();
    error_log('Database Error in assign_rider.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
} catch (Exception $e) {
    $db->rollBack();
    error_log('Error in assign_rider.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while assigning rider.']);
}
?>