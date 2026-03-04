<?php
/**
 * ajax/get_employee_profile.php
 * Returns full employee profile including equipment and software assigned to them.
 * Updated for unified tbl_equipment schema.
 */
require_once '../config/session-guard.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
header('Content-Type: application/json');

$db = Database::getInstance()->getConnection();

$employeeId = filter_input(INPUT_GET, 'employee_id', FILTER_VALIDATE_INT);

if (!$employeeId) {
    echo json_encode(['success' => false, 'message' => 'Invalid employee ID.']);
    exit;
}

try {
    // ---- Employee info + location ----
    $empStmt = $db->prepare("
        SELECT 
            e.*,
            l.location_name,
            l.location_type_id,
            lt.name AS location_type_name,
            parent_loc.location_name AS parent_location_name,
            grandparent_loc.location_name AS grandparent_location_name,
            (
                (SELECT COUNT(*) FROM tbl_equipment WHERE employee_id = e.employeeId AND is_archived = 0) +
                (SELECT COUNT(*) FROM tbl_software  WHERE employeeId = e.employeeId)
            ) AS equipment_count
        FROM tbl_employee e
        LEFT JOIN location l            ON e.location_id = l.location_id
        LEFT JOIN location_type lt      ON l.location_type_id = lt.id
        LEFT JOIN location parent_loc   ON l.parent_location_id = parent_loc.location_id
        LEFT JOIN location grandparent_loc ON parent_loc.parent_location_id = grandparent_loc.location_id
        WHERE e.employeeId = :id
    ");
    $empStmt->execute([':id' => $employeeId]);
    $employee = $empStmt->fetch(PDO::FETCH_ASSOC);

    if (!$employee) {
        echo json_encode(['success' => false, 'message' => 'Employee not found.']);
        exit;
    }

    $employee['is_active'] = ($employee['equipment_count'] ?? 0) > 0 ? 1 : 0;

    // ---- All equipment (unified) ----
    $eqStmt = $db->prepare("
        SELECT eq.equipment_id, eq.type_id, eq.brand, eq.model, eq.serial_number, 
               eq.property_number, eq.status, eq.year_acquired, eq.acquisition_date,
               r.typeName, r.context
        FROM tbl_equipment eq
        INNER JOIN tbl_equipment_type_registry r ON eq.type_id = r.typeId
        WHERE eq.employee_id = :id AND eq.is_archived = 0
        ORDER BY r.typeName, eq.equipment_id
    ");
    $eqStmt->execute([':id' => $employeeId]);
    $allEquipment = $eqStmt->fetchAll(PDO::FETCH_ASSOC);

    // Bulk load specs
    $equipmentIds = array_column($allEquipment, 'equipment_id');
    $specsMap = [];
    if (!empty($equipmentIds)) {
        $placeholders = implode(',', array_fill(0, count($equipmentIds), '?'));
        $specStmt = $db->prepare("SELECT equipment_id, spec_key, spec_value FROM tbl_equipment_specs WHERE equipment_id IN ($placeholders)");
        $specStmt->execute($equipmentIds);
        while ($row = $specStmt->fetch(PDO::FETCH_ASSOC)) {
            $specsMap[$row['equipment_id']][$row['spec_key']] = $row['spec_value'];
        }
    }

    // Organize by type for backward compatibility
    $systemUnits = [];
    $allinones   = [];
    $monitors    = [];
    $printers    = [];
    $other       = [];
    $equipment   = [];

    foreach ($allEquipment as $eq) {
        $specs = $specsMap[$eq['equipment_id']] ?? [];
        $item = array_merge($eq, ['specs' => $specs]);
        $equipment[] = $item;

        switch ($eq['typeName']) {
            case 'System Unit':
                $systemUnits[] = [
                    'systemunitId'           => $eq['equipment_id'],
                    'systemUnitCategory'     => $specs['Category'] ?? 'Pre-Built',
                    'systemUnitBrand'        => $eq['brand'],
                    'specificationProcessor' => $specs['Processor'] ?? '',
                    'specificationMemory'    => $specs['Memory'] ?? '',
                    'specificationGPU'       => $specs['GPU'] ?? '',
                    'specificationStorage'   => $specs['Storage'] ?? '',
                    'systemUnitSerial'       => $eq['serial_number'],
                    'yearAcquired'           => $eq['year_acquired'],
                ];
                break;
            case 'All-in-One':
                $allinones[] = [
                    'allinoneId'             => $eq['equipment_id'],
                    'allinoneBrand'          => $eq['brand'],
                    'allinoneSerial'         => $eq['serial_number'],
                    'specificationProcessor' => $specs['Processor'] ?? '',
                    'specificationMemory'    => $specs['Memory'] ?? '',
                    'specificationGPU'       => $specs['GPU'] ?? '',
                    'specificationStorage'   => $specs['Storage'] ?? '',
                ];
                break;
            case 'Monitor':
                $monitors[] = [
                    'monitorId'    => $eq['equipment_id'],
                    'monitorBrand' => $eq['brand'],
                    'monitorSize'  => $specs['Monitor Size'] ?? '',
                    'monitorSerial'=> $eq['serial_number'],
                    'yearAcquired' => $eq['year_acquired'],
                ];
                break;
            case 'Printer':
                $printers[] = [
                    'printerId'     => $eq['equipment_id'],
                    'printerBrand'  => $eq['brand'],
                    'printerModel'  => $eq['model'],
                    'printerSerial' => $eq['serial_number'],
                    'yearAcquired'  => $eq['year_acquired'],
                ];
                break;
            default:
                $other[] = [
                    'otherEquipmentId' => $eq['equipment_id'],
                    'equipmentType'    => $eq['typeName'],
                    'brand'            => $eq['brand'],
                    'model'            => $eq['model'],
                    'serialNumber'     => $eq['serial_number'],
                    'yearAcquired'     => $eq['year_acquired'],
                ];
                break;
        }
    }

    // ---- Software ----
    $swStmt = $db->prepare("
        SELECT softwareId, licenseSoftware, licenseDetails, licenseType, expiryDate, email
        FROM tbl_software
        WHERE employeeId = :id
    ");
    $swStmt->execute([':id' => $employeeId]);
    $software = $swStmt->fetchAll(PDO::FETCH_ASSOC);

    $today = new DateTime();
    foreach ($software as &$sw) {
        if ($sw['expiryDate']) {
            $exp = new DateTime($sw['expiryDate']);
            $interval = $today->diff($exp);
            $days = $interval->invert ? -$interval->days : $interval->days;
            $sw['daysUntilExpiry'] = $days;
            if ($days < 0)       $sw['status'] = 'Expired';
            elseif ($days <= 30) $sw['status'] = 'Expiring Soon';
            else                 $sw['status'] = 'Active';
        } else {
            $sw['status'] = 'Active';
            $sw['daysUntilExpiry'] = null;
        }
    }
    unset($sw);

    echo json_encode([
        'success'     => true,
        'employee'    => $employee,
        'equipment'   => $equipment,
        'systemUnits' => $systemUnits,
        'allinones'   => $allinones,
        'monitors'    => $monitors,
        'printers'    => $printers,
        'other'       => $other,
        'software'    => $software,
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
