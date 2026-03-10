<?php
/**
 * get_location_equipment.php
 * ──────────────────────────
 * Returns equipment grouped by section → unit for a given division_id.
 * Also supports ?location_stats=<id> to get quick counts for a single location node.
 *
 * GET ?division_id=<id>
 *   → { success, sections: [ { location_id, name, equipment: [...], units: [ { location_id, name, equipment: [...] } ] } ] }
 *
 * GET ?location_stats=<id>
 *   → { success, total, scheduled, unscheduled }
 */
require_once '../config/session-guard.php';
require_once '../config/database.php';
require_once '../config/config.php';
header('Content-Type: application/json');

$db = getDB();

// ── Quick stats for a single location ──
if (!empty($_GET['location_stats'])) {
    $locId = intval($_GET['location_stats']);
    try {
        // Gather this location + children
        $locationIds = [$locId];
        $childStmt = $db->prepare("SELECT location_id FROM location WHERE parent_location_id = ? AND is_deleted = '0'");
        $childStmt->execute([$locId]);
        while ($cid = $childStmt->fetchColumn()) {
            $locationIds[] = (int)$cid;
        }

        $ph = implode(',', array_fill(0, count($locationIds), '?'));

        // Equipment directly at these locations OR assigned to employees at these locations
        $countStmt = $db->prepare("
            SELECT COUNT(*) as total
            FROM tbl_equipment eq
            LEFT JOIN tbl_employee e ON eq.employee_id = e.employeeId
            WHERE eq.is_archived = 0
              AND (eq.location_id IN ($ph) OR e.location_id IN ($ph))
        ");
        $params = array_merge($locationIds, $locationIds);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Count how many of those have active maintenance schedules
        $schedStmt = $db->prepare("
            SELECT COUNT(DISTINCT eq.equipment_id) as scheduled
            FROM tbl_equipment eq
            LEFT JOIN tbl_employee e ON eq.employee_id = e.employeeId
            INNER JOIN tbl_maintenance_schedule ms ON ms.equipmentId = eq.equipment_id AND ms.isActive = 1
            WHERE eq.is_archived = 0
              AND (eq.location_id IN ($ph) OR e.location_id IN ($ph))
        ");
        $schedStmt->execute($params);
        $scheduled = (int)$schedStmt->fetchColumn();

        echo json_encode([
            'success' => true,
            'total' => $total,
            'scheduled' => $scheduled,
            'unscheduled' => $total - $scheduled
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ── Full location tree for a division ──
$divisionId = intval($_GET['division_id'] ?? 0);
if (!$divisionId) {
    echo json_encode(['success' => false, 'message' => 'Missing division_id']);
    exit;
}

try {
    // 1. Get sections under this division
    $stmtSections = $db->prepare("
        SELECT location_id, location_name
        FROM location
        WHERE parent_location_id = ? AND is_deleted = '0'
        ORDER BY location_name
    ");
    $stmtSections->execute([$divisionId]);
    $sections = $stmtSections->fetchAll(PDO::FETCH_ASSOC);

    // 2. For each section, get units
    $stmtUnits = $db->prepare("
        SELECT location_id, location_name
        FROM location
        WHERE parent_location_id = ? AND is_deleted = '0'
        ORDER BY location_name
    ");

    // 3. Build list of ALL location_ids we need equipment for
    $allLocationIds = [];
    $sectionData = [];
    foreach ($sections as $sec) {
        $secId = (int)$sec['location_id'];
        $allLocationIds[] = $secId;
        $entry = [
            'location_id' => $secId,
            'name' => $sec['location_name'],
            'units' => []
        ];
        $stmtUnits->execute([$secId]);
        $units = $stmtUnits->fetchAll(PDO::FETCH_ASSOC);
        foreach ($units as $u) {
            $uid = (int)$u['location_id'];
            $allLocationIds[] = $uid;
            $entry['units'][] = [
                'location_id' => $uid,
                'name' => $u['location_name'],
                'equipment' => []
            ];
        }
        $sectionData[] = $entry;
    }

    if (empty($allLocationIds)) {
        echo json_encode(['success' => true, 'sections' => []]);
        exit;
    }

    // 4. Fetch ALL equipment for these locations in one query
    //    Equipment can be linked via eq.location_id OR employee.location_id
    $ph = implode(',', array_fill(0, count($allLocationIds), '?'));
    $eqStmt = $db->prepare("
        SELECT eq.equipment_id, eq.type_id, eq.employee_id,
               eq.brand, eq.model, eq.serial_number, eq.status,
               eq.year_acquired,
               r.typeName,
               CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) AS employeeName,
               COALESCE(eq.location_id, e.location_id) AS resolved_location_id
        FROM tbl_equipment eq
        INNER JOIN tbl_equipment_type_registry r ON eq.type_id = r.typeId
        LEFT JOIN tbl_employee e ON eq.employee_id = e.employeeId
        WHERE eq.is_archived = 0
          AND (eq.location_id IN ($ph) OR e.location_id IN ($ph))
        ORDER BY r.typeName, eq.brand
    ");
    $params = array_merge($allLocationIds, $allLocationIds);
    $eqStmt->execute($params);
    $allEquipment = $eqStmt->fetchAll(PDO::FETCH_ASSOC);

    // Bulk-load specs for maintenance date
    $eqIds = array_column($allEquipment, 'equipment_id');
    $specsByEq = [];
    $nextDueByEq = [];
    $scheduledEqIds = [];
    if (!empty($eqIds)) {
        $spPh = implode(',', array_fill(0, count($eqIds), '?'));
        $spStmt = $db->prepare("SELECT equipment_id, spec_key, spec_value FROM tbl_equipment_specs WHERE equipment_id IN ($spPh) AND spec_key IN ('Maintenance Date', 'Next Maintenance Date')");
        $spStmt->execute($eqIds);
        while ($sr = $spStmt->fetch(PDO::FETCH_ASSOC)) {
            if ($sr['spec_key'] === 'Maintenance Date') {
                $specsByEq[(int)$sr['equipment_id']] = $sr['spec_value'];
            } else {
                $nextDueByEq[(int)$sr['equipment_id']] = $sr['spec_value'];
            }
        }

        // Bulk-load active schedule IDs
        $schStmt = $db->prepare("SELECT DISTINCT equipmentId FROM tbl_maintenance_schedule WHERE equipmentId IN ($spPh) AND isActive = 1");
        $schStmt->execute($eqIds);
        while ($sid = $schStmt->fetchColumn()) {
            $scheduledEqIds[(int)$sid] = true;
        }
    }

    // 5. Group equipment by resolved_location_id
    $eqByLoc = [];
    foreach ($allEquipment as $eq) {
        $lid = (int)$eq['resolved_location_id'];
        $eqByLoc[$lid][] = [
            'id'           => (int)$eq['equipment_id'],
            'type_id'      => (int)$eq['type_id'],
            'typeName'     => $eq['typeName'],
            'brand'        => $eq['brand'],
            'model'        => $eq['model'],
            'serial'       => $eq['serial_number'],
            'status'       => $eq['status'],
            'employeeName' => $eq['employeeName'],
            'lastMaint'    => $specsByEq[(int)$eq['equipment_id']] ?? null,
            'nextDue'      => $nextDueByEq[(int)$eq['equipment_id']] ?? null,
            'hasSchedule'  => isset($scheduledEqIds[(int)$eq['equipment_id']]),
        ];
    }

    // 6. Assign equipment to sections/units + compute schedule stats
    foreach ($sectionData as &$sec) {
        $sec['equipment'] = $eqByLoc[$sec['location_id']] ?? [];
        $secTotal = count($sec['equipment']);
        $secScheduled = count(array_filter($sec['equipment'], fn($e) => $e['hasSchedule']));
        foreach ($sec['units'] as &$unit) {
            $unit['equipment'] = $eqByLoc[$unit['location_id']] ?? [];
            $unitTotal = count($unit['equipment']);
            $unitScheduled = count(array_filter($unit['equipment'], fn($e) => $e['hasSchedule']));
            $unit['schedule_stats'] = [
                'total' => $unitTotal,
                'scheduled' => $unitScheduled,
                'unscheduled' => $unitTotal - $unitScheduled
            ];
            $secTotal += $unitTotal;
            $secScheduled += $unitScheduled;
        }
        $sec['schedule_stats'] = [
            'total' => $secTotal,
            'scheduled' => $secScheduled,
            'unscheduled' => $secTotal - $secScheduled
        ];
    }
    unset($sec, $unit);

    echo json_encode(['success' => true, 'sections' => $sectionData]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
