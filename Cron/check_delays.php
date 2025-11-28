<?php

// --- Database Connection Setup ---
require_once '../Database/db_connection.php';
// Ensure email.php and send_notification.php are accessible
require_once '../Notification/email.php';
require_once '../Notification/send_notification.php';

$database = new Database();
$db = $database->getConnect();

// Get current date/time
$now = date("Y-m-d H:i:s");

/**
 * Return user details (email, name) for a given user_id
 */
function getUserDetails($db, $userId) {
    if (!$userId) return null;
    $stmt = $db->prepare("SELECT email, name FROM users WHERE user_id = :id LIMIT 1");
    $stmt->execute([':id' => $userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Return order details along with delivery address from users.address (joined by user_id)
 * Returns associative array with keys:
 *  - pickup_datetime
 *  - delivery_datetime
 *  - total_amount
 *  - delivery_address
 */
function getOrderDetails($db, $refillId) {
    $sql = "
        SELECT 
            wr.pickup_datetime,
            wr.delivery_datetime,
            wr.total_amount,
            u.address AS delivery_address,
            wr.order_number,
            wr.user_id,
            wr.rider_id
        FROM water_refills wr
        INNER JOIN users u ON wr.user_id = u.user_id
        WHERE wr.refill_id = :id
        LIMIT 1
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $refillId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// ----------------------------------------------------------------------
// AUTO SET PICKUP_DELAYED
// ----------------------------------------------------------------------
$pickupQuery = "
    SELECT wr.refill_id, wr.user_id, wr.rider_id, wr.order_number, wr.pickup_datetime
    FROM water_refills wr
    WHERE wr.pickup_datetime < :now
    AND wr.order_status IN ('pending', 'assigned', 'pickup_in_progress')
";

$stmtPickup = $db->prepare($pickupQuery);
$stmtPickup->execute([':now' => $now]);
$pickupOrders = $stmtPickup->fetchAll(PDO::FETCH_ASSOC);

foreach ($pickupOrders as $order) {
    // 1. Update status
    $update = $db->prepare("UPDATE water_refills SET order_status = 'pickup_delayed' WHERE refill_id = :id");
    $update->execute([':id' => $order['refill_id']]);

    $link_customer = "Customer/order_details.php?order_number={$order['order_number']}";
    $link_rider = "Rider/order_details.php?order_number={$order['order_number']}";

    $customer = getUserDetails($db, $order['user_id']);
    
    // 2. Notify Customer (Notification + Email)
    if ($customer) {
        createNotification(
            $db, $order['user_id'], 
            "Pickup Delayed", 
            "Your order #{$order['order_number']} pickup is delayed.",
            $link_customer
        );
        // Send Email (customer-facing)
        emailPickupDelayedCustomer($customer['email'], $customer['name'], $order['order_number']);
    }

    // 3. Notify Rider (Notification + Email)
    if (!empty($order['rider_id'])) {
        $rider = getUserDetails($db, $order['rider_id']);
        if ($rider) {
            createNotification(
                $db, $order['rider_id'], 
                "Pickup Delayed", 
                "Pickup for order #{$order['order_number']} is delayed.",
                $link_rider
            );
            // Send Email to rider (includes pickup_datetime)
            emailPickupDelayedRider($rider['email'], $rider['name'], $order['order_number'], $order['pickup_datetime']);
        }
    }
}
// ----------------------------------------------------------------------
//  AUTO SET DELIVERY_DELAYED
// ----------------------------------------------------------------------
$deliveryQuery = "
    SELECT wr.refill_id, wr.user_id, wr.rider_id, wr.order_number, wr.delivery_datetime
    FROM water_refills wr
    WHERE wr.delivery_datetime < :now
    AND wr.order_status IN ('picked_up', 'on_delivery')
";

$stmtDelivery = $db->prepare($deliveryQuery);
$stmtDelivery->execute([':now' => $now]);
$deliveryOrders = $stmtDelivery->fetchAll(PDO::FETCH_ASSOC);

foreach ($deliveryOrders as $order) {
    // 1. Update status
    $update = $db->prepare("UPDATE water_refills SET order_status = 'delivery_delayed' WHERE refill_id = :id");
    $update->execute([':id' => $order['refill_id']]);

    $link_customer = "Customer/order_details.php?order_number={$order['order_number']}";
    $link_rider = "Rider/order_details.php?order_number={$order['order_number']}";

    $customer = getUserDetails($db, $order['user_id']);

    // 2. Notify Customer (Notification + Email)
    if ($customer) {
        createNotification($db, $order['user_id'],
            "Delivery Delayed",
            "Your order #{$order['order_number']} delivery is delayed.",
            $link_customer
        );
        emailDeliveryDelayedCustomer($customer['email'], $customer['name'], $order['order_number']);
    }

    // 3. Notify Rider (Notification + Email)
    if (!empty($order['rider_id'])) {
        $rider = getUserDetails($db, $order['rider_id']);
        if ($rider) {
            createNotification($db, $order['rider_id'],
                "Delivery Delayed",
                "Delivery for order #{$order['order_number']} is delayed.",
                $link_rider
            );
            emailDeliveryDelayedRider($rider['email'], $rider['name'], $order['order_number'], $order['delivery_datetime']);
        }
    }
}

// ----------------------------------------------------------------------
//  REMINDER: 1 DAY BEFORE PICKUP
//  NOTE: fetch delivery_address from users via JOIN
// ----------------------------------------------------------------------
$pickupReminderQuery = "
    SELECT 
        wr.refill_id, 
        wr.user_id, 
        wr.rider_id, 
        wr.order_number, 
        wr.pickup_datetime,
        u.address AS delivery_address
    FROM water_refills wr
    INNER JOIN users u ON wr.user_id = u.user_id
    WHERE DATE(wr.pickup_datetime) = DATE(DATE_ADD(NOW(), INTERVAL 1 DAY))
    AND wr.order_status NOT IN ('cancelled', 'completed', 'pickup_delayed')
";

$stmtPickupRem = $db->prepare($pickupReminderQuery);
$stmtPickupRem->execute();
$pickupReminders = $stmtPickupRem->fetchAll(PDO::FETCH_ASSOC);

foreach ($pickupReminders as $order) {
    $link_customer = "Customer/order_details.php?order_number={$order['order_number']}";
    $link_rider = "Rider/order_details.php?order_number={$order['order_number']}";
    
    $customer = getUserDetails($db, $order['user_id']);

    // Notify Customer (Notification + Email)
    if ($customer) {
        createNotification($db, $order['user_id'],
            "Pickup Tomorrow",
            "Reminder: Your order #{$order['order_number']} pickup is scheduled for tomorrow.",
            $link_customer
        );
        emailPickupReminderCustomer($customer['email'], $customer['name'], $order['order_number'], $order['pickup_datetime']);
    }

    // Notify Rider (Notification + Email) - include delivery_address from users.address
    if (!empty($order['rider_id'])) {
        $rider = getUserDetails($db, $order['rider_id']);
        if ($rider) {
            createNotification($db, $order['rider_id'],
                "Pickup Tomorrow",
                "Reminder: Order #{$order['order_number']} is scheduled for pickup tomorrow.",
                $link_rider
            );
            // delivery_address is now sourced from users.address via the JOIN above
            emailPickupReminderRider(
                $rider['email'],
                $rider['name'],
                $order['order_number'],
                $order['pickup_datetime'],
                $order['delivery_address']
            );
        }
    }
}

// ----------------------------------------------------------------------
//  REMINDER: 1 DAY BEFORE DELIVERY
//  NOTE: fetch delivery_address from users via JOIN
// ----------------------------------------------------------------------
$deliveryReminderQuery = "
    SELECT 
        wr.refill_id, 
        wr.user_id, 
        wr.rider_id, 
        wr.order_number, 
        wr.delivery_datetime,
        u.address AS delivery_address
    FROM water_refills wr
    INNER JOIN users u ON wr.user_id = u.user_id
    WHERE DATE(wr.delivery_datetime) = DATE(DATE_ADD(NOW(), INTERVAL 1 DAY))
    AND wr.order_status NOT IN ('cancelled', 'completed', 'delivery_delayed', 'pickup_delayed')
";

$stmtDeliveryRem = $db->prepare($deliveryReminderQuery);
$stmtDeliveryRem->execute();
$deliveryReminders = $stmtDeliveryRem->fetchAll(PDO::FETCH_ASSOC);

foreach ($deliveryReminders as $order) {
    $link_customer = "Customer/order_details.php?order_number={$order['order_number']}";
    $link_rider = "Rider/order_details.php?order_number={$order['order_number']}";

    $customer = getUserDetails($db, $order['user_id']);

    // Notify Customer (Notification + Email)
    if ($customer) {
        createNotification($db, $order['user_id'],
            "Delivery Tomorrow",
            "Reminder: Your order #{$order['order_number']} delivery is scheduled for tomorrow.",
            $link_customer
        );
        emailDeliveryReminderCustomer($customer['email'], $customer['name'], $order['order_number'], $order['delivery_datetime']);
    }

    // Notify Rider (Notification + Email) - include delivery_address
    if (!empty($order['rider_id'])) {
        $rider = getUserDetails($db, $order['rider_id']);
        if ($rider) {
            createNotification($db, $order['rider_id'],
                "Delivery Tomorrow",
                "Reminder: Order #{$order['order_number']} is scheduled for delivery tomorrow.",
                $link_rider
            );
            emailDeliveryReminderRider(
                $rider['email'],
                $rider['name'],
                $order['order_number'],
                $order['delivery_datetime'],
                $order['delivery_address']
            );
        }
    }
}

?> 
