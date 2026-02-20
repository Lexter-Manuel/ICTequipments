<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';
header('Content-Type: application/json');

// Require a logged-in user
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$db = getDB();

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate Inputs
    if (empty($input['scheduleId']) || empty($input['checklistData'])) {
        throw new Exception("Missing required data");
    }

    $db->beginTransaction();

    try {
        // 1. LOOKUP: Fetch the correct Equipment ID and Type from the Schedule Table
        // This fixes the "0" issue by getting the real data from the source of truth
        $stmtLookup = $db->prepare("SELECT equipmentId, equipmentType FROM tbl_maintenance_schedule WHERE scheduleId = ?");
        $stmtLookup->execute([$input['scheduleId']]);
        $schedInfo = $stmtLookup->fetch(PDO::FETCH_ASSOC);

        if (!$schedInfo) {
            throw new Exception("Invalid Schedule ID");
        }

        $realEquipmentId = $schedInfo['equipmentId'];
        $realTypeId = $schedInfo['equipmentType']; // Matches tbl_maintenance_record.equipmentTypeId

        // 2. Insert into tbl_maintenance_record (The History Log)
        $stmtRecord = $db->prepare("
            INSERT INTO tbl_maintenance_record 
            (scheduleId, equipmentTypeId, equipmentId, accountId, maintenanceDate, checklistJson, remarks, overallStatus, preparedBy, checkedBy, notedBy) 
            VALUES 
            (:sid, :tid, :eid, :uid, NOW(), :json, :remarks, :status, :prep, :check, :note)
        ");

        $stmtRecord->execute([
            ':sid'    => $input['scheduleId'],
            ':tid'    => $realTypeId,       // <--- Uses the ID found in the database
            ':eid'    => $realEquipmentId,  // <--- Uses the ID found in the database
            ':uid'    => $_SESSION['user_id'],
            ':json'   => json_encode($input['checklistData']),
            ':remarks'=> $input['remarks'],
            ':status' => $input['overallStatus'] ?? 'Operational',
            ':prep'   => !empty($input['signatories']['preparedBy']) ? $input['signatories']['preparedBy'] : ($_SESSION['user_name'] ?? 'Unknown'),
            ':check'  => $input['signatories']['checkedBy'],
            ':note'   => $input['signatories']['notedBy']
        ]);

        // 3. Calculate Next Due Date (Update the Schedule)
        $stmtFreq = $db->prepare("SELECT maintenanceFrequency FROM tbl_maintenance_schedule WHERE scheduleId = ?");
        $stmtFreq->execute([$input['scheduleId']]);
        $freqName = $stmtFreq->fetchColumn();

        $daysToAdd = 180; // Default Semi-Annual
        if ($freqName === 'Monthly') $daysToAdd = 30;
        elseif ($freqName === 'Quarterly') $daysToAdd = 90;
        elseif ($freqName === 'Annual') $daysToAdd = 365;

        // Update the Schedule Table
        $stmtUpdate = $db->prepare("
            UPDATE tbl_maintenance_schedule 
            SET lastMaintenanceDate = CURDATE(),
                nextDueDate = DATE_ADD(CURDATE(), INTERVAL ? DAY)
            WHERE scheduleId = ?
        ");
        $stmtUpdate->execute([$daysToAdd, $input['scheduleId']]);

        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Maintenance recorded successfully']);

    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>