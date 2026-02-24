<?php
/**
 * Equipment Summary — Data Queries
 *
 * Expects the following variables to be defined before including this file:
 *   $db             — PDO connection
 *   $filterDivision — string (location_id or '')
 *   $filterEqType   — string (typeId or '')
 *   $filterYear     — string (year or '')
 *
 * After inclusion the following variables are available:
 *   $divisions, $yearOptions, $equipmentTypes,
 *   $showSU, $showMO, $showPR, $showAIO, $showOTH,
 *   $suCount, $moCount, $prCount, $aioCount, $othCount, $total,
 *   $assigned, $unassigned,
 *   $byDivision, $byYear,
 *   $conditionSummary, $statusSummary,
 *   $softTotal, $softExpired, $softExpiring, $softPerpetual
 */

// ── Dropdown options ─────────────────────────────────────────────────
$divisions = $db->query(
    "SELECT location_id, location_name FROM location WHERE location_type_id = 1 AND is_deleted = '0' ORDER BY location_name"
)->fetchAll(PDO::FETCH_ASSOC);

$yearOptions = $db->query("
    SELECT DISTINCT year_acquired FROM (
        SELECT yearAcquired as year_acquired FROM tbl_systemunit WHERE yearAcquired IS NOT NULL
        UNION SELECT yearAcquired FROM tbl_monitor WHERE yearAcquired IS NOT NULL
        UNION SELECT yearAcquired FROM tbl_printer WHERE yearAcquired IS NOT NULL
        UNION SELECT yearAcquired FROM tbl_otherequipment WHERE yearAcquired IS NOT NULL
    ) yrs ORDER BY year_acquired DESC
")->fetchAll(PDO::FETCH_COLUMN);

$equipmentTypes = $db->query(
    "SELECT typeId, typeName FROM tbl_equipment_type_registry ORDER BY typeName"
)->fetchAll(PDO::FETCH_ASSOC);

// ── Division-filter helper ───────────────────────────────────────────
if (!function_exists('buildDivFilter')) {
    function buildDivFilter($tableAlias, $empFk, $divId) {
        if ($divId === '') return '';
        return " AND EXISTS (
            SELECT 1 FROM tbl_employee emp
            INNER JOIN location loc ON emp.location_id = loc.location_id
            LEFT JOIN location parent ON loc.parent_location_id = parent.location_id
            LEFT JOIN location grandparent ON parent.parent_location_id = grandparent.location_id
            WHERE emp.employeeId = {$tableAlias}.employeeId
            AND (loc.location_id = {$divId} OR parent.location_id = {$divId} OR grandparent.location_id = {$divId})
        )";
    }
}

$divFilterSU  = $filterDivision !== '' ? buildDivFilter('su',  'employeeId', intval($filterDivision)) : '';
$divFilterMO  = $filterDivision !== '' ? buildDivFilter('mo',  'employeeId', intval($filterDivision)) : '';
$divFilterPR  = $filterDivision !== '' ? buildDivFilter('pr',  'employeeId', intval($filterDivision)) : '';
$divFilterAIO = $filterDivision !== '' ? buildDivFilter('aio', 'employeeId', intval($filterDivision)) : '';
$divFilterOTH = $filterDivision !== '' ? buildDivFilter('oth', 'employeeId', intval($filterDivision)) : '';

$yearFilterSU  = $filterYear !== '' ? " AND su.yearAcquired = "  . intval($filterYear) : '';
$yearFilterMO  = $filterYear !== '' ? " AND mo.yearAcquired = "  . intval($filterYear) : '';
$yearFilterPR  = $filterYear !== '' ? " AND pr.yearAcquired = "  . intval($filterYear) : '';
$yearFilterOTH = $filterYear !== '' ? " AND oth.yearAcquired = " . intval($filterYear) : '';

// ── Equipment-type visibility ────────────────────────────────────────
$showSU  = ($filterEqType === '' || $filterEqType === '1');
$showMO  = ($filterEqType === '' || $filterEqType === '3');
$showPR  = ($filterEqType === '' || $filterEqType === '4');
$showAIO = ($filterEqType === '' || $filterEqType === '2');
$showOTH = ($filterEqType === '' || in_array($filterEqType, ['5','6','7','8','9']));

