<?php
/**
 * Equipment Summary — Data Queries (Unified tbl_equipment schema)
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
    SELECT DISTINCT year_acquired
    FROM tbl_equipment
    WHERE year_acquired IS NOT NULL AND is_archived = 0
    ORDER BY year_acquired DESC
")->fetchAll(PDO::FETCH_COLUMN);

$equipmentTypes = $db->query(
    "SELECT typeId, typeName FROM tbl_equipment_type_registry ORDER BY typeName"
)->fetchAll(PDO::FETCH_ASSOC);

// ── Filter helpers ───────────────────────────────────────────────────
$whereBase = "eq.is_archived = 0";
$params = [];

if ($filterDivision !== '') {
    $divId = intval($filterDivision);
    $whereBase .= " AND EXISTS (
        SELECT 1 FROM tbl_employee emp
        INNER JOIN location loc ON emp.location_id = loc.location_id
        LEFT JOIN location parent ON loc.parent_location_id = parent.location_id
        LEFT JOIN location grandparent ON parent.parent_location_id = grandparent.location_id
        WHERE emp.employeeId = eq.employee_id
        AND (loc.location_id = {$divId} OR parent.location_id = {$divId} OR grandparent.location_id = {$divId})
    )";
}

if ($filterYear !== '') {
    $whereBase .= " AND eq.year_acquired = " . intval($filterYear);
}

$typeFilter = '';
if ($filterEqType !== '') {
    $typeFilter = " AND eq.type_id = " . intval($filterEqType);
}

// ── Equipment-type visibility ────────────────────────────────────────
$showSU  = ($filterEqType === '' || $filterEqType === '1');
$showMO  = ($filterEqType === '' || $filterEqType === '3');
$showPR  = ($filterEqType === '' || $filterEqType === '4');
$showAIO = ($filterEqType === '' || $filterEqType === '2');
$showOTH = ($filterEqType === '' || in_array($filterEqType, ['5','6','7','8','9']));

// ── Counts per type ──────────────────────────────────────────────────
$countStmt = $db->query("
    SELECT r.typeName, COUNT(*) AS cnt
    FROM tbl_equipment eq
    INNER JOIN tbl_equipment_type_registry r ON eq.type_id = r.typeId
    WHERE {$whereBase} {$typeFilter}
    GROUP BY r.typeId, r.typeName
");
$countsByType = $countStmt->fetchAll(PDO::FETCH_KEY_PAIR);

$suCount  = $showSU  ? (int)($countsByType['System Unit'] ?? 0) : 0;
$moCount  = $showMO  ? (int)($countsByType['Monitor'] ?? 0) : 0;
$prCount  = $showPR  ? (int)($countsByType['Printer'] ?? 0) : 0;
$aioCount = $showAIO ? (int)($countsByType['All-in-One'] ?? 0) : 0;
$othCount = $showOTH ? array_sum($countsByType) - ($countsByType['System Unit'] ?? 0) - ($countsByType['Monitor'] ?? 0) - ($countsByType['Printer'] ?? 0) - ($countsByType['All-in-One'] ?? 0) : 0;
$total    = $suCount + $moCount + $prCount + $aioCount + $othCount;

// ── Assigned / Unassigned ────────────────────────────────────────────
$assigned = (int) $db->query("
    SELECT COUNT(*) FROM tbl_equipment eq
    INNER JOIN tbl_equipment_type_registry r ON eq.type_id = r.typeId
    WHERE {$whereBase} {$typeFilter} AND eq.employee_id IS NOT NULL
")->fetchColumn();
$unassigned = $total - $assigned;

// ── Equipment by division ────────────────────────────────────────────
$divExtraWhere = $filterDivision !== '' ? " AND l.location_id = " . intval($filterDivision) : '';
$byDivision = $db->query("
    SELECT l.location_name as division,
           SUM(CASE WHEN r.typeName = 'System Unit' THEN 1 ELSE 0 END) as system_units,
           SUM(CASE WHEN r.typeName = 'Monitor' THEN 1 ELSE 0 END) as monitors,
           SUM(CASE WHEN r.typeName = 'Printer' THEN 1 ELSE 0 END) as printers,
           SUM(CASE WHEN r.typeName = 'All-in-One' THEN 1 ELSE 0 END) as allinones
    FROM location l
    LEFT JOIN location sec ON sec.parent_location_id = l.location_id AND sec.is_deleted = '0'
    LEFT JOIN location unit ON unit.parent_location_id = sec.location_id AND unit.is_deleted = '0'
    LEFT JOIN tbl_employee e ON (e.location_id = l.location_id OR e.location_id = sec.location_id OR e.location_id = unit.location_id) AND e.is_archive = 0
    LEFT JOIN tbl_equipment eq ON eq.employee_id = e.employeeId AND eq.is_archived = 0
        " . ($filterYear !== '' ? " AND eq.year_acquired = " . intval($filterYear) : '') . "
        " . ($filterEqType !== '' ? " AND eq.type_id = " . intval($filterEqType) : '') . "
    LEFT JOIN tbl_equipment_type_registry r ON eq.type_id = r.typeId
    WHERE l.location_type_id = 1 AND l.is_deleted = '0' $divExtraWhere
    GROUP BY l.location_id, l.location_name
    ORDER BY l.location_name
")->fetchAll(PDO::FETCH_ASSOC);

// ── Equipment by year acquired ───────────────────────────────────────
$byYear = $db->query("
    SELECT eq.year_acquired, COUNT(*) as total
    FROM tbl_equipment eq
    INNER JOIN tbl_equipment_type_registry r ON eq.type_id = r.typeId
    WHERE {$whereBase} {$typeFilter} AND eq.year_acquired IS NOT NULL
    GROUP BY eq.year_acquired
    ORDER BY eq.year_acquired DESC
")->fetchAll(PDO::FETCH_ASSOC);

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
