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

// Load config first so the custom session name is set before session_start()
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Helper: detect AJAX/fetch requests ─────────────────────────
$_sg_isAjax = (
    (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
    || (isset($_SERVER['HTTP_SEC_FETCH_DEST']) && $_SERVER['HTTP_SEC_FETCH_DEST'] === 'empty')
);

// ── Helper: terminate with 401/redirect ────────────────────────
function _sgDeny(string $message = 'Session expired. Please log in again.'): void {
    global $_sg_isAjax;
    if ($_sg_isAjax) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['unauthorized' => true, 'message' => $message]);
        exit();
    }
    $_envPath = __DIR__ . '/.env';
    $_envData = file_exists($_envPath) ? parse_ini_file($_envPath) : [];
    $_baseFolder = $_envData['BASE_FOLDER'] ?? 'iedevelopment';
    header('Location: /' . $_baseFolder . '/modules/auth/unauthorized.php');
    exit();
}

// ── 1. Authentication check ────────────────────────────────────
if (empty($_SESSION['user_id'])) {
    _sgDeny();
}

// ── 2. Session fingerprint validation (hijack protection) ──────
// Build a fingerprint from user-agent + a portion of the IP
$_sg_fingerprint = hash('sha256',
    ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown')
    . '|' . substr($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', 0, strrpos($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', '.'))
);

if (!isset($_SESSION['_fingerprint'])) {
    // First request after login — store the fingerprint
    $_SESSION['_fingerprint'] = $_sg_fingerprint;
} elseif (!hash_equals($_SESSION['_fingerprint'], $_sg_fingerprint)) {
    // Fingerprint mismatch — possible session hijacking
    session_unset();
    session_destroy();
    _sgDeny('Session invalidated for security. Please log in again.');
}

// ── Authenticated — carry on ───────────────────────────────────

// ── 3. Module direct-access protection ─────────────────────────
// Module pages (outside auth/) must be loaded via AJAX/fetch, not direct browser navigation.
// This is the PHP-level fallback for browsers that don't send Sec-Fetch-* headers.
$_sg_callerPath = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');
if (strpos($_sg_callerPath, '/modules/') !== false && strpos($_sg_callerPath, '/modules/auth/') === false) {
    $_sg_secFetchDest = $_SERVER['HTTP_SEC_FETCH_DEST'] ?? null;
    $_sg_accept       = $_SERVER['HTTP_ACCEPT'] ?? '*/*';

    // Modern browsers: Sec-Fetch-Dest is "document" for direct navigation, "empty" for fetch()
    // Old browsers:    Accept contains "text/html" for navigation, but fetch() sends "*/*"
    $_sg_isDirectNav = (
        ($_sg_secFetchDest !== null && $_sg_secFetchDest === 'document')
        || ($_sg_secFetchDest === null && strpos($_sg_accept, 'text/html') !== false)
    );

    if ($_sg_isDirectNav) {
        $_envPath = __DIR__ . '/.env';
        $_envData = file_exists($_envPath) ? parse_ini_file($_envPath) : [];
        $_baseFolder = $_envData['BASE_FOLDER'] ?? 'iedevelopment';
        header('Location: /' . $_baseFolder . '/');
        exit();
    }
}

return;
