<?php
require_once '../Database/db_connection.php';
$database = new Database();
$db = $database->getConnect();

$sender_id = $_POST['sender_id'];
$receiver_id = $_POST['receiver_id'];
$message = trim($_POST['message'] ?? '');
$image_path = null;

// Handle image upload
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/';
    $fileTmpPath = $_FILES['image']['tmp_name'];
    $fileName = uniqid() . "_" . basename($_FILES['image']['name']);
    $destPath = $uploadDir . $fileName;

    // Move the uploaded file
    if (move_uploaded_file($fileTmpPath, $destPath)) {
        $image_path = 'uploads/' . $fileName; // relative path for front-end
    }
}

// Only insert if message or image exists
if (!empty($message) || $image_path) {
    $stmt = $db->prepare("INSERT INTO messages (sender_id, receiver_id, message, image_path) VALUES (?, ?, ?, ?)");
    $stmt->execute([$sender_id, $receiver_id, $message, $image_path]);
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Empty message']);
}
?>
