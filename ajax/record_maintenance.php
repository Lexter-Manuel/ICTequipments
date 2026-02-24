<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';
require_once '../config/config.php';
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

        // Resolve templateId (may be null for older front-end versions)
        $templateId = !empty($input['templateId']) ? (int)$input['templateId'] : null;

        // 2. Insert into tbl_maintenance_record (The History Log)
        // checklistJson is kept as a read-only backup for backward compatibility;
        // tbl_maintenance_response is the primary normalised storage.
        $stmtRecord = $db->prepare("
            INSERT INTO tbl_maintenance_record 
            (scheduleId, templateId, equipmentTypeId, equipmentId, accountId, maintenanceDate, checklistJson, remarks, overallStatus, preparedBy, checkedBy, notedBy) 
            VALUES 
            (:sid, :tmpl, :tid, :eid, :uid, NOW(), :json, :remarks, :status, :prep, :check, :note)
        ");

        $stmtRecord->execute([
            ':sid'    => $input['scheduleId'],
            ':tmpl'   => $templateId,
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

        $newRecordId = (int)$db->lastInsertId();

        // 2b. Insert individual checklist responses into tbl_maintenance_response
        if (!empty($input['checklistData']) && is_array($input['checklistData'])) {
            $stmtResp = $db->prepare("
                INSERT INTO tbl_maintenance_response
                (recordId, itemId, categoryId, categoryName, taskDescription, response, sequenceOrder)
                VALUES (:rid, :iid, :cid, :catName, :task, :resp, :seq)
            ");

            foreach ($input['checklistData'] as $idx => $item) {
                $itemId     = !empty($item['itemId'])     ? (int)$item['itemId']     : null;
                $categoryId = !empty($item['categoryId']) ? (int)$item['categoryId'] : null;
                $catName    = $item['categoryName'] ?? 'General';
                $task       = $item['desc'] ?? $item['taskDescription'] ?? '';
                $resp       = $item['status'] ?? 'N/A';
                $seq        = !empty($item['seq']) ? (int)$item['seq'] : ($idx + 1);

                // Normalise response to ENUM values
                $respLower = strtolower(trim($resp));
                if (in_array($respLower, ['yes', 'ok', 'done', 'pass', '1', 'true']))  $resp = 'Yes';
                elseif (in_array($respLower, ['no', 'fail', 'failed', '0', 'false']))   $resp = 'No';
                else                                                                     $resp = 'N/A';

                $stmtResp->execute([
                    ':rid'     => $newRecordId,
                    ':iid'     => $itemId,
                    ':cid'     => $categoryId,
                    ':catName' => $catName,
                    ':task'    => $task,
                    ':resp'    => $resp,
                    ':seq'     => $seq
                ]);
            }
        }

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

        logActivity(ACTION_CREATE, MODULE_MAINTENANCE,
            "Recorded maintenance for schedule ID {$input['scheduleId']} (Equipment ID: {$realEquipmentId}, Type ID: {$realTypeId}). Status: " . ($input['overallStatus'] ?? 'Operational') . ". Prepared by: " . ($input['signatories']['preparedBy'] ?? 'Unknown') . ".");

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