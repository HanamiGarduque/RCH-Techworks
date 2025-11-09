<?php
require_once '../Database/db_connection.php';
$database = new Database();
$db = $database->getConnect();

$sender_id = $_GET['sender_id'];
$receiver_id = $_GET['receiver_id'];
$search = $_GET['search'] ?? ''; // Optional search term

// Base query
$sql = "
    SELECT message_id, sender_id, receiver_id, message, image_path, created_at
    FROM messages
    WHERE (sender_id = :sender_id AND receiver_id = :receiver_id)
       OR (sender_id = :receiver_id AND receiver_id = :sender_id)
";

// Add search condition if search term is provided
$params = [
    ':sender_id' => $sender_id,
    ':receiver_id' => $receiver_id
];

if (!empty($search)) {
    $sql .= " AND message LIKE :search";
    $params[':search'] = '%' . $search . '%';
}

$sql .= " ORDER BY created_at ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);

$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($messages);
?>
