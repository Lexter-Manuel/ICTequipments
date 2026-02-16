<?php
/**
 * Role-Based Access Control and Permissions
 * NIA UPRIIS ICT Inventory System
 */

/**
 * Check if user has permission for a specific action
 * 
 * @param string $module The module name (e.g., 'employees', 'equipment')
 * @param string $action The action (view, create, update, delete, export)
 * @return bool
 */
function hasPermission($module, $action) {
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        return false;
    }
    
    // Super Admin has all permissions
    if ($_SESSION['role'] === 'Super Admin') {
        return true;
    }
    
    // Check database for admin permissions
    try {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT * FROM tbl_role_permissions 
            WHERE role = ? AND module = ?
            LIMIT 1
        ");
        
        $stmt->execute([$_SESSION['role'], $module]);
        $perm = $stmt->fetch();
        
        if (!$perm) {
            return false;
        }
        
        // Check specific permission
        switch ($action) {
            case 'view':
                return $perm['can_view'] == 1;
            case 'create':
                return $perm['can_create'] == 1;
            case 'update':
                return $perm['can_update'] == 1;
            case 'delete':
                return $perm['can_delete'] == 1;
            case 'export':
                return $perm['can_export'] == 1;
            default:
                return false;
        }
        
    } catch (PDOException $e) {
        error_log("Permission Check Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Require permission or terminate with error
 * 
 * @param string $module The module name
 * @param string $action The action
 */
function requirePermission($module, $action) {
    if (!hasPermission($module, $action)) {
        http_response_code(403);
        
        // Check if AJAX request
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => "Access Denied. You do not have permission to {$action} {$module}."
            ]);
            exit();
        }
        
        // Regular request
        $_SESSION['error_message'] = "Access Denied. You do not have permission to {$action} {$module}.";
        header('Location: ../../public/dashboard.php');
        exit();
    }
}

/**
 * Check if current user is Super Admin
 * 
 * @return bool
 */
function isSuperAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'Super Admin';
}

/**
 * Check if current user is Admin (any type)
 * 
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['role']) && 
           ($_SESSION['role'] === 'Super Admin' || $_SESSION['role'] === 'Admin');
}

/**
 * Require Super Admin access
 */
function requireSuperAdmin() {
    if (!isSuperAdmin()) {
        http_response_code(403);
        
        // Check if AJAX request
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Access Denied. Super Admin access required.'
            ]);
            exit();
        }
        
        // Regular request
        $_SESSION['error_message'] = 'Access Denied. Super Admin access required.';
        header('Location: ../../public/dashboard.php');
        exit();
    }
}

/**
 * Require Admin access (Super Admin or Admin)
 */
function requireAdmin() {
    if (!isAdmin()) {
        http_response_code(403);
        
        // Check if AJAX request
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Access Denied. Administrator access required.'
            ]);
            exit();
        }
        
        // Regular request
        $_SESSION['error_message'] = 'Access Denied. Administrator access required.';
        header('Location: ../../public/dashboard.php');
        exit();
    }
}

/**
 * Get user permissions for a module
 * 
 * @param string $module The module name
 * @return array Array of permissions
 */
function getUserPermissions($module) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        return [
            'can_view' => false,
            'can_create' => false,
            'can_update' => false,
            'can_delete' => false,
            'can_export' => false
        ];
    }
    
    // Super Admin has all permissions
    if ($_SESSION['role'] === 'Super Admin') {
        return [
            'can_view' => true,
            'can_create' => true,
            'can_update' => true,
            'can_delete' => true,
            'can_export' => true
        ];
    }
    
    try {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT can_view, can_create, can_update, can_delete, can_export
            FROM tbl_role_permissions 
            WHERE role = ? AND module = ?
            LIMIT 1
        ");
        
        $stmt->execute([$_SESSION['role'], $module]);
        $perms = $stmt->fetch();
        
        if (!$perms) {
            return [
                'can_view' => false,
                'can_create' => false,
                'can_update' => false,
                'can_delete' => false,
                'can_export' => false
            ];
        }
        
        return [
            'can_view' => (bool)$perms['can_view'],
            'can_create' => (bool)$perms['can_create'],
            'can_update' => (bool)$perms['can_update'],
            'can_delete' => (bool)$perms['can_delete'],
            'can_export' => (bool)$perms['can_export']
        ];
        
    } catch (PDOException $e) {
        error_log("Get Permissions Error: " . $e->getMessage());
        return [
            'can_view' => false,
            'can_create' => false,
            'can_update' => false,
            'can_delete' => false,
            'can_export' => false
        ];
    }
}

/**
 * Get current user info
 * 
 * @return array|null User data or null if not logged in
 */
function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'role' => $_SESSION['role'] ?? '',
        'is_super_admin' => isSuperAdmin(),
        'is_admin' => isAdmin()
    ];
}