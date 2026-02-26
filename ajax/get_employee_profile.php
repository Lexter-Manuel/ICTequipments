<?php
/**
 * ajax/get_employee_profile.php
 * Returns full employee profile including equipment and software assigned to them.
 */

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
                (SELECT COUNT(*) FROM tbl_systemunit     WHERE employeeId = e.employeeId) +
                (SELECT COUNT(*) FROM tbl_allinone       WHERE employeeId = e.employeeId) +
                (SELECT COUNT(*) FROM tbl_monitor        WHERE employeeId = e.employeeId) +
                (SELECT COUNT(*) FROM tbl_printer        WHERE employeeId = e.employeeId) +
                (SELECT COUNT(*) FROM tbl_otherequipment WHERE employeeId = e.employeeId) +
                (SELECT COUNT(*) FROM tbl_software       WHERE employeeId = e.employeeId)
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

    // Derive is_active from equipment count — no extra column needed
    $employee['is_active'] = ($employee['equipment_count'] ?? 0) > 0 ? 1 : 0;

    // ---- System Units ----
    $suStmt = $db->prepare("
        SELECT systemunitId, systemUnitCategory, systemUnitBrand,
               specificationProcessor, specificationMemory,
               specificationGPU, specificationStorage,
               systemUnitSerial, yearAcquired
        FROM tbl_systemunit
        WHERE employeeId = :id
    ");
    $suStmt->execute([':id' => $employeeId]);
    $systemUnits = $suStmt->fetchAll(PDO::FETCH_ASSOC);

    // ---- All-in-One ----
    $aioStmt = $db->prepare("
        SELECT allinoneId, allinoneBrand,
               specificationProcessor, specificationMemory,
               specificationGPU, specificationStorage
        FROM tbl_allinone
        WHERE employeeId = :id
    ");
    $aioStmt->execute([':id' => $employeeId]);
    $allinones = $aioStmt->fetchAll(PDO::FETCH_ASSOC);

    // ---- Monitors ----
    $monStmt = $db->prepare("
        SELECT monitorId, monitorBrand, monitorSize, monitorSerial, yearAcquired
        FROM tbl_monitor
        WHERE employeeId = :id
    ");
    $monStmt->execute([':id' => $employeeId]);
    $monitors = $monStmt->fetchAll(PDO::FETCH_ASSOC);

    // ---- Printers ----
    $prtStmt = $db->prepare("
        SELECT printerId, printerBrand, printerModel, printerSerial, yearAcquired
        FROM tbl_printer
        WHERE employeeId = :id
    ");
    $prtStmt->execute([':id' => $employeeId]);
    $printers = $prtStmt->fetchAll(PDO::FETCH_ASSOC);

    // ---- Other Equipment (including Laptop) ----
    $otherStmt = $db->prepare("
        SELECT otherEquipmentId, equipmentType, brand, model, serialNumber, yearAcquired
        FROM tbl_otherequipment
        WHERE employeeId = :id
    ");
    $otherStmt->execute([':id' => $employeeId]);
    $other = $otherStmt->fetchAll(PDO::FETCH_ASSOC);

    // ---- Software ----
    $swStmt = $db->prepare("
        SELECT softwareId, licenseSoftware, licenseDetails, licenseType, expiryDate, email
        FROM tbl_software
        WHERE employeeId = :id
    ");
    $swStmt->execute([':id' => $employeeId]);
    $software = $swStmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate software status
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