<?php
// ajax/get_section.php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$sectionId = intval($_GET['id'] ?? 0);

if (empty($sectionId)) {
    echo json_encode(['success' => false, 'message' => 'Invalid section ID']);
    exit();
}

try {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        SELECT 
            location_id as sectionId,
            location_name as sectionName,
            parent_location_id as divisionId
        FROM location 
        WHERE location_id = ? AND location_type_id = 2 AND is_deleted = '0'
    ");
    $stmt->execute([$sectionId]);
    $section = $stmt->fetch();
    
    if (!$section) {
        echo json_encode(['success' => false, 'message' => 'Section not found']);
        exit();
    }
    
    echo json_encode([
        'success' => true,
        'data' => $section
    ]);
    
} catch (PDOException $e) {
    logActivity("Database error in get_section: " . $e->getMessage(), 'ERROR');
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}