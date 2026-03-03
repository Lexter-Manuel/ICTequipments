<?php
/**
 * Unified Equipment AJAX Handler
 * Replaces manage_systemunit.php, manage_monitor.php, manage_printer.php,
 * manage_allinone.php, and manage_otherequipment.php
 * 
 * Works with: tbl_equipment + tbl_equipment_specs (EAV)
 */
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../includes/maintenanceHelper.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) { session_start(); }

$db = getDB();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// ── Validation helpers ──────────────────────────────────────────────────────

function sanitizeString($input) {
    return trim(htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8'));
}

function validateYear($year) {
    $year = filter_var($year, FILTER_VALIDATE_INT);
    $currentYear = date('Y');
    if ($year === false || $year < 1990 || $year > $currentYear + 1) {
        throw new Exception("Invalid year. Must be between 1990 and " . ($currentYear + 1));
    }
    return $year;
}

function validateSerial($serial) {
    $serial = sanitizeString($serial);
    if (empty($serial)) return null; // serials are optional now
    if (strlen($serial) > 255) {
        throw new Exception("Serial number must not exceed 255 characters");
    }
    return $serial;
}

function validateBrand($brand) {
    $brand = sanitizeString($brand);
    if (empty($brand)) {
        throw new Exception("Brand name cannot be empty");
    }
    if (strlen($brand) > 100) {
        throw new Exception("Brand name must not exceed 100 characters");
    }
    return $brand;
}

function validateTypeId($db, $typeId) {
    $typeId = filter_var($typeId, FILTER_VALIDATE_INT);
    if (!$typeId) throw new Exception("Equipment type is required");
    $stmt = $db->prepare("SELECT COUNT(*) FROM tbl_equipment_type_registry WHERE typeId = ?");
    $stmt->execute([$typeId]);
    if ($stmt->fetchColumn() == 0) throw new Exception("Invalid equipment type");
    return $typeId;
}

function validateEmployeeId($db, $employeeId) {
    if (empty($employeeId)) return null;
    $employeeId = filter_var($employeeId, FILTER_VALIDATE_INT);
    if ($employeeId === false || $employeeId < 1) throw new Exception("Invalid employee ID");
    $stmt = $db->prepare("SELECT COUNT(*) FROM tbl_employee WHERE employeeId = ?");
    $stmt->execute([$employeeId]);
    if ($stmt->fetchColumn() == 0) throw new Exception("Employee not found");
    return $employeeId;
}

function validateLocationId($db, $locationId) {
    if (empty($locationId)) return null;
    $locationId = filter_var($locationId, FILTER_VALIDATE_INT);
    if ($locationId === false || $locationId < 1) throw new Exception("Invalid location ID");
    $stmt = $db->prepare("SELECT COUNT(*) FROM location WHERE location_id = ?");
    $stmt->execute([$locationId]);
    if ($stmt->fetchColumn() == 0) throw new Exception("Location not found");
    return $locationId;
}

// ── Spec helpers ────────────────────────────────────────────────────────────

/**
 * Save specs for an equipment item (insert or replace)
 */
function saveSpecs($db, $equipmentId, array $specs) {
    // Delete existing specs for this equipment
    $db->prepare("DELETE FROM tbl_equipment_specs WHERE equipment_id = ?")->execute([$equipmentId]);
    
    if (empty($specs)) return;
    
    $stmt = $db->prepare("INSERT INTO tbl_equipment_specs (equipment_id, spec_key, spec_value) VALUES (?, ?, ?)");
    foreach ($specs as $key => $value) {
        $value = trim($value);
        if ($value !== '') {
            $stmt->execute([$equipmentId, sanitizeString($key), sanitizeString($value)]);
        }
    }
}

/**
 * Get specs for an equipment item as key=>value
 */
function getSpecs($db, $equipmentId) {
    $stmt = $db->prepare("SELECT spec_key, spec_value FROM tbl_equipment_specs WHERE equipment_id = ?");
    $stmt->execute([$equipmentId]);
    $specs = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $specs[$row['spec_key']] = $row['spec_value'];
    }
    return $specs;
}

