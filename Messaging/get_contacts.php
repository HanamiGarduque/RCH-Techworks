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

    // Modified query to get users with their last message info directly
    $sql = "
        SELECT 
            u.user_id,
            u.name,
            u.role,
            (SELECT m.message 
             FROM messages m 
             WHERE (m.sender_id = u.user_id AND m.receiver_id = :current_user_id1)
                OR (m.sender_id = :current_user_id2 AND m.receiver_id = u.user_id)
             ORDER BY m.created_at DESC 
             LIMIT 1) as last_message,
            (SELECT m.image_path 
             FROM messages m 
             WHERE (m.sender_id = u.user_id AND m.receiver_id = :current_user_id3)
                OR (m.sender_id = :current_user_id4 AND m.receiver_id = u.user_id)
             ORDER BY m.created_at DESC 
             LIMIT 1) as last_image_path,
            (SELECT m.created_at 
             FROM messages m 
             WHERE (m.sender_id = u.user_id AND m.receiver_id = :current_user_id5)
                OR (m.sender_id = :current_user_id6 AND m.receiver_id = u.user_id)
             ORDER BY m.created_at DESC 
             LIMIT 1) as last_message_time,
            (SELECT COUNT(*) 
             FROM messages m 
             WHERE m.receiver_id = :current_user_id7 
               AND m.sender_id = u.user_id 
               AND m.is_read = 0) as unread_count
        FROM users u
        WHERE u.user_id != :current_user_id8
    ";

    // Add search condition if query is provided
    if (!empty($search)) {
        $sql .= " AND u.name LIKE :search";
    }

    // Order by last message time (most recent first), then by name
    $sql .= " ORDER BY last_message_time DESC, u.name ASC";

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':current_user_id1', $current_user_id, PDO::PARAM_INT);
    $stmt->bindValue(':current_user_id2', $current_user_id, PDO::PARAM_INT);
    $stmt->bindValue(':current_user_id3', $current_user_id, PDO::PARAM_INT);
    $stmt->bindValue(':current_user_id4', $current_user_id, PDO::PARAM_INT);
    $stmt->bindValue(':current_user_id5', $current_user_id, PDO::PARAM_INT);
    $stmt->bindValue(':current_user_id6', $current_user_id, PDO::PARAM_INT);
    $stmt->bindValue(':current_user_id7', $current_user_id, PDO::PARAM_INT);
    $stmt->bindValue(':current_user_id8', $current_user_id, PDO::PARAM_INT);

    if (!empty($search)) {
        $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
    }

    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $contacts = [];

    foreach ($users as $user) {
        // Compute initials
        $nameParts = explode(' ', trim($user['name']));
        $initials = strtoupper(substr($nameParts[0], 0, 1));
        if (isset($nameParts[1])) {
            $initials .= strtoupper(substr($nameParts[1], 0, 1));
        }

        // Format time ago with better accuracy
        $timeAgo = '';
        if (!empty($user['last_message_time'])) {
            $createdTime = strtotime($user['last_message_time']);
            $now = time();
            $diff = $now - $createdTime;

            $diffSecs = $diff;
            $diffMins = floor($diff / 60);
            $diffHours = floor($diff / 3600);
            $diffDays = floor($diff / 86400);

            if ($diffSecs < 60) {
                $timeAgo = 'just now';
            } elseif ($diffMins < 60) {
                $timeAgo = $diffMins . 'm';
            } elseif ($diffHours < 24) {
                $timeAgo = $diffHours . 'h';
            } elseif ($diffDays < 7) {
                $timeAgo = $diffDays . 'd';
            } elseif ($diffDays < 365) {
                $timeAgo = date('M j', $createdTime);
            } else {
                $timeAgo = date('M j, Y', $createdTime);
            }
        }

        // Determine last message text
        $lastMsgText = '';
        if (!empty($user['last_image_path'])) {
            $lastMsgText = "📷 Photo";
        } elseif (!empty($user['last_message'])) {
            $lastMsgText = htmlspecialchars($user['last_message'], ENT_QUOTES, 'UTF-8');
            // Truncate long messages
            if (strlen($lastMsgText) > 50) {
                $lastMsgText = substr($lastMsgText, 0, 50) . '...';
            }
        } else {
            $lastMsgText = 'No messages yet';
        }

        // Check for unread messages
        $hasUnread = ($user['unread_count'] > 0);

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
            'last_message_time' => $user['last_message_time'],
            'has_unread' => $hasUnread,
            'unread_count' => (int)$user['unread_count'],
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