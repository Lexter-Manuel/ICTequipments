<?php
/**
 * ajax/add_equipment_to_employee.php
 * Assigns existing (unassigned) equipment to an employee,
 * or creates a NEW record and assigns it directly.
 */
require_once '../config/session-guard.php';

if (session_status() === PHP_SESSION_NONE) session_start();

require_once '../config/database.php';
require_once '../includes/assignmentHistoryHelper.php';
header('Content-Type: application/json');

$db   = Database::getInstance()->getConnection();
$post = $_POST;

$employeeId    = filter_var($post['employee_id'] ?? 0, FILTER_VALIDATE_INT);
$equipmentType = $post['equipment_type'] ?? '';
$mode          = $post['mode'] ?? 'existing';

if (!$employeeId || !$equipmentType) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

// Verify employee exists
$empCheck = $db->prepare("SELECT employeeId FROM tbl_employee WHERE employeeId = :id");
$empCheck->execute([':id' => $employeeId]);
if (!$empCheck->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Employee not found.']);
    exit;
}

try {
    $db->beginTransaction(); // Use transaction for multi-table inserts

    if ($mode === 'existing') {
        // ── ASSIGN EXISTING UNASSIGNED EQUIPMENT ──────────────────────────────
        $equipmentId = filter_var($post['equipment_id'] ?? 0, FILTER_VALIDATE_INT);
        if (!$equipmentId) {
            echo json_encode(['success' => false, 'message' => 'No equipment selected.']);
            exit;
        }

        // Map equipment string to new registry type_id
        $typeMapping = [
            'systemunit' => 1,
            'allinone'   => 2,
            'monitor'    => 3,
            'printer'    => 4
        ];

        if (isset($typeMapping[$equipmentType])) {
            $stmt = $db->prepare("UPDATE tbl_equipment SET employee_id = :eid, location_id = NULL, status = 'In Use' WHERE equipment_id = :id AND employee_id IS NULL");
            $stmt->execute([':eid' => $employeeId, ':id' => $equipmentId]);
            if ($stmt->rowCount() > 0) {
                recordAssignmentChange($db, $equipmentId, null, $employeeId);
            }
        } elseif ($equipmentType === 'otherequipment') {
            $stmt = $db->prepare("UPDATE tbl_otherequipment SET employeeId = :eid, status = 'In Use' WHERE otherEquipmentId = :id AND employeeId IS NULL");
            $stmt->execute([':eid' => $employeeId, ':id' => $equipmentId]);
        } elseif ($equipmentType === 'software') {
            $stmt = $db->prepare("UPDATE tbl_software SET employeeId = :eid WHERE softwareId = :id AND employeeId IS NULL");
            $stmt->execute([':eid' => $employeeId, ':id' => $equipmentId]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Unknown equipment type.']);
            exit;
        }

        if ($stmt->rowCount() === 0) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Equipment not found or already assigned.']);
            exit;
        }

        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Equipment assigned successfully.']);

    } elseif ($mode === 'new') {
        // ── CREATE & ASSIGN NEW EQUIPMENT ─────────────────────────────────────
        switch ($equipmentType) {

            case 'systemunit':
                $stmt = $db->prepare("
                    INSERT INTO tbl_equipment
                        (type_id, employee_id, brand, model, serial_number, year_acquired, status)
                    VALUES
                        (1, :eid, :brand, :model, :serial, :year, 'In Use')
                ");
                $stmt->execute([
                    ':eid'    => $employeeId,
                    ':brand'  => $post['systemUnitBrand'] ?? '',
                    ':model'  => $post['systemUnitCategory'] ?? 'Pre-Built',
                    ':serial' => $post['systemUnitSerial'] ?? '',
                    ':year'   => $post['yearAcquired'] ?? date('Y'),
                ]);
                $newId = $db->lastInsertId();

                // Insert dynamic specs
                $specs = [
                    'Processor' => $post['specificationProcessor'] ?? '',
                    'Memory'    => $post['specificationMemory'] ?? '',
                    'GPU'       => $post['specificationGPU'] ?? '',
                    'Storage'   => $post['specificationStorage'] ?? ''
                ];
                $specStmt = $db->prepare("INSERT INTO tbl_equipment_specs (equipment_id, spec_key, spec_value) VALUES (?, ?, ?)");
                foreach ($specs as $k => $v) {
                    if ($v !== '') $specStmt->execute([$newId, $k, $v]);
                }
                recordAssignmentChange($db, $newId, null, $employeeId);
                break;

            case 'allinone':
                $stmt = $db->prepare("
                    INSERT INTO tbl_equipment
                        (type_id, employee_id, brand, serial_number, year_acquired, status)
                    VALUES
                        (2, :eid, :brand, :serial, :year, 'In Use')
                ");
                $stmt->execute([
                    ':eid'    => $employeeId,
                    ':brand'  => $post['allinoneBrand'] ?? '',
                    ':serial' => $post['allinoneSerial'] ?? '',
                    ':year'   => $post['yearAcquired'] ?? date('Y'),
                ]);
                $newId = $db->lastInsertId();

                $specs = [
                    'Processor' => $post['specificationProcessor'] ?? '',
                    'Memory'    => $post['specificationMemory'] ?? '',
                    'GPU'       => $post['specificationGPU'] ?? '',
                    'Storage'   => $post['specificationStorage'] ?? ''
                ];
                $specStmt = $db->prepare("INSERT INTO tbl_equipment_specs (equipment_id, spec_key, spec_value) VALUES (?, ?, ?)");
                foreach ($specs as $k => $v) {
                    if ($v !== '') $specStmt->execute([$newId, $k, $v]);
                }
                recordAssignmentChange($db, $newId, null, $employeeId);
                break;

            case 'monitor':
                $stmt = $db->prepare("
                    INSERT INTO tbl_equipment
                        (type_id, employee_id, brand, serial_number, year_acquired, status)
                    VALUES
                        (3, :eid, :brand, :serial, :year, 'In Use')
                ");
                $stmt->execute([
                    ':eid'    => $employeeId,
                    ':brand'  => $post['monitorBrand'] ?? '',
                    ':serial' => $post['monitorSerial'] ?? '',
                    ':year'   => $post['yearAcquired'] ?? date('Y'),
                ]);
                $newId = $db->lastInsertId();

                if (!empty($post['monitorSize'])) {
                    $db->prepare("INSERT INTO tbl_equipment_specs (equipment_id, spec_key, spec_value) VALUES (?, 'Monitor Size', ?)")
                       ->execute([$newId, $post['monitorSize']]);
                }
                recordAssignmentChange($db, $newId, null, $employeeId);
                break;

            case 'printer':
                $stmt = $db->prepare("
                    INSERT INTO tbl_equipment
                        (type_id, employee_id, brand, model, serial_number, year_acquired, status)
                    VALUES
                        (4, :eid, :brand, :model, :serial, :year, 'In Use')
                ");
                $stmt->execute([
                    ':eid'    => $employeeId,
                    ':brand'  => $post['printerBrand'] ?? '',
                    ':model'  => $post['printerModel'] ?? '',
                    ':serial' => $post['printerSerial'] ?? '',
                    ':year'   => $post['yearAcquired'] ?? date('Y'),
                ]);
                $printerId = $db->lastInsertId();
                recordAssignmentChange($db, $printerId, null, $employeeId);
                break;

            case 'otherequipment':
                $empLoc = $db->prepare("SELECT location_id FROM tbl_employee WHERE employeeId = :id");
                $empLoc->execute([':id' => $employeeId]);
                $locRow    = $empLoc->fetch(PDO::FETCH_ASSOC);
                $locationId = $locRow['location_id'] ?? 0;

                $stmt = $db->prepare("
                    INSERT INTO tbl_otherequipment
                        (equipmentType, brand, model, serialNumber, yearAcquired, location_id, employeeId, status)
                    VALUES
                        (:type, :brand, :model, :serial, :year, :loc, :eid, 'In Use')
                ");
                $stmt->execute([
                    ':type'   => $post['equipmentType']  ?? 'Other',
                    ':brand'  => $post['brand']          ?? '',
                    ':model'  => $post['model']          ?? '',
                    ':serial' => $post['serialNumber']   ?? '',
                    ':year'   => $post['yearAcquired']   ?? date('Y'),
                    ':loc'    => $locationId,
                    ':eid'    => $employeeId,
                ]);
                break;

            case 'software':
                $stmt = $db->prepare("
                    INSERT INTO tbl_software
                        (licenseSoftware, licenseDetails, licenseType, expiryDate, email, employeeId)
                    VALUES
                        (:software, :details, :type, :expiry, :email, :eid)
                ");
                $expiryDate = !empty($post['expiryDate']) ? $post['expiryDate'] : null;
                $stmt->execute([
                    ':software' => $post['licenseSoftware'] ?? '',
                    ':details'  => $post['licenseDetails']  ?? '',
                    ':type'     => $post['licenseType']     ?? 'Perpetual',
                    ':expiry'   => $expiryDate,
                    ':email'    => $post['email']           ?? '',
                    ':eid'      => $employeeId,
                ]);
                break;

            default:
                $db->rollBack();
                echo json_encode(['success' => false, 'message' => 'Unknown equipment type.']);
                exit;
        }

        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Equipment added and assigned successfully.']);

    } else {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Invalid mode.']);
    }

} catch (PDOException $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}