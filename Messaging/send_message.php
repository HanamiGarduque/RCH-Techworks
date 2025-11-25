<?php
// send_message.php - COMPLETE FIX with debugging
ob_start();

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    require_once __DIR__ . '/../Database/db_connection.php';
    
    $database = new Database();
    $db = $database->getConnect();

    if (!isset($_POST['sender_id']) || !isset($_POST['receiver_id'])) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Sender and receiver IDs are required'
        ]);
        exit;
    }

    $sender_id = (int)$_POST['sender_id'];   
    $receiver_id = (int)$_POST['receiver_id'];
    $message = trim($_POST['message'] ?? '');
    $image_path = null;

    // Create uploads directory
    $uploadDir = __DIR__ . '/uploads/';
    
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            ob_clean();
            echo json_encode([
                'success' => false,
                'message' => 'Failed to create uploads directory',
                'debug_path' => $uploadDir
            ]);
            exit;
        }
    }

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($_FILES['image']['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            ob_clean();
            echo json_encode([
                'success' => false,
                'message' => 'Invalid file type. Only images (JPEG, PNG, GIF, WebP) are allowed.'
            ]);
            exit;
        }

        if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            ob_clean();
            echo json_encode([
                'success' => false,
                'message' => 'File too large. Maximum size is 5MB.'
            ]);
            exit;
        }

        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        $fileName = uniqid('img_', true) . '_' . time() . '.' . $fileExtension;
        $destPath = $uploadDir . $fileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            // CRITICAL FIX: Store relative path that works from web root
            // Since this file is in /Admin/, store path relative to Admin folder
            $image_path = 'uploads/' . $fileName;
        } else {
            ob_clean();
            echo json_encode([
                'success' => false,
                'message' => 'Failed to upload image. Check directory permissions.',
                'debug_dest' => $destPath,
                'debug_tmp' => $fileTmpPath
            ]);
            exit;
        }
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        
        $errorMessage = $uploadErrors[$_FILES['image']['error']] ?? 'Unknown upload error';
        
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => $errorMessage
        ]);
        exit;
    }

    if (empty($message) && empty($image_path)) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Cannot send empty message'
        ]);
        exit;
    }

    // Insert message into database
    $stmt = $db->prepare("
        INSERT INTO messages (sender_id, receiver_id, message, image_path, created_at, is_read) 
        VALUES (?, ?, ?, ?, NOW(), 0)
    ");
    
    $stmt->execute([
        $sender_id, 
        $receiver_id, 
        $message ?: null, 
        $image_path
    ]);

    $message_id = $db->lastInsertId();

    ob_clean();
    echo json_encode([
        'success' => true,
        'message_id' => $message_id,
        'image_path' => $image_path,
        'debug_full_path' => $image_path ? $destPath : null,
        'message' => 'Message sent successfully'
    ]);

} catch (PDOException $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error',
        'message' => $e->getMessage()
    ]);
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error',
        'message' => $e->getMessage()
    ]);
}

ob_end_flush();
?>