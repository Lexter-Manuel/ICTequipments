<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log logout activity
if (isset($_SESSION['user_id'])) {
    logActivity(ACTION_LOGOUT, MODULE_AUTH, 'User logged out');
    
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

// Use JS replace() to remove dashboard from history stack
// so the back button can't return to it after logout
?><!DOCTYPE html>
<html><head><title>Logging out…</title></head>
<body>
<script>
    sessionStorage.removeItem('nia-active-page');
    window.location.replace('login.php?logged_out=1');
</script>
<noscript><meta http-equiv="refresh" content="0;url=login.php?logged_out=1"></noscript>
</body>
</html>
<?php exit(); ?>