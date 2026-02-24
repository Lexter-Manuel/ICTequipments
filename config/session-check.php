<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/permissions.php';

if (!isLoggedIn()) {
    // Try auto-login via "Remember Me" cookie before redirecting
    if (validateRememberToken()) {
        // Auto-login succeeded – continue normally
    } else {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: /ictequipment/modules/auth/login.php');
        exit();
    }
}

if (isset($_SESSION['last_activity'])) {
    $inactive = time() - $_SESSION['last_activity'];
    
    if ($inactive > SESSION_LIFETIME) {
        // Session timed out – try "Remember Me" before kicking to login
        if (!empty($_COOKIE['remember_me'])) {
            session_unset();
            session_destroy();
            session_start();
            if (validateRememberToken()) {
                // Auto-login succeeded after timeout – continue
            } else {
                header('Location: /ictequipment/modules/auth/login.php?timeout=1');
                exit();
            }
        } else {
            session_unset();
            session_destroy();
            header('Location: /ictequipment/modules/auth/login.php?timeout=1');
            exit();
        }
    }
}

$_SESSION['last_activity'] = time();

if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} else if (time() - $_SESSION['created'] > 1800) {
    regenerateSession();
    $_SESSION['created'] = time();
}

$current_user = getCurrentUser();