/**
 * Get specs for multiple equipment items
 */
function getBulkSpecs($db, array $equipmentIds) {
    if (empty($equipmentIds)) return [];
    $placeholders = implode(',', array_fill(0, count($equipmentIds), '?'));
    $stmt = $db->prepare("SELECT equipment_id, spec_key, spec_value FROM tbl_equipment_specs WHERE equipment_id IN ($placeholders)");
    $stmt->execute($equipmentIds);
    $specsMap = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $specsMap[$row['equipment_id']][$row['spec_key']] = $row['spec_value'];
    }
    return $specsMap;
}

// ── Parse specs from POST data ──────────────────────────────────────────────

function parseSpecsFromPost() {
    $specs = [];
    
    // Standard spec fields that may be sent
    $knownKeys = [
        'processor' => 'Processor',
        'memory'    => 'Memory',
        'gpu'       => 'GPU',
        'storage'   => 'Storage',
        'category'  => 'Category',
        'monitor_size' => 'Monitor Size',
        'details'   => 'Details',
        'ip_address' => 'IP Address',
        'resolution' => 'Resolution',
        'printer_type' => 'Printer Type',
        'connectivity' => 'Connectivity',
    ];
    
    foreach ($knownKeys as $postKey => $specKey) {
        if (isset($_POST[$postKey]) && trim($_POST[$postKey]) !== '') {
            $specs[$specKey] = $_POST[$postKey];
        }
    }
    
    // Also accept dynamic specs[] array
    if (isset($_POST['specs']) && is_array($_POST['specs'])) {
        foreach ($_POST['specs'] as $specKey => $specValue) {
            if (trim($specValue) !== '') {
                $specs[sanitizeString($specKey)] = $specValue;
            }
        }
    }
    
    return $specs;
}

// ── Route ───────────────────────────────────────────────────────────────────

