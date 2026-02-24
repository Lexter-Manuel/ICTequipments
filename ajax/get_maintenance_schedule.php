<?php
/**
 * get_maintenance_schedule.php
 * ----------------------------
 * Backend for the Maintenance Schedule page.
 *
 * Modes (via ?view=):
 *   stats     – aggregate counts (overdue / due-soon / scheduled / total)
 *   detailed  – paginated table, filterable by search / division / status
 *   summary   – division cards with section → unit breakdown
 *   division  – drill-down: all assets for one division (groupable)
 */

require_once '../config/database.php';
header('Content-Type: application/json');
$db = getDB();

$view       = $_GET['view']       ?? 'detailed';
$search     = trim($_GET['search']  ?? '');
$sectionUnit = trim($_GET['sectionUnit'] ?? '');
$division   = $_GET['division']   ?? '';   // location_id of division (for summary drill-down)
$status     = $_GET['status']     ?? '';   // overdue | due_soon | scheduled
$unit       = $_GET['unit']       ?? '';   // location_id of unit (division view)
$page       = max(1, (int)($_GET['page']  ?? 1));
$limit      = max(1, min(100, (int)($_GET['limit'] ?? 10)));
$dueSoonDays = 7; // threshold

/**
 * Derive schedule status from days until due.
 */
function schedStatus($daysDue) {
    if ($daysDue < 0)  return 'overdue';
    if ($daysDue <= 7) return 'due_soon';
    return 'scheduled';
}

/**
 * Human label for a date difference.
 */
function daysLabel($daysDue) {
    if ($daysDue < 0) {
        $d = abs($daysDue);
        return $d . ' Day' . ($d !== 1 ? 's' : '') . ' Overdue';
    }
    if ($daysDue === 0) return 'Due Today';
    return $daysDue . ' Day' . ($daysDue !== 1 ? 's' : '') . ' Away';
}

/**
 * Build a CTE that resolves each unit → section → division.
 * Returns columns: unit_id, unit_name, section_id, section_name, division_id, division_name
 */
function locationCTE() {
    return "
    loc_tree AS (
        SELECT
            l.location_id   AS unit_id,
            l.location_name AS unit_name,
            CASE
                WHEN l.location_type_id = 3 THEN COALESCE(p1.location_id, l.location_id)
                ELSE l.location_id
            END AS section_id,
            CASE
                WHEN l.location_type_id = 3 THEN COALESCE(p1.location_name, l.location_name)
                ELSE l.location_name
            END AS section_name,
            CASE
                WHEN l.location_type_id = 3 THEN COALESCE(p2.location_id, p1.location_id, l.location_id)
                WHEN l.location_type_id = 2 THEN COALESCE(p1.location_id, l.location_id)
                ELSE l.location_id
            END AS division_id,
            CASE
                WHEN l.location_type_id = 3 THEN COALESCE(p2.location_name, p1.location_name, l.location_name)
                WHEN l.location_type_id = 2 THEN COALESCE(p1.location_name, l.location_name)
                ELSE l.location_name
            END AS division_name
        FROM location l
        LEFT JOIN location p1 ON l.parent_location_id = p1.location_id AND p1.is_deleted = '0'
        LEFT JOIN location p2 ON p1.parent_location_id = p2.location_id AND p2.is_deleted = '0'
        WHERE l.is_deleted = '0'
    )";
}

try {

// ==================================================================
// MODE: STATS
// ==================================================================
if ($view === 'stats') {
    $sql = "
        SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN DATEDIFF(ms.nextDueDate, CURDATE()) < 0  THEN 1 ELSE 0 END) AS overdue,
            SUM(CASE WHEN DATEDIFF(ms.nextDueDate, CURDATE()) BETWEEN 0 AND $dueSoonDays THEN 1 ELSE 0 END) AS dueSoon,
            SUM(CASE WHEN DATEDIFF(ms.nextDueDate, CURDATE()) > $dueSoonDays THEN 1 ELSE 0 END) AS scheduled
        FROM tbl_maintenance_schedule ms
        JOIN view_maintenance_master v ON ms.equipmentId = v.id AND ms.equipmentType = v.type_id
        WHERE ms.isActive = 1
    ";
    $row = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
    foreach ($row as &$v) $v = (int)$v;
    echo json_encode(['success' => true, 'data' => $row]);
    exit;
}

