<?php
/**
 * Main Configuration File
 * NIA UPRIIS ICT Inventory System
 */

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Manila');

// Session Configuration
// ini_set('session.cookie_httponly', 1);
// ini_set('session.use_only_cookies', 1);
// ini_set('session.cookie_secure', 0);
// ini_set('session.cookie_samesite', 'Strict');

// Application Settings
define('APP_NAME', 'NIA UPRIIS ICT Inventory System');
define('APP_VERSION', '1.0.0');

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
define('BASE_URL', $protocol . "://" . $host . "/ictequipment/");

// Session Settings
define('SESSION_LIFETIME', 3600); // 1 hour
define('REMEMBER_ME_LIFETIME', 2592000); // 30 days

// Security Settings
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// File Upload Settings
define('UPLOAD_MAX_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

/**
 * Generate CSRF Token
 */
function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verifyCSRFToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    
    return true;
}

/**
 * Sanitize Input
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    
    return $data;
}

/**
 * Validate Email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Hash Password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify Password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Redirect
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['user_id']) && isset($_SESSION['email']);
}

/**
 * Require login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect('/ictequipment/modules/auth/login.php');
    }
}

/**
 * Regenerate Session ID
 */
function regenerateSession() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}


// ============================================================
// AUDIT TRAIL — Standardised action constants
// Action  = WHAT operation was performed (the verb)
// Module  = WHERE it happened (the noun/section)
// Description = human-readable sentence about WHAT changed
// ============================================================

// --- Auth ---
define('ACTION_LOGIN',          'LOGIN');
define('ACTION_LOGOUT',         'LOGOUT');
define('ACTION_LOGIN_FAILED',   'LOGIN_FAILED');
define('ACTION_PASSWORD_RESET', 'PASSWORD_RESET');

// --- CRUD ---
define('ACTION_CREATE',  'CREATE');
define('ACTION_UPDATE',  'UPDATE');
define('ACTION_DELETE',  'DELETE');
define('ACTION_RESTORE', 'RESTORE');

// --- Data operations ---
define('ACTION_EXPORT',  'EXPORT');
define('ACTION_IMPORT',  'IMPORT');
define('ACTION_VIEW',    'VIEW');      // use sparingly – only sensitive views

// --- Module constants (WHERE) ---
define('MODULE_EMPLOYEES',         'Employees');
define('MODULE_COMPUTERS',         'Computers');
define('MODULE_PRINTERS',          'Printers');
define('MODULE_SOFTWARE',          'Software Licenses');
define('MODULE_OTHER_EQUIPMENT',   'Other Equipment');
define('MODULE_MAINTENANCE',       'Maintenance');
define('MODULE_DIVISIONS',         'Divisions');
define('MODULE_SECTIONS',          'Sections');
define('MODULE_UNITS',             'Units');
define('MODULE_ACCOUNTS',          'Accounts');
define('MODULE_AUDIT_TRAIL',       'Audit Trail');
define('MODULE_SETTINGS',          'Settings');
define('MODULE_PROFILE',           'Profile');
define('MODULE_AUTH',              'Authentication');
define('MODULE_ORGANIZATION',      'Organization');
define('MODULE_REPORTS',           'Reports');

/**
 * Log a standardised activity record.
 *
 * @param string      $action      One of the ACTION_* constants (e.g. ACTION_CREATE)
 * @param string|null $module      One of the MODULE_* constants (e.g. MODULE_EMPLOYEES)
 * @param string|null $description Plain-English sentence: "Added employee Juan Dela Cruz (ID 42)"
 * @param bool        $success     Whether the operation succeeded (default true)
 */
