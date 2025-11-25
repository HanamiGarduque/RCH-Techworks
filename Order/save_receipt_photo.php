<?php
header('Content-Type: application/json');

require_once '../Database/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_FILES['receipt_image'])) {
            throw new Exception('No image file uploaded');
        }

        $order_id = $_POST['order_id'] ?? null;
        $transaction_number = $_POST['transaction_number'] ?? null;
        $customer_name = $_POST['customer_name'] ?? 'Walk-in Customer';
        $total_amount = $_POST['total_amount'] ?? 0;

        if (!$order_id || !$transaction_number) {
            throw new Exception('Missing required fields');
        }

        // Create receipts directory if it doesn't exist
        $upload_dir = '../Assets/receipts/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate unique filename
        $file_extension = 'png';
        $filename = 'receipt_' . $transaction_number . '_' . time() . '.' . $file_extension;
        $filepath = $upload_dir . $filename;

        // Move uploaded file
        if (!move_uploaded_file($_FILES['receipt_image']['tmp_name'], $filepath)) {
            throw new Exception('Failed to save receipt image');
        }

        // Save to database
        $database = new Database();
        $db = $database->getConnect();

        $stmt = $db->prepare("
            INSERT INTO receipt_photos (
                order_id, 
                transaction_number, 
                customer_name, 
                total_amount, 
                image_path, 
                created_at
            ) VALUES (
                :order_id, 
                :transaction_number, 
                :customer_name, 
                :total_amount, 
                :image_path, 
                NOW()
            )
        ");

        $stmt->execute([
            ':order_id' => $order_id,
            ':transaction_number' => $transaction_number,
            ':customer_name' => $customer_name,
            ':total_amount' => $total_amount,
            ':image_path' => $filename
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Receipt saved successfully',
            'filename' => $filename
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>