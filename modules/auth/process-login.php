<?php
/**
 * Process Login Script
 * NIA UPRIIS ICT Inventory System
 */

// Start session FIRST before any other code
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once '../../config/database.php';
require_once '../../config/config.php';

// Set JSON header
header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse([
        'success' => false,
        'message' => 'Invalid request method'
    ], 405);
}

try {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        jsonResponse([
            'success' => false,
            'message' => 'Invalid security token. Please refresh the page and try again.'
        ], 403);
    }
    
    // Get and sanitize input
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; // Don't sanitize password
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validate input
    if (empty($email) || empty($password)) {
        jsonResponse([
            'success' => false,
            'message' => 'Email and password are required'
        ], 400);
    }
    
    // Validate email format
    if (!validateEmail($email)) {
        jsonResponse([
            'success' => false,
            'message' => 'Please enter a valid email address'
        ], 400);
    }
    
    // Check for too many failed attempts
    $ipAddress = getClientIP();
    $db = Database::getInstance()->getConnection();
    
    // Check failed login attempts
    $stmt = $db->prepare("
        SELECT COUNT(*) as attempts 
        FROM login_attempts 
        WHERE ip_address = ? 
        AND attempt_time > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
        AND success = 0
    ");
    $stmt->execute([$ipAddress]);
    $attempts = $stmt->fetch();
    
    if ($attempts['attempts'] >= MAX_LOGIN_ATTEMPTS) {
        jsonResponse([
            'success' => false,
            'message' => 'Too many failed login attempts. Please try again in 1 minute.'
        ], 429);
    }
    
    // Find user by email
    $stmt = $db->prepare("
        SELECT id, user_name, email, password, role, status 
        FROM tbl_accounts 
        WHERE email = ? 
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    // Log failed attempt if user not found
    if (!$user) {
        logLoginAttempt($ipAddress, $email, false);
        
        jsonResponse([
            'success' => false,
            'message' => 'Invalid email or password'
        ], 401);
    }
    
    // Check if account is active
    if ($user['status'] !== 'Active') {
        jsonResponse([
            'success' => false,
            'message' => 'Your account has been deactivated. Please contact the administrator.'
        ], 403);
    }
    
    // Verify password
    if (!verifyPassword($password, $user['password'])) {
        logLoginAttempt($ipAddress, $email, false);
        
        jsonResponse([
            'success' => false,
            'message' => 'Invalid email or password'
        ], 401);
    }
    
    // Login successful - regenerate session ID for security
    session_regenerate_id(true);
    
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['user_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['logged_in_at'] = time();
    $_SESSION['last_activity'] = time();
    $_SESSION['created'] = time();
    
    // Set cookie lifetime based on remember me
    if ($remember) {
        setcookie(session_name(), session_id(), [
            'expires' => time() + REMEMBER_ME_LIFETIME,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }
    
    // Log successful login
    logLoginAttempt($ipAddress, $email, true);
    logActivity($user['id'], 'login', 'User logged in successfully');
    
    // Update last login time
    $stmt = $db->prepare("
        UPDATE tbl_accounts 
        SET updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$user['id']]);
    
    // Clear failed attempts for this IP
    $stmt = $db->prepare("
        DELETE FROM login_attempts 
        WHERE ip_address = ?
    ");
    $stmt->execute([$ipAddress]);
    
    // Check if there's a redirect URL
    $redirect = $_SESSION['redirect_after_login'] ?? '../../public/dashboard.php';
    unset($_SESSION['redirect_after_login']);
    
    jsonResponse([
        'success' => true,
        'message' => 'Login successful',
        'redirect' => $redirect,
        'user' => [
            'name' => $user['user_name'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ], 200);
    
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    
    jsonResponse([
        'success' => false,
        'message' => 'A database error occurred. Please try again.'
    ], 500);
} catch (Exception $e) {
    error_log("Login Error: " . $e->getMessage());
    
    jsonResponse([
        'success' => false,
        'message' => 'An unexpected error occurred. Please try again.'
    ], 500);
}

/**
 * Log login attempt
 */
function logLoginAttempt($ipAddress, $email, $success) {
    try {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            INSERT INTO login_attempts (ip_address, email, success, attempt_time)
            VALUES (?, ?, ?, NOW())
        ");
        
        $stmt->execute([$ipAddress, $email, $success ? 1 : 0]);
        
    } catch (PDOException $e) {
        error_log("Login Attempt Log Error: " . $e->getMessage());
    }
}