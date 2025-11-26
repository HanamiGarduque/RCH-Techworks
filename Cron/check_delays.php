<?php

require_once '../Database/db_connection.php';
require_once '../Notification/email.php';
require_once '../Notification/send_notification.php';


$database = new Database();
$db = $database->getConnect();

// Get current date/time
$now = date("Y-m-d H:i:s");

function getUserDetails($db, $userId) {
    if (!$userId) return null;
    $stmt = $db->prepare("SELECT email, name FROM users WHERE user_id = :id LIMIT 1");
    $stmt->execute([':id' => $userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC); 
}

// Function to fetch order details for delays/reminders
function getOrderDetails($db, $refillId) {
    $stmt = $db->prepare("
        SELECT pickup_datetime, delivery_datetime, total_amount
        FROM water_refills WHERE refill_id = :id
    ");
    $stmt->execute([':id' => $refillId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// ----------------------------------------------------------------------
// AUTO SET PICKUP_DELAYED
// ----------------------------------------------------------------------
$pickupQuery = "
    SELECT refill_id, user_id, rider_id, order_number, pickup_datetime 
    FROM water_refills
    WHERE pickup_datetime < :now
    AND order_status IN ('pending', 'assigned', 'pickup_in_progress')
";

$stmt = $db->prepare($pickupQuery);
$stmt->execute([':now' => $now]);
$pickupOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($pickupOrders as $order) {
    // 1. Update status
    $update = $db->prepare("UPDATE water_refills SET order_status = 'pickup_delayed' WHERE refill_id = :id");
    $update->execute([':id' => $order['refill_id']]);

    $link_customer = "../Customer/order_details.php?order_number={$order['order_number']}";
    $link_rider = "../Rider/order_details.php?order_number={$order['order_number']}";

    $customer = getUserDetails($db, $order['user_id']);
    
    // 2. Notify Customer (Email now requires pickup_datetime for context)
    if ($customer) {
        createNotification(
            $db, $order['user_id'], 
            "Pickup Delayed", 
            "Your order #{$order['order_number']} pickup is delayed.",
            $link_customer
        );
        // Send Email (Uses the new expanded function signature)
        // We need pickup_datetime for the expanded customer email, but the expanded version only took orderNumber.
        // I will use the simpler one as intended by the original expanded function, but ensure the rider one is correct.
        emailPickupDelayedCustomer($customer['email'], $customer['name'], $order['order_number']);
    }

    // 3. Notify Rider (Email now requires pickup_datetime)
    if ($order['rider_id']) {
        $rider = getUserDetails($db, $order['rider_id']);
        if ($rider) {
            createNotification(
                $db, $order['rider_id'], 
                "Pickup Delayed", 
                "Pickup for order #{$order['order_number']} is delayed.",
                $link_rider
            );
            // Send Email (Uses the new expanded function signature)
            emailPickupDelayedRider($rider['email'], $rider['name'], $order['order_number'], $order['pickup_datetime']);
        }
    }
}
// ----------------------------------------------------------------------
//  AUTO SET DELIVERY_DELAYED
// ----------------------------------------------------------------------
$deliveryQuery = "
    SELECT refill_id, user_id, rider_id, order_number, delivery_datetime
    FROM water_refills
    WHERE delivery_datetime < :now
    AND order_status IN ('picked_up', 'on_delivery')
";

$stmt = $db->prepare($deliveryQuery);
$stmt->execute([':now' => $now]);
$deliveryOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($deliveryOrders as $order) {
    // 1. Update status
    $update = $db->prepare("UPDATE water_refills SET order_status = 'delivery_delayed' WHERE refill_id = :id");
    $update->execute([':id' => $order['refill_id']]);

    $link_customer = "Order/order_details.php?order_number={$order['order_number']}";
    $link_rider = "Rider/order_details.php?order_number={$order['order_number']}";

    $customer = getUserDetails($db, $order['user_id']);

    // 2. Notify Customer (Notification + Email)
    if ($customer) {
        // Save Notification
        createNotification($db, $order['user_id'],
            "Delivery Delayed",
            "Your order #{$order['order_number']} delivery is delayed.",
            $link_customer
        );
        // Send Email (Uses the new expanded function signature)
        // Customer email expanded version only took orderNumber, as per your request
        emailDeliveryDelayedCustomer($customer['email'], $customer['name'], $order['order_number']);
    }

    // 3. Notify Rider (Notification + Email)
    if ($order['rider_id']) {
        $rider = getUserDetails($db, $order['rider_id']);
        if ($rider) {
            // Save Notification
            createNotification($db, $order['rider_id'],
                "Delivery Delayed",
                "Delivery for order #{$order['order_number']} is delayed.",
                $link_rider
            );
            // Send Email (Uses the new expanded function signature)
            emailDeliveryDelayedRider($rider['email'], $rider['name'], $order['order_number'], $order['delivery_datetime']);
        }
    }
}

// ----------------------------------------------------------------------
//  REMINDER: 1 DAY BEFORE PICKUP
// ----------------------------------------------------------------------
$pickupReminderQuery = "
    SELECT refill_id, user_id, rider_id, order_number, pickup_datetime
    FROM water_refills
    WHERE DATE(pickup_datetime) = DATE(DATE_ADD(NOW(), INTERVAL 1 DAY))
    AND order_status NOT IN ('cancelled', 'completed', 'pickup_delayed')
";

$stmt = $db->prepare($pickupReminderQuery);
$stmt->execute();
$pickupReminders = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($pickupReminders as $order) {
    $link_customer = "Order/order_details.php?order_number={$order['order_number']}";
    $link_rider = "Rider/order_details.php?order_number={$order['order_number']}";
    
    // --- NEW EMAIL AND NOTIFICATION LOGIC ---
    $customer = getUserDetails($db, $order['user_id']);

    // Notify Customer (Notification + Email)
    if ($customer) {
        // Save Notification
        createNotification($db, $order['user_id'],
            "Pickup Tomorrow",
            "Reminder: Your order #{$order['order_number']} pickup is scheduled for tomorrow.",
            $link_customer
        );
        // Send Email (Uses the new expanded function signature)
        emailPickupReminderCustomer($customer['email'], $customer['name'], $order['order_number'], $order['pickup_datetime']);
    }

    // Notify Rider (Notification + Email)
    if ($order['rider_id']) {
        $rider = getUserDetails($db, $order['rider_id']);
        if ($rider) {
            // Save Notification
            createNotification($db, $order['rider_id'],
                "Pickup Tomorrow",
                "Reminder: Order #{$order['order_number']} is scheduled for pickup tomorrow.",
                $link_rider
            );
            // Send Email (Uses the new expanded function signature)
            emailPickupReminderRider($rider['email'], $rider['name'], $order['order_number'], $order['pickup_datetime'], $order['delivery_address']);
        }
    }
}

// ----------------------------------------------------------------------
//  REMINDER: 1 DAY BEFORE DELIVERY
// ----------------------------------------------------------------------
$deliveryReminderQuery = "
    SELECT refill_id, user_id, rider_id, order_number, delivery_datetime
    FROM water_refills
    WHERE DATE(delivery_datetime) = DATE(DATE_ADD(NOW(), INTERVAL 1 DAY))
    AND order_status NOT IN ('cancelled', 'completed', 'delivery_delayed', 'pickup_delayed')
";

$stmt = $db->prepare($deliveryReminderQuery);
$stmt->execute();
$deliveryReminders = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($deliveryReminders as $order) {
    $link_customer = "Order/order_details.php?order_number={$order['order_number']}";
    $link_rider = "Rider/order_details.php?order_number={$order['order_number']}";

    // --- NEW EMAIL AND NOTIFICATION LOGIC ---
    $customer = getUserDetails($db, $order['user_id']);

    // Notify Customer (Notification + Email)
    if ($customer) {
        // Save Notification
        createNotification($db, $order['user_id'],
            "Delivery Tomorrow",
            "Reminder: Your order #{$order['order_number']} delivery is scheduled for tomorrow.",
            $link_customer
        );
        // Send Email (Uses the new expanded function signature)
        emailDeliveryReminderCustomer($customer['email'], $customer['name'], $order['order_number'], $order['delivery_datetime']);
    }

    // Notify Rider (Notification + Email)
    if ($order['rider_id']) {
        $rider = getUserDetails($db, $order['rider_id']);
        if ($rider) {
            // Save Notification
            createNotification($db, $order['rider_id'],
                "Delivery Tomorrow",
                "Reminder: Order #{$order['order_number']} is scheduled for delivery tomorrow.",
                $link_rider
            );
            // Send Email (Uses the new expanded function signature)
            emailDeliveryReminderRider($rider['email'], $rider['name'], $order['order_number'], $order['delivery_datetime'], $order['delivery_address']);
        }
    }
}

?>