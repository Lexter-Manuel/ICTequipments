<?php
// 1. Load config first
require_once '../config/config.php';

// 2. Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['user_id'])) {
    // Logged in — send to dashboard
    header('Location: ' . BASE_URL . 'public/dashboard.php');
    exit();
}

// Not logged in — send to unauthorized page
header('Location: ' . BASE_URL . 'modules/auth/unauthorized.php');
exit();