// ==================================================================
// MODE: DETAILED — paginated table
// ==================================================================
if ($view === 'detailed') {
    $offset = ($page - 1) * $limit;

    $baseSql = "
        WITH " . locationCTE() . "
        SELECT %COLS%
        FROM tbl_maintenance_schedule ms
        JOIN view_maintenance_master v ON ms.equipmentId = v.id AND ms.equipmentType = v.type_id
        LEFT JOIN location el ON v.location_name = el.location_name AND el.is_deleted = '0'
        LEFT JOIN loc_tree lt ON el.location_id = lt.unit_id
        WHERE ms.isActive = 1
    ";

    $whereParts = [];
    $params = [];

    // Search filter
    if ($search) {
        $whereParts[] = "(v.brand LIKE :s1 OR v.serial LIKE :s2 OR v.owner_name LIKE :s3 OR v.location_name LIKE :s4)";
        $params[':s1'] = "%$search%";
        $params[':s2'] = "%$search%";
        $params[':s3'] = "%$search%";
        $params[':s4'] = "%$search%";
    }

    // Section/Unit filter (by location_name)
    if ($sectionUnit) {
        $whereParts[] = "v.location_name = :sectionUnit";
        $params[':sectionUnit'] = $sectionUnit;
    }

    // Status filter
    if ($status === 'overdue') {
        $whereParts[] = "DATEDIFF(ms.nextDueDate, CURDATE()) < 0";
    } elseif ($status === 'due_soon') {
        $whereParts[] = "DATEDIFF(ms.nextDueDate, CURDATE()) BETWEEN 0 AND $dueSoonDays";
    } elseif ($status === 'scheduled') {
        $whereParts[] = "DATEDIFF(ms.nextDueDate, CURDATE()) > $dueSoonDays";
    }

    $whereClause = count($whereParts) ? ' AND ' . implode(' AND ', $whereParts) : '';

    // Count
    $countSql = str_replace('%COLS%', 'COUNT(*) AS cnt', $baseSql) . $whereClause;
    $countStmt = $db->prepare($countSql);
    foreach ($params as $k => $val) $countStmt->bindValue($k, $val);
    $countStmt->execute();
    $totalCount = (int)$countStmt->fetchColumn();

    // Fetch page
    $cols = "
        ms.scheduleId,
        ms.equipmentType,
        ms.equipmentId,
        ms.maintenanceFrequency,
        ms.nextDueDate,
        ms.lastMaintenanceDate,
        DATEDIFF(ms.nextDueDate, CURDATE()) AS daysDue,
        v.brand,
        v.serial,
        v.type_name,
        v.type_id,
        v.owner_name,
        v.location_name,
        lt.section_name,
        lt.division_name
    ";
    $dataSql = str_replace('%COLS%', $cols, $baseSql) . $whereClause . " ORDER BY ms.nextDueDate ASC LIMIT :lim OFFSET :off";
    $stmt = $db->prepare($dataSql);
    foreach ($params as $k => $val) $stmt->bindValue($k, $val);
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Add computed fields
    foreach ($rows as &$r) {
        $dd = (int)$r['daysDue'];
        $r['status']    = schedStatus($dd);
        $r['daysLabel'] = daysLabel($dd);
    }

    echo json_encode([
        'success'    => true,
        'data'       => $rows,
        'pagination' => [
            'page'       => $page,
            'limit'      => $limit,
            'total'      => $totalCount,
            'totalPages' => (int)ceil($totalCount / $limit),
        ]
    ]);
    exit;
}

