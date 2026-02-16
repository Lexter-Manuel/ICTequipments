<?php
require_once '../config/database.php';
require_once '../includes/maintenanceHelper.php';

header('Content-Type: application/json');

$db = getDB();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

/**
 * Sanitize string input
 */
function sanitizeString($input) {
    return trim(htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8'));
}

/**
 * Validate brand name
 */
function validateBrand($brand) {
    $brand = sanitizeString($brand);
    if (empty($brand)) {
        throw new Exception("Brand name cannot be empty");
    }
    if (strlen($brand) < 2 || strlen($brand) > 100) {
        throw new Exception("Brand name must be between 2 and 100 characters");
    }
    return $brand;
}

/**
 * Validate specification fields
 */
function validateSpecification($spec, $fieldName) {
    $spec = sanitizeString($spec);
    if (empty($spec)) {
        throw new Exception("$fieldName cannot be empty");
    }
    if (strlen($spec) > 255) {
        throw new Exception("$fieldName must not exceed 255 characters");
    }
    return $spec;
}

/**
 * Validate employee ID
 */
function validateEmployeeId($db, $employeeId) {
    if (empty($employeeId)) {
        return null;
    }
    
    $employeeId = filter_var($employeeId, FILTER_VALIDATE_INT);
    if ($employeeId === false || $employeeId < 1) {
        throw new Exception("Invalid employee ID");
    }
    
    // Check if employee exists
    $stmt = $db->prepare("SELECT COUNT(*) FROM tbl_employee WHERE employeeId = :id");
    $stmt->execute([':id' => $employeeId]);
    if ($stmt->fetchColumn() == 0) {
        throw new Exception("Employee not found");
    }
    
    return $employeeId;
}

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
        $term = "%$search%";
        $sql .= " AND (
            a.allinoneBrand LIKE :search1 OR
            a.specificationProcessor LIKE :search2 OR
            CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) LIKE :search3
        )";
        $params [':search1'] = $term;
        $params [':search2'] = $term;
        $params [':search3'] = $term;
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
            'serial' => 'AIO-' . str_pad($a['allinoneId'], 6, '0', STR_PAD_LEFT),
            'screenSize' => '24 inches',
            'yearAcquired' => '2024',
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
    // Validate and sanitize all inputs
    $brand = validateBrand($_POST['brand'] ?? '');
    $processor = validateSpecification($_POST['processor'] ?? '', 'Processor');
    $memory = validateSpecification($_POST['memory'] ?? '', 'Memory');
    $gpu = validateSpecification($_POST['gpu'] ?? '', 'GPU');
    $storage = validateSpecification($_POST['storage'] ?? '', 'Storage');
    $employeeId = validateEmployeeId($db, $_POST['employee_id'] ?? null);
    
    $stmt = $db->prepare("
        INSERT INTO tbl_allinone (
            allinoneBrand, specificationProcessor, specificationMemory,
            specificationGPU, specificationStorage, employeeId
        ) VALUES (
            :brand, :processor, :memory, :gpu, :storage, :employeeId
        )
    ");
    
    $stmt->execute([
        ':brand' => $brand,
        ':processor' => $processor,
        ':memory' => $memory,
        ':gpu' => $gpu,
        ':storage' => $storage,
        ':employeeId' => $employeeId
    ]);

    $newId = $db->lastInsertId();

    try {
        $maint = new MaintenanceHelper($db);
        $maint->initScheduleByType('All-in-One PC', $newId);
    } catch (Exception $e) {
        error_log("Failed to schedule maintenance for All-in-One ID $newId: " . $e->getMessage());
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'All-in-One added successfully',
        'allinone_id' => $newId
    ]);
}

function updateAllInOne($db) {
    $id = filter_var($_POST['allinone_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$id || $id < 1) {
        throw new Exception('Valid All-in-One ID is required');
    }
    
    // Check exists
    $stmt = $db->prepare("SELECT COUNT(*) FROM tbl_allinone WHERE allinoneId = :id");
    $stmt->execute([':id' => $id]);
    if ($stmt->fetchColumn() == 0) {
        throw new Exception('All-in-One not found');
    }
    
    // Validate and sanitize all inputs
    $brand = validateBrand($_POST['brand'] ?? '');
    $processor = validateSpecification($_POST['processor'] ?? '', 'Processor');
    $memory = validateSpecification($_POST['memory'] ?? '', 'Memory');
    $gpu = validateSpecification($_POST['gpu'] ?? '', 'GPU');
    $storage = validateSpecification($_POST['storage'] ?? '', 'Storage');
    $employeeId = validateEmployeeId($db, $_POST['employee_id'] ?? null);
    
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
        ':brand' => $brand,
        ':processor' => $processor,
        ':memory' => $memory,
        ':gpu' => $gpu,
        ':storage' => $storage,
        ':employeeId' => $employeeId,
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