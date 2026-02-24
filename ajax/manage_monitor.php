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
 * Validate monitor size
 */
function validateMonitorSize($size) {
    $size = sanitizeString($size);
    if (empty($size)) {
        throw new Exception("Monitor size cannot be empty");
    }
    // Accept formats like "24", "24 inch", "24 inches", "24\"", "27.5"
    if (!preg_match('/^\d+(\.\d+)?(\s*(inch|inches|"|\'\'|in))?$/i', $size)) {
        throw new Exception("Invalid monitor size format. Use formats like '24', '24 inch', or '24\"'");
    }
    if (strlen($size) > 50) {
        throw new Exception("Monitor size must not exceed 50 characters");
    }
    return $size;
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

    $sql = "SELECT m.*, CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) as employeeName
        FROM tbl_monitor m
        LEFT JOIN tbl_employee e ON m.employeeId = e.employeeId
        where 1=1";
    
    $params = [];
    if (!empty($search)) {
        $term = "%$search%";
        $sql .= " AND (
            m.monitorBrand LIKE :search1 OR
            m.monitorSerial LIKE :search2 OR
            m.monitorSize LIKE :search3 OR
            CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) LIKE :search4
        )";
        $params[':search1'] = $term;
        $params[':search2'] = $term;
        $params[':search3'] = $term;
        $params[':search4'] = $term;
    }
    
    $sql .= " ORDER BY m.monitorId DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $monitors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $formatted = array_map(function($m) {
        return [
            'monitorId' => $m['monitorId'],
            'monitorBrand' => $m['monitorBrand'],
            'monitorSize' => $m['monitorSize'],
            'monitorSerial' => $m['monitorSerial'],
            'resolution' => '1920x1080 (Full HD)',
            'panelType' => 'IPS',
            'yearAcquired' => $m['yearAcquired'],
            'employeeId' => $m['employeeId'],
            'employeeName' => $m['employeeName'],
            'status' => $m['employeeId'] ? 'Active' : 'Available'
        ];
    }, $monitors);
    
    echo json_encode(['success' => true, 'data' => $formatted]);
}

function getMonitor($db) {
    $id = $_GET['monitorId'] ?? null;
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
    // Validate and sanitize all inputs
    $brand = validateBrand($_POST['brand'] ?? '');
    $size = validateMonitorSize($_POST['size'] ?? '');
    $serial = validateSerial($_POST['monitorSerial'] ?? '');
    $year = validateYear($_POST['year'] ?? '');
    $employeeId = validateEmployeeId($db, $_POST['employee_id'] ?? null);
    
    // Check duplicate serial
    $stmt = $db->prepare("SELECT COUNT(*) FROM tbl_monitor WHERE monitorSerial = :monitorSerial");
    $stmt->execute([':monitorSerial' => $serial]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Serial number already exists');
    }
    
    $stmt = $db->prepare("
        INSERT INTO tbl_monitor (
            monitorBrand, monitorSize, monitorSerial, yearAcquired, employeeId
        ) VALUES (
            :brand, :size, :monitorSerial, :year, :employeeId
        )
    ");
    
    $stmt->execute([
        ':brand' => $brand,
        ':size' => $size,
        ':monitorSerial' => $serial,
        ':year' => $year,
        ':employeeId' => $employeeId
    ]);

    $newId = $db->lastInsertId();

    logActivity(ACTION_CREATE, MODULE_COMPUTERS,
        "Added Monitor — Brand: {$brand}, Serial: {$serial}, Size: {$size}, Year: {$year}."
        . ($employeeId ? " Assigned to employee ID {$employeeId}." : ""));

    try {
        $maint = new MaintenanceHelper($db);
        $maint->initScheduleByType('Monitor', $newId);
    } catch (Exception $e) {
        error_log("Failed to schedule maintenance for Monitor ID $newId: " . $e->getMessage());
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Monitor added successfully',
        'monitorId' => $newId
    ]);
}

function updateMonitor($db) {
    $id = filter_var($_POST['monitorId'] ?? null, FILTER_VALIDATE_INT);
    if (!$id || $id < 1) {
        throw new Exception('Valid monitor ID is required');
    }
    
    // Check exists
    $stmt = $db->prepare("SELECT COUNT(*) FROM tbl_monitor WHERE monitorId = :id");
    $stmt->execute([':id' => $id]);
    if ($stmt->fetchColumn() == 0) {
        throw new Exception('Monitor not found');
    }
    
    // Validate and sanitize all inputs
    $brand = validateBrand($_POST['brand'] ?? '');
    $size = validateMonitorSize($_POST['size'] ?? '');
    $serial = validateSerial($_POST['monitorSerial'] ?? '');
    $year = validateYear($_POST['year'] ?? '');
    $employeeId = validateEmployeeId($db, $_POST['employee_id'] ?? null);
    
    // Check duplicate serial
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM tbl_monitor 
        WHERE monitorSerial = :monitorSerial AND monitorId != :id
    ");
    $stmt->execute([':monitorSerial' => $serial, ':id' => $id]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Serial number already exists');
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
        ':brand' => $brand,
        ':size' => $size,
        ':monitorSerial' => $serial,
        ':year' => $year,
        ':employeeId' => $employeeId,
        ':id' => $id
    ]);
    
    logActivity(ACTION_UPDATE, MODULE_COMPUTERS,
        "Updated Monitor (ID: {$id}) — Brand: {$brand}, Serial: {$serial}."
        . ($employeeId ? " Assigned to employee ID {$employeeId}." : " Unassigned."));

    echo json_encode(['success' => true, 'message' => 'Monitor updated successfully']);
}

function deleteMonitor($db) {
    $id = $_POST['monitorId'] ?? null;
    if (!$id) throw new Exception('Monitor ID is required');

    // Fetch details before deleting
    $row = $db->prepare("SELECT monitorBrand, monitorSerial FROM tbl_monitor WHERE monitorId = :id");
    $row->execute([':id' => $id]);
    $item = $row->fetch();
    
    $stmt = $db->prepare("DELETE FROM tbl_monitor WHERE monitorId = :id");
    $stmt->execute([':id' => $id]);
    
    if ($stmt->rowCount() == 0) {
        throw new Exception('Monitor not found or already deleted');
    }

    logActivity(ACTION_DELETE, MODULE_COMPUTERS,
        "Deleted Monitor (ID: {$id}) — Brand: " . ($item['monitorBrand'] ?? 'Unknown')
        . ", Serial: " . ($item['monitorSerial'] ?? 'Unknown') . ".");
    
    echo json_encode(['success' => true, 'message' => 'Monitor deleted successfully']);
}