// ==================================================================
// MODE: SUMMARY — Division cards with section/unit breakdown
// ==================================================================
if ($view === 'summary') {
    // Get all schedules with location tree
    $sql = "
        WITH " . locationCTE() . "
        SELECT
            lt.division_id,
            lt.division_name,
            lt.section_id,
            lt.section_name,
            lt.unit_id,
            lt.unit_name,
            DATEDIFF(ms.nextDueDate, CURDATE()) AS daysDue
        FROM tbl_maintenance_schedule ms
        JOIN view_maintenance_master v ON ms.equipmentId = v.id AND ms.equipmentType = v.type_id
        LEFT JOIN location el ON v.location_name = el.location_name AND el.is_deleted = '0'
        LEFT JOIN loc_tree lt ON el.location_id = lt.unit_id
        WHERE ms.isActive = 1
    ";
    $rows = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    // Build division → section → unit tree with counts
    $divisions = [];
    foreach ($rows as $r) {
        $divId   = $r['division_id']   ?: 'unknown';
        $divName = $r['division_name'] ?: 'Unknown Division';
        $secId   = $r['section_id']    ?: $divId;
        $secName = $r['section_name']  ?: $divName;
        $unitId  = $r['unit_id']       ?: $secId;
        $unitName= $r['unit_name']     ?: $secName;
        $dd      = (int)$r['daysDue'];
        $st      = schedStatus($dd);

        if (!isset($divisions[$divId])) {
            $divisions[$divId] = [
                'divisionId'   => $divId,
                'divisionName' => $divName,
                'overdue'      => 0,
                'dueSoon'      => 0,
                'scheduled'    => 0,
                'sections'     => []
            ];
        }
        $div = &$divisions[$divId];
        if ($st === 'overdue')   $div['overdue']++;
        elseif ($st === 'due_soon') $div['dueSoon']++;
        else $div['scheduled']++;

        // Section
        if (!isset($div['sections'][$secId])) {
            $div['sections'][$secId] = [
                'sectionId'   => $secId,
                'sectionName' => $secName,
                'units'       => []
            ];
        }
        $sec = &$div['sections'][$secId];

        // Unit
        if (!isset($sec['units'][$unitId])) {
            $sec['units'][$unitId] = [
                'unitId'   => $unitId,
                'unitName' => $unitName,
                'overdue'  => 0,
                'dueSoon'  => 0,
                'scheduled'=> 0,
            ];
        }
        $u = &$sec['units'][$unitId];
        if ($st === 'overdue')   $u['overdue']++;
        elseif ($st === 'due_soon') $u['dueSoon']++;
        else $u['scheduled']++;
    }

    // Convert associative → indexed arrays
    foreach ($divisions as &$d) {
        foreach ($d['sections'] as &$s) {
            $s['units'] = array_values($s['units']);
        }
        $d['sections'] = array_values($d['sections']);
    }
    $divisions = array_values($divisions);

    echo json_encode(['success' => true, 'data' => $divisions]);
    exit;
}

