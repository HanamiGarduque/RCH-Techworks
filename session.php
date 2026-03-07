<?php
session_start();

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
    // Example: /1stSem3rdYear
    $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
    $parts = explode('/', trim($scriptPath, '/'));
    return '/' . $parts[0]; // first folder is project folder
}

// Redirect if not logged in
if (!isLoggedIn()) {
    header(header: "Location: " . getProjectRootUrl() . "/index.php");
    exit();
}
function protectPage() {
    if (!isLoggedIn()) {
        header("Location: " . getProjectRootUrl() . "/index.php");
        exit();
    }
}
// Example: redirect based on role
function redirectIfRoleMismatch($requiredRole) {
    
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $requiredRole) {
        header("Location: " . getProjectRootUrl() . "/index.php");
        exit();
    }
}
