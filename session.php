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

if (!isLoggedIn()) {
    header("Location: " . dirname($_SERVER['PHP_SELF']) . "/index.php");
    exit();
}
