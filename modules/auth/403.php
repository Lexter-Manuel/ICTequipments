<?php
/**
 * Custom 403 – Forbidden handler
 * If logged in → show 404 page, otherwise → show unauthorized page.
 */

session_start();

if (!empty($_SESSION['user_id'])) {
    // Logged in – show the 404 page
    include __DIR__ . '/404.php';
} else {
    // Not logged in – show unauthorized page
    include __DIR__ . '/unauthorized.php';
}
