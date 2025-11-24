<?php
header('Content-Type: application/json');

require_once '../Database/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;

        if (!$id) {
            throw new Exception('Receipt ID is required');
        }

        $database = new Database();
        $db = $database->getConnect();

        // Get image path before deleting
        $stmt = $db->prepare("SELECT image_path FROM receipt_photos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $receipt = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$receipt) {
            throw new Exception('Receipt not found');
        }

        // Delete file
        $filepath = '../Assets/receipts/' . $receipt['image_path'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }

        // Delete from database
        $stmt = $db->prepare("DELETE FROM receipt_photos WHERE id = :id");
        $stmt->execute([':id' => $id]);

        echo json_encode([
            'success' => true,
            'message' => 'Receipt deleted successfully'
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