function logActivity(string $action, ?string $module = null, ?string $description = null, bool $success = true): void {
    try {
        $db = Database::getInstance()->getConnection();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_SESSION['user_id'] ?? null;
        $email  = $_SESSION['email'] ?? ($_SESSION['user_email'] ?? 'system');

        $stmt = $db->prepare("
            INSERT INTO activity_log
                (user_id, email, action, module, description, ip_address, user_agent, success, timestamp)
            VALUES
                (:user_id, :email, :action, :module, :description, :ip, :ua, :success, NOW())
        ");

        $stmt->execute([
            ':user_id'     => $userId,
            ':email'       => $email,
            ':action'      => $action,
            ':module'      => $module,
            ':description' => $description,
            ':ip'          => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            ':ua'          => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            ':success'     => $success ? 1 : 0,
        ]);

    } catch (PDOException $e) {
        error_log("Activity Log Error: " . $e->getMessage());
    }
}

/**
 * Send JSON Response
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

/**
 * Create a "Remember Me" token and set it as a cookie.
 * Uses a selector/validator split for security (no timing attacks).
 */
function createRememberToken($userId) {
    try {
        $selector = bin2hex(random_bytes(16));   // public lookup key
        $validator = bin2hex(random_bytes(32));   // secret validated via hash
        $hashedValidator = hash('sha256', $validator);
        $expires = date('Y-m-d H:i:s', time() + REMEMBER_ME_LIFETIME);

        $db = Database::getInstance()->getConnection();

        // Remove any existing tokens for this user (one active token per user)
        $stmt = $db->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
        $stmt->execute([$userId]);

        // Insert new token
        $stmt = $db->prepare("
            INSERT INTO remember_tokens (user_id, selector, hashed_validator, expires_at)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $selector, $hashedValidator, $expires]);

        // Set cookie  –  value is "selector:validator"
        setcookie('remember_me', $selector . ':' . $validator, [
            'expires'  => time() + REMEMBER_ME_LIFETIME,
            'path'     => '/',
            'httponly'  => true,
            'samesite' => 'Strict'
        ]);
    } catch (Exception $e) {
        error_log("Remember Token Error: " . $e->getMessage());
    }
}

/**
 * Validate a "Remember Me" cookie and auto-login the user.
 * Returns true if auto-login succeeded, false otherwise.
 */
function validateRememberToken() {
    if (empty($_COOKIE['remember_me'])) {
        return false;
    }

    $parts = explode(':', $_COOKIE['remember_me']);
    if (count($parts) !== 2) {
        clearRememberCookie();
        return false;
    }

    [$selector, $validator] = $parts;

    $db = Database::getInstance()->getConnection();

    // Purge expired tokens
    $db->exec("DELETE FROM remember_tokens WHERE expires_at < NOW()");

    // Lookup by selector
    $stmt = $db->prepare("
        SELECT rt.*, u.id AS uid, u.user_name, u.email, u.role, u.status
        FROM remember_tokens rt
        JOIN tbl_accounts u ON u.id = rt.user_id
        WHERE rt.selector = ?
        LIMIT 1
    ");
    $stmt->execute([$selector]);
    $row = $stmt->fetch();

    if (!$row) {
        clearRememberCookie();
        return false;
    }

    // Constant-time comparison of validator
    if (!hash_equals($row['hashed_validator'], hash('sha256', $validator))) {
        // Possible token theft – delete all tokens for this user
        $stmt = $db->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
        $stmt->execute([$row['user_id']]);
        clearRememberCookie();
        return false;
    }

    // Account must still be active
    if ($row['status'] !== 'Active') {
        $stmt = $db->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
        $stmt->execute([$row['user_id']]);
        clearRememberCookie();
        return false;
    }

    // Auto-login: set session
    session_regenerate_id(true);
    $_SESSION['user_id']       = $row['uid'];
    $_SESSION['user_name']     = $row['user_name'];
    $_SESSION['email']         = $row['email'];
    $_SESSION['role']          = $row['role'];
    $_SESSION['logged_in_at']  = time();
    $_SESSION['last_activity'] = time();
    $_SESSION['created']       = time();

    // Rotate the token (one-time use) for extra security
    $newValidator = bin2hex(random_bytes(32));
    $newHashedValidator = hash('sha256', $newValidator);
    $newExpires = date('Y-m-d H:i:s', time() + REMEMBER_ME_LIFETIME);

    $stmt = $db->prepare("
        UPDATE remember_tokens
        SET hashed_validator = ?, expires_at = ?
        WHERE selector = ?
    ");
    $stmt->execute([$newHashedValidator, $newExpires, $selector]);

    // Update cookie with new validator
    setcookie('remember_me', $selector . ':' . $newValidator, [
        'expires'  => time() + REMEMBER_ME_LIFETIME,
        'path'     => '/',
        'httponly'  => true,
        'samesite' => 'Strict'
    ]);

    return true;
}

/**
 * Clear the Remember Me cookie and remove tokens for a user.
 */
function clearRememberToken($userId = null) {
    clearRememberCookie();

    if ($userId) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
            $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("Clear Remember Token Error: " . $e->getMessage());
        }
    }
}

/**
 * Clear the Remember Me cookie.
 */
function clearRememberCookie() {
    setcookie('remember_me', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'httponly'  => true,
        'samesite' => 'Strict'
    ]);
}

/**
 * Get client IP address
 */
function getClientIP() {
    $ipaddress = '';
    
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    
    return $ipaddress;
}