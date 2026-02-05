<?php
/**
 * System Unit Management AJAX Handler
 * Handles all CRUD operations for system units (desktop computers)
 * Works with existing tbl_systemunit schema
 */

require_once '../config/database.php';

header('Content-Type: application/json');

$db = getDB();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            listSystemUnits($db);
            break;
        case 'get':
            getSystemUnit($db);
            break;
        case 'create':
            createSystemUnit($db);
            break;
        case 'update':
            updateSystemUnit($db);
            break;
        case 'delete':
            deleteSystemUnit($db);
            break;
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function listSystemUnits($db) {
    $search = $_GET['search'] ?? '';
    $sql = "
        SELECT 
            s.*,
            CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) as employeeName
        FROM tbl_systemunit s
        LEFT JOIN tbl_employee e ON s.employeeId = e.employeeId
        WHERE 1=1
    ";
    
    $params = [];
    if (!empty($search)) {
        $sql .= " AND (
            s.systemUnitBrand LIKE :search OR
            s.systemUnitSerial LIKE :search OR
            s.specificationProcessor LIKE :search
        )";
        $params[':search'] = "%$search%";
    }
    
    $sql .= " ORDER BY s.systemunitId DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $units = $stmt->fetchAll();
    
    $formatted = array_map(function($s) {
        return [
            'systemunitId' => $s['systemunitId'],
            'systemUnitCategory' => $s['systemUnitCategory'] ?? 'Pre-Built',
            'systemUnitBrand' => $s['systemUnitBrand'],
            'specificationProcessor' => $s['specificationProcessor'],
            'specificationMemory' => $s['specificationMemory'],
            'specificationGPU' => $s['specificationGPU'],
            'specificationStorage' => $s['specificationStorage'],
            'systemUnitSerial' => $s['systemUnitSerial'],
            'yearAcquired' => $s['yearAcquired'],
            'employeeId' => $s['employeeId'],
            'employeeName' => $s['employeeName'],
            'status' => $s['employeeId'] ? 'Active' : 'Available'
        ];
    }, $units);
    
    echo json_encode(['success' => true, 'data' => $formatted]);
}

function getSystemUnit($db) {
    $id = $_GET['systemunit_id'] ?? null;
    if (!$id) throw new Exception('System unit ID is required');
    
    $stmt = $db->prepare("
        SELECT s.*, CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) as employeeName
        FROM tbl_systemunit s
        LEFT JOIN tbl_employee e ON s.employeeId = e.employeeId
        WHERE s.systemunitId = :id
    ");
    $stmt->execute([':id' => $id]);
    $unit = $stmt->fetch();
    
    if (!$unit) throw new Exception('System unit not found');
    
    $formatted = [
        'systemunitId' => $unit['systemunitId'],
        'systemUnitCategory' => $unit['systemUnitCategory'] ?? 'Pre-Built',
        'systemUnitBrand' => $unit['systemUnitBrand'],
        'specificationProcessor' => $unit['specificationProcessor'],
        'specificationMemory' => $unit['specificationMemory'],
        'specificationGPU' => $unit['specificationGPU'],
        'specificationStorage' => $unit['specificationStorage'],
        'systemUnitSerial' => $unit['systemUnitSerial'],
        'yearAcquired' => $unit['yearAcquired'],
        'employeeId' => $unit['employeeId'],
        'employeeName' => $unit['employeeName'],
        'status' => $unit['employeeId'] ? 'Active' : 'Available'
    ];
    
    echo json_encode(['success' => true, 'data' => $formatted]);
}

function createSystemUnit($db) {
    $required = ['brand', 'processor', 'memory', 'gpu', 'storage', 'serial', 'year'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Field '$field' is required");
        }
    }
    
    // Check duplicate serial
    $stmt = $db->prepare("SELECT COUNT(*) FROM tbl_systemunit WHERE systemUnitSerial = :serial");
    $stmt->execute([':serial' => $_POST['serial']]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Serial number already exists');
    }
    
    $stmt = $db->prepare("
        INSERT INTO tbl_systemunit (
            systemUnitCategory, systemUnitBrand, specificationProcessor,
            specificationMemory, specificationGPU, specificationStorage,
            systemUnitSerial, yearAcquired, employeeId
        ) VALUES (
            :category, :brand, :processor, :memory, :gpu, :storage, :serial, :year, :employeeId
        )
    ");
    
    $stmt->execute([
        ':category' => $_POST['category'] ?? 'Pre-Built',
        ':brand' => $_POST['brand'],
        ':processor' => $_POST['processor'],
        ':memory' => $_POST['memory'],
        ':gpu' => $_POST['gpu'],
        ':storage' => $_POST['storage'],
        ':serial' => $_POST['serial'],
        ':year' => $_POST['year'],
        ':employeeId' => $_POST['employee_id'] ?: null
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'System unit added successfully',
        'systemunit_id' => $db->lastInsertId()
    ]);
}

function updateSystemUnit($db) {
    $id = $_POST['systemunit_id'] ?? null;
    if (!$id) throw new Exception('System unit ID is required');
    
    // Check exists
    $stmt = $db->prepare("SELECT COUNT(*) FROM tbl_systemunit WHERE systemunitId = :id");
    $stmt->execute([':id' => $id]);
    if ($stmt->fetchColumn() == 0) throw new Exception('System unit not found');
    
    // Check duplicate serial
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM tbl_systemunit 
        WHERE systemUnitSerial = :serial AND systemunitId != :id
    ");
    $stmt->execute([':serial' => $_POST['serial'], ':id' => $id]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Serial number already exists');
    }
    
    $stmt = $db->prepare("
        UPDATE tbl_systemunit SET
            systemUnitCategory = :category,
            systemUnitBrand = :brand,
            specificationProcessor = :processor,
            specificationMemory = :memory,
            specificationGPU = :gpu,
            specificationStorage = :storage,
            systemUnitSerial = :serial,
            yearAcquired = :year,
            employeeId = :employeeId
        WHERE systemunitId = :id
    ");
    
    $stmt->execute([
        ':category' => $_POST['category'] ?? 'Pre-Built',
        ':brand' => $_POST['brand'],
        ':processor' => $_POST['processor'],
        ':memory' => $_POST['memory'],
        ':gpu' => $_POST['gpu'],
        ':storage' => $_POST['storage'],
        ':serial' => $_POST['serial'],
        ':year' => $_POST['year'],
        ':employeeId' => $_POST['employee_id'] ?: null,
        ':id' => $id
    ]);
    
    echo json_encode(['success' => true, 'message' => 'System unit updated successfully']);
}

function deleteSystemUnit($db) {
    $id = $_POST['systemunit_id'] ?? null;
    if (!$id) throw new Exception('System unit ID is required');
    
    $stmt = $db->prepare("DELETE FROM tbl_systemunit WHERE systemunitId = :id");
    $stmt->execute([':id' => $id]);
    
    if ($stmt->rowCount() == 0) {
        throw new Exception('System unit not found or already deleted');
    }
    
    echo json_encode(['success' => true, 'message' => 'System unit deleted successfully']);
}