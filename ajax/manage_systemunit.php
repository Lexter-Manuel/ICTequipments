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
 * Validate and sanitize year
 */
function validateYear($year) {
    $year = filter_var($year, FILTER_VALIDATE_INT);
    $currentYear = date('Y');
    if ($year === false || $year < 1990 || $year > $currentYear + 1) {
        throw new Exception("Invalid year. Must be between 1990 and " . ($currentYear + 1));
    }
    return $year;
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
    // Allow alphanumeric, hyphens, and underscores
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
 * Validate category
 */
function validateCategory($category) {
    $validCategories = ['Pre-Built', 'Custom Built'];
    $category = sanitizeString($category);
    if (empty($category)) {
        return 'Pre-Built'; // Default
    }
    if (!in_array($category, $validCategories)) {
        throw new Exception("Invalid category. Must be one of: " . implode(', ', $validCategories));
    }
    return $category;
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

    $sql = "SELECT s.*, CONCAT_WS(' ', e.firstName, e.lastName) as employeeName 
                FROM tbl_systemunit s 
                LEFT JOIN tbl_employee e ON s.employeeId = e.employeeId
                where 1=1";
    
    $params = [];

    if (!empty($search)) {
        $term = "%$search%";
        $sql .= " AND (
            s.systemUnitCategory LIKE :search1 OR
            s.systemUnitBrand LIKE :search2 OR
            s.specificationProcessor LIKE :search3 OR
            s.specificationMemory LIKE :search4 OR
            s.specificationGPU LIKE :search5 OR
            s.specificationStorage LIKE :search6 OR
            s.systemUnitSerial LIKE :search7 OR
            e.firstName LIKE :search8 OR
            e.lastName LIKE :search9
        )";
        $params[':search1'] = $term;
        $params[':search2'] = $term;
        $params[':search3'] = $term;
        $params[':search4'] = $term;
        $params[':search5'] = $term;
        $params[':search6'] = $term;
        $params[':search7'] = $term;
        $params[':search8'] = $term;
        $params[':search9'] = $term;
    }
    
    $sql .= " ORDER BY s.systemunitId DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $units = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    $formattedSystemUnit = array_map(function($s) {
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
    
    echo json_encode(['success' => true, 'data' => $formattedSystemUnit]);
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
    // Validate and sanitize all inputs
    $category = validateCategory($_POST['category'] ?? 'Pre-Built');
    $brand = validateBrand($_POST['brand'] ?? '');
    $processor = validateSpecification($_POST['processor'] ?? '', 'Processor');
    $memory = validateSpecification($_POST['memory'] ?? '', 'Memory');
    $gpu = validateSpecification($_POST['gpu'] ?? '', 'GPU');
    $storage = validateSpecification($_POST['storage'] ?? '', 'Storage');
    $serial = validateSerial($_POST['serial'] ?? '');
    $year = validateYear($_POST['year'] ?? '');
    $employeeId = validateEmployeeId($db, $_POST['employee_id'] ?? null);
    
    // Check duplicate serial
    $stmt = $db->prepare("SELECT COUNT(*) FROM tbl_systemunit WHERE systemUnitSerial = :serial");
    $stmt->execute([':serial' => $serial]);
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
        ':category' => $category,
        ':brand' => $brand,
        ':processor' => $processor,
        ':memory' => $memory,
        ':gpu' => $gpu,
        ':storage' => $storage,
        ':serial' => $serial,
        ':year' => $year,
        ':employeeId' => $employeeId
    ]);
    $newId = $db->lastInsertId();

    try {
        $maint = new MaintenanceHelper($db);
        $maint->initScheduleByType('System Unit', $newId);
    } catch (Exception $e) {
         error_log("Failed to schedule maintenance for System Unit ID $newId: " . $e->getMessage());

    }
    echo json_encode([
        'success' => true,
        'message' => 'System unit added successfully',
        'systemunit_id' => $newId
    ]);
}

function updateSystemUnit($db) {
    $id = filter_var($_POST['systemunit_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$id || $id < 1) {
        throw new Exception('Valid system unit ID is required');
    }
    
    // Check exists
    $stmt = $db->prepare("SELECT COUNT(*) FROM tbl_systemunit WHERE systemunitId = :id");
    $stmt->execute([':id' => $id]);
    if ($stmt->fetchColumn() == 0) {
        throw new Exception('System unit not found');
    }
    
    // Validate and sanitize all inputs
    $category = validateCategory($_POST['category'] ?? 'Pre-Built');
    $brand = validateBrand($_POST['brand'] ?? '');
    $processor = validateSpecification($_POST['processor'] ?? '', 'Processor');
    $memory = validateSpecification($_POST['memory'] ?? '', 'Memory');
    $gpu = validateSpecification($_POST['gpu'] ?? '', 'GPU');
    $storage = validateSpecification($_POST['storage'] ?? '', 'Storage');
    $serial = validateSerial($_POST['serial'] ?? '');
    $year = validateYear($_POST['year'] ?? '');
    $employeeId = validateEmployeeId($db, $_POST['employee_id'] ?? null);
    
    // Check duplicate serial
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM tbl_systemunit 
        WHERE systemUnitSerial = :serial AND systemunitId != :id
    ");
    $stmt->execute([':serial' => $serial, ':id' => $id]);
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
        ':category' => $category,
        ':brand' => $brand,
        ':processor' => $processor,
        ':memory' => $memory,
        ':gpu' => $gpu,
        ':storage' => $storage,
        ':serial' => $serial,
        ':year' => $year,
        ':employeeId' => $employeeId,
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