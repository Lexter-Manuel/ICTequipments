<?php


$divisions = $db->query(
    "SELECT location_id, location_name FROM location WHERE location_type_id = 1 AND is_deleted = '0' ORDER BY location_name"
)->fetchAll(PDO::FETCH_ASSOC);

$equipmentTypes = $db->query(
    "SELECT typeId, typeName FROM tbl_equipment_type_registry ORDER BY typeName"
)->fetchAll(PDO::FETCH_ASSOC);

$schedWhere  = " s.isActive = 1";
$schedParams = [];
if ($filterEqType !== '') {
    $schedWhere .= " AND s.equipmentType = :eqType";
    $schedParams[':eqType'] = $filterEqType;
}
if ($filterDivision !== '') {
    $schedWhere .= " AND EXISTS (
        SELECT 1 FROM tbl_employee emp
        INNER JOIN location loc ON emp.location_id = loc.location_id
        LEFT JOIN location parent ON loc.parent_location_id = parent.location_id
        LEFT JOIN location grandparent ON parent.parent_location_id = grandparent.location_id
        WHERE emp.employeeId = COALESCE(
            (SELECT su.employeeId FROM tbl_systemunit su WHERE su.systemunitId = s.equipmentId AND s.equipmentType = 1),
            (SELECT aio.employeeId FROM tbl_allinone aio WHERE aio.allinoneId = s.equipmentId AND s.equipmentType = 2),
            (SELECT mo.employeeId FROM tbl_monitor mo WHERE mo.monitorId = s.equipmentId AND s.equipmentType = 3),
            (SELECT pr.employeeId FROM tbl_printer pr WHERE pr.printerId = s.equipmentId AND s.equipmentType = 4),
            (SELECT oe.employeeId FROM tbl_otherequipment oe WHERE oe.otherEquipmentId = s.equipmentId AND s.equipmentType IN (5,6,7,8,9))
        )
        AND (loc.location_id = :divId_sched OR parent.location_id = :divId_sched2 OR grandparent.location_id = :divId_sched3)
    )";
    $schedParams[':divId_sched']  = $filterDivision;
    $schedParams[':divId_sched2'] = $filterDivision;
    $schedParams[':divId_sched3'] = $filterDivision;
}

$recWhere  = " 1=1";
$recParams = [];
if ($filterDateFrom !== '') {
    $recWhere .= " AND mr.maintenanceDate >= :dateFrom";
    $recParams[':dateFrom'] = $filterDateFrom;
}
if ($filterDateTo !== '') {
    $recWhere .= " AND mr.maintenanceDate <= :dateTo";
    $recParams[':dateTo'] = $filterDateTo;
}
if ($filterEqType !== '') {
    $recWhere .= " AND EXISTS (SELECT 1 FROM tbl_maintenance_schedule ss WHERE ss.scheduleId = mr.scheduleId AND ss.equipmentType = :eqTypeRec)";
    $recParams[':eqTypeRec'] = $filterEqType;
}
if ($filterDivision !== '') {
    $recWhere .= " AND EXISTS (
        SELECT 1 FROM tbl_maintenance_schedule ss
        INNER JOIN tbl_employee emp ON emp.employeeId = COALESCE(
            (SELECT su.employeeId FROM tbl_systemunit su WHERE su.systemunitId = ss.equipmentId AND ss.equipmentType = 1),
            (SELECT aio.employeeId FROM tbl_allinone aio WHERE aio.allinoneId = ss.equipmentId AND ss.equipmentType = 2),
            (SELECT mo.employeeId FROM tbl_monitor mo WHERE mo.monitorId = ss.equipmentId AND ss.equipmentType = 3),
            (SELECT pr.employeeId FROM tbl_printer pr WHERE pr.printerId = ss.equipmentId AND ss.equipmentType = 4),
            (SELECT oe.employeeId FROM tbl_otherequipment oe WHERE oe.otherEquipmentId = ss.equipmentId AND ss.equipmentType IN (5,6,7,8,9))
        )
        INNER JOIN location loc ON emp.location_id = loc.location_id
        LEFT JOIN location parent ON loc.parent_location_id = parent.location_id
        LEFT JOIN location grandparent ON parent.parent_location_id = grandparent.location_id
        WHERE ss.scheduleId = mr.scheduleId
        AND (loc.location_id = :divIdRec OR parent.location_id = :divIdRec2 OR grandparent.location_id = :divIdRec3)
    )";
    $recParams[':divIdRec']  = $filterDivision;
    $recParams[':divIdRec2'] = $filterDivision;
    $recParams[':divIdRec3'] = $filterDivision;
}

