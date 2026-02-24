<?php
/**
 * process_maintenance.php
 *
 * Legacy endpoint for recording maintenance. Now mirrors record_maintenance.php:
 *   - Requires authenticated session
 *   - Records accountId, preparedBy, checkedBy, notedBy
 *   - Stores responses in tbl_maintenance_response (primary)
 *   - Keeps checklistJson as read-only backup for backward compat
 */
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
        throw new Exception('Invalid request method');
    }

    // 1. Get POST Data (JSON body)
    $input = json_decode(file_get_contents('php://input'), true);

    $scheduleId  = $input['scheduleId']  ?? null;
    $templateId  = !empty($input['templateId']) ? (int)$input['templateId'] : null;
    $remarks     = $input['remarks']     ?? '';
    $rating      = $input['rating']      ?? 'Good';
    $status      = $input['status']      ?? 'Operational';
    $checklist   = $input['checklist']   ?? $input['checklistData'] ?? [];
    $signatories = $input['signatories'] ?? [];

    if (empty($scheduleId)) {
        throw new Exception('Missing required scheduleId');
    }

    $db->beginTransaction();

    try {
        // 1b. Lookup real equipment details from schedule (source of truth)
        $stmtLookup = $db->prepare("SELECT equipmentId, equipmentType FROM tbl_maintenance_schedule WHERE scheduleId = ?");
        $stmtLookup->execute([$scheduleId]);
        $schedInfo = $stmtLookup->fetch(PDO::FETCH_ASSOC);

        if (!$schedInfo) {
            throw new Exception('Invalid Schedule ID');
        }

        $realEquipmentId = $schedInfo['equipmentId'];
        $realTypeId      = $schedInfo['equipmentType'];

        // Resolve signatory names
        $preparedBy = !empty($signatories['preparedBy']) ? $signatories['preparedBy'] : ($_SESSION['user_name'] ?? 'Unknown');
        $checkedBy  = !empty($signatories['checkedBy'])  ? $signatories['checkedBy']  : null;
        $notedBy    = !empty($signatories['notedBy'])     ? $signatories['notedBy']    : null;

        // 2. Insert into tbl_maintenance_record
        $stmtRecord = $db->prepare("
            INSERT INTO tbl_maintenance_record
            (scheduleId, templateId, equipmentTypeId, equipmentId, accountId,
             maintenanceDate, checklistJson, remarks, overallStatus, conditionRating,
             preparedBy, checkedBy, notedBy)
            VALUES
            (:sid, :tmpl, :tid, :eid, :uid,
             NOW(), :json, :remarks, :status, :rating,
             :prep, :check, :note)
        ");
        $stmtRecord->execute([
            ':sid'    => $scheduleId,
            ':tmpl'   => $templateId,
            ':tid'    => $realTypeId,
            ':eid'    => $realEquipmentId,
            ':uid'    => $_SESSION['user_id'],
            ':json'   => json_encode($checklist),   // backup only
            ':remarks'=> $remarks,
            ':status' => $status,
            ':rating' => $rating,
            ':prep'   => $preparedBy,
            ':check'  => $checkedBy,
            ':note'   => $notedBy
        ]);

        $newRecordId = (int)$db->lastInsertId();

        // 2b. Insert individual responses into tbl_maintenance_response (primary storage)
        if (!empty($checklist) && is_array($checklist)) {
            $stmtResp = $db->prepare("
                INSERT INTO tbl_maintenance_response
                (recordId, itemId, categoryId, categoryName, taskDescription, response, sequenceOrder)
                VALUES (:rid, :iid, :cid, :catName, :task, :resp, :seq)
            ");

            foreach ($checklist as $idx => $item) {
                $itemId     = !empty($item['itemId'])     ? (int)$item['itemId']     : null;
                $categoryId = !empty($item['categoryId']) ? (int)$item['categoryId'] : null;
                $catName    = $item['categoryName'] ?? 'General';
                $task       = $item['desc'] ?? $item['taskDescription'] ?? $item['text'] ?? '';
                $resp       = $item['status'] ?? $item['value'] ?? 'N/A';
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

        // 3. Update Schedule (Move Next Due Date forward)
        $stmtFreq = $db->prepare("SELECT maintenanceFrequency FROM tbl_maintenance_schedule WHERE scheduleId = ?");
        $stmtFreq->execute([$scheduleId]);
        $freqName = $stmtFreq->fetchColumn();

        $daysToAdd = 180; // Default Semi-Annual
        if ($freqName === 'Monthly')    $daysToAdd = 30;
        elseif ($freqName === 'Quarterly')  $daysToAdd = 90;
        elseif ($freqName === 'Annual')     $daysToAdd = 365;

        $stmtUpdate = $db->prepare("
            UPDATE tbl_maintenance_schedule
            SET lastMaintenanceDate = CURDATE(),
                nextDueDate = DATE_ADD(CURDATE(), INTERVAL ? DAY)
            WHERE scheduleId = ?
        ");
        $stmtUpdate->execute([$daysToAdd, $scheduleId]);

        $db->commit();

        logActivity(ACTION_CREATE, MODULE_MAINTENANCE, "Recorded maintenance for schedule #{$scheduleId} (Record #{$newRecordId})");

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