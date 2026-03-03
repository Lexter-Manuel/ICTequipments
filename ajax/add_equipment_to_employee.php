<?php
/**
 * ajax/add_equipment_to_employee.php
 * Assigns existing equipment or creates new equipment for an employee.
 * Updated for unified tbl_equipment schema.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
require_once '../includes/maintenanceHelper.php';
header('Content-Type: application/json');

$db = Database::getInstance()->getConnection();

$action = $_POST['action'] ?? '';

try {
    switch ($action) {

        // Assign an existing equipment record to an employee
        case 'assign':
            $equipmentId = filter_input(INPUT_POST, 'equipment_id', FILTER_VALIDATE_INT);
            $employeeId  = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);

            if (!$equipmentId || !$employeeId) {
                echo json_encode(['success' => false, 'message' => 'Missing equipment_id or employee_id.']);
                exit;
            }

            $stmt = $db->prepare("UPDATE tbl_equipment SET employee_id = :emp WHERE equipment_id = :eid AND employee_id IS NULL");
            $stmt->execute([':emp' => $employeeId, ':eid' => $equipmentId]);

            if ($stmt->rowCount() === 0) {
                echo json_encode(['success' => false, 'message' => 'Equipment already assigned or not found.']);
                exit;
            }

            echo json_encode(['success' => true, 'message' => 'Equipment assigned successfully.']);
            break;

        // Create new equipment and assign to employee
        case 'create':
            $employeeId    = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);
            $typeId        = filter_input(INPUT_POST, 'type_id', FILTER_VALIDATE_INT);
            $brand         = trim($_POST['brand'] ?? '');
            $model         = trim($_POST['model'] ?? '');
            $serialNumber  = trim($_POST['serial_number'] ?? '');
            $propertyNumber= trim($_POST['property_number'] ?? '');
            $yearAcquired  = filter_input(INPUT_POST, 'year_acquired', FILTER_VALIDATE_INT);
            $locationId    = filter_input(INPUT_POST, 'location_id', FILTER_VALIDATE_INT) ?: null;

            if (!$employeeId || !$typeId) {
                echo json_encode(['success' => false, 'message' => 'Employee and equipment type are required.']);
                exit;
            }

            $db->beginTransaction();

            $stmt = $db->prepare("
                INSERT INTO tbl_equipment (type_id, employee_id, location_id, brand, model, serial_number, property_number, status, year_acquired)
                VALUES (:type_id, :emp_id, :loc_id, :brand, :model, :serial, :prop, 'Active', :year)
            ");
            $stmt->execute([
                ':type_id' => $typeId,
                ':emp_id'  => $employeeId,
                ':loc_id'  => $locationId,
                ':brand'   => $brand,
                ':model'   => $model,
                ':serial'  => $serialNumber ?: null,
                ':prop'    => $propertyNumber ?: null,
                ':year'    => $yearAcquired ?: null,
            ]);
            $equipmentId = $db->lastInsertId();

            // Save specs from POST (keys prefixed with spec_)
            $specKeys = [];
            foreach ($_POST as $key => $value) {
                if (str_starts_with($key, 'spec_') && trim($value) !== '') {
                    $specKey = str_replace('_', ' ', substr($key, 5));
                    $specKeys[] = ['key' => $specKey, 'value' => trim($value)];
                }
            }

            if (!empty($specKeys)) {
                $specStmt = $db->prepare("INSERT INTO tbl_equipment_specs (equipment_id, spec_key, spec_value) VALUES (:eid, :sk, :sv)");
                foreach ($specKeys as $spec) {
                    $specStmt->execute([':eid' => $equipmentId, ':sk' => $spec['key'], ':sv' => $spec['value']]);
                }
            }

            // Initialize maintenance schedule
            $helper = new MaintenanceHelper($db);
            $helper->initScheduleByTypeId($typeId, $equipmentId);

            $db->commit();
            echo json_encode(['success' => true, 'message' => 'Equipment created and assigned.', 'equipment_id' => $equipmentId]);
            break;

        // Unassign equipment from employee
        case 'unassign':
            $equipmentId = filter_input(INPUT_POST, 'equipment_id', FILTER_VALIDATE_INT);

            if (!$equipmentId) {
                echo json_encode(['success' => false, 'message' => 'Missing equipment_id.']);
                exit;
            }

            $stmt = $db->prepare("UPDATE tbl_equipment SET employee_id = NULL WHERE equipment_id = :eid");
            $stmt->execute([':eid' => $equipmentId]);

            echo json_encode(['success' => true, 'message' => 'Equipment unassigned.']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    }
} catch (PDOException $e) {
    if ($db->inTransaction()) $db->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
