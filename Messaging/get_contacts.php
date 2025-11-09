<?php
require_once '../Database/db_connection.php';
session_start();

// Example: the currently logged-in user's ID
// $current_user_id = $_SESSION['user_id'] ?? null;

$current_user_id = 3;
if (!$current_user_id) {
    echo json_encode([]);
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

    // Get unread messages count
    $unreadQuery = "
        SELECT COUNT(*) AS unread_count
        FROM messages
        WHERE receiver_id = :current_user
        AND sender_id = :contact
        AND message IS NOT NULL
        AND message != ''
    ";
    $unreadStmt = $db->prepare($unreadQuery);
    $unreadStmt->execute([
        ':current_user' => $current_user_id,
        ':contact' => $user['user_id']
    ]);
    $unread = $unreadStmt->fetch(PDO::FETCH_ASSOC)['unread_count'] ?? 0;

    // Compute initials
    $nameParts = explode(' ', trim($user['name']));
    $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));

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
            $timeAgo = date('h:i A', $createdTime);
        }
    }

    // Determine last message text
    $lastMsgText = '';
    if ($lastMessage) {
        if (!empty($lastMessage['image_path'])) {
            $lastMsgText = "[Photo]";
        } else {
            $lastMsgText = htmlspecialchars($lastMessage['message']);
        }
    }

    $contacts[] = [
        'user_id' => $user['user_id'],
        'name' => $user['name'],
        'type' => strtolower($user['role']),
        'last_message' => $lastMsgText,
        'last_message_time' => $lastMessage['created_at'] ?? null,
        'time_ago' => $timeAgo,
        'unread_count' => $unread,
        'initials' => $initials,
        'avatar_color' => $user['role'] === 'customer' ? 'from-blue-400 to-blue-600' : 'from-orange-400 to-orange-600'
    ];
}

echo json_encode($contacts);
?>
