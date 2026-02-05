<?php
// ajax/manage_section.php
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
            $sectionName = sanitize($_POST['sectionName'] ?? '');
            $divisionId = intval($_POST['divisionId'] ?? 0);
            
            if (empty($sectionName) || empty($divisionId)) {
                throw new Exception('All fields are required');
            }
            
            // Check if section name already exists
            $stmt = $db->prepare("
                SELECT location_id FROM location 
                WHERE location_name = ? AND location_type_id = 2 AND is_deleted = '0'
            ");
            $stmt->execute([$sectionName]);
            
            if ($stmt->fetch()) {
                throw new Exception('Section name already exists');
            }
            
            // Verify division exists
            $stmt = $db->prepare("
                SELECT location_id FROM location 
                WHERE location_id = ? AND location_type_id = 1 AND is_deleted = '0'
            ");
            $stmt->execute([$divisionId]);
            
            if (!$stmt->fetch()) {
                throw new Exception('Invalid division selected');
            }
            
            // Insert new section (location_type_id = 2 for Section)
            $stmt = $db->prepare("
                INSERT INTO location (location_name, location_type_id, parent_location_id, created_at, is_deleted) 
                VALUES (?, 2, ?, NOW(), '0')
            ");
            $stmt->execute([$sectionName, $divisionId]);
            
            logActivity("Section created: {$sectionName}", 'INFO');
            
            echo json_encode([
                'success' => true,
                'message' => 'Section added successfully'
            ]);
            break;
            
        case 'update':
            $sectionId = intval($_POST['sectionId'] ?? 0);
            $sectionName = sanitize($_POST['sectionName'] ?? '');
            $divisionId = intval($_POST['divisionId'] ?? 0);
            
            if (empty($sectionId) || empty($sectionName) || empty($divisionId)) {
                throw new Exception('All fields are required');
            }
            
            // Check if section name already exists (excluding current section)
            $stmt = $db->prepare("
                SELECT location_id FROM location 
                WHERE location_name = ? AND location_type_id = 2 AND location_id != ? AND is_deleted = '0'
            ");
            $stmt->execute([$sectionName, $sectionId]);
            
            if ($stmt->fetch()) {
                throw new Exception('Section name already exists');
            }
            
            // Verify division exists
            $stmt = $db->prepare("
                SELECT location_id FROM location 
                WHERE location_id = ? AND location_type_id = 1 AND is_deleted = '0'
            ");
            $stmt->execute([$divisionId]);
            
            if (!$stmt->fetch()) {
                throw new Exception('Invalid division selected');
            }
            
            // Update section
            $stmt = $db->prepare("
                UPDATE location 
                SET location_name = ?, parent_location_id = ?
                WHERE location_id = ? AND location_type_id = 2
            ");
            $stmt->execute([$sectionName, $divisionId, $sectionId]);
            
            logActivity("Section updated: {$sectionName}", 'INFO');
            
            echo json_encode([
                'success' => true,
                'message' => 'Section updated successfully'
            ]);
            break;
            
        case 'delete':
            $sectionId = intval($_POST['sectionId'] ?? 0);
            
            if (empty($sectionId)) {
                throw new Exception('Invalid section ID');
            }
            
            // Check if section has employees
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM tbl_employee WHERE sectionId = ?");
            $stmt->execute([$sectionId]);
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                throw new Exception('Cannot delete section with existing employees. Please delete or reassign employees first.');
            }
            
            // Check if section has child units
            $stmt = $db->prepare("
                SELECT COUNT(*) as count FROM location 
                WHERE parent_location_id = ? AND location_type_id = 3 AND is_deleted = '0'
            ");
            $stmt->execute([$sectionId]);
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                throw new Exception('Cannot delete section with existing units. Please delete or reassign units first.');
            }
            
            // Get section name for logging
            $stmt = $db->prepare("SELECT location_name FROM location WHERE location_id = ?");
            $stmt->execute([$sectionId]);
            $section = $stmt->fetch();
            
            // Soft delete section (set is_deleted = '1')
            $stmt = $db->prepare("UPDATE location SET is_deleted = '1' WHERE location_id = ?");
            $stmt->execute([$sectionId]);
            
            logActivity("Section deleted: " . ($section['location_name'] ?? $sectionId), 'INFO');
            
            echo json_encode([
                'success' => true,
                'message' => 'Section deleted successfully'
            ]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (PDOException $e) {
    logActivity("Database error in manage_section: " . $e->getMessage(), 'ERROR');
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