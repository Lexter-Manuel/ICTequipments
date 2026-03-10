<?php
/**
 * ajax/manage_otherequipment.php
 * Adapter: translates legacy Other Equipment API calls to unified tbl_equipment + tbl_equipment_specs.
 * Unlike other adapters, this handles DYNAMIC type_ids (any type not in the fixed 1-4 set).
 */
require_once '../config/session-guard.php';
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../includes/maintenanceHelper.php';
require_once '../includes/assignmentHistoryHelper.php';

header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();

$db = getDB();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Built-in type IDs that have their own dedicated manage_*.php handlers
$BUILTIN_TYPE_IDS = [1, 2, 3, 4]; // System Unit, All-in-One, Monitor, Printer

function sanitizeString($input) {
    return trim(htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8'));
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

function resolveTypeId($db, $typeName) {
    $stmt = $db->prepare("SELECT typeId FROM tbl_equipment_type_registry WHERE typeName = :tn");
    $stmt->execute([':tn' => $typeName]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? (int)$row['typeId'] : null;
}

function autoRegisterType($db, $typeName, $employeeId = null) {
    $context = $employeeId ? 'Employee' : 'Location';
    $stmt = $db->prepare("INSERT INTO tbl_equipment_type_registry (typeName, defaultFrequency, context) VALUES (:tn, 180, :ctx)");
    $stmt->execute([':tn' => $typeName, ':ctx' => $context]);
    error_log("Auto-registered new equipment type: {$typeName}");
    return (int)$db->lastInsertId();
}

try {
    switch ($action) {
        case 'list':   listEquipment($db); break;
        case 'get':    getEquipment($db); break;
        case 'create': createEquipment($db); break;
        case 'update': updateEquipment($db); break;
        case 'delete': deleteEquipment($db); break;
        default: throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function listEquipment($db) {
    global $BUILTIN_TYPE_IDS;
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';

    $builtinPh = implode(',', $BUILTIN_TYPE_IDS);
    $sql = "SELECT eq.equipment_id, eq.type_id, eq.brand, eq.model, eq.serial_number, eq.property_number,
                   eq.status, eq.year_acquired, eq.employee_id, eq.location_id,
                   etr.typeName AS equipmentType,
                   CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) AS employeeName,
                   l.location_name
            FROM tbl_equipment eq
            LEFT JOIN tbl_employee e ON eq.employee_id = e.employeeId
            LEFT JOIN location l ON eq.location_id = l.location_id
            LEFT JOIN tbl_equipment_type_registry etr ON eq.type_id = etr.typeId
            WHERE eq.type_id NOT IN ($builtinPh) AND eq.is_archived = 0";
    $params = [];

    if (!empty($status)) {
        $sql .= " AND eq.status = :status";
        $params[':status'] = $status;
    }
    if (!empty($search)) {
        $t = "%$search%";
        $sql .= " AND (etr.typeName LIKE :s1 OR eq.brand LIKE :s2 OR eq.model LIKE :s3 OR eq.serial_number LIKE :s4
                  OR l.location_name LIKE :s5 OR CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) LIKE :s6)";
        $params[':s1'] = $t; $params[':s2'] = $t; $params[':s3'] = $t;
        $params[':s4'] = $t; $params[':s5'] = $t; $params[':s6'] = $t;
    }
    $sql .= " ORDER BY eq.equipment_id DESC";
    $stmt = $db->prepare($sql); $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $specs = bulkSpecs($db, array_column($rows, 'equipment_id'));

    $data = array_map(function($r) use ($specs) {
        $sp = $specs[$r['equipment_id']] ?? [];
        return [
            'otherEquipmentId' => $r['equipment_id'],
            'equipmentType' => $r['equipmentType'],
            'brand' => $r['brand'],
            'model' => $r['model'],
            'serialNumber' => $r['serial_number'],
            'details' => $sp['Details'] ?? '',
            'status' => $r['status'],
            'yearAcquired' => $r['year_acquired'],
            'location_id' => $r['location_id'],
            'location_name' => $r['location_name'],
            'employeeId' => $r['employee_id'],
            'employeeName' => $r['employeeName'],
        ];
    }, $rows);
    echo json_encode(['success' => true, 'data' => $data]);
}

function getEquipment($db) {
    global $BUILTIN_TYPE_IDS;
    $id = $_GET['id'] ?? null;
    if (!$id) throw new Exception('ID required');

    $builtinPh = implode(',', $BUILTIN_TYPE_IDS);
    $stmt = $db->prepare("SELECT eq.*, etr.typeName AS equipmentType,
            CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) AS employeeName,
            l.location_name
        FROM tbl_equipment eq
        LEFT JOIN tbl_employee e ON eq.employee_id = e.employeeId
        LEFT JOIN location l ON eq.location_id = l.location_id
        LEFT JOIN tbl_equipment_type_registry etr ON eq.type_id = etr.typeId
        WHERE eq.equipment_id = :id AND eq.type_id NOT IN ($builtinPh)");
    $stmt->execute([':id' => $id]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$r) throw new Exception('Equipment not found');
    $sp = getSpecs($db, $id);

    echo json_encode(['success' => true, 'data' => [
        'otherEquipmentId' => $r['equipment_id'],
        'equipmentType' => $r['equipmentType'],
        'brand' => $r['brand'],
        'model' => $r['model'],
        'serialNumber' => $r['serial_number'],
        'details' => $sp['Details'] ?? '',
        'status' => $r['status'],
        'yearAcquired' => $r['year_acquired'],
        'location_id' => $r['location_id'],
        'location_name' => $r['location_name'],
        'employeeId' => $r['employee_id'],
        'employeeName' => $r['employeeName'],
        'maintenanceDate' => $sp['Maintenance Date'] ?? null,
        'nextMaintenanceDate' => $sp['Next Maintenance Date'] ?? null,
    ]]);
}

function createEquipment($db) {
    $type = sanitizeString($_POST['type'] ?? '');
    if (empty($type)) throw new Exception("Equipment Type is required");
    $brand = sanitizeString($_POST['brand'] ?? '');
    if (empty($brand)) throw new Exception("Brand name cannot be empty");
    $model = sanitizeString($_POST['model'] ?? '');
    $serial = sanitizeString($_POST['serial'] ?? '');
    if (empty($serial)) throw new Exception("Serial number cannot be empty");
    $details = sanitizeString($_POST['details'] ?? '');
    $year = $_POST['year'] ?? date('Y');
    $locationId = $_POST['location_id'] ?? null;
    $employeeId = validateEmployeeId($db, $_POST['employee_id'] ?? null);
    $status = $_POST['status'] ?? 'Available';
    if ($employeeId) $status = 'In Use';

    // Resolve or auto-register type
    $typeId = resolveTypeId($db, $type);
    if (!$typeId) $typeId = autoRegisterType($db, $type, $employeeId);

    // Check duplicate serial within this type
    $chk = $db->prepare("SELECT COUNT(*) FROM tbl_equipment WHERE type_id = :tid AND serial_number = :s");
    $chk->execute([':tid' => $typeId, ':s' => $serial]);
    if ($chk->fetchColumn() > 0) throw new Exception("Serial number already exists for this equipment type");

    $db->beginTransaction();
    $stmt = $db->prepare("INSERT INTO tbl_equipment (type_id, employee_id, location_id, brand, model, serial_number, status, year_acquired) VALUES (:tid,:eid,:lid,:brand,:model,:serial,:status,:year)");
    $stmt->execute([':tid'=>$typeId, ':eid'=>$employeeId, ':lid'=>$locationId?:null, ':brand'=>$brand, ':model'=>$model?:null, ':serial'=>$serial?:null, ':status'=>$status, ':year'=>$year]);
    $newId = $db->lastInsertId();
    $maintDate = trim($_POST['maintenance_date'] ?? '');
    $nextMaintDate = trim($_POST['next_maintenance_date'] ?? '');
    saveSpecs($db, $newId, ['Details' => $details, 'Maintenance Date' => $maintDate, 'Next Maintenance Date' => $nextMaintDate]);

    $db->commit();

    logActivity(ACTION_CREATE, MODULE_OTHER_EQUIPMENT,
        "Added {$type} — {$brand} {$model}, Serial: {$serial}, Status: {$status}, Year: {$year}."
        . ($employeeId ? " Assigned to employee ID {$employeeId}." : ""));

    echo json_encode(['success' => true, 'message' => 'Equipment added successfully']);
}

function updateEquipment($db) {
    $id = $_POST['otherEquipmentId'] ?? null;
    if (!$id) throw new Exception("ID required");

    // Fetch old employee_id for history tracking
    $oldStmt = $db->prepare("SELECT employee_id FROM tbl_equipment WHERE equipment_id = ?");
    $oldStmt->execute([$id]);
    $oldRow = $oldStmt->fetch(PDO::FETCH_ASSOC);
    $oldEmployeeId = $oldRow ? ($oldRow['employee_id'] ? (int)$oldRow['employee_id'] : null) : null;

    $type = sanitizeString($_POST['type'] ?? '');
    $brand = sanitizeString($_POST['brand'] ?? '');
    if (empty($brand)) throw new Exception("Brand name cannot be empty");
    $model = sanitizeString($_POST['model'] ?? '');
    $serial = sanitizeString($_POST['serial'] ?? '');
    if (empty($serial)) throw new Exception("Serial number cannot be empty");
    $details = sanitizeString($_POST['details'] ?? '');
    $year = $_POST['year'] ?? null;
    $locationId = $_POST['location_id'] ?? null;
    $employeeId = validateEmployeeId($db, $_POST['employee_id'] ?? null);
    $status = $_POST['status'] ?? 'Available';
    if ($employeeId) $status = 'In Use';

    // Resolve type if changed
    $typeId = null;
    if (!empty($type)) {
        $typeId = resolveTypeId($db, $type);
        if (!$typeId) $typeId = autoRegisterType($db, $type, $employeeId);
    }

    $db->beginTransaction();
    $sql = "UPDATE tbl_equipment SET brand=:brand, model=:model, serial_number=:serial, status=:status, year_acquired=:year, location_id=:lid, employee_id=:eid";
    $params = [':brand'=>$brand, ':model'=>$model?:null, ':serial'=>$serial?:null, ':status'=>$status, ':year'=>$year, ':lid'=>$locationId?:null, ':eid'=>$employeeId, ':id'=>$id];
    if ($typeId) { $sql .= ", type_id=:tid"; $params[':tid'] = $typeId; }
    $sql .= " WHERE equipment_id=:id";
    $db->prepare($sql)->execute($params);
    $maintDate = trim($_POST['maintenance_date'] ?? '');
    $nextMaintDate = trim($_POST['next_maintenance_date'] ?? '');
    saveSpecs($db, $id, ['Details' => $details, 'Maintenance Date' => $maintDate, 'Next Maintenance Date' => $nextMaintDate]);

    recordAssignmentChange($db, (int)$id, $oldEmployeeId, $employeeId);

    $db->commit();

    logActivity(ACTION_UPDATE, MODULE_OTHER_EQUIPMENT,
        "Updated {$type} (ID: {$id}) — {$brand} {$model}, Serial: {$serial}, Status: {$status}."
        . ($employeeId ? " Assigned to employee ID {$employeeId}." : " Unassigned."));

    echo json_encode(['success' => true, 'message' => 'Equipment updated successfully']);
}

function deleteEquipment($db) {
    $id = $_POST['id'] ?? null;
    if (!$id) throw new Exception('ID required');

    // Fetch details before deleting
    $row = $db->prepare("SELECT eq.brand, eq.model, eq.serial_number, etr.typeName AS equipmentType
        FROM tbl_equipment eq LEFT JOIN tbl_equipment_type_registry etr ON eq.type_id = etr.typeId
        WHERE eq.equipment_id = :id");
    $row->execute([':id' => $id]);
    $item = $row->fetch(PDO::FETCH_ASSOC);

    $db->beginTransaction();
    $db->prepare("DELETE FROM tbl_equipment_specs WHERE equipment_id = :id")->execute([':id'=>$id]);
    $db->prepare("DELETE FROM tbl_maintenance_schedule WHERE equipmentId = :id")->execute([':id'=>$id]);
    $stmt = $db->prepare("DELETE FROM tbl_equipment WHERE equipment_id = :id"); $stmt->execute([':id'=>$id]);
    if ($stmt->rowCount() == 0) { $db->rollBack(); throw new Exception('Equipment not found'); }
    $db->commit();

    logActivity(ACTION_DELETE, MODULE_OTHER_EQUIPMENT,
        "Deleted " . ($item['equipmentType'] ?? 'Equipment') . " (ID: {$id}) — "
        . ($item['brand'] ?? '') . " " . ($item['model'] ?? '')
        . ", Serial: " . ($item['serial_number'] ?? 'Unknown') . ".");

    echo json_encode(['success' => true, 'message' => 'Equipment deleted successfully']);
}

function getSpecs($db, $id) { $s = $db->prepare("SELECT spec_key, spec_value FROM tbl_equipment_specs WHERE equipment_id = :id"); $s->execute([':id'=>$id]); return $s->fetchAll(PDO::FETCH_KEY_PAIR); }
function bulkSpecs($db, $ids) { if (empty($ids)) return []; $ph = implode(',', array_fill(0, count($ids), '?')); $s = $db->prepare("SELECT equipment_id, spec_key, spec_value FROM tbl_equipment_specs WHERE equipment_id IN ($ph)"); $s->execute($ids); $m = []; while ($r = $s->fetch(PDO::FETCH_ASSOC)) $m[$r['equipment_id']][$r['spec_key']] = $r['spec_value']; return $m; }
function saveSpecs($db, $id, $specs) { $db->prepare("DELETE FROM tbl_equipment_specs WHERE equipment_id = :id")->execute([':id'=>$id]); $s = $db->prepare("INSERT INTO tbl_equipment_specs (equipment_id, spec_key, spec_value) VALUES (:id,:k,:v)"); foreach ($specs as $k => $v) { if (trim($v) !== '') $s->execute([':id'=>$id,':k'=>$k,':v'=>trim($v)]); } }