$stmt = $db->prepare("SELECT COUNT(*) FROM tbl_maintenance_schedule s WHERE $schedWhere");
$stmt->execute($schedParams);
$activeSchedules = (int) $stmt->fetchColumn();

$totalSchedules = (int) $db->query("SELECT COUNT(*) FROM tbl_maintenance_schedule")->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM tbl_maintenance_schedule s WHERE $schedWhere AND s.nextDueDate < CURDATE()");
$stmt->execute($schedParams);
$overdue = (int) $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM tbl_maintenance_schedule s WHERE $schedWhere AND s.nextDueDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
$stmt->execute($schedParams);
$dueSoon = (int) $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM tbl_maintenance_record mr WHERE $recWhere");
$stmt->execute($recParams);
$totalRecords = (int) $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM tbl_maintenance_record mr WHERE $recWhere AND MONTH(mr.maintenanceDate) = MONTH(CURDATE()) AND YEAR(mr.maintenanceDate) = YEAR(CURDATE())");
$stmt->execute($recParams);
$thisMonthRecords = (int) $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM tbl_maintenance_schedule s WHERE $schedWhere AND MONTH(s.nextDueDate) = MONTH(CURDATE()) AND YEAR(s.nextDueDate) = YEAR(CURDATE())");
$stmt->execute($schedParams);
$dueThisMonth = (int) $stmt->fetchColumn();

