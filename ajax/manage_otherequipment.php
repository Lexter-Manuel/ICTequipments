<?php

require_once '../config/database.php';
require_once '../includes/maintenanceHelper.php';

header('Content-Type: application/json');

$db = getDB();
$action = $_GET['action'] ?? $_POST['action'] ?? '';
function sanitizeString($input) {
    return trim(htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8'));
}

function validateBrand($brand) {
    $brand = sanitizeString($brand);
    if (empty($brand)) throw new Exception("Brand name cannot be empty");
    return $brand;
}

function validateSerial($serial) {
    $serial = sanitizeString($serial);
    if (empty($serial)) throw new Exception("Serial number cannot be empty");
    return $serial;
}

function validateEmployeeId($db, $employeeId) {
    if (empty($employeeId)) return null;
    $employeeId = filter_var($employeeId, FILTER_VALIDATE_INT);
    if (!$employeeId) throw new Exception("Invalid employee ID");
    
    $stmt = $db->prepare("SELECT COUNT(*) FROM tbl_employee WHERE employeeId = :id");
    $stmt->execute([':id' => $employeeId]);
    if ($stmt->fetchColumn() == 0) throw new Exception("Employee not found");
    
    return $employeeId;
}
// =======================================================

try {
    switch ($action) {
        case 'list': listEquipment($db); break;
        case 'get': getEquipment($db); break;
        case 'create': createEquipment($db); break;
        case 'update': updateEquipment($db); break;
        case 'delete': deleteEquipment($db); break;
        default: throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function listEquipment($db) {
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';

    $sql = "
        SELECT 
            o.*,
            CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) as employeeName,
            l.location_name
        FROM tbl_otherequipment o
        LEFT JOIN tbl_employee e ON o.employeeId = e.employeeId
        LEFT JOIN location l ON o.location_id = l.location_id
        WHERE 1=1
    ";

    $params = [];

    if (!empty($status)) {
        $sql .= " AND o.status = :status";
        $params[':status'] = $status;
    }

    if (!empty($search)) {
        $term = "%$search%";
        $sql .= " AND (
            o.equipmentType LIKE :s1 OR
            o.brand LIKE :s2 OR
            o.model LIKE :s3 OR
            o.serialNumber LIKE :s4 OR
            o.details LIKE :s5 OR
            l.location_name LIKE :s6 OR
            CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) LIKE :s7
        )";
        // Unique placeholders to prevent SQL HY093 errors
        $params[':s1'] = $term; $params[':s2'] = $term; $params[':s3'] = $term;
        $params[':s4'] = $term; $params[':s5'] = $term; $params[':s6'] = $term;
        $params[':s7'] = $term;
    }

    $sql .= " ORDER BY o.otherEquipmentId DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $data]);
}

function getEquipment($db) {
    $id = $_GET['id'] ?? null;
    if (!$id) throw new Exception('ID required');
    
    $stmt = $db->prepare("SELECT * FROM tbl_otherequipment WHERE otherEquipmentId = :id");
    $stmt->execute([':id' => $id]);
    $item = $stmt->fetch();
    
    if (!$item) throw new Exception('Equipment not found');
    echo json_encode(['success' => true, 'data' => $item]);
}

function createEquipment($db) {
    // 1. Validate Inputs
    $type = sanitizeString($_POST['type'] ?? '');
    if (empty($type)) throw new Exception("Equipment Type is required");
    
    $brand = validateBrand($_POST['brand'] ?? '');
    $model = sanitizeString($_POST['model'] ?? '');
    $serial = validateSerial($_POST['serial'] ?? '');
    $details = sanitizeString($_POST['details'] ?? '');
    $year = $_POST['year'] ?? date('Y');
    $locationId = $_POST['location_id'] ?? null;
    $employeeId = validateEmployeeId($db, $_POST['employee_id'] ?? null);
    
    // Auto-set status
    $status = $_POST['status'] ?? 'Available';
    if ($employeeId) $status = 'In Use';

    // Check Duplicate Serial
    $chk = $db->prepare("SELECT COUNT(*) FROM tbl_otherequipment WHERE serialNumber = ?");
    $chk->execute([$serial]);
    if($chk->fetchColumn() > 0) throw new Exception("Serial number already exists");

    // 2. Insert Record
    $stmt = $db->prepare("
        INSERT INTO tbl_otherequipment 
        (equipmentType, brand, model, details, serialNumber, status, yearAcquired, location_id, employeeId, createdAt)
        VALUES (:type, :brand, :model, :details, :serial, :status, :year, :loc, :emp, NOW())
    ");
    
    $stmt->execute([
        ':type' => $type, ':brand' => $brand, ':model' => $model, 
        ':details' => $details, ':serial' => $serial, ':status' => $status,
        ':year' => $year, ':loc' => $locationId, ':emp' => $employeeId
    ]);

    $newId = $db->lastInsertId();

    // 3. MAINTENANCE HOOK (Dynamic Type Lookup)
    try {
        $maint = new MaintenanceHelper($db);
        // Look up the Type ID from registry based on the text user entered (e.g., "Projector")
        $maint->initScheduleByType($type, $newId); 
    } catch (Exception $e) {
        // Silently fail or log error so we don't break the creation process
        error_log("Maintenance Schedule Error: " . $e->getMessage());
    }

    echo json_encode(['success' => true, 'message' => 'Equipment added successfully']);
}

function updateEquipment($db) {
    $id = $_POST['otherEquipmentId'] ?? null;
    if (!$id) throw new Exception("ID required");

    $type = sanitizeString($_POST['type'] ?? '');
    $brand = validateBrand($_POST['brand'] ?? '');
    $model = sanitizeString($_POST['model'] ?? '');
    $serial = validateSerial($_POST['serial'] ?? '');
    $details = sanitizeString($_POST['details'] ?? '');
    $year = $_POST['year'];
    $locationId = $_POST['location_id'] ?? null;
    $employeeId = validateEmployeeId($db, $_POST['employee_id'] ?? null);
    
    $status = $_POST['status'];
    if ($employeeId) $status = 'In Use';

    $stmt = $db->prepare("
        UPDATE tbl_otherequipment SET
            equipmentType = :type, brand = :brand, model = :model,
            details = :details, serialNumber = :serial, status = :status,
            yearAcquired = :year, location_id = :loc, employeeId = :emp,
            updatedAt = NOW()
        WHERE otherEquipmentId = :id
    ");
    
    $stmt->execute([
        ':type' => $type, ':brand' => $brand, ':model' => $model, 
        ':details' => $details, ':serial' => $serial, ':status' => $status,
        ':year' => $year, ':loc' => $locationId, ':emp' => $employeeId,
        ':id' => $id
    ]);

    echo json_encode(['success' => true, 'message' => 'Equipment updated successfully']);
}

function deleteEquipment($db) {
    $id = $_POST['id'] ?? null;
    $stmt = $db->prepare("DELETE FROM tbl_otherequipment WHERE otherEquipmentId = :id");
    $stmt->execute([':id' => $id]);
    echo json_encode(['success' => true, 'message' => 'Equipment deleted successfully']);
}
?>