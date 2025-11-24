<?php
// Prevent any output before JSON
ob_start();

// Set headers
header('Content-Type: application/json');

// Disable error display
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once __DIR__ . '/../Database/db_connection.php';
    
    $database = new Database();
    $db = $database->getConnect();

    // Validate required parameters
    if (!isset($_GET['sender_id']) || !isset($_GET['receiver_id'])) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Sender and receiver IDs are required',
            'data' => []
        ]);
        exit;
    }

    $sender_id = (int)$_GET['sender_id'];
    $receiver_id = (int)$_GET['receiver_id'];
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    // Base query
    $sql = "
        SELECT 
            message_id, 
            sender_id, 
            receiver_id, 
            message, 
            image_path, 
            created_at,
            is_read
        FROM messages
        WHERE (sender_id = :sender_id AND receiver_id = :receiver_id)
           OR (sender_id = :receiver_id AND receiver_id = :sender_id)
    ";

    $params = [
        ':sender_id' => $sender_id,
        ':receiver_id' => $receiver_id
    ];

    // Add search condition if search term is provided
    if (!empty($search)) {
        $sql .= " AND message LIKE :search";
        $params[':search'] = '%' . $search . '%';
    }

    $sql .= " ORDER BY created_at ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format messages
    $formattedMessages = [];
    foreach ($messages as $msg) {
        $formattedMessages[] = [
            'message_id' => (int)$msg['message_id'],
            'sender_id' => (int)$msg['sender_id'],
            'receiver_id' => (int)$msg['receiver_id'],
            'message' => $msg['message'],
            'image_path' => $msg['image_path'],
            'created_at' => $msg['created_at'],
            'is_read' => (int)$msg['is_read']
        ];
    }

    // Clear buffer and return messages
    ob_clean();
    echo json_encode([
        'success' => true,
        'data' => $formattedMessages,
        'count' => count($formattedMessages)
    ], JSON_THROW_ON_ERROR);

} catch (PDOException $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error',
        'message' => $e->getMessage(),
        'data' => []
    ]);
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error',
        'message' => $e->getMessage(),
        'data' => []
    ]);
}

ob_end_flush();
?>