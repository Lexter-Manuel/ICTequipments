<?php
/**
 * Printer Management AJAX Handler
 * Handles all CRUD operations for printers
 * Works with existing tbl_printer schema
 * 
 * Actions:
 * - list: Get all printers with optional filtering
 * - get: Get single printer details
 * - create: Add new printer
 * - update: Update existing printer
 * - delete: Delete printer
 */

require_once '../config/database.php';

header('Content-Type: application/json');

// Get database connection
$db = getDB();

// Get action from request
$action = $_GET['action'] ?? $_POST['action'] ?? '';

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
    $printerId = $_GET['printer_id'] ?? null;
    
    if (!$printerId) {
        throw new Exception('Printer ID is required');
    }
    
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
    // Validate required fields
    $required = ['brand', 'model', 'serial_number', 'year_acquired'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Field '$field' is required");
        }
    }
    
    // Check for duplicate serial number
    $stmt = $db->prepare("SELECT COUNT(*) FROM tbl_printer WHERE printerSerial = :serial");
    $stmt->execute([':serial' => $_POST['serial_number']]);
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
    
    $employeeId = !empty($_POST['employee_id']) ? $_POST['employee_id'] : null;
    
    $stmt->execute([
        ':brand' => $_POST['brand'],
        ':model' => $_POST['model'],
        ':serial' => $_POST['serial_number'],
        ':year' => $_POST['year_acquired'],
        ':employeeId' => $employeeId
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Printer added successfully',
        'printer_id' => $db->lastInsertId()
    ]);
}

/**
 * Update existing printer
 */
function updatePrinter($db) {
    $printerId = $_POST['printer_id'] ?? null;
    
    if (!$printerId) {
        throw new Exception('Printer ID is required');
    }
    
    // Check if printer exists
    $stmt = $db->prepare("SELECT COUNT(*) FROM tbl_printer WHERE printerId = :id");
    $stmt->execute([':id' => $printerId]);
    if ($stmt->fetchColumn() == 0) {
        throw new Exception('Printer not found');
    }
    
    // Check for duplicate serial number (excluding current printer)
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM tbl_printer 
        WHERE printerSerial = :serial AND printerId != :id
    ");
    $stmt->execute([
        ':serial' => $_POST['serial_number'],
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
    
    $employeeId = !empty($_POST['employee_id']) ? $_POST['employee_id'] : null;
    
    $stmt->execute([
        ':brand' => $_POST['brand'],
        ':model' => $_POST['model'],
        ':serial' => $_POST['serial_number'],
        ':year' => $_POST['year_acquired'],
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
    $printerId = $_POST['printer_id'] ?? null;
    
    if (!$printerId) {
        throw new Exception('Printer ID is required');
    }
    
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