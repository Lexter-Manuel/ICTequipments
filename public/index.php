<?php

session_start();
require_once __DIR__ . '/../config/config.php';

if (!empty($_SESSION['user_id'])) {
    // Logged in — send to dashboard
    header('Location: dashboard.php');
    exit();
}

header('Location: ' . BASE_URL . 'modules/auth/unauthorized.php');
exit();