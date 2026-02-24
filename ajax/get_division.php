<?php
// ajax/get_division.php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$divisionId = intval($_GET['id'] ?? 0);

if (empty($divisionId)) {
    echo json_encode(['success' => false, 'message' => 'Invalid division ID']);
    exit();
}

try {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        SELECT 
            location_id as divisionId,
            location_name as divisionName
        FROM location 
        WHERE location_id = ? AND location_type_id = 1 AND is_deleted = '0'
    ");
    $stmt->execute([$divisionId]);
    $division = $stmt->fetch();
    
    if (!$division) {
        echo json_encode(['success' => false, 'message' => 'Division not found']);
        exit();
    }
    
    echo json_encode([
        'success' => true,
        'data' => $division
    ]);
    
} catch (PDOException $e) {
    logActivity('DATABASE_ERROR', MODULE_ORGANIZATION, "Division fetch error: " . $e->getMessage(), false);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}