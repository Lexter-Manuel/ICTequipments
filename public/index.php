<?php
/**
 * Public directory index guard
 * Catches requests to /ictequipment/public/ (with or without hash fragment)
 * and redirects based on session status.
 */

session_start();

if (!empty($_SESSION['user_id'])) {
    // Logged in — send to dashboard
    header('Location: dashboard.php');
    exit();
}

// Not logged in — send to unauthorized page
header('Location: /ictequipment/modules/auth/unauthorized.php');
exit();
