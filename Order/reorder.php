<?php
// File: Order/reorder.php

header('Content-Type: application/json');

require_once __DIR__ . '/../session.php';
require_once '../Database/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$refillId = isset($input['refill_id']) ? $input['refill_id'] : null;

if (!$refillId) {
    echo json_encode(['success' => false, 'error' => 'Refill ID is required']);
    exit;
}

$database = new Database();
$db = $database->getConnect();

try {
    // Start transaction
    $db->beginTransaction();
    
    // Verify order belongs to user
    $checkQuery = "
        SELECT refill_id 
        FROM water_refills 
        WHERE refill_id = :refill_id 
        AND user_id = :user_id
    ";
    
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':refill_id', $refillId);
    $checkStmt->bindParam(':user_id', $userId);
    $checkStmt->execute();
    
    $order = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        $db->rollBack();
        echo json_encode(['success' => false, 'error' => 'Order not found']);
        exit;
    }
    
    // Get all items from the original order
    $itemsQuery = "
        SELECT 
            product_id,
            gallon_type,
            gallon_size,
            quantity,
            unit_price
        FROM water_refill_items 
        WHERE refill_id = :refill_id
    ";
    
    $itemsStmt = $db->prepare($itemsQuery);
    $itemsStmt->bindParam(':refill_id', $refillId);
    $itemsStmt->execute();
    
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($items)) {
        $db->rollBack();
        echo json_encode(['success' => false, 'error' => 'No items found in order']);
        exit;
    }
    
    // Here you would typically add these items to a cart session or temporary cart table
    // For this example, I'll store them in the session
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    foreach ($items as $item) {
        $cartKey = $item['product_id'] ? 'product_' . $item['product_id'] : 'refill_' . $item['gallon_type'] . '_' . $item['gallon_size'];
        
        // Check if item already exists in cart
        $found = false;
        foreach ($_SESSION['cart'] as &$cartItem) {
            if ($cartItem['key'] === $cartKey) {
                $cartItem['quantity'] += $item['quantity'];
                $found = true;
                break;
            }
        }
        
        // If not found, add new item to cart
        if (!$found) {
            $_SESSION['cart'][] = [
                'key' => $cartKey,
                'product_id' => $item['product_id'],
                'gallon_type' => $item['gallon_type'],
                'gallon_size' => $item['gallon_size'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price']
            ];
        }
    }
    
    // Commit transaction
    $db->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Items added to cart successfully',
        'items_count' => count($items)
    ]);
    
} catch (PDOException $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>