<?php
// ajax/manage_division.php
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
            $divisionName = sanitize($_POST['divisionName'] ?? '');
            
            if (empty($divisionName)) {
                throw new Exception('Division name is required');
            }
            
            // Check if division name already exists
            $stmt = $db->prepare("
                SELECT location_id FROM location 
                WHERE location_name = ? AND location_type_id = 1 AND is_deleted = '0'
            ");
            $stmt->execute([$divisionName]);
            
            if ($stmt->fetch()) {
                throw new Exception('Division name already exists');
            }
            
            // Insert new division (location_type_id = 1 for Division)
            $stmt = $db->prepare("
                INSERT INTO location (location_name, location_type_id, parent_location_id, created_at, is_deleted) 
                VALUES (?, 1, NULL, NOW(), '0')
            ");
            $stmt->execute([$divisionName]);
            
            logActivity(ACTION_CREATE, MODULE_ORGANIZATION, "Created division: {$divisionName}");
            
            echo json_encode([
                'success' => true,
                'message' => 'Division added successfully'
            ]);
            break;
            
        case 'update':
            $divisionId = intval($_POST['divisionId'] ?? 0);
            $divisionName = sanitize($_POST['divisionName'] ?? '');
            
            if (empty($divisionId) || empty($divisionName)) {
                throw new Exception('All fields are required');
            }
            
            // Check if division name already exists (excluding current division)
            $stmt = $db->prepare("
                SELECT location_id FROM location 
                WHERE location_name = ? AND location_type_id = 1 AND location_id != ? AND is_deleted = '0'
            ");
            $stmt->execute([$divisionName, $divisionId]);
            
            if ($stmt->fetch()) {
                throw new Exception('Division name already exists');
            }
            
            // Update division
            $stmt = $db->prepare("
                UPDATE location 
                SET location_name = ?
                WHERE location_id = ? AND location_type_id = 1
            ");
            $stmt->execute([$divisionName, $divisionId]);
            
            logActivity(ACTION_UPDATE, MODULE_ORGANIZATION, "Updated division: {$divisionName}");
            
            echo json_encode([
                'success' => true,
                'message' => 'Division updated successfully'
            ]);
            break;
            
        case 'delete':
            $divisionId = intval($_POST['divisionId'] ?? 0);
            
            if (empty($divisionId)) {
                throw new Exception('Invalid division ID');
            }
            
            // Check if division has sections
            $stmt = $db->prepare("
                SELECT COUNT(*) as count FROM location 
                WHERE parent_location_id = ? AND location_type_id = 2 AND is_deleted = '0'
            ");
            $stmt->execute([$divisionId]);
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                throw new Exception('Cannot delete division with existing sections. Please delete or reassign sections first.');
            }
            
            // Get division name for logging
            $stmt = $db->prepare("SELECT location_name FROM location WHERE location_id = ?");
            $stmt->execute([$divisionId]);
            $division = $stmt->fetch();
            
            // Soft delete division (set is_deleted = '1')
            $stmt = $db->prepare("UPDATE location SET is_deleted = '1' WHERE location_id = ?");
            $stmt->execute([$divisionId]);
            
            logActivity(ACTION_DELETE, MODULE_ORGANIZATION, "Deleted division: " . ($division['location_name'] ?? $divisionId));
            
            echo json_encode([
                'success' => true,
                'message' => 'Division deleted successfully'
            ]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (PDOException $e) {
    logActivity('DATABASE_ERROR', MODULE_ORGANIZATION, "Division error: " . $e->getMessage(), false);
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