<?php
/**
 * All-in-One PC Management AJAX Handler
 * Handles all CRUD operations for all-in-one computers
 * Works with existing tbl_allinone schema
 */

require_once '../config/database.php';

header('Content-Type: application/json');

$db = getDB();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            listAllInOnes($db);
            break;
        case 'get':
            getAllInOne($db);
            break;
        case 'create':
            createAllInOne($db);
            break;
        case 'update':
            updateAllInOne($db);
            break;
        case 'delete':
            deleteAllInOne($db);
            break;
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function listAllInOnes($db) {
    $search = $_GET['search'] ?? '';
    $sql = "
        SELECT 
            a.*,
            CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) as employeeName
        FROM tbl_allinone a
        LEFT JOIN tbl_employee e ON a.employeeId = e.employeeId
        WHERE 1=1
    ";
    
    $params = [];
    if (!empty($search)) {
        $sql .= " AND (
            a.allinoneBrand LIKE :search OR
            a.specificationProcessor LIKE :search
        )";
        $params[':search'] = "%$search%";
    }
    
    $sql .= " ORDER BY a.allinoneId DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $units = $stmt->fetchAll();
    
    $formatted = array_map(function($a) {
        return [
            'allinoneId' => $a['allinoneId'],
            'allinoneBrand' => $a['allinoneBrand'],
            'specificationProcessor' => $a['specificationProcessor'],
            'specificationMemory' => $a['specificationMemory'],
            'specificationGPU' => $a['specificationGPU'],
            'specificationStorage' => $a['specificationStorage'],
            'serial' => 'AIO-' . str_pad($a['allinoneId'], 6, '0', STR_PAD_LEFT), // Generated since not in schema
            'screenSize' => '24 inches', // Default since not in schema
            'yearAcquired' => '2024', // Default since not in schema
            'employeeId' => $a['employeeId'],
            'employeeName' => $a['employeeName'],
            'status' => $a['employeeId'] ? 'Active' : 'Available'
        ];
    }, $units);
    
    echo json_encode(['success' => true, 'data' => $formatted]);
}

function getAllInOne($db) {
    $id = $_GET['allinone_id'] ?? null;
    if (!$id) throw new Exception('All-in-One ID is required');
    
    $stmt = $db->prepare("
        SELECT a.*, CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) as employeeName
        FROM tbl_allinone a
        LEFT JOIN tbl_employee e ON a.employeeId = e.employeeId
        WHERE a.allinoneId = :id
    ");
    $stmt->execute([':id' => $id]);
    $unit = $stmt->fetch();
    
    if (!$unit) throw new Exception('All-in-One not found');
    
    $formatted = [
        'allinoneId' => $unit['allinoneId'],
        'allinoneBrand' => $unit['allinoneBrand'],
        'specificationProcessor' => $unit['specificationProcessor'],
        'specificationMemory' => $unit['specificationMemory'],
        'specificationGPU' => $unit['specificationGPU'],
        'specificationStorage' => $unit['specificationStorage'],
        'serial' => 'AIO-' . str_pad($unit['allinoneId'], 6, '0', STR_PAD_LEFT),
        'screenSize' => '24 inches',
        'yearAcquired' => '2024',
        'employeeId' => $unit['employeeId'],
        'employeeName' => $unit['employeeName'],
        'status' => $unit['employeeId'] ? 'Active' : 'Available'
    ];
    
    echo json_encode(['success' => true, 'data' => $formatted]);
}

function createAllInOne($db) {
    $required = ['brand', 'processor', 'memory', 'gpu', 'storage'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Field '$field' is required");
        }
    }
    
    $stmt = $db->prepare("
        INSERT INTO tbl_allinone (
            allinoneBrand, specificationProcessor, specificationMemory,
            specificationGPU, specificationStorage, employeeId
        ) VALUES (
            :brand, :processor, :memory, :gpu, :storage, :employeeId
        )
    ");
    
    $stmt->execute([
        ':brand' => $_POST['brand'],
        ':processor' => $_POST['processor'],
        ':memory' => $_POST['memory'],
        ':gpu' => $_POST['gpu'],
        ':storage' => $_POST['storage'],
        ':employeeId' => $_POST['employee_id'] ?: null
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'All-in-One added successfully',
        'allinone_id' => $db->lastInsertId()
    ]);
}

function updateAllInOne($db) {
    $id = $_POST['allinone_id'] ?? null;
    if (!$id) throw new Exception('All-in-One ID is required');
    
    // Check exists
    $stmt = $db->prepare("SELECT COUNT(*) FROM tbl_allinone WHERE allinoneId = :id");
    $stmt->execute([':id' => $id]);
    if ($stmt->fetchColumn() == 0) throw new Exception('All-in-One not found');
    
    $stmt = $db->prepare("
        UPDATE tbl_allinone SET
            allinoneBrand = :brand,
            specificationProcessor = :processor,
            specificationMemory = :memory,
            specificationGPU = :gpu,
            specificationStorage = :storage,
            employeeId = :employeeId
        WHERE allinoneId = :id
    ");
    
    $stmt->execute([
        ':brand' => $_POST['brand'],
        ':processor' => $_POST['processor'],
        ':memory' => $_POST['memory'],
        ':gpu' => $_POST['gpu'],
        ':storage' => $_POST['storage'],
        ':employeeId' => $_POST['employee_id'] ?: null,
        ':id' => $id
    ]);
    
    echo json_encode(['success' => true, 'message' => 'All-in-One updated successfully']);
}

function deleteAllInOne($db) {
    $id = $_POST['allinone_id'] ?? null;
    if (!$id) throw new Exception('All-in-One ID is required');
    
    $stmt = $db->prepare("DELETE FROM tbl_allinone WHERE allinoneId = :id");
    $stmt->execute([':id' => $id]);
    
    if ($stmt->rowCount() == 0) {
        throw new Exception('All-in-One not found or already deleted');
    }
    
    echo json_encode(['success' => true, 'message' => 'All-in-One deleted successfully']);
}