<?php
require_once '../config/database.php';
require_once '../includes/maintenanceHelper.php';

header('Content-Type: application/json');

// Get database connection
$db = getDB();

// Get action from request
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
 * Validate model name
 */
function validateModel($model) {
    $model = sanitizeString($model);
    if (empty($model)) {
        throw new Exception("Model name cannot be empty");
    }
    if (strlen($model) < 1 || strlen($model) > 150) {
        throw new Exception("Model name must be between 1 and 150 characters");
    }
    return $model;
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

/**
 * Validate printer ID
 */
function validatePrinterId($printerId) {
    $printerId = filter_var($printerId, FILTER_VALIDATE_INT);
    if ($printerId === false || $printerId < 1) {
        throw new Exception("Invalid printer ID");
    }
    return $printerId;
}

try {
    switch ($action) {
        case 'list':
            listPrinters($db);
            break;
            
        case 'get':
            getPrinter($db);
            break;
            
        case 'create':
            createPrinter($db);
            break;
            
        case 'update':
            updatePrinter($db);
            break;
            
        case 'delete':
            deletePrinter($db);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * List all printers with optional filtering
 */
function listPrinters($db) {
    $search = $_GET['search'] ?? '';
    
    $sql = "
        SELECT 
            p.*,
            CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) as employeeName
        FROM tbl_printer p
        LEFT JOIN tbl_employee e ON p.employeeId = e.employeeId
        WHERE 1=1
    ";
    
    $params = [];
    
    // Add search filter
    if (!empty($search)) {
        $sql .= " AND (
            p.printerBrand LIKE :search OR
            p.printerModel LIKE :search OR
            p.printerSerial LIKE :search
        )";
        $params[':search'] = "%$search%";
    }
    
    $sql .= " ORDER BY p.printerId DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $printers = $stmt->fetchAll();
    
    // Format data for frontend
    $formattedPrinters = array_map(function($p) {
        // Determine status based on employee assignment
        $status = $p['employeeId'] ? 'Working' : 'Available';
        
        return [
            'printer_id' => $p['printerId'],
            'name' => $p['printerBrand'] . ' ' . $p['printerModel'],
            'brand' => $p['printerBrand'],
            'model' => $p['printerModel'],
            'serial_number' => $p['printerSerial'],
            'location' => 'Office', // Default since not in schema
            'year_acquired' => $p['yearAcquired'],
            'employee_id' => $p['employeeId'],
            'employee_name' => $p['employeeName'],
            'status' => $status
        ];
    }, $printers);
    
    echo json_encode([
        'success' => true,
        'data' => $formattedPrinters
    ]);
}

/**
 * Get single printer details
 */
function getPrinter($db) {
    $printerId = validatePrinterId($_GET['printer_id'] ?? null);
    
    $stmt = $db->prepare("
        SELECT 
            p.*,
            CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) as employeeName
        FROM tbl_printer p
        LEFT JOIN tbl_employee e ON p.employeeId = e.employeeId
        WHERE p.printerId = :printerId
    ");
    
    $stmt->execute([':printerId' => $printerId]);
    $printer = $stmt->fetch();
    
    if (!$printer) {
        throw new Exception('Printer not found');
    }
    
    // Determine status
    $status = $printer['employeeId'] ? 'Working' : 'Available';
    
    // Format data for frontend
    $formattedPrinter = [
        'printer_id' => $printer['printerId'],
        'name' => $printer['printerBrand'] . ' ' . $printer['printerModel'],
        'brand' => $printer['printerBrand'],
        'model' => $printer['printerModel'],
        'serial_number' => $printer['printerSerial'],
        'location' => 'Office',
        'year_acquired' => $printer['yearAcquired'],
        'employee_id' => $printer['employeeId'],
        'employee_name' => $printer['employeeName'],
        'status' => $status
    ];
    
    echo json_encode([
        'success' => true,
        'data' => $formattedPrinter
    ]);
}

/**
 * Create new printer
 */
function createPrinter($db) {
    // Validate and sanitize all inputs
    $brand = validateBrand($_POST['brand'] ?? '');
    $model = validateModel($_POST['model'] ?? '');
    $serial = validateSerial($_POST['serial_number'] ?? '');
    $year = validateYear($_POST['year_acquired'] ?? '');
    $employeeId = validateEmployeeId($db, $_POST['employee_id'] ?? null);
    
    // Check for duplicate serial number
    $stmt = $db->prepare("SELECT COUNT(*) FROM tbl_printer WHERE printerSerial = :serial");
    $stmt->execute([':serial' => $serial]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Serial number already exists');
    }
    
    $stmt = $db->prepare("
        INSERT INTO tbl_printer (
            printerBrand,
            printerModel,
            printerSerial,
            yearAcquired,
            employeeId
        ) VALUES (
            :brand,
            :model,
            :serial,
            :year,
            :employeeId
        )
    ");
    
    $stmt->execute([
        ':brand' => $brand,
        ':model' => $model,
        ':serial' => $serial,
        ':year' => $year,
        ':employeeId' => $employeeId
    ]);

    $newId = $db->lastInsertId();

    try {
        $maint = new MaintenanceHelper($db);
        $maint->initScheduleByType('Printer', $newId);
    } catch (Exception $e) {
        error_log("Failed to schedule maintenance for Printer ID $newId: " . $e->getMessage());
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Printer added successfully',
        'printer_id' => $newId
    ]);
}

/**
 * Update existing printer
 */
function updatePrinter($db) {
    $printerId = validatePrinterId($_POST['printer_id'] ?? null);
    
    // Check if printer exists
    $stmt = $db->prepare("SELECT COUNT(*) FROM tbl_printer WHERE printerId = :id");
    $stmt->execute([':id' => $printerId]);
    if ($stmt->fetchColumn() == 0) {
        throw new Exception('Printer not found');
    }
    
    // Validate and sanitize all inputs
    $brand = validateBrand($_POST['brand'] ?? '');
    $model = validateModel($_POST['model'] ?? '');
    $serial = validateSerial($_POST['serial_number'] ?? '');
    $year = validateYear($_POST['year_acquired'] ?? '');
    $employeeId = validateEmployeeId($db, $_POST['employee_id'] ?? null);
    
    // Check for duplicate serial number (excluding current printer)
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM tbl_printer 
        WHERE printerSerial = :serial AND printerId != :id
    ");
    $stmt->execute([
        ':serial' => $serial,
        ':id' => $printerId
    ]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Serial number already exists');
    }
    
    $stmt = $db->prepare("
        UPDATE tbl_printer SET
            printerBrand = :brand,
            printerModel = :model,
            printerSerial = :serial,
            yearAcquired = :year,
            employeeId = :employeeId
        WHERE printerId = :printerId
    ");
    
    $stmt->execute([
        ':brand' => $brand,
        ':model' => $model,
        ':serial' => $serial,
        ':year' => $year,
        ':employeeId' => $employeeId,
        ':printerId' => $printerId
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Printer updated successfully'
    ]);
}

/**
 * Delete printer
 */
function deletePrinter($db) {
    $printerId = validatePrinterId($_POST['printer_id'] ?? null);
    
    $stmt = $db->prepare("DELETE FROM tbl_printer WHERE printerId = :id");
    $stmt->execute([':id' => $printerId]);
    
    if ($stmt->rowCount() == 0) {
        throw new Exception('Printer not found or already deleted');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Printer deleted successfully'
    ]);
}