<?php
/**
 * ajax/manage_printer.php
 * Adapter: translates legacy Printer API calls to unified tbl_equipment + tbl_equipment_specs.
 */
require_once '../config/session-guard.php';
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../includes/maintenanceHelper.php';

header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();

$db = getDB();
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$TYPE_ID = 4; // Printer

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

    $sql = "SELECT eq.equipment_id, eq.brand, eq.model, eq.serial_number, eq.year_acquired, eq.employee_id,
                   CONCAT_WS(' ', e.firstName, e.lastName) AS employeeName
            FROM tbl_equipment eq LEFT JOIN tbl_employee e ON eq.employee_id = e.employeeId
            WHERE eq.type_id = :tid AND eq.is_archived = 0";
    $params = [':tid' => $TYPE_ID];

    if ($status === 'Active')    $sql .= " AND eq.employee_id IS NOT NULL";
    if ($status === 'Available') $sql .= " AND eq.employee_id IS NULL";
    if ($search !== '') {
        $sql .= " AND (eq.brand LIKE :s OR eq.model LIKE :s2 OR eq.serial_number LIKE :s3 OR e.firstName LIKE :s4 OR e.lastName LIKE :s5)";
        $t = "%$search%"; $params[':s'] = $t; $params[':s2'] = $t; $params[':s3'] = $t; $params[':s4'] = $t; $params[':s5'] = $t;
    }
    $sql .= " ORDER BY eq.equipment_id DESC";
    $stmt = $db->prepare($sql); $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = array_map(function($r) {
        return [
            'printerId' => $r['equipment_id'], 'printerBrand' => $r['brand'],
            'printerModel' => $r['model'], 'printerSerial' => $r['serial_number'],
            'yearAcquired' => $r['year_acquired'], 'employeeId' => $r['employee_id'],
            'employeeName' => $r['employeeName'], 'status' => $r['employee_id'] ? 'Active' : 'Available',
        ];
    }, $rows);
    echo json_encode(['success' => true, 'data' => $data]);
}

