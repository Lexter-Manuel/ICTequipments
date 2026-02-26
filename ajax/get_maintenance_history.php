<?php
require_once '../config/database.php';
header('Content-Type: application/json');
$db = getDB();

$view     = $_GET['view']     ?? 'detailed';
$search   = $_GET['search']   ?? '';
$page     = max(1, (int)($_GET['page']  ?? 1));
$limit    = max(1, min(100, (int)($_GET['limit'] ?? 10)));

// New: accept explicit dateFrom / dateTo  (YYYY-MM-DD)
// Empty strings mean "All Time"
$dateFrom    = trim($_GET['dateFrom'] ?? '');
$dateTo      = trim($_GET['dateTo']   ?? '');
$sectionUnit = trim($_GET['sectionUnit'] ?? '');

/**
 * Build a SQL date condition string (and optionally bind params).
 *
 * Pass $bindings by reference; the function will push named params into it.
 *
 * @param string  $dateFrom   YYYY-MM-DD or empty
 * @param string  $dateTo     YYYY-MM-DD or empty
 * @param array   &$bindings  Receives ':dateFrom' / ':dateTo' when needed
 * @param string  $alias      Table alias prefix (default 'mr')
 * @return string             SQL fragment (no leading AND)
 */
function buildDateCondition(string $dateFrom, string $dateTo, array &$bindings, string $alias = 'mr'): string {
    if ($dateFrom === '' && $dateTo === '') {
        return '1=1'; // All Time
    }

    if ($dateFrom !== '' && $dateTo !== '') {
        $bindings[':dateFrom'] = $dateFrom;
        $bindings[':dateTo']   = $dateTo;
        return "DATE($alias.maintenanceDate) BETWEEN :dateFrom AND :dateTo";
    }

    if ($dateFrom !== '') {
        $bindings[':dateFrom'] = $dateFrom;
        return "DATE($alias.maintenanceDate) >= :dateFrom";
    }

    // Only dateTo supplied
    $bindings[':dateTo'] = $dateTo;
    return "DATE($alias.maintenanceDate) <= :dateTo";
}

