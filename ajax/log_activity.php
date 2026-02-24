<?php
/**
 * Activity Logger - Logs user actions to the activity_log table
 * 
 * Usage: POST request with fields: action, module, description
 * Also called internally by logActivity() helper function
 */
require_once '../config/database.php';

/**
 * Log an activity to the database
 */
function logActivity($action, $module = null, $description = null, $userId = null, $email = null) {
    try {
        $db = Database::getInstance()->getConnection();
        
        // Get user info from session if not provided
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $userId = $userId ?? ($_SESSION['user_id'] ?? null);
        $email  = $email ?? ($_SESSION['user_email'] ?? ($_SESSION['email'] ?? 'system'));
        
        $stmt = $db->prepare("
            INSERT INTO activity_log (user_id, email, action, module, description, ip_address, user_agent, success, timestamp)
            VALUES (:user_id, :email, :action, :module, :description, :ip, :ua, 1, NOW())
        ");
        
        $stmt->execute([
            ':user_id'    => $userId,
            ':email'      => $email,
            ':action'     => $action,
            ':module'     => $module,
            ':description'=> $description,
            ':ip'         => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            ':ua'         => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        ]);
        
        return true;
    } catch (PDOException $e) {
        error_log("Activity log error: " . $e->getMessage());
        return false;
    }
}

// Handle direct POST requests (from AJAX beacon or form)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action      = $_POST['action'] ?? $_POST['page'] ?? 'page_view';
    $module      = $_POST['module'] ?? null;
    $description = $_POST['description'] ?? $_POST['timestamp'] ?? null;
    
    // Don't log page views (too noisy) - only log meaningful actions
    if ($action === 'page_view' || $action === 'page') {
        http_response_code(204);
        exit;
    }
    
    logActivity($action, $module, $description);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
}
