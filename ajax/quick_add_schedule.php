<?php
require_once '../config/database.php';
require_once '../config/config.php';
header('Content-Type: application/json');
$db = getDB();

$data = json_decode(file_get_contents('php://input'), true);
$equipmentId = $data['equipmentId'] ?? null;
$typeId = $data['equipmentType'] ?? null;

if (!$equipmentId || !$typeId) {
    echo json_encode(['success' => false, 'message' => 'Missing ID or Type']);
    exit;
}

try {
    // 1. Check if schedule already exists (Safety Check)
    $stmt = $db->prepare("SELECT scheduleId FROM tbl_maintenance_schedule WHERE equipmentId = ? AND equipmentType = ? AND isActive = 1");
    $stmt->execute([$equipmentId, $typeId]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        echo json_encode(['success' => true, 'scheduleId' => $existing['scheduleId'], 'message' => 'Found existing schedule']);
        exit;
    }

    // 2. INTELLIGENT SYNC: Find the Asset's Location
    // We use the Master View because it already handles the logic of 
    // resolving Employee -> Location or Equipment -> Location
    $stmtLoc = $db->prepare("SELECT location_name FROM view_maintenance_master WHERE id = ? AND type_id = ?");
    $stmtLoc->execute([$equipmentId, $typeId]);
    $locationRow = $stmtLoc->fetch(PDO::FETCH_ASSOC);
    $locationName = $locationRow['location_name'] ?? null;

    // 3. Determine the Start Date
    $dateToUse = date('Y-m-d'); // Default: Today
    $syncMessage = "Started new cycle (No location set)";

    if ($locationName) {
        // Look for ANY other equipment in this same location that already has a schedule
        // We join Schedule + View to find a "neighbor"
        $stmtNeighbor = $db->prepare("
            SELECT ms.nextDueDate 
            FROM tbl_maintenance_schedule ms
            JOIN view_maintenance_master v 
              ON ms.equipmentId = v.id AND ms.equipmentType = v.type_id
            WHERE v.location_name = ? 
              AND ms.isActive = 1 
              AND ms.nextDueDate >= CURDATE() -- Only sync with future/current cycles
            ORDER BY ms.nextDueDate ASC 
            LIMIT 1
        ");
        $stmtNeighbor->execute([$locationName]);
        $neighbor = $stmtNeighbor->fetch(PDO::FETCH_ASSOC);

        if ($neighbor && !empty($neighbor['nextDueDate'])) {
            $dateToUse = $neighbor['nextDueDate'];
            $syncMessage = "Synced with location: " . $locationName;
        } else {
            $syncMessage = "Started new cycle for location: " . $locationName;
        }
    }

    // 4. Create the Schedule
    $stmtInsert = $db->prepare("
        INSERT INTO tbl_maintenance_schedule 
        (equipmentType, equipmentId, maintenanceFrequency, nextDueDate, isActive) 
        VALUES (?, ?, 'Semi-Annual', ?, 1)
    ");
    $stmtInsert->execute([$typeId, $equipmentId, $dateToUse]);
    
    $newScheduleId = $db->lastInsertId();

    logActivity(ACTION_CREATE, MODULE_MAINTENANCE, "Created maintenance schedule #{$newScheduleId} for equipment ID {$equipmentId} (type {$typeId}). {$syncMessage}");

    echo json_encode([
        'success' => true, 
        'scheduleId' => $newScheduleId, 
        'message' => 'Schedule created. ' . $syncMessage
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>