<?php

require_once '../Database/db_connection.php';
$database = new Database();
$db = $database->getConnect();

function createNotification($db, $userId, $title, $message, $link) {
    try {
        // Use the prepared statement directly
        $notify = $db->prepare("
            INSERT INTO notifications (user_id, title, message, link, status, created_at)
            VALUES (:user_id, :title, :message, :link, 'unread', NOW())
        ");
        $notify->execute([
            ':user_id' => $userId,
            ':title' => $title,
            ':message' => $message,
            ':link' => $link
        ]);
        return true;
    } catch (PDOException $e) {
        // Log the error instead of failing silently in a real application
        error_log("Notification creation failed: " . $e->getMessage()); 
        return false;
    }
}

?>