<?php
/**
 * ajax/manage_systemunit.php
 * Adapter: translates legacy System Unit API calls to unified tbl_equipment + tbl_equipment_specs.
 * Maintains backward-compatible JSON response format.
 */
require_once '../config/session-guard.php';
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../includes/maintenanceHelper.php';

header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();

$db = getDB();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// System Unit type_id = 1
$TYPE_ID = 1;

try {
    switch ($action) {
        case 'list':   listItems($db); break;
        case 'get':    getItem($db); break;
        case 'create': createItem($db); break;
        case 'update': updateItem($db); break;
        case 'delete': deleteItem($db); break;
        default: throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function listItems($db) {
    global $TYPE_ID;
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';

    $sql = "SELECT eq.equipment_id, eq.brand, eq.serial_number, eq.year_acquired, eq.employee_id,
                   CONCAT_WS(' ', e.firstName, e.lastName) AS employeeName
            FROM tbl_equipment eq
            LEFT JOIN tbl_employee e ON eq.employee_id = e.employeeId
            WHERE eq.type_id = :tid AND eq.is_archived = 0";
    $params = [':tid' => $TYPE_ID];

    if ($status === 'Active')    $sql .= " AND eq.employee_id IS NOT NULL";
    if ($status === 'Available') $sql .= " AND eq.employee_id IS NULL";

    if ($search !== '') {
        $sql .= " AND (eq.brand LIKE :s OR eq.serial_number LIKE :s2 OR e.firstName LIKE :s3 OR e.lastName LIKE :s4)";
        $t = "%$search%";
        $params[':s'] = $t; $params[':s2'] = $t; $params[':s3'] = $t; $params[':s4'] = $t;
    }
    $sql .= " ORDER BY eq.equipment_id DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $ids = array_column($rows, 'equipment_id');
    $specs = bulkSpecs($db, $ids);

    $data = array_map(function($r) use ($specs) {
        $sp = $specs[$r['equipment_id']] ?? [];
        return [
            'systemunitId' => $r['equipment_id'],
            'systemUnitCategory' => $sp['Category'] ?? 'Pre-Built',
            'systemUnitBrand' => $r['brand'],
            'specificationProcessor' => $sp['Processor'] ?? '',
            'specificationMemory' => $sp['Memory'] ?? '',
            'specificationGPU' => $sp['GPU'] ?? '',
            'specificationStorage' => $sp['Storage'] ?? '',
            'systemUnitSerial' => $r['serial_number'],
            'yearAcquired' => $r['year_acquired'],
            'employeeId' => $r['employee_id'],
            'employeeName' => $r['employeeName'],
            'status' => $r['employee_id'] ? 'Active' : 'Available',
        ];
    }, $rows);
    echo json_encode(['success' => true, 'data' => $data]);
}

function getItem($db) {
    global $TYPE_ID;
    $id = $_GET['systemunit_id'] ?? null;
    if (!$id) throw new Exception('System unit ID is required');

    $stmt = $db->prepare("SELECT eq.*, CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) AS employeeName
        FROM tbl_equipment eq LEFT JOIN tbl_employee e ON eq.employee_id = e.employeeId
        WHERE eq.equipment_id = :id AND eq.type_id = :tid");
    $stmt->execute([':id' => $id, ':tid' => $TYPE_ID]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$r) throw new Exception('System unit not found');
    $sp = getSpecs($db, $id);

    echo json_encode(['success' => true, 'data' => [
        'systemunitId' => $r['equipment_id'],
        'systemUnitCategory' => $sp['Category'] ?? 'Pre-Built',
        'systemUnitBrand' => $r['brand'],
        'specificationProcessor' => $sp['Processor'] ?? '',
        'specificationMemory' => $sp['Memory'] ?? '',
        'specificationGPU' => $sp['GPU'] ?? '',
        'specificationStorage' => $sp['Storage'] ?? '',
        'systemUnitSerial' => $r['serial_number'],
        'yearAcquired' => $r['year_acquired'],
        'employeeId' => $r['employee_id'],
        'employeeName' => $r['employeeName'],
        'location_id' => $r['location_id'],
        'maintenanceDate' => $sp['Maintenance Date'] ?? null,
        'nextMaintenanceDate' => $sp['Next Maintenance Date'] ?? null,
        'status' => $r['employee_id'] ? 'Active' : 'Available',
    ]]);
}

function createItem($db) {
    global $TYPE_ID;
    $brand = trim($_POST['brand'] ?? '');
    $serial = trim($_POST['serial'] ?? '');
    $year = $_POST['year'] ?? null;
    $empId = $_POST['employee_id'] ?? null;
    $locId = $_POST['location_id'] ?? null;
    $category = trim($_POST['category'] ?? 'Pre-Built');
    $processor = trim($_POST['processor'] ?? '');
    $memory = trim($_POST['memory'] ?? '');
    $gpu = trim($_POST['gpu'] ?? '');
    $storage = trim($_POST['storage'] ?? '');
    $maintDate = trim($_POST['maintenance_date'] ?? '');
    $nextMaintDate = trim($_POST['next_maintenance_date'] ?? '');

    if (empty($brand)) throw new Exception('Brand is required');
    if (empty($serial)) throw new Exception('Serial number is required');

    $db->beginTransaction();
    $stmt = $db->prepare("INSERT INTO tbl_equipment (type_id, employee_id, location_id, brand, serial_number, status, year_acquired)
        VALUES (:tid, :eid, :lid, :brand, :serial, 'Active', :year)");
    $stmt->execute([':tid' => $TYPE_ID, ':eid' => $empId ?: null, ':lid' => $locId ?: null, ':brand' => $brand, ':serial' => $serial, ':year' => $year ?: null]);
    $newId = $db->lastInsertId();

    $specData = ['Category' => $category, 'Processor' => $processor, 'Memory' => $memory, 'GPU' => $gpu, 'Storage' => $storage, 'Maintenance Date' => $maintDate, 'Next Maintenance Date' => $nextMaintDate];
    saveSpecs($db, $newId, $specData);

    $db->commit();

    logActivity(ACTION_CREATE, MODULE_COMPUTERS, "Added System Unit — Brand: {$brand}, Serial: {$serial}.");
    echo json_encode(['success' => true, 'message' => 'System unit added successfully', 'systemunit_id' => $newId]);
}

function updateItem($db) {
    global $TYPE_ID;
    $id = $_POST['systemunit_id'] ?? null;
    if (!$id) throw new Exception('System unit ID is required');

    $brand = trim($_POST['brand'] ?? '');
    $serial = trim($_POST['serial'] ?? '');
    $year = $_POST['year'] ?? null;
    $empId = $_POST['employee_id'] ?? null;
    $locId = $_POST['location_id'] ?? null;
    $category = trim($_POST['category'] ?? 'Pre-Built');
    $processor = trim($_POST['processor'] ?? '');
    $memory = trim($_POST['memory'] ?? '');
    $gpu = trim($_POST['gpu'] ?? '');
    $storage = trim($_POST['storage'] ?? '');
    $maintDate = trim($_POST['maintenance_date'] ?? '');
    $nextMaintDate = trim($_POST['next_maintenance_date'] ?? '');

    $db->beginTransaction();
    $stmt = $db->prepare("UPDATE tbl_equipment SET brand = :brand, serial_number = :serial, year_acquired = :year, employee_id = :eid, location_id = :lid
        WHERE equipment_id = :id AND type_id = :tid");
    $stmt->execute([':brand' => $brand, ':serial' => $serial, ':year' => $year ?: null, ':eid' => $empId ?: null, ':lid' => $locId ?: null, ':id' => $id, ':tid' => $TYPE_ID]);

    $specData = ['Category' => $category, 'Processor' => $processor, 'Memory' => $memory, 'GPU' => $gpu, 'Storage' => $storage, 'Maintenance Date' => $maintDate, 'Next Maintenance Date' => $nextMaintDate];
    saveSpecs($db, $id, $specData);
    $db->commit();

    logActivity(ACTION_UPDATE, MODULE_COMPUTERS, "Updated System Unit (ID: {$id}) — Brand: {$brand}, Serial: {$serial}.");
    echo json_encode(['success' => true, 'message' => 'System unit updated successfully']);
}

function deleteItem($db) {
    $id = $_POST['systemunit_id'] ?? null;
    if (!$id) throw new Exception('System unit ID is required');

    $db->beginTransaction();
    $db->prepare("DELETE FROM tbl_equipment_specs WHERE equipment_id = :id")->execute([':id' => $id]);
    $db->prepare("DELETE FROM tbl_maintenance_schedule WHERE equipmentId = :id AND equipmentType = :tid")->execute([':id' => $id, ':tid' => 1]);
    $stmt = $db->prepare("DELETE FROM tbl_equipment WHERE equipment_id = :id");
    $stmt->execute([':id' => $id]);
    if ($stmt->rowCount() == 0) { $db->rollBack(); throw new Exception('System unit not found'); }
    $db->commit();

    logActivity(ACTION_DELETE, MODULE_COMPUTERS, "Deleted System Unit (ID: {$id}).");
    echo json_encode(['success' => true, 'message' => 'System unit deleted successfully']);
}

function getSpecs($db, $eqId) {
    $stmt = $db->prepare("SELECT spec_key, spec_value FROM tbl_equipment_specs WHERE equipment_id = :id");
    $stmt->execute([':id' => $eqId]);
    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

function bulkSpecs($db, $ids) {
    if (empty($ids)) return [];
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $db->prepare("SELECT equipment_id, spec_key, spec_value FROM tbl_equipment_specs WHERE equipment_id IN ($ph)");
    $stmt->execute($ids);
    $map = [];
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) $map[$r['equipment_id']][$r['spec_key']] = $r['spec_value'];
    return $map;
}

function saveSpecs($db, $eqId, $specs) {
    $db->prepare("DELETE FROM tbl_equipment_specs WHERE equipment_id = :id")->execute([':id' => $eqId]);
    $stmt = $db->prepare("INSERT INTO tbl_equipment_specs (equipment_id, spec_key, spec_value) VALUES (:id, :k, :v)");
    foreach ($specs as $k => $v) {
        if (trim($v) !== '') $stmt->execute([':id' => $eqId, ':k' => $k, ':v' => trim($v)]);
    }
}