try {

    // =================================================================
    // MODE: STATS — Aggregate counts for the stat cards
    // =================================================================
    if ($view === 'stats') {
        $bindings      = [];
        $dateCondition = buildDateCondition($dateFrom, $dateTo, $bindings);

        $statsWhere = [$dateCondition];
        if ($sectionUnit) {
            $statsWhere[]          = "v_stat.location_name = :sectionUnit";
            $bindings[':sectionUnit'] = $sectionUnit;
        }
        $statsWhereSQL = implode(' AND ', $statsWhere);

        $sql = "
            SELECT 
                COUNT(*) AS totalRecords,
                SUM(CASE WHEN mr.overallStatus = 'Operational' THEN 1 ELSE 0 END) AS maintained,
                SUM(CASE WHEN mr.conditionRating IN ('Excellent','Good') THEN 1 ELSE 0 END) AS excellentGood,
                0 AS pending
            FROM tbl_maintenance_record mr
            " . ($sectionUnit ? "LEFT JOIN tbl_maintenance_schedule ms_stat ON mr.scheduleId = ms_stat.scheduleId
            LEFT JOIN view_maintenance_master v_stat ON ms_stat.equipmentId = v_stat.id AND ms_stat.equipmentType = v_stat.type_id" : '') . "
            WHERE $statsWhereSQL
        ";
        $stmt = $db->prepare($sql);
        foreach ($bindings as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Pending = active schedules overdue with no record in range
        $pendingBindings = [];
        $pendingDateCond = buildDateCondition($dateFrom, $dateTo, $pendingBindings, 'mr2');

        // Rename params to avoid clash
        $pendingParamMap = [];
        foreach ($pendingBindings as $k => $v) {
            $newKey = $k . '_p';
            $pendingParamMap[$newKey] = $v;
        }
        $pendingDateCondRenamed = str_replace(
            array_keys($pendingBindings),
            array_keys($pendingParamMap),
            $pendingDateCond
        );

        $pendingSql = "
            SELECT COUNT(*) AS pending
            FROM tbl_maintenance_schedule ms
            WHERE ms.isActive = 1
              AND ms.nextDueDate <= CURDATE()
              AND NOT EXISTS (
                  SELECT 1 FROM tbl_maintenance_record mr2
                  WHERE mr2.scheduleId = ms.scheduleId
                    AND $pendingDateCondRenamed
              )
        ";
        $pendingStmt = $db->prepare($pendingSql);
        foreach ($pendingParamMap as $k => $v) $pendingStmt->bindValue($k, $v);
        $pendingStmt->execute();
        $stats['pending'] = (int)$pendingStmt->fetchColumn();

        $stats['totalRecords']  = (int)$stats['totalRecords'];
        $stats['maintained']    = (int)$stats['maintained'];
        $stats['excellentGood'] = (int)$stats['excellentGood'];

        echo json_encode(['success' => true, 'data' => $stats]);
        exit;
    }

    // =================================================================
    // MODE: DETAILED — Paginated list for the table view
    // =================================================================
    if ($view === 'detailed') {
        $bindings      = [];
        $dateCondition = buildDateCondition($dateFrom, $dateTo, $bindings);
        $offset        = ($page - 1) * $limit;

        $whereParts = [$dateCondition];

        if ($search) {
            $whereParts[]      = "(v.serial LIKE :s1 OR v.brand LIKE :s2 OR mr.preparedBy LIKE :s3 OR mr.remarks LIKE :s4 OR v.owner_name LIKE :s5)";
            $bindings[':s1']   = "%$search%";
            $bindings[':s2']   = "%$search%";
            $bindings[':s3']   = "%$search%";
            $bindings[':s4']   = "%$search%";
            $bindings[':s5']   = "%$search%";
        }

        if ($sectionUnit) {
            $whereParts[]             = "v.location_name = :sectionUnit";
            $bindings[':sectionUnit'] = $sectionUnit;
        }

        $where = implode(' AND ', $whereParts);

        // Count total
        $countSql = "
            SELECT COUNT(*) 
            FROM tbl_maintenance_record mr
            LEFT JOIN tbl_maintenance_schedule ms ON mr.scheduleId = ms.scheduleId
            LEFT JOIN view_maintenance_master v ON ms.equipmentId = v.id AND ms.equipmentType = v.type_id
            WHERE $where
        ";
        $countStmt = $db->prepare($countSql);
        foreach ($bindings as $k => $v) $countStmt->bindValue($k, $v);
        $countStmt->execute();
        $totalCount = (int)$countStmt->fetchColumn();

        // Fetch page
        $sql = "
            SELECT 
                mr.recordId,
                mr.maintenanceDate,
                mr.overallStatus,
                mr.conditionRating,
                mr.remarks,
                mr.preparedBy AS technician,
                v.brand,
                v.serial,
                v.type_name,
                v.location_name,
                v.owner_name
            FROM tbl_maintenance_record mr
            LEFT JOIN tbl_maintenance_schedule ms ON mr.scheduleId = ms.scheduleId
            LEFT JOIN view_maintenance_master v ON ms.equipmentId = v.id AND ms.equipmentType = v.type_id
            WHERE $where
            ORDER BY mr.maintenanceDate DESC
            LIMIT :lim OFFSET :off
        ";
        $stmt = $db->prepare($sql);
        foreach ($bindings as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode([
            'success'    => true,
            'data'       => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'pagination' => [
                'page'       => $page,
                'limit'      => $limit,
                'total'      => $totalCount,
                'totalPages' => ceil($totalCount / $limit)
            ]
        ]);
        exit;
    }

    // =================================================================
    // MODE: SUMMARY — Division hierarchy with rollup stats
    // =================================================================
    if ($view === 'summary') {

        // 1. Fetch All Locations
        $locStmt = $db->query("SELECT location_id, location_name, location_type_id, parent_location_id FROM location WHERE is_deleted = '0' ORDER BY location_name ASC");
        $locations = [];

        while ($row = $locStmt->fetch(PDO::FETCH_ASSOC)) {
            $row['stats'] = ['total' => 0, 'maintained' => 0, 'pending' => 0, 'compliance' => 0];
            $row['children'] = [];
            $locations[$row['location_id']] = $row;
        }

        // 2. Compute stats per location (unit level) from schedules
        $sqlStats = "
            SELECT 
                v.location_name,
                COUNT(*) AS total,
                SUM(CASE WHEN ms.nextDueDate >= CURDATE() THEN 1 ELSE 0 END) AS maintained
            FROM tbl_maintenance_schedule ms
            JOIN view_maintenance_master v ON ms.equipmentId = v.id AND ms.equipmentType = v.type_id
            WHERE ms.isActive = 1
            GROUP BY v.location_name
        ";
        $statStmt = $db->query($sqlStats);
        $locationStats = [];
        while ($row = $statStmt->fetch(PDO::FETCH_ASSOC)) {
            $locationStats[$row['location_name']] = $row;
        }

        // 3. Map stats to location IDs
        foreach ($locations as $id => &$node) {
            $name = $node['location_name'];
            if (isset($locationStats[$name])) {
                $node['stats']['total']      = (int)$locationStats[$name]['total'];
                $node['stats']['maintained'] = (int)$locationStats[$name]['maintained'];
                $node['stats']['pending']    = $node['stats']['total'] - $node['stats']['maintained'];
            }
        }

        // 4. Build tree
        $tree = [];
        foreach ($locations as $id => &$node) {
            $pid = $node['parent_location_id'];
            if ($pid && isset($locations[$pid])) {
                $locations[$pid]['children'][] = &$node;
            } elseif ($node['location_type_id'] == 1) {
                $tree[] = &$node;
            }
        }

        // 5. Rollup
        function rollupStats(&$node) {
            foreach ($node['children'] as &$child) {
                rollupStats($child);
                $node['stats']['total']      += $child['stats']['total'];
                $node['stats']['maintained'] += $child['stats']['maintained'];
                $node['stats']['pending']    += $child['stats']['pending'];
            }
            if ($node['stats']['total'] > 0) {
                $node['stats']['compliance'] = round(($node['stats']['maintained'] / $node['stats']['total']) * 100);
            }
        }

        foreach ($tree as &$root) {
            rollupStats($root);
        }

        echo json_encode(['success' => true, 'data' => $tree]);
        exit;
    }

    // =================================================================
    // MODE: DIVISION — Drill-down records for a specific division
    // =================================================================
    if ($view === 'division') {
        $divisionId = (int)($_GET['divisionId'] ?? 0);
        if (!$divisionId) {
            echo json_encode(['success' => false, 'message' => 'Missing divisionId']);
            exit;
        }

        // Get all descendant location IDs under this division
        $locStmt = $db->query("SELECT location_id, location_name, location_type_id, parent_location_id FROM location WHERE is_deleted = '0'");
        $allLocs = [];
        while ($r = $locStmt->fetch(PDO::FETCH_ASSOC)) {
            $allLocs[$r['location_id']] = $r;
        }

        function getDescendantIds($parentId, &$allLocs) {
            $ids = [$parentId];
            foreach ($allLocs as $loc) {
                if ($loc['parent_location_id'] == $parentId) {
                    $ids = array_merge($ids, getDescendantIds($loc['location_id'], $allLocs));
                }
            }
            return $ids;
        }

        $descendantIds = getDescendantIds($divisionId, $allLocs);

        $locationNames = [];
        foreach ($descendantIds as $lid) {
            if (isset($allLocs[$lid])) {
                $locationNames[] = $allLocs[$lid]['location_name'];
            }
        }

        if (empty($locationNames)) {
            echo json_encode(['success' => true, 'data' => ['assets' => [], 'units' => [], 'employees' => []]]);
            exit;
        }

        // Build placeholders for IN clause
        $inPlaceholders = implode(',', array_fill(0, count($locationNames), '?'));

        // Build date condition for division query (positional style to avoid conflict)
        $bindings         = [];
        $dateCondFragment = buildDateCondition($dateFrom, $dateTo, $bindings);

        // Convert named bindings to positional for consistency with location IN params
        // We'll use a separate prepare with mixed approach: named for date, positional for IN
        $locationInParams = $locationNames; // indexed array → positional ?

        // Combine: date bindings stay named, location bindings stay positional
        // Easiest: rebuild query using named params for dates and named params for locations too
        $locNamedParams = [];
        foreach ($locationNames as $idx => $name) {
            $locNamedParams[':loc' . $idx] = $name;
        }
        $locPlaceholderStr = implode(',', array_keys($locNamedParams));

        $dateBindings = [];
        $dateCondSQL  = buildDateCondition($dateFrom, $dateTo, $dateBindings);

        $sql = "
            SELECT 
                mr.recordId,
                mr.maintenanceDate,
                mr.overallStatus,
                mr.conditionRating,
                mr.remarks,
                mr.preparedBy AS technician,
                v.brand,
                v.serial,
                v.type_name,
                v.location_name,
                v.owner_name,
                v.id AS equipmentId,
                v.type_id AS equipmentTypeId
            FROM tbl_maintenance_record mr
            LEFT JOIN tbl_maintenance_schedule ms ON mr.scheduleId = ms.scheduleId
            LEFT JOIN view_maintenance_master v ON ms.equipmentId = v.id AND ms.equipmentType = v.type_id
            WHERE v.location_name IN ($locPlaceholderStr)
              AND $dateCondSQL
            ORDER BY mr.maintenanceDate DESC
        ";
        $stmt = $db->prepare($sql);
        foreach ($locNamedParams as $k => $v) $stmt->bindValue($k, $v);
        foreach ($dateBindings as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $unitNames = array_values(array_unique(array_filter(array_column($assets, 'location_name'))));

        // Fetch employees in these locations
        $empSql = "
            SELECT 
                e.employeeId,
                CONCAT(e.firstName, ' ', e.lastName) AS name,
                e.position,
                l.location_name AS unit,
                e.location_id
            FROM tbl_employee e
            LEFT JOIN location l ON e.location_id = l.location_id
            WHERE e.is_archive = 0
              AND l.location_name IN ($locPlaceholderStr)
            ORDER BY e.lastName, e.firstName
        ";
        $empStmt = $db->prepare($empSql);
        foreach ($locNamedParams as $k => $v) $empStmt->bindValue($k, $v);
        $empStmt->execute();
        $employees = $empStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($employees as &$emp) {
            $emp['assets'] = [];
            foreach ($assets as $asset) {
                if ($asset['owner_name'] === $emp['name']) {
                    $emp['assets'][] = $asset['recordId'];
                }
            }
        }
        unset($emp);

        $assetLookup = [];
        foreach ($assets as $a) {
            $assetLookup[$a['recordId']] = $a;
        }

        echo json_encode([
            'success' => true,
            'data'    => [
                'assets'      => $assets,
                'assetLookup' => $assetLookup,
                'units'       => $unitNames,
                'employees'   => $employees
            ]
        ]);
        exit;
    }

    // Fallback
    echo json_encode(['success' => false, 'message' => 'Invalid view parameter']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>