// ==================================================================
// MODE: DIVISION — drill-down assets for one division
// ==================================================================
if ($view === 'division') {
    if (!$division) {
        echo json_encode(['success' => false, 'message' => 'division parameter required']);
        exit;
    }

    $unitCondition = '';
    $params = [':div' => $division];
    if ($unit) {
        $unitCondition = ' AND el.location_id = :uid';
        $params[':uid'] = $unit;
    }

    $sql = "
        WITH " . locationCTE() . "
        SELECT
            ms.scheduleId,
            ms.equipmentType,
            ms.equipmentId,
            ms.maintenanceFrequency,
            ms.nextDueDate,
            DATEDIFF(ms.nextDueDate, CURDATE()) AS daysDue,
            v.brand,
            v.serial,
            v.type_name,
            v.type_id,
            v.owner_name,
            v.location_name,
            lt.unit_name,
            lt.section_name,
            e.employeeId,
            e.position AS employeePosition
        FROM tbl_maintenance_schedule ms
        JOIN view_maintenance_master v ON ms.equipmentId = v.id AND ms.equipmentType = v.type_id
        LEFT JOIN location el ON v.location_name = el.location_name AND el.is_deleted = '0'
        LEFT JOIN loc_tree lt ON el.location_id = lt.unit_id
        LEFT JOIN tbl_employee e ON v.owner_name = CONCAT(e.firstName, ' ', e.lastName)
        WHERE ms.isActive = 1
          AND lt.division_id = :div
          $unitCondition
        ORDER BY ms.nextDueDate ASC
    ";
    $stmt = $db->prepare($sql);
    foreach ($params as $k => $val) $stmt->bindValue($k, $val);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Add computed fields
    foreach ($rows as &$r) {
        $dd = (int)$r['daysDue'];
        $r['status']    = schedStatus($dd);
        $r['daysLabel'] = daysLabel($dd);
    }
    unset($r); // break reference to prevent PHP foreach gotcha

    // Also gather distinct units for the filter dropdown
    $unitSql = "
        WITH " . locationCTE() . "
        SELECT DISTINCT lt.unit_id, lt.unit_name
        FROM tbl_maintenance_schedule ms
        JOIN view_maintenance_master v ON ms.equipmentId = v.id AND ms.equipmentType = v.type_id
        LEFT JOIN location el ON v.location_name = el.location_name AND el.is_deleted = '0'
        LEFT JOIN loc_tree lt ON el.location_id = lt.unit_id
        WHERE ms.isActive = 1 AND lt.division_id = :div
        ORDER BY lt.unit_name
    ";
    $uStmt = $db->prepare($unitSql);
    $uStmt->bindValue(':div', $division);
    $uStmt->execute();
    $units = $uStmt->fetchAll(PDO::FETCH_ASSOC);

    // Build employees array (grouped)
    $employees = [];
    foreach ($rows as $r) {
        $empKey = $r['employeeId'] ?: ('noemp_' . $r['equipmentId']);
        if (!isset($employees[$empKey])) {
            $employees[$empKey] = [
                'employeeId' => $r['employeeId'],
                'name'       => $r['owner_name'] ?: 'Unassigned',
                'position'   => $r['employeePosition'] ?: '',
                'unit'       => $r['unit_name'] ?: $r['location_name'] ?: '',
                'overdue'    => 0,
                'dueSoon'    => 0,
                'scheduled'  => 0,
                'assets'     => []
            ];
        }
        $emp = &$employees[$empKey];
        if ($r['status'] === 'overdue')   $emp['overdue']++;
        elseif ($r['status'] === 'due_soon') $emp['dueSoon']++;
        else $emp['scheduled']++;

        $emp['assets'][] = [
            'scheduleId'  => $r['scheduleId'],
            'equipmentId' => $r['equipmentId'],
            'brand'       => $r['brand'],
            'serial'      => $r['serial'],
            'typeName'    => $r['type_name'],
            'typeId'      => $r['type_id'],
            'nextDueDate' => $r['nextDueDate'],
            'daysDue'     => (int)$r['daysDue'],
            'status'      => $r['status'],
            'daysLabel'   => $r['daysLabel'],
            'frequency'   => $r['maintenanceFrequency'],
        ];
    }
    $employees = array_values($employees);

    // Division-level stats
    $stats = ['overdue' => 0, 'dueSoon' => 0, 'scheduled' => 0, 'total' => count($rows)];
    foreach ($rows as $r) {
        if ($r['status'] === 'overdue')       $stats['overdue']++;
        elseif ($r['status'] === 'due_soon')  $stats['dueSoon']++;
        else                                  $stats['scheduled']++;
    }

    echo json_encode([
        'success'   => true,
        'data'      => $rows,
        'units'     => $units,
        'employees' => $employees,
        'stats'     => $stats,
    ]);
    exit;
}

// ==================================================================
// MODE: DIVISIONS LIST — for the filter dropdown
// ==================================================================
if ($view === 'divisions') {
    $sql = "SELECT location_id, location_name FROM location WHERE location_type_id = 1 AND is_deleted = '0' ORDER BY location_name";
    $rows = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $rows]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid view parameter']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
