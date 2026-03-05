<?php

session_start();

if (!empty($_SESSION['user_id'])) {
    // Logged in — send to dashboard
    header('Location: dashboard.php');
    exit();
}

// Not logged in — send to unauthorized page
header('Location: /ictequipment/modules/auth/unauthorized.php');
exit();
