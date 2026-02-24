<?php
// ajax/manage_unit.php
// Prevent any output before JSON
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to output
ini_set('log_errors', 1); // Log errors instead

session_start();
require_once '../config/database.php';
require_once '../config/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$db = Database::getInstance()->getConnection();
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'create':
            $unitName = trim($_POST['unitName'] ?? '');
            $parentId = intval($_POST['parentId'] ?? 0);
            
            if (empty($unitName) || empty($parentId)) {
                throw new Exception('All fields are required');
            }
            
            // Check if unit name already exists
            $stmt = $db->prepare("
                SELECT location_id FROM location 
                WHERE location_name = ? AND location_type_id = 3 AND is_deleted = '0'
            ");
            $stmt->execute([$unitName]);
            
            if ($stmt->fetch()) {
                throw new Exception('Unit name already exists');
            }
            
            // Verify parent exists and is either a division (type 1) or section (type 2)
            $stmt = $db->prepare("
                SELECT location_id, location_type_id FROM location 
                WHERE location_id = ? AND location_type_id IN (1, 2) AND is_deleted = '0'
            ");
            $stmt->execute([$parentId]);
            
            if (!$stmt->fetch()) {
                throw new Exception('Invalid parent location selected. Parent must be a division or section.');
            }
            
            // Insert new unit (location_type_id = 3 for Unit)
            $stmt = $db->prepare("
                INSERT INTO location (location_name, location_type_id, parent_location_id, created_at, is_deleted) 
                VALUES (?, 3, ?, NOW(), '0')
            ");
            $stmt->execute([$unitName, $parentId]);
            
            logActivity(ACTION_CREATE, MODULE_ORGANIZATION, "Created unit: {$unitName}");
            
            echo json_encode([
                'success' => true,
                'message' => 'Unit added successfully'
            ]);
            break;
            
        case 'update':
            $unitId = intval($_POST['unitId'] ?? 0);
            $unitName = trim($_POST['unitName'] ?? '');
            $parentId = intval($_POST['parentId'] ?? 0);
            
            if (empty($unitId) || empty($unitName) || empty($parentId)) {
                throw new Exception('All fields are required');
            }
            
            // Check if unit name already exists (excluding current unit)
            $stmt = $db->prepare("
                SELECT location_id FROM location 
                WHERE location_name = ? AND location_type_id = 3 AND location_id != ? AND is_deleted = '0'
            ");
            $stmt->execute([$unitName, $unitId]);
            
            if ($stmt->fetch()) {
                throw new Exception('Unit name already exists');
            }
            
            // Verify parent exists and is either a division (type 1) or section (type 2)
            $stmt = $db->prepare("
                SELECT location_id, location_type_id FROM location 
                WHERE location_id = ? AND location_type_id IN (1, 2) AND is_deleted = '0'
            ");
            $stmt->execute([$parentId]);
            
            if (!$stmt->fetch()) {
                throw new Exception('Invalid parent location selected. Parent must be a division or section.');
            }
            
            // Update unit
            $stmt = $db->prepare("
                UPDATE location 
                SET location_name = ?, parent_location_id = ?
                WHERE location_id = ? AND location_type_id = 3
            ");
            $stmt->execute([$unitName, $parentId, $unitId]);
            
            logActivity(ACTION_UPDATE, MODULE_ORGANIZATION, "Updated unit: {$unitName}");
            
            echo json_encode([
                'success' => true,
                'message' => 'Unit updated successfully'
            ]);
            break;
            
        case 'delete':
            $unitId = intval($_POST['unitId'] ?? 0);
            
            if (empty($unitId)) {
                throw new Exception('Invalid unit ID');
            }
            
            // Check if unit has employees
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM tbl_employee WHERE location_id = ?");
            $stmt->execute([$unitId]);
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                throw new Exception('Cannot delete unit with existing employees. Please delete or reassign employees first.');
            }
            
            // Get unit name for logging
            $stmt = $db->prepare("SELECT location_name FROM location WHERE location_id = ?");
            $stmt->execute([$unitId]);
            $unit = $stmt->fetch();

            $stmt = $db->prepare("UPDATE location SET is_deleted = '1' WHERE location_id = ?");
            $stmt->execute([$unitId]);
            
            logActivity(ACTION_DELETE, MODULE_ORGANIZATION, "Deleted unit: " . ($unit['location_name'] ?? $unitId));
            
            echo json_encode([
                'success' => true,
                'message' => 'Unit deleted successfully'
            ]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (PDOException $e) {
    logActivity('DATABASE_ERROR', MODULE_ORGANIZATION, "Unit error: " . $e->getMessage(), false);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// Catch any fatal errors or warnings that might output HTML
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        echo json_encode([
            'success' => false,
            'message' => 'Server error: ' . $error['message']
        ]);
    }
});