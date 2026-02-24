<?php
/**
 * Activity Logger - AJAX endpoint for client-side activity logging
 * 
 * Usage: POST request with fields: action, module, description
 * The canonical logActivity() function lives in config/config.php
 */
require_once '../config/database.php';
require_once '../config/config.php';

// Handle direct POST requests (from AJAX beacon or form)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $action      = $_POST['action'] ?? null;
    $module      = $_POST['module'] ?? null;
    $description = $_POST['description'] ?? null;
    
    // Don't log page views / navigation (too noisy) - only log meaningful actions
    if (!$action || in_array(strtoupper($action), ['PAGE_VIEW', 'PAGE', 'VIEW', 'NAVIGATE'])) {
        http_response_code(204);
        exit;
    }
    
    logActivity($action, $module, $description);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
}
