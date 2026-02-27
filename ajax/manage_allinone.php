<?php
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../includes/maintenanceHelper.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) { session_start(); }

$db = getDB();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

/**
 * Sanitize string input
 */
function sanitizeString($input) {
    return trim(htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8'));
}

/**
 * Validate serial number format
 */
function validateSerial($serial) {
    $serial = sanitizeString($serial);
    if (empty($serial)) {
        throw new Exception("Serial number cannot be empty");
    }
    if (strlen($serial) < 3 || strlen($serial) > 100) {
        throw new Exception("Serial number must be between 3 and 100 characters");
    }
    if (!preg_match('/^[a-zA-Z0-9\-_]+$/', $serial)) {
        throw new Exception("Serial number can only contain letters, numbers, hyphens, and underscores");
    }
    return $serial;
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
    $status = $_GET['status'] ?? '';

    $sql = "
        SELECT 
            a.*,
            CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) as employeeName
        FROM tbl_allinone a
        LEFT JOIN tbl_employee e ON a.employeeId = e.employeeId
        WHERE 1=1
    ";
    
    $params = [];

    if (!empty($status)) {
        if ($status === 'Active') {
            $sql .= " AND a.employeeId IS NOT NULL";
        } elseif ($status === 'Available') {
            $sql .= " AND a.employeeId IS NULL";
        }
    }

    if (!empty($search)) {
        $term = "%$search%";
        $sql .= " AND (
            a.allinoneBrand LIKE :search1 OR
            a.allinoneSerial LIKE :search2 OR
            a.specificationProcessor LIKE :search3 OR
            CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) LIKE :search4
        )";
        $params [':search1'] = $term;
        $params [':search2'] = $term;
        $params [':search3'] = $term;
        $params [':search4'] = $term;
    }
    
    $sql .= " ORDER BY a.allinoneId DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $units = $stmt->fetchAll();
    
    $formatted = array_map(function($a) {
        return [
            'allinoneId' => $a['allinoneId'],
            'allinoneBrand' => $a['allinoneBrand'],
            'allinoneSerial' => $a['allinoneSerial'] ?? null,
            'specificationProcessor' => $a['specificationProcessor'],
            'specificationMemory' => $a['specificationMemory'],
            'specificationGPU' => $a['specificationGPU'],
            'specificationStorage' => $a['specificationStorage'],
            'yearAcquired' => $a['yearAcquired'] ?? null,
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
        'allinoneSerial' => $unit['allinoneSerial'] ?? null,
        'specificationProcessor' => $unit['specificationProcessor'],
        'specificationMemory' => $unit['specificationMemory'],
        'specificationGPU' => $unit['specificationGPU'],
        'specificationStorage' => $unit['specificationStorage'],
        'yearAcquired' => $unit['yearAcquired'] ?? null,
        'employeeId' => $unit['employeeId'],
        'employeeName' => $unit['employeeName'],
        'status' => $unit['employeeId'] ? 'Active' : 'Available'
    ];
    
    echo json_encode(['success' => true, 'data' => $formatted]);
}

function createAllInOne($db) {
    $brand = validateBrand($_POST['brand'] ?? '');
    $serial = validateSerial($_POST['allinoneSerial'] ?? '');
    $processor = validateSpecification($_POST['processor'] ?? '', 'Processor');
    $memory = validateSpecification($_POST['memory'] ?? '', 'Memory');
    $gpu = validateSpecification($_POST['gpu'] ?? '', 'GPU');
    $storage = validateSpecification($_POST['storage'] ?? '', 'Storage');
    $employeeId = validateEmployeeId($db, $_POST['employee_id'] ?? null);

    $yearAcquired = null;
    if (!empty($_POST['year_acquired'])) {
        $yr = filter_var($_POST['year_acquired'], FILTER_VALIDATE_INT);
        $currentYear = (int)date('Y');
        if ($yr === false || $yr < 1990 || $yr > $currentYear + 1) {
            throw new Exception("Invalid year. Must be between 1990 and " . ($currentYear + 1));
        }
        $yearAcquired = $yr;
    }

    // Check duplicate serial
    $stmt = $db->prepare("SELECT COUNT(*) FROM tbl_allinone WHERE allinoneSerial = :serial");
    $stmt->execute([':serial' => $serial]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Serial number already exists');
    }
    
    $stmt = $db->prepare("
        INSERT INTO tbl_allinone (
            allinoneBrand, allinoneSerial, specificationProcessor, specificationMemory,
            specificationGPU, specificationStorage, yearAcquired, employeeId
        ) VALUES (
            :brand, :serial, :processor, :memory, :gpu, :storage, :yearAcquired, :employeeId
        )
    ");
    
    $stmt->execute([
        ':brand'        => $brand,
        ':serial'       => $serial,
        ':processor'    => $processor,
        ':memory'       => $memory,
        ':gpu'          => $gpu,
        ':storage'      => $storage,
        ':yearAcquired' => $yearAcquired,
        ':employeeId'   => $employeeId
    ]);

    $newId = $db->lastInsertId();

    logActivity(ACTION_CREATE, MODULE_COMPUTERS,
        "Added All-in-One — Brand: {$brand}, Serial: {$serial}, CPU: {$processor}, RAM: {$memory}, Storage: {$storage}."
        . ($employeeId ? " Assigned to employee ID {$employeeId}." : ""));

    try {
        $maint = new MaintenanceHelper($db);
        $maint->initScheduleByType('All-in-One', $newId);
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
    
    $brand = validateBrand($_POST['brand'] ?? '');
    $serial = validateSerial($_POST['allinoneSerial'] ?? '');
    $processor = validateSpecification($_POST['processor'] ?? '', 'Processor');
    $memory = validateSpecification($_POST['memory'] ?? '', 'Memory');
    $gpu = validateSpecification($_POST['gpu'] ?? '', 'GPU');
    $storage = validateSpecification($_POST['storage'] ?? '', 'Storage');
    $employeeId = validateEmployeeId($db, $_POST['employee_id'] ?? null);

    $yearAcquired = null;
    if (!empty($_POST['year_acquired'])) {
        $yr = filter_var($_POST['year_acquired'], FILTER_VALIDATE_INT);
        $currentYear = (int)date('Y');
        if ($yr === false || $yr < 1990 || $yr > $currentYear + 1) {
            throw new Exception("Invalid year. Must be between 1990 and " . ($currentYear + 1));
        }
        $yearAcquired = $yr;
    }

    // Check duplicate serial (exclude self)
    $stmt = $db->prepare("SELECT COUNT(*) FROM tbl_allinone WHERE allinoneSerial = :serial AND allinoneId != :id");
    $stmt->execute([':serial' => $serial, ':id' => $id]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Serial number already exists');
    }
    
    $stmt = $db->prepare("
        UPDATE tbl_allinone SET
            allinoneBrand = :brand,
            allinoneSerial = :serial,
            specificationProcessor = :processor,
            specificationMemory = :memory,
            specificationGPU = :gpu,
            specificationStorage = :storage,
            yearAcquired = :yearAcquired,
            employeeId = :employeeId
        WHERE allinoneId = :id
    ");
    
    $stmt->execute([
        ':brand'        => $brand,
        ':serial'       => $serial,
        ':processor'    => $processor,
        ':memory'       => $memory,
        ':gpu'          => $gpu,
        ':storage'      => $storage,
        ':yearAcquired' => $yearAcquired,
        ':employeeId'   => $employeeId,
        ':id'           => $id
    ]);
    
    logActivity(ACTION_UPDATE, MODULE_COMPUTERS,
        "Updated All-in-One (ID: {$id}) — Brand: {$brand}, Serial: {$serial}."
        . ($employeeId ? " Assigned to employee ID {$employeeId}." : " Unassigned."));

    echo json_encode(['success' => true, 'message' => 'All-in-One updated successfully']);
}

function deleteAllInOne($db) {
    $id = $_POST['allinone_id'] ?? null;
    if (!$id) throw new Exception('All-in-One ID is required');
    
    // Fetch details before deleting so we can log them
    $row = $db->prepare("SELECT allinoneBrand, allinoneSerial FROM tbl_allinone WHERE allinoneId = :id");
    $row->execute([':id' => $id]);
    $item = $row->fetch();

    $stmt = $db->prepare("DELETE FROM tbl_allinone WHERE allinoneId = :id");
    $stmt->execute([':id' => $id]);
    
    if ($stmt->rowCount() == 0) {
        throw new Exception('All-in-One not found or already deleted');
    }

    logActivity(ACTION_DELETE, MODULE_COMPUTERS,
        "Deleted All-in-One (ID: {$id}) — Brand: " . ($item['allinoneBrand'] ?? 'Unknown')
        . ", Serial: " . ($item['allinoneSerial'] ?? 'Unknown') . ".");
    
    echo json_encode(['success' => true, 'message' => 'All-in-One deleted successfully']);
}