<?php
/**
 * AJAX endpoint for real-time dashboard data
 * Returns JSON with all dashboard metrics
 */
require_once '../config/database.php';
require_once '../config/session-check.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();
    $data = [];

    // ─── Equipment Counts ───
    $data['equipment'] = [
        'systemUnits'  => (int) $db->query("SELECT COUNT(*) FROM tbl_systemunit")->fetchColumn(),
        'monitors'     => (int) $db->query("SELECT COUNT(*) FROM tbl_monitor")->fetchColumn(),
        'printers'     => (int) $db->query("SELECT COUNT(*) FROM tbl_printer")->fetchColumn(),
        'allInOnes'    => (int) $db->query("SELECT COUNT(*) FROM tbl_allinone")->fetchColumn(),
        'otherEquip'   => (int) $db->query("SELECT COUNT(*) FROM tbl_otherequipment")->fetchColumn(),
    ];
    $data['equipment']['total'] = array_sum($data['equipment']);

    // Assigned equipment count (equipment that has an employeeId)
    $data['equipment']['assigned'] = (int) $db->query("
        SELECT (
            (SELECT COUNT(*) FROM tbl_systemunit WHERE employeeId IS NOT NULL) +
            (SELECT COUNT(*) FROM tbl_monitor WHERE employeeId IS NOT NULL) +
            (SELECT COUNT(*) FROM tbl_printer WHERE employeeId IS NOT NULL) +
            (SELECT COUNT(*) FROM tbl_allinone WHERE employeeId IS NOT NULL) +
            (SELECT COUNT(*) FROM tbl_otherequipment WHERE employeeId IS NOT NULL)
        ) AS total
    ")->fetchColumn();
    $data['equipment']['unassigned'] = $data['equipment']['total'] - $data['equipment']['assigned'];

    // ─── People & Org ───
    $data['employees']  = (int) $db->query("SELECT COUNT(*) FROM tbl_employee WHERE is_active = 1")->fetchColumn();
    $data['software']   = (int) $db->query("SELECT COUNT(*) FROM tbl_software")->fetchColumn();
    $data['divisions']  = (int) $db->query("SELECT COUNT(*) FROM location WHERE location_type_id = 1 AND is_deleted = '0'")->fetchColumn();
    $data['sections']   = (int) $db->query("SELECT COUNT(*) FROM location WHERE location_type_id = 2 AND is_deleted = '0'")->fetchColumn();
    $data['units']      = (int) $db->query("SELECT COUNT(*) FROM location WHERE location_type_id = 3 AND is_deleted = '0'")->fetchColumn();

    // ─── Maintenance Stats ───
    $data['maintenance'] = [];

    // Active schedules
    $data['maintenance']['activeSchedules'] = (int) $db->query("
        SELECT COUNT(*) FROM tbl_maintenance_schedule WHERE isActive = 1
    ")->fetchColumn();

    // Overdue
    $data['maintenance']['overdue'] = (int) $db->query("
        SELECT COUNT(*) FROM tbl_maintenance_schedule 
        WHERE isActive = 1 AND nextDueDate < CURDATE()
    ")->fetchColumn();

    // Due within 7 days
    $data['maintenance']['dueSoon'] = (int) $db->query("
        SELECT COUNT(*) FROM tbl_maintenance_schedule 
        WHERE isActive = 1 AND nextDueDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ")->fetchColumn();

    // Due within 30 days
    $data['maintenance']['dueThisMonth'] = (int) $db->query("
        SELECT COUNT(*) FROM tbl_maintenance_schedule 
        WHERE isActive = 1 AND nextDueDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    ")->fetchColumn();

    // Completed this month
    $data['maintenance']['completedThisMonth'] = (int) $db->query("
        SELECT COUNT(*) FROM tbl_maintenance_record 
        WHERE MONTH(maintenanceDate) = MONTH(CURDATE()) AND YEAR(maintenanceDate) = YEAR(CURDATE())
    ")->fetchColumn();

    // Total completed
    $data['maintenance']['totalCompleted'] = (int) $db->query("
        SELECT COUNT(*) FROM tbl_maintenance_record
    ")->fetchColumn();

    // Compliance rate (scheduled and completed vs total active schedules)
    $totalActive = $data['maintenance']['activeSchedules'];
    $overdueCount = $data['maintenance']['overdue'];
    $data['maintenance']['complianceRate'] = $totalActive > 0
        ? round((($totalActive - $overdueCount) / $totalActive) * 100, 1)
        : 100;

    // Condition breakdown from latest maintenance records
    $conditionStmt = $db->query("
        SELECT conditionRating, COUNT(*) as cnt 
        FROM (
            SELECT mr.conditionRating,
                   ROW_NUMBER() OVER (PARTITION BY mr.scheduleId ORDER BY mr.maintenanceDate DESC) as rn
            FROM tbl_maintenance_record mr
            WHERE mr.conditionRating != ''
        ) latest
        WHERE rn = 1
        GROUP BY conditionRating
    ");
    $data['maintenance']['conditionBreakdown'] = [];
    while ($row = $conditionStmt->fetch(PDO::FETCH_ASSOC)) {
        $data['maintenance']['conditionBreakdown'][$row['conditionRating']] = (int) $row['cnt'];
    }

    // Overall status breakdown from latest records
    $statusStmt = $db->query("
        SELECT overallStatus, COUNT(*) as cnt 
        FROM (
            SELECT mr.overallStatus,
                   ROW_NUMBER() OVER (PARTITION BY mr.scheduleId ORDER BY mr.maintenanceDate DESC) as rn
            FROM tbl_maintenance_record mr
            WHERE mr.overallStatus != ''
        ) latest
        WHERE rn = 1
        GROUP BY overallStatus
    ");
    $data['maintenance']['statusBreakdown'] = [];
    while ($row = $statusStmt->fetch(PDO::FETCH_ASSOC)) {
        $data['maintenance']['statusBreakdown'][$row['overallStatus']] = (int) $row['cnt'];
    }

    // ─── Alerts / Warnings ───
    $alerts = [];

    // Overdue maintenance details
    $overdueStmt = $db->query("
        SELECT ms.scheduleId, ms.equipmentType, ms.equipmentId, ms.nextDueDate,
               DATEDIFF(CURDATE(), ms.nextDueDate) as days_overdue,
               etr.typeName
        FROM tbl_maintenance_schedule ms
        LEFT JOIN tbl_equipment_type_registry etr ON ms.equipmentType = etr.typeId
        WHERE ms.isActive = 1 AND ms.nextDueDate < CURDATE()
        ORDER BY days_overdue DESC
        LIMIT 10
    ");
    $data['alerts']['overdue'] = $overdueStmt->fetchAll(PDO::FETCH_ASSOC);

    // Due soon details
    $dueSoonStmt = $db->query("
        SELECT ms.scheduleId, ms.equipmentType, ms.equipmentId, ms.nextDueDate,
               DATEDIFF(ms.nextDueDate, CURDATE()) as days_until_due,
               etr.typeName
        FROM tbl_maintenance_schedule ms
        LEFT JOIN tbl_equipment_type_registry etr ON ms.equipmentType = etr.typeId
        WHERE ms.isActive = 1 AND ms.nextDueDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        ORDER BY ms.nextDueDate ASC
        LIMIT 10
    ");
    $data['alerts']['dueSoon'] = $dueSoonStmt->fetchAll(PDO::FETCH_ASSOC);

    // Equipment marked "For Replacement" or "Poor" condition
    $problemStmt = $db->query("
        SELECT mr.scheduleId, mr.equipmentTypeId, mr.equipmentId, mr.overallStatus, 
               mr.conditionRating, mr.maintenanceDate, mr.remarks,
               etr.typeName
        FROM tbl_maintenance_record mr
        LEFT JOIN tbl_equipment_type_registry etr ON mr.equipmentTypeId = etr.typeId
        WHERE (mr.overallStatus = 'For Replacement' OR mr.overallStatus = 'Disposed' OR mr.conditionRating = 'Poor')
        AND mr.maintenanceDate = (
            SELECT MAX(mr2.maintenanceDate) FROM tbl_maintenance_record mr2 
            WHERE mr2.scheduleId = mr.scheduleId
        )
        ORDER BY mr.maintenanceDate DESC
        LIMIT 10
    ");
    $data['alerts']['problemEquipment'] = $problemStmt->fetchAll(PDO::FETCH_ASSOC);

    // Expiring software licenses (within 90 days)
    $licenseStmt = $db->query("
        SELECT s.softwareId, s.licenseSoftware, s.licenseType, s.expiryDate,
               DATEDIFF(s.expiryDate, CURDATE()) as days_until_expiry,
               CONCAT(e.firstName, ' ', e.lastName) as assignedTo
        FROM tbl_software s
        LEFT JOIN tbl_employee e ON s.employeeId = e.employeeId
        WHERE s.expiryDate IS NOT NULL 
          AND s.expiryDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)
        ORDER BY s.expiryDate ASC
    ");
    $data['alerts']['expiringLicenses'] = $licenseStmt->fetchAll(PDO::FETCH_ASSOC);

    // Expired software licenses
    $expiredStmt = $db->query("
        SELECT s.softwareId, s.licenseSoftware, s.licenseType, s.expiryDate,
               DATEDIFF(CURDATE(), s.expiryDate) as days_expired,
               CONCAT(e.firstName, ' ', e.lastName) as assignedTo
        FROM tbl_software s
        LEFT JOIN tbl_employee e ON s.employeeId = e.employeeId
        WHERE s.expiryDate IS NOT NULL AND s.expiryDate < CURDATE()
        ORDER BY s.expiryDate DESC
    ");
    $data['alerts']['expiredLicenses'] = $expiredStmt->fetchAll(PDO::FETCH_ASSOC);

    // ─── Recent Activity ───
    $activityStmt = $db->query("
        SELECT al.action, al.module, al.description, al.timestamp, al.email, al.success
        FROM activity_log al
        ORDER BY al.timestamp DESC
        LIMIT 15
    ");
    $data['recentActivity'] = $activityStmt->fetchAll(PDO::FETCH_ASSOC);

    // ─── Recent Maintenance ───
    $recentMaintStmt = $db->query("
        SELECT mr.recordId, mr.maintenanceDate, mr.overallStatus, mr.conditionRating,
               mr.preparedBy, mr.remarks, etr.typeName
        FROM tbl_maintenance_record mr
        LEFT JOIN tbl_equipment_type_registry etr ON mr.equipmentTypeId = etr.typeId
        ORDER BY mr.maintenanceDate DESC
        LIMIT 8
    ");
    $data['recentMaintenance'] = $recentMaintStmt->fetchAll(PDO::FETCH_ASSOC);

    // ─── Equipment by Division ───
    $divStmt = $db->query("
        SELECT l.location_name as division,
               COUNT(DISTINCT e.employeeId) as employee_count,
               COUNT(DISTINCT su.systemunitId) as system_units,
               COUNT(DISTINCT mo.monitorId) as monitors,
               COUNT(DISTINCT pr.printerId) as printers
        FROM location l
        LEFT JOIN location sec ON sec.parent_location_id = l.location_id
        LEFT JOIN location unit ON unit.parent_location_id = sec.location_id
        LEFT JOIN tbl_employee e ON (e.location_id = l.location_id OR e.location_id = sec.location_id OR e.location_id = unit.location_id) AND e.is_active = 1
        LEFT JOIN tbl_systemunit su ON su.employeeId = e.employeeId
        LEFT JOIN tbl_monitor mo ON mo.employeeId = e.employeeId
        LEFT JOIN tbl_printer pr ON pr.employeeId = e.employeeId
        WHERE l.location_type_id = 1 AND l.is_deleted = '0'
        GROUP BY l.location_id, l.location_name
    ");
    $data['equipmentByDivision'] = $divStmt->fetchAll(PDO::FETCH_ASSOC);

    $data['timestamp'] = date('Y-m-d H:i:s');

    echo json_encode(['success' => true, 'data' => $data]);

} catch (PDOException $e) {
    error_log("Dashboard API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
} catch (Exception $e) {
    error_log("Dashboard API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
