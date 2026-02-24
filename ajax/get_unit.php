<?php
// ajax/get_unit.php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$unitId = intval($_GET['id'] ?? 0);

if (empty($unitId)) {
    echo json_encode(['success' => false, 'message' => 'Invalid unit ID']);
    exit();
}

try {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        SELECT 
            u.location_id as unitId,
            u.location_name as unitName,
            u.parent_location_id as parentId,
            p.location_type_id as parentTypeId,
            p.parent_location_id as divisionId
        FROM location u
        LEFT JOIN location p ON u.parent_location_id = p.location_id
        WHERE u.location_id = ? AND u.location_type_id = 3 AND u.is_deleted = '0'
    ");
    $stmt->execute([$unitId]);
    $unit = $stmt->fetch();
    
    if (!$unit) {
        echo json_encode(['success' => false, 'message' => 'Unit not found']);
        exit();
    }
    
    // If parent is a division (type 1), use parent as division
    if ($unit['parentTypeId'] == 1) {
        $unit['divisionId'] = $unit['parentId'];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $unit
    ]);
    
} catch (PDOException $e) {
    logActivity('DATABASE_ERROR', MODULE_ORGANIZATION, "Unit fetch error: " . $e->getMessage(), false);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}