$stmt = $db->prepare("
    SELECT COUNT(DISTINCT ms.scheduleId)
    FROM tbl_maintenance_schedule ms
    INNER JOIN tbl_maintenance_record mr ON mr.scheduleId = ms.scheduleId
    WHERE ms.isActive = 1
      AND MONTH(ms.nextDueDate) = MONTH(CURDATE())
      AND YEAR(ms.nextDueDate) = YEAR(CURDATE())
      AND MONTH(mr.maintenanceDate) = MONTH(CURDATE())
      AND YEAR(mr.maintenanceDate) = YEAR(CURDATE())
");
$stmt->execute();
$completedThisMonth = (int) $stmt->fetchColumn();
$compliance = ($dueThisMonth > 0) ? round(($completedThisMonth / $dueThisMonth) * 100) : ($activeSchedules > 0 ? 100 : 0);

$stmt = $db->prepare("
    SELECT DATE_FORMAT(mr.maintenanceDate, '%Y-%m') as month_key,
           DATE_FORMAT(mr.maintenanceDate, '%b %Y') as label,
           COUNT(*) as cnt
    FROM tbl_maintenance_record mr
    WHERE mr.maintenanceDate >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) AND $recWhere
    GROUP BY month_key, label ORDER BY month_key
");
$stmt->execute($recParams);
$monthly = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("
    SELECT mt.templateName, COUNT(mr.recordId) as cnt
    FROM tbl_maintenance_record mr
    LEFT JOIN tbl_maintenance_template mt ON mt.templateId = mr.templateId
    WHERE $recWhere
    GROUP BY mt.templateId, mt.templateName ORDER BY cnt DESC
");
$stmt->execute($recParams);
$byType = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Condition summary ────────────────────────────────────────────────
$stmt = $db->prepare("
    SELECT conditionRating, COUNT(*) as cnt
    FROM (
        SELECT mr.conditionRating,
               ROW_NUMBER() OVER (PARTITION BY mr.scheduleId ORDER BY mr.maintenanceDate DESC) as rn
        FROM tbl_maintenance_record mr WHERE mr.conditionRating != '' AND $recWhere
    ) latest WHERE rn = 1 GROUP BY conditionRating ORDER BY FIELD(conditionRating, 'Excellent','Good','Fair','Poor')
");
$stmt->execute($recParams);
$conditionSummary = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Status summary ───────────────────────────────────────────────────
$stmt = $db->prepare("
    SELECT overallStatus, COUNT(*) as cnt
    FROM (
        SELECT mr.overallStatus,
               ROW_NUMBER() OVER (PARTITION BY mr.scheduleId ORDER BY mr.maintenanceDate DESC) as rn
        FROM tbl_maintenance_record mr WHERE mr.overallStatus != '' AND $recWhere
    ) latest WHERE rn = 1 GROUP BY overallStatus ORDER BY FIELD(overallStatus, 'Operational','For Replacement','Disposed')
");
$stmt->execute($recParams);
$statusSummary = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Overdue list (top 20) ────────────────────────────────────────────
$overdueExtraWhere = '';
$overdueParams = [];
if ($filterEqType !== '') {
    $overdueExtraWhere .= " AND v.equipmentType = :eqTypeOD";
    $overdueParams[':eqTypeOD'] = $filterEqType;
}
$stmt = $db->prepare("
    SELECT v.scheduleId, v.equipmentType as typeId, v.equipmentId, v.maintenanceFrequency,
           v.nextDueDate, v.days_overdue,
           etr.typeName as equipment_type,
           CASE
               WHEN etr.tableName = 'tbl_systemunit' THEN (SELECT CONCAT(systemUnitBrand, ' ', systemUnitCategory) FROM tbl_systemunit WHERE systemunitId = v.equipmentId)
               WHEN etr.tableName = 'tbl_monitor' THEN (SELECT CONCAT(monitorBrand, ' ', monitorSize) FROM tbl_monitor WHERE monitorId = v.equipmentId)
               WHEN etr.tableName = 'tbl_printer' THEN (SELECT CONCAT(printerBrand, ' ', printerModel) FROM tbl_printer WHERE printerId = v.equipmentId)
               WHEN etr.tableName = 'tbl_allinone' THEN (SELECT CONCAT(allinoneBrand, ' AIO') FROM tbl_allinone WHERE allinoneId = v.equipmentId)
               WHEN etr.tableName = 'tbl_otherequipment' THEN (SELECT CONCAT(brand, ' ', model) FROM tbl_otherequipment WHERE otherEquipmentId = v.equipmentId)
               ELSE CONCAT('Equipment #', v.equipmentId)
           END as equipment_name
    FROM view_overdue_maintenance v
    LEFT JOIN tbl_equipment_type_registry etr ON etr.typeId = v.equipmentType
    WHERE 1=1 $overdueExtraWhere
    ORDER BY v.days_overdue DESC LIMIT 20
");
$stmt->execute($overdueParams);
$overdueList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Technicians leaderboard (top 10) ─────────────────────────────────
$stmt = $db->prepare("
    SELECT mr.preparedBy, COUNT(*) as cnt,
           ROUND(AVG(CASE WHEN mr.conditionRating='Excellent' THEN 4 WHEN mr.conditionRating='Good' THEN 3 WHEN mr.conditionRating='Fair' THEN 2 WHEN mr.conditionRating='Poor' THEN 1 ELSE 0 END),1) as avg_rating
    FROM tbl_maintenance_record mr
    WHERE mr.preparedBy IS NOT NULL AND mr.preparedBy != '' AND $recWhere
    GROUP BY mr.preparedBy ORDER BY cnt DESC LIMIT 10
");
$stmt->execute($recParams);
$technicians = $stmt->fetchAll(PDO::FETCH_ASSOC);