// ── Counts per type ──────────────────────────────────────────────────
$suCount  = $showSU  ? (int) $db->query("SELECT COUNT(*) FROM tbl_systemunit su WHERE 1=1 $divFilterSU $yearFilterSU")->fetchColumn()       : 0;
$moCount  = $showMO  ? (int) $db->query("SELECT COUNT(*) FROM tbl_monitor mo WHERE 1=1 $divFilterMO $yearFilterMO")->fetchColumn()           : 0;
$prCount  = $showPR  ? (int) $db->query("SELECT COUNT(*) FROM tbl_printer pr WHERE 1=1 $divFilterPR $yearFilterPR")->fetchColumn()           : 0;
$aioCount = $showAIO ? (int) $db->query("SELECT COUNT(*) FROM tbl_allinone aio WHERE 1=1 $divFilterAIO")->fetchColumn()                     : 0;
$othCount = $showOTH ? (int) $db->query("SELECT COUNT(*) FROM tbl_otherequipment oth WHERE 1=1 $divFilterOTH $yearFilterOTH")->fetchColumn() : 0;
$total    = $suCount + $moCount + $prCount + $aioCount + $othCount;

// ── Assigned / Unassigned ────────────────────────────────────────────
$assignedParts = [];
if ($showSU)  $assignedParts[] = "(SELECT COUNT(*) FROM tbl_systemunit su WHERE su.employeeId IS NOT NULL $divFilterSU $yearFilterSU)";
if ($showMO)  $assignedParts[] = "(SELECT COUNT(*) FROM tbl_monitor mo WHERE mo.employeeId IS NOT NULL $divFilterMO $yearFilterMO)";
if ($showPR)  $assignedParts[] = "(SELECT COUNT(*) FROM tbl_printer pr WHERE pr.employeeId IS NOT NULL $divFilterPR $yearFilterPR)";
if ($showAIO) $assignedParts[] = "(SELECT COUNT(*) FROM tbl_allinone aio WHERE aio.employeeId IS NOT NULL $divFilterAIO)";
if ($showOTH) $assignedParts[] = "(SELECT COUNT(*) FROM tbl_otherequipment oth WHERE oth.employeeId IS NOT NULL $divFilterOTH $yearFilterOTH)";
$assigned   = !empty($assignedParts) ? (int) $db->query("SELECT (" . implode(' + ', $assignedParts) . ")")->fetchColumn() : 0;
$unassigned = $total - $assigned;