try {
    switch ($action) {
        case 'list':       listEquipment($db);   break;
        case 'get':        getEquipment($db);     break;
        case 'create':     createEquipment($db);  break;
        case 'update':     updateEquipment($db);  break;
        case 'delete':     deleteEquipment($db);  break;
        case 'types':      getTypes($db);         break;
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// ── Actions ─────────────────────────────────────────────────────────────────

function listEquipment($db) {
    $search   = $_GET['search'] ?? '';
    $status   = $_GET['status'] ?? '';
    $typeId   = $_GET['type_id'] ?? '';
    $location = $_GET['location_id'] ?? '';

    $sql = "SELECT e.*, 
                r.typeName,
                r.context,
                CONCAT_WS(' ', emp.firstName, emp.lastName) AS employeeName,
                l.location_name AS locationName,
                el.location_name AS employeeLocationName
            FROM tbl_equipment e
            INNER JOIN tbl_equipment_type_registry r ON e.type_id = r.typeId
            LEFT JOIN tbl_employee emp ON e.employee_id = emp.employeeId
            LEFT JOIN location l ON e.location_id = l.location_id
            LEFT JOIN location el ON emp.location_id = el.location_id
            WHERE e.is_archived = 0";
    
    $params = [];

    if (!empty($typeId)) {
        $sql .= " AND e.type_id = :type_id";
        $params[':type_id'] = $typeId;
    }

    if (!empty($status)) {
        $sql .= " AND e.status = :status";
        $params[':status'] = $status;
    }

    if (!empty($location)) {
        $sql .= " AND (e.location_id = :loc OR emp.location_id = :loc2)";
        $params[':loc'] = $location;
        $params[':loc2'] = $location;
    }

    if (!empty($search)) {
        $term = "%$search%";
        $sql .= " AND (
            e.brand LIKE :s1 OR
            e.model LIKE :s2 OR
            e.serial_number LIKE :s3 OR
            e.property_number LIKE :s4 OR
            r.typeName LIKE :s5 OR
            emp.firstName LIKE :s6 OR
            emp.lastName LIKE :s7
        )";
        $params[':s1'] = $term;
        $params[':s2'] = $term;
        $params[':s3'] = $term;
        $params[':s4'] = $term;
        $params[':s5'] = $term;
        $params[':s6'] = $term;
        $params[':s7'] = $term;
    }
    
    $sql .= " ORDER BY e.equipment_id DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $equipment = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Bulk-load specs
    $ids = array_column($equipment, 'equipment_id');
    $specsMap = getBulkSpecs($db, $ids);

    $formatted = array_map(function($e) use ($specsMap) {
        $specs = $specsMap[$e['equipment_id']] ?? [];
        return [
            'equipment_id'   => (int)$e['equipment_id'],
            'type_id'        => (int)$e['type_id'],
            'typeName'       => $e['typeName'],
            'context'        => $e['context'],
            'employee_id'    => $e['employee_id'] ? (int)$e['employee_id'] : null,
            'employeeName'   => $e['employeeName'],
            'location_id'    => $e['location_id'] ? (int)$e['location_id'] : null,
            'locationName'   => $e['locationName'] ?? $e['employeeLocationName'] ?? null,
            'brand'          => $e['brand'],
            'model'          => $e['model'],
            'serial_number'  => $e['serial_number'],
            'property_number'=> $e['property_number'],
            'status'         => $e['status'],
            'year_acquired'  => $e['year_acquired'],
            'acquisition_date' => $e['acquisition_date'],
            'specs'          => $specs,
            // Legacy compat fields
            'employeeId'     => $e['employee_id'] ? (int)$e['employee_id'] : null,
        ];
    }, $equipment);
    
    echo json_encode(['success' => true, 'data' => $formatted]);
}

function getEquipment($db) {
    $id = filter_var($_GET['equipment_id'] ?? $_GET['id'] ?? null, FILTER_VALIDATE_INT);
    if (!$id) throw new Exception('Equipment ID is required');
    
    $stmt = $db->prepare("
        SELECT e.*, 
            r.typeName, r.context,
            CONCAT_WS(' ', emp.firstName, emp.middleName, emp.lastName) AS employeeName,
            l.location_name AS locationName,
            el.location_name AS employeeLocationName
        FROM tbl_equipment e
        INNER JOIN tbl_equipment_type_registry r ON e.type_id = r.typeId
        LEFT JOIN tbl_employee emp ON e.employee_id = emp.employeeId
        LEFT JOIN location l ON e.location_id = l.location_id
        LEFT JOIN location el ON emp.location_id = el.location_id
        WHERE e.equipment_id = :id
    ");
    $stmt->execute([':id' => $id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$item) throw new Exception('Equipment not found');
    
    $specs = getSpecs($db, $id);
    
    $formatted = [
        'equipment_id'   => (int)$item['equipment_id'],
        'type_id'        => (int)$item['type_id'],
        'typeName'       => $item['typeName'],
        'context'        => $item['context'],
        'employee_id'    => $item['employee_id'] ? (int)$item['employee_id'] : null,
        'employeeName'   => $item['employeeName'],
        'location_id'    => $item['location_id'] ? (int)$item['location_id'] : null,
        'locationName'   => $item['locationName'] ?? $item['employeeLocationName'] ?? null,
        'brand'          => $item['brand'],
        'model'          => $item['model'],
        'serial_number'  => $item['serial_number'],
        'property_number'=> $item['property_number'],
        'status'         => $item['status'],
        'year_acquired'  => $item['year_acquired'],
        'acquisition_date' => $item['acquisition_date'],
        'specs'          => $specs,
    ];
    
    echo json_encode(['success' => true, 'data' => $formatted]);
}

function createEquipment($db) {
    $typeId     = validateTypeId($db, $_POST['type_id'] ?? '');
    $brand      = validateBrand($_POST['brand'] ?? '');
    $model      = sanitizeString($_POST['model'] ?? '');
    $serial     = validateSerial($_POST['serial_number'] ?? $_POST['serial'] ?? '');
    $property   = sanitizeString($_POST['property_number'] ?? '');
    $year       = !empty($_POST['year_acquired'] ?? $_POST['year'] ?? '') 
                  ? validateYear($_POST['year_acquired'] ?? $_POST['year'] ?? '') 
                  : null;
    $acqDate    = !empty($_POST['acquisition_date'] ?? '') ? $_POST['acquisition_date'] : null;
    $employeeId = validateEmployeeId($db, $_POST['employee_id'] ?? null);
    $locationId = validateLocationId($db, $_POST['location_id'] ?? null);
    $status     = sanitizeString($_POST['status'] ?? 'Available');
    
    if ($employeeId) $status = 'In Use';
    
    // Check duplicate serial within same type
    if ($serial) {
        $stmt = $db->prepare("SELECT COUNT(*) FROM tbl_equipment WHERE serial_number = :s AND type_id = :t");
        $stmt->execute([':s' => $serial, ':t' => $typeId]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception('Serial number already exists for this equipment type');
        }
    }
    
    $stmt = $db->prepare("
        INSERT INTO tbl_equipment (type_id, employee_id, location_id, brand, model, serial_number, property_number, status, year_acquired, acquisition_date)
        VALUES (:type_id, :employee_id, :location_id, :brand, :model, :serial, :property, :status, :year, :acq_date)
    ");
    $stmt->execute([
        ':type_id'     => $typeId,
        ':employee_id' => $employeeId,
        ':location_id' => $locationId,
        ':brand'       => $brand,
        ':model'       => $model ?: null,
        ':serial'      => $serial ?: null,
        ':property'    => $property ?: null,
        ':status'      => $status,
        ':year'        => $year,
        ':acq_date'    => $acqDate,
    ]);
    $newId = $db->lastInsertId();
    
    // Save specs
    $specs = parseSpecsFromPost();
    saveSpecs($db, $newId, $specs);

    // Get type name for logging
    $stmt = $db->prepare("SELECT typeName FROM tbl_equipment_type_registry WHERE typeId = ?");
    $stmt->execute([$typeId]);
    $typeName = $stmt->fetchColumn();

    logActivity(ACTION_CREATE, MODULE_COMPUTERS,
        "Added {$typeName} — Brand: {$brand}, Serial: {$serial}, Year: {$year}."
        . ($employeeId ? " Assigned to employee ID {$employeeId}." : ""));

    // Auto-create maintenance schedule
    try {
        $maint = new MaintenanceHelper($db);
        $maint->initScheduleByTypeId($typeId, $newId);
    } catch (Exception $e) {
        error_log("Failed to schedule maintenance for equipment ID $newId: " . $e->getMessage());
    }

    echo json_encode([
        'success' => true,
        'message' => "$typeName added successfully",
        'equipment_id' => $newId
    ]);
}

function updateEquipment($db) {
    $id = filter_var($_POST['equipment_id'] ?? $_POST['id'] ?? null, FILTER_VALIDATE_INT);
    if (!$id) throw new Exception('Equipment ID is required');
    
    // Check exists
    $stmt = $db->prepare("SELECT type_id FROM tbl_equipment WHERE equipment_id = ?");
    $stmt->execute([$id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$existing) throw new Exception('Equipment not found');
    
    $typeId     = !empty($_POST['type_id']) ? validateTypeId($db, $_POST['type_id']) : $existing['type_id'];
    $brand      = validateBrand($_POST['brand'] ?? '');
    $model      = sanitizeString($_POST['model'] ?? '');
    $serial     = validateSerial($_POST['serial_number'] ?? $_POST['serial'] ?? '');
    $property   = sanitizeString($_POST['property_number'] ?? '');
    $year       = !empty($_POST['year_acquired'] ?? $_POST['year'] ?? '') 
                  ? validateYear($_POST['year_acquired'] ?? $_POST['year'] ?? '') 
                  : null;
    $acqDate    = !empty($_POST['acquisition_date'] ?? '') ? $_POST['acquisition_date'] : null;
    $employeeId = validateEmployeeId($db, $_POST['employee_id'] ?? null);
    $locationId = validateLocationId($db, $_POST['location_id'] ?? null);
    $status     = sanitizeString($_POST['status'] ?? ($employeeId ? 'In Use' : 'Available'));
    
    // Check duplicate serial within type
    if ($serial) {
        $stmt = $db->prepare("SELECT COUNT(*) FROM tbl_equipment WHERE serial_number = :s AND type_id = :t AND equipment_id != :id");
        $stmt->execute([':s' => $serial, ':t' => $typeId, ':id' => $id]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception('Serial number already exists for this equipment type');
        }
    }
    
    $stmt = $db->prepare("
        UPDATE tbl_equipment SET
            type_id = :type_id, employee_id = :employee_id, location_id = :location_id,
            brand = :brand, model = :model, serial_number = :serial, property_number = :property,
            status = :status, year_acquired = :year, acquisition_date = :acq_date
        WHERE equipment_id = :id
    ");
    $stmt->execute([
        ':type_id'     => $typeId,
        ':employee_id' => $employeeId,
        ':location_id' => $locationId,
        ':brand'       => $brand,
        ':model'       => $model ?: null,
        ':serial'      => $serial ?: null,
        ':property'    => $property ?: null,
        ':status'      => $status,
        ':year'        => $year,
        ':acq_date'    => $acqDate,
        ':id'          => $id,
    ]);
    
    // Update specs
    $specs = parseSpecsFromPost();
    saveSpecs($db, $id, $specs);

    $stmt = $db->prepare("SELECT typeName FROM tbl_equipment_type_registry WHERE typeId = ?");
    $stmt->execute([$typeId]);
    $typeName = $stmt->fetchColumn();

    logActivity(ACTION_UPDATE, MODULE_COMPUTERS,
        "Updated {$typeName} (ID: {$id}) — Brand: {$brand}, Serial: {$serial}."
        . ($employeeId ? " Assigned to employee ID {$employeeId}." : " Unassigned."));

    echo json_encode(['success' => true, 'message' => "$typeName updated successfully"]);
}

function deleteEquipment($db) {
    $id = filter_var($_POST['equipment_id'] ?? $_POST['id'] ?? null, FILTER_VALIDATE_INT);
    if (!$id) throw new Exception('Equipment ID is required');
    
    // Fetch for log
    $stmt = $db->prepare("
        SELECT e.brand, e.serial_number, r.typeName
        FROM tbl_equipment e
        JOIN tbl_equipment_type_registry r ON e.type_id = r.typeId
        WHERE e.equipment_id = ?
    ");
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $db->prepare("DELETE FROM tbl_equipment WHERE equipment_id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() == 0) {
        throw new Exception('Equipment not found or already deleted');
    }
    
    logActivity(ACTION_DELETE, MODULE_COMPUTERS,
        "Deleted {$item['typeName']} (ID: {$id}) — Brand: " . ($item['brand'] ?? 'Unknown')
        . ", Serial: " . ($item['serial_number'] ?? 'Unknown') . ".");
    
    echo json_encode(['success' => true, 'message' => ($item['typeName'] ?? 'Equipment') . ' deleted successfully']);
}

function getTypes($db) {
    $stmt = $db->query("SELECT typeId, typeName, defaultFrequency, context FROM tbl_equipment_type_registry ORDER BY typeName");
    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $types]);
}
