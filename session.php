<?php
// FIXED: session stays alive for 8 hours of inactivity.
// Removed strict_mode which was discarding valid sessions and causing 1-2 min logout.

if (!defined('SESSION_IDLE_TIMEOUT')) {
    define('SESSION_IDLE_TIMEOUT', 8 * 60 * 60); // 28800 = 8 hours
}

ini_set('session.gc_maxlifetime', (string)SESSION_IDLE_TIMEOUT);
ini_set('session.cookie_lifetime', (string)SESSION_IDLE_TIMEOUT);
ini_set('session.gc_probability', '1');
ini_set('session.gc_divisor', '100');
ini_set('session.use_strict_mode', '0');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_path', '/');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function sessionDebugLog($reason) {
    $line = date('Y-m-d H:i:s')
        . ' | ' . $reason
        . ' | URI=' . ($_SERVER['REQUEST_URI'] ?? '')
        . ' | SID=' . session_id()
        . ' | has_id=' . (isset($_SESSION['id']) ? 'yes' : 'no')
        . ' | role=' . ($_SESSION['role'] ?? 'none')
        . ' | LAST_ACTIVITY=' . ($_SESSION['LAST_ACTIVITY'] ?? 'none')
        . ' | idle=' . (isset($_SESSION['LAST_ACTIVITY']) ? (time() - (int)$_SESSION['LAST_ACTIVITY']) . 's' : 'n/a')
        . ' | gc_maxlifetime=' . ini_get('session.gc_maxlifetime')
        . ' | cookie_lifetime=' . ini_get('session.cookie_lifetime')
        . PHP_EOL;
    @file_put_contents(__DIR__ . '/session_debug.log', $line, FILE_APPEND);
}

// Sliding idle timeout: 8 hours
if (isset($_SESSION['LAST_ACTIVITY'])) {
    $idleFor = time() - (int)$_SESSION['LAST_ACTIVITY'];
    if ($idleFor > SESSION_IDLE_TIMEOUT) {
        sessionDebugLog('EXPIRED_AFTER_8H idle=' . $idleFor . 's');
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
        // Do NOT start a new empty session here, let the redirect below handle it
        header("Location: " . getProjectRootUrl() . "/index.php");
        exit();
    }
}
$_SESSION['LAST_ACTIVITY'] = time();

function isLoggedIn()
{
    return isset($_SESSION['id']);
}

function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isRider()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'rider';
}

function isCustomer()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'customer';
}

// Helper to get project root relative URL
function getProjectRootUrl() {
    $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
    $parts = explode('/', trim($scriptPath, '/'));
    return '/' . $parts[0];
}

// Redirect if not logged in
// Do NOT redirect on login / index pages themselves, or you get a redirect loop
$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
$publicPages = ['index.php', 'login.php', 'register.php', 'forgot_password.php'];
if (!in_array($currentPage, $publicPages, true) && !isLoggedIn()) {
    sessionDebugLog('NOT_LOGGED_IN');
    header("Location: " . getProjectRootUrl() . "/index.php");
    exit();
}

function protectPage() {
    if (!isLoggedIn()) {
        sessionDebugLog('NOT_LOGGED_IN protectPage');
        header("Location: " . getProjectRootUrl() . "/index.php");
        exit();
    }
}

function redirectIfRoleMismatch($requiredRole) {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $requiredRole) {
        sessionDebugLog('ROLE_MISMATCH required=' . $requiredRole);
        header("Location: " . getProjectRootUrl() . "/index.php");
        exit();
    }
}