// ── Equipment by division ────────────────────────────────────────────
$divExtraWhere = $filterDivision !== '' ? " AND l.location_id = " . intval($filterDivision) : '';
$byDivision = $db->query("
    SELECT l.location_name as division,
           COUNT(DISTINCT su.systemunitId) as system_units,
           COUNT(DISTINCT mo.monitorId) as monitors,
           COUNT(DISTINCT pr.printerId) as printers,
           COUNT(DISTINCT aio.allinoneId) as allinones
    FROM location l
    LEFT JOIN location sec ON sec.parent_location_id = l.location_id AND sec.is_deleted = '0'
    LEFT JOIN location unit ON unit.parent_location_id = sec.location_id AND unit.is_deleted = '0'
    LEFT JOIN tbl_employee e ON (e.location_id = l.location_id OR e.location_id = sec.location_id OR e.location_id = unit.location_id) AND e.is_active = 1
    LEFT JOIN tbl_systemunit su ON su.employeeId = e.employeeId" . ($showSU ? '' : ' AND 1=0') . ($filterYear !== '' ? " AND su.yearAcquired = " . intval($filterYear) : '') . "
    LEFT JOIN tbl_monitor mo ON mo.employeeId = e.employeeId" . ($showMO ? '' : ' AND 1=0') . ($filterYear !== '' ? " AND mo.yearAcquired = " . intval($filterYear) : '') . "
    LEFT JOIN tbl_printer pr ON pr.employeeId = e.employeeId" . ($showPR ? '' : ' AND 1=0') . ($filterYear !== '' ? " AND pr.yearAcquired = " . intval($filterYear) : '') . "
    LEFT JOIN tbl_allinone aio ON aio.employeeId = e.employeeId" . ($showAIO ? '' : ' AND 1=0') . "
    WHERE l.location_type_id = 1 AND l.is_deleted = '0' $divExtraWhere
    GROUP BY l.location_id, l.location_name
    ORDER BY l.location_name
")->fetchAll(PDO::FETCH_ASSOC);

// ── Equipment by year acquired ───────────────────────────────────────
$yearExtraFilter = $filterYear !== '' ? " AND yearAcquired = " . intval($filterYear) : '';
$yearParts = [];
if ($showSU)  $yearParts[] = "SELECT yearAcquired as year_acquired, COUNT(*) as cnt FROM tbl_systemunit WHERE yearAcquired IS NOT NULL $yearExtraFilter GROUP BY yearAcquired";
if ($showMO)  $yearParts[] = "SELECT yearAcquired, COUNT(*) FROM tbl_monitor WHERE yearAcquired IS NOT NULL $yearExtraFilter GROUP BY yearAcquired";
if ($showPR)  $yearParts[] = "SELECT yearAcquired, COUNT(*) FROM tbl_printer WHERE yearAcquired IS NOT NULL $yearExtraFilter GROUP BY yearAcquired";
if ($showOTH) $yearParts[] = "SELECT yearAcquired, COUNT(*) FROM tbl_otherequipment WHERE yearAcquired IS NOT NULL $yearExtraFilter GROUP BY yearAcquired";
$byYear = [];
if (!empty($yearParts)) {
    $byYear = $db->query(
        "SELECT year_acquired, SUM(cnt) as total FROM (" . implode(' UNION ALL ', $yearParts) . ") combined GROUP BY year_acquired ORDER BY year_acquired DESC"
    )->fetchAll(PDO::FETCH_ASSOC);
}

// ── Latest condition from maintenance records ────────────────────────
$recEqFilter = '';
if ($filterEqType !== '') {
    $recEqFilter = " AND EXISTS (SELECT 1 FROM tbl_maintenance_schedule ss WHERE ss.scheduleId = mr.scheduleId AND ss.equipmentType = " . intval($filterEqType) . ")";
}
$conditionSummary = $db->query("
    SELECT conditionRating, COUNT(*) as cnt
    FROM (
        SELECT mr.conditionRating,
               ROW_NUMBER() OVER (PARTITION BY mr.scheduleId ORDER BY mr.maintenanceDate DESC) as rn
        FROM tbl_maintenance_record mr WHERE mr.conditionRating != '' $recEqFilter
    ) latest WHERE rn = 1 GROUP BY conditionRating ORDER BY FIELD(conditionRating, 'Excellent','Good','Fair','Poor')
")->fetchAll(PDO::FETCH_ASSOC);

// ── Status from maintenance records ──────────────────────────────────
$statusSummary = $db->query("
    SELECT overallStatus, COUNT(*) as cnt
    FROM (
        SELECT mr.overallStatus,
               ROW_NUMBER() OVER (PARTITION BY mr.scheduleId ORDER BY mr.maintenanceDate DESC) as rn
        FROM tbl_maintenance_record mr WHERE mr.overallStatus != '' $recEqFilter
    ) latest WHERE rn = 1 GROUP BY overallStatus ORDER BY FIELD(overallStatus, 'Operational','For Replacement','Disposed')
")->fetchAll(PDO::FETCH_ASSOC);

// ── Software license summary ─────────────────────────────────────────
$softTotal     = (int) $db->query("SELECT COUNT(*) FROM tbl_software")->fetchColumn();
$softExpired   = (int) $db->query("SELECT COUNT(*) FROM tbl_software WHERE expiryDate IS NOT NULL AND expiryDate < CURDATE()")->fetchColumn();
$softExpiring  = (int) $db->query("SELECT COUNT(*) FROM tbl_software WHERE expiryDate IS NOT NULL AND expiryDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)")->fetchColumn();
$softPerpetual = (int) $db->query("SELECT COUNT(*) FROM tbl_software WHERE licenseType = 'Perpetual'")->fetchColumn();
