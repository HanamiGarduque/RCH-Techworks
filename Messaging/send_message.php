<?php
// Prevent any output before JSON
ob_start();

// Set headers
header('Content-Type: application/json');

// Disable error display
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    require_once __DIR__ . '/../Database/db_connection.php';
    
    $database = new Database();
    $db = $database->getConnect();

    // Validate required parameters
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
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    $image_path = null;

    // Validate that we have either a message or an image
    if (empty($message) && empty($_FILES['image']['name'])) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Message or image is required'
        ]);
        exit;
    }

    // Handle image upload if present
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = mime_content_type($file['tmp_name']);
        
        if (!in_array($file_type, $allowed_types)) {
            ob_clean();
            echo json_encode([
                'success' => false,
                'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.'
            ]);
            exit;
        }

        // Validate file size (5MB max)
        if ($file['size'] > 5 * 1024 * 1024) {
            ob_clean();
            echo json_encode([
                'success' => false,
                'message' => 'File size exceeds 5MB limit'
            ]);
            exit;
        }

        // Create uploads directory if it doesn't exist
        $upload_dir = __DIR__ . '/uploads/';
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                ob_clean();
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to create upload directory'
                ]);
                exit;
            }
        }

        // Generate unique filename
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $unique_filename = 'msg_' . uniqid() . '_' . time() . '.' . $file_extension;
        $target_path = $upload_dir . $unique_filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            // Store relative path in database
            $image_path = 'uploads/' . $unique_filename;
        } else {
            ob_clean();
            echo json_encode([
                'success' => false,
                'message' => 'Failed to upload image. Please check directory permissions.'
            ]);
            exit;
        }
    }

    // Insert message into database
    $sql = "INSERT INTO messages (sender_id, receiver_id, message, image_path, created_at, is_read) 
            VALUES (:sender_id, :receiver_id, :message, :image_path, NOW(), 0)";
    
    $stmt = $db->prepare($sql);
    $result = $stmt->execute([
        ':sender_id' => $sender_id,
        ':receiver_id' => $receiver_id,
        ':message' => $message,
        ':image_path' => $image_path
    ]);

    if ($result) {
        $message_id = $db->lastInsertId();
        
        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Message sent successfully',
            'message_id' => (int)$message_id,
            'image_path' => $image_path,
            'created_at' => date('Y-m-d H:i:s')
        ], JSON_THROW_ON_ERROR);
    } else {
        // If database insert failed but image was uploaded, delete the image
        if ($image_path && file_exists($upload_dir . basename($image_path))) {
            unlink($upload_dir . basename($image_path));
        }
        
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Failed to save message to database'
        ]);
    }

} catch (PDOException $e) {
    // If error occurs and image was uploaded, clean up
    if (isset($image_path) && isset($upload_dir)) {
        $file_to_delete = $upload_dir . basename($image_path);
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete);
        }
    }
    
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error',
        'message' => $e->getMessage()
    ]);
} catch (Exception $e) {
    // If error occurs and image was uploaded, clean up
    if (isset($image_path) && isset($upload_dir)) {
        $file_to_delete = $upload_dir . basename($image_path);
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete);
        }
    }
    
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