function getItem($db) {
    global $TYPE_ID;
    $id = $_GET['printer_id'] ?? null;
    if (!$id) throw new Exception('Printer ID is required');
    $stmt = $db->prepare("SELECT eq.*, CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) AS employeeName
        FROM tbl_equipment eq LEFT JOIN tbl_employee e ON eq.employee_id = e.employeeId
        WHERE eq.equipment_id = :id AND eq.type_id = :tid");
    $stmt->execute([':id' => $id, ':tid' => $TYPE_ID]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$r) throw new Exception('Printer not found');
    $sp = getSpecs($db, $id);
    echo json_encode(['success' => true, 'data' => [
        'printerId' => $r['equipment_id'], 'printerBrand' => $r['brand'],
        'printerModel' => $r['model'], 'printerSerial' => $r['serial_number'],
        'yearAcquired' => $r['year_acquired'], 'employeeId' => $r['employee_id'],
        'employeeName' => $r['employeeName'], 'location_id' => $r['location_id'],
        'maintenanceDate' => $sp['Maintenance Date'] ?? null,
        'nextMaintenanceDate' => $sp['Next Maintenance Date'] ?? null,
        'status' => $r['employee_id'] ? 'Active' : 'Available',
    ]]);
}

function createItem($db) {
    global $TYPE_ID;
    $brand = trim($_POST['brand'] ?? ''); $model = trim($_POST['model'] ?? '');
    $serial = trim($_POST['serial_number'] ?? $_POST['serial'] ?? ''); $year = $_POST['year_acquired'] ?? $_POST['year'] ?? null;
    $empId = $_POST['employee_id'] ?? null;
    $locId = $_POST['location_id'] ?? null;
    $maintDate = trim($_POST['maintenance_date'] ?? '');
    $nextMaintDate = trim($_POST['next_maintenance_date'] ?? '');
    if (empty($brand)) throw new Exception('Brand is required');

    $db->beginTransaction();
    $stmt = $db->prepare("INSERT INTO tbl_equipment (type_id, employee_id, location_id, brand, model, serial_number, status, year_acquired) VALUES (:tid,:eid,:lid,:brand,:model,:serial,'Active',:year)");
    $stmt->execute([':tid'=>$TYPE_ID,':eid'=>$empId?:null,':lid'=>$locId?:null,':brand'=>$brand,':model'=>$model?:null,':serial'=>$serial?:null,':year'=>$year?:null]);
    $newId = $db->lastInsertId();
    saveSpecs($db, $newId, ['Maintenance Date' => $maintDate, 'Next Maintenance Date' => $nextMaintDate]);
    $db->commit();
    logActivity(ACTION_CREATE, MODULE_COMPUTERS, "Added Printer — Brand: {$brand}, Model: {$model}.");
    echo json_encode(['success' => true, 'message' => 'Printer added successfully', 'printer_id' => $newId]);
}

function updateItem($db) {
    global $TYPE_ID;
    $id = $_POST['printer_id'] ?? null; if (!$id) throw new Exception('Printer ID is required');
    $brand = trim($_POST['brand'] ?? ''); $model = trim($_POST['model'] ?? '');
    $serial = trim($_POST['serial_number'] ?? $_POST['serial'] ?? ''); $year = $_POST['year_acquired'] ?? $_POST['year'] ?? null;
    $empId = $_POST['employee_id'] ?? null;
    $locId = $_POST['location_id'] ?? null;
    $maintDate = trim($_POST['maintenance_date'] ?? '');
    $nextMaintDate = trim($_POST['next_maintenance_date'] ?? '');

    $db->prepare("UPDATE tbl_equipment SET brand=:brand, model=:model, serial_number=:serial, year_acquired=:year, employee_id=:eid, location_id=:lid WHERE equipment_id=:id AND type_id=:tid")
       ->execute([':brand'=>$brand,':model'=>$model?:null,':serial'=>$serial?:null,':year'=>$year?:null,':eid'=>$empId?:null,':lid'=>$locId?:null,':id'=>$id,':tid'=>$TYPE_ID]);
    saveSpecs($db, $id, ['Maintenance Date' => $maintDate, 'Next Maintenance Date' => $nextMaintDate]);
    logActivity(ACTION_UPDATE, MODULE_COMPUTERS, "Updated Printer (ID: {$id}) — Brand: {$brand}.");
    echo json_encode(['success' => true, 'message' => 'Printer updated successfully']);
}

function deleteItem($db) {
    global $TYPE_ID;
    $id = $_POST['printer_id'] ?? null; if (!$id) throw new Exception('Printer ID is required');
    $db->beginTransaction();
    $db->prepare("DELETE FROM tbl_maintenance_schedule WHERE equipmentId = :id AND equipmentType = :tid")->execute([':id'=>$id,':tid'=>$TYPE_ID]);
    $stmt = $db->prepare("DELETE FROM tbl_equipment WHERE equipment_id = :id"); $stmt->execute([':id'=>$id]);
    if ($stmt->rowCount() == 0) { $db->rollBack(); throw new Exception('Printer not found'); }
    $db->commit();
    logActivity(ACTION_DELETE, MODULE_COMPUTERS, "Deleted Printer (ID: {$id}).");
    echo json_encode(['success' => true, 'message' => 'Printer deleted successfully']);
}

function getSpecs($db, $id) { $s = $db->prepare("SELECT spec_key, spec_value FROM tbl_equipment_specs WHERE equipment_id = :id"); $s->execute([':id'=>$id]); return $s->fetchAll(PDO::FETCH_KEY_PAIR); }
function saveSpecs($db, $id, $specs) { $db->prepare("DELETE FROM tbl_equipment_specs WHERE equipment_id = :id")->execute([':id'=>$id]); $s = $db->prepare("INSERT INTO tbl_equipment_specs (equipment_id, spec_key, spec_value) VALUES (:id,:k,:v)"); foreach ($specs as $k => $v) { if (trim($v) !== '') $s->execute([':id'=>$id,':k'=>$k,':v'=>trim($v)]); } }
