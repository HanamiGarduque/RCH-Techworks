<?php
// Prevent output before JSON
ob_start();

// JSON header
header('Content-Type: application/json');

// Disable error display (log only)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Start session
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
    // -------------------------
    // SESSION CHECK
    // -------------------------
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

    // -------------------------
    // DB INIT FIRST (VERY IMPORTANT)
    // -------------------------
    $database = new Database();
    $db = $database->getConnect();

    // -------------------------
    // FETCH USER ROLE
    // -------------------------
    $roleQuery = $db->prepare("SELECT role FROM users WHERE user_id = :uid LIMIT 1");
    $roleQuery->bindValue(':uid', $current_user_id, PDO::PARAM_INT);
    $roleQuery->execute();
    $current_role = strtolower($roleQuery->fetchColumn());

    // -------------------------
    // DETERMINE ALLOWED CONTACT ROLES
    // -------------------------
    switch ($current_role) {
        case 'admin':
            $allowedRoles = ['customer', 'rider'];
            break;
        case 'customer':
            $allowedRoles = ['admin', 'rider'];
            break;
        case 'rider':
            $allowedRoles = ['admin', 'customer'];
            break;
        default:
            // FAILSAFE
            $allowedRoles = ['admin', 'customer', 'rider'];
    }

    // Prepare allowed roles safely for SQL
    $allowedRolesSql = "'" . implode("','", array_map('addslashes', $allowedRoles)) . "'";

    // -------------------------
    // SEARCH FILTER
    // -------------------------
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $searchSql = !empty($search) ? " AND u.name LIKE :search" : "";

    // -------------------------
    // MAIN QUERY
    // -------------------------
    $sql = "
        SELECT 
            u.user_id,
            u.name,
            u.role,

            -- Last message
            (
                SELECT m.message 
                FROM messages m
                WHERE 
                    (m.sender_id = u.user_id AND m.receiver_id = :uid1)
                    OR 
                    (m.sender_id = :uid2 AND m.receiver_id = u.user_id)
                ORDER BY m.created_at DESC 
                LIMIT 1
            ) AS last_message,

            -- Last image
            (
                SELECT m.image_path 
                FROM messages m
                WHERE 
                    (m.sender_id = u.user_id AND m.receiver_id = :uid3)
                    OR 
                    (m.sender_id = :uid4 AND m.receiver_id = u.user_id)
                ORDER BY m.created_at DESC 
                LIMIT 1
            ) AS last_image_path,

            -- Last message time
            (
                SELECT m.created_at 
                FROM messages m
                WHERE 
                    (m.sender_id = u.user_id AND m.receiver_id = :uid5)
                    OR 
                    (m.sender_id = :uid6 AND m.receiver_id = u.user_id)
                ORDER BY m.created_at DESC 
                LIMIT 1
            ) AS last_message_time,

            -- Unread message count
            (
                SELECT COUNT(*) 
                FROM messages m
                WHERE 
                    m.receiver_id = :uid7 
                    AND m.sender_id = u.user_id
                    AND m.is_read = 0
            ) AS unread_count

        FROM users u
        WHERE 
            u.user_id != :uid8
            AND u.role IN ($allowedRolesSql)
            $searchSql
        ORDER BY last_message_time DESC, u.name ASC
    ";

    $stmt = $db->prepare($sql);

    // Bind repeated user_id values
    for ($i = 1; $i <= 8; $i++) {
        $stmt->bindValue(":uid$i", $current_user_id, PDO::PARAM_INT);
    }

    if (!empty($search)) {
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }

    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // -------------------------
    // FORMAT CONTACT OUTPUT
    // -------------------------
    $contacts = [];

    foreach ($users as $user) {

        // INITIALS
        $parts = explode(' ', trim($user['name']));
        $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));

        // TIME AGO
        $timeAgo = '';
        if (!empty($user['last_message_time'])) {
            $t = strtotime($user['last_message_time']);
            $now = time();
            $d = $now - $t;

            if ($d < 60) $timeAgo = 'just now';
            elseif ($d < 3600) $timeAgo = floor($d / 60) . 'm';
            elseif ($d < 86400) $timeAgo = floor($d / 3600) . 'h';
            elseif ($d < 604800) $timeAgo = floor($d / 86400) . 'd';
            elseif ($d < 31536000) $timeAgo = date('M j', $t);
            else $timeAgo = date('M j, Y', $t);
        }

        // LAST MESSAGE PREVIEW
        if (!empty($user['last_image_path'])) {
            $lastMsg = "📷 Photo";
        } elseif (!empty($user['last_message'])) {
            $lastMsg = htmlspecialchars($user['last_message']);
            if (strlen($lastMsg) > 50) {
                $lastMsg = substr($lastMsg, 0, 50) . "...";
            }
        } else {
            $lastMsg = "No messages yet";
        }

        // AVATAR COLOR BY ROLE
        $roleColorMap = [
            'customer' => 'from-blue-400 to-blue-600',
            'rider'    => 'from-orange-400 to-orange-600',
            'admin'    => 'from-purple-400 to-purple-600'
        ];
        $avatarColor = $roleColorMap[strtolower($user['role'])] ?? 'from-gray-400 to-gray-600';

        // STORE CONTACT
        $contacts[] = [
            'user_id' => (int)$user['user_id'],
            'name' => htmlspecialchars($user['name']),
            'type' => strtolower($user['role']),
            'last_message' => $lastMsg,
            'last_message_time' => $user['last_message_time'],
            'has_unread' => $user['unread_count'] > 0,
            'unread_count' => (int)$user['unread_count'],
            'initials' => $initials,
            'avatar_color' => $avatarColor
        ];
    }

    ob_clean();
    echo json_encode([
        'success' => true,
        'data' => $contacts,
        'count' => count($contacts)
    ], JSON_THROW_ON_ERROR);

} catch (PDOException $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'error' => 'Database error',
        'message' => $e->getMessage()
    ]);
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'error' => 'Server error',
        'message' => $e->getMessage()
    ]);
}

ob_end_flush();
