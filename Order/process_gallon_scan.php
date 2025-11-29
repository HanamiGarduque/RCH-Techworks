<?php
require_once __DIR__ . '/../session.php';
redirectIfRoleMismatch('admin');

require_once '../Database/db_connection.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnect();

$data = json_decode(file_get_contents("php://input"), true);
$qrCode = $data['qr_code'] ?? null;
$orderNumber = $data['order_number'] ?? null;
$userId = $data['user_id'] ?? null;

if (!$qrCode || !$orderNumber || !$userId) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters.']);
    exit;
}

try {
    $db->beginTransaction();

    // 1. Find the gallon by QR code
    $gallonQuery = "SELECT 
        go.ownership_id,
        go.gallon_type,
        go.current_owner_id,
        go.status
    FROM gallon_ownership go
    WHERE go.code_value = :qr_code
    LIMIT 1";
    
    $gallonStmt = $db->prepare($gallonQuery);
    $gallonStmt->bindParam(':qr_code', $qrCode);
    $gallonStmt->execute();
    
    if ($gallonStmt->rowCount() === 0) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Gallon not found.']);
        exit;
    }
    
    $gallon = $gallonStmt->fetch(PDO::FETCH_ASSOC);
    
    // Store previous values for activity log
    $previousOwnerId = $gallon['current_owner_id'];
    $previousStatus = $gallon['status'];

    // 2. Check if gallon is available
    if ($gallon['status'] !== 'Available' && $gallon['status'] !== 'available') {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Gallon is not available.']);
        exit;
    }

    // 3. Get the order details
    $orderQuery = "SELECT refill_id FROM water_refills WHERE order_number = :order_number LIMIT 1";
    $orderStmt = $db->prepare($orderQuery);
    $orderStmt->bindParam(':order_number', $orderNumber);
    $orderStmt->execute();
    
    if ($orderStmt->rowCount() === 0) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Order not found.']);
        exit;
    }
    
    $order = $orderStmt->fetch(PDO::FETCH_ASSOC);

    // 4. Find matching item with remaining quantity to process
    $itemQuery = "SELECT 
        ri.item_id,
        ri.product_id,
        i.item_name,
        ri.gallon_owner,
        ri.quantity,
        ri.processed_quantity
    FROM water_refill_items ri
    LEFT JOIN inventory i ON ri.product_id = i.item_id
    WHERE ri.refill_id = :refill_id 
        AND ri.gallon_owner = 'station'
        AND (i.item_name LIKE :gallon_type OR ri.gallon_type LIKE :gallon_type)
        AND (ri.processed_quantity IS NULL OR ri.processed_quantity < ri.quantity)
    LIMIT 1";
    
    $itemStmt = $db->prepare($itemQuery);
    $itemStmt->bindParam(':refill_id', $order['refill_id']);
    $gallonTypeSearch = '%' . $gallon['gallon_type'] . '%';
    $itemStmt->bindParam(':gallon_type', $gallonTypeSearch);
    $itemStmt->execute();
    
    if ($itemStmt->rowCount() === 0) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'No matching order item found or all gallons already processed.']);
        exit;
    }
    
    $item = $itemStmt->fetch(PDO::FETCH_ASSOC);
    
    // Calculate current processed quantity
    $currentProcessed = $item['processed_quantity'] ?? 0;
    $newProcessed = $currentProcessed + 1;

    // 5. Update gallon ownership
    $updateGallonQuery = "UPDATE gallon_ownership 
                          SET current_owner_id = :user_id, 
                              status = 'In-Use' 
                          WHERE ownership_id = :ownership_id";
    
    $updateGallonStmt = $db->prepare($updateGallonQuery);
    $updateGallonStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $updateGallonStmt->bindParam(':ownership_id', $gallon['ownership_id'], PDO::PARAM_INT);
    
    if (!$updateGallonStmt->execute()) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to update gallon ownership.']);
        exit;
    }

    // 6. Update processed_quantity counter (gallon_owner stays as 'station')
    $updateItemQuery = "UPDATE water_refill_items 
                        SET processed_quantity = :new_processed
                        WHERE item_id = :item_id";
    
    $updateItemStmt = $db->prepare($updateItemQuery);
    $updateItemStmt->bindParam(':new_processed', $newProcessed, PDO::PARAM_INT);
    $updateItemStmt->bindParam(':item_id', $item['item_id'], PDO::PARAM_INT);
    
    if (!$updateItemStmt->execute()) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to update order item.']);
        exit;
    }

    // 7. CREATE ACTIVITY LOG ENTRY
    $logNotes = "Gallon assigned to customer via order #{$orderNumber}. Processed {$newProcessed} of {$item['quantity']} gallons for {$item['item_name']}.";
    
    $logQuery = "INSERT INTO gallon_activity_logs 
                (ownership_id, action_type, performed_by, previous_owner_id, new_owner_id, previous_status, new_status, notes) 
                VALUES 
                (:ownership_id, 'assigned', :performed_by, :previous_owner, :new_owner, :prev_status, 'In-Use', :notes)";
    
    $logStmt = $db->prepare($logQuery);
    $logStmt->bindParam(':ownership_id', $gallon['ownership_id'], PDO::PARAM_INT);
    $logStmt->bindParam(':performed_by', $_SESSION['user_id'], PDO::PARAM_INT);
    $logStmt->bindParam(':previous_owner', $previousOwnerId, PDO::PARAM_INT);
    $logStmt->bindParam(':new_owner', $userId, PDO::PARAM_INT);
    $logStmt->bindParam(':prev_status', $previousStatus);
    $logStmt->bindParam(':notes', $logNotes);
    
    if (!$logStmt->execute()) {
        // Log error but don't fail the transaction
        error_log('Warning: Failed to create activity log for gallon processing');
    }

    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Gallon processed successfully.',
        'item_name' => $item['item_name'],
        'gallon_type' => $gallon['gallon_type'],
        'processed' => $newProcessed,
        'total' => $item['quantity'],
        'item_complete' => ($newProcessed >= $item['quantity'])
    ]);

} catch (PDOException $e) {
    $db->rollBack();
    error_log('Database Error in process_gallon_scan.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
} catch (Exception $e) {
    $db->rollBack();
    error_log('Error in process_gallon_scan.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while processing gallon.']);
}
?>