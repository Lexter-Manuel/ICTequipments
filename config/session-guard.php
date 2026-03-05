<?php
/**
 * Session Guard
 * Lightweight include that protects module and AJAX files from unauthorized access.
 *
 * Usage (at the top of every module / AJAX file):
 *   require_once __DIR__ . '/../../config/session-guard.php';
 *      — or —
 *   require_once '../config/session-guard.php';
 *
 * Behavior:
 *  • For AJAX/fetch requests (XMLHttpRequest or Accept: application/json):
 *      Returns  {"unauthorized": true, "message": "..."}  with HTTP 401.
 *  • For direct browser requests:
 *      Redirects to the unauthorized error page which then sends user to login.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Quick check — if user_id exists in session, they're logged in
if (!empty($_SESSION['user_id'])) {
    return; // authenticated — carry on
}

// ── Not authenticated ──────────────────────────────────────────────

// Detect AJAX / fetch requests
$isAjax = (
    (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
    || (isset($_SERVER['HTTP_SEC_FETCH_DEST']) && $_SERVER['HTTP_SEC_FETCH_DEST'] === 'empty')
);

if ($isAjax) {
    // AJAX request — return JSON so the JS can detect it and redirect
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode([
        'unauthorized' => true,
        'message'      => 'Session expired. Please log in again.'
    ]);
    exit();
}

// Direct browser access — redirect to the unauthorized page
$_envPath = __DIR__ . '/.env';
$_envData = file_exists($_envPath) ? parse_ini_file($_envPath) : [];
$_baseFolder = $_envData['BASE_FOLDER'] ?? 'iedevelopment';
header('Location: /' . $_baseFolder . '/modules/auth/unauthorized.php');
exit();
