<?php
// Prevent any output before JSON
ob_start();

// Set headers first
header('Content-Type: application/json');

// Disable error display, log instead
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    require_once __DIR__ . '/../session.php';
    require_once __DIR__ . '/../Database/db_connection.php';
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'error' => 'Failed to load required files',
        'message' => $e->getMessage()
    ]);
    exit;
}

try {
    // Get current user ID from session
    $current_user_id = $_SESSION['id'] ?? null;
    
    if (!$current_user_id) {
        ob_clean();
        echo json_encode([
            'success' => true,
            'data' => [],
            'message' => 'No active session'
        ]);
        exit;
    }

    $database = new Database();
    $db = $database->getConnect();

    // Get search query (if any)
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    // Base SQL: fetch all users except current one
    $sql = "
        SELECT 
            u.user_id,
            u.name,
            u.role
        FROM users u
        WHERE u.user_id != :current_user_id
    ";

    // Add search condition if query is provided
    if (!empty($search)) {
        $sql .= " AND u.name LIKE :search";
    }

    $sql .= " ORDER BY u.name ASC";

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':current_user_id', $current_user_id, PDO::PARAM_INT);

    if (!empty($search)) {
        $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
    }

    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $contacts = [];

    foreach ($users as $user) {
        // Get last message between current user and this contact
        $msgQuery = "
            SELECT 
                message,
                image_path,
                sender_id,
                created_at
            FROM messages
            WHERE 
                (sender_id = :current_user AND receiver_id = :contact)
                OR (sender_id = :contact AND receiver_id = :current_user)
            ORDER BY created_at DESC
            LIMIT 1
        ";
        $msgStmt = $db->prepare($msgQuery);
        $msgStmt->execute([
            ':current_user' => $current_user_id,
            ':contact' => $user['user_id']
        ]);
        $lastMessage = $msgStmt->fetch(PDO::FETCH_ASSOC);

        // Check if there are unread messages
        $unreadQuery = "
            SELECT COUNT(*) as unread_count
            FROM messages
            WHERE receiver_id = :current_user
              AND sender_id = :contact
              AND is_read = 0
        ";
        $unreadStmt = $db->prepare($unreadQuery);
        $unreadStmt->execute([
            ':current_user' => $current_user_id,
            ':contact' => $user['user_id']
        ]);
        $unreadResult = $unreadStmt->fetch(PDO::FETCH_ASSOC);
        $hasUnread = ($unreadResult && $unreadResult['unread_count'] > 0);

        // Compute initials
        $nameParts = explode(' ', trim($user['name']));
        $initials = strtoupper(substr($nameParts[0], 0, 1));
        if (isset($nameParts[1])) {
            $initials .= strtoupper(substr($nameParts[1], 0, 1));
        }

        // Format time ago
        $timeAgo = '';
        if (!empty($lastMessage['created_at'])) {
            $createdTime = strtotime($lastMessage['created_at']);
            $now = time();
            $diff = $now - $createdTime;

            $diffMins = floor($diff / 60);
            $diffHours = floor($diff / 3600);
            $diffDays = floor($diff / 86400);

            if ($diffMins < 1) {
                $timeAgo = 'just now';
            } elseif ($diffMins < 60) {
                $timeAgo = $diffMins . 'm';
            } elseif ($diffHours < 24) {
                $timeAgo = $diffHours . 'h';
            } elseif ($diffDays < 7) {
                $timeAgo = $diffDays . 'd';
            } else {
                $timeAgo = date('M j', $createdTime);
            }
        }

        // Determine last message text
        $lastMsgText = '';
        if ($lastMessage) {
            if (!empty($lastMessage['image_path'])) {
                $lastMsgText = "📷 Photo";
            } elseif (!empty($lastMessage['message'])) {
                $lastMsgText = htmlspecialchars($lastMessage['message'], ENT_QUOTES, 'UTF-8');
                // Truncate long messages
                if (strlen($lastMsgText) > 50) {
                    $lastMsgText = substr($lastMsgText, 0, 50) . '...';
                }
            } else {
                $lastMsgText = '';
            }
        }

        // Determine avatar color based on role
        $avatarColor = 'from-gray-400 to-gray-600'; // default
        switch (strtolower($user['role'])) {
            case 'customer':
                $avatarColor = 'from-blue-400 to-blue-600';
                break;
            case 'rider':
                $avatarColor = 'from-orange-400 to-orange-600';
                break;
            case 'admin':
                $avatarColor = 'from-purple-400 to-purple-600';
                break;
        }

        $contacts[] = [
            'user_id' => (int)$user['user_id'],
            'name' => htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'),
            'type' => strtolower($user['role']),
            'last_message' => $lastMsgText,
            'last_message_time' => $lastMessage['created_at'] ?? null,
            'time_ago' => $timeAgo,
            'has_unread' => $hasUnread,
            'initials' => $initials,
            'avatar_color' => $avatarColor
        ];
    }

    // Clear any output buffer content
    ob_clean();
    
    // Return successful response
    echo json_encode([
        'success' => true,
        'data' => $contacts,
        'count' => count($contacts)
    ], JSON_THROW_ON_ERROR);

} catch (PDOException $e) {
    // Database error
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error',
        'message' => $e->getMessage()
    ]);
} catch (Exception $e) {
    // General error
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error',
        'message' => $e->getMessage()
    ]);
}

// End output buffering and send
ob_end_flush();
?>