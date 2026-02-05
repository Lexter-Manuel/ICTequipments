<?php
/**
 * Monitor Management AJAX Handler
 * Handles all CRUD operations for monitors
 * Works with existing tbl_monitor schema
 */

require_once '../config/database.php';

header('Content-Type: application/json');

$db = getDB();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            listMonitors($db);
            break;
        case 'get':
            getMonitor($db);
            break;
        case 'create':
            createMonitor($db);
            break;
        case 'update':
            updateMonitor($db);
            break;
        case 'delete':
            deleteMonitor($db);
            break;
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function listMonitors($db) {
    $search = $_GET['search'] ?? '';
    $sql = "
        SELECT 
            m.*,
            CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) as employeeName
        FROM tbl_monitor m
        LEFT JOIN tbl_employee e ON m.employeeId = e.employeeId
        WHERE 1=1
    ";
    
    $params = [];
    if (!empty($search)) {
        $sql .= " AND (
            m.monitorBrand LIKE :search OR
            m.monitorSerial LIKE :search OR
            m.monitorSize LIKE :search
        )";
        $params[':search'] = "%$search%";
    }
    
    $sql .= " ORDER BY m.monitorId DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $monitors = $stmt->fetchAll();
    
    $formatted = array_map(function($m) {
        return [
            'monitorId' => $m['monitorId'],
            'monitorBrand' => $m['monitorBrand'],
            'monitorSize' => $m['monitorSize'],
            'monitorSerial' => $m['monitorSerial'],
            'resolution' => '1920x1080 (Full HD)', // Default since not in schema
            'panelType' => 'IPS', // Default since not in schema
            'yearAcquired' => $m['yearAcquired'],
            'employeeId' => $m['employeeId'],
            'employeeName' => $m['employeeName'],
            'status' => $m['employeeId'] ? 'Active' : 'Available'
        ];
    }, $monitors);
    
    echo json_encode(['success' => true, 'data' => $formatted]);
}

function getMonitor($db) {
    $id = $_GET['monitor_id'] ?? null;
    if (!$id) throw new Exception('Monitor ID is required');
    
    $stmt = $db->prepare("
        SELECT m.*, CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) as employeeName
        FROM tbl_monitor m
        LEFT JOIN tbl_employee e ON m.employeeId = e.employeeId
        WHERE m.monitorId = :id
    ");
    $stmt->execute([':id' => $id]);
    $monitor = $stmt->fetch();
    
    if (!$monitor) throw new Exception('Monitor not found');
    
    $formatted = [
        'monitorId' => $monitor['monitorId'],
        'monitorBrand' => $monitor['monitorBrand'],
        'monitorSize' => $monitor['monitorSize'],
        'monitorSerial' => $monitor['monitorSerial'],
        'resolution' => '1920x1080 (Full HD)',
        'panelType' => 'IPS',
        'yearAcquired' => $monitor['yearAcquired'],
        'employeeId' => $monitor['employeeId'],
        'employeeName' => $monitor['employeeName'],
        'status' => $monitor['employeeId'] ? 'Active' : 'Available'
    ];
    
    echo json_encode(['success' => true, 'data' => $formatted]);
}

function createMonitor($db) {
    $required = ['brand', 'size', 'monitorSerial', 'year'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Field '$field' is required");
        }
    }
    
    // Check duplicate monitorSerial
    $stmt = $db->prepare("SELECT COUNT(*) FROM tbl_monitor WHERE monitorSerial = :monitorSerial");
    $stmt->execute([':monitorSerial' => $_POST['monitorSerial']]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('monitorSerial number already exists');
    }
    
    $stmt = $db->prepare("
        INSERT INTO tbl_monitor (
            monitorBrand, monitorSize, monitorSerial, yearAcquired, employeeId
        ) VALUES (
            :brand, :size, :monitorSerial, :year, :employeeId
        )
    ");
    
    $stmt->execute([
        ':brand' => $_POST['brand'],
        ':size' => $_POST['size'],
        ':monitorSerial' => $_POST['monitorSerial'],
        ':year' => $_POST['year'],
        ':employeeId' => $_POST['employee_id'] ?: null
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Monitor added successfully',
        'monitor_id' => $db->lastInsertId()
    ]);
}

function updateMonitor($db) {
    $id = $_POST['monitor_id'] ?? null;
    if (!$id) throw new Exception('Monitor ID is required');
    
    // Check exists
    $stmt = $db->prepare("SELECT COUNT(*) FROM tbl_monitor WHERE monitorId = :id");
    $stmt->execute([':id' => $id]);
    if ($stmt->fetchColumn() == 0) throw new Exception('Monitor not found');
    
    // Check duplicate monitorSerial
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM tbl_monitor 
        WHERE monitorSerial = :monitorSerial AND monitorId != :id
    ");
    $stmt->execute([':monitorSerial' => $_POST['monitorSerial'], ':id' => $id]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('monitorSerial number already exists');
    }
    
    $stmt = $db->prepare("
        UPDATE tbl_monitor SET
            monitorBrand = :brand,
            monitorSize = :size,
            monitorSerial = :monitorSerial,
            yearAcquired = :year,
            employeeId = :employeeId
        WHERE monitorId = :id
    ");
    
    $stmt->execute([
        ':brand' => $_POST['brand'],
        ':size' => $_POST['size'],
        ':monitorSerial' => $_POST['monitorSerial'],
        ':year' => $_POST['year'],
        ':employeeId' => $_POST['employee_id'] ?: null,
        ':id' => $id
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Monitor updated successfully']);
}

function deleteMonitor($db) {
    $id = $_POST['monitor_id'] ?? null;
    if (!$id) throw new Exception('Monitor ID is required');
    
    $stmt = $db->prepare("DELETE FROM tbl_monitor WHERE monitorId = :id");
    $stmt->execute([':id' => $id]);
    
    if ($stmt->rowCount() == 0) {
        throw new Exception('Monitor not found or already deleted');
    }
    
    echo json_encode(['success' => true, 'message' => 'Monitor deleted successfully']);
}