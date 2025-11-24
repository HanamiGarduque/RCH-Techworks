<?php
header('Content-Type: application/json');

require_once '../Database/db_connection.php';

try {
    $database = new Database();
    $db = $database->getConnect();

    $stmt = $db->prepare("
        SELECT 
            id,
            order_id,
            transaction_number,
            customer_name,
            total_amount,
            image_path,
            created_at
        FROM receipt_photos
        ORDER BY created_at DESC
    ");

    $stmt->execute();
    $receipts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'receipts' => $receipts
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>