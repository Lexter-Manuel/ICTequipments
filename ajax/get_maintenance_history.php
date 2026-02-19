<?php
require_once '../config/database.php';
header('Content-Type: application/json');
$db = getDB();

$view   = $_GET['view']   ?? 'detailed';
$range  = $_GET['range']  ?? 'Last 3 Months';
$search = $_GET['search'] ?? '';
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = max(1, min(100, (int)($_GET['limit'] ?? 10)));

// Reusable date condition builder
function buildDateCondition($range, $alias = 'mr') {
    switch ($range) {
        case 'Last 7 Days':   return "$alias.maintenanceDate >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        case 'This Month':    return "$alias.maintenanceDate >= DATE_FORMAT(NOW(), '%Y-%m-01')";
        case 'Last 3 Months': return "$alias.maintenanceDate >= DATE_SUB(NOW(), INTERVAL 3 MONTH)";
        case 'This Year':     return "$alias.maintenanceDate >= DATE_FORMAT(NOW(), '%Y-01-01')";
        case 'All Time':      return "1=1";
        default:              return "$alias.maintenanceDate >= DATE_SUB(NOW(), INTERVAL 3 MONTH)";
    }
}

try {

    // =================================================================
    // MODE: STATS — Aggregate counts for the stat cards
    // =================================================================
    if ($view === 'stats') {
        $dateCondition = buildDateCondition($range);

        $sql = "
            SELECT 
                COUNT(*) AS totalRecords,
                SUM(CASE WHEN mr.overallStatus = 'Operational' THEN 1 ELSE 0 END) AS maintained,
                SUM(CASE WHEN mr.conditionRating IN ('Excellent','Good') THEN 1 ELSE 0 END) AS excellentGood,
                0 AS pending
            FROM tbl_maintenance_record mr
            WHERE $dateCondition
        ";
        $stmt = $db->query($sql);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Pending = schedules that are active but have no record in range
        $pendingSql = "
            SELECT COUNT(*) AS pending
            FROM tbl_maintenance_schedule ms
            WHERE ms.isActive = 1
              AND ms.nextDueDate <= CURDATE()
              AND NOT EXISTS (
                  SELECT 1 FROM tbl_maintenance_record mr2
                  WHERE mr2.scheduleId = ms.scheduleId
                    AND $dateCondition
              )
        ";
        $pendingStmt = $db->query(str_replace('mr.maintenanceDate', 'mr2.maintenanceDate', $pendingSql));
        $stats['pending'] = (int)$pendingStmt->fetchColumn();

        // Cast to int
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
        $dateCondition = buildDateCondition($range);
        $offset = ($page - 1) * $limit;

        // Build WHERE parts
        $whereParts = [$dateCondition];
        $params = [];

        if ($search) {
            $whereParts[] = "(v.serial LIKE :s OR v.brand LIKE :s OR mr.preparedBy LIKE :s OR mr.remarks LIKE :s OR v.owner_name LIKE :s)";
            $params[':s'] = "%$search%";
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
        foreach ($params as $k => $val) $countStmt->bindValue($k, $val);
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
        foreach ($params as $k => $val) $stmt->bindValue($k, $val);
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

        // Recursive gather descendant IDs
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

        // Get names for these locations
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
        $placeholders = implode(',', array_fill(0, count($locationNames), '?'));

        // Fetch maintenance records for equipment in these locations
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
            WHERE v.location_name IN ($placeholders)
            ORDER BY mr.maintenanceDate DESC
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute($locationNames);
        $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Collect unique unit names for the filter dropdown
        $unitNames = array_values(array_unique(array_filter(array_column($assets, 'location_name'))));

        // Fetch employees in these locations with their assigned equipment
        $empSql = "
            SELECT 
                e.employeeId,
                CONCAT(e.firstName, ' ', e.lastName) AS name,
                e.position,
                l.location_name AS unit,
                e.location_id
            FROM tbl_employee e
            LEFT JOIN location l ON e.location_id = l.location_id
            WHERE e.is_active = 1
              AND l.location_name IN ($placeholders)
            ORDER BY e.lastName, e.firstName
        ";
        $empStmt = $db->prepare($empSql);
        $empStmt->execute($locationNames);
        $employees = $empStmt->fetchAll(PDO::FETCH_ASSOC);

        // For each employee, find which maintenance records belong to their equipment
        // We link via: employee -> their equipment (via employeeId in each equipment table)
        // But it's simpler to match by location_name + owner_name
        foreach ($employees as &$emp) {
            $emp['assets'] = [];
            foreach ($assets as $asset) {
                if ($asset['owner_name'] === $emp['name']) {
                    $emp['assets'][] = $asset['recordId'];
                }
            }
        }
        unset($emp);

        // Build asset lookup keyed by recordId for frontend
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
