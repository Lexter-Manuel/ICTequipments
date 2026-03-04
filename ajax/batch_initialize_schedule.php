<?php
/**
 * batch_initialize_schedule.php
 * ─────────────────────────────
 * Creates maintenance schedules for ALL equipment under a given
 * organizational node (unit or leaf-section). Equipment that already
require_once '../config/session-guard.php';
 * has an active schedule is skipped.
 *
 * POST JSON:
 *   locationId  – location_id of the unit or section
 *   startDate   – YYYY-MM-DD (the shared nextDueDate for the batch)
 *   frequency   – Monthly | Quarterly | Semi-Annual | Annual
 *
 * Returns:
 *   { success, created, skipped, total, message }
 */

if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/database.php';
require_once '../config/config.php';
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$db = getDB();

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $input = json_decode(file_get_contents('php://input'), true);

    $locationId = intval($input['locationId'] ?? 0);
    $startDate  = $input['startDate'] ?? '';
    $frequency  = $input['frequency'] ?? 'Semi-Annual';

    if (!$locationId || !$startDate) {
        throw new Exception('Missing locationId or startDate');
    }

    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
        throw new Exception('Invalid date format. Use YYYY-MM-DD.');
    }

    // Validate frequency
    $validFreq = ['Monthly', 'Quarterly', 'Semi-Annual', 'Annual'];
    if (!in_array($frequency, $validFreq)) {
        throw new Exception('Invalid frequency. Use: ' . implode(', ', $validFreq));
    }

    // ── 1. Resolve the target location ──
    $stmtLoc = $db->prepare("
        SELECT location_id, location_name, location_type_id 
        FROM location 
        WHERE location_id = ? AND is_deleted = '0'
    ");
    $stmtLoc->execute([$locationId]);
    $loc = $stmtLoc->fetch(PDO::FETCH_ASSOC);

    if (!$loc) {
        throw new Exception('Location not found');
    }

    // ── 2. Gather all location_ids under this node ──
    // If it's a section (type 2), include its child units too
    // If it's a unit (type 3), just use itself
    $locationIds = [$locationId];
    if ((int)$loc['location_type_id'] === 2) {
        $stmtChildren = $db->prepare("
            SELECT location_id FROM location 
            WHERE parent_location_id = ? AND is_deleted = '0'
        ");
        $stmtChildren->execute([$locationId]);
        $childIds = $stmtChildren->fetchAll(PDO::FETCH_COLUMN);
        $locationIds = array_merge($locationIds, $childIds);
    }

    // ── 3. Find ALL equipment under these locations via view_maintenance_master ──
    // We need to match location_name since the view resolves to location_name
    $placeholders = implode(',', array_fill(0, count($locationIds), '?'));
    $stmtNames = $db->prepare("
        SELECT location_name FROM location 
        WHERE location_id IN ($placeholders) AND is_deleted = '0'
    ");
    $stmtNames->execute($locationIds);
    $locationNames = $stmtNames->fetchAll(PDO::FETCH_COLUMN);

    if (empty($locationNames)) {
        echo json_encode([
            'success' => true, 'created' => 0, 'skipped' => 0, 'total' => 0,
            'message' => 'No locations found under this node.'
        ]);
        exit;
    }

    $namePlaceholders = implode(',', array_fill(0, count($locationNames), '?'));
    $stmtAssets = $db->prepare("
        SELECT v.type_id, v.id, v.brand, v.serial, v.location_name
        FROM view_maintenance_master v
        WHERE v.location_name IN ($namePlaceholders)
    ");
    $stmtAssets->execute($locationNames);
    $assets = $stmtAssets->fetchAll(PDO::FETCH_ASSOC);

    if (empty($assets)) {
        echo json_encode([
            'success' => true, 'created' => 0, 'skipped' => 0, 'total' => 0,
            'message' => 'No equipment found under ' . $loc['location_name'] . '.'
        ]);
        exit;
    }

    // ── 4. Batch-create schedules (skip existing active ones) ──
    $db->beginTransaction();

    $stmtCheck = $db->prepare("
        SELECT scheduleId FROM tbl_maintenance_schedule 
        WHERE equipmentType = ? AND equipmentId = ? AND isActive = 1
    ");

    $stmtInsert = $db->prepare("
        INSERT INTO tbl_maintenance_schedule 
        (equipmentType, equipmentId, maintenanceFrequency, nextDueDate, isActive, location_group_id, is_synced) 
        VALUES (?, ?, ?, ?, 1, ?, 1)
    ");

    $created = 0;
    $skipped = 0;

    foreach ($assets as $asset) {
        $stmtCheck->execute([$asset['type_id'], $asset['id']]);
        $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Already has an active schedule — optionally update its group
            $stmtUpdateGroup = $db->prepare("
                UPDATE tbl_maintenance_schedule 
                SET location_group_id = ? 
                WHERE scheduleId = ? AND location_group_id IS NULL
            ");
            $stmtUpdateGroup->execute([$locationId, $existing['scheduleId']]);
            $skipped++;
            continue;
        }

        $stmtInsert->execute([
            $asset['type_id'],
            $asset['id'],
            $frequency,
            $startDate,
            $locationId    // location_group_id
        ]);
        $created++;
    }

    $db->commit();

    $total = $created + $skipped;

    logActivity(ACTION_CREATE, MODULE_MAINTENANCE,
        "Batch initialized {$created} maintenance schedules for {$loc['location_name']} (location_id: {$locationId}). " .
        "Frequency: {$frequency}, Start: {$startDate}. Skipped {$skipped} (already scheduled). Total equipment: {$total}."
    );

    echo json_encode([
        'success' => true,
        'created' => $created,
        'skipped' => $skipped,
        'total'   => $total,
        'message' => "Created {$created} schedules for {$loc['location_name']}." .
                     ($skipped > 0 ? " {$skipped} already had active schedules." : '')
    ]);

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
