<?php
/**
 * Logout Script
 * NIA UPRIIS ICT Inventory System
 */

session_start();

require_once '../../config/database.php';
require_once '../../config/config.php';

// Log logout activity
if (isset($_SESSION['user_id'])) {
    logActivity($_SESSION['user_id'], 'logout', 'User logged out');
    
    // Clear "Remember Me" token
    clearRememberToken($_SESSION['user_id']);
}

// Unset all session variables
$_SESSION = array();

// Destroy session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy session
session_destroy();

// Redirect to login page
header('Location: login.php?logged_